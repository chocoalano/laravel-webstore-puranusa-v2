<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ResolvesSanctumCustomer;
use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\CreateWalletTopupTokenRequest;
use App\Http\Requests\Dashboard\StoreWalletWithdrawalRequest;
use App\Http\Requests\Dashboard\SyncWalletTopupStatusRequest;
use App\Models\CustomerWalletTransaction;
use App\Services\Dashboard\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use OpenApi\Annotations as OA;

class DashboardWalletController extends Controller
{
    use ResolvesSanctumCustomer;

    public function __construct(
        private readonly DashboardService $dashboardService,
    ) {}

    /**
     * @OA\Post(
     *     path="/api/dashboard/wallet/topup/token",
     *     tags={"Dashboard Wallet"},
     *     summary="Buat token topup wallet",
     *     security={{"sanctum":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"amount"},
     *
     *             @OA\Property(property="amount", type="number", format="float", minimum=10000, example=100000),
     *             @OA\Property(property="notes", type="string", nullable=true, example="Topup saldo via mobile app")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Token topup berhasil dibuat",
     *
     *         @OA\JsonContent(
     *             example={
     *                 "message":"Token topup Midtrans berhasil dibuat.",
     *                 "snapToken":"midtrans-wallet-snap-token",
     *                 "transaction":{"id":991,"type":"topup","status":"pending"}
     *             }
     *         )
     *     ),
     *
     *     @OA\Response(response=401, description="Tidak terautentikasi", @OA\JsonContent(example={"message":"Tidak terautentikasi."})),
     *     @OA\Response(response=422, description="Validasi gagal", @OA\JsonContent(example={"message":"Data tidak valid.","errors":{"field":{"Field wajib diisi."}}}))
     * )
     */
    public function createTopupToken(CreateWalletTopupTokenRequest $request): JsonResponse
    {
        $customer = $this->resolveSanctumCustomer($request);

        if (! $customer) {
            return response()->json(['message' => 'Tidak terautentikasi.'], 401);
        }

        try {
            return response()->json(
                $this->dashboardService->createWalletTopupToken($customer, $request->payload())
            );
        } catch (ValidationException $exception) {
            $firstError = collect($exception->errors())->flatten()->first();
            $message = is_string($firstError) ? $firstError : 'Gagal membuat token topup Midtrans.';

            return response()->json([
                'message' => $message,
                'errors' => $exception->errors(),
            ], 422);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/dashboard/wallet/topup/{walletTransaction}/payment-status",
     *     tags={"Dashboard Wallet"},
     *     summary="Sinkronisasi status topup wallet",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(name="walletTransaction", in="path", required=true, @OA\Schema(type="integer")),
     *
     *     @OA\RequestBody(
     *         required=false,
     *
     *         @OA\JsonContent(example={})
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Status topup berhasil disinkronkan",
     *
     *         @OA\JsonContent(
     *             example={
     *                 "message":"Status topup berhasil disinkronkan.",
     *                 "data":{
     *                     "transaction":{"id":991,"status":"completed"},
     *                     "balance":750000
     *                 }
     *             }
     *         )
     *     ),
     *
     *     @OA\Response(response=401, description="Tidak terautentikasi", @OA\JsonContent(example={"message":"Tidak terautentikasi."})),
     *     @OA\Response(response=403, description="Tidak diizinkan", @OA\JsonContent(example={"message":"Tindakan tidak diizinkan."})),
     *     @OA\Response(response=422, description="Validasi gagal", @OA\JsonContent(example={"message":"Data tidak valid.","errors":{"field":{"Field wajib diisi."}}}))
     * )
     */
    public function syncTopupStatus(
        SyncWalletTopupStatusRequest $request,
        CustomerWalletTransaction $walletTransaction
    ): JsonResponse {
        $customer = $this->resolveSanctumCustomer($request);

        if (! $customer) {
            return response()->json(['message' => 'Tidak terautentikasi.'], 401);
        }

        try {
            $result = $this->dashboardService->syncWalletTopupStatus($customer, (int) $walletTransaction->id);

            return response()->json([
                'message' => $result['message'],
                'data' => [
                    'transaction' => $result['transaction'],
                    'balance' => $result['balance'],
                ],
            ]);
        } catch (ValidationException $exception) {
            $firstError = collect($exception->errors())->flatten()->first();
            $message = is_string($firstError) ? $firstError : 'Gagal sinkronisasi status topup Midtrans.';

            return response()->json([
                'message' => $message,
                'errors' => $exception->errors(),
            ], 422);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/dashboard/wallet/withdrawal",
     *     tags={"Dashboard Wallet"},
     *     summary="Ajukan withdrawal wallet",
     *     security={{"sanctum":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"amount","password"},
     *
     *             @OA\Property(property="amount", type="number", format="float", minimum=10000, example=50000),
     *             @OA\Property(property="password", type="string", format="password", example="secret123"),
     *             @OA\Property(property="notes", type="string", nullable=true, example="Penarikan mingguan")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Permintaan withdrawal berhasil dibuat",
     *
     *         @OA\JsonContent(
     *             example={
     *                 "message":"Permintaan withdrawal berhasil dibuat.",
     *                 "data":{
     *                     "transaction":{"id":1202,"status":"pending"},
     *                     "balance":500000
     *                 }
     *             }
     *         )
     *     ),
     *
     *     @OA\Response(response=401, description="Tidak terautentikasi", @OA\JsonContent(example={"message":"Tidak terautentikasi."})),
     *     @OA\Response(response=422, description="Validasi gagal", @OA\JsonContent(example={"message":"Data tidak valid.","errors":{"field":{"Field wajib diisi."}}}))
     * )
     */
    public function storeWithdrawal(StoreWalletWithdrawalRequest $request): JsonResponse
    {
        $customer = $this->resolveSanctumCustomer($request);

        if (! $customer) {
            return response()->json(['message' => 'Tidak terautentikasi.'], 401);
        }

        try {
            $result = $this->dashboardService->submitWalletWithdrawal($customer, $request->payload());

            return response()->json([
                'message' => $result['message'],
                'data' => [
                    'transaction' => $result['transaction'],
                    'balance' => $result['balance'],
                ],
            ]);
        } catch (ValidationException $exception) {
            $firstError = collect($exception->errors())->flatten()->first();
            $message = is_string($firstError) ? $firstError : 'Gagal mengirim permintaan withdrawal.';

            return response()->json([
                'message' => $message,
                'errors' => $exception->errors(),
            ], 422);
        }
    }
}
