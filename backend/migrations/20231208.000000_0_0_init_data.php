<?php

declare(strict_types=1);

namespace Migration;

use Cycle\Migrations\Migration;

// phpcs:ignore
class InitDataMigration extends Migration
{
	protected const DATABASE = 'default';

	public function up(): void
	{
		$this->database()->insert('currencies')->values([
			['id' => 1, 'code' => 'USD', 'name' => 'US Dollar', 'symbol' => '$', 'multiply_currency_id' => null, 'multiplier' => 1, 'is_selectable' => true],
			['id' => 2, 'code' => 'EUR', 'name' => 'Euro', 'symbol' => '€', 'multiply_currency_id' => null, 'multiplier' => 1, 'is_selectable' => true],
			['id' => 3, 'code' => 'GBP', 'name' => 'British Pound', 'symbol' => '£', 'multiply_currency_id' => null, 'multiplier' => 1, 'is_selectable' => true],
			['id' => 5, 'code' => 'CZK', 'name' => 'Czech Crown', 'symbol' => 'Kč', 'multiply_currency_id' => null, 'multiplier' => 1, 'is_selectable' => true],
			['id' => 6, 'code' => 'CAD', 'name' => 'Canadian Dollar', 'symbol' => 'CA$', 'multiply_currency_id' => null, 'multiplier' => 1, 'is_selectable' => true],
			['id' => 7, 'code' => 'DKK', 'name' => 'Danish krone', 'symbol' => 'kr.', 'multiply_currency_id' => null, 'multiplier' => 1, 'is_selectable' => true],
			['id' => 8, 'code' => 'NOK', 'name' => 'Norwegian krone', 'symbol' => 'kr', 'multiply_currency_id' => null, 'multiplier' => 1, 'is_selectable' => true],
			['id' => 9, 'code' => 'SEK', 'name' => 'Swedish krona', 'symbol' => 'kr', 'multiply_currency_id' => null, 'multiplier' => 1, 'is_selectable' => true],
			['id' => 10, 'code' => 'CHF', 'name' => 'Swiss franc', 'symbol' => 'fr.', 'multiply_currency_id' => null, 'multiplier' => 1, 'is_selectable' => true],
			['id' => 11, 'code' => 'RUB', 'name' => 'Russian ruble', 'symbol' => '₽', 'multiply_currency_id' => null, 'multiplier' => 1, 'is_selectable' => true],
			['id' => 12, 'code' => 'TRY', 'name' => 'Turkish lira', 'symbol' => '₺', 'multiply_currency_id' => null, 'multiplier' => 1, 'is_selectable' => true],
			['id' => 13, 'code' => 'HUF', 'name' => 'Hungarian forint', 'symbol' => 'Ft', 'multiply_currency_id' => null, 'multiplier' => 1, 'is_selectable' => true],
			['id' => 14, 'code' => 'ARS', 'name' => 'Argentine peso', 'symbol' => 'Arg$', 'multiply_currency_id' => null, 'multiplier' => 1, 'is_selectable' => true],
			['id' => 15, 'code' => 'BRL', 'name' => 'Brazilian real', 'symbol' => 'R$', 'multiply_currency_id' => null, 'multiplier' => 1, 'is_selectable' => true],
			['id' => 16, 'code' => 'CLP', 'name' => 'Chilean peso', 'symbol' => '$', 'multiply_currency_id' => null, 'multiplier' => 1, 'is_selectable' => true],
			['id' => 17, 'code' => 'MXN', 'name' => 'Mexican peso', 'symbol' => '$', 'multiply_currency_id' => null, 'multiplier' => 1, 'is_selectable' => true],
			['id' => 18, 'code' => 'PEN', 'name' => 'Peruvian sol', 'symbol' => 'S/', 'multiply_currency_id' => null, 'multiplier' => 1, 'is_selectable' => true],
			['id' => 19, 'code' => 'AUD', 'name' => 'Australian dollar', 'symbol' => '$', 'multiply_currency_id' => null, 'multiplier' => 1, 'is_selectable' => true],
			['id' => 20, 'code' => 'CNY', 'name' => 'Renminbi', 'symbol' => '¥', 'multiply_currency_id' => null, 'multiplier' => 1, 'is_selectable' => true],
			['id' => 21, 'code' => 'HKD', 'name' => 'Hong Kong dollar', 'symbol' => 'HK$', 'multiply_currency_id' => null, 'multiplier' => 1, 'is_selectable' => true],
			['id' => 22, 'code' => 'INR', 'name' => 'Indian rupee', 'symbol' => '₹', 'multiply_currency_id' => null, 'multiplier' => 1, 'is_selectable' => true],
			['id' => 23, 'code' => 'IDR', 'name' => 'Indonesian rupee', 'symbol' => 'Rp', 'multiply_currency_id' => null, 'multiplier' => 1, 'is_selectable' => true],
			['id' => 24, 'code' => 'ILS', 'name' => 'Israeli new shekel', 'symbol' => '₪', 'multiply_currency_id' => null, 'multiplier' => 1, 'is_selectable' => true],
			['id' => 26, 'code' => 'JPY', 'name' => 'Japanese yen', 'symbol' => '¥', 'multiply_currency_id' => null, 'multiplier' => 1, 'is_selectable' => true],
			['id' => 27, 'code' => 'MYR', 'name' => 'Malaysian ringgit', 'symbol' => 'RM', 'multiply_currency_id' => null, 'multiplier' => 1, 'is_selectable' => true],
			['id' => 28, 'code' => 'QAR', 'name' => 'Qatari riyal', 'symbol' => 'QR', 'multiply_currency_id' => null, 'multiplier' => 1, 'is_selectable' => true],
			['id' => 29, 'code' => 'SAR', 'name' => 'Saudi riyal', 'symbol' => 'SR', 'multiply_currency_id' => null, 'multiplier' => 1, 'is_selectable' => true],
			['id' => 30, 'code' => 'SGD', 'name' => 'Singapore dollar', 'symbol' => 'S$', 'multiply_currency_id' => null, 'multiplier' => 1, 'is_selectable' => true],
			['id' => 31, 'code' => 'KRW', 'name' => 'South Korean won', 'symbol' => '₩', 'multiply_currency_id' => null, 'multiplier' => 1, 'is_selectable' => true],
			['id' => 32, 'code' => 'TWD', 'name' => 'New Taiwan dollar', 'symbol' => 'NT$', 'multiply_currency_id' => null, 'multiplier' => 1, 'is_selectable' => true],
			['id' => 33, 'code' => 'THB', 'name' => 'Thai baht', 'symbol' => '฿', 'multiply_currency_id' => null, 'multiplier' => 1, 'is_selectable' => true],
			['id' => 34, 'code' => 'AED', 'name' => 'United Arab Emirates dirham', 'symbol' => 'Dh', 'multiply_currency_id' => null, 'multiplier' => 1, 'is_selectable' => true],
			['id' => 35, 'code' => 'EGP', 'name' => 'Egyptian pound', 'symbol' => 'E£', 'multiply_currency_id' => null, 'multiplier' => 1, 'is_selectable' => true],
			['id' => 36, 'code' => 'EGP', 'name' => 'South African rand', 'symbol' => 'R', 'multiply_currency_id' => null, 'multiplier' => 1, 'is_selectable' => true],
			['id' => 38, 'code' => 'NZD', 'name' => 'New Zealand dollar', 'symbol' => '$', 'multiply_currency_id' => null, 'multiplier' => 1, 'is_selectable' => true],
		])->run();

		$this->database()->insert('currencies')->values([
			['id' => 4, 'code' => 'GBX', 'name' => 'British Pound (penny)', 'symbol' => 'p', 'multiply_currency_id' => 3, 'multiplier' => 100, 'is_selectable' => false],
			['id' => 25, 'code' => 'ILA', 'name' => 'Israeli new shekel (agora)', 'symbol' => 'a', 'multiply_currency_id' => 24, 'multiplier' => 100, 'is_selectable' => false],
			['id' => 37, 'code' => 'EGP', 'name' => 'South African rand (cent)', 'symbol' => 'c', 'multiply_currency_id' => 36, 'multiplier' => 100, 'is_selectable' => false],
		])->run();

		$this->database()->insert('users')->values([
			['id' => 1, 'email' => 'admin@fingather.com', 'password' => '$2y$10$Gv6vVjtQsz2n/1zk.wbyKOOON7ThrnpzJ0U7xJAUNHSE.4dJSyvSS', 'name' => 'FinGather Admin', 'default_currency_id' => 5, 'role' => 'Admin'],
		])->run();

		$this->database()->insert('portfolios')->values([
			['id' => 1, 'user_id' => 1, 'name' => 'My Portfolio', 'is_default' => true],
		])->run();

		$this->database()->insert('markets')->values([
			['id' => 1, 'type' => 'Stock', 'name' => 'NASDAQ Stock Exchange', 'acronym' => 'NASDAQ', 'mic' => 'XNAS', 'country' => 'US', 'city' => 'New York', 'timezone' => 'America/New_York', 'currency_id' => 1],
			['id' => 2, 'type' => 'Stock', 'name' => 'NASDAQ Stock Exchange', 'acronym' => 'NASDAQ', 'mic' => 'XNMS', 'country' => 'US', 'city' => 'New York', 'timezone' => 'America/New_York', 'currency_id' => 1],
			['id' => 3, 'type' => 'Stock', 'name' => 'NASDAQ Stock Exchange', 'acronym' => 'NASDAQ', 'mic' => 'XNGS', 'country' => 'US', 'city' => 'New York', 'timezone' => 'America/New_York', 'currency_id' => 1],
			['id' => 4, 'type' => 'Stock', 'name' => 'NASDAQ Stock Exchange', 'acronym' => 'NASDAQ', 'mic' => 'XNCM', 'country' => 'US', 'city' => 'New York', 'timezone' => 'America/New_York', 'currency_id' => 1],
			['id' => 5, 'type' => 'Stock', 'name' => 'New York Stock Exchange', 'acronym' => 'NYSE', 'mic' => 'XNYS', 'country' => 'US', 'city' => 'New York', 'timezone' => 'America/New_York', 'currency_id' => 1],
			['id' => 6, 'type' => 'Stock', 'name' => 'New York Stock Exchange', 'acronym' => 'NYSE', 'mic' => 'XASE', 'country' => 'US', 'city' => 'New York', 'timezone' => 'America/New_York', 'currency_id' => 1],
			['id' => 7, 'type' => 'Stock', 'name' => 'New York Stock Exchange', 'acronym' => 'NYSE', 'mic' => 'ARCX', 'country' => 'US', 'city' => 'New York', 'timezone' => 'America/New_York', 'currency_id' => 1],
			['id' => 8, 'type' => 'Stock', 'name' => 'OTC Markets', 'acronym' => 'OTC', 'mic' => 'PSGM', 'country' => 'US', 'city' => 'New York', 'timezone' => 'America/New_York', 'currency_id' => 1],
			['id' => 9, 'type' => 'Stock', 'name' => 'OTC Markets', 'acronym' => 'OTC', 'mic' => 'EXPM', 'country' => 'US', 'city' => 'New York', 'timezone' => 'America/New_York', 'currency_id' => 1],
			['id' => 10, 'type' => 'Stock', 'name' => 'OTC Markets', 'acronym' => 'OTC', 'mic' => 'PINX', 'country' => 'US', 'city' => 'New York', 'timezone' => 'America/New_York', 'currency_id' => 1],
			['id' => 11, 'type' => 'Stock', 'name' => 'OTC Markets', 'acronym' => 'OTC', 'mic' => 'OTCB', 'country' => 'US', 'city' => 'New York', 'timezone' => 'America/New_York', 'currency_id' => 1],
			['id' => 12, 'type' => 'Stock', 'name' => 'OTC Markets', 'acronym' => 'OTC', 'mic' => 'OTCQ', 'country' => 'US', 'city' => 'New York', 'timezone' => 'America/New_York', 'currency_id' => 1],
			['id' => 13, 'type' => 'Stock', 'name' => 'Chicago Board Options Exchange', 'acronym' => 'CBOE', 'mic' => 'BATS', 'country' => 'US', 'city' => 'Chicago', 'timezone' => 'America/New_York', 'currency_id' => 1],
			['id' => 14, 'type' => 'Stock', 'name' => 'New York Board of Trade', 'acronym' => 'ICE', 'mic' => 'NYBOT', 'country' => 'US', 'city' => 'New York', 'timezone' => 'America/New_York', 'currency_id' => 1],
			['id' => 15, 'type' => 'Stock', 'name' => 'London Stock Exchange', 'acronym' => 'LSE', 'mic' => 'XLON', 'country' => 'GB', 'city' => 'London', 'timezone' => 'Europe/London', 'currency_id' => 3],
			['id' => 16, 'type' => 'Stock', 'name' => 'Euronext Paris', 'acronym' => 'EURONEXT', 'mic' => 'XPAR', 'country' => 'FR', 'city' => 'Paris', 'timezone' => 'Europe/Paris', 'currency_id' => 2],
			['id' => 17, 'type' => 'Stock', 'name' => 'Euronext Brussels', 'acronym' => 'EURONEXT', 'mic' => 'XBRU', 'country' => 'BE', 'city' => 'Brussels', 'timezone' => 'Europe/Brussels', 'currency_id' => 2],
			['id' => 18, 'type' => 'Stock', 'name' => 'Euronext Amsterdam', 'acronym' => 'EURONEXT', 'mic' => 'XAMS', 'country' => 'NL', 'city' => 'Amsterdam', 'timezone' => 'Europe/Amsterdam', 'currency_id' => 2],
			['id' => 19, 'type' => 'Stock', 'name' => 'Euronext Lisbon', 'acronym' => 'EURONEXT', 'mic' => 'XLIS', 'country' => 'PT', 'city' => 'Lisbon', 'timezone' => 'Europe/Lisbon', 'currency_id' => 2],
			['id' => 20, 'type' => 'Stock', 'name' => 'Frankfurt Stock Exchange', 'acronym' => 'FSX', 'mic' => 'XFRA', 'country' => 'DE', 'city' => 'Frankfurt', 'timezone' => 'Europe/Berlin', 'currency_id' => 2],
			['id' => 21, 'type' => 'Stock', 'name' => 'Berlin Stock Exchange', 'acronym' => 'XBER', 'mic' => 'XBER', 'country' => 'DE', 'city' => 'Berlin', 'timezone' => 'Europe/Berlin', 'currency_id' => 2],
			['id' => 22, 'type' => 'Stock', 'name' => 'Munich Stock Exchange', 'acronym' => 'XMUN', 'mic' => 'XMUN', 'country' => 'DE', 'city' => 'Munich', 'timezone' => 'Europe/Berlin', 'currency_id' => 2],
			['id' => 23, 'type' => 'Stock', 'name' => 'Stuttgart Stock Exchange', 'acronym' => 'XSTU', 'mic' => 'XMUN', 'country' => 'DE', 'city' => 'Stuttgart', 'timezone' => 'Europe/Berlin', 'currency_id' => 2],
			['id' => 24, 'type' => 'Stock', 'name' => 'Deutsche Börse Xetra', 'acronym' => 'XETR', 'mic' => 'XETR', 'country' => 'DE', 'city' => 'Frankfurt', 'timezone' => 'Europe/Berlin', 'currency_id' => 2],
			['id' => 25, 'type' => 'Stock', 'name' => 'Hanover Stock Exchange', 'acronym' => 'XHAN', 'mic' => 'XHAN', 'country' => 'DE', 'city' => 'Hanover', 'timezone' => 'Europe/Berlin', 'currency_id' => 2],
			['id' => 26, 'type' => 'Stock', 'name' => 'Dusseldorf Stock Exchange', 'acronym' => 'XDUS', 'mic' => 'XDUS', 'country' => 'DE', 'city' => 'Dusseldorf', 'timezone' => 'Europe/Berlin', 'currency_id' => 2],
			['id' => 27, 'type' => 'Stock', 'name' => 'Hamburg Stock Exchange', 'acronym' => 'XHAM', 'mic' => 'XDUS', 'country' => 'DE', 'city' => 'Hamburg', 'timezone' => 'Europe/Berlin', 'currency_id' => 2],
			['id' => 28, 'type' => 'Stock', 'name' => 'Vienna Stock Exchange', 'acronym' => 'VSE', 'mic' => 'XWBO', 'country' => 'AT', 'city' => 'Vienna', 'timezone' => 'Europe/Vienna', 'currency_id' => 2],
			['id' => 29, 'type' => 'Stock', 'name' => 'Helsinki Stock Exchange', 'acronym' => 'OMXH', 'mic' => 'XHEL', 'country' => 'FI', 'city' => 'Helsinki', 'timezone' => 'Europe/Helsinki', 'currency_id' => 2],
			['id' => 30, 'type' => 'Stock', 'name' => 'Irish Stock Exchange', 'acronym' => 'ISE', 'mic' => 'XDUB', 'country' => 'IE', 'city' => 'Dublin', 'timezone' => 'Europe/Dublin', 'currency_id' => 2],
			['id' => 31, 'type' => 'Stock', 'name' => 'Euronext Growth Dublin', 'acronym' => 'XESM', 'mic' => 'XESM', 'country' => 'IE', 'city' => 'Dublin', 'timezone' => 'Europe/Dublin', 'currency_id' => 2],
			['id' => 32, 'type' => 'Stock', 'name' => 'Euronext Dublin', 'acronym' => 'XMSM', 'mic' => 'XMSM', 'country' => 'IE', 'city' => 'Dublin', 'timezone' => 'Europe/Dublin', 'currency_id' => 2],
			['id' => 33, 'type' => 'Stock', 'name' => 'Italian Stock Exchange in Milan', 'acronym' => 'MTA', 'mic' => 'XMIL', 'country' => 'IT', 'city' => 'Milan', 'timezone' => 'Europe/Rome', 'currency_id' => 2],
			['id' => 34, 'type' => 'Stock', 'name' => 'Oslo Stock Exchange', 'acronym' => 'OSE', 'mic' => 'XOSL', 'country' => 'NO', 'city' => 'Oslo', 'timezone' => 'Europe/Oslo', 'currency_id' => 8],
			['id' => 35, 'type' => 'Stock', 'name' => 'Madrid Stock Exchange', 'acronym' => 'BME', 'mic' => 'XMAD', 'country' => 'ES', 'city' => 'Madrid', 'timezone' => 'Europe/Madrid', 'currency_id' => 2],
			['id' => 36, 'type' => 'Stock', 'name' => 'Nasdaq Stockholm', 'acronym' => 'OMX', 'mic' => 'XSTO', 'country' => 'SE', 'city' => 'Stockholm', 'timezone' => 'Europe/Stockholm', 'currency_id' => 9],
			['id' => 37, 'type' => 'Stock', 'name' => 'First North Sweden', 'acronym' => 'SSME', 'mic' => 'SSME', 'country' => 'SE', 'city' => 'Stockholm', 'timezone' => 'Europe/Stockholm', 'currency_id' => 9],
			['id' => 38, 'type' => 'Stock', 'name' => 'Spotlight Stock Market', 'acronym' => 'SSM', 'mic' => 'XSAT', 'country' => 'SE', 'city' => 'Stockholm', 'timezone' => 'Europe/Stockholm', 'currency_id' => 9],
			['id' => 39, 'type' => 'Stock', 'name' => 'SIX Swiss Exchange', 'acronym' => 'SIX', 'mic' => 'XSWX', 'country' => 'CH', 'city' => 'Zurich', 'timezone' => 'Europe/Zurich', 'currency_id' => 10],
			['id' => 40, 'type' => 'Stock', 'name' => 'Moscow Exchange', 'acronym' => 'MOEX', 'mic' => 'MISX', 'country' => 'RU', 'city' => 'Moscow', 'timezone' => 'Europe/Moscow', 'currency_id' => 11],
			['id' => 41, 'type' => 'Stock', 'name' => 'Istanbul Stock Exchange', 'acronym' => 'BIST', 'mic' => 'XIST', 'country' => 'TR', 'city' => 'Istanbul', 'timezone' => 'Europe/Istanbul', 'currency_id' => 12],
			['id' => 42, 'type' => 'Stock', 'name' => 'Nasdaq Copenhagen', 'acronym' => 'OMXC', 'mic' => 'XCSE', 'country' => 'DK', 'city' => 'Copenhagen', 'timezone' => 'Europe/Copenhagen', 'currency_id' => 7],
			['id' => 43, 'type' => 'Stock', 'name' => 'First North Denmark', 'acronym' => 'DSME', 'mic' => 'DSME', 'country' => 'DK', 'city' => 'Copenhagen', 'timezone' => 'Europe/Copenhagen', 'currency_id' => 7],
			['id' => 44, 'type' => 'Stock', 'name' => 'Nasdaq Tallinn', 'acronym' => 'OMXT', 'mic' => 'XTAL', 'country' => 'EE', 'city' => 'Tallinn', 'timezone' => 'Europe/Tallinn', 'currency_id' => 2],
			['id' => 45, 'type' => 'Stock', 'name' => 'Athens Stock Exchange', 'acronym' => 'ASE', 'mic' => 'ASEX', 'country' => 'GR', 'city' => 'Athens', 'timezone' => 'Europe/Athens', 'currency_id' => 2],
			['id' => 46, 'type' => 'Stock', 'name' => 'Prague Stock Exchange', 'acronym' => 'PSE', 'mic' => 'XPRA', 'country' => 'CZ', 'city' => 'Prague', 'timezone' => 'Europe/Prague', 'currency_id' => 5],
			['id' => 47, 'type' => 'Stock', 'name' => 'Budapest Stock Exchange', 'acronym' => 'BSE', 'mic' => 'XBUD', 'country' => 'HU', 'city' => 'Budapest', 'timezone' => 'Europe/Budapest', 'currency_id' => 13],
			['id' => 48, 'type' => 'Stock', 'name' => 'Nasdaq Riga', 'acronym' => 'OMXR', 'mic' => 'XRIS', 'country' => 'LV', 'city' => 'Riga', 'timezone' => 'Europe/Riga', 'currency_id' => 2],
			['id' => 49, 'type' => 'Stock', 'name' => 'Nasdaq Vilnius', 'acronym' => 'OMXV', 'mic' => 'XLIT', 'country' => 'LT', 'city' => 'Vilnius', 'timezone' => 'Europe/Vilnius', 'currency_id' => 2],
			['id' => 50, 'type' => 'Stock', 'name' => 'Canadian Securities Exchange', 'acronym' => 'CSE', 'mic' => 'XCNQ', 'country' => 'CA', 'city' => 'Toronto', 'timezone' => 'America/Toronto', 'currency_id' => 6],
			['id' => 51, 'type' => 'Stock', 'name' => 'NEO Exchange', 'acronym' => 'NEO', 'mic' => 'NEOE', 'country' => 'CA', 'city' => 'Toronto', 'timezone' => 'America/Toronto', 'currency_id' => 6],
			['id' => 52, 'type' => 'Stock', 'name' => 'Toronto Stock Exchange', 'acronym' => 'TSX', 'mic' => 'XTSE', 'country' => 'CA', 'city' => 'Toronto', 'timezone' => 'America/Toronto', 'currency_id' => 6],
			['id' => 53, 'type' => 'Stock', 'name' => 'TSX Venture Exchange', 'acronym' => 'TSXV', 'mic' => 'XTSX', 'country' => 'CA', 'city' => 'Toronto', 'timezone' => 'America/Toronto', 'currency_id' => 6],
			['id' => 54, 'type' => 'Stock', 'name' => 'Buenos Aires Stock Exchange', 'acronym' => 'BCBA', 'mic' => 'XBUE', 'country' => 'AR', 'city' => 'Buenos Aires', 'timezone' => 'America/Argentina/Buenos_Aires', 'currency_id' => 14],
			['id' => 55, 'type' => 'Stock', 'name' => 'B3 Stock Exchange', 'acronym' => 'Bovespa', 'mic' => 'BVMF', 'country' => 'BR', 'city' => 'São Paulo', 'timezone' => 'America/Paramaribo', 'currency_id' => 15],
			['id' => 56, 'type' => 'Stock', 'name' => 'Santiago Stock Exchange', 'acronym' => 'SSE', 'mic' => 'XSGO', 'country' => 'CL', 'city' => 'Santiago', 'timezone' => 'America/Santiago', 'currency_id' => 16],
			['id' => 57, 'type' => 'Stock', 'name' => 'Mexican Stock Exchange', 'acronym' => 'BMV', 'mic' => 'XMEX', 'country' => 'MX', 'city' => 'Mexico City', 'timezone' => 'America/Mexico_City', 'currency_id' => 17],
			['id' => 58, 'type' => 'Stock', 'name' => 'Lima Stock Exchange', 'acronym' => 'BVL', 'mic' => 'XLIM', 'country' => 'PE', 'city' => 'Lima', 'timezone' => 'America/Lima', 'currency_id' => 18],
			['id' => 59, 'type' => 'Stock', 'name' => 'CBOE Australia', 'acronym' => 'CXA', 'mic' => 'CXAC', 'country' => 'AU', 'city' => 'Sydney', 'timezone' => 'Australia/Sydney', 'currency_id' => 19],
			['id' => 60, 'type' => 'Stock', 'name' => 'Australia Stock Exchange', 'acronym' => 'ASX', 'mic' => 'XASX', 'country' => 'AU', 'city' => 'Sydney', 'timezone' => 'Australia/Sydney', 'currency_id' => 19],
			['id' => 61, 'type' => 'Stock', 'name' => 'Shenzhen Stock Exchange', 'acronym' => 'SZSE', 'mic' => 'XSHE', 'country' => 'CN', 'city' => 'Shenzhen', 'timezone' => 'Asia/Shanghai', 'currency_id' => 20],
			['id' => 62, 'type' => 'Stock', 'name' => 'Shanghai Stock Exchange', 'acronym' => 'SSE', 'mic' => 'XSHG', 'country' => 'CN', 'city' => 'Shanghai', 'timezone' => 'Asia/Shanghai', 'currency_id' => 20],
			['id' => 63, 'type' => 'Stock', 'name' => 'Hong Kong Stock Exchange', 'acronym' => 'HKEX', 'mic' => 'XHKG', 'country' => 'HK', 'city' => 'Hong Kong', 'timezone' => 'Asia/Hong_Kong', 'currency_id' => 21],
			['id' => 64, 'type' => 'Stock', 'name' => 'Bombay Stock Exchange', 'acronym' => 'BSE', 'mic' => 'XBOM', 'country' => 'IN', 'city' => 'Mumbai', 'timezone' => 'Asia/Kolkata', 'currency_id' => 22],
			['id' => 65, 'type' => 'Stock', 'name' => 'India National Stock Exchange', 'acronym' => 'NSE', 'mic' => 'XNSE', 'country' => 'IN', 'city' => 'Mumbai', 'timezone' => 'Asia/Kolkata', 'currency_id' => 22],
			['id' => 66, 'type' => 'Stock', 'name' => 'Indonesia Stock Exchange', 'acronym' => 'IDX', 'mic' => 'XIDX', 'country' => 'ID', 'city' => 'Jakarta', 'timezone' => 'Asia/Jakarta', 'currency_id' => 23],
			['id' => 67, 'type' => 'Stock', 'name' => 'Tel Aviv Stock Exchange', 'acronym' => 'TASE', 'mic' => 'XTAE', 'country' => 'IL', 'city' => 'Tel Aviv', 'timezone' => 'Asia/Jerusalem', 'currency_id' => 25],
			['id' => 68, 'type' => 'Stock', 'name' => 'Japan Exchange Group', 'acronym' => 'JPX', 'mic' => 'XJPX', 'country' => 'JP', 'city' => 'Tokyo', 'timezone' => 'Asia/Tokyo', 'currency_id' => 26],
			['id' => 69, 'type' => 'Stock', 'name' => 'Sapporo Securities Exchange', 'acronym' => 'XSAP', 'mic' => 'XSAP', 'country' => 'JP', 'city' => 'Sapporo', 'timezone' => 'Asia/Tokyo', 'currency_id' => 26],
			['id' => 70, 'type' => 'Stock', 'name' => 'Bursa Malaysia', 'acronym' => 'MYX', 'mic' => 'XKLS', 'country' => 'MY', 'city' => 'Kuala Lumpur', 'timezone' => 'Asia/Kuala_Lumpur', 'currency_id' => 27],
			['id' => 71, 'type' => 'Stock', 'name' => 'Qatar Exchange', 'acronym' => 'QE', 'mic' => 'DSMD', 'country' => 'QA', 'city' => 'Doha', 'timezone' => 'Asia/Qatar', 'currency_id' => 28],
			['id' => 72, 'type' => 'Stock', 'name' => 'Saudi Stock Exchange', 'acronym' => 'SSE', 'mic' => 'DSMD', 'country' => 'SA', 'city' => 'Rijad', 'timezone' => 'Asia/Riyadh', 'currency_id' => 29],
			['id' => 73, 'type' => 'Stock', 'name' => 'Singapore Exchange', 'acronym' => 'SGX', 'mic' => 'XSES', 'country' => 'SG', 'city' => 'Singapore', 'timezone' => 'Asia/Singapore', 'currency_id' => 30],
			['id' => 74, 'type' => 'Stock', 'name' => 'Korea Exchange (Stock Market)', 'acronym' => 'KRX', 'mic' => 'XKRX', 'country' => 'KR', 'city' => 'Seoul', 'timezone' => 'Asia/Seoul', 'currency_id' => 31],
			['id' => 75, 'type' => 'Stock', 'name' => 'Korea Exchange (Kosdaq)', 'acronym' => 'KOSDAQ', 'mic' => 'XKOS', 'country' => 'KR', 'city' => 'Seoul', 'timezone' => 'Asia/Seoul', 'currency_id' => 31],
			['id' => 76, 'type' => 'Stock', 'name' => 'Korea New Exchange', 'acronym' => 'KONEX', 'mic' => 'XKON', 'country' => 'KR', 'city' => 'Seoul', 'timezone' => 'Asia/Seoul', 'currency_id' => 31],
			['id' => 77, 'type' => 'Stock', 'name' => 'Taiwan Stock Exchange', 'acronym' => 'TWSE', 'mic' => 'XTAI', 'country' => 'TW', 'city' => 'Taipei', 'timezone' => 'Asia/Taipei', 'currency_id' => 32],
			['id' => 78, 'type' => 'Stock', 'name' => 'Stock Exchange of Thailand', 'acronym' => 'SET', 'mic' => 'XBKK', 'country' => 'TH', 'city' => 'Bangkok', 'timezone' => 'Asia/Bangkok', 'currency_id' => 33],
			['id' => 79, 'type' => 'Stock', 'name' => 'Abu Dhabi Securities Exchange', 'acronym' => 'ADX', 'mic' => 'XADS', 'country' => 'AE', 'city' => 'Abu Dhabi', 'timezone' => 'Asia/Dubai', 'currency_id' => 34],
			['id' => 80, 'type' => 'Stock', 'name' => 'Egyptian Exchange', 'acronym' => 'EGX', 'mic' => 'XCAI', 'country' => 'EG', 'city' => 'Cairo', 'timezone' => 'Africa/Cairo', 'currency_id' => 35],
			['id' => 81, 'type' => 'Stock', 'name' => 'Johannesburg Stock Exchange', 'acronym' => 'JSE', 'mic' => 'XJSE', 'country' => 'ZA', 'city' => 'Johannesburg', 'timezone' => 'Africa/Johannesburg', 'currency_id' => 37],
			['id' => 82, 'type' => 'Stock', 'name' => 'New Zealand Exchange Ltd', 'acronym' => 'NZX', 'mic' => 'XNZE', 'country' => 'NZ', 'city' => 'Wellington', 'timezone' => 'Pacific/Auckland', 'currency_id' => 38],

			['id' => 83, 'type' => 'Crypto', 'name' => 'Binance', 'acronym' => '', 'mic' => 'Bina', 'country' => '', 'city' => '', 'web' => '', 'currency_id' => 1],

		])->run();

		$this->database()->insert('brokers')->values([
			['id' => 1, 'user_id' => 1, 'portfolio_id' => 1, 'name' => 'Trading212', 'import_type' => 'Trading212'],
			['id' => 2, 'user_id' => 1, 'portfolio_id' => 1, 'name' => 'Revolut', 'import_type' => 'Revolut'],
			['id' => 3, 'user_id' => 1, 'portfolio_id' => 1, 'name' => 'Anycoin', 'import_type' => 'Anycoin'],
		])->run();

		$this->database()->insert('groups')->values([
			['id' => 1, 'user_id' => 1, 'portfolio_id' => 1, 'name' => 'Others', 'is_others' => true],
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
