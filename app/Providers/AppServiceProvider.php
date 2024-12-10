<?php

namespace App\Providers;

use App\Models\User;
use App\Models\Wallet;
use Akaunting\Money\Money;
use Carbon\CarbonImmutable;
use Laravel\Sanctum\Sanctum;
use Illuminate\Http\Request;
use App\Models\PersonalAccessToken;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Date::useClass(CarbonImmutable::class);

        Model::shouldBeStrict(! $this->app->isProduction());

        Relation::enforceMorphMap([
            'user' => User::class,
            'wallet' => Wallet::class,
            'personal_access_token' => PersonalAccessToken::class,
        ]);

        Money::macro('getArray', function (): array {
            return [
                'amount' => $this->getAmount(),
                'currency' => $this->getCurrency()->getCurrency(),
                'formatted' => $this->formatWithoutZeroes(),
            ];
        });

        Request::macro('money', function (string $key, bool $convert = true): Money {
            return money($this->float($key), convert: $convert);
        });
    }
}
