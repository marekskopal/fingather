<?php

declare(strict_types=1);

namespace Migration;

use Cycle\Migrations\Migration;

final class TickerFundamentalsMigration extends Migration
{
	protected const DATABASE = 'default';

	public function up(): void
	{
		$this->table('ticker_fundamentals')
			->addColumn('ticker_id', 'integer', [
				'nullable' => false,
				'defaultValue' => null,
				'size' => 11,
				'autoIncrement' => false,
				'unsigned' => false,
				'zerofill' => false,
			])
			->addColumn('market_capitalization', 'bigInteger', [
				'nullable' => true,
				'defaultValue' => null,
				'size' => 20,
				'autoIncrement' => false,
				'unsigned' => false,
				'zerofill' => false,
			])
			->addColumn('enterprise_value', 'bigInteger', [
				'nullable' => true,
				'defaultValue' => null,
				'size' => 20,
				'autoIncrement' => false,
				'unsigned' => false,
				'zerofill' => false,
			])
			->addColumn('trailing_pe', 'float', ['nullable' => true, 'defaultValue' => null])
			->addColumn('forward_pe', 'float', ['nullable' => true, 'defaultValue' => null])
			->addColumn('peg_ratio', 'float', ['nullable' => true, 'defaultValue' => null])
			->addColumn('price_to_sales_ttm', 'float', ['nullable' => true, 'defaultValue' => null])
			->addColumn('price_to_book_mrq', 'float', ['nullable' => true, 'defaultValue' => null])
			->addColumn('enterprise_to_revenue', 'float', ['nullable' => true, 'defaultValue' => null])
			->addColumn('enterprise_to_ebitda', 'float', ['nullable' => true, 'defaultValue' => null])
			->addColumn('fiscal_year_ends', 'date', ['nullable' => true, 'defaultValue' => null])
			->addColumn('most_recent_quarter', 'date', ['nullable' => true, 'defaultValue' => null])
			->addColumn('profit_margin', 'float', ['nullable' => true, 'defaultValue' => null])
			->addColumn('operating_margin', 'float', ['nullable' => true, 'defaultValue' => null])
			->addColumn('return_on_assets_ttm', 'float', ['nullable' => true, 'defaultValue' => null])
			->addColumn('return_on_equity_ttm', 'float', ['nullable' => true, 'defaultValue' => null])
			->addColumn('revenue_ttm', 'bigInteger', [
				'nullable' => true,
				'defaultValue' => null,
				'size' => 20,
				'autoIncrement' => false,
				'unsigned' => false,
				'zerofill' => false,
			])
			->addColumn('revenue_per_share_ttm', 'float', ['nullable' => true, 'defaultValue' => null])
			->addColumn('quarterly_revenue_growth', 'float', ['nullable' => true, 'defaultValue' => null])
			->addColumn('gross_profit_ttm', 'bigInteger', [
				'nullable' => true,
				'defaultValue' => null,
				'size' => 20,
				'autoIncrement' => false,
				'unsigned' => false,
				'zerofill' => false,
			])
			->addColumn('ebitda', 'bigInteger', [
				'nullable' => true,
				'defaultValue' => null,
				'size' => 20,
				'autoIncrement' => false,
				'unsigned' => false,
				'zerofill' => false,
			])
			->addColumn('net_income_to_common_ttm', 'bigInteger', [
				'nullable' => true,
				'defaultValue' => null,
				'size' => 20,
				'autoIncrement' => false,
				'unsigned' => false,
				'zerofill' => false,
			])
			->addColumn('diluted_eps_ttm', 'float', ['nullable' => true, 'defaultValue' => null])
			->addColumn('quarterly_earnings_growth_yoy', 'float', ['nullable' => true, 'defaultValue' => null])
			->addColumn('total_cash_mrq', 'bigInteger', [
				'nullable' => true,
				'defaultValue' => null,
				'size' => 20,
				'autoIncrement' => false,
				'unsigned' => false,
				'zerofill' => false,
			])
			->addColumn('total_cash_per_share_mrq', 'float', ['nullable' => true, 'defaultValue' => null])
			->addColumn('total_debt_mrq', 'bigInteger', [
				'nullable' => true,
				'defaultValue' => null,
				'size' => 20,
				'autoIncrement' => false,
				'unsigned' => false,
				'zerofill' => false,
			])
			->addColumn('total_debt_to_equity_mrq', 'float', ['nullable' => true, 'defaultValue' => null])
			->addColumn('current_ratio_mrq', 'float', ['nullable' => true, 'defaultValue' => null])
			->addColumn('book_value_per_share_mrq', 'float', ['nullable' => true, 'defaultValue' => null])
			->addColumn('operating_cash_flow_ttm', 'bigInteger', [
				'nullable' => true,
				'defaultValue' => null,
				'size' => 20,
				'autoIncrement' => false,
				'unsigned' => false,
				'zerofill' => false,
			])
			->addColumn('levered_free_cash_flow_ttm', 'bigInteger', [
				'nullable' => true,
				'defaultValue' => null,
				'size' => 20,
				'autoIncrement' => false,
				'unsigned' => false,
				'zerofill' => false,
			])
			->addColumn('shares_outstanding', 'bigInteger', [
				'nullable' => true,
				'defaultValue' => null,
				'size' => 20,
				'autoIncrement' => false,
				'unsigned' => false,
				'zerofill' => false,
			])
			->addColumn('float_shares', 'bigInteger', [
				'nullable' => true,
				'defaultValue' => null,
				'size' => 20,
				'autoIncrement' => false,
				'unsigned' => false,
				'zerofill' => false,
			])
			->addColumn('avg10_volume', 'bigInteger', [
				'nullable' => true,
				'defaultValue' => null,
				'size' => 20,
				'autoIncrement' => false,
				'unsigned' => false,
				'zerofill' => false,
			])
			->addColumn('avg90_volume', 'bigInteger', [
				'nullable' => true,
				'defaultValue' => null,
				'size' => 20,
				'autoIncrement' => false,
				'unsigned' => false,
				'zerofill' => false,
			])
			->addColumn('shares_short', 'bigInteger', [
				'nullable' => true,
				'defaultValue' => null,
				'size' => 20,
				'autoIncrement' => false,
				'unsigned' => false,
				'zerofill' => false,
			])
			->addColumn('short_ratio', 'float', ['nullable' => true, 'defaultValue' => null])
			->addColumn('short_percent_of_shares_outstanding', 'float', ['nullable' => true, 'defaultValue' => null])
			->addColumn('percent_held_by_insiders', 'float', ['nullable' => true, 'defaultValue' => null])
			->addColumn('percent_held_by_institutions', 'float', ['nullable' => true, 'defaultValue' => null])
			->addColumn('fifty_two_week_low', 'float', ['nullable' => true, 'defaultValue' => null])
			->addColumn('fifty_two_week_high', 'float', ['nullable' => true, 'defaultValue' => null])
			->addColumn('fifty_two_week_change', 'float', ['nullable' => true, 'defaultValue' => null])
			->addColumn('beta', 'float', ['nullable' => true, 'defaultValue' => null])
			->addColumn('day50_ma', 'float', ['nullable' => true, 'defaultValue' => null])
			->addColumn('day200_ma', 'float', ['nullable' => true, 'defaultValue' => null])
			->addColumn('forward_annual_dividend_rate', 'float', ['nullable' => true, 'defaultValue' => null])
			->addColumn('forward_annual_dividend_yield', 'float', ['nullable' => true, 'defaultValue' => null])
			->addColumn('trailing_annual_dividend_rate', 'float', ['nullable' => true, 'defaultValue' => null])
			->addColumn('trailing_annual_dividend_yield', 'float', ['nullable' => true, 'defaultValue' => null])
			->addColumn('five_year_average_dividend_yield', 'float', ['nullable' => true, 'defaultValue' => null])
			->addColumn('payout_ratio', 'float', ['nullable' => true, 'defaultValue' => null])
			->addColumn('dividend_date', 'date', ['nullable' => true, 'defaultValue' => null])
			->addColumn('ex_dividend_date', 'date', ['nullable' => true, 'defaultValue' => null])
			->addColumn('id', 'primary', [
				'nullable' => false,
				'defaultValue' => null,
				'size' => 11,
				'autoIncrement' => true,
				'unsigned' => false,
				'zerofill' => false,
			])
			->addIndex(['ticker_id'], ['name' => 'ticker_fundamentals_index_ticker_id_66281971d83b0', 'unique' => false])
			->addForeignKey(['ticker_id'], 'tickers', ['id'], [
				'name' => 'ticker_fundamentals_foreign_ticker_id_66281971d83ba',
				'delete' => 'CASCADE',
				'update' => 'CASCADE',
				'indexCreate' => true,
			])
			->setPrimaryKeys(['id'])
			->create();
	}

	public function down(): void
	{
		$this->table('ticker_fundamentals')->drop();
	}
}
