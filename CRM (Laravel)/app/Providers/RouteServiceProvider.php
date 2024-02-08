<?php 

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to your controller routes.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'App\Http\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        //

        parent::boot();
        // $this->configureRateLimiting();
    }

    // protected function configureRateLimiting(): void
    // {
    //     RateLimiter::for('global', function (Request $request) {
    //         return Limit::perMinute(1000);
    //     });
    // }

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map()
    {
        $this->mapApiRoutes();

        $this->mapWebRoutes();

        $this->mapCustomRoutes();

        $this->moduleWebRoutes();

        $this->moduleMobileRoutes();
        $this->mobileRoutes();

        $this->moduleMasterAdminRoutes();
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapWebRoutes()
    {
        Route::middleware('web')
             ->namespace($this->namespace)
             ->group(base_path('routes/web.php'));
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapApiRoutes()
    {
        Route::prefix('api')
             ->middleware('api')
             ->namespace($this->namespace)
             ->group(base_path('routes/api.php'));
    }
    protected function mapCustomRoutes()
    {
        Route::prefix('custom')
             ->namespace($this->namespace)
             ->group(base_path('routes/custom.php'));
    }

    protected function moduleWebRoutes()
    {
        Route::prefix('moduleweb')
             ->namespace($this->namespace)
             ->group(base_path('routes/moduleWeb.php'));
    }


     protected function moduleMobileRoutes()
    {
        Route::prefix('modulemobile')
             ->namespace($this->namespace)
             ->group(base_path('routes/moduleMobile.php'));
    }

    protected function mobileRoutes()
    {
        Route::prefix('mobile')
             ->namespace($this->namespace)
             ->group(base_path('routes/mobile.php'));
    }


    protected function moduleMasterAdminRoutes()
    {
        Route::prefix('modulemasteradmin')
             ->namespace($this->namespace)
             ->group(base_path('routes/moduleMasterAdmin.php'));
    }
}
