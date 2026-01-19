<?php

declare(strict_types=1);

namespace Migrations;

use MarekSkopal\ORM\Enum\Type;
use MarekSkopal\ORM\Migrations\Migration\Migration;

final class AddGoogleIdMigration extends Migration
{
	public function up(): void
	{
		$this->table('users')
			->addColumn('google_id', Type::String, nullable: true)
			->alterColumn('password', Type::String, nullable: true)
			->alter();
	}

	public function down(): void
	{
		$this->table('users')
			->alterColumn('password', Type::String, nullable: false)
			->dropColumn('google_id')
			->alter();
	}
}
