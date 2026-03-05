<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\Home\HomeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class BerandaController extends Controller
{
    public function index(Request $request, HomeService $homeService): Response
    {
        if ($request->has('referral_code')) {
            $request->session()->put('referral_code', $request->query('referral_code'));
        }

        return Inertia::render('Home', [
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
            ...$homeService->getIndexPageData(),
        ]);
    }
}
