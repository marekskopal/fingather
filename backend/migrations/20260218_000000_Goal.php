<?php

declare(strict_types=1);

namespace Migrations;

use MarekSkopal\ORM\Enum\Type;
use MarekSkopal\ORM\Migrations\Migration\Migration;

final class GoalMigration extends Migration
{
	public function up(): void
	{
		$this->table('goals')
			->addColumn('id', Type::Int, autoincrement: true, primary: true)
			->addColumn('user_id', Type::Int, size: 11)
			->addColumn('portfolio_id', Type::Int, size: 11)
			->addColumn('type', Type::Enum, enum: ['PortfolioValue', 'ReturnPercentage', 'InvestedAmount'])
			->addColumn('target_value', Type::Decimal, precision: 18, scale: 8)
			->addColumn('deadline', Type::Timestamp, nullable: true)
			->addColumn('is_active', Type::Boolean, default: true)
			->addColumn('achieved_at', Type::Timestamp, nullable: true)
			->addColumn('created_at', Type::Timestamp)
			->addIndex(['user_id'], 'goals_user_id_index', false)
			->addIndex(['portfolio_id'], 'goals_portfolio_id_index', false)
			->addIndex(['is_active'], 'goals_is_active_index', false)
			->addForeignKey('user_id', 'users', 'id', 'goals_user_id_users_id_fk')
			->addForeignKey('portfolio_id', 'portfolios', 'id', 'goals_portfolio_id_portfolios_id_fk')
			->create();
	}

	public function down(): void
	{
		$this->table('goals')
			->drop();
	}
}
