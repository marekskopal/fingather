<?php

declare(strict_types=1);

namespace Migration;

use Cycle\Migrations\Migration;

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
        ->setPrimaryKeys(['id'])
        ->create();
        $this->table('users')
        ->addColumn('id', 'primary', ['nullable' => false, 'default' => null, 'size' => 11])
        ->addColumn('email', 'string', ['nullable' => false, 'default' => null, 'size' => 255])
        ->addColumn('password', 'string', ['nullable' => false, 'default' => null, 'size' => 255])
        ->addColumn('name', 'string', ['nullable' => false, 'default' => null, 'size' => 255])
        ->addColumn('default_currency_id', 'integer', ['nullable' => false, 'default' => null, 'size' => 11])
        ->addIndex(['default_currency_id'], ['name' => 'users_index_default_currency_id_657179dd4f5a5', 'unique' => false])
        ->addForeignKey(['default_currency_id'], 'currencies', ['id'], [
            'name' => 'users_foreign_default_currency_id_657179dd4f5a8',
            'delete' => 'CASCADE',
            'update' => 'CASCADE',
            'indexCreate' => true,
        ])
        ->setPrimaryKeys(['id'])
        ->create();
        $this->table('groups')
        ->addColumn('id', 'primary', ['nullable' => false, 'default' => null, 'size' => 11])
        ->addColumn('name', 'string', ['nullable' => false, 'default' => null, 'size' => 255])
        ->addColumn('user_id', 'integer', ['nullable' => false, 'default' => null, 'size' => 11])
        ->addIndex(['user_id'], ['name' => 'groups_index_user_id_657179dd4f473', 'unique' => false])
        ->addForeignKey(['user_id'], 'users', ['id'], [
            'name' => 'groups_foreign_user_id_657179dd4f47d',
            'delete' => 'CASCADE',
            'update' => 'CASCADE',
            'indexCreate' => true,
        ])
        ->setPrimaryKeys(['id'])
        ->create();
        $this->table('markets')
        ->addColumn('id', 'primary', ['nullable' => false, 'default' => null, 'size' => 11])
        ->addColumn('name', 'string', ['nullable' => false, 'default' => null, 'size' => 255])
        ->addColumn('acronym', 'string', ['nullable' => false, 'default' => null, 'size' => 20])
        ->addColumn('mic', 'string', ['nullable' => false, 'default' => null, 'size' => 4])
        ->addColumn('country', 'string', ['nullable' => false, 'default' => null, 'size' => 2])
        ->addColumn('city', 'string', ['nullable' => false, 'default' => null, 'size' => 255])
        ->addColumn('web', 'string', ['nullable' => false, 'default' => null, 'size' => 255])
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
        ->setPrimaryKeys(['id'])
        ->create();
        $this->table('assets')
        ->addColumn('id', 'primary', ['nullable' => false, 'default' => null, 'size' => 11])
        ->addColumn('user_id', 'integer', ['nullable' => false, 'default' => null, 'size' => 11])
        ->addColumn('ticker_id', 'integer', ['nullable' => false, 'default' => null, 'size' => 11])
        ->addColumn('group_id', 'integer', ['nullable' => true, 'default' => null, 'size' => 11])
        ->addIndex(['user_id'], ['name' => 'assets_index_user_id_657179dd4f4c5', 'unique' => false])
        ->addIndex(['ticker_id'], ['name' => 'assets_index_ticker_id_657179dd4f4d9', 'unique' => false])
        ->addIndex(['group_id'], ['name' => 'assets_index_group_id_657179dd4f4eb', 'unique' => false])
        ->addForeignKey(['user_id'], 'users', ['id'], [
            'name' => 'assets_foreign_user_id_657179dd4f4c9',
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
        ->addColumn('date', 'timestamp', ['nullable' => false, 'default' => null])
        ->addColumn('factor', 'decimal', ['nullable' => false, 'default' => null, 'scale' => 10, 'precision' => 10])
        ->addColumn('ticker_id', 'integer', ['nullable' => false, 'default' => null, 'size' => 11])
        ->addIndex(['ticker_id'], ['name' => 'splits_index_ticker_id_657179dd4f4fe', 'unique' => false])
        ->addForeignKey(['ticker_id'], 'tickers', ['id'], [
            'name' => 'splits_foreign_ticker_id_657179dd4f500',
            'delete' => 'CASCADE',
            'update' => 'CASCADE',
            'indexCreate' => true,
        ])
        ->setPrimaryKeys(['id'])
        ->create();
        $this->table('ticker_datas')
        ->addColumn('id', 'primary', ['nullable' => false, 'default' => null, 'size' => 11])
        ->addColumn('date', 'timestamp', ['nullable' => false, 'default' => null])
        ->addColumn('open', 'decimal', ['nullable' => false, 'default' => null, 'scale' => 10, 'precision' => 10])
        ->addColumn('close', 'decimal', ['nullable' => false, 'default' => null, 'scale' => 10, 'precision' => 10])
        ->addColumn('high', 'decimal', ['nullable' => false, 'default' => null, 'scale' => 10, 'precision' => 10])
        ->addColumn('low', 'decimal', ['nullable' => false, 'default' => null, 'scale' => 10, 'precision' => 10])
        ->addColumn('volume', 'decimal', ['nullable' => false, 'default' => null, 'scale' => 10, 'precision' => 10])
        ->addColumn('performance', 'double', ['nullable' => false, 'default' => null])
        ->addColumn('ticker_id', 'integer', ['nullable' => false, 'default' => null, 'size' => 11])
        ->addIndex(['ticker_id'], ['name' => 'ticker_datas_index_ticker_id_657179dd4f527', 'unique' => false])
        ->addForeignKey(['ticker_id'], 'currencies', ['id'], [
            'name' => 'ticker_datas_foreign_ticker_id_657179dd4f52a',
            'delete' => 'CASCADE',
            'update' => 'CASCADE',
            'indexCreate' => true,
        ])
        ->setPrimaryKeys(['id'])
        ->create();
        $this->table('brokers')
        ->addColumn('id', 'primary', ['nullable' => false, 'default' => null, 'size' => 11])
        ->addColumn('user_id', 'integer', ['nullable' => false, 'default' => null, 'size' => 11])
        ->addColumn('name', 'string', ['nullable' => false, 'default' => null, 'size' => 255])
        ->addColumn('import_type', 'enum', ['nullable' => false, 'default' => null, 'values' => ['Trading212', 'Revolut']])
		->addIndex(['user_id'], ['name' => 'brokers_index_user_id_657179dd4f4c5', 'unique' => false])
		->addForeignKey(['user_id'], 'users', ['id'], [
			'name' => 'brokers_foreign_user_id_657179dd4f4c9',
			'delete' => 'CASCADE',
			'update' => 'CASCADE',
			'indexCreate' => true,
		])
        ->setPrimaryKeys(['id'])
        ->create();
        $this->table('exchange_rates')
        ->addColumn('id', 'primary', ['nullable' => false, 'default' => null, 'size' => 11])
        ->addColumn('date', 'timestamp', ['nullable' => false, 'default' => null])
        ->addColumn('rate', 'decimal', ['nullable' => false, 'default' => null, 'scale' => 10, 'precision' => 10])
        ->addColumn('currency_id', 'integer', ['nullable' => false, 'default' => null, 'size' => 11])
        ->addIndex(['currency_id'], ['name' => 'exchange_rates_index_currency_id_657179dd4f539', 'unique' => false])
        ->addForeignKey(['currency_id'], 'currencies', ['id'], [
            'name' => 'exchange_rates_foreign_currency_id_657179dd4f53c',
            'delete' => 'CASCADE',
            'update' => 'CASCADE',
            'indexCreate' => true,
        ])
        ->setPrimaryKeys(['id'])
        ->create();
        $this->table('dividends')
        ->addColumn('id', 'primary', ['nullable' => false, 'default' => null, 'size' => 11])
        ->addColumn('paid_date', 'timestamp', ['nullable' => false, 'default' => null])
        ->addColumn('price_gross', 'decimal', ['nullable' => false, 'default' => null, 'scale' => 10, 'precision' => 10])
        ->addColumn('price_net', 'decimal', ['nullable' => false, 'default' => null, 'scale' => 10, 'precision' => 10])
        ->addColumn('tax', 'decimal', ['nullable' => false, 'default' => null, 'scale' => 10, 'precision' => 10])
        ->addColumn('exchange_rate', 'decimal', ['nullable' => false, 'default' => null, 'scale' => 10, 'precision' => 10])
        ->addColumn('asset_id', 'integer', ['nullable' => false, 'default' => null, 'size' => 11])
        ->addColumn('broker_id', 'integer', ['nullable' => false, 'default' => null, 'size' => 11])
        ->addColumn('currency_id', 'integer', ['nullable' => false, 'default' => null, 'size' => 11])
        ->addIndex(['asset_id'], ['name' => 'dividends_index_asset_id_657179dd4f572', 'unique' => false])
        ->addIndex(['broker_id'], ['name' => 'dividends_index_broker_id_657179dd4f585', 'unique' => false])
        ->addIndex(['currency_id'], ['name' => 'dividends_index_currency_id_657179dd4f598', 'unique' => false])
        ->addForeignKey(['asset_id'], 'assets', ['id'], [
            'name' => 'dividends_foreign_asset_id_657179dd4f576',
            'delete' => 'CASCADE',
            'update' => 'CASCADE',
            'indexCreate' => true,
        ])
        ->addForeignKey(['broker_id'], 'brokers', ['id'], [
            'name' => 'dividends_foreign_broker_id_657179dd4f588',
            'delete' => 'CASCADE',
            'update' => 'CASCADE',
            'indexCreate' => true,
        ])
        ->addForeignKey(['currency_id'], 'currencies', ['id'], [
            'name' => 'dividends_foreign_currency_id_657179dd4f59b',
            'delete' => 'CASCADE',
            'update' => 'CASCADE',
            'indexCreate' => true,
        ])
        ->setPrimaryKeys(['id'])
        ->create();
        $this->table('transactions')
        ->addColumn('id', 'primary', ['nullable' => false, 'default' => null, 'size' => 11])
        ->addColumn('action_type', 'enum', ['nullable' => false, 'default' => null, 'values' => ['Undefined', 'Buy', 'Sell']])
        ->addColumn('created', 'timestamp', ['nullable' => false, 'default' => null])
        ->addColumn('units', 'decimal', ['nullable' => false, 'default' => null, 'scale' => 10, 'precision' => 10])
        ->addColumn('price_unit', 'decimal', ['nullable' => false, 'default' => null, 'scale' => 10, 'precision' => 10])
        ->addColumn('exchange_rate', 'decimal', ['nullable' => false, 'default' => null, 'scale' => 10, 'precision' => 10])
        ->addColumn('fee_conversion', 'decimal', ['nullable' => false, 'default' => null, 'scale' => 10, 'precision' => 10])
        ->addColumn('notes', 'tinyText', ['nullable' => false, 'default' => null])
        ->addColumn('import_identifier', 'string', ['nullable' => false, 'default' => null, 'size' => 255])
        ->addColumn('user_id', 'integer', ['nullable' => false, 'default' => null, 'size' => 11])
        ->addColumn('asset_id', 'integer', ['nullable' => false, 'default' => null, 'size' => 11])
        ->addColumn('broker_id', 'integer', ['nullable' => false, 'default' => null, 'size' => 11])
        ->addColumn('currency_id', 'integer', ['nullable' => false, 'default' => null, 'size' => 11])
        ->addIndex(['user_id'], ['name' => 'transactions_index_user_id_657179dd4f5bc', 'unique' => false])
        ->addIndex(['asset_id'], ['name' => 'transactions_index_asset_id_657179dd4f5cf', 'unique' => false])
        ->addIndex(['broker_id'], ['name' => 'transactions_index_broker_id_657179dd4f5e2', 'unique' => false])
        ->addIndex(['currency_id'], ['name' => 'transactions_index_currency_id_657179dd4f5fb', 'unique' => false])
        ->addForeignKey(['user_id'], 'users', ['id'], [
            'name' => 'transactions_foreign_user_id_657179dd4f5c0',
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
        ->setPrimaryKeys(['id'])
        ->create();
    }

    public function down(): void
    {
        $this->table('transactions')->drop();
        $this->table('dividends')->drop();
        $this->table('exchange_rates')->drop();
        $this->table('brokers')->drop();
        $this->table('ticker_datas')->drop();
        $this->table('splits')->drop();
        $this->table('assets')->drop();
        $this->table('tickers')->drop();
        $this->table('markets')->drop();
        $this->table('groups')->drop();
        $this->table('users')->drop();
        $this->table('currencies')->drop();
    }
}
