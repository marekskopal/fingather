<?php

declare(strict_types=1);

namespace Migration;

use Cycle\Migrations\Migration;

final class CoinbaseBinanceImportTypeMigration extends Migration
{
	protected const DATABASE = 'default';

	public function up(): void
	{
		$this->table('brokers')
			->alterColumn('import_type', 'enum', [
				'nullable' => false,
				'defaultValue' => null,
				'values' => ['Trading212', 'InteractiveBrokers', 'Xtb', 'Etoro', 'Revolut', 'Anycoin', 'Degiro', 'Portu', 'Coinbase', 'Binance'],
			])
			->update();
	}

	public function down(): void
	{
		$this->table('brokers')
			->alterColumn('import_type', 'enum', [
				'nullable' => false,
				'defaultValue' => null,
				'values' => ['Trading212', 'InteractiveBrokers', 'Xtb', 'Etoro', 'Revolut', 'Anycoin', 'Degiro', 'Portu'],
			])
			->update();
	}
}
