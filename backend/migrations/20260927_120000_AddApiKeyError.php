<?php

declare(strict_types=1);

namespace Migrations;

use MarekSkopal\ORM\Enum\Type;
use MarekSkopal\ORM\Migrations\Migration\Migration;

final class AddApiKeyErrorMigration extends Migration
{
	public function up(): void
	{
		$this->table('api_keys')
			->addColumn('error', Type::Text, nullable: true)
			->alter();
	}

	public function down(): void
	{
		$this->table('api_keys')
			->dropColumn('error')
			->alter();
	}
}
