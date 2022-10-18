<?php

namespace App\Http;

class GatewayHostMapping {
	public static function getMicroservicesAndPorts(): array {
		return [
			env('HOST_MACHINE_PRODUCTS') => env('HOST_MACHINE_PRODUCTS_PORT'),
			env('HOST_MACHINE_STOCKS') => env('HOST_MACHINE_STOCKS_PORT')
		];
	}
}