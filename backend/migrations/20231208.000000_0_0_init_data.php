<?php

declare(strict_types=1);

namespace Migration;

use Cycle\Migrations\Migration;

class InitDataMigration extends Migration
{
	protected const DATABASE = 'default';

	public function up(): void
	{
		$this->database()->insert('currencies')->values([
			['id' => 1, 'code' => 'USD', 'name' => 'US Dollar', 'symbol' => '$'],
			['id' => 2, 'code' => 'EUR', 'name' => 'Euro', 'symbol' => '€'],
			['id' => 3, 'code' => 'GBP', 'name' => 'British Pound', 'symbol' => '£'],
			['id' => 4, 'code' => 'GBX', 'name' => 'British Pound (penny)', 'symbol' => 'p'],
			['id' => 5, 'code' => 'CZK', 'name' => 'Czech Crown', 'symbol' => 'Kč'],
			['id' => 6, 'code' => 'CAD', 'name' => 'Canadian Dollar', 'symbol' => 'CA$'],
		])->run();

		$this->database()->insert('users')->values([
			['id' => 1, 'email' => 'skopal.marek@gmail.com', 'password' => '$2y$10$Gv6vVjtQsz2n/1zk.wbyKOOON7ThrnpzJ0U7xJAUNHSE.4dJSyvSS', 'name' => 'Marek Skopal', 'default_currency_id' => 5],
		])->run();

		$this->database()->insert('markets')->values([
			['id' => 1, 'type' => 'Stock', 'name' => 'NASDAQ Stock Exchange', 'acronym' => 'NASDAQ', 'mic' => 'XNAS', 'country' => 'US', 'city' => 'New York', 'web' => 'www.nasdaq.com', 'currency_id' => 1],
			['id' => 2, 'type' => 'Stock', 'name' => 'New York Stock Exchange', 'acronym' => 'NYSE', 'mic' => 'XNYS', 'country' => 'US', 'city' => 'New York', 'web' => 'www.nyse.com', 'currency_id' => 1],
			['id' => 3, 'type' => 'Stock', 'name' => 'London Stock Exchange', 'acronym' => 'LSE', 'mic' => 'XLON', 'country' => 'GB', 'city' => 'London', 'web' => 'www.londonstockexchange.com', 'currency_id' => 3],
			['id' => 4, 'type' => 'Stock', 'name' => 'Canadian Securities Exchange', 'acronym' => 'CNSX', 'mic' => 'XCNQ', 'country' => 'CA', 'city' => 'Toronto', 'web' => 'www.cnsx.ca', 'currency_id' => 6],
			['id' => 5, 'type' => 'Stock', 'name' => 'Euronext Paris', 'acronym' => 'EURONEXT', 'mic' => 'XPAR', 'country' => 'FR', 'city' => 'Paris', 'web' => 'www.euronext.com', 'currency_id' => 2],
			['id' => 6, 'type' => 'Stock', 'name' => 'Deutsche B\u00f6rse', 'acronym' => 'FSX', 'mic' => 'XFRA', 'country' => 'DE', 'city' => 'Frankfurt', 'web' => 'www.deutsche-boerse.com', 'currency_id' => 2],
			['id' => 7, 'type' => 'Crypto', 'name' => 'Crypto', 'acronym' => '', 'mic' => '', 'country' => '', 'city' => '', 'web' => '', 'currency_id' => 1],
		])->run();

		$this->database()->insert('brokers')->values([
			['id' => 1, 'user_id' => 1, 'name' => 'Trading212', 'import_type' => 'Trading212'],
			['id' => 2, 'user_id' => 1, 'name' => 'Revolut', 'import_type' => 'Revolut'],
			['id' => 3, 'user_id' => 1, 'name' => 'Anycoin', 'import_type' => 'Anycoin'],
		])->run();

		$this->database()->insert('tickers')->values([
			['id' => 1, 'ticker' => 'VUSA.XLON', 'name' => 'Vanguard S&P 500 UCITS ETF', 'market_id' => 3, 'currency_id' => 3],
			['id' => 2, 'ticker' => 'NVDA.XNAS', 'name' => 'NVIDIA Corp', 'market_id' => 1, 'currency_id' => 1],
			['id' => 3, 'ticker' => 'BTC', 'name' => 'Bitcoin', 'market_id' => 7, 'currency_id' => 1],
		])->run();

		$this->database()->insert('groups')->values([
			['id' => 1, 'user_id' => 1, 'name' => 'Others', 'is_others' => true],
		])->run();
	}

	public function down(): void
	{
		$this->database()->query('TRUNCATE ?', ['groups']);
		$this->database()->query('TRUNCATE ?', ['tickers']);
		$this->database()->query('TRUNCATE ?', ['brokers']);
		$this->database()->query('TRUNCATE ?', ['markets']);
		$this->database()->query('TRUNCATE ?', ['users']);
		$this->database()->query('TRUNCATE ?', ['currencies']);
	}
}
