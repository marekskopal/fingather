<?php

declare(strict_types=1);

namespace Migration;

use Cycle\Migrations\Migration;

class TickerSectorIndustryCountryMigration extends Migration
{
	protected const DATABASE = 'default';

	public function up(): void
	{
		$this->table('industries')
			->addColumn('id', 'primary', [
				'nullable' => false,
				'defaultValue' => null,
				'size' => 11,
				'autoIncrement' => true,
				'unsigned' => false,
				'zerofill' => false,
			])
			->addColumn('name', 'string', ['nullable' => false, 'defaultValue' => null, 'size' => 255])
			->addColumn('is_others', 'boolean', ['nullable' => false, 'default' => false])
			->setPrimaryKeys(['id'])
			->addIndex(['name'], ['name' => 'industries_index_name', 'unique' => true])
			->create();

		$this->table('sectors')
			->addColumn('id', 'primary', [
				'nullable' => false,
				'defaultValue' => null,
				'size' => 11,
				'autoIncrement' => true,
				'unsigned' => false,
				'zerofill' => false,
			])
			->addColumn('name', 'string', ['nullable' => false, 'defaultValue' => null, 'size' => 255])
			->addColumn('is_others', 'boolean', ['nullable' => false, 'default' => false])
			->setPrimaryKeys(['id'])
			->addIndex(['name'], ['name' => 'sectors_index_name', 'unique' => true])
			->create();

		$this->database()->insert('industries')->values([
			['id' => 1, 'name' => 'Others', 'is_others' => true],
		])->run();
		$this->database()->insert('sectors')->values([
			['id' => 1, 'name' => 'Others', 'is_others' => true],
		])->run();

		$this->table('tickers')
			->addColumn('sector_id', 'integer', [
				'nullable' => false,
				'defaultValue' => 1,
				'size' => 11,
				'autoIncrement' => false,
				'unsigned' => false,
				'zerofill' => false,
			])
			->addColumn('industry_id', 'integer', [
				'nullable' => false,
				'defaultValue' => 1,
				'size' => 11,
				'autoIncrement' => false,
				'unsigned' => false,
				'zerofill' => false,
			])
			->addColumn('country_id', 'integer', [
				'nullable' => false,
				'defaultValue' => 240,
				'size' => 11,
				'autoIncrement' => false,
				'unsigned' => false,
				'zerofill' => false,
			])
			->addIndex(['sector_id'], ['name' => 'tickers_index_sector_id_664bb9e10c21b', 'unique' => false])
			->addIndex(['industry_id'], ['name' => 'tickers_index_industry_id_664bb9e10c26b', 'unique' => false])
			->addIndex(['country_id'], ['name' => 'tickers_index_country_id_664bb9e10c2bd', 'unique' => false])
			->addForeignKey(['sector_id'], 'sectors', ['id'], [
				'name' => 'tickers_foreign_sector_id_664bb9e10c225',
				'delete' => 'CASCADE',
				'update' => 'CASCADE',
				'indexCreate' => true,
			])
			->addForeignKey(['industry_id'], 'industries', ['id'], [
				'name' => 'tickers_foreign_industry_id_664bb9e10c272',
				'delete' => 'CASCADE',
				'update' => 'CASCADE',
				'indexCreate' => true,
			])
			->addForeignKey(['country_id'], 'countries', ['id'], [
				'name' => 'tickers_foreign_country_id_664bb9e10c2ee',
				'delete' => 'CASCADE',
				'update' => 'CASCADE',
				'indexCreate' => true,
			])
			->update();

		$this->database()->query(
			'INSERT INTO `industries` (`name`) SELECT DISTINCT `industry` FROM `tickers` WHERE `industry` IS NOT NULL AND `industry` != ""',
		);
		$this->database()->query(
			'UPDATE `tickers` SET `industry_id` = IFNULL((SELECT `id` FROM `industries` WHERE `name` = `industry` AND `industry` IS NOT NULL AND `industry` != ""), 1)',
		);

		$this->database()->query(
			'INSERT INTO `sectors` (`name`) SELECT DISTINCT `sector` FROM `tickers` WHERE `sector` IS NOT NULL AND `sector` != ""',
		);
		$this->database()->query(
			'UPDATE `tickers` SET `sector_id` = IFNULL((SELECT `id` FROM `sectors` WHERE `name` = `sector` AND `sector` IS NOT NULL AND `sector` != ""), 1)',
		);

		$this->database()->query(
			'UPDATE `tickers` SET `country_id` = IFNULL((SELECT `id` FROM `countries` WHERE `name` = `country` AND `country` IS NOT NULL), 240)',
		);

		$this->table('tickers')
			->dropColumn('sector')
			->dropColumn('industry')
			->dropColumn('country')
			->update();
	}

	public function down(): void
	{
		$this->table('tickers')
			->addColumn('sector', 'string', ['nullable' => true, 'defaultValue' => null, 'size' => 255])
			->addColumn('industry', 'string', ['nullable' => true, 'defaultValue' => null, 'size' => 255])
			->addColumn('country', 'string', ['nullable' => true, 'defaultValue' => null, 'size' => 255])
			->update();

		$this->table('tickers')
			->dropForeignKey(['sector_id'])
			->dropForeignKey(['industry_id'])
			->dropForeignKey(['country_id'])
			->dropIndex(['sector_id'])
			->dropIndex(['industry_id'])
			->dropIndex(['country_id'])
			->dropColumn('sector_id')
			->dropColumn('industry_id')
			->dropColumn('country_id')
			->update();
		$this->table('sectors')->drop();
		$this->table('industries')->drop();
	}
}
