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
	// Cache::flush();
	if (!Cache::has('flights')) {
		$url = 'http://prova.123milhas.net/api/flights';
		$client = new Client();
		$response = $client->get($url);
		$body = $response->getBody()->getContents();
		$flights = Cache::put('flights', json_decode($body,true) );
	}else {
		$flights = Cache::get('flights');
	}

	$groups = [];
	$fareSeparation = [];
	foreach ($flights as $key => $value) {

		$fareSeparation[$value['fare']]['uniqueId'] = uniqid();

		if(empty( $fareSeparation[$value['fare']]['totalPrice'] )) {
			$fareSeparation[$value['fare']]['totalPrice'] = 0;
		}
		$fareSeparation[$value['fare']]['totalPrice'] = $fareSeparation[$value['fare']]['totalPrice'] + $value['price'];

		if($value['outbound']){
			$fareSeparation[$value['fare']]['outbound'][] = $value;
		}

		if($value['inbound']){
			$fareSeparation[$value['fare']]['inbound'][] = $value;
		}

	}

	$totalGroups = count($fareSeparation);
	$cheapestPrice = 0;
	$cheapestGroup = '';

	foreach ($fareSeparation as $key => $value) {
		$groups[] = $value;
		if(empty($cheapestPrice)){
			$cheapestPrice = $value['totalPrice'];
			$cheapestGroup = $value['uniqueId'];
		}

		if( $value['totalPrice'] < $cheapestPrice ) {
			$cheapestPrice = $value['totalPrice'];
			$cheapestGroup = $value['uniqueId'];
		}

 	}

	$jsonRetorno = [
		'flights' => $flights, //voos da api
		'groups' => $groups ,
		'totalGroups'=> $totalGroups, //quantidade total de grupos
		'totalFlights' => count($flights), //quantidade total de voos unicos
		'cheapestPrice' => $cheapestPrice, //preco do grupo mais barato
		'cheapestGroup' => $cheapestGroup, //id unido do grupo mais barato
	];

	return json_encode($jsonRetorno);


});
