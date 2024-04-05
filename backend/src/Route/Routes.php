<?php

declare(strict_types=1);

namespace FinGather\Route;

enum Routes: string
{
	case Health = '/api/health';

	case AdminUsers = '/api/admin/user';
	case AdminUser = '/api/admin/user/{userId:number}';

	case Assets = '/api/assets/{portfolioId:number}';
	case AssetsWithProperties = '/api/assets/with-properties/{portfolioId:number}';
	case Asset = '/api/asset/{assetId:number}';

	case AuthenticationLogin = '/api/authentication/login';
	case AuthenticationRefreshToken = '/api/authentication/refresh-token';
	case AuthenticationSignUp = '/api/authentication/sign-up';
	case AuthenticationEmailExists = '/api/authentication/email-exists';

	case Brokers = '/api/brokers/{portfolioId:number}';
	case Broker = '/api/broker/{brokerId:number}';

	case Currencies = '/api/currency';

	case CurrentUser = '/api/current-user';

	case DividendDataRange = '/api/dividend-data-range/{portfolioId:number}';

	case EmailVerify = '/api/email-verify';

	case Groups = '/api/groups/{portfolioId:number}';
	case Group = '/api/group/{groupId:number}';
	case GroupOthers = '/api/group/others/{portfolioId:number}';

	case GroupsWithGroupData = '/api/groups-with-group-data/{portfolioId:number}';

	case ImportPrepare = '/api/import/import-prepare/{portfolioId:number}';
	case ImportStart = '/api/import/import-start';

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
}
