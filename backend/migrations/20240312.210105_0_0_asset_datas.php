<?php

declare(strict_types=1);

namespace Migration;

use Cycle\Migrations\Migration;

class AssetDatasMigration extends Migration
{
	protected const DATABASE = 'default';

	public function up(): void
	{
		$this->table('asset_datas')
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
			->addColumn('portfolio_id', 'integer', [
				'nullable' => false,
				'defaultValue' => null,
				'size' => 11,
				'autoIncrement' => false,
				'unsigned' => false,
				'zerofill' => false,
			])
			->addColumn('asset_id', 'integer', [
				'nullable' => false,
				'defaultValue' => null,
				'size' => 11,
				'autoIncrement' => false,
				'unsigned' => false,
				'zerofill' => false,
			])
			->addColumn('date', 'timestamp', ['nullable' => false, 'defaultValue' => null])
			->addColumn('price', 'decimal', ['nullable' => false, 'defaultValue' => null, 'precision' => 20, 'scale' => 10])
			->addColumn('units', 'decimal', ['nullable' => false, 'defaultValue' => null, 'precision' => 18, 'scale' => 8])
			->addColumn('value', 'decimal', ['nullable' => false, 'defaultValue' => null, 'precision' => 12, 'scale' => 2])
			->addColumn('transaction_value', 'decimal', [
				'nullable' => false,
				'defaultValue' => null,
				'precision' => 12,
				'scale' => 2,
			])
			->addColumn('transaction_value_default_currency', 'decimal', [
				'nullable' => false,
				'defaultValue' => null,
				'precision' => 12,
				'scale' => 2,
			])
			->addColumn('gain', 'decimal', ['nullable' => false, 'defaultValue' => null, 'precision' => 12, 'scale' => 2])
			->addColumn('gain_default_currency', 'decimal', [
				'nullable' => false,
				'defaultValue' => null,
				'precision' => 12,
				'scale' => 2,
			])
			->addColumn('gain_percentage', 'float', ['nullable' => false, 'defaultValue' => null])
			->addColumn('gain_percentage_per_annum', 'float', ['nullable' => false, 'defaultValue' => null])
			->addColumn('dividend_gain', 'decimal', ['nullable' => false, 'defaultValue' => null, 'precision' => 12, 'scale' => 2])
			->addColumn('dividend_gain_default_currency', 'decimal', [
				'nullable' => false,
				'defaultValue' => null,
				'precision' => 12,
				'scale' => 2,
			])
			->addColumn('dividend_gain_percentage', 'float', ['nullable' => false, 'defaultValue' => null])
			->addColumn('dividend_gain_percentage_per_annum', 'float', ['nullable' => false, 'defaultValue' => null])
			->addColumn('fx_impact', 'decimal', ['nullable' => false, 'defaultValue' => null, 'precision' => 12, 'scale' => 2])
			->addColumn('fx_impact_percentage', 'float', ['nullable' => false, 'defaultValue' => null])
			->addColumn('fx_impact_percentage_per_annum', 'float', ['nullable' => false, 'defaultValue' => null])
			->addColumn('return', 'decimal', ['nullable' => false, 'defaultValue' => null, 'precision' => 12, 'scale' => 2])
			->addColumn('return_percentage', 'float', ['nullable' => false, 'defaultValue' => null])
			->addColumn('return_percentage_per_annum', 'float', ['nullable' => false, 'defaultValue' => null])
			->addColumn('first_transaction_action_created', 'timestamp', ['nullable' => false, 'defaultValue' => null])
			->addIndex(['user_id'], ['name' => 'asset_datas_index_user_id_65f0c291092c2', 'unique' => false])
			->addIndex(['portfolio_id'], ['name' => 'asset_datas_index_portfolio_id_65f0c291092dd', 'unique' => false])
			->addIndex(['asset_id'], ['name' => 'asset_datas_index_asset_id_65f0c291092f4', 'unique' => false])
			->addForeignKey(['user_id'], 'users', ['id'], [
				'name' => 'asset_datas_foreign_user_id_65f0c291092c9',
				'delete' => 'CASCADE',
				'update' => 'CASCADE',
				'indexCreate' => true,
			])
			->addForeignKey(['portfolio_id'], 'portfolios', ['id'], [
				'name' => 'asset_datas_foreign_portfolio_id_65f0c291092e1',
				'delete' => 'CASCADE',
				'update' => 'CASCADE',
				'indexCreate' => true,
			])
			->addForeignKey(['asset_id'], 'assets', ['id'], [
				'name' => 'asset_datas_foreign_asset_id_65f0c291092f7',
				'delete' => 'CASCADE',
				'update' => 'CASCADE',
				'indexCreate' => true,
			])
			->setPrimaryKeys(['id'])
			->create();
	}

	public function down(): void
	{
		$this->table('asset_datas')->drop();
	}
}
