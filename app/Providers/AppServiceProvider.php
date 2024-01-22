<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Carbon;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Validator::extend('time_after_now', function ($attribute, $value, $parameters, $validator) {
            // Get the current time
            $currentTime = Carbon::now()->format('H:i');
            // Check if the value is after the current time
            return (strtotime($value) > strtotime($currentTime));
        });
        Validator::replacer('time_after_now', function ($message, $attribute, $rule, $parameters) {
            return str_replace(':attribute', $attribute, 'The :attribute must be a time after the current time.');
        });
    }
}
