<?php

namespace App\Providers;


use App\Models\Accounts;
use Illuminate\Support\Facades\Hash;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use App\Models\User;
use App\Models\Settings;
class AppServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Define your route model bindings, pattern filters, etc.
     */
    public function boot(): void
    {

        if(Accounts::count()===0){
            Accounts::create([
                'name'=>'Owner',
                'user_id'=>1,
               'ontransaction'=>1, 
            ]);
        }
        if (User::count() === 0) {
            User::create([
                'name' => 'admin',
                'category' => 'admin',
                'password' => Hash::make('password'), // رمز عبور
            ]);
        }
        if (Settings::count() === 0) {
            Settings::create([
'language' => 'English',
                'date' => 'English',
                'company_pic'=>'null',
                'company_name'=>'Company Name',
                'description'=>'description',
                'address'=>'address',
                'phone'=>'phone',
                'email'=>'exaple@example.com',            ]);
        }


        $this->configureRateLimiting();

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php')); // روت‌های API از این فایل خوانده می‌شوند

            Route::middleware('web')
                ->group(base_path('routes/web.php')); // روت‌های وب از این فایل خوانده می‌شوند
        });
    }

    /**
     * Configure the rate limiters for the application.
     */
    protected function configureRateLimiting(): void
    {
        \Illuminate\Support\Facades\RateLimiter::for('api', function (\Illuminate\Http\Request $request) {
            return \Illuminate\Cache\RateLimiting\Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }
}
