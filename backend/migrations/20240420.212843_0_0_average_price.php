<?php

declare(strict_types=1);

namespace Migration;

use Cycle\Migrations\Migration;

class AveragePriceMigration extends Migration
{
	protected const DATABASE = 'default';

	public function up(): void
	{
		$this->database()->query('TRUNCATE `asset_datas`');
		$this->table('asset_datas')
			->addColumn('average_price', 'decimal', ['nullable' => false, 'defaultValue' => null, 'precision' => 12, 'scale' => 2])
			->addColumn('average_price_default_currency', 'decimal', [
				'nullable' => false,
				'defaultValue' => null,
				'precision' => 12,
				'scale' => 2,
			])
			->update();
	}

	public function down(): void
	{
		$this->table('asset_datas')
			->dropColumn('average_price')
			->dropColumn('average_price_default_currency')
			->update();
	}
}
