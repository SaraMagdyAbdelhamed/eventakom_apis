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
  //countries
$router->get('all_countries',  ['uses' => 'GeoCountriesController@getAllCountries']);
 //cities
$router->get('all_cities',  ['uses' => 'GeoCitiesController@getAllCities']);
$router->get('getcitycountry',  ['uses' => 'GeoCitiesController@getcitycountry']);
$router->get('searchcitycountry',  ['uses' => 'GeoCitiesController@searchcitycountry']);

  //users routes
  $router->get('all_users',  ['uses' => 'UsersController@getAllUsers']);
  $router->post('user_signup',  ['uses' => 'UsersController@signup']);
  $router->post('verify_verification_code', ['uses' =>'UsersController@verify_verification_code']);
  $router->post('resend_verification_code', ['uses' =>'UsersController@resend_verification_code']);
  $router->post('login', 'UsersController@login');
  $router->post('logout', 'UsersController@logout');
  $router->put('change_language', ['uses' =>'UsersController@change_language']);

  $router->post('mail_existence', ['uses' =>'UsersController@mail_existence']);
  $router->post('mobile_existence', ['uses' =>'UsersController@mobile_existence']);

  //fixed pages
  $router->get('fixed_pages', ['uses' =>'UsersController@fixed_pages']);

  //interests
  $router->post('add_interests', ['uses' =>'UsersController@add_interests']);
  

});
