<?php

namespace App\Http\Controllers\Web\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\Customer;
use App\Services\Auth\CustomerAuthService;
use App\Services\Auth\CustomerRegistrationService;
use App\Services\Auth\ReferralContextService;
use App\Services\Dashboard\DashboardService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Throwable;

class CustomerAuthController extends Controller
{
    public function __construct(
        private readonly CustomerAuthService $authService,
        private readonly CustomerRegistrationService $registrationService,
        private readonly ReferralContextService $referralContextService,
        private readonly DashboardService $dashboardService,
    ) {}

    public function showLogin(): Response
    {
        return Inertia::render('Auth/Login', [
            'seo' => [
                'title' => 'Masuk ke Akun Member',
                'description' => 'Masuk ke akun member '.config('app.name').'. Nikmati harga eksklusif, pantau e-wallet, dan kelola jaringan afiliasi Anda kapan saja.',
                'canonical' => route('login'),
            ],
        ]);
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        if (! $this->authService->attemptLogin(
            $request->string('username'),
            $request->string('password'),
            $request->boolean('remember')
        )) {
            return back()
                ->withErrors(['username' => 'Username atau kata sandi salah.'])
                ->onlyInput('username');
        }

        $request->session()->regenerate();

        return redirect()->intended('/');
    }

    public function showRegister(Request $request): Response
    {
        $referralContext = $this->referralContextService->captureFromRequest($request);

        return Inertia::render('Auth/Register', [
            'referralCode' => $referralContext['referralCode'],
            'referralUsername' => $referralContext['referralUsername'],
            'debugMode' => config('app.debug'),
            'seo' => [
                'title' => 'Daftar Jadi Member',
                'description' => 'Bergabunglah sebagai member '.config('app.name').' dan nikmati harga eksklusif, bonus afiliasi, serta akses ke ribuan produk unggulan. Gratis daftar sekarang!',
                'canonical' => route('register'),
            ],
        ]);
    }

    public function register(RegisterRequest $request): RedirectResponse
    {
        try {
            $this->registrationService->register($request);
        } catch (Throwable $exception) {
            Log::error('Customer registration failed.', [
                'username' => $request->string('username')->toString(),
                'email' => $request->string('email')->toString(),
                'telp' => $request->string('telp')->toString(),
                'referral_username' => $request->string('referral_username')->toString(),
                'referral_code' => $request->string('referral_code')->toString(),
                'exception' => $exception::class,
                'message' => $exception->getMessage(),
            ]);

            return back()
                ->withErrors([
                    'error' => 'Pendaftaran gagal. Silakan coba lagi.',
                ])
                ->onlyInput(
                    'name',
                    'username',
                    'email',
                    'telp',
                    'nik',
                    'gender',
                    'alamat',
                    'referral_username',
                    'referral_code',
                    'terms',
                );
        }

        return redirect()->route('login')->with('status', 'Akun berhasil dibuat! Silakan masuk.');
    }

    public function logout(Request $request): RedirectResponse
    {
        $this->authService->logout($request);

        return redirect('/');
    }

    public function dashboard(Request $request): Response
    {
        /** @var Customer $customer */
        $customer = auth('customer')->user();

        if (! $customer) {
            abort(401);
        }

        $walletFilters = [
            'search' => $request->query('wallet_search'),
            'type' => $request->query('wallet_type'),
            'status' => $request->query('wallet_status'),
        ];
        $orderFilters = [
            'q' => $request->query('orders_q'),
            'status' => $request->query('orders_status'),
            'sort' => $request->query('orders_sort'),
            'date_from' => $request->query('orders_date_from'),
            'date_to' => $request->query('orders_date_to'),
        ];
        $networkRootId = $request->integer('network_root_id');

        if ($networkRootId <= 0) {
            $networkRootId = null;
        }

        return Inertia::render('Auth/Dashboard/Index', [
            'seo' => [
                'title' => 'Dashboard Member',
                'description' => 'Kelola akun member Anda di '.config('app.name').'. Pantau e-wallet, akses riwayat pesanan, dan nikmati penawaran eksklusif hanya untuk member.',
                'canonical' => route('dashboard'),
            ],
            ...$this->dashboardService->getPageData(
                $customer,
                max(1, (int) $request->integer('orders_page', 1)),
                max(1, (int) $request->integer('wallet_page', 1)),
                $walletFilters,
                $orderFilters,
                $networkRootId,
            ),
        ]);
    }

    public function stopImpersonation(Request $request): RedirectResponse|SymfonyResponse
    {
        $impersonationSession = $request->session()->get('impersonation', []);

        if (! is_array($impersonationSession) || ! (bool) ($impersonationSession['is_active'] ?? false)) {
            return redirect()->route('home');
        }

        $adminStillAuthenticated = Auth::guard('web')->check();

        Auth::guard('customer')->logout();

        $request->session()->forget('impersonation');
        $request->session()->regenerate();
        $request->session()->regenerateToken();

        if ($adminStillAuthenticated) {
            $customerIndexUrl = route('filament.control-panel.resources.customers.index');

            if ($request->header('X-Inertia')) {
                return Inertia::location($customerIndexUrl);
            }

            return redirect()->to($customerIndexUrl);
        }

        return redirect()->route('home');
    }
}
