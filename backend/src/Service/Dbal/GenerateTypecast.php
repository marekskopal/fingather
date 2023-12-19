<?php

declare(strict_types=1);

namespace FinGather\Service\Dbal;

use Cycle\Database\Schema\AbstractColumn;
use Cycle\Schema\Definition\Entity;
use Cycle\Schema\GeneratorInterface;
use Cycle\Schema\Registry;

final class GenerateTypecast implements GeneratorInterface
{
	public function run(Registry $registry): Registry
	{
		foreach ($registry as $entity) {
			$this->compute($registry, $entity);
		}

		return $registry;
	}

	/** @phpstan-ignore-next-line */
	protected function compute(Registry $registry, Entity $entity): void
	{
		if (!$registry->hasTable($entity)) {
			return;
		}

		$table = $registry->getTableSchema($entity);

		foreach ($entity->getFields() as $field) {
			if ($field->hasTypecast() || !$table->hasColumn($field->getColumn())) {
				continue;
			}

			$column = $table->column($field->getColumn());

			$field->setTypecast($this->typecast($column));
		}
	}

	private function typecast(AbstractColumn $column): ?string
	{
		if ($column->getAbstractType() === 'decimal') {
			return 'string';
		}

		switch ($column->getType()) {
			case AbstractColumn::BOOL:
				return 'bool';
			case AbstractColumn::INT:
				return 'int';
			case AbstractColumn::FLOAT:
				return 'float';
			case 'decimal':
				return 'string';
		}

		if (in_array($column->getAbstractType(), ['datetime', 'date', 'time', 'timestamp'], strict: true)) {
			return 'datetime';
		}

		return null;
	}
}
