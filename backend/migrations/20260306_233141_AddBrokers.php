<?php

declare(strict_types=1);

namespace Migrations;

use MarekSkopal\ORM\Enum\Type;
use MarekSkopal\ORM\Migrations\Migration\Migration;

final class AddBrokersMigration extends Migration
{
	public function up(): void
	{
		$this->table('brokers')
			->alterColumn(
				'import_type',
				Type::Enum,
				enum: ['Trading212', 'InteractiveBrokers', 'Xtb', 'Etoro', 'Revolut', 'Anycoin', 'Degiro', 'Portu', 'Coinbase', 'Binance', 'FioBanka', 'Patria', 'EtoroApi', 'Lightyear', 'Freetrade', 'Schwab'],
			)
			->alter();
	}

	public function down(): void
	{
		$this->table('brokers')
			->alterColumn(
				'import_type',
				Type::Enum,
				enum: ['Trading212', 'InteractiveBrokers', 'Xtb', 'Etoro', 'Revolut', 'Anycoin', 'Degiro', 'Portu', 'Coinbase', 'Binance', 'FioBanka', 'Patria', 'EtoroApi'],
			)
			->alter();
	}
}
