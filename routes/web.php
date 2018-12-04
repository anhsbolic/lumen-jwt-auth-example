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

$router->get('/', function () use ($router) {
    return $router->app->version();
});

/**
 * PUBLIC
 * JWT NOT REQUIRED
 * version : v1
 */
$router->group(['prefix' => 'v1'], function () use ($router) {
    //auth
    $router->post('/auth/login', 'AuthController@login');
});

/**
 * PRIVATE
 * JWT REQUIRED
 * version : v1
 */
$router->group(['prefix' => 'v1', 'middleware' => 'auth.jwtRefresh'], function () use ($router) {
    //auth
    $router->post('auth/logout', 'AuthController@logout');
    $router->get('auth/me', 'AuthController@me'); 
});