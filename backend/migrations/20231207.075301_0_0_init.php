<?php

declare(strict_types=1);

namespace Migration;

use Cycle\Migrations\Migration;

// phpcs:ignore
class InitMigration extends Migration
{
	protected const DATABASE = 'default';

	public function up(): void
	{
		$this->table('currencies')
			->addColumn('id', 'primary', ['nullable' => false, 'default' => null, 'size' => 11])
			->addColumn('code', 'string', ['nullable' => false, 'default' => null, 'size' => 3])
			->addColumn('name', 'string', ['nullable' => false, 'default' => null, 'size' => 50])
			->addColumn('symbol', 'string', ['nullable' => false, 'default' => null, 'size' => 5])
			->addIndex(['code'], ['name' => 'currencies_index_code', 'unique' => false])
			->setPrimaryKeys(['id'])
			->create();

		$this->table('users')
			->addColumn('id', 'primary', ['nullable' => false, 'default' => null, 'size' => 11])
			->addColumn('email', 'string', ['nullable' => false, 'default' => null, 'size' => 255])
			->addColumn('password', 'string', ['nullable' => false, 'default' => null, 'size' => 255])
			->addColumn('name', 'string', ['nullable' => false, 'default' => null, 'size' => 255])
			->addColumn('default_currency_id', 'integer', ['nullable' => false, 'default' => null, 'size' => 11])
			->addColumn('role', 'enum', ['nullable' => false, 'default' => 'User', 'values' => ['User', 'Admin']])
			->addColumn('is_email_verified', 'boolean', ['nullable' => false, 'default' => false])
			->addIndex(['default_currency_id'], ['name' => 'users_index_default_currency_id_657179dd4f5a5', 'unique' => false])
			->addForeignKey(['default_currency_id'], 'currencies', ['id'], [
				'name' => 'users_foreign_default_currency_id_657179dd4f5a8',
				'delete' => 'CASCADE',
				'update' => 'CASCADE',
				'indexCreate' => true,
			])
			->addIndex(['email'], ['name' => 'users_index_email', 'unique' => true])
			->setPrimaryKeys(['id'])
			->create();

		$this->table('portfolios')
			->addColumn('id', 'primary', [
				'nullable' => false,
				'defaultValue' => null,
				'size' => 11,
				'autoIncrement' => true,
				'unsigned' => false,
				'zerofill' => false,
			])
			->addColumn('user_id', 'integer', [
				'nullable' => false,
				'defaultValue' => null,
				'size' => 11,
				'autoIncrement' => false,
				'unsigned' => false,
				'zerofill' => false,
			])
			->addColumn('name', 'string', ['nullable' => false, 'defaultValue' => null, 'size' => 255])
			->addColumn('is_default', 'boolean', [
				'nullable' => false,
				'defaultValue' => null,
				'size' => 1,
				'autoIncrement' => false,
				'unsigned' => false,
				'zerofill' => false,
			])
			->addIndex(['user_id'], ['name' => 'portfolios_index_user_id_65b57eba83b92', 'unique' => false])
			->addForeignKey(['user_id'], 'users', ['id'], [
				'name' => 'portfolios_foreign_user_id_65b57eba83b95',
				'delete' => 'CASCADE',
				'update' => 'CASCADE',
				'indexCreate' => true,
			])
			->setPrimaryKeys(['id'])
			->create();

		$this->table('groups')
			->addColumn('id', 'primary', ['nullable' => false, 'default' => null, 'size' => 11])
			->addColumn('user_id', 'integer', ['nullable' => false, 'default' => null, 'size' => 11])
			->addColumn('portfolio_id', 'integer', [
				'nullable' => false,
				'defaultValue' => null,
				'size' => 11,
				'autoIncrement' => false,
				'unsigned' => false,
				'zerofill' => false,
			])
			->addColumn('name', 'string', ['nullable' => false, 'default' => null, 'size' => 255])
			->addColumn('color', 'string', ['nullable' => false, 'default' => null, 'size' => 7])
			->addColumn('is_others', 'boolean', ['nullable' => false, 'default' => false])
			->addIndex(['user_id'], ['name' => 'groups_index_user_id_657179dd4f473', 'unique' => false])
			->addIndex(['portfolio_id'], ['name' => 'groups_index_portfolio_id_65b57aa23e661', 'unique' => false])
			->addForeignKey(['user_id'], 'users', ['id'], [
				'name' => 'groups_foreign_user_id_657179dd4f47d',
				'delete' => 'CASCADE',
				'update' => 'CASCADE',
				'indexCreate' => true,
			])
			->addForeignKey(['portfolio_id'], 'portfolios', ['id'], [
				'name' => 'groups_foreign_portfolio_id_65b57aa23e664',
				'delete' => 'CASCADE',
				'update' => 'CASCADE',
				'indexCreate' => true,
			])
			->setPrimaryKeys(['id'])
			->create();

		$this->table('markets')
			->addColumn('id', 'primary', ['nullable' => false, 'default' => null, 'size' => 11])
			->addColumn('type', 'enum', ['nullable' => false, 'default' => 'Stock', 'values' => ['Stock', 'Crypto']])
			->addColumn('name', 'string', ['nullable' => false, 'default' => null, 'size' => 255])
			->addColumn('acronym', 'string', ['nullable' => false, 'default' => null, 'size' => 20])
			->addColumn('mic', 'string', ['nullable' => false, 'default' => null, 'size' => 4])
			->addColumn('country', 'string', ['nullable' => false, 'default' => null, 'size' => 2])
			->addColumn('city', 'string', ['nullable' => false, 'default' => null, 'size' => 255])
			->addColumn('timezone', 'string', ['nullable' => false, 'default' => null, 'size' => 255])
			->addColumn('currency_id', 'integer', ['nullable' => false, 'default' => null, 'size' => 11])
			->addIndex(['currency_id'], ['name' => 'markets_index_currency_id_657179dd4f50f', 'unique' => false])
			->addForeignKey(['currency_id'], 'currencies', ['id'], [
				'name' => 'markets_foreign_currency_id_657179dd4f512',
				'delete' => 'CASCADE',
				'update' => 'CASCADE',
				'indexCreate' => true,
			])
			->setPrimaryKeys(['id'])
			->create();

		$this->table('tickers')
			->addColumn('id', 'primary', ['nullable' => false, 'default' => null, 'size' => 11])
			->addColumn('ticker', 'string', ['nullable' => false, 'default' => null, 'size' => 20])
			->addColumn('name', 'string', ['nullable' => false, 'default' => null, 'size' => 255])
			->addColumn('market_id', 'integer', ['nullable' => false, 'default' => null, 'size' => 11])
			->addColumn('currency_id', 'integer', ['nullable' => false, 'default' => null, 'size' => 11])
			->addIndex(['market_id'], ['name' => 'tickers_index_market_id_657179dd4f54a', 'unique' => false])
			->addIndex(['currency_id'], ['name' => 'tickers_index_currency_id_657179dd4f55c', 'unique' => false])
			->addForeignKey(['market_id'], 'markets', ['id'], [
				'name' => 'tickers_foreign_market_id_657179dd4f54d',
				'delete' => 'CASCADE',
				'update' => 'CASCADE',
				'indexCreate' => true,
			])
			->addForeignKey(['currency_id'], 'currencies', ['id'], [
				'name' => 'tickers_foreign_currency_id_657179dd4f55f',
				'delete' => 'CASCADE',
				'update' => 'CASCADE',
				'indexCreate' => true,
			])
			->addIndex(['ticker'], ['name' => 'tickers_index_ticker', 'unique' => false])
			->setPrimaryKeys(['id'])
			->create();

		$this->table('assets')
			->addColumn('id', 'primary', ['nullable' => false, 'default' => null, 'size' => 11])
			->addColumn('user_id', 'integer', ['nullable' => false, 'default' => null, 'size' => 11])
			->addColumn('portfolio_id', 'integer', [
				'nullable' => false,
				'defaultValue' => null,
				'size' => 11,
				'autoIncrement' => false,
				'unsigned' => false,
				'zerofill' => false,
			])
			->addColumn('ticker_id', 'integer', ['nullable' => false, 'default' => null, 'size' => 11])
			->addColumn('group_id', 'integer', ['nullable' => false, 'default' => null, 'size' => 11])
			->addIndex(['user_id'], ['name' => 'assets_index_user_id_657179dd4f4c5', 'unique' => false])
			->addIndex(['portfolio_id'], ['name' => 'assets_index_portfolio_id_65b57aa23e798', 'unique' => false])
			->addIndex(['ticker_id'], ['name' => 'assets_index_ticker_id_657179dd4f4d9', 'unique' => false])
			->addIndex(['group_id'], ['name' => 'assets_index_group_id_657179dd4f4eb', 'unique' => false])
			->addForeignKey(['user_id'], 'users', ['id'], [
				'name' => 'assets_foreign_user_id_657179dd4f4c9',
				'delete' => 'CASCADE',
				'update' => 'CASCADE',
				'indexCreate' => true,
			])
			->addForeignKey(['portfolio_id'], 'portfolios', ['id'], [
				'name' => 'assets_foreign_portfolio_id_65b57aa23e79b',
				'delete' => 'CASCADE',
				'update' => 'CASCADE',
				'indexCreate' => true,
			])
			->addForeignKey(['ticker_id'], 'tickers', ['id'], [
				'name' => 'assets_foreign_ticker_id_657179dd4f4dc',
				'delete' => 'CASCADE',
				'update' => 'CASCADE',
				'indexCreate' => true,
			])
			->addForeignKey(['group_id'], 'groups', ['id'], [
				'name' => 'assets_foreign_group_id_657179dd4f4ee',
				'delete' => 'CASCADE',
				'update' => 'CASCADE',
				'indexCreate' => true,
			])
			->setPrimaryKeys(['id'])
			->create();

		$this->table('splits')
			->addColumn('id', 'primary', ['nullable' => false, 'default' => null, 'size' => 11])
			->addColumn('ticker_id', 'integer', ['nullable' => false, 'default' => null, 'size' => 11])
			->addColumn('date', 'timestamp', ['nullable' => false, 'default' => null])
			->addColumn('factor', 'decimal', ['nullable' => false, 'default' => null, 'scale' => 4, 'precision' => 8])
			->addIndex(['ticker_id'], ['name' => 'splits_index_ticker_id_657179dd4f4fe', 'unique' => false])
			->addForeignKey(['ticker_id'], 'tickers', ['id'], [
				'name' => 'splits_foreign_ticker_id_657179dd4f500',
				'delete' => 'CASCADE',
				'update' => 'CASCADE',
				'indexCreate' => true,
			])
			->addIndex(['date'], ['name' => 'splits_index_date', 'unique' => false])
			->addIndex(['date', 'ticker_id'], ['name' => 'splits_index_date_ticker_id', 'unique' => true])
			->setPrimaryKeys(['id'])
			->create();

		$this->table('ticker_datas')
			->addColumn('id', 'primary', ['nullable' => false, 'default' => null, 'size' => 11])
			->addColumn('ticker_id', 'integer', ['nullable' => false, 'default' => null, 'size' => 11])
			->addColumn('date', 'timestamp', ['nullable' => false, 'default' => null])
			->addColumn('open', 'decimal', ['nullable' => false, 'default' => null, 'scale' => 10, 'precision' => 20])
			->addColumn('close', 'decimal', ['nullable' => false, 'default' => null, 'scale' => 10, 'precision' => 20])
			->addColumn('high', 'decimal', ['nullable' => false, 'default' => null, 'scale' => 10, 'precision' => 20])
			->addColumn('low', 'decimal', ['nullable' => false, 'default' => null, 'scale' => 10, 'precision' => 20])
			->addColumn('volume', 'decimal', ['nullable' => false, 'default' => null, 'scale' => 10, 'precision' => 20])
			->addIndex(['ticker_id'], ['name' => 'ticker_datas_index_ticker_id_657179dd4f527', 'unique' => false])
			->addForeignKey(['ticker_id'], 'tickers', ['id'], [
				'name' => 'ticker_datas_foreign_ticker_id_657179dd4f52a',
				'delete' => 'CASCADE',
				'update' => 'CASCADE',
				'indexCreate' => true,
			])
			->addIndex(['date'], ['name' => 'ticker_datas_index_date', 'unique' => false])
			->addIndex(['date', 'ticker_id'], ['name' => 'ticker_datas_index_date_ticker_id', 'unique' => true])
			->setPrimaryKeys(['id'])
			->create();

		$this->table('brokers')
			->addColumn('id', 'primary', ['nullable' => false, 'default' => null, 'size' => 11])
			->addColumn('user_id', 'integer', ['nullable' => false, 'default' => null, 'size' => 11])
			->addColumn('portfolio_id', 'integer', [
				'nullable' => false,
				'defaultValue' => null,
				'size' => 11,
				'autoIncrement' => false,
				'unsigned' => false,
				'zerofill' => false,
			])
			->addColumn('name', 'string', ['nullable' => false, 'default' => null, 'size' => 255])
			->addColumn('import_type', 'enum', ['nullable' => false, 'default' => null, 'values' => ['Trading212', 'Revolut', 'Anycoin']])
			->addIndex(['user_id'], ['name' => 'brokers_index_user_id_657179dd4f4c5', 'unique' => false])
			->addIndex(['portfolio_id'], ['name' => 'brokers_index_portfolio_id_65b57aa23e69e', 'unique' => false])
			->addForeignKey(['user_id'], 'users', ['id'], [
				'name' => 'brokers_foreign_user_id_657179dd4f4c9',
				'delete' => 'CASCADE',
				'update' => 'CASCADE',
				'indexCreate' => true,
			])
			->addForeignKey(['portfolio_id'], 'portfolios', ['id'], [
				'name' => 'brokers_foreign_portfolio_id_65b57aa23e6a1',
				'delete' => 'CASCADE',
				'update' => 'CASCADE',
				'indexCreate' => true,
			])
			->setPrimaryKeys(['id'])
			->create();

		$this->table('exchange_rates')
			->addColumn('id', 'primary', ['nullable' => false, 'default' => null, 'size' => 11])
			->addColumn('currency_id', 'integer', ['nullable' => false, 'default' => null, 'size' => 11])
			->addColumn('date', 'timestamp', ['nullable' => false, 'default' => null])
			->addColumn('rate', 'decimal', ['nullable' => false, 'default' => null, 'scale' => 4, 'precision' => 9])
			->addIndex(['currency_id'], ['name' => 'exchange_rates_index_currency_id_657179dd4f539', 'unique' => false])
			->addForeignKey(['currency_id'], 'currencies', ['id'], [
				'name' => 'exchange_rates_foreign_currency_id_657179dd4f53c',
				'delete' => 'CASCADE',
				'update' => 'CASCADE',
				'indexCreate' => true,
			])
			->addIndex(['date'], ['name' => 'exchange_rates_index_date', 'unique' => false])
			->setPrimaryKeys(['id'])
			->create();

		$this->table('transactions')
			->addColumn('id', 'primary', ['nullable' => false, 'default' => null, 'size' => 11])
			->addColumn('user_id', 'integer', ['nullable' => false, 'default' => null, 'size' => 11])
			->addColumn('portfolio_id', 'integer', [
				'nullable' => false,
				'defaultValue' => null,
				'size' => 11,
				'autoIncrement' => false,
				'unsigned' => false,
				'zerofill' => false,
			])
			->addColumn('asset_id', 'integer', ['nullable' => false, 'default' => null, 'size' => 11])
			->addColumn('broker_id', 'integer', ['nullable' => false, 'default' => null, 'size' => 11])
			->addColumn(
				'action_type',
				'enum',
				['nullable' => false, 'default' => null, 'values' => ['Undefined', 'Buy', 'Sell', 'Dividend']],
			)
			->addColumn('action_created', 'timestamp', ['nullable' => false, 'default' => null])
			->addColumn('create_type', 'enum', ['nullable' => false, 'default' => 'Manual', 'values' => ['Manual', 'Import']])
			->addColumn('created', 'timestamp', ['nullable' => false, 'default' => null])
			->addColumn('modified', 'timestamp', ['nullable' => false, 'default' => null])
			->addColumn('units', 'decimal', ['nullable' => false, 'default' => null, 'scale' => 8, 'precision' => 18])
			->addColumn('price', 'decimal', ['nullable' => false, 'default' => null, 'scale' => 2, 'precision' => 9])
			->addColumn('currency_id', 'integer', ['nullable' => false, 'default' => null, 'size' => 11])
			->addColumn('tax', 'decimal', ['nullable' => false, 'default' => null, 'scale' => 2, 'precision' => 9])
			->addColumn('notes', 'tinyText', ['nullable' => true, 'default' => null])
			->addColumn('import_identifier', 'string', ['nullable' => true, 'default' => null, 'size' => 255])
			->addIndex(['user_id'], ['name' => 'transactions_index_user_id_657179dd4f5bc', 'unique' => false])
			->addIndex(['portfolio_id'], ['name' => 'transactions_index_portfolio_id_65b57aa23e609', 'unique' => false])
			->addIndex(['asset_id'], ['name' => 'transactions_index_asset_id_657179dd4f5cf', 'unique' => false])
			->addIndex(['broker_id'], ['name' => 'transactions_index_broker_id_657179dd4f5e2', 'unique' => false])
			->addIndex(['currency_id'], ['name' => 'transactions_index_currency_id_657179dd4f5fb', 'unique' => false])
			->addForeignKey(['user_id'], 'users', ['id'], [
				'name' => 'transactions_foreign_user_id_657179dd4f5c0',
				'delete' => 'CASCADE',
				'update' => 'CASCADE',
				'indexCreate' => true,
			])
			->addForeignKey(['portfolio_id'], 'portfolios', ['id'], [
				'name' => 'transactions_foreign_portfolio_id_65b57aa23e610',
				'delete' => 'CASCADE',
				'update' => 'CASCADE',
				'indexCreate' => true,
			])
			->addForeignKey(['asset_id'], 'assets', ['id'], [
				'name' => 'transactions_foreign_asset_id_657179dd4f5d2',
				'delete' => 'CASCADE',
				'update' => 'CASCADE',
				'indexCreate' => true,
			])
			->addForeignKey(['broker_id'], 'brokers', ['id'], [
				'name' => 'transactions_foreign_broker_id_657179dd4f5e9',
				'delete' => 'CASCADE',
				'update' => 'CASCADE',
				'indexCreate' => true,
			])
			->addForeignKey(['currency_id'], 'currencies', ['id'], [
				'name' => 'transactions_foreign_currency_id_657179dd4f5fe',
				'delete' => 'CASCADE',
				'update' => 'CASCADE',
				'indexCreate' => true,
			])
			->addIndex(['action_type'], ['name' => 'transactions_index_action_type', 'unique' => false])
			->addIndex(['action_created'], ['name' => 'transactions_index_action_created', 'unique' => false])
			->addIndex(['created'], ['name' => 'transactions_index_created', 'unique' => false])
			->addIndex(['modified'], ['name' => 'transactions_index_modified', 'unique' => false])
			->setPrimaryKeys(['id'])
			->create();

		$this->table('group_datas')
			->addColumn('id', 'primary', ['nullable' => false, 'default' => null, 'size' => 11])
			->addColumn('group_id', 'integer', ['nullable' => false, 'default' => null, 'size' => 11])
			->addColumn('user_id', 'integer', ['nullable' => false, 'default' => null, 'size' => 11])
			->addColumn('portfolio_id', 'integer', [
				'nullable' => false,
				'defaultValue' => null,
				'size' => 11,
				'autoIncrement' => false,
				'unsigned' => false,
				'zerofill' => false,
			])
			->addColumn('date', 'timestamp', ['nullable' => false, 'default' => null])
			->addColumn('value', 'decimal', ['nullable' => false, 'default' => null, 'scale' => 2, 'precision' => 11])
			->addColumn('transaction_value', 'decimal', ['nullable' => false, 'default' => null, 'scale' => 2, 'precision' => 11])
			->addColumn('gain', 'decimal', ['nullable' => false, 'default' => null, 'scale' => 2, 'precision' => 11])
			->addColumn('gain_percentage', 'float', ['nullable' => false, 'default' => null])
			->addColumn('dividend_gain', 'decimal', ['nullable' => false, 'default' => null, 'scale' => 2, 'precision' => 11])
			->addColumn('dividend_gain_percentage', 'float', ['nullable' => false, 'default' => null])
			->addColumn('fx_impact', 'decimal', ['nullable' => false, 'default' => null, 'scale' => 2, 'precision' => 11])
			->addColumn('fx_impact_percentage', 'float', ['nullable' => false, 'default' => null])
			->addColumn('return', 'decimal', ['nullable' => false, 'default' => null, 'scale' => 2, 'precision' => 11])
			->addColumn('return_percentage', 'float', ['nullable' => false, 'default' => null])
			->addIndex(['group_id'], ['name' => 'group_datas_index_group_id_6580626872321', 'unique' => false])
			->addIndex(['user_id'], ['name' => 'group_datas_index_user_id_658062687233a', 'unique' => false])
			->addIndex(['portfolio_id'], ['name' => 'group_datas_index_portfolio_id_65b57aa23e69a', 'unique' => false])
			->addForeignKey(['group_id'], 'groups', ['id'], [
				'name' => 'group_datas_foreign_group_id_658062687232a',
				'delete' => 'CASCADE',
				'update' => 'CASCADE',
				'indexCreate' => true,
			])
			->addForeignKey(['user_id'], 'users', ['id'], [
				'name' => 'group_datas_foreign_user_id_658062687233e',
				'delete' => 'CASCADE',
				'update' => 'CASCADE',
				'indexCreate' => true,
			])
			->addForeignKey(['portfolio_id'], 'portfolios', ['id'], [
				'name' => 'group_datas_foreign_portfolio_id_65b57aa23e69d',
				'delete' => 'CASCADE',
				'update' => 'CASCADE',
				'indexCreate' => true,
			])
			->addIndex(['date'], ['name' => 'group_datas_index_date', 'unique' => false])
			->addIndex(['date', 'group_id', 'portfolio_id'], ['name' => 'group_datas_index_date_group_id_portfolio_id', 'unique' => true])
			->setPrimaryKeys(['id'])
			->create();

		$this->table('portfolio_datas')
			->addColumn('id', 'primary', ['nullable' => false, 'default' => null, 'size' => 11])
			->addColumn('user_id', 'integer', ['nullable' => false, 'default' => null, 'size' => 11])
			->addColumn('portfolio_id', 'integer', [
				'nullable' => false,
				'defaultValue' => null,
				'size' => 11,
				'autoIncrement' => false,
				'unsigned' => false,
				'zerofill' => false,
			])
			->addColumn('date', 'timestamp', ['nullable' => false, 'default' => null])
			->addColumn('value', 'decimal', ['nullable' => false, 'default' => null, 'scale' => 2, 'precision' => 11])
			->addColumn('transaction_value', 'decimal', ['nullable' => false, 'default' => null, 'scale' => 2, 'precision' => 11])
			->addColumn('gain', 'decimal', ['nullable' => false, 'default' => null, 'scale' => 2, 'precision' => 11])
			->addColumn('gain_percentage', 'float', ['nullable' => false, 'default' => null])
			->addColumn('dividend_gain', 'decimal', ['nullable' => false, 'default' => null, 'scale' => 2, 'precision' => 11])
			->addColumn('dividend_gain_percentage', 'float', ['nullable' => false, 'default' => null])
			->addColumn('fx_impact', 'decimal', ['nullable' => false, 'default' => null, 'scale' => 2, 'precision' => 11])
			->addColumn('fx_impact_percentage', 'float', ['nullable' => false, 'default' => null])
			->addColumn('return', 'decimal', ['nullable' => false, 'default' => null, 'scale' => 2, 'precision' => 11])
			->addColumn('return_percentage', 'float', ['nullable' => false, 'default' => null])
			->addIndex(['user_id'], ['name' => 'portfolio_datas_index_user_id_658062687238c', 'unique' => false])
			->addIndex(['portfolio_id'], ['name' => 'portfolio_datas_index_portfolio_id_65b57aa23e70a', 'unique' => false])
			->addForeignKey(['user_id'], 'users', ['id'], [
				'name' => 'portfolio_datas_foreign_user_id_6580626872392',
				'delete' => 'CASCADE',
				'update' => 'CASCADE',
				'indexCreate' => true,
			])
			->addForeignKey(['portfolio_id'], 'portfolios', ['id'], [
				'name' => 'portfolio_datas_foreign_portfolio_id_65b57aa23e70d',
				'delete' => 'CASCADE',
				'update' => 'CASCADE',
				'indexCreate' => true,
			])
			->addIndex(['date'], ['name' => 'portfolio_datas_index_date', 'unique' => false])
			->addIndex(['date', 'portfolio_id'], ['name' => 'portfolio_datas_index_date_portfolio_id', 'unique' => true])
			->setPrimaryKeys(['id'])
			->create();

		$this->table('email_verifies')
			->addColumn('token', 'uuid', ['nullable' => false, 'default' => null, 'size' => 36])
			->addColumn('id', 'primary', ['nullable' => false, 'default' => null, 'size' => 11])
			->addColumn('user_id', 'integer', ['nullable' => false, 'default' => null, 'size' => 11])
			->addIndex(['user_id'], ['name' => 'email_verifies_index_user_id_659d35383d89f', 'unique' => false])
			->addForeignKey(['user_id'], 'users', ['id'], [
				'name' => 'email_verifies_foreign_user_id_659d35383d8a6',
				'delete' => 'CASCADE',
				'update' => 'CASCADE',
				'indexCreate' => true,
			])
			->addIndex(['token'], ['name' => 'email_verifies_index_token', 'unique' => false])
			->setPrimaryKeys(['id'])
			->create();

		$this->table('benchmark_datas')
			->addColumn('id', 'primary', ['nullable' => false, 'default' => null, 'size' => 11])
			->addColumn('user_id', 'integer', ['nullable' => false, 'default' => null, 'size' => 11])
			->addColumn('portfolio_id', 'integer', [
				'nullable' => false,
				'defaultValue' => null,
				'size' => 11,
				'autoIncrement' => false,
				'unsigned' => false,
				'zerofill' => false,
			])
			->addColumn('asset_id', 'integer', ['nullable' => false, 'default' => null, 'size' => 11])
			->addColumn('date', 'timestamp', ['nullable' => false, 'default' => null])
			->addColumn('from_date', 'timestamp', ['nullable' => false, 'default' => null])
			->addColumn('value', 'decimal', ['nullable' => false, 'default' => null, 'scale' => 2, 'precision' => 11])
			->addColumn('units', 'decimal', ['nullable' => false, 'default' => null, 'scale' => 8, 'precision' => 18])
			->addIndex(['user_id'], ['name' => 'benchmark_datas_index_user_id_65a6ba59cf8ba', 'unique' => false])
			->addIndex(['portfolio_id'], ['name' => 'benchmark_datas_index_portfolio_id_65b57aa23e71a', 'unique' => false])
			->addIndex(['asset_id'], ['name' => 'benchmark_datas_index_asset_id_65a6ba59cf8d1', 'unique' => false])
			->addForeignKey(['user_id'], 'users', ['id'], [
				'name' => 'benchmark_datas_foreign_user_id_65a6ba59cf8c1',
				'delete' => 'CASCADE',
				'update' => 'CASCADE',
				'indexCreate' => true,
			])
			->addForeignKey(['portfolio_id'], 'portfolios', ['id'], [
				'name' => 'benchmark_datas_foreign_portfolio_id_65b57aa23e71d',
				'delete' => 'CASCADE',
				'update' => 'CASCADE',
				'indexCreate' => true,
			])
			->addForeignKey(['asset_id'], 'assets', ['id'], [
				'name' => 'benchmark_datas_foreign_asset_id_65a6ba59cf8d4',
				'delete' => 'CASCADE',
				'update' => 'CASCADE',
				'indexCreate' => true,
			])
			->addIndex(['date'], ['name' => 'benchmark_datas_index_date', 'unique' => false])
			->addIndex(['date', 'from_date', 'asset_id'], ['name' => 'benchmark_datas_index_date_from_date_asset_id', 'unique' => true])
			->setPrimaryKeys(['id'])
			->create();
	}

	public function down(): void
	{
		$this->table('benchmark_datas')->drop();
		$this->table('email_verifies')->drop();
		$this->table('portfolio_datas')->drop();
		$this->table('group_datas')->drop();
		$this->table('transactions')->drop();
		$this->table('exchange_rates')->drop();
		$this->table('brokers')->drop();
		$this->table('ticker_datas')->drop();
		$this->table('splits')->drop();
		$this->table('assets')->drop();
		$this->table('tickers')->drop();
		$this->table('markets')->drop();
		$this->table('groups')->drop();
		$this->table('portfolios')->drop();
		$this->table('users')->drop();
		$this->table('currencies')->drop();
	}
}
