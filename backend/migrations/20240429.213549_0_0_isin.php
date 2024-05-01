<?php

declare(strict_types=1);

namespace Migration;

use Cycle\Migrations\Migration;

class IsinMigration extends Migration
{
	protected const DATABASE = 'default';

	public function up(): void
	{
		$this->table('markets')
			->addColumn('exchange_code', 'string', ['nullable' => false, 'defaultValue' => null, 'size' => 2])
			->update();

		$this->database()->update('markets')->set('exchange_code', '')->where('id', 1)->run();
		$this->database()->update('markets')->set('exchange_code', 'UQ')->where('id', 2)->run();
		$this->database()->update('markets')->set('exchange_code', 'UW')->where('id', 3)->run();
		$this->database()->update('markets')->set('exchange_code', 'UR')->where('id', 4)->run();
		$this->database()->update('markets')->set('exchange_code', 'UN')->where('id', 5)->run();
		$this->database()->update('markets')->set('exchange_code', 'UA')->where('id', 6)->run();
		$this->database()->update('markets')->set('exchange_code', 'UP')->where('id', 7)->run();
		$this->database()->update('markets')->set('exchange_code', 'PQ')->where('id', 8)->run();
		$this->database()->update('markets')->set('exchange_code', 'PQ')->where('id', 9)->run();
		$this->database()->update('markets')->set('exchange_code', 'PQ')->where('id', 10)->run();
		$this->database()->update('markets')->set('exchange_code', 'PQ')->where('id', 11)->run();
		$this->database()->update('markets')->set('exchange_code', 'PQ')->where('id', 12)->run();
		$this->database()->update('markets')->set('exchange_code', 'UF')->where('id', 13)->run();
		$this->database()->update('markets')->set('exchange_code', '')->where('id', 14)->run();
		$this->database()->update('markets')->set('exchange_code', 'LN')->where('id', 15)->run();
		$this->database()->update('markets')->set('exchange_code', 'FP')->where('id', 16)->run();
		$this->database()->update('markets')->set('exchange_code', 'BB')->where('id', 17)->run();
		$this->database()->update('markets')->set('exchange_code', 'NA')->where('id', 18)->run();
		$this->database()->update('markets')->set('exchange_code', 'PL')->where('id', 19)->run();
		$this->database()->update('markets')->set('exchange_code', 'GF')->where('id', 20)->run();
		$this->database()->update('markets')->set('exchange_code', 'GB')->where('id', 21)->run();
		$this->database()->update('markets')->set('exchange_code', 'GM')->where('id', 22)->run();
		$this->database()->update('markets')->set('exchange_code', 'GS')->where('id', 23)->run();
		$this->database()->update('markets')->set('exchange_code', 'GY')->where('id', 24)->run();
		$this->database()->update('markets')->set('exchange_code', 'GI')->where('id', 25)->run();
		$this->database()->update('markets')->set('exchange_code', 'GD')->where('id', 26)->run();
		$this->database()->update('markets')->set('exchange_code', 'GH')->where('id', 27)->run();
		$this->database()->update('markets')->set('exchange_code', 'AV')->where('id', 28)->run();
		$this->database()->update('markets')->set('exchange_code', 'FH')->where('id', 29)->run();
		$this->database()->update('markets')->set('exchange_code', 'ID')->where('id', 30)->run();
		$this->database()->update('markets')->set('exchange_code', '')->where('id', 31)->run();
		$this->database()->update('markets')->set('exchange_code', '')->where('id', 32)->run();
		$this->database()->update('markets')->set('exchange_code', 'IM')->where('id', 33)->run();
		$this->database()->update('markets')->set('exchange_code', 'NO')->where('id', 34)->run();
		$this->database()->update('markets')->set('exchange_code', 'SN')->where('id', 35)->run();
		$this->database()->update('markets')->set('exchange_code', 'SS')->where('id', 36)->run();
		$this->database()->update('markets')->set('exchange_code', 'SF')->where('id', 37)->run();
		$this->database()->update('markets')->set('exchange_code', 'NO')->where('id', 38)->run();
		$this->database()->update('markets')->set('exchange_code', 'KA')->where('id', 39)->run();
		$this->database()->update('markets')->set('exchange_code', 'RR')->where('id', 40)->run();
		$this->database()->update('markets')->set('exchange_code', 'TS')->where('id', 41)->run();
		$this->database()->update('markets')->set('exchange_code', 'DF')->where('id', 42)->run();
		$this->database()->update('markets')->set('exchange_code', '')->where('id', 43)->run();
		$this->database()->update('markets')->set('exchange_code', 'ET')->where('id', 44)->run();
		$this->database()->update('markets')->set('exchange_code', 'XT')->where('id', 45)->run();
		$this->database()->update('markets')->set('exchange_code', 'CK')->where('id', 46)->run();
		$this->database()->update('markets')->set('exchange_code', 'HB')->where('id', 47)->run();
		$this->database()->update('markets')->set('exchange_code', 'LG')->where('id', 48)->run();
		$this->database()->update('markets')->set('exchange_code', 'LH')->where('id', 49)->run();
		$this->database()->update('markets')->set('exchange_code', 'CF')->where('id', 50)->run();
		$this->database()->update('markets')->set('exchange_code', 'QF')->where('id', 51)->run();
		$this->database()->update('markets')->set('exchange_code', 'CT')->where('id', 52)->run();
		$this->database()->update('markets')->set('exchange_code', 'CV')->where('id', 53)->run();
		$this->database()->update('markets')->set('exchange_code', 'AF')->where('id', 54)->run();
		$this->database()->update('markets')->set('exchange_code', 'BS')->where('id', 55)->run();
		$this->database()->update('markets')->set('exchange_code', 'CC')->where('id', 56)->run();
		$this->database()->update('markets')->set('exchange_code', 'MM')->where('id', 57)->run();
		$this->database()->update('markets')->set('exchange_code', 'PE')->where('id', 58)->run();
		$this->database()->update('markets')->set('exchange_code', '')->where('id', 59)->run();
		$this->database()->update('markets')->set('exchange_code', 'AT')->where('id', 60)->run();
		$this->database()->update('markets')->set('exchange_code', 'CS')->where('id', 61)->run();
		$this->database()->update('markets')->set('exchange_code', 'CG')->where('id', 62)->run();
		$this->database()->update('markets')->set('exchange_code', 'HK')->where('id', 63)->run();
		$this->database()->update('markets')->set('exchange_code', 'IB')->where('id', 64)->run();
		$this->database()->update('markets')->set('exchange_code', 'IS')->where('id', 65)->run();
		$this->database()->update('markets')->set('exchange_code', 'IJ')->where('id', 66)->run();
		$this->database()->update('markets')->set('exchange_code', 'IT')->where('id', 67)->run();
		$this->database()->update('markets')->set('exchange_code', 'JT')->where('id', 68)->run();
		$this->database()->update('markets')->set('exchange_code', 'JS')->where('id', 69)->run();
		$this->database()->update('markets')->set('exchange_code', 'MK')->where('id', 70)->run();
		$this->database()->update('markets')->set('exchange_code', 'QD')->where('id', 71)->run();

		$this->database()->update('markets')->set('mic', 'XSAU')->where('id', 72)->run();
		$this->database()->update('markets')->set('exchange_code', 'AB')->where('id', 72)->run();

		$this->database()->update('markets')->set('exchange_code', 'SP')->where('id', 73)->run();
		$this->database()->update('markets')->set('exchange_code', 'KP')->where('id', 74)->run();
		$this->database()->update('markets')->set('exchange_code', 'KQ')->where('id', 75)->run();
		$this->database()->update('markets')->set('exchange_code', 'KE')->where('id', 76)->run();
		$this->database()->update('markets')->set('exchange_code', 'TT')->where('id', 77)->run();
		$this->database()->update('markets')->set('exchange_code', 'TB')->where('id', 78)->run();
		$this->database()->update('markets')->set('exchange_code', 'DH')->where('id', 79)->run();
		$this->database()->update('markets')->set('exchange_code', 'EC')->where('id', 80)->run();
		$this->database()->update('markets')->set('exchange_code', 'SJ')->where('id', 81)->run();
		$this->database()->update('markets')->set('exchange_code', 'NZ')->where('id', 82)->run();
		$this->database()->update('markets')->set('exchange_code', '')->where('id', 83)->run();

		$this->table('tickers')
			->addColumn('isin', 'string', ['nullable' => true, 'defaultValue' => null, 'size' => 255])
			->update();
	}

	public function down(): void
	{
		$this->table('tickers')
			->dropColumn('isin')
			->update();

		$this->table('markets')
			->dropColumn('exchange_code')
			->update();
	}
}
