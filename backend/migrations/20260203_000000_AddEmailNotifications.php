<?php

declare(strict_types=1);

namespace Migrations;

use MarekSkopal\ORM\Enum\Type;
use MarekSkopal\ORM\Migrations\Migration\Migration;

final class AddEmailNotificationsMigration extends Migration
{
	public function up(): void
	{
		$this->table('users')
			->addColumn('is_email_notifications_enabled', Type::Boolean, default: true)
			->alter();
	}

	public function down(): void
	{
		$this->table('users')
			->dropColumn('is_email_notifications_enabled')
			->alter();
	}
}
