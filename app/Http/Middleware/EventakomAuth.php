<?php

namespace App\Http\Middleware;

use Closure;
use App\User;
use App\Libraries\Helpers;
use Illuminate\Contracts\Auth\Factory as Auth;

class EventakomAuth
{
    /**
     * The authentication guard factory instance.
     *
     * @var \Illuminate\Contracts\Auth\Factory
     */
    protected $auth;

    /**
     * Create a new middleware instance.
     *
     * @param  \Illuminate\Contracts\Auth\Factory  $auth
     * @return void
     */
    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
          // dd($request->header('api_token'));
        if($request->header('api_token'))
             {
                $api_token=$request->header('api_token');
           
            
            if ($api_token) 
            {   $user= User::where('api_token', $api_token)->first();
        //dd($api_token);
             }
              if ($user) 
            {  
                  return $next($request);
              }else{

               // return response('Unauthorized.', 401);
                return Helpers::Get_Response(400,'error',trans('messages.logged'),[],(object)[]);
              }
              
            }  
        return Helpers::Get_Response(400,'error',trans('messages.logged'),[],(object)[]);
    }
}
