<?php

declare(strict_types=1);

namespace Migrations;

use MarekSkopal\ORM\Enum\Type;
use MarekSkopal\ORM\Migrations\Migration\Migration;

final class AddApiKeyUserKeyMigration extends Migration
{
	public function up(): void
	{
		$this->table('api_keys')
			->addColumn('user_key', Type::Text, nullable: true)
			->alter();

		$this->table('api_keys')
			->alterColumn(
				'type',
				Type::Enum,
				enum: ['Trading212', 'Etoro'],
			)
			->alter();

		$this->table('brokers')
			->alterColumn(
				'import_type',
				Type::Enum,
				enum: ['Trading212', 'InteractiveBrokers', 'Xtb', 'Etoro', 'Revolut', 'Anycoin', 'Degiro', 'Portu', 'Coinbase', 'Binance', 'FioBanka', 'Patria', 'EtoroApi'],
			)
			->alter();
	}

	public function down(): void
	{
		$this->table('api_keys')
			->dropColumn('user_key')
			->alter();

		$this->table('api_keys')
			->alterColumn(
				'type',
				Type::Enum,
				enum: ['Trading212'],
			)
			->alter();

		$this->table('brokers')
			->alterColumn(
				'import_type',
				Type::Enum,
				enum: ['Trading212', 'InteractiveBrokers', 'Xtb', 'Etoro', 'Revolut', 'Anycoin', 'Degiro', 'Portu', 'Coinbase', 'Binance', 'FioBanka', 'Patria'],
			)
			->alter();
	}
}
