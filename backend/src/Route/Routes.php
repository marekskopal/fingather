<?php

declare(strict_types=1);

namespace FinGather\Route;

enum Routes: string
{
	case Health = '/api/health';

	case AdminUsers = '/api/admin/user';
	case AdminUser = '/api/admin/user/{userId:number}';

	case ApiKeys = '/api/api-keys/{portfolioId:number}';
	case ApiKey = '/api/api-key/{apiKeyId:number}';

	case Assets = '/api/assets/{portfolioId:number}';
	case AssetsWithProperties = '/api/assets/with-properties/{portfolioId:number}';
	case Asset = '/api/asset/{assetId:number}';

	case AssetDataRange = '/api/asset-data-range/{assetId:number}';

	case AuthenticationLogin = '/api/authentication/login';
	case AuthenticationRefreshToken = '/api/authentication/refresh-token';
	case AuthenticationSignUp = '/api/authentication/sign-up';
	case AuthenticationEmailExists = '/api/authentication/email-exists';
	case AuthenticationGoogleClientId = '/api/authentication/google-client-id';
	case AuthenticationGoogleLogin = '/api/authentication/google-login';

	case Brokers = '/api/brokers/{portfolioId:number}';
	case Broker = '/api/broker/{brokerId:number}';

	case CountriesWithCountryData = '/api/countries-with-country-data/{portfolioId:number}';

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
	case ImportImportFile = '/api/import/import-file/{importFileId:number}';

	case IndustriesWithIndustryData = '/api/industries-with-industry-data/{portfolioId:number}';

	case OnboardingComplete = '/api/onboarding-complete';

	case OverviewYearOverview = '/api/overview/year-overview/{portfolioId:number}';

	case Portfolios = '/api/portfolios';
	case Portfolio = '/api/portfolio/{portfolioId:number}';
	case PortfolioDefault = '/api/portfolio/default';

	case PortfolioData = '/api/portfolio-data/{portfolioId:number}';
	case PortfolioDataRange = '/api/portfolio-data-range/{portfolioId:number}';

	case SectorsWithSectorData = '/api/sectors-with-sector-data/{portfolioId:number}';

	case Tickers = '/api/tickers';
	case TickersMostUsed = '/api/tickers/most-used';

	case TickerFundamental = '/api/ticker-fundamental/{tickerId:number}';

	case TickerData = '/api/ticker-data/{tickerId:number}';

	case Transactions = '/api/transactions/{portfolioId:number}';
	case Transaction = '/api/transaction/{transactionId:number}';
}
