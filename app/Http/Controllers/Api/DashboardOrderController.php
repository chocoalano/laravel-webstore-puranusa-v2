<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ResolvesSanctumCustomer;
use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\CheckOrderPaymentStatusRequest;
use App\Http\Requests\Dashboard\CreateMidtransPayNowRequest;
use App\Http\Requests\Dashboard\DownloadOrderInvoiceRequest;
use App\Models\Order;
use App\Services\Dashboard\DashboardService;
use App\Services\Orders\OrderInvoicePdfService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use OpenApi\Annotations as OA;

class DashboardOrderController extends Controller
{
    use ResolvesSanctumCustomer;

    public function __construct(
        private readonly DashboardService $dashboardService,
    ) {}

    /**
     * @OA\Post(
     *     path="/api/dashboard/orders/{order}/payment-status",
     *     tags={"Dashboard Orders"},
     *     summary="Sinkronisasi status pembayaran order",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(name="order", in="path", required=true, @OA\Schema(type="integer")),
     *
     *     @OA\RequestBody(
     *         required=false,
     *
     *         @OA\JsonContent(example={})
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Status pembayaran berhasil diperiksa",
     *
     *         @OA\JsonContent(
     *             example={
     *                 "message":"Status pembayaran sudah diperbarui.",
     *                 "data":{"order":{"id":1201,"order_no":"ORD-20260301-ABC123","status":"paid"}}
     *             }
     *         )
     *     ),
     *
     *     @OA\Response(response=401, description="Tidak terautentikasi", @OA\JsonContent(example={"message":"Tidak terautentikasi."})),
     *     @OA\Response(response=403, description="Tidak diizinkan", @OA\JsonContent(example={"message":"Tindakan tidak diizinkan."})),
     *     @OA\Response(response=422, description="Validasi gagal", @OA\JsonContent(example={"message":"Data tidak valid.","errors":{"field":{"Field wajib diisi."}}}))
     * )
     */
    public function checkPaymentStatus(
        CheckOrderPaymentStatusRequest $request,
        Order $order
    ): JsonResponse {
        $customer = $this->resolveSanctumCustomer($request);

        if (! $customer) {
            return response()->json(['message' => 'Tidak terautentikasi.'], 401);
        }

        try {
            $result = $this->dashboardService->checkOrderPaymentStatus($customer, (int) $order->id);

            return response()->json([
                'message' => $result['message'],
                'data' => [
                    'order' => $result['order'],
                ],
            ]);
        } catch (ValidationException $exception) {
            $firstError = collect($exception->errors())->flatten()->first();
            $message = is_string($firstError) ? $firstError : 'Gagal memeriksa status pembayaran.';

            return response()->json([
                'message' => $message,
                'errors' => $exception->errors(),
            ], 422);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/dashboard/orders/{order}/pay-now",
     *     tags={"Dashboard Orders"},
     *     summary="Generate token Midtrans pay now untuk order",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(name="order", in="path", required=true, @OA\Schema(type="integer")),
     *
     *     @OA\RequestBody(
     *         required=false,
     *
     *         @OA\JsonContent(example={})
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Token pay now berhasil dibuat",
     *
     *         @OA\JsonContent(
     *             example={
     *                 "message":"Token pembayaran Midtrans berhasil dibuat.",
     *                 "snapToken":"midtrans-snap-token",
     *                 "orderId":1201,
     *                 "orderNo":"ORD-20260301-ABC123"
     *             }
     *         )
     *     ),
     *
     *     @OA\Response(response=401, description="Tidak terautentikasi", @OA\JsonContent(example={"message":"Tidak terautentikasi."})),
     *     @OA\Response(response=403, description="Tidak diizinkan", @OA\JsonContent(example={"message":"Tindakan tidak diizinkan."})),
     *     @OA\Response(response=422, description="Validasi gagal", @OA\JsonContent(example={"message":"Data tidak valid.","errors":{"field":{"Field wajib diisi."}}}))
     * )
     */
    public function createMidtransPayNowToken(
        CreateMidtransPayNowRequest $request,
        Order $order
    ): JsonResponse {
        $customer = $this->resolveSanctumCustomer($request);

        if (! $customer) {
            return response()->json(['message' => 'Tidak terautentikasi.'], 401);
        }

        try {
            return response()->json(
                $this->dashboardService->createMidtransPayNowToken($customer, (int) $order->id)
            );
        } catch (ValidationException $exception) {
            $firstError = collect($exception->errors())->flatten()->first();
            $message = is_string($firstError) ? $firstError : 'Gagal membuat token pembayaran Midtrans.';

            return response()->json([
                'message' => $message,
                'errors' => $exception->errors(),
            ], 422);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/dashboard/orders/{order}/invoice",
     *     tags={"Dashboard Orders"},
     *     summary="Download invoice order customer",
     *     description="Menghasilkan file PDF invoice untuk order customer yang sudah dibayar.",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(name="order", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="preview", in="query", required=false, @OA\Schema(type="boolean")),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Invoice PDF berhasil dibuat",
     *
     *         @OA\MediaType(
     *             mediaType="application/pdf",
     *
     *             @OA\Schema(type="string", format="binary", example="%PDF-1.7 ...binary invoice content...")
     *         )
     *     ),
     *
     *     @OA\Response(response=401, description="Tidak terautentikasi", @OA\JsonContent(example={"message":"Tidak terautentikasi."})),
     *     @OA\Response(response=403, description="Tidak diizinkan", @OA\JsonContent(example={"message":"Tindakan tidak diizinkan."})),
     *     @OA\Response(response=404, description="Invoice tidak tersedia", @OA\JsonContent(example={"message":"Invoice hanya tersedia untuk pesanan yang sudah dibayar."}))
     * )
     */
    public function downloadInvoice(
        DownloadOrderInvoiceRequest $request,
        Order $order,
        OrderInvoicePdfService $invoicePdfService
    ): Response {
        abort_if(blank($order->paid_at), 404, 'Invoice hanya tersedia untuk pesanan yang sudah dibayar.');

        $order->loadMissing([
            'customer:id,name,email',
            'shippingAddress',
            'billingAddress',
            'items:id,order_id,name,sku,qty,unit_price,row_total',
            'payments:id,order_id,status,provider_txn_id,transaction_id',
        ]);

        $invoice = $invoicePdfService->generate($order);
        $dispositionType = $request->boolean('preview') ? 'inline' : 'attachment';

        return response($invoice['content'], 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => $dispositionType.'; filename="'.$invoice['filename'].'"',
            'Content-Length' => (string) strlen($invoice['content']),
        ]);
    }
}
