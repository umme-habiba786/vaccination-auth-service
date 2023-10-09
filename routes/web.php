<?php

/** @var \Laravel\Lumen\Routing\Router $router */

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

$router->get('/', function () use ($router) {
    echo "<center> Welcome </center>";
});

$router->get('/version', function () use ($router) {
    return $router->app->version();
});

$router->group(['prefix' => 'api'], function () use ($router) {
    $router->post('registration', 'Controller@registration');
    $router->post('login', 'Controller@login');
    $router->post('logout', 'Controller@logout');
    $router->post('refresh', 'Controller@refresh');
    $router->post('user-profile', 'Controller@me');

    $router->post('appointments', 'AppointmentController@saveAppointment');
    $router->get('appointments', 'AppointmentController@newAppointment');
    $router->post('appointments/{appointmentId}', 'AppointmentController@updateAppointment');
    $router->get('user/appointments', 'AppointmentController@appointments');
    $router->get('appointments/{appointmentId}', 'AppointmentController@getAppointment');
});
