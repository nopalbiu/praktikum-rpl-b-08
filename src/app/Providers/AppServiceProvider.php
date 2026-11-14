<?php

namespace App\Providers;

use App\Models\CartItem;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }

    public function boot(): void
    {
        $this->configureDefaults();
        $this->shareCartCount();

        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }
    }

    /** Jumlah barang di keranjang untuk badge di navbar. */
    protected function shareCartCount(): void
    {
        View::composer('components.navbar', function ($view) {
            $user = auth()->user();

            $view->with('cartCount', $user
                ? (int) CartItem::whereHas('cart', fn ($q) => $q->where('id_user', $user->id_user))->sum('qty')
                : 0);
        });
    }

    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}