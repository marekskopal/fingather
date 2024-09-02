<?php

declare(strict_types=1);

namespace Migration;

use Cycle\Migrations\Migration;

class ApiImportMigration extends Migration
{
	protected const DATABASE = 'default';

	public function up(): void
	{
		$this->table('api_keys')
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
			->addColumn('type', 'enum', ['nullable' => false, 'defaultValue' => null, 'values' => ['Trading212']])
			->addColumn('api_key', 'string', ['nullable' => false, 'defaultValue' => null, 'size' => 255])
			->addIndex(['user_id'], ['name' => 'api_keys_index_user_id_66d59c44799db', 'unique' => false])
			->addIndex(['portfolio_id'], ['name' => 'api_keys_index_portfolio_id_66d59c4479a03', 'unique' => false])
			->addForeignKey(['user_id'], 'users', ['id'], [
				'name' => 'api_keys_foreign_user_id_66d59c44799ef',
				'delete' => 'CASCADE',
				'update' => 'CASCADE',
				'indexCreate' => true,
			])
			->addForeignKey(['portfolio_id'], 'portfolios', ['id'], [
				'name' => 'api_keys_foreign_portfolio_id_66d59c4479a06',
				'delete' => 'CASCADE',
				'update' => 'CASCADE',
				'indexCreate' => true,
			])
			->setPrimaryKeys(['id'])
			->create();
		$this->table('api_imports')
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
			->addColumn('api_key_id', 'integer', [
				'nullable' => false,
				'defaultValue' => null,
				'size' => 11,
				'autoIncrement' => false,
				'unsigned' => false,
				'zerofill' => false,
			])
			->addColumn('status', 'enum', [
				'nullable' => false,
				'defaultValue' => null,
				'values' => ['New', 'Waiting', 'InProgress', 'Finished', 'Error'],
			])
			->addColumn('date_from', 'timestamp', ['nullable' => false, 'defaultValue' => null])
			->addColumn('date_to', 'timestamp', ['nullable' => false, 'defaultValue' => null])
			->addColumn('report_id', 'integer', [
				'nullable' => true,
				'defaultValue' => null,
				'size' => 11,
				'autoIncrement' => false,
				'unsigned' => false,
				'zerofill' => false,
			])
			->addColumn('error', 'string', [
				'nullable' => true,
				'defaultValue' => null,
				'size' => 255,
			])
			->addIndex(['user_id'], ['name' => 'api_imports_index_user_id_66d59c4479777', 'unique' => false])
			->addIndex(['portfolio_id'], ['name' => 'api_imports_index_portfolio_id_66d59c44797a3', 'unique' => false])
			->addIndex(['api_key_id'], ['name' => 'api_imports_index_apikey_id_66d59c44797fd', 'unique' => false])
			->addForeignKey(['user_id'], 'users', ['id'], [
				'name' => 'api_imports_foreign_user_id_66d59c447977e',
				'delete' => 'CASCADE',
				'update' => 'CASCADE',
				'indexCreate' => true,
			])
			->addForeignKey(['portfolio_id'], 'portfolios', ['id'], [
				'name' => 'api_imports_foreign_portfolio_id_66d59c44797b8',
				'delete' => 'CASCADE',
				'update' => 'CASCADE',
				'indexCreate' => true,
			])
			->addForeignKey(['api_key_id'], 'api_keys', ['id'], [
				'name' => 'api_imports_foreign_apikey_id_66d59c4479802',
				'delete' => 'CASCADE',
				'update' => 'CASCADE',
				'indexCreate' => true,
			])
			->setPrimaryKeys(['id'])
			->create();
	}

	public function down(): void
	{
		$this->table('api_imports')->drop();
		$this->table('api_keys')->drop();
	}
}
