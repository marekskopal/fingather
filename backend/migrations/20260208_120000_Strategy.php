<?php

declare(strict_types=1);

namespace Migrations;

use MarekSkopal\ORM\Enum\Type;
use MarekSkopal\ORM\Migrations\Migration\Migration;
use MarekSkopal\ORM\Migrations\Migration\Query\Enum\ReferenceOptionEnum;

final class StrategyMigration extends Migration
{
	public function up(): void
	{
		$this->table('strategies')
			->addColumn('id', Type::Int, autoincrement: true, primary: true)
			->addColumn('user_id', Type::Int, size: 11)
			->addColumn('portfolio_id', Type::Int, size: 11)
			->addColumn('name', Type::String)
			->addColumn('is_default', Type::Boolean, default: false)
			->addIndex(['user_id'], 'strategies_user_id_index', false)
			->addIndex(['portfolio_id'], 'strategies_portfolio_id_index', false)
			->addForeignKey('user_id', 'users', 'id', 'strategies_user_id_users_id_fk')
			->addForeignKey('portfolio_id', 'portfolios', 'id', 'strategies_portfolio_id_portfolios_id_fk')
			->create();

		$this->table('strategy_items')
			->addColumn('id', Type::Int, autoincrement: true, primary: true)
			->addColumn('strategy_id', Type::Int, size: 11)
			->addColumn('asset_id', Type::Int, nullable: true, size: 11)
			->addColumn('group_id', Type::Int, nullable: true, size: 11)
			->addColumn('percentage', Type::Decimal, precision: 5, scale: 2)
			->addIndex(['strategy_id'], 'strategy_items_strategy_id_index', false)
			->addIndex(['asset_id'], 'strategy_items_asset_id_index', false)
			->addIndex(['group_id'], 'strategy_items_group_id_index', false)
			->addForeignKey('strategy_id', 'strategies', 'id', 'strategy_items_strategy_id_strategies_id_fk')
			->addForeignKey('asset_id', 'assets', 'id', 'strategy_items_asset_id_assets_id_fk', onDelete: ReferenceOptionEnum::SetNull)
			->addForeignKey('group_id', 'groups', 'id', 'strategy_items_group_id_groups_id_fk', onDelete: ReferenceOptionEnum::SetNull)
			->create();
	}

	public function down(): void
	{
		$this->table('strategy_items')
			->drop();

		$this->table('strategies')
			->drop();
	}
}
