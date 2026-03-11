<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Services\Home\HomeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class BerandaController extends Controller
{
    public function index(Request $request, HomeService $homeService): Response
    {
        $this->captureReferralCode($request);

        return Inertia::render('Home', [
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
            ...$homeService->getIndexPageData(),
        ]);
    }

    private function captureReferralCode(Request $request): void
    {
        if ($request->filled('referral_code')) {
            $request->session()->put('referral_code', (string) $request->query('referral_code'));

            return;
        }

        if (! $request->filled('username')) {
            return;
        }

        $username = trim((string) $request->query('username'));

        if ($username === '') {
            $request->session()->forget('referral_code');

            return;
        }

        $resolvedReferralCode = Customer::query()
            ->where('username', mb_strtolower($username))
            ->orWhere('username', $username)
            ->value('ref_code');

        if (filled($resolvedReferralCode)) {
            $request->session()->put('referral_code', (string) $resolvedReferralCode);

            return;
        }

        $request->session()->forget('referral_code');
    }
}
