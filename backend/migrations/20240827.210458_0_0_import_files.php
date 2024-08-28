<?php

declare(strict_types=1);

namespace Migration;

use Cycle\Migrations\Migration;

class ImportFilesMigration extends Migration
{
	protected const DATABASE = 'default';

	public function up(): void
	{
		$this->table('import_files')
			->addColumn('id', 'primary', [
				'nullable' => false,
				'defaultValue' => null,
				'size' => 11,
				'autoIncrement' => true,
				'unsigned' => false,
				'zerofill' => false,
			])
			->addColumn('import_id', 'integer', [
				'nullable' => false,
				'defaultValue' => null,
				'size' => 11,
				'autoIncrement' => false,
				'unsigned' => false,
				'zerofill' => false,
			])
			->addColumn('created', 'timestamp', ['nullable' => false, 'defaultValue' => null])
			->addColumn('file_name', 'string', ['nullable' => false, 'defaultValue' => null, 'size' => 255])
			->addColumn('contents', 'longText', ['nullable' => false, 'defaultValue' => null])
			->addIndex(['import_id'], ['name' => 'import_files_index_import_id_66ce3f7a1506f', 'unique' => false])
			->addForeignKey(['import_id'], 'imports', ['id'], [
				'name' => 'import_files_foreign_import_id_66ce3f7a1509a',
				'delete' => 'CASCADE',
				'update' => 'CASCADE',
				'indexCreate' => true,
			])
			->setPrimaryKeys(['id'])
			->create();
	}

	public function down(): void
	{
		$this->table('import_files')->drop();
	}
}
