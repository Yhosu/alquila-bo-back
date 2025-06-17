<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        app()->useLangPath(base_path('lang'));
        \Validator::extendImplicit('validate_tenant_url', function($attribute, $value, $parameters, $validator) {
            return request()->tenant_id && !request()->tenant_url ? false : true;
        });
    }
}
