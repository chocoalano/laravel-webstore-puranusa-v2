<?php

use App\Http\Controllers\Web\ArticleController;
use App\Http\Controllers\Web\Auth\CustomerAuthController;
use App\Http\Controllers\Web\BerandaController;
use App\Http\Controllers\Web\CartController;
use App\Http\Controllers\Web\CheckoutController;
use App\Http\Controllers\Web\CustomerAddressController;
use App\Http\Controllers\Web\DashboardAccountController;
use App\Http\Controllers\Web\DashboardOrderController;
use App\Http\Controllers\Web\DashboardWalletController;
use App\Http\Controllers\Web\MidtransWebhookController;
use App\Http\Controllers\Web\MlmPlacementController;
use App\Http\Controllers\Web\NewsletterSubscriptionController;
use App\Http\Controllers\Web\OrderInvoiceDownloadController;
use App\Http\Controllers\Web\PageController;
use App\Http\Controllers\Web\ShopController;
use App\Http\Controllers\Web\WishlistController;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Support\Facades\Route;

Route::get('/', [BerandaController::class, 'index'])->name('home');
Route::post('/payments/midtrans/callback', MidtransWebhookController::class)
    ->withoutMiddleware([ValidateCsrfToken::class])
    ->name('payments.midtrans.callback');
Route::post('/newsletter/subscribe', NewsletterSubscriptionController::class)
    ->middleware('throttle:10,1')
    ->name('newsletter.subscribe');
Route::middleware('auth')
    ->get('/control-panel/orders/{order}/invoice', OrderInvoiceDownloadController::class)
    ->whereNumber('order')
    ->name('control-panel.orders.invoice');

/*
|--------------------------------------------------------------------------
| Customer Authentication
|--------------------------------------------------------------------------
*/
Route::middleware('guest:customer')->group(function () {
    Route::get('/login', [CustomerAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [CustomerAuthController::class, 'login'])->name('login.store');
    Route::get('/register', [CustomerAuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [CustomerAuthController::class, 'register'])->name('register.store');
});

Route::middleware('auth:customer')->group(function () {
    Route::post('/logout', [CustomerAuthController::class, 'logout'])->name('logout');
    Route::post('/impersonation/stop', [CustomerAuthController::class, 'stopImpersonation'])->name('impersonation.stop');
    Route::get('/dashboard', [CustomerAuthController::class, 'dashboard'])->name('dashboard');

    Route::post('/cart/add', [CartController::class, 'addItem'])->name('cart.add');
    Route::patch('/cart/items/{cartItem}', [CartController::class, 'updateItem'])->name('cart.update');
    Route::delete('/cart/items/{cartItem}', [CartController::class, 'removeItem'])->name('cart.remove');
    Route::delete('/cart', [CartController::class, 'clearCart'])->name('cart.clear');
    Route::post('/wishlist/toggle', [WishlistController::class, 'toggle'])->name('wishlist.toggle');
    Route::delete('/wishlist/items/{wishlistItem}', [WishlistController::class, 'removeItem'])->name('wishlist.remove');
    Route::post('/wishlist/remove-selected', [WishlistController::class, 'removeSelected'])->name('wishlist.removeSelected');
    Route::delete('/wishlist', [WishlistController::class, 'clearWishlist'])->name('wishlist.clear');
    Route::post('/wishlist/items/{wishlistItem}/move-to-cart', [WishlistController::class, 'moveToCart'])->name('wishlist.moveToCart');
    Route::post('/wishlist/move-to-cart', [WishlistController::class, 'bulkMoveToCart'])->name('wishlist.bulkMoveToCart');
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::get('/checkout/shipping/provinces', [CheckoutController::class, 'provinces'])->name('checkout.shipping.provinces');
    Route::get('/checkout/shipping/cities', [CheckoutController::class, 'cities'])->name('checkout.shipping.cities');
    Route::get('/checkout/shipping/districts', [CheckoutController::class, 'districts'])->name('checkout.shipping.districts');
    Route::get('/checkout/shipping/cost', [CheckoutController::class, 'shippingCost'])->name('checkout.shipping.cost');
    Route::post('/checkout/midtrans/token', [CheckoutController::class, 'getMidtransToken'])->name('checkout.midtrans.token');
    Route::post('/checkout/pay/saldo', [CheckoutController::class, 'payWithSaldo'])->name('checkout.pay.saldo');
    Route::post('/mlm/place-member', [MlmPlacementController::class, 'store'])->name('mlm.place-member');
    Route::post('/dashboard/orders/{order}/payment-status', [DashboardOrderController::class, 'checkPaymentStatus'])
        ->whereNumber('order')
        ->name('dashboard.orders.check-payment-status');
    Route::post('/dashboard/orders/{order}/pay-now', [DashboardOrderController::class, 'createMidtransPayNowToken'])
        ->whereNumber('order')
        ->name('dashboard.orders.pay-now');
    Route::get('/dashboard/orders/{order}/invoice', [DashboardOrderController::class, 'downloadInvoice'])
        ->whereNumber('order')
        ->name('dashboard.orders.invoice');
    Route::post('/dashboard/wallet/topup/token', [DashboardWalletController::class, 'createTopupToken'])
        ->name('dashboard.wallet.topup-token');
    Route::post('/dashboard/wallet/topup/{walletTransaction}/payment-status', [DashboardWalletController::class, 'syncTopupStatus'])
        ->whereNumber('walletTransaction')
        ->name('dashboard.wallet.topup-sync-status');
    Route::post('/dashboard/wallet/withdrawal', [DashboardWalletController::class, 'storeWithdrawal'])
        ->name('dashboard.wallet.withdrawal');
    Route::post('/dashboard/account/profile', [DashboardAccountController::class, 'update'])
        ->name('dashboard.account.update');

    Route::prefix('account/addresses')->name('account.addresses.')->group(function (): void {
        Route::get('/', [CustomerAddressController::class, 'index'])->name('index');
        Route::get('/options/provinces', [CustomerAddressController::class, 'provinceOptions'])->name('options.provinces');
        Route::get('/options/cities', [CustomerAddressController::class, 'cityOptions'])->name('options.cities');
        Route::get('/options/districts', [CustomerAddressController::class, 'districtOptions'])->name('options.districts');
        Route::post('/', [CustomerAddressController::class, 'store'])->name('store');
        Route::put('/{addressId}', [CustomerAddressController::class, 'update'])->whereNumber('addressId')->name('update');
        Route::delete('/{addressId}', [CustomerAddressController::class, 'destroy'])->whereNumber('addressId')->name('destroy');
        Route::post('/{addressId}/default', [CustomerAddressController::class, 'setDefault'])->whereNumber('addressId')->name('set-default');
    });
});

Route::get('/shop', [ShopController::class, 'index'])->name('shop.index');
Route::get('/shop/{slug}', [ShopController::class, 'show'])->name('shop.show');
Route::get('/articles', [ArticleController::class, 'index'])->name('articles.index');
Route::get('/articles/{slug}', [ArticleController::class, 'show'])->name('articles.show');
Route::get('/page/{slug}', [PageController::class, 'show'])->name('pages.show');
