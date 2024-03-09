<?php

declare(strict_types=1);

namespace FinGather\Service\Dbal;

use Cycle\ORM\Parser\CastableInterface;
use Cycle\ORM\Parser\UncastableInterface;
use Decimal\Decimal;

final class DecimalTypecast implements CastableInterface, UncastableInterface
{
	public const Type = 'decimal';

	/** @var array<string, string> */
	private array $rules = [];

	/**
	 * @param array<non-empty-string, mixed> $rules
	 * @return array<non-empty-string, mixed>
	 */
	public function setRules(array $rules): array
	{
		foreach ($rules as $key => $rule) {
			if ($rule === self::Type) {
				unset($rules[$key]);
				$this->rules[$key] = $rule;
			}
		}

		return $rules;
	}

	/**
	 * @param array<mixed> $data
	 * @return array<string, mixed>
	 */
	public function cast(array $data): array
	{
		foreach ($this->rules as $column => $rule) {
			if (!isset($data[$column])) {
				continue;
			}

			$data[$column] = new Decimal($data[$column]);
		}

		return $data;
	}

	/**
	 * @param array<mixed> $data
	 * @return array<string, mixed>
	 */
	public function uncast(array $data): array
	{
		foreach ($this->rules as $column => $rule) {
			if (!isset($data[$column]) || !$data[$column] instanceof Decimal) {
				continue;
			}

			$data[$column] = $data[$column]->toString();
		}

		return $data;
	}
}
