<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$router->group(['prefix' => 'payment', 'namespace' => 'Payment'], function () use($router) {
    
    $router->post('booking', 'StripeController@bookingPayment');
});