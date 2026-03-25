<?php

declare(strict_types=1);

namespace Migrations;

use MarekSkopal\ORM\Enum\Type;
use MarekSkopal\ORM\Migrations\Migration\Migration;
use MarekSkopal\ORM\Migrations\Migration\Query\Enum\ReferenceOptionEnum;

final class GoalDcaPlanMigration extends Migration
{
	public function up(): void
	{
		$this->table('goals')
			->addColumn('dca_plan_id', Type::Int, nullable: true, size: 11)
			->addIndex(['dca_plan_id'], 'goals_dca_plan_id_index', false)
			->addForeignKey('dca_plan_id', 'dca_plans', 'id', 'goals_dca_plan_id_dca_plans_id_fk', onDelete: ReferenceOptionEnum::SetNull)
			->alter();
	}

	public function down(): void
	{
		$this->table('goals')
			->dropForeignKey('goals_dca_plan_id_dca_plans_id_fk')
			->dropIndex('goals_dca_plan_id_index')
			->dropColumn('dca_plan_id')
			->alter();
	}
}
