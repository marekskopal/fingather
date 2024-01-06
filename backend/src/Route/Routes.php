<?php

declare(strict_types=1);

namespace FinGather\Route;

use FinGather\Controller\Admin\UserController;
use FinGather\Controller\AssetController;
use FinGather\Controller\AuthenticationController;
use FinGather\Controller\BrokerController;
use FinGather\Controller\CurrencyController;
use FinGather\Controller\CurrentUserController;
use FinGather\Controller\GroupController;
use FinGather\Controller\ImportDataController;
use FinGather\Controller\OverviewController;
use FinGather\Controller\PortfolioController;
use FinGather\Controller\PortfolioDataController;
use FinGather\Controller\TickerDataController;
use FinGather\Controller\TransactionController;

enum Routes: string
{
	case Health = '/api/health';

	case AdminUsers = '/api/admin/user';
	case AdminUser = '/api/admin/user/{userId:number}';

	case Assets = '/api/asset';
	case Asset = '/api/asset/{assetId:number}';

	case AuthenticationLogin = '/api/authentication/login';

	case Brokers = '/api/broker';
	case Broker = '/api/broker/{brokerId:number}';

	case Currencies = '/api/currency';

	case CurrentUser = '/api/current-user';

	case Groups = '/api/group';
	case Group = '/api/group/{groupId:number}';
	case GroupOthers = '/api/group/others';

	case ImportData = '/api/import-data';

	case OverviewYearOverview = '/api/overview/year-overview';

	case Portfolio = '/api/portfolio';

	case PortfolioData = '/api/portfolio-data';

	case TickerData = '/api/ticker-data/{tickerId:number}';

	case Transactions = '/api/transaction';
	case Transaction = '/api/transaction/{transactionId:number}';

	public static function getRouteList(): RouteList
	{
		$routeList = new RouteList();

		$routeList->get(self::Health->value, fn (): array => ['status' => 200, 'message' => 'OK']);

		$routeList->get(self::AdminUsers->value, [UserController::class, 'actionGetUsers']);
		$routeList->get(self::AdminUser->value, [UserController::class, 'actionGetUser']);
		$routeList->post(self::AdminUsers->value, [UserController::class, 'actionCreateUser']);
		$routeList->put(self::AdminUser->value, [UserController::class, 'actionUpdateUser']);
		$routeList->delete(self::AdminUser->value, [UserController::class, 'actionDeleteUser']);

		$routeList->post(self::AuthenticationLogin->value, [AuthenticationController::class, 'actionPostLogin']);

		$routeList->get(self::Assets->value, [AssetController::class, 'actionGetAssets']);
		$routeList->get(self::Asset->value, [AssetController::class, 'actionGetAsset']);

		$routeList->get(self::Brokers->value, [BrokerController::class, 'actionGetBrokers']);
		$routeList->get(self::Broker->value, [BrokerController::class, 'actionGetBroker']);
		$routeList->post(self::Brokers->value, [BrokerController::class, 'actionCreateBroker']);
		$routeList->put(self::Broker->value, [BrokerController::class, 'actionUpdateBroker']);
		$routeList->delete(self::Broker->value, [BrokerController::class, 'actionDeleteBroker']);

		$routeList->get(self::Currencies->value, [CurrencyController::class, 'actionGetCurrencies']);

		$routeList->get(self::CurrentUser->value, [CurrentUserController::class, 'actionGetCurrentUser']);

		$routeList->get(self::Groups->value, [GroupController::class, 'actionGetGroups']);
		$routeList->get(self::Group->value, [GroupController::class, 'actionGetGroup']);
		$routeList->get(self::GroupOthers->value, [GroupController::class, 'actionGetOthersGroup']);
		$routeList->post(self::Groups->value, [GroupController::class, 'actionPostGroup']);
		$routeList->put(self::Group->value, [GroupController::class, 'actionPutGroup']);
		$routeList->delete(self::Group->value, [GroupController::class, 'actionDeleteGroup']);

		$routeList->post(self::ImportData->value, [ImportDataController::class, 'actionImportData']);

		$routeList->get(self::OverviewYearOverview->value, [OverviewController::class, 'actionGetYearOverview']);

		$routeList->get(self::Portfolio->value, [PortfolioController::class, 'actionGetPortfolio']);

		$routeList->get(self::PortfolioData->value, [PortfolioDataController::class, 'actionGetPortfolioData']);

		$routeList->get(self::TickerData->value, [TickerDataController::class, 'actionGetTickerData']);

		$routeList->get(self::Transactions->value, [TransactionController::class, 'actionGetTransactions']);
		$routeList->get(self::Transaction->value, [TransactionController::class, 'actionGetTransaction']);
		$routeList->post(self::Transactions->value, [TransactionController::class, 'actionCreateTransaction']);
		$routeList->put(self::Transaction->value, [TransactionController::class, 'actionUpdateTransaction']);
		$routeList->delete(self::Transaction->value, [TransactionController::class, 'actionDeleteTransaction']);

		return $routeList;
	}
}
