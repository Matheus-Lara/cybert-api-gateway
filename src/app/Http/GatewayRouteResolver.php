<?php

namespace App\Http;

use App\Http\Controllers\GatewayController;
use Illuminate\Support\Facades\Route;

class GatewayRouteResolver {

	public static ?string $method;
	public static ?string $microService;
	public static array $requestBody;
	public static array $pathAsArray;
	public static int $port;

	/**
	 * Singleton to resolve current request lifecycle to configured micro-services.
	 * Uses env variables to get micro-services container names and ports and verifies if micro-service is configured.
	 * If micro-service is configured, it registers a route with the same path and method.
	 * If not, it won't register a route and if the route is not hardcoded in api.php or web.php, it will return a 404.
	 *
	 * @return void
	 */
	public static function resolve(): void {
		$microservicesAndPorts = GatewayHostMapping::getMicroservicesAndPorts();
		self::$method = request()->method();

		self::$requestBody = request()->all();

		self::$pathAsArray = explode('/', request()->path());
		array_shift(self::$pathAsArray); // removes "/api" prefix from path

		self::$microService = self::$pathAsArray[0] ?? null;
		array_shift(self::$pathAsArray); // removes microservice name from path

		$microServiceExists = in_array(self::$microService, array_keys($microservicesAndPorts));
		if (!$microServiceExists || self::isInternalRoute()) {
			return;
		}

		self::$port = $microservicesAndPorts[self::$microService];

		// dynamically register routes based in current request path and method (with Route::post() or Route::get() etc.)
		$routeRegisterFunction = Route::class . '::' . strtolower(self::$method);
		call_user_func_array($routeRegisterFunction, [
			request()->path(),
			[GatewayController::class, 'handle']
		]);
	}

	private static function isInternalRoute(): bool {
		return (self::$method == 'GET' && self::$microService == '') || self::$microService == 'login';
	}
}