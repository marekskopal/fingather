<?php

declare(strict_types=1);

namespace Migration;

use Cycle\Migrations\Migration;

class AddImportUuidMigration extends Migration
{
	protected const DATABASE = 'default';

	public function up(): void
	{
		$this->database()->query('TRUNCATE `import_files`');
		$this->database()->query('DELETE FROM `imports`');
		$this->database()->query('ALTER TABLE `imports` AUTO_INCREMENT = 1');

		$this->table('imports')
			->addColumn('uuid', 'uuid', ['nullable' => false, 'defaultValue' => null, 'size' => 36])
			->addIndex(['uuid'], ['name' => 'imports_index_uuid', 'unique' => true])
			->update();
	}

	public function down(): void
	{
		$this->table('imports')
			->dropIndex(['uuid'])
			->dropColumn('uuid')
			->update();
	}
}
