<?php

declare(strict_types=1);

namespace FinGather\Route;

use FinGather\Controller\AssetController;
use FinGather\Controller\AuthenticationController;
use FinGather\Controller\BrokerController;
use FinGather\Controller\GroupController;
use FinGather\Controller\ImportDataController;
use FinGather\Controller\PortfolioController;
use FinGather\Controller\PortfolioDataController;
use FinGather\Controller\TickerDataController;

enum Routes: string
{
	case Health = '/api/health';

	case Assets = '/api/asset';
	case Asset = '/api/asset/{assetId:number}';

	case AuthenticationLogin = '/api/authentication/login';

	case Brokers = '/api/broker';
	case Broker = '/api/broker/{brokerId:number}';

	case Groups = '/api/group';
	case Group = '/api/group/{groupId:number}';
	case GroupOthers = '/api/group/others';

	case ImportData = '/api/import-data';

	case Portfolio = '/api/portfolio';

	case PortfolioData = '/api/portfolio-data';

	case TickerData = '/api/ticker-data/{tickerId:number}';

	public static function getRouteList(): RouteList
	{
		$routeList = new RouteList();

		$routeList->get(self::Health->value, fn (): array => ['status' => 200, 'message' => 'OK']);

		$routeList->post(self::AuthenticationLogin->value, [AuthenticationController::class, 'actionPostLogin']);

		$routeList->get(self::Assets->value, [AssetController::class, 'actionGetAssets']);
		$routeList->get(self::Asset->value, [AssetController::class, 'actionGetAsset']);

		$routeList->get(self::Brokers->value, [BrokerController::class, 'actionGetBrokers']);
		$routeList->get(self::Broker->value, [BrokerController::class, 'actionGetBroker']);
		$routeList->post(self::Brokers->value, [BrokerController::class, 'actionCreateBroker']);
		$routeList->put(self::Broker->value, [BrokerController::class, 'actionUpdateBroker']);
		$routeList->delete(self::Broker->value, [BrokerController::class, 'actionDeleteBroker']);

		$routeList->get(self::Groups->value, [GroupController::class, 'actionGetGroups']);
		$routeList->get(self::Group->value, [GroupController::class, 'actionGetGroup']);
		$routeList->get(self::GroupOthers->value, [GroupController::class, 'actionGetOthersGroup']);
		$routeList->post(self::Groups->value, [GroupController::class, 'actionPostGroup']);
		$routeList->put(self::Group->value, [GroupController::class, 'actionPutGroup']);
		$routeList->delete(self::Group->value, [GroupController::class, 'actionDeleteGroup']);

		$routeList->post(self::ImportData->value, [ImportDataController::class, 'actionImportData']);

		$routeList->get(self::Portfolio->value, [PortfolioController::class, 'actionGetPortfolio']);

		$routeList->get(self::PortfolioData->value, [PortfolioDataController::class, 'actionGetPortfolioData']);

		$routeList->get(self::TickerData->value, [TickerDataController::class, 'actionGetTickerData']);

		return $routeList;
	}
}
