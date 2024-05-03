<?php

declare(strict_types=1);

namespace Migration;

use Cycle\Migrations\Migration;

final class TickerProfileMigration extends Migration
{
	protected const DATABASE = 'default';

	public function up(): void
	{
		$this->table('tickers')
			->addColumn('type', 'enum', ['nullable' => false, 'defaultValue' => 'Stock', 'values' => ['Stock', 'Etf', 'Crypto']])
			->addColumn('sector', 'string', ['nullable' => true, 'defaultValue' => null, 'size' => 255])
			->addColumn('industry', 'string', ['nullable' => true, 'defaultValue' => null, 'size' => 255])
			->addColumn('website', 'string', ['nullable' => true, 'defaultValue' => null, 'size' => 255])
			->addColumn('description', 'text', ['nullable' => true, 'defaultValue' => null])
			->addColumn('country', 'string', ['nullable' => true, 'defaultValue' => null, 'size' => 255])
			->update();
	}

	public function down(): void
	{
		$this->table('tickers')
			->dropColumn('type')
			->dropColumn('sector')
			->dropColumn('industry')
			->dropColumn('website')
			->dropColumn('description')
			->dropColumn('country')
			->update();
	}
}
