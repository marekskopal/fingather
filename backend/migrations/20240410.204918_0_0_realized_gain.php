<?php

declare(strict_types=1);

namespace Migration;

use Cycle\Migrations\Migration;

class RealizedGainMigration extends Migration
{
	protected const DATABASE = 'default';

	public function up(): void
	{
		$this->database()->query('TRUNCATE `asset_datas`');
		$this->table('asset_datas')
			->addColumn('realized_gain', 'decimal', ['nullable' => false, 'defaultValue' => null, 'precision' => 12, 'scale' => 2])
			->addColumn('realized_gain_default_currency', 'decimal', [
				'nullable' => false,
				'defaultValue' => null,
				'precision' => 12,
				'scale' => 2,
			])
			->update();

		$this->database()->query('TRUNCATE `group_datas`');
		$this->table('group_datas')
			->addColumn('realized_gain', 'decimal', ['nullable' => false, 'defaultValue' => null, 'precision' => 11, 'scale' => 2])
			->update();

		$this->database()->query('TRUNCATE `portfolio_datas`');
		$this->table('portfolio_datas')
			->addColumn('realized_gain', 'decimal', ['nullable' => false, 'defaultValue' => null, 'precision' => 11, 'scale' => 2])
			->update();
	}

	public function down(): void
	{
		$this->table('portfolio_datas')
			->dropColumn('realized_gain')
			->update();

		$this->table('group_datas')
			->dropColumn('realized_gain')
			->update();

		$this->table('asset_datas')
			->dropColumn('realized_gain')
			->dropColumn('realized_gain_default_currency')
			->update();
	}
}
