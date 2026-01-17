<?php

declare(strict_types=1);

namespace Migrations;

use MarekSkopal\ORM\Enum\Type;
use MarekSkopal\ORM\Migrations\Migration\Migration;

final class InitMigration extends Migration
{
	public function up(): void
	{
		$this->table('users')
			->addColumn('id', Type::Int, autoincrement: true, primary: true)
			->addColumn('email', Type::String)
			->addColumn('password', Type::String)
			->addColumn('name', Type::String)
			->addColumn('role', Type::Enum, enum: ['User', 'Admin'], default: 'User')
			->addColumn('is_email_verified', Type::Boolean, default: false)
			->addColumn('is_onboarding_completed', Type::Boolean, default: false)
			->create();

		$this->table('sectors')
			->addColumn('id', Type::Int, autoincrement: true, primary: true)
			->addColumn('name', Type::String)
			->addColumn('is_others', Type::Boolean, default: false)
			->create();

		$this->table('countries')
			->addColumn('id', Type::Int, autoincrement: true, primary: true)
			->addColumn('iso_code', Type::String, size: 2)
			->addColumn('iso_code3', Type::String, size: 3)
			->addColumn('name', Type::String, size: 50)
			->addColumn('is_others', Type::Boolean, default: false)
			->create();

		$this->table('industries')
			->addColumn('id', Type::Int, autoincrement: true, primary: true)
			->addColumn('name', Type::String)
			->addColumn('is_others', Type::Boolean, default: false)
			->create();

		$this->table('currencies')
			->addColumn('id', Type::Int, autoincrement: true, primary: true)
			->addColumn('code', Type::String, size: 3)
			->addColumn('name', Type::String, size: 50)
			->addColumn('symbol', Type::String, size: 5)
			->addColumn('multiply_currency_id', Type::Int, nullable: true, size: 11)
			->addColumn('multiplier', Type::Int, default: 1)
			->addColumn('is_selectable', Type::Boolean, default: true)
			->addIndex(['multiply_currency_id'], 'currencies_multiply_currency_id_index', false)
			->addForeignKey('multiply_currency_id', 'currencies', 'id', 'currencies_multiply_currency_id_currencies_id_fk')
			->create();

		$this->table('markets')
			->addColumn('id', Type::Int, autoincrement: true, primary: true)
			->addColumn('type', Type::Enum, enum: ['Stock', 'Crypto'], default: 'Stock')
			->addColumn('name', Type::String)
			->addColumn('acronym', Type::String, size: 20)
			->addColumn('mic', Type::String, size: 5)
			->addColumn('exchange_code', Type::String, size: 2)
			->addColumn('country', Type::String, size: 2)
			->addColumn('city', Type::String)
			->addColumn('timezone', Type::String)
			->addColumn('currency_id', Type::Int, size: 11)
			->addIndex(['currency_id'], 'markets_currency_id_index', false)
			->addForeignKey('currency_id', 'currencies', 'id', 'markets_currency_id_currencies_id_fk')
			->create();

		$this->table('portfolios')
			->addColumn('id', Type::Int, autoincrement: true, primary: true)
			->addColumn('user_id', Type::Int, size: 11)
			->addColumn('currency_id', Type::Int, size: 11)
			->addColumn('name', Type::String)
			->addColumn('is_default', Type::Boolean)
			->addIndex(['user_id'], 'portfolios_user_id_index', false)
			->addIndex(['currency_id'], 'portfolios_currency_id_index', false)
			->addForeignKey('user_id', 'users', 'id', 'portfolios_user_id_users_id_fk')
			->addForeignKey('currency_id', 'currencies', 'id', 'portfolios_currency_id_currencies_id_fk')
			->create();

		$this->table('email_verifies')
			->addColumn('id', Type::Int, autoincrement: true, primary: true)
			->addColumn('user_id', Type::Int, size: 11)
			->addColumn('token', Type::Uuid)
			->addIndex(['user_id'], 'email_verifies_user_id_index', false)
			->addForeignKey('user_id', 'users', 'id', 'email_verifies_user_id_users_id_fk')
			->create();

		$this->table('brokers')
			->addColumn('id', Type::Int, autoincrement: true, primary: true)
			->addColumn('user_id', Type::Int, size: 11)
			->addColumn('portfolio_id', Type::Int, size: 11)
			->addColumn('name', Type::String)
			->addColumn(
				'import_type',
				Type::Enum,
				enum: ['Trading212', 'InteractiveBrokers', 'Xtb', 'Etoro', 'Revolut', 'Anycoin', 'Degiro', 'Portu', 'Coinbase', 'Binance', 'FioBanka'],
			)
			->addIndex(['user_id'], 'brokers_user_id_index', false)
			->addIndex(['portfolio_id'], 'brokers_portfolio_id_index', false)
			->addForeignKey('user_id', 'users', 'id', 'brokers_user_id_users_id_fk')
			->addForeignKey('portfolio_id', 'portfolios', 'id', 'brokers_portfolio_id_portfolios_id_fk')
			->create();

		$this->table('exchange_rates')
			->addColumn('id', Type::Int, autoincrement: true, primary: true)
			->addColumn('currency_id', Type::Int, size: 11)
			->addColumn('date', Type::Timestamp)
			->addColumn('rate', Type::Decimal, precision: 9, scale: 4)
			->addIndex(['currency_id'], 'exchange_rates_currency_id_index', false)
			->addForeignKey('currency_id', 'currencies', 'id', 'exchange_rates_currency_id_currencies_id_fk')
			->create();

		$this->table('tickers')
			->addColumn('id', Type::Int, autoincrement: true, primary: true)
			->addColumn('ticker', Type::String, size: 20)
			->addColumn('name', Type::String)
			->addColumn('market_id', Type::Int, size: 11)
			->addColumn('currency_id', Type::Int, size: 11)
			->addColumn('type', Type::Enum, enum: ['Stock', 'Etf', 'Crypto'], default: 'Stock')
			->addColumn('isin', Type::String, nullable: true)
			->addColumn('logo', Type::String, nullable: true)
			->addColumn('sector_id', Type::Int, size: 11)
			->addColumn('industry_id', Type::Int, size: 11)
			->addColumn('website', Type::String, nullable: true)
			->addColumn('description', Type::Text, nullable: true)
			->addColumn('country_id', Type::Int, size: 11)
			->addIndex(['market_id'], 'tickers_market_id_index', false)
			->addIndex(['currency_id'], 'tickers_currency_id_index', false)
			->addIndex(['sector_id'], 'tickers_sector_id_index', false)
			->addIndex(['industry_id'], 'tickers_industry_id_index', false)
			->addIndex(['country_id'], 'tickers_country_id_index', false)
			->addForeignKey('market_id', 'markets', 'id', 'tickers_market_id_markets_id_fk')
			->addForeignKey('currency_id', 'currencies', 'id', 'tickers_currency_id_currencies_id_fk')
			->addForeignKey('sector_id', 'sectors', 'id', 'tickers_sector_id_sectors_id_fk')
			->addForeignKey('industry_id', 'industries', 'id', 'tickers_industry_id_industries_id_fk')
			->addForeignKey('country_id', 'countries', 'id', 'tickers_country_id_countries_id_fk')
			->create();

		$this->table('api_keys')
			->addColumn('id', Type::Int, autoincrement: true, primary: true)
			->addColumn('user_id', Type::Int, size: 11)
			->addColumn('portfolio_id', Type::Int, size: 11)
			->addColumn('type', Type::Enum, enum: ['Trading212'])
			->addColumn('api_key', Type::String)
			->addIndex(['user_id'], 'api_keys_user_id_index', false)
			->addIndex(['portfolio_id'], 'api_keys_portfolio_id_index', false)
			->addForeignKey('user_id', 'users', 'id', 'api_keys_user_id_users_id_fk')
			->addForeignKey('portfolio_id', 'portfolios', 'id', 'api_keys_portfolio_id_portfolios_id_fk')
			->create();

		$this->table('imports')
			->addColumn('id', Type::Int, autoincrement: true, primary: true)
			->addColumn('user_id', Type::Int, size: 11)
			->addColumn('portfolio_id', Type::Int, size: 11)
			->addColumn('created', Type::Timestamp)
			->addColumn('uuid', Type::Uuid)
			->addIndex(['user_id'], 'imports_user_id_index', false)
			->addIndex(['portfolio_id'], 'imports_portfolio_id_index', false)
			->addForeignKey('user_id', 'users', 'id', 'imports_user_id_users_id_fk')
			->addForeignKey('portfolio_id', 'portfolios', 'id', 'imports_portfolio_id_portfolios_id_fk')
			->create();

		$this->table('groups')
			->addColumn('id', Type::Int, autoincrement: true, primary: true)
			->addColumn('user_id', Type::Int, size: 11)
			->addColumn('portfolio_id', Type::Int, size: 11)
			->addColumn('name', Type::String)
			->addColumn('color', Type::String, size: 7)
			->addColumn('is_others', Type::Boolean, default: false)
			->addIndex(['user_id'], 'groups_user_id_index', false)
			->addIndex(['portfolio_id'], 'groups_portfolio_id_index', false)
			->addForeignKey('user_id', 'users', 'id', 'groups_user_id_users_id_fk')
			->addForeignKey('portfolio_id', 'portfolios', 'id', 'groups_portfolio_id_portfolios_id_fk')
			->create();

		$this->table('assets')
			->addColumn('id', Type::Int, autoincrement: true, primary: true)
			->addColumn('user_id', Type::Int, size: 11)
			->addColumn('portfolio_id', Type::Int, size: 11)
			->addColumn('ticker_id', Type::Int, size: 11)
			->addColumn('group_id', Type::Int, size: 11)
			->addIndex(['user_id'], 'assets_user_id_index', false)
			->addIndex(['portfolio_id'], 'assets_portfolio_id_index', false)
			->addIndex(['ticker_id'], 'assets_ticker_id_index', false)
			->addIndex(['group_id'], 'assets_group_id_index', false)
			->addForeignKey('user_id', 'users', 'id', 'assets_user_id_users_id_fk')
			->addForeignKey('portfolio_id', 'portfolios', 'id', 'assets_portfolio_id_portfolios_id_fk')
			->addForeignKey('ticker_id', 'tickers', 'id', 'assets_ticker_id_tickers_id_fk')
			->addForeignKey('group_id', 'groups', 'id', 'assets_group_id_groups_id_fk')
			->create();

		$this->table('splits')
			->addColumn('id', Type::Int, autoincrement: true, primary: true)
			->addColumn('ticker_id', Type::Int)
			->addColumn('date', Type::Timestamp)
			->addColumn('factor', Type::Decimal, precision: 8, scale: 4)
			->addIndex(['ticker_id'], 'splits_ticker_id_index', false)
			->addForeignKey('ticker_id', 'tickers', 'id', 'splits_ticker_id_tickers_id_fk')
			->create();

		$this->table('import_mappings')
			->addColumn('id', Type::Int, autoincrement: true, primary: true)
			->addColumn('user_id', Type::Int, size: 11)
			->addColumn('portfolio_id', Type::Int, size: 11)
			->addColumn('broker_id', Type::Int, size: 11)
			->addColumn('import_ticker', Type::String)
			->addColumn('ticker_id', Type::Int, size: 11)
			->addIndex(['user_id'], 'import_mappings_user_id_index', false)
			->addIndex(['portfolio_id'], 'import_mappings_portfolio_id_index', false)
			->addIndex(['broker_id'], 'import_mappings_broker_id_index', false)
			->addIndex(['ticker_id'], 'import_mappings_ticker_id_index', false)
			->addForeignKey('user_id', 'users', 'id', 'import_mappings_user_id_users_id_fk')
			->addForeignKey('portfolio_id', 'portfolios', 'id', 'import_mappings_portfolio_id_portfolios_id_fk')
			->addForeignKey('broker_id', 'brokers', 'id', 'import_mappings_broker_id_brokers_id_fk')
			->addForeignKey('ticker_id', 'tickers', 'id', 'import_mappings_ticker_id_tickers_id_fk')
			->create();

		$this->table('ticker_fundamentals')
			->addColumn('id', Type::Int, autoincrement: true, primary: true)
			->addColumn('ticker_id', Type::Int, size: 11)
			->addColumn('market_capitalization', Type::BigInt, nullable: true)
			->addColumn('enterprise_value', Type::BigInt, nullable: true)
			->addColumn('trailing_pe', Type::Float, nullable: true)
			->addColumn('forward_pe', Type::Float, nullable: true)
			->addColumn('peg_ratio', Type::Float, nullable: true)
			->addColumn('price_to_sales_ttm', Type::Float, nullable: true)
			->addColumn('price_to_book_mrq', Type::Float, nullable: true)
			->addColumn('enterprise_to_revenue', Type::Float, nullable: true)
			->addColumn('enterprise_to_ebitda', Type::Float, nullable: true)
			->addColumn('fiscal_year_ends', Type::Date, nullable: true)
			->addColumn('most_recent_quarter', Type::Date, nullable: true)
			->addColumn('profit_margin', Type::Float, nullable: true)
			->addColumn('operating_margin', Type::Float, nullable: true)
			->addColumn('return_on_assets_ttm', Type::Float, nullable: true)
			->addColumn('return_on_equity_ttm', Type::Float, nullable: true)
			->addColumn('revenue_ttm', Type::BigInt, nullable: true)
			->addColumn('revenue_per_share_ttm', Type::Float, nullable: true)
			->addColumn('quarterly_revenue_growth', Type::Float, nullable: true)
			->addColumn('gross_profit_ttm', Type::BigInt, nullable: true)
			->addColumn('ebitda', Type::BigInt, nullable: true)
			->addColumn('net_income_to_common_ttm', Type::BigInt, nullable: true)
			->addColumn('diluted_eps_ttm', Type::Float, nullable: true)
			->addColumn('quarterly_earnings_growth_yoy', Type::Float, nullable: true)
			->addColumn('total_cash_mrq', Type::BigInt, nullable: true)
			->addColumn('total_cash_per_share_mrq', Type::Float, nullable: true)
			->addColumn('total_debt_mrq', Type::BigInt, nullable: true)
			->addColumn('total_debt_to_equity_mrq', Type::Float, nullable: true)
			->addColumn('current_ratio_mrq', Type::Float, nullable: true)
			->addColumn('book_value_per_share_mrq', Type::Float, nullable: true)
			->addColumn('operating_cash_flow_ttm', Type::BigInt, nullable: true)
			->addColumn('levered_free_cash_flow_ttm', Type::BigInt, nullable: true)
			->addColumn('shares_outstanding', Type::BigInt, nullable: true)
			->addColumn('float_shares', Type::BigInt, nullable: true)
			->addColumn('avg10_volume', Type::BigInt, nullable: true)
			->addColumn('avg90_volume', Type::BigInt, nullable: true)
			->addColumn('shares_short', Type::BigInt, nullable: true)
			->addColumn('short_ratio', Type::Float, nullable: true)
			->addColumn('short_percent_of_shares_outstanding', Type::Float, nullable: true)
			->addColumn('percent_held_by_insiders', Type::Float, nullable: true)
			->addColumn('percent_held_by_institutions', Type::Float, nullable: true)
			->addColumn('fifty_two_week_low', Type::Float, nullable: true)
			->addColumn('fifty_two_week_high', Type::Float, nullable: true)
			->addColumn('fifty_two_week_change', Type::Float, nullable: true)
			->addColumn('beta', Type::Float, nullable: true)
			->addColumn('day50_ma', Type::Float, nullable: true)
			->addColumn('day200_ma', Type::Float, nullable: true)
			->addColumn('forward_annual_dividend_rate', Type::Float, nullable: true)
			->addColumn('forward_annual_dividend_yield', Type::Float, nullable: true)
			->addColumn('trailing_annual_dividend_rate', Type::Float, nullable: true)
			->addColumn('trailing_annual_dividend_yield', Type::Float, nullable: true)
			->addColumn('five_year_average_dividend_yield', Type::Float, nullable: true)
			->addColumn('payout_ratio', Type::Float, nullable: true)
			->addColumn('dividend_date', Type::Date, nullable: true)
			->addColumn('ex_dividend_date', Type::Date, nullable: true)
			->addIndex(['ticker_id'], 'ticker_fundamentals_ticker_id_index', false)
			->addForeignKey('ticker_id', 'tickers', 'id', 'ticker_fundamentals_ticker_id_tickers_id_fk')
			->create();

		$this->table('ticker_datas')
			->addColumn('id', Type::Int, autoincrement: true, primary: true)
			->addColumn('ticker_id', Type::Int, size: 11)
			->addColumn('date', Type::Timestamp)
			->addColumn('open', Type::Decimal, precision: 20, scale: 10)
			->addColumn('close', Type::Decimal, precision: 20, scale: 10)
			->addColumn('high', Type::Decimal, precision: 20, scale: 10)
			->addColumn('low', Type::Decimal, precision: 20, scale: 10)
			->addColumn('volume', Type::Decimal, precision: 22, scale: 10)
			->addIndex(['ticker_id'], 'ticker_datas_ticker_id_index', false)
			->addForeignKey('ticker_id', 'tickers', 'id', 'ticker_datas_ticker_id_tickers_id_fk')
			->create();

		$this->table('import_files')
			->addColumn('id', Type::Int, autoincrement: true, primary: true)
			->addColumn('import_id', Type::Int, size: 11)
			->addColumn('created', Type::Timestamp)
			->addColumn('file_name', Type::String)
			->addColumn('contents', Type::LongBlob)
			->addIndex(['import_id'], 'import_files_import_id_index', false)
			->addForeignKey('import_id', 'imports', 'id', 'import_files_import_id_imports_id_fk')
			->create();

		$this->table('api_imports')
			->addColumn('id', Type::Int, autoincrement: true, primary: true)
			->addColumn('user_id', Type::Int, size: 11)
			->addColumn('portfolio_id', Type::Int, size: 11)
			->addColumn('api_key_id', Type::Int, size: 11)
			->addColumn('status', Type::Enum, enum: ['New', 'Waiting', 'InProgress', 'Finished', 'Error'])
			->addColumn('date_from', Type::Timestamp)
			->addColumn('date_to', Type::Timestamp)
			->addColumn('report_id', Type::Int, nullable: true)
			->addColumn('error', Type::Text, nullable: true)
			->addIndex(['user_id'], 'api_imports_user_id_index', false)
			->addIndex(['portfolio_id'], 'api_imports_portfolio_id_index', false)
			->addIndex(['api_key_id'], 'api_imports_api_key_id_index', false)
			->addForeignKey('user_id', 'users', 'id', 'api_imports_user_id_users_id_fk')
			->addForeignKey('portfolio_id', 'portfolios', 'id', 'api_imports_portfolio_id_portfolios_id_fk')
			->addForeignKey('api_key_id', 'api_keys', 'id', 'api_imports_api_key_id_api_keys_id_fk')
			->create();

		$this->table('transactions')
			->addColumn('id', Type::Int, autoincrement: true, primary: true)
			->addColumn('user_id', Type::Int, size: 11)
			->addColumn('portfolio_id', Type::Int, size: 11)
			->addColumn('asset_id', Type::Int, size: 11)
			->addColumn('broker_id', Type::Int, nullable: true)
			->addColumn('action_type', Type::Enum, enum: ['Undefined', 'Buy', 'Sell', 'Dividend', 'Tax', 'Fee', 'DividendTax'])
			->addColumn('action_created', Type::Timestamp)
			->addColumn('create_type', Type::Enum, enum: ['Manual', 'Import'], default: 'Manual')
			->addColumn('created', Type::Timestamp)
			->addColumn('modified', Type::Timestamp)
			->addColumn('units', Type::Decimal, precision: 18, scale: 8)
			->addColumn('price', Type::Decimal, precision: 9, scale: 2)
			->addColumn('currency_id', Type::Int, size: 11)
			->addColumn('price_ticker_currency', Type::Decimal, precision: 9, scale: 2)
			->addColumn('price_default_currency', Type::Decimal, precision: 9, scale: 2)
			->addColumn('tax', Type::Decimal, precision: 9, scale: 2)
			->addColumn('tax_currency_id', Type::Int, size: 11)
			->addColumn('tax_ticker_currency', Type::Decimal, precision: 9, scale: 2)
			->addColumn('tax_default_currency', Type::Decimal, precision: 9, scale: 2)
			->addColumn('fee', Type::Decimal, precision: 9, scale: 2)
			->addColumn('fee_currency_id', Type::Int, size: 11)
			->addColumn('fee_ticker_currency', Type::Decimal, precision: 9, scale: 2)
			->addColumn('fee_default_currency', Type::Decimal, precision: 9, scale: 2)
			->addColumn('notes', Type::TinyText, nullable: true)
			->addColumn('import_identifier', Type::String, nullable: true)
			->addIndex(['user_id'], 'transactions_user_id_index', false)
			->addIndex(['portfolio_id'], 'transactions_portfolio_id_index', false)
			->addIndex(['asset_id'], 'transactions_asset_id_index', false)
			->addIndex(['broker_id'], 'transactions_broker_id_index', false)
			->addIndex(['currency_id'], 'transactions_currency_id_index', false)
			->addIndex(['tax_currency_id'], 'transactions_tax_currency_id_index', false)
			->addIndex(['fee_currency_id'], 'transactions_fee_currency_id_index', false)
			->addForeignKey('user_id', 'users', 'id', 'transactions_user_id_users_id_fk')
			->addForeignKey('portfolio_id', 'portfolios', 'id', 'transactions_portfolio_id_portfolios_id_fk')
			->addForeignKey('asset_id', 'assets', 'id', 'transactions_asset_id_assets_id_fk')
			->addForeignKey('broker_id', 'brokers', 'id', 'transactions_broker_id_brokers_id_fk')
			->addForeignKey('currency_id', 'currencies', 'id', 'transactions_currency_id_currencies_id_fk')
			->addForeignKey('tax_currency_id', 'currencies', 'id', 'transactions_tax_currency_id_currencies_id_fk')
			->addForeignKey('fee_currency_id', 'currencies', 'id', 'transactions_fee_currency_id_currencies_id_fk')
			->create();
	}

	public function down(): void
	{
		$this->table('transactions')
			->drop();
		$this->table('api_imports')
			->drop();
		$this->table('import_files')
			->drop();
		$this->table('ticker_datas')
			->drop();
		$this->table('ticker_fundamentals')
			->drop();
		$this->table('import_mappings')
			->drop();
		$this->table('splits')
			->drop();
		$this->table('assets')
			->drop();
		$this->table('groups')
			->drop();
		$this->table('imports')
			->drop();
		$this->table('api_keys')
			->drop();
		$this->table('tickers')
			->drop();
		$this->table('exchange_rates')
			->drop();
		$this->table('brokers')
			->drop();
		$this->table('email_verifies')
			->drop();
		$this->table('portfolios')
			->drop();
		$this->table('markets')
			->drop();
		$this->table('currencies')
			->drop();
		$this->table('industries')
			->drop();
		$this->table('countries')
			->drop();
		$this->table('sectors')
			->drop();
		$this->table('users')
			->drop();
	}
}
