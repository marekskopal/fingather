<?php

declare(strict_types=1);

namespace FinGather\Route;

use FinGather\Controller\AuthenticationController;
use FinGather\Controller\PortfolioController;

enum Routes: string
{
	case Health = '/acl/health';

	case AuthenticationLogin = '/api/authentication/login';
	case Portfolio = '/api/portfolio';


	public static function getRouteList(): RouteList
	{
		$routeList = new RouteList();

		$routeList->get(self::Health->value, fn (): array => ['status' => 200, 'message' => 'OK']);

		$routeList->post(self::AuthenticationLogin->value, [AuthenticationController::class, 'actionPostLogin']);

		$routeList->get(self::Portfolio->value, [PortfolioController::class, 'actionGetPortfolio']);

		return $routeList;
	}
}
