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

	foreach ($flights as $key => $value) {


		if(empty($totalPriceGroup[$value['fare']])) {
			$totalPriceGroup[$value['fare']] = 0;
		}
		$totalPriceGroup[$value['fare']] = $totalPriceGroup[$value['fare']] + $value['price'];

		if($value['outbound']) $outbound[] = $value;
		if($value['inbound']) $inboundoutbound[] = $value;

		if(!empty($totalGroups[$value['fare']])){
			$totalGroups[$value['fare']] = $totalGroups[$value['fare']] + 1;
		}else {
			$totalGroups[$value['fare']] = 1;
		}


		$groups[$value['fare']][] = [
			'uniqueId' => $key,
			'totalPrice' => $totalPriceGroup[$value['fare']],
			'outbound' => $outbound,
			'inbound' => $inbound,
			'totalGroups' => $totalGroups,
			'totalFlights' => 7, //quantidade total de voos unicos
			'cheapestPrice' => 342.88, //preco do grupo mais barato
			'cheapestGroup' => 4858, //id unido do grupo mais barato
		];
		//dd('key', $key, 'value', $value);
	}

	dd( 'groups', $groups );

	$jsonRetorno = [
		'flights' => $flights, //voos da api
		'groups' => [
			$groups
			// [
			// 	'uniqueId' => 123, //id unico do grupo
			// 	'totalPrice' => 482.00, //preco total do grupo
			// 	'outbound' => [
			// 		['id' => 1, 'voo' => 'ida 1'],
			// 		['id' => 2, 'voo' => 'ida 2'],
			// 	], //voos de ida
			// 	'inbound' => [
			// 		['id' => 1, 'voo' => 'volta 1'],
			// 		['id' => 2, 'voo' => 'volta 2'],
			// 	], // voos de volta
			// 	'totalGroups'=> 2, //quantidade total de grupos
			// 	'totalFlights' => 7, //quantidade total de voos unicos
			// 	'cheapestPrice' => 342.88, //preco do grupo mais barato
			// 	'cheapestGroup' => 4858, //id unido do grupo mais barato
			// ]
		],
	];

	return json_encode($jsonRetorno);


});
