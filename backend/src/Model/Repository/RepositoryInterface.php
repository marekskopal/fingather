<?php

declare(strict_types=1);

namespace FinGather\Model\Repository;

use Cycle\ORM\ORM;
use Cycle\ORM\Select;
use FinGather\Service\Dbal\QueryProvider;

/**
 * @template TEntity of object
 * @extends \Cycle\ORM\RepositoryInterface<TEntity>
 */
interface RepositoryInterface extends \Cycle\ORM\RepositoryInterface
{
	/**
	 * @param int $id
	 * @return TEntity|null
	 */
	//@phpstan-ignore-next-line
	public function findByPK(mixed $id): ?object;

	/**
	 * @param array<mixed> $scope
	 * @return TEntity|null
	 */
	public function findOne(array $scope = []): ?object;

	/**
	 * @param array<mixed> $scope
	 * @param array<string, string> $orderBy
	 * @return list<TEntity>
	 */
	public function findAll(array $scope = [], array $orderBy = []): array;

	/** @return Select<TEntity> */
	public function select(): Select;

	/** @param TEntity $entity */
	public function persist(object $entity): void;

	/** @param TEntity $entity */
	public function delete(object $entity): void;

	public function getOrm(): ORM;

	/** @return QueryProvider<TEntity> */
	public function getQueryProvider(): QueryProvider;

	/**
	 * @internal
	 * @param QueryProvider<TEntity> $queryProvider
	 */
	public function setQueryProvider(QueryProvider $queryProvider): void;
}
