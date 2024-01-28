<?php

declare(strict_types=1);

namespace FinGather\Route;

use FinGather\Controller\Admin\UserController;
use FinGather\Controller\AssetController;
use FinGather\Controller\AuthenticationController;
use FinGather\Controller\BrokerController;
use FinGather\Controller\CurrencyController;
use FinGather\Controller\CurrentUserController;
use FinGather\Controller\EmailVerifyController;
use FinGather\Controller\GroupController;
use FinGather\Controller\GroupWithGroupDataController;
use FinGather\Controller\ImportDataController;
use FinGather\Controller\OverviewController;
use FinGather\Controller\PortfolioController;
use FinGather\Controller\PortfolioDataController;
use FinGather\Controller\TickerController;
use FinGather\Controller\TickerDataController;
use FinGather\Controller\TransactionController;

enum Routes: string
{
	case Health = '/api/health';

	case AdminUsers = '/api/admin/user';
	case AdminUser = '/api/admin/user/{userId:number}';

	case Assets = '/api/assets/{portfolioId:number}';
	case AssetsOpened = '/api/assets/opened/{portfolioId:number}';
	case AssetsClosed = '/api/assets/closed/{portfolioId:number}';
	case AssetsWatched = '/api/assets/watched/{portfolioId:number}';
	case Asset = '/api/asset/{assetId:number}';

	case AuthenticationLogin = '/api/authentication/login';
	case AuthenticationRefreshToken = '/api/authentication/refresh-token';
	case AuthenticationSignUp = '/api/authentication/sign-up';
	case AuthenticationEmailExists = '/api/authentication/email-exists';

	case Brokers = '/api/brokers/{portfolioId:number}';
	case Broker = '/api/broker/{brokerId:number}';

	case Currencies = '/api/currency';

	case CurrentUser = '/api/current-user';

	case EmailVerify = '/api/email-verify';

	case Groups = '/api/groups/{portfolioId:number}';
	case Group = '/api/group/{groupId:number}';
	case GroupOthers = '/api/group/others/{portfolioId:number}';

	case GroupsWithGroupData = '/api/groups-with-group-data/{portfolioId:number}';

	case ImportData = '/api/import-data';

	case OverviewYearOverview = '/api/overview/year-overview/{portfolioId:number}';

	case Portfolios = '/api/portfolios';
	case Portfolio = '/api/portfolio/{portfolioId:number}';
	case PortfolioDefault = '/api/portfolio/default';

	case PortfolioData = '/api/portfolio-data/{portfolioId:number}';
	case PortfolioDataRange = '/api/portfolio-data-range/{portfolioId:number}';

	case Tickers = '/api/ticker';

	case TickerData = '/api/ticker-data/{tickerId:number}';

	case Transactions = '/api/transactions/{portfolioId:number}';
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
		$routeList->post(self::AuthenticationRefreshToken->value, [AuthenticationController::class, 'actionPostRefreshToken']);
		$routeList->post(self::AuthenticationSignUp->value, [AuthenticationController::class, 'actionPostSignUp']);
		$routeList->post(self::AuthenticationEmailExists->value, [AuthenticationController::class, 'actionPostEmailExists']);

		$routeList->get(self::Assets->value, [AssetController::class, 'actionGetAssets']);
		$routeList->get(self::AssetsOpened->value, [AssetController::class, 'actionGetAssetsOpened']);
		$routeList->get(self::AssetsClosed->value, [AssetController::class, 'actionGetAssetsClosed']);
		$routeList->get(self::AssetsWatched->value, [AssetController::class, 'actionGetAssetsWatched']);
		$routeList->get(self::Asset->value, [AssetController::class, 'actionGetAsset']);
		$routeList->post(self::Assets->value, [AssetController::class, 'actionCreateAsset']);

		$routeList->get(self::Brokers->value, [BrokerController::class, 'actionGetBrokers']);
		$routeList->get(self::Broker->value, [BrokerController::class, 'actionGetBroker']);
		$routeList->post(self::Brokers->value, [BrokerController::class, 'actionCreateBroker']);
		$routeList->put(self::Broker->value, [BrokerController::class, 'actionUpdateBroker']);
		$routeList->delete(self::Broker->value, [BrokerController::class, 'actionDeleteBroker']);

		$routeList->get(self::Currencies->value, [CurrencyController::class, 'actionGetCurrencies']);

		$routeList->get(self::CurrentUser->value, [CurrentUserController::class, 'actionGetCurrentUser']);

		$routeList->post(self::EmailVerify->value, [EmailVerifyController::class, 'actionPostEmailVerify']);

		$routeList->get(self::Groups->value, [GroupController::class, 'actionGetGroups']);
		$routeList->get(self::Group->value, [GroupController::class, 'actionGetGroup']);
		$routeList->get(self::GroupOthers->value, [GroupController::class, 'actionGetOthersGroup']);
		$routeList->post(self::Groups->value, [GroupController::class, 'actionPostGroup']);
		$routeList->put(self::Group->value, [GroupController::class, 'actionPutGroup']);
		$routeList->delete(self::Group->value, [GroupController::class, 'actionDeleteGroup']);

		$routeList->get(self::GroupsWithGroupData->value, [GroupWithGroupDataController::class, 'actionGetGroupsWithGroupData']);

		$routeList->post(self::ImportData->value, [ImportDataController::class, 'actionImportData']);

		$routeList->get(self::OverviewYearOverview->value, [OverviewController::class, 'actionGetYearOverview']);

		$routeList->get(self::Portfolios->value, [PortfolioController::class, 'actionGetPortfolios']);
		$routeList->get(self::Portfolio->value, [PortfolioController::class, 'actionGetPortfolio']);
		$routeList->get(self::PortfolioDefault->value, [PortfolioController::class, 'actionGetDefaultPortfolio']);
		$routeList->post(self::Portfolios->value, [PortfolioController::class, 'actionPostPortfolio']);
		$routeList->put(self::Portfolio->value, [PortfolioController::class, 'actionPutPortfolio']);
		$routeList->delete(self::Portfolio->value, [PortfolioController::class, 'actionDeletePortfolio']);

		$routeList->get(self::PortfolioData->value, [PortfolioDataController::class, 'actionGetPortfolioData']);
		$routeList->get(self::PortfolioDataRange->value, [PortfolioDataController::class, 'actionGetPortfolioDataRange']);

		$routeList->get(self::Tickers->value, [TickerController::class, 'actionGetTickers']);

		$routeList->get(self::TickerData->value, [TickerDataController::class, 'actionGetTickerData']);

		$routeList->get(self::Transactions->value, [TransactionController::class, 'actionGetTransactions']);
		$routeList->get(self::Transaction->value, [TransactionController::class, 'actionGetTransaction']);
		$routeList->post(self::Transactions->value, [TransactionController::class, 'actionCreateTransaction']);
		$routeList->put(self::Transaction->value, [TransactionController::class, 'actionUpdateTransaction']);
		$routeList->delete(self::Transaction->value, [TransactionController::class, 'actionDeleteTransaction']);

		return $routeList;
	}
}
