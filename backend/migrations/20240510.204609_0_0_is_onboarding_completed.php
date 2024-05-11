<?php

declare(strict_types=1);

namespace Migration;

use Cycle\Migrations\Migration;

class IsOnboardingCompletedMigration extends Migration
{
	protected const DATABASE = 'default';

	public function up(): void
	{
		$this->table('users')
			->addColumn('is_onboarding_completed', 'boolean', [
				'nullable' => false,
				'defaultValue' => false,
				'size' => 1,
				'autoIncrement' => false,
				'unsigned' => false,
				'zerofill' => false,
			])
			->update();
	}

	public function down(): void
	{
		$this->table('users')
			->dropColumn('is_onboarding_completed')
			->update();
	}
}
