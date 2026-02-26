<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\Home\HomeService;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class BerandaController extends Controller
{
    public function index(HomeService $homeService): Response
    {
        return Inertia::render('Home', [
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
            ...$homeService->getIndexPageData(),
        ]);
    }
}
