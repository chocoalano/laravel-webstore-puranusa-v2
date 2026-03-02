<?php

namespace App\Providers;

use App\Repositories\Articles\Contracts\ArticleRepositoryInterface;
use App\Repositories\Articles\EloquentArticleRepository;
use App\Repositories\Auth\Contracts\CustomerAuthRepositoryInterface;
use App\Repositories\Auth\Contracts\CustomerProfileRepositoryInterface;
use App\Repositories\Auth\Contracts\CustomerRegistrationRepositoryInterface;
use App\Repositories\Auth\CustomerAuthRepository;
use App\Repositories\Auth\CustomerProfileRepository;
use App\Repositories\Auth\CustomerRegistrationRepository;
use App\Repositories\Cart\Contracts\CartRepositoryInterface;
use App\Repositories\Cart\EloquentCartRepository;
use App\Repositories\Home\Contracts\HomeRepositoryInterface;
use App\Repositories\Home\EloquentHomeRepository;
use App\Repositories\Pages\Contracts\PageRepositoryInterface;
use App\Repositories\Pages\EloquentPageRepository;
use App\Repositories\Payments\Contracts\MidtransCallbackRepositoryInterface;
use App\Repositories\Payments\EloquentMidtransCallbackRepository;
use App\Repositories\WhatsApp\Contracts\WhatsAppBroadcastRepositoryInterface;
use App\Repositories\WhatsApp\EloquentWhatsAppBroadcastRepository;
use App\Repositories\Wishlist\Contracts\WishlistRepositoryInterface;
use App\Repositories\Wishlist\EloquentWishlistRepository;
use App\Repositories\ZennerAcademy\Contracts\ContentCategoryRepositoryInterface;
use App\Repositories\ZennerAcademy\Contracts\ContentRepositoryInterface;
use App\Repositories\ZennerAcademy\EloquentContentCategoryRepository;
use App\Repositories\ZennerAcademy\EloquentContentRepository;
use Filament\Support\Facades\FilamentIcon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(CustomerAuthRepositoryInterface::class, CustomerAuthRepository::class);
        $this->app->bind(CustomerProfileRepositoryInterface::class, CustomerProfileRepository::class);
        $this->app->bind(CustomerRegistrationRepositoryInterface::class, CustomerRegistrationRepository::class);
        $this->app->bind(
            \App\Repositories\Products\Contracts\ProductRepositoryInterface::class,
            \App\Repositories\Products\EloquentProductRepository::class
        );
        $this->app->bind(CartRepositoryInterface::class, EloquentCartRepository::class);
        $this->app->bind(WishlistRepositoryInterface::class, EloquentWishlistRepository::class);
        $this->app->bind(
            \App\Repositories\Checkout\Contracts\CheckoutRepositoryInterface::class,
            \App\Repositories\Checkout\EloquentCheckoutRepository::class
        );
        $this->app->bind(
            \App\Repositories\Shipping\Contracts\ShippingTargetRepositoryInterface::class,
            \App\Repositories\Shipping\EloquentShippingTargetRepository::class
        );
        $this->app->bind(
            \App\Repositories\Dashboard\Contracts\DashboardRepositoryInterface::class,
            \App\Repositories\Dashboard\EloquentDashboardRepository::class
        );
        $this->app->bind(HomeRepositoryInterface::class, EloquentHomeRepository::class);
        $this->app->bind(
            \App\Repositories\CustomerAddress\Contracts\CustomerAddressRepositoryInterface::class,
            \App\Repositories\CustomerAddress\EloquentCustomerAddressRepository::class
        );
        $this->app->bind(MidtransCallbackRepositoryInterface::class, EloquentMidtransCallbackRepository::class);
        $this->app->bind(ArticleRepositoryInterface::class, EloquentArticleRepository::class);
        $this->app->bind(PageRepositoryInterface::class, EloquentPageRepository::class);
        $this->app->bind(WhatsAppBroadcastRepositoryInterface::class, EloquentWhatsAppBroadcastRepository::class);
        $this->app->bind(ContentCategoryRepositoryInterface::class, EloquentContentCategoryRepository::class);
        $this->app->bind(ContentRepositoryInterface::class, EloquentContentRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::before(function (mixed $user): ?bool {
            if (! is_object($user)) {
                return null;
            }

            $role = strtolower(trim((string) data_get($user, 'role', '')));

            if ($role === 'developer') {
                return true;
            }

            if (! method_exists($user, 'hasRole')) {
                return null;
            }

            try {
                return $user->hasRole('developer') ? true : null;
            } catch (\Throwable) {
                return null;
            }
        });

        FilamentIcon::register([
            // Mengganti icon toggle sidebar saat terbuka
            'panels::sidebar.collapse-button' => 'heroicon-m-bars-3-bottom-right',

            // Mengganti icon toggle sidebar saat tertutup (mobile/collapsed)
            'panels::sidebar.expand-button' => 'heroicon-m-bars-3',
        ]);
    }
}
