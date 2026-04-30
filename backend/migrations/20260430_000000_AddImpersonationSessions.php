<?php

declare(strict_types=1);

namespace Migrations;

use MarekSkopal\ORM\Enum\Type;
use MarekSkopal\ORM\Migrations\Migration\Migration;

final class AddImpersonationSessionsMigration extends Migration
{
	public function up(): void
	{
		$this->table('impersonation_sessions')
			->addColumn('id', Type::Int, autoincrement: true, primary: true)
			->addColumn('admin_user_id', Type::Int)
			->addColumn('target_user_id', Type::Int)
			->addColumn('started_at', Type::Timestamp)
			->addColumn('ended_at', Type::Timestamp, nullable: true)
			->addColumn('ip_address', Type::String, size: 45)
			->addColumn('user_agent', Type::String, size: 255)
			->addColumn('termination_reason', Type::String, size: 32, nullable: true)
			->addForeignKey('admin_user_id', 'users', 'id')
			->addForeignKey('target_user_id', 'users', 'id')
			->addIndex(['admin_user_id'], 'impersonation_sessions_admin_idx', false)
			->addIndex(['target_user_id'], 'impersonation_sessions_target_idx', false)
			->create();
	}

	public function down(): void
	{
		$this->table('impersonation_sessions')->drop();
	}
}
