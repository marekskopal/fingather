<?php

declare(strict_types=1);

namespace Migrations;

use MarekSkopal\ORM\Enum\Type;
use MarekSkopal\ORM\Migrations\Migration\Migration;
use MarekSkopal\ORM\Migrations\Migration\Query\Enum\ReferenceOptionEnum;

final class DcaPlanMigration extends Migration
{
	public function up(): void
	{
		$this->table('dca_plans')
			->addColumn('id', Type::Int, autoincrement: true, primary: true)
			->addColumn('user_id', Type::Int, size: 11)
			->addColumn('target_type', Type::Enum, enum: ['Portfolio', 'Asset', 'Group', 'Strategy'])
			->addColumn('portfolio_id', Type::Int, size: 11)
			->addColumn('asset_id', Type::Int, nullable: true, size: 11)
			->addColumn('group_id', Type::Int, nullable: true, size: 11)
			->addColumn('strategy_id', Type::Int, nullable: true, size: 11)
			->addColumn('amount', Type::Decimal, precision: 18, scale: 8)
			->addColumn('currency_id', Type::Int, size: 11)
			->addColumn('interval_months', Type::Int, default: 1)
			->addColumn('start_date', Type::Timestamp)
			->addColumn('end_date', Type::Timestamp, nullable: true)
			->addColumn('created_at', Type::Timestamp)
			->addIndex(['user_id'], 'dca_plans_user_id_index', false)
			->addIndex(['portfolio_id'], 'dca_plans_portfolio_id_index', false)
			->addIndex(['asset_id'], 'dca_plans_asset_id_index', false)
			->addForeignKey('user_id', 'users', 'id', 'dca_plans_user_id_users_id_fk')
			->addForeignKey('portfolio_id', 'portfolios', 'id', 'dca_plans_portfolio_id_portfolios_id_fk')
			->addForeignKey('asset_id', 'assets', 'id', 'dca_plans_asset_id_assets_id_fk', onDelete: ReferenceOptionEnum::SetNull)
			->addForeignKey('group_id', 'groups', 'id', 'dca_plans_group_id_groups_id_fk', onDelete: ReferenceOptionEnum::SetNull)
			->addForeignKey(
				'strategy_id',
				'strategies',
				'id',
				'dca_plans_strategy_id_strategies_id_fk',
				onDelete: ReferenceOptionEnum::SetNull,
			)
			->addForeignKey('currency_id', 'currencies', 'id', 'dca_plans_currency_id_currencies_id_fk')
			->create();
	}

	public function down(): void
	{
		$this->table('dca_plans')->drop();
	}
}
