<?php

declare(strict_types=1);

namespace Migrations;

use MarekSkopal\ORM\Enum\Type;
use MarekSkopal\ORM\Migrations\Migration\Migration;

final class PriceAlertMigration extends Migration
{
	public function up(): void
	{
		$this->table('price_alerts')
			->addColumn('id', Type::Int, autoincrement: true, primary: true)
			->addColumn('user_id', Type::Int, size: 11)
			->addColumn('portfolio_id', Type::Int, size: 11, nullable: true)
			->addColumn('ticker_id', Type::Int, size: 11, nullable: true)
			->addColumn('type', Type::Enum, enum: ['Price', 'Portfolio'])
			->addColumn('condition_type', Type::Enum, enum: ['Above', 'Below'])
			->addColumn('target_value', Type::Decimal, precision: 18, scale: 8)
			->addColumn('recurrence', Type::Enum, enum: ['OneTime', 'Recurring'])
			->addColumn('cooldown_hours', Type::Int, default: 24)
			->addColumn('is_active', Type::Boolean, default: true)
			->addColumn('last_triggered_at', Type::Timestamp, nullable: true)
			->addColumn('created_at', Type::Timestamp)
			->addIndex(['user_id'], 'price_alerts_user_id_index', false)
			->addIndex(['portfolio_id'], 'price_alerts_portfolio_id_index', false)
			->addIndex(['ticker_id'], 'price_alerts_ticker_id_index', false)
			->addIndex(['is_active'], 'price_alerts_is_active_index', false)
			->addForeignKey('user_id', 'users', 'id', 'price_alerts_user_id_users_id_fk')
			->addForeignKey('portfolio_id', 'portfolios', 'id', 'price_alerts_portfolio_id_portfolios_id_fk')
			->addForeignKey('ticker_id', 'tickers', 'id', 'price_alerts_ticker_id_tickers_id_fk')
			->create();
	}

	public function down(): void
	{
		$this->table('price_alerts')
			->drop();
	}
}
