<?php

declare(strict_types=1);

namespace FinGather\Model\Repository;

use Cycle\ORM\EntityManager;
use Cycle\ORM\ORM;
use Cycle\ORM\Select;

/**
 * @template TEntity of object
 * @implements RepositoryInterface<TEntity>
 */
abstract class ARepository implements RepositoryInterface
{
	protected readonly EntityManager $entityManager;

	/** @param Select<TEntity> $select */
	public function __construct(private Select $select, protected readonly ORM $orm)
	{
		$this->entityManager = new EntityManager($this->orm);
	}

	public function __clone()
	{
		$this->select = clone $this->select;
	}

	/**
	 * @param int $id
	 * @return TEntity|null
	 */
	//@phpstan-ignore-next-line
	public function findByPK(mixed $id): ?object
	{
		return $this->select()->wherePK($id)->fetchOne();
	}

	/**
	 * @param array<mixed> $scope
	 * @return TEntity|null
	 */
	public function findOne(array $scope = []): ?object
	{
		return $this->select()->fetchOne($scope);
	}

	/**
	 * @param array<mixed> $scope
	 * @param array<string, string> $orderBy
	 * @return list<TEntity>
	 */
	public function findAll(array $scope = [], array $orderBy = []): array
	{
		return $this->select()->where($scope)->orderBy($orderBy)->fetchAll();
	}

	/** @return Select<TEntity> */
	public function select(): Select
	{
		return clone $this->select;
	}

	/** @param TEntity $entity */
	public function persist(object $entity, bool $cascade = false): void
	{
		$this->entityManager->persist($entity, $cascade)->run();
	}

	/** @param TEntity $entity */
	public function delete(object $entity): void
	{
		$this->entityManager->delete($entity)->run();
	}

	public function getOrm(): ORM
	{
		return $this->orm;
	}
}
