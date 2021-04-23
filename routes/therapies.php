<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Application Therapies related Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->group(['prefix' => 'therapy', 'namespace' => 'Therapy', 'guard' => 'therapy'], function () use($router) {
    $router->group(['prefix' => 'questionnaire'], function () use($router) {
        $router->post('/', 'TherapyController@getQuestions');
        $router->post('save', 'TherapyController@createQuestions');
    });
});
