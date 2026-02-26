<?php

namespace App\Http\Controllers\Web\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Services\Auth\CustomerAuthService;
use App\Services\Auth\CustomerRegistrationService;
use App\Services\Dashboard\DashboardService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class CustomerAuthController extends Controller
{
    public function __construct(
        private readonly CustomerAuthService $authService,
        private readonly CustomerRegistrationService $registrationService,
        private readonly DashboardService $dashboardService,
    ) {}

    public function showLogin(): Response
    {
        return Inertia::render('Auth/Login', [
            'seo' => [
                'title'       => 'Masuk ke Akun Member',
                'description' => 'Masuk ke akun member ' . config('app.name') . '. Nikmati harga eksklusif, pantau e-wallet, dan kelola jaringan afiliasi Anda kapan saja.',
                'canonical'   => route('login'),
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
        if ($request->has('referral_code')) {
            $referralCode = $request->query('referral_code');
            // Simpan referral code di session untuk digunakan saat registrasi
            $request->session()->put('referral_code', $referralCode);
        }
        return Inertia::render('Auth/Register', [
            'referralCode' => $request->session()->get('referral_code'),
            'seo'          => [
                'title'       => 'Daftar Jadi Member',
                'description' => 'Bergabunglah sebagai member ' . config('app.name') . ' dan nikmati harga eksklusif, bonus afiliasi, serta akses ke ribuan produk unggulan. Gratis daftar sekarang!',
                'canonical'   => route('register'),
            ],
        ]);
    }

    public function register(RegisterRequest $request): RedirectResponse
    {
        $this->registrationService->register($request);

        return redirect()->route('login')->with('status', 'Akun berhasil dibuat! Silakan masuk.');
    }

    public function logout(Request $request): RedirectResponse
    {
        $this->authService->logout($request);

        return redirect('/');
    }

    public function dashboard(Request $request): Response
    {
        /** @var \App\Models\Customer $customer */
        $customer = auth('customer')->user();

        if (! $customer) {
            abort(401);
        }

        $walletFilters = [
            'search' => $request->query('wallet_search'),
            'type' => $request->query('wallet_type'),
            'status' => $request->query('wallet_status'),
        ];

        return Inertia::render('Auth/Dashboard/Index', [
            'seo' => [
                'title'       => 'Dashboard Member',
                'description' => 'Kelola akun member Anda di ' . config('app.name') . '. Pantau e-wallet, akses riwayat pesanan, dan nikmati penawaran eksklusif hanya untuk member.',
                'canonical'   => route('dashboard'),
            ],
            ...$this->dashboardService->getPageData(
                $customer,
                max(1, (int) $request->integer('orders_page', 1)),
                max(1, (int) $request->integer('wallet_page', 1)),
                $walletFilters,
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
