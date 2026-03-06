<?php

declare(strict_types=1);

namespace Migrations;

use MarekSkopal\ORM\Enum\Type;
use MarekSkopal\ORM\Migrations\Migration\Migration;

final class PasswordResetMigration extends Migration
{
	public function up(): void
	{
		$this->table('password_resets')
			->addColumn('id', Type::Int, autoincrement: true, primary: true)
			->addColumn('user_id', Type::Int, size: 11)
			->addColumn('token', Type::Uuid)
			->addColumn('created_at', Type::Timestamp)
			->addIndex(['token'], 'password_resets_token_index', false)
			->addIndex(['user_id'], 'password_resets_user_id_index', false)
			->addForeignKey('user_id', 'users', 'id', 'password_resets_user_id_users_id_fk')
			->create();
	}

	public function down(): void
	{
		$this->table('password_resets')
			->drop();
	}
}
