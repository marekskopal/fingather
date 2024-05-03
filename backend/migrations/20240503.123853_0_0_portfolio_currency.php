<?php

declare(strict_types=1);

namespace Migration;

use Cycle\Migrations\Migration;

final class PortfolioCurrencyMigration extends Migration
{
	protected const DATABASE = 'default';

	public function up(): void
	{
		$this->table('portfolios')
			->addColumn('currency_id', 'integer', [
				'nullable' => false,
				'defaultValue' => null,
				'size' => 11,
				'autoIncrement' => false,
				'unsigned' => false,
				'zerofill' => false,
			])->update();

		$this->database()->query(
			'UPDATE `portfolios` LEFT JOIN `users` ON `portfolios`.`user_id` = `users`.`id` SET `portfolios`.`currency_id` = `users`.`default_currency_id`',
		);

		$this->table('portfolios')
			->addIndex(['currency_id'], ['name' => 'portfolios_index_currency_id_6634daddd7d52', 'unique' => false])
			->addForeignKey(['currency_id'], 'currencies', ['id'], [
				'name' => 'portfolios_foreign_currency_id_6634daddd7d91',
				'delete' => 'CASCADE',
				'update' => 'CASCADE',
				'indexCreate' => true,
			])
			->update();

		$this->table('users')
			->dropForeignKey(['default_currency_id'])
			->dropIndex(['default_currency_id'])
			->dropColumn('default_currency_id')
			->update();
	}

	public function down(): void
	{
		$this->table('users')
			->addColumn('default_currency_id', 'integer', ['nullable' => false, 'default' => null, 'size' => 11])
			->addIndex(['default_currency_id'], ['name' => 'users_index_default_currency_id_657179dd4f5a5', 'unique' => false])
			->addForeignKey(['default_currency_id'], 'currencies', ['id'], [
				'name' => 'users_foreign_default_currency_id_657179dd4f5a8',
				'delete' => 'CASCADE',
				'update' => 'CASCADE',
				'indexCreate' => true,
			])
			->update();

		$this->database()->query(
			'UPDATE `users` LEFT JOIN `portfolios` ON `portfolios`.`user_id` = `users`.`id` SET `users`.`default_currency_id` = `portfolios`.`currency_id`',
		);

		$this->table('portfolios')
			->dropForeignKey(['currency_id'])
			->dropIndex(['currency_id'])
			->dropColumn('currency_id')
			->update();
	}
}
