<?php

/** @var \Laravel\Lumen\Routing\Router $router */

use Illuminate\Support\Facades\Http;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;

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


	if (!Cache::has('flights')) {
		$url = 'http://prova.123milhas.net/api/flights';
		$client = new Client();
		$response = $client->get($url);
		$body = $response->getBody();
		$flights = Cache::put('flights', $body);
	}else {
		$flights = Cache::get('flights');
	}



});
