<?php

declare(strict_types=1);

namespace Migration;

use Cycle\Migrations\Migration;

class TransactionTickerPriceDefaultPriceMigration extends Migration
{
	protected const DATABASE = 'default';

	public function up(): void
	{
		$this->table('transactions')
			->addColumn('price_ticker_currency', 'decimal', [
				'nullable' => false,
				'defaultValue' => null,
				'precision' => 9,
				'scale' => 2,
			])
			->addColumn('price_default_currency', 'decimal', [
				'nullable' => false,
				'defaultValue' => null,
				'precision' => 9,
				'scale' => 2,
			])
			->addColumn('tax_ticker_currency', 'decimal', [
				'nullable' => false,
				'defaultValue' => null,
				'precision' => 9,
				'scale' => 2,
			])
			->addColumn('tax_default_currency', 'decimal', [
				'nullable' => false,
				'defaultValue' => null,
				'precision' => 9,
				'scale' => 2,
			])
			->addColumn('fee_ticker_currency', 'decimal', [
				'nullable' => false,
				'defaultValue' => null,
				'precision' => 9,
				'scale' => 2,
			])
			->addColumn('fee_default_currency', 'decimal', [
				'nullable' => false,
				'defaultValue' => null,
				'precision' => 9,
				'scale' => 2,
			])
			->update();
	}

	public function down(): void
	{
		$this->table('transactions')
			->dropColumn('price_ticker_currency')
			->dropColumn('price_default_currency')
			->dropColumn('tax_ticker_currency')
			->dropColumn('tax_default_currency')
			->dropColumn('fee_ticker_currency')
			->dropColumn('fee_default_currency')
			->update();
	}
}
