<?php

namespace App\Providers;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider as ServiceProvider;

class PaginationServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        
        // Paginator::resolveCurrentPage(function()
        // {
        //     // return $this->app['request']->input('pageNumber');
        //     Paginator::setPageName('pageNumber');
        // });
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
