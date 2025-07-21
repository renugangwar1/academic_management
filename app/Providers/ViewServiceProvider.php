<?php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\AcademicSession;

class ViewServiceProvider extends ServiceProvider
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
        View::composer('*', function ($view) {
            $regularSession = AcademicSession::where('active', 1)
                ->where('type', 'regular')
                ->latest('id')
                ->first();

            $diplomaSession = AcademicSession::where('active', 1)
                ->where('type', 'diploma')
                ->latest('id')
                ->first();

            $view->with(compact('regularSession', 'diplomaSession'));
        });
    }
}

