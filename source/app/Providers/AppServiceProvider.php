<?php

namespace App\Providers;

use App\Observers\ObserverRegistration;
use App\Repositories\RepositoryRegistration;
use App\Services\ServiceRegistration;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        ObserverRegistration::register($this->app);
        RepositoryRegistration::register($this->app);
        ServiceRegistration::register($this->app);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        switch (config('app.env')) {
            case 'production':
                $this->bootProductionEnvironment();
                break;
            case 'staging':
                $this->bootStagingEnvironment();
                break;
            default:
                $this->bootDevelopmentEnvironment();
        }
    }

    private function bootStagingEnvironment(): void
    {
        // add any boot setting for staging environment
    }

    private function bootDevelopmentEnvironment(): void
    {
        // add any boot setting for development environment
    }

    private function bootProductionEnvironment(): void
    {
        // add any boot setting for production environment
    }
}
