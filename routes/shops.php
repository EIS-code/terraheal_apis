<?php

use Illuminate\Http\Request;
use App\Shop;
/*
|--------------------------------------------------------------------------
| Application News Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->group(['prefix' => 'shops', 'namespace' => 'Shops'], function () use($router) {
  
   $router->post('/signin', 'ShopsController@signIn');
});
