<?php

declare(strict_types=1);

namespace Migration;

use Cycle\Migrations\Migration;

final class TickerIsinIndexMigration extends Migration
{
	protected const DATABASE = 'default';

	public function up(): void
	{
		$this->table('tickers')
			->addIndex(['isin'], ['name' => 'tickers_index_isin', 'unique' => false])
			->update();
	}

	public function down(): void
	{
		$this->table('brokers')
			->dropIndex(['isin'])
			->update();
	}
}
