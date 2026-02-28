<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ResolvesSanctumCustomer;
use App\Http\Controllers\Controller;
use App\Services\Dashboard\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

class DashboardController extends Controller
{
    use ResolvesSanctumCustomer;

    public function __construct(
        private readonly DashboardService $dashboardService
    ) {}

    /**
     * @OA\Get(
     *     path="/api/dashboard",
     *     tags={"Dashboard"},
     *     summary="Data dashboard customer",
     *     description="Mengembalikan seluruh data dashboard customer seperti profil, order, wallet, bonus, dan jaringan.",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(name="orders_page", in="query", required=false, @OA\Schema(type="integer", minimum=1)),
     *     @OA\Parameter(name="wallet_page", in="query", required=false, @OA\Schema(type="integer", minimum=1)),
     *     @OA\Parameter(name="wallet_search", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="wallet_type", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="wallet_status", in="query", required=false, @OA\Schema(type="string")),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Data dashboard berhasil diambil",
     *
     *         @OA\JsonContent(
     *             example={
     *                 "message":"Data dashboard berhasil diambil.",
     *                 "data":{
     *                     "customer":{"id":1,"name":"Budi Santoso"},
     *                     "stats":{"orders_total":12,"wallet_balance":150000}
     *                 }
     *             }
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Tidak terautentikasi",
     *
     *         @OA\JsonContent(example={"message":"Tidak terautentikasi."})
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $customer = $this->resolveSanctumCustomer($request);

        if (! $customer) {
            return response()->json([
                'message' => 'Tidak terautentikasi.',
            ], 401);
        }

        $walletFilters = [
            'search' => $request->query('wallet_search'),
            'type' => $request->query('wallet_type'),
            'status' => $request->query('wallet_status'),
        ];

        $data = $this->dashboardService->getPageData(
            $customer,
            max(1, (int) $request->integer('orders_page', 1)),
            max(1, (int) $request->integer('wallet_page', 1)),
            $walletFilters
        );

        return response()->json([
            'message' => 'Data dashboard berhasil diambil.',
            'data' => $data,
        ]);
    }
}
