<?php

declare(strict_types=1);

namespace Migrations;

use MarekSkopal\ORM\Enum\Type;
use MarekSkopal\ORM\Migrations\Migration\Migration;

final class BenchmarkAssetMigration extends Migration
{
	public function up(): void
	{
		$this->table('benchmark_assets')
			->addColumn('id', Type::Int, autoincrement: true, primary: true)
			->addColumn('ticker_id', Type::Int, size: 11)
			->addIndex(['ticker_id'], 'benchmark_assets_ticker_id_index', true)
			->addForeignKey('ticker_id', 'tickers', 'id', 'benchmark_assets_ticker_id_tickers_id_fk')
			->create();
	}

	public function down(): void
	{
		$this->table('benchmark_assets')
			->drop();
	}
}
