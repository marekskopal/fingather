<?php

declare(strict_types=1);

namespace Migrations;

use MarekSkopal\ORM\Enum\Type;
use MarekSkopal\ORM\Migrations\Migration\Migration;
use MarekSkopal\ORM\Migrations\Migration\Query\Enum\ReferenceOptionEnum;

final class TickerDcfValuationMigration extends Migration
{
	public function up(): void
	{
		$this->table('ticker_dcf_history_points')
			->addColumn('id', Type::Int, autoincrement: true, primary: true)
			->addColumn('ticker_id', Type::Int, size: 11)
			->addColumn('fiscal_date', Type::Date)
			->addColumn('free_cash_flow', Type::BigInt, nullable: true)
			->addColumn('revenue', Type::BigInt, nullable: true)
			->addIndex(['ticker_id', 'fiscal_date'], 'ticker_dcf_history_points_unique', true)
			->addForeignKey('ticker_id', 'tickers', 'id', 'ticker_dcf_history_points_ticker_id_fk', onDelete: ReferenceOptionEnum::Cascade)
			->create();
	}

	public function down(): void
	{
		$this->table('ticker_dcf_history_points')->drop();
	}
}
