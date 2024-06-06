<?php

declare(strict_types=1);

namespace Migration;

use Cycle\Migrations\Migration;

class DividendYieldRenameMigration extends Migration
{
	protected const DATABASE = 'default';

	public function up(): void
	{
		$this->table('asset_datas')
			->renameColumn('dividend_gain', 'dividend_yield')
			->renameColumn('dividend_gain_default_currency', 'dividend_yield_default_currency')
			->renameColumn('dividend_gain_percentage', 'dividend_yield_percentage')
			->renameColumn('dividend_gain_percentage_per_annum', 'dividend_yield_percentage_per_annum')
			->update();

		$this->table('country_datas')
			->renameColumn('dividend_gain', 'dividend_yield')
			->renameColumn('dividend_gain_percentage', 'dividend_yield_percentage')
			->renameColumn('dividend_gain_percentage_per_annum', 'dividend_yield_percentage_per_annum')
			->update();

		$this->table('group_datas')
			->renameColumn('dividend_gain', 'dividend_yield')
			->renameColumn('dividend_gain_percentage', 'dividend_yield_percentage')
			->renameColumn('dividend_gain_percentage_per_annum', 'dividend_yield_percentage_per_annum')
			->update();

		$this->table('industry_datas')
			->renameColumn('dividend_gain', 'dividend_yield')
			->renameColumn('dividend_gain_percentage', 'dividend_yield_percentage')
			->renameColumn('dividend_gain_percentage_per_annum', 'dividend_yield_percentage_per_annum')
			->update();

		$this->table('portfolio_datas')
			->renameColumn('dividend_gain', 'dividend_yield')
			->renameColumn('dividend_gain_percentage', 'dividend_yield_percentage')
			->renameColumn('dividend_gain_percentage_per_annum', 'dividend_yield_percentage_per_annum')
			->update();

		$this->table('sector_datas')
			->renameColumn('dividend_gain', 'dividend_yield')
			->renameColumn('dividend_gain_percentage', 'dividend_yield_percentage')
			->renameColumn('dividend_gain_percentage_per_annum', 'dividend_yield_percentage_per_annum')
			->update();
	}

	public function down(): void
	{
		$this->table('industry_datas')
			->renameColumn('dividend_yield', 'dividend_gain')
			->renameColumn('dividend_yield_percentage', 'dividend_gain_percentage')
			->renameColumn('dividend_yield_percentage_per_annum', 'dividend_gain_percentage_per_annum')
			->update();

		$this->table('portfolio_datas')
			->renameColumn('dividend_yield', 'dividend_gain')
			->renameColumn('dividend_yield_percentage', 'dividend_gain_percentage')
			->renameColumn('dividend_yield_percentage_per_annum', 'dividend_gain_percentage_per_annum')
			->update();

		$this->table('sector_datas')
			->renameColumn('dividend_yield', 'dividend_gain')
			->renameColumn('dividend_yield_percentage', 'dividend_gain_percentage')
			->renameColumn('dividend_yield_percentage_per_annum', 'dividend_gain_percentage_per_annum')
			->update();

		$this->table('industry_datas')
			->renameColumn('dividend_yield', 'dividend_gain')
			->renameColumn('dividend_yield_percentage', 'dividend_gain_percentage')
			->renameColumn('dividend_yield_percentage_per_annum', 'dividend_gain_percentage_per_annum')
			->update();

		$this->table('group_datas')
			->renameColumn('dividend_yield', 'dividend_gain')
			->renameColumn('dividend_yield_percentage', 'dividend_gain_percentage')
			->renameColumn('dividend_yield_percentage_per_annum', 'dividend_gain_percentage_per_annum')
			->update();

		$this->table('country_datas')
			->renameColumn('dividend_gain', 'dividend_yield')
			->renameColumn('dividend_gain_percentage', 'dividend_yield_percentage')
			->renameColumn('dividend_gain_percentage_per_annum', 'dividend_yield_percentage_per_annum')
			->update();

		$this->table('asset_datas')
			->renameColumn('dividend_yield', 'dividend_gain')
			->renameColumn('dividend_yield_default_currency', 'dividend_gain_default_currency')
			->renameColumn('dividend_yield_percentage', 'dividend_gain_percentage')
			->renameColumn('dividend_yield_percentage_per_annum', 'dividend_gain_percentage_per_annum')
			->update();
	}
}
