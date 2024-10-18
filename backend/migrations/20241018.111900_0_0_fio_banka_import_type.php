<?php

declare(strict_types=1);

namespace Migration;

use Cycle\Migrations\Migration;

final class FioBankaImportTypeMigration extends Migration
{
	protected const DATABASE = 'default';

	public function up(): void
	{
		$this->table('brokers')
			->alterColumn('import_type', 'enum', [
				'nullable' => false,
				'defaultValue' => null,
				'values' => ['Trading212', 'InteractiveBrokers', 'Xtb', 'Etoro', 'Revolut', 'Anycoin', 'Degiro', 'Portu', 'Coinbase', 'Binance', 'FioBanka'],
			])
			->update();

		$this->table('import_files')
			->alterColumn('contents', 'longBinary', ['nullable' => false, 'defaultValue' => null])
			->update();
	}

	public function down(): void
	{
		$this->table('import_files')
			->alterColumn('contents', 'longText', ['nullable' => false, 'defaultValue' => null])
			->update();

		$this->table('brokers')
			->alterColumn('import_type', 'enum', [
				'nullable' => false,
				'defaultValue' => null,
				'values' => ['Trading212', 'InteractiveBrokers', 'Xtb', 'Etoro', 'Revolut', 'Anycoin', 'Degiro', 'Portu', 'Coinbase', 'Binance'],
			])
			->update();
	}
}
