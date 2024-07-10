<?php

declare(strict_types=1);

namespace Migration;

use Cycle\Migrations\Migration;

class TransactionsBrokerIdNullMigration extends Migration
{
	protected const DATABASE = 'default';

	public function up(): void
	{
		$this->table('transactions')
			->alterColumn('broker_id', 'integer', [
				'nullable' => true,
				'defaultValue' => null,
				'size' => 11,
				'autoIncrement' => false,
				'unsigned' => false,
				'zerofill' => false,
			])
			->update();
	}

	public function down(): void
	{
		$this->table('transactions')
			->alterColumn('broker_id', 'integer', [
				'nullable' => false,
				'defaultValue' => null,
				'size' => 11,
				'autoIncrement' => false,
				'unsigned' => false,
				'zerofill' => false,
			])
			->update();
	}
}
