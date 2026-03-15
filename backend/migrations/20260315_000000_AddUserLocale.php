<?php

declare(strict_types=1);

namespace Migrations;

use FinGather\Model\Entity\Enum\LocaleEnum;
use MarekSkopal\ORM\Enum\Type;
use MarekSkopal\ORM\Migrations\Migration\Migration;

final class AddUserLocaleMigration extends Migration
{
	public function up(): void
	{
		$this->table('users')
			->addColumn('locale', Type::Enum, enum: array_column(LocaleEnum::cases(), 'value'), default: LocaleEnum::En->value)
			->alter();
	}

	public function down(): void
	{
		$this->table('users')
			->dropColumn('locale')
			->alter();
	}
}
