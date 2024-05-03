<?php

declare(strict_types=1);

namespace Migration;

use Cycle\Migrations\Migration;

final class TaxFeeMigration extends Migration
{
	protected const DATABASE = 'default';

	public function up(): void
	{
		$this->database()->query('TRUNCATE `asset_datas`');
		$this->table('asset_datas')
			->addColumn('tax', 'decimal', ['nullable' => false, 'defaultValue' => null, 'precision' => 12, 'scale' => 2])
			->addColumn('tax_default_currency', 'decimal', [
				'nullable' => false,
				'defaultValue' => null,
				'precision' => 12,
				'scale' => 2,
			])
			->addColumn('fee', 'decimal', ['nullable' => false, 'defaultValue' => null, 'precision' => 12, 'scale' => 2])
			->addColumn('fee_default_currency', 'decimal', [
				'nullable' => false,
				'defaultValue' => null,
				'precision' => 12,
				'scale' => 2,
			])
			->update();

		$this->database()->query('TRUNCATE `group_datas`');
		$this->table('group_datas')
			->addColumn('tax', 'decimal', ['nullable' => false, 'defaultValue' => null, 'precision' => 12, 'scale' => 2])
			->addColumn('fee', 'decimal', ['nullable' => false, 'defaultValue' => null, 'precision' => 12, 'scale' => 2])
			->update();

		$this->database()->query('TRUNCATE `portfolio_datas`');
		$this->table('portfolio_datas')
			->addColumn('tax', 'decimal', ['nullable' => false, 'defaultValue' => null, 'precision' => 12, 'scale' => 2])
			->addColumn('fee', 'decimal', ['nullable' => false, 'defaultValue' => null, 'precision' => 12, 'scale' => 2])
			->update();
	}

	public function down(): void
	{
		$this->table('portfolio_datas')
			->dropColumn('tax')
			->dropColumn('fee')
			->update();
		$this->table('group_datas')
			->dropColumn('tax')
			->dropColumn('fee')
			->update();
		$this->table('asset_datas')
			->dropColumn('tax')
			->dropColumn('tax_default_currency')
			->dropColumn('fee')
			->dropColumn('fee_default_currency')
			->update();
	}
}
