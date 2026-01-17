<?php

declare(strict_types=1);

namespace Migrations;

use MarekSkopal\ORM\Enum\Type;
use MarekSkopal\ORM\Migrations\Migration\Migration;

final class TransactionDecimalMigration extends Migration
{
	public function up(): void
	{
		$this->table('transactions')
			->alterColumn('price', Type::Decimal, precision: 11, scale: 2)
			->alterColumn('price_ticker_currency', Type::Decimal, precision: 11, scale: 2)
			->alterColumn('price_default_currency', Type::Decimal, precision: 11, scale: 2)
			->alterColumn('tax', Type::Decimal, precision: 11, scale: 2)
			->alterColumn('tax_ticker_currency', Type::Decimal, precision: 11, scale: 2)
			->alterColumn('tax_default_currency', Type::Decimal, precision: 11, scale: 2)
			->alterColumn('fee', Type::Decimal, precision: 11, scale: 2)
			->alterColumn('fee_ticker_currency', Type::Decimal, precision: 11, scale: 2)
			->alterColumn('fee_default_currency', Type::Decimal, precision: 11, scale: 2)
			->alter();
	}

	public function down(): void
	{
		$this->table('transactions')
			->alterColumn('fee_default_currency', Type::Decimal, size: 0, precision: 9, scale: 2)
			->alterColumn('fee_ticker_currency', Type::Decimal, size: 0, precision: 9, scale: 2)
			->alterColumn('fee', Type::Decimal, size: 0, precision: 9, scale: 2)
			->alterColumn('tax_default_currency', Type::Decimal, size: 0, precision: 9, scale: 2)
			->alterColumn('tax_ticker_currency', Type::Decimal, size: 0, precision: 9, scale: 2)
			->alterColumn('tax', Type::Decimal, size: 0, precision: 9, scale: 2)
			->alterColumn('price_default_currency', Type::Decimal, size: 0, precision: 9, scale: 2)
			->alterColumn('price_ticker_currency', Type::Decimal, size: 0, precision: 9, scale: 2)
			->alterColumn('price', Type::Decimal, size: 0, precision: 9, scale: 2)
			->alter();
	}
}
