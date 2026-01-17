<?php

declare(strict_types=1);

namespace Migrations;

use MarekSkopal\ORM\Enum\Type;
use MarekSkopal\ORM\Migrations\Migration\Migration;

final class UserLoginTrackingMigration extends Migration
{
	public function up(): void
	{
		$this->table('users')
			->addColumn('last_logged_in', Type::Timestamp, nullable: true)
			->addColumn('last_refresh_token_generated', Type::Timestamp, nullable: true)
			->alter();
	}

	public function down(): void
	{
		$this->table('users')
			->dropColumn('last_refresh_token_generated')
			->dropColumn('last_logged_in')
			->alter();
	}
}
