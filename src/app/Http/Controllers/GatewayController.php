<?php

namespace App\Http\Controllers;

use App\Http\GatewayRouteResolver;
use GuzzleHttp\Client;
use Illuminate\Http\JsonResponse;

class GatewayController extends Controller {

	/**
	 * handle request to dockerized micro-service and return its response
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function handle(): JsonResponse {
		$uri = $this->getParsedUri();
		$requestMethod = GatewayRouteResolver::$method;
		$requestBody = GatewayRouteResolver::$requestBody;

		$client = new Client();
		$response = $client->request($requestMethod, $uri, [
			'json' => $requestBody
		]);

		$responseContent = json_decode($response->getBody()->getContents());
		return response()
			->json($responseContent, $response->getStatusCode());
	}

	private function getParsedUri(): string {
		$serviceUrl = GatewayRouteResolver::$microService;
		$port = GatewayRouteResolver::$port;
		return 'http://' . $serviceUrl . ':' . $port . $this->getParsedPathParams();
	}

	private function getParsedPathParams(): string {
		if (!empty(GatewayRouteResolver::$pathAsArray)) {
			return '/' . implode('/', GatewayRouteResolver::$pathAsArray);
		}
		return '';
	}
}
