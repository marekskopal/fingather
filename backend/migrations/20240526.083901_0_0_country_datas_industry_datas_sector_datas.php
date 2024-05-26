<?php

declare(strict_types=1);

namespace Migration;

use Cycle\Migrations\Migration;

class CountryDatasIndustryDatasSectorDatasMigration extends Migration
{
	protected const DATABASE = 'default';

	public function up(): void
	{
		$this->table('country_datas')
			->addColumn('id', 'primary', [
				'nullable' => false,
				'defaultValue' => null,
				'size' => 11,
				'autoIncrement' => true,
				'unsigned' => false,
				'zerofill' => false,
			])
			->addColumn('country_id', 'integer', [
				'nullable' => false,
				'defaultValue' => null,
				'size' => 11,
				'autoIncrement' => false,
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
			->addColumn('date', 'timestamp', ['nullable' => false, 'defaultValue' => null])
			->addColumn('value', 'decimal', ['nullable' => false, 'defaultValue' => null, 'precision' => 11, 'scale' => 2])
			->addColumn('transaction_value', 'decimal', [
				'nullable' => false,
				'defaultValue' => null,
				'precision' => 11,
				'scale' => 2,
			])
			->addColumn('gain', 'decimal', ['nullable' => false, 'defaultValue' => null, 'precision' => 11, 'scale' => 2])
			->addColumn('gain_percentage', 'float', ['nullable' => false, 'defaultValue' => null])
			->addColumn('gain_percentage_per_annum', 'float', ['nullable' => false, 'defaultValue' => null])
			->addColumn('realized_gain', 'decimal', ['nullable' => false, 'defaultValue' => null, 'precision' => 11, 'scale' => 2])
			->addColumn('dividend_gain', 'decimal', ['nullable' => false, 'defaultValue' => null, 'precision' => 11, 'scale' => 2])
			->addColumn('dividend_gain_percentage', 'float', ['nullable' => false, 'defaultValue' => null])
			->addColumn('dividend_gain_percentage_per_annum', 'float', ['nullable' => false, 'defaultValue' => null])
			->addColumn('fx_impact', 'decimal', ['nullable' => false, 'defaultValue' => null, 'precision' => 11, 'scale' => 2])
			->addColumn('fx_impact_percentage', 'float', ['nullable' => false, 'defaultValue' => null])
			->addColumn('fx_impact_percentage_per_annum', 'float', ['nullable' => false, 'defaultValue' => null])
			->addColumn('return', 'decimal', ['nullable' => false, 'defaultValue' => null, 'precision' => 11, 'scale' => 2])
			->addColumn('return_percentage', 'float', ['nullable' => false, 'defaultValue' => null])
			->addColumn('return_percentage_per_annum', 'float', ['nullable' => false, 'defaultValue' => null])
			->addColumn('tax', 'decimal', ['nullable' => false, 'defaultValue' => null, 'precision' => 12, 'scale' => 2])
			->addColumn('fee', 'decimal', ['nullable' => false, 'defaultValue' => null, 'precision' => 12, 'scale' => 2])
			->addIndex(['country_id'], ['name' => 'country_datas_index_country_id_6652f52589b74', 'unique' => false])
			->addIndex(['user_id'], ['name' => 'country_datas_index_user_id_6652f52589baa', 'unique' => false])
			->addIndex(['portfolio_id'], ['name' => 'country_datas_index_portfolio_id_6652f52589c08', 'unique' => false])
			->addForeignKey(['country_id'], 'countries', ['id'], [
				'name' => 'country_datas_foreign_country_id_6652f52589b7d',
				'delete' => 'CASCADE',
				'update' => 'CASCADE',
				'indexCreate' => true,
			])
			->addForeignKey(['user_id'], 'users', ['id'], [
				'name' => 'country_datas_foreign_user_id_6652f52589baf',
				'delete' => 'CASCADE',
				'update' => 'CASCADE',
				'indexCreate' => true,
			])
			->addForeignKey(['portfolio_id'], 'portfolios', ['id'], [
				'name' => 'country_datas_foreign_portfolio_id_6652f52589c0e',
				'delete' => 'CASCADE',
				'update' => 'CASCADE',
				'indexCreate' => true,
			])
			->setPrimaryKeys(['id'])
			->create();

		$this->table('industry_datas')
			->addColumn('id', 'primary', [
				'nullable' => false,
				'defaultValue' => null,
				'size' => 11,
				'autoIncrement' => true,
				'unsigned' => false,
				'zerofill' => false,
			])
			->addColumn('industry_id', 'integer', [
				'nullable' => false,
				'defaultValue' => null,
				'size' => 11,
				'autoIncrement' => false,
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
			->addColumn('date', 'timestamp', ['nullable' => false, 'defaultValue' => null])
			->addColumn('value', 'decimal', ['nullable' => false, 'defaultValue' => null, 'precision' => 11, 'scale' => 2])
			->addColumn('transaction_value', 'decimal', [
				'nullable' => false,
				'defaultValue' => null,
				'precision' => 11,
				'scale' => 2,
			])
			->addColumn('gain', 'decimal', ['nullable' => false, 'defaultValue' => null, 'precision' => 11, 'scale' => 2])
			->addColumn('gain_percentage', 'float', ['nullable' => false, 'defaultValue' => null])
			->addColumn('gain_percentage_per_annum', 'float', ['nullable' => false, 'defaultValue' => null])
			->addColumn('realized_gain', 'decimal', ['nullable' => false, 'defaultValue' => null, 'precision' => 11, 'scale' => 2])
			->addColumn('dividend_gain', 'decimal', ['nullable' => false, 'defaultValue' => null, 'precision' => 11, 'scale' => 2])
			->addColumn('dividend_gain_percentage', 'float', ['nullable' => false, 'defaultValue' => null])
			->addColumn('dividend_gain_percentage_per_annum', 'float', ['nullable' => false, 'defaultValue' => null])
			->addColumn('fx_impact', 'decimal', ['nullable' => false, 'defaultValue' => null, 'precision' => 11, 'scale' => 2])
			->addColumn('fx_impact_percentage', 'float', ['nullable' => false, 'defaultValue' => null])
			->addColumn('fx_impact_percentage_per_annum', 'float', ['nullable' => false, 'defaultValue' => null])
			->addColumn('return', 'decimal', ['nullable' => false, 'defaultValue' => null, 'precision' => 11, 'scale' => 2])
			->addColumn('return_percentage', 'float', ['nullable' => false, 'defaultValue' => null])
			->addColumn('return_percentage_per_annum', 'float', ['nullable' => false, 'defaultValue' => null])
			->addColumn('tax', 'decimal', ['nullable' => false, 'defaultValue' => null, 'precision' => 12, 'scale' => 2])
			->addColumn('fee', 'decimal', ['nullable' => false, 'defaultValue' => null, 'precision' => 12, 'scale' => 2])
			->addIndex(['industry_id'], ['name' => 'industry_datas_index_industry_id_6652f52589e10', 'unique' => false])
			->addIndex(['user_id'], ['name' => 'industry_datas_index_user_id_6652f52589e2b', 'unique' => false])
			->addIndex(['portfolio_id'], ['name' => 'industry_datas_index_portfolio_id_6652f52589e66', 'unique' => false])
			->addForeignKey(['industry_id'], 'industries', ['id'], [
				'name' => 'industry_datas_foreign_industry_id_6652f52589e16',
				'delete' => 'CASCADE',
				'update' => 'CASCADE',
				'indexCreate' => true,
			])
			->addForeignKey(['user_id'], 'users', ['id'], [
				'name' => 'industry_datas_foreign_user_id_6652f52589e30',
				'delete' => 'CASCADE',
				'update' => 'CASCADE',
				'indexCreate' => true,
			])
			->addForeignKey(['portfolio_id'], 'portfolios', ['id'], [
				'name' => 'industry_datas_foreign_portfolio_id_6652f52589e6d',
				'delete' => 'CASCADE',
				'update' => 'CASCADE',
				'indexCreate' => true,
			])
			->setPrimaryKeys(['id'])
			->create();

		$this->table('sector_datas')
			->addColumn('id', 'primary', [
				'nullable' => false,
				'defaultValue' => null,
				'size' => 11,
				'autoIncrement' => true,
				'unsigned' => false,
				'zerofill' => false,
			])
			->addColumn('sector_id', 'integer', [
				'nullable' => false,
				'defaultValue' => null,
				'size' => 11,
				'autoIncrement' => false,
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
			->addColumn('date', 'timestamp', ['nullable' => false, 'defaultValue' => null])
			->addColumn('value', 'decimal', ['nullable' => false, 'defaultValue' => null, 'precision' => 11, 'scale' => 2])
			->addColumn('transaction_value', 'decimal', [
				'nullable' => false,
				'defaultValue' => null,
				'precision' => 11,
				'scale' => 2,
			])
			->addColumn('gain', 'decimal', ['nullable' => false, 'defaultValue' => null, 'precision' => 11, 'scale' => 2])
			->addColumn('gain_percentage', 'float', ['nullable' => false, 'defaultValue' => null])
			->addColumn('gain_percentage_per_annum', 'float', ['nullable' => false, 'defaultValue' => null])
			->addColumn('realized_gain', 'decimal', ['nullable' => false, 'defaultValue' => null, 'precision' => 11, 'scale' => 2])
			->addColumn('dividend_gain', 'decimal', ['nullable' => false, 'defaultValue' => null, 'precision' => 11, 'scale' => 2])
			->addColumn('dividend_gain_percentage', 'float', ['nullable' => false, 'defaultValue' => null])
			->addColumn('dividend_gain_percentage_per_annum', 'float', ['nullable' => false, 'defaultValue' => null])
			->addColumn('fx_impact', 'decimal', ['nullable' => false, 'defaultValue' => null, 'precision' => 11, 'scale' => 2])
			->addColumn('fx_impact_percentage', 'float', ['nullable' => false, 'defaultValue' => null])
			->addColumn('fx_impact_percentage_per_annum', 'float', ['nullable' => false, 'defaultValue' => null])
			->addColumn('return', 'decimal', ['nullable' => false, 'defaultValue' => null, 'precision' => 11, 'scale' => 2])
			->addColumn('return_percentage', 'float', ['nullable' => false, 'defaultValue' => null])
			->addColumn('return_percentage_per_annum', 'float', ['nullable' => false, 'defaultValue' => null])
			->addColumn('tax', 'decimal', ['nullable' => false, 'defaultValue' => null, 'precision' => 12, 'scale' => 2])
			->addColumn('fee', 'decimal', ['nullable' => false, 'defaultValue' => null, 'precision' => 12, 'scale' => 2])
			->addIndex(['sector_id'], ['name' => 'sector_datas_index_sector_id_6652f52589e90', 'unique' => false])
			->addIndex(['user_id'], ['name' => 'sector_datas_index_user_id_6652f52589f86', 'unique' => false])
			->addIndex(['portfolio_id'], ['name' => 'sector_datas_index_portfolio_id_6652f52589fba', 'unique' => false])
			->addForeignKey(['sector_id'], 'sectors', ['id'], [
				'name' => 'sector_datas_foreign_sector_id_6652f52589ecf',
				'delete' => 'CASCADE',
				'update' => 'CASCADE',
				'indexCreate' => true,
			])
			->addForeignKey(['user_id'], 'users', ['id'], [
				'name' => 'sector_datas_foreign_user_id_6652f52589f8f',
				'delete' => 'CASCADE',
				'update' => 'CASCADE',
				'indexCreate' => true,
			])
			->addForeignKey(['portfolio_id'], 'portfolios', ['id'], [
				'name' => 'sector_datas_foreign_portfolio_id_6652f52589fc0',
				'delete' => 'CASCADE',
				'update' => 'CASCADE',
				'indexCreate' => true,
			])
			->setPrimaryKeys(['id'])
			->create();
	}

	public function down(): void
	{
		$this->table('sector_datas')->drop();
		$this->table('industry_datas')->drop();
		$this->table('country_datas')->drop();
	}
}
