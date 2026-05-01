<?php

declare(strict_types=1);

namespace Migrations;

use FinGather\Model\Entity\Enum\TickerTypeEnum;
use MarekSkopal\ORM\Enum\Type;
use MarekSkopal\ORM\Migrations\Migration\Migration;

final class ProxyAssetMigration extends Migration
{
	public function up(): void
	{
		$this->table('proxy_assets')
			->addColumn('id', Type::Int, autoincrement: true, primary: true)
			->addColumn('ticker_type', Type::Enum, enum: array_column(TickerTypeEnum::cases(), 'value'))
			->addColumn('ticker_id', Type::Int, size: 11)
			->addIndex(['ticker_type'], 'proxy_assets_ticker_type_index', true)
			->addForeignKey('ticker_id', 'tickers', 'id', 'proxy_assets_ticker_id_tickers_id_fk')
			->create();
	}

	public function down(): void
	{
		$this->table('proxy_assets')
			->drop();
	}
}
