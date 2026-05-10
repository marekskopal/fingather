<?php

declare(strict_types=1);

namespace Migrations;

use FinGather\Model\Entity\Enum\TaxJurisdictionEnum;
use MarekSkopal\ORM\Enum\Type;
use MarekSkopal\ORM\Migrations\Migration\Migration;

final class ExtendTaxJurisdictionEnumMigration extends Migration
{
	public function up(): void
	{
		$this->table('portfolios')
			->alterColumn(
				'tax_jurisdiction',
				Type::Enum,
				enum: array_column(TaxJurisdictionEnum::cases(), 'value'),
				default: TaxJurisdictionEnum::Generic->value,
			)
			->alter();
	}

	public function down(): void
	{
		$this->table('portfolios')
			->alterColumn(
				'tax_jurisdiction',
				Type::Enum,
				enum: ['CzechRepublic', 'Generic'],
				default: 'Generic',
			)
			->alter();
	}
}
