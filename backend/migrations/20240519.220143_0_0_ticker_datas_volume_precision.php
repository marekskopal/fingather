<?php

declare(strict_types=1);

namespace Migration;

use Cycle\Migrations\Migration;

class TickerDatasVolumePrecisionMigration extends Migration
{
	protected const DATABASE = 'default';

	public function up(): void
	{
		$this->table('ticker_datas')
			->alterColumn('volume', 'decimal', ['nullable' => false, 'defaultValue' => null, 'precision' => 22, 'scale' => 10])
			->update();
	}

	public function down(): void
	{
		$this->table('ticker_datas')
			->alterColumn('volume', 'decimal', ['nullable' => false, 'defaultValue' => null, 'precision' => 20, 'scale' => 10])
			->update();
	}
}
