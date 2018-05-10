<?php

namespace App\Providers;
use Laravel\Passport\Passport;
use App\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Dusterio\LumenPassport\LumenPassport;
//use App\Http\Controllers\Carbon;
use Carbon\Carbon;
class AuthServiceProvider extends ServiceProvider
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
 // protected $policies = [
 
 //       'App\Model' => 'App\Policies\ModelPolicy',
 
 //   ];
    /**
     * Boot the authentication services for the application.
     *
     * @return void
     */
    public function boot()
    {
        // Here you may define how you wish users to be authenticated for your Lumen
        // application. The callback which receives the incoming request instance
        // should return either a User instance or null. You're free to obtain
        // the User instance via an API token or any other method necessary.

         LumenPassport::routes($this->app);
         Carbon::setLocale('ar');
         // Second parameter is the client Id
// LumenPassport::tokensExpireIn(Carbon::now()->addYears(50), 2);
        $this->app['auth']->viaRequest('api', function ($request) {
             if($request->header('api_token'))
             {
                $api_token=$request->input('api_token');
             }
             else
             {
                 $api_token=$request->input('api_token');
             }
            if ($api_token) 
            {
                return User::where('api_token', $api_token)->first();
            }
        });
    }
}
