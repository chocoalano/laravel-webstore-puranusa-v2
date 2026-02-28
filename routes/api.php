<?php

use App\Http\Controllers\Api\ArticleController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BerandaController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CheckoutController;
use App\Http\Controllers\Api\CustomerAddressController;
use App\Http\Controllers\Api\DashboardAccountController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\DashboardOrderController;
use App\Http\Controllers\Api\DashboardWalletController;
use App\Http\Controllers\Api\MidtransWebhookController;
use App\Http\Controllers\Api\MlmPlacementController;
use App\Http\Controllers\Api\NewsletterSubscriptionController;
use App\Http\Controllers\Api\OrderInvoiceDownloadController;
use App\Http\Controllers\Api\PageController;
use App\Http\Controllers\Api\ShopController;
use App\Http\Controllers\Api\WishlistController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/health', fn () => ['status' => 'ok'])->name('api.health');

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum')->name('api.user');

Route::get('/home', [BerandaController::class, 'index'])->name('api.home.index');
Route::get('/shop', [ShopController::class, 'index'])->name('api.shop.index');
Route::get('/shop/{slug}', [ShopController::class, 'show'])->name('api.shop.show');
Route::get('/articles', [ArticleController::class, 'index'])->name('api.articles.index');
Route::get('/articles/{slug}', [ArticleController::class, 'show'])->name('api.articles.show');
Route::get('/pages/{slug}', [PageController::class, 'show'])->name('api.pages.show');

Route::post('/newsletter/subscribe', NewsletterSubscriptionController::class)
    ->middleware('throttle:10,1')
    ->name('api.newsletter.subscribe');

Route::post('/payments/midtrans/callback', MidtransWebhookController::class)
    ->name('api.payments.midtrans.callback');

Route::prefix('auth')->name('api.auth.')->group(function (): void {
    Route::get('/login-meta', [AuthController::class, 'loginMeta'])->name('login-meta');
    Route::get('/register-meta', [AuthController::class, 'registerMeta'])->name('register-meta');
    Route::post('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/login', [AuthController::class, 'login'])->name('login');

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('/me', [AuthController::class, 'me'])->name('me');
        Route::post('/impersonation/stop', [AuthController::class, 'stopImpersonation'])->name('impersonation.stop');
    });
});

Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('api.dashboard.index');
    Route::post('/mlm/place-member', [MlmPlacementController::class, 'store'])->name('api.mlm.place-member');

    Route::prefix('cart')->name('api.cart.')->group(function (): void {
        Route::post('/items', [CartController::class, 'addItem'])->name('items.store');
        Route::patch('/items/{cartItem}', [CartController::class, 'updateItem'])->name('items.update');
        Route::delete('/items/{cartItem}', [CartController::class, 'removeItem'])->name('items.destroy');
        Route::delete('/', [CartController::class, 'clearCart'])->name('clear');
    });

    Route::prefix('wishlist')->name('api.wishlist.')->group(function (): void {
        Route::post('/toggle', [WishlistController::class, 'toggle'])->name('toggle');
        Route::delete('/items/{wishlistItem}', [WishlistController::class, 'removeItem'])->name('items.destroy');
        Route::post('/remove-selected', [WishlistController::class, 'removeSelected'])->name('remove-selected');
        Route::delete('/', [WishlistController::class, 'clearWishlist'])->name('clear');
        Route::post('/items/{wishlistItem}/move-to-cart', [WishlistController::class, 'moveToCart'])->name('items.move-to-cart');
        Route::post('/move-to-cart', [WishlistController::class, 'bulkMoveToCart'])->name('move-to-cart');
    });

    Route::prefix('checkout')->name('api.checkout.')->group(function (): void {
        Route::get('/', [CheckoutController::class, 'index'])->name('index');
        Route::get('/shipping/provinces', [CheckoutController::class, 'provinces'])->name('shipping.provinces');
        Route::get('/shipping/cities', [CheckoutController::class, 'cities'])->name('shipping.cities');
        Route::get('/shipping/districts', [CheckoutController::class, 'districts'])->name('shipping.districts');
        Route::get('/shipping/cost', [CheckoutController::class, 'shippingCost'])->name('shipping.cost');
        Route::post('/midtrans/token', [CheckoutController::class, 'getMidtransToken'])->name('midtrans.token');
        Route::post('/pay/saldo', [CheckoutController::class, 'payWithSaldo'])->name('pay.saldo');
    });

    Route::prefix('account/addresses')->name('api.account.addresses.')->group(function (): void {
        Route::get('/', [CustomerAddressController::class, 'index'])->name('index');
        Route::get('/options/provinces', [CustomerAddressController::class, 'provinceOptions'])->name('options.provinces');
        Route::get('/options/cities', [CustomerAddressController::class, 'cityOptions'])->name('options.cities');
        Route::get('/options/districts', [CustomerAddressController::class, 'districtOptions'])->name('options.districts');
        Route::post('/', [CustomerAddressController::class, 'store'])->name('store');
        Route::put('/{addressId}', [CustomerAddressController::class, 'update'])->whereNumber('addressId')->name('update');
        Route::delete('/{addressId}', [CustomerAddressController::class, 'destroy'])->whereNumber('addressId')->name('destroy');
        Route::post('/{addressId}/default', [CustomerAddressController::class, 'setDefault'])->whereNumber('addressId')->name('set-default');
    });

    Route::prefix('dashboard/orders')->name('api.dashboard.orders.')->group(function (): void {
        Route::post('/{order}/payment-status', [DashboardOrderController::class, 'checkPaymentStatus'])
            ->whereNumber('order')
            ->name('check-payment-status');
        Route::post('/{order}/pay-now', [DashboardOrderController::class, 'createMidtransPayNowToken'])
            ->whereNumber('order')
            ->name('pay-now');
        Route::get('/{order}/invoice', [DashboardOrderController::class, 'downloadInvoice'])
            ->whereNumber('order')
            ->name('invoice');
    });

    Route::prefix('dashboard/wallet')->name('api.dashboard.wallet.')->group(function (): void {
        Route::post('/topup/token', [DashboardWalletController::class, 'createTopupToken'])->name('topup-token');
        Route::post('/topup/{walletTransaction}/payment-status', [DashboardWalletController::class, 'syncTopupStatus'])
            ->whereNumber('walletTransaction')
            ->name('topup-sync-status');
        Route::post('/withdrawal', [DashboardWalletController::class, 'storeWithdrawal'])->name('withdrawal');
    });

    Route::post('/dashboard/account/profile', [DashboardAccountController::class, 'update'])
        ->name('api.dashboard.account.update');

    Route::get('/control-panel/orders/{order}/invoice', OrderInvoiceDownloadController::class)
        ->whereNumber('order')
        ->name('api.control-panel.orders.invoice');
});
