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
  //user routes
 $router->get('all_users',  ['uses' => 'UsersController@getAllUsers']);
 $router->post('user_signup',  ['uses' => 'UsersController@signup']);
 $router->post('login', 'UsersController@login');
 $router->post('verify_verification_code', ['uses' =>'UsersController@verify_verification_code']);
 $router->post('resend_verification_code', ['uses' =>'UsersController@resend_verification_code']);
 $router->post('forget_password', ['uses' =>'UsersController@forget_password']);
 $router->post('social_login','UsersController@social_login');
 $router->post('sms','UsersController@sms');
 $router->post('all_interests','UsersController@all_interests');

 $router->get('verify_email',  ['uses' => 'UsersController@verify_email']);


   //countries
$router->get('all_countries',  ['uses' => 'GeoCountriesController@getAllCountries']);
 //cities
$router->get('all_cities',  ['uses' => 'GeoCitiesController@getAllCities']);
$router->get('getcitycountry',  ['uses' => 'GeoCitiesController@getcitycountry']);
$router->get('searchcitycountry',  ['uses' => 'GeoCitiesController@searchcitycountry']);
  //fixed pages
$router->get('fixed_pages', ['uses' =>'UsersController@fixed_pages']);

//data existence
$router->post('mail_existence', ['uses' =>'UsersController@mail_existence']);
$router->post('mobile_existence', ['uses' =>'UsersController@mobile_existence']);

  });


$router->group(['prefix' => 'api',  'middleware' => 'EventakomAuth'], function () use ($router) {

  //users routes
$router->post('logout', 'UsersController@logout');
$router->post('change_lang','UsersController@change_lang');
$router->post('edit_profile',  'UsersController@edit_profile');



  //interests
$router->post('add_interests',['uses' =>'UsersController@add_interests']);
$router->post('add_user_interests', ['uses' =>'UsersController@add_user_interests']);
$router->post('edit_user_interests',['uses'=>'UsersController@edit_user_interests']);
$router->get('user_interests',['uses'=>'UsersController@user_interests']);



//password section
$router->post('change_password','UsersController@change_password');


//Events Section
$router->post("add_event","EventsController@add_event");
$router->post('events[/{type}]',"EventsController@list_events");
$router->post("big_events[/{type}]","EventsController@big_events");
$router->post('current_month_events',"EventsController@current_month_events");
$router->post("age_ranges","EventsController@age_ranges");

});
