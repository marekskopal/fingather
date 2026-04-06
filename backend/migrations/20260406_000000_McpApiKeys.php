<?php

declare(strict_types=1);

namespace Migrations;

use MarekSkopal\ORM\Enum\Type;
use MarekSkopal\ORM\Migrations\Migration\Migration;

final class McpApiKeysMigration extends Migration
{
	public function up(): void
	{
		$this->table('mcp_api_keys')
			->addColumn('id', Type::Int, autoincrement: true, primary: true)
			->addColumn('user_id', Type::Int)
			->addColumn('name', Type::String)
			->addColumn('api_key', Type::String)
			->addColumn('key_hash', Type::String, size: 64)
			->addColumn('created_at', Type::Timestamp)
			->addForeignKey('user_id', 'users', 'id')
			->addIndex(['key_hash'], 'mcp_api_keys_key_hash_unique', true)
			->create();
	}

	public function down(): void
	{
		$this->table('mcp_api_keys')->drop();
	}
}
