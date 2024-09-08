<?php

declare(strict_types=1);

namespace Migration;

use Cycle\Migrations\Migration;

final class PortuImportTypeMigration extends Migration
{
	protected const DATABASE = 'default';

	public function up(): void
	{
		$this->table('brokers')
			->alterColumn('import_type', 'enum', [
				'nullable' => false,
				'defaultValue' => null,
				'values' => ['Trading212', 'InteractiveBrokers', 'Xtb', 'Etoro', 'Revolut', 'Anycoin', 'Degiro', 'Portu'],
			])
			->update();
	}

	public function down(): void
	{
		$this->table('brokers')
			->alterColumn('import_type', 'enum', [
				'nullable' => false,
				'defaultValue' => null,
				'values' => ['Trading212', 'InteractiveBrokers', 'Xtb', 'Etoro', 'Revolut', 'Anycoin', 'Degiro'],
			])
			->update();
	}
}
