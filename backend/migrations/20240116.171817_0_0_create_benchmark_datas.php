<?php

declare(strict_types=1);

namespace Migration;

use Cycle\Migrations\Migration;

class CreateBenchmarkDatas extends Migration
{
	protected const DATABASE = 'default';

	public function up(): void
	{
		$this->table('benchmark_datas')
			->addColumn('id', 'primary', ['nullable' => false, 'default' => null, 'size' => 11])
			->addColumn('user_id', 'integer', ['nullable' => false, 'default' => null, 'size' => 11])
			->addColumn('asset_id', 'integer', ['nullable' => false, 'default' => null, 'size' => 11])
			->addColumn('date', 'timestamp', ['nullable' => false, 'default' => null])
			->addColumn('value', 'decimal', ['nullable' => false, 'default' => null, 'scale' => 2, 'precision' => 11])
			->addIndex(['user_id'], ['name' => 'benchmark_datas_index_user_id_65a6ba59cf8ba', 'unique' => false])
			->addIndex(['asset_id'], ['name' => 'benchmark_datas_index_asset_id_65a6ba59cf8d1', 'unique' => false])
			->addForeignKey(['user_id'], 'users', ['id'], [
				'name' => 'benchmark_datas_foreign_user_id_65a6ba59cf8c1',
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
			->addIndex(['date', 'asset_id'], ['name' => 'benchmark_datas_index_date_asset_id', 'unique' => true])
			->setPrimaryKeys(['id'])
			->create();
	}

	public function down(): void
	{
		$this->table('benchmark_datas')->drop();
	}
}
