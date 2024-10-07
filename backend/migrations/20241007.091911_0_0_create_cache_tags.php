<?php

declare(strict_types=1);

namespace Migration;

use Cycle\Migrations\Migration;

class CreateCacheTagsMigration extends Migration
{
	protected const DATABASE = 'default';

	public function up(): void
	{
		$this->table('cache_tags')
			->addColumn('id', 'primary', [
				'nullable' => false,
				'defaultValue' => null,
				'size' => 11,
				'autoIncrement' => true,
				'unsigned' => false,
				'zerofill' => false,
			])
			->addColumn('key', 'string', ['nullable' => false, 'defaultValue' => null, 'size' => 255])
			->addColumn('driver', 'enum', ['nullable' => false, 'defaultValue' => 'memcached', 'values' => ['memcached', 'redis']])
			->addColumn('user_id', 'integer', [
				'nullable' => true,
				'defaultValue' => null,
				'size' => 11,
				'autoIncrement' => false,
				'unsigned' => false,
				'zerofill' => false,
			])
			->addColumn('portfolio_id', 'integer', [
				'nullable' => true,
				'defaultValue' => null,
				'size' => 11,
				'autoIncrement' => false,
				'unsigned' => false,
				'zerofill' => false,
			])
			->addColumn('date', 'timestamp', ['nullable' => true, 'defaultValue' => null])
			->addIndex(['user_id'], ['name' => 'cache_tags_index_user_id_6703a78f19452', 'unique' => false])
			->addIndex(['portfolio_id'], ['name' => 'cache_tags_index_portfolio_id_6703a78f19461', 'unique' => false])
			->addForeignKey(['user_id'], 'users', ['id'], [
				'name' => 'cache_tags_foreign_user_id_6703a78f1944b',
				'delete' => 'CASCADE',
				'update' => 'CASCADE',
				'indexCreate' => true,
			])
			->addForeignKey(['portfolio_id'], 'portfolios', ['id'], [
				'name' => 'cache_tags_foreign_portfolio_id_6703a78f1945e',
				'delete' => 'CASCADE',
				'update' => 'CASCADE',
				'indexCreate' => true,
			])
			->setPrimaryKeys(['id'])
			->addIndex(['driver', 'key'], ['name' => 'cache_tags_index_driver_key', 'unique' => true])
			->addIndex(['date'], ['date' => 'cache_tags_index_date', 'unique' => false])
			->create();
	}

	public function down(): void
	{
		$this->table('cache_tags')->drop();
	}
}
