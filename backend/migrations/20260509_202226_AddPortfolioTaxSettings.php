<?php

declare(strict_types=1);

namespace Migrations;

use FinGather\Model\Entity\Enum\CostBasisMethodEnum;
use FinGather\Model\Entity\Enum\TaxJurisdictionEnum;
use MarekSkopal\ORM\Enum\Type;
use MarekSkopal\ORM\Migrations\Migration\Migration;

final class AddPortfolioTaxSettingsMigration extends Migration
{
	public function up(): void
	{
		$this->table('portfolios')
			->addColumn(
				'tax_jurisdiction',
				Type::Enum,
				enum: array_column(TaxJurisdictionEnum::cases(), 'value'),
				default: TaxJurisdictionEnum::Generic->value,
			)
			->addColumn(
				'cost_basis_method',
				Type::Enum,
				enum: array_column(CostBasisMethodEnum::cases(), 'value'),
				default: CostBasisMethodEnum::Fifo->value,
			)
			->addColumn('estimated_tax_rate', Type::Decimal, precision: 5, scale: 4, nullable: true, default: null)
			->alter();

		$pdo = $this->databaseProvider->getDatabase()->getPdo();
		$pdo->query(
			"UPDATE portfolios p
				JOIN users u ON u.id = p.user_id
				SET p.tax_jurisdiction = '" . TaxJurisdictionEnum::CzechRepublic->value . "',
					p.estimated_tax_rate = 0.1500
				WHERE u.locale = 'cs'",
		);
	}

	public function down(): void
	{
		$this->table('portfolios')
			->dropColumn('tax_jurisdiction')
			->dropColumn('cost_basis_method')
			->dropColumn('estimated_tax_rate')
			->alter();
	}
}
