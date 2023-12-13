<?php

declare(strict_types=1);

namespace FinGather\Model\Repository;

use Cycle\ORM\EntityManager;
use Cycle\ORM\ORM;
use Cycle\ORM\Select;
use Cycle\ORM\Select\Repository;

/**
 * @template TEntity of object
 * @extends Repository<TEntity>
 * @method array<int, TEntity> findAll(array $scope = [], array $orderBy = [])
 */
abstract class ARepository extends Repository
{
	protected readonly EntityManager $entityManager;

	public function __construct(Select $select, protected readonly ORM $orm)
	{
		parent::__construct($select);

		$this->entityManager = new EntityManager($this->orm);
	}

	/** @param TEntity $entity */
	public function persist(object $entity): void
	{
		$this->entityManager->persist($entity)->run();
	}

	/** @param TEntity $entity */
	public function delete(object $entity): void
	{
		$this->entityManager->delete($entity)->run();
	}
}
