<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/
// $router->get('/key', function() {
//     return str_random(32);
// });
$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->group(['prefix' => 'api'], function () use ($router) {
  $router->get('all_countries',  ['uses' => 'GeoCountriesController@getAllCountries']);


  //users routes
   $router->get('all_users',  ['uses' => 'UsersController@getAllUsers']);
  $router->post('user_signup',  ['uses' => 'UsersController@signup']);
  $router->post('verify_verification_code', ['uses' =>'UsersController@verify_verification_code']);
  $router->post('login', 'UsersController@login');
  $router->post('logout', 'UsersController@logout');
  
 
});