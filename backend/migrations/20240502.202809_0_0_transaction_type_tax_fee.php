<?php

declare(strict_types=1);

namespace Migration;

use Cycle\Migrations\Migration;

class TransactionTypeTaxFee extends Migration
{
	protected const DATABASE = 'default';

	public function up(): void
	{
		$this->table('transactions')
			->alterColumn('action_type', 'enum', [
				'nullable' => false,
				'defaultValue' => null,
				'values' => ['Undefined', 'Buy', 'Sell', 'Dividend', 'Tax', 'Fee', 'DividendTax'],
			])
			->update();
	}

	public function down(): void
	{
		$this->table('transactions')
			->alterColumn('action_type', 'enum', [
				'nullable' => false,
				'defaultValue' => null,
				'values' => ['Undefined', 'Buy', 'Sell', 'Dividend'],
			])
			->update();
	}
}
