<?php

declare(strict_types=1);

namespace FinGather\App\ServiceProvider;

use FinGather\Service\Import\Factory\ImportMapperFactory;
use FinGather\Service\Import\Factory\ImportMapperFactoryInterface;
use FinGather\Service\Import\Factory\TransactionRecordFactory;
use FinGather\Service\Import\Factory\TransactionRecordFactoryInterface;
use League\Container\ServiceProvider\AbstractServiceProvider;

final class ImportServiceProvider extends AbstractServiceProvider
{
	public function provides(string $id): bool
	{
		return in_array($id, [
			ImportMapperFactoryInterface::class,
			TransactionRecordFactoryInterface::class,
		], true);
	}

	public function register(): void
	{
		$container = $this->getContainer();

		$container->add(ImportMapperFactoryInterface::class, ImportMapperFactory::class);
		$container->add(TransactionRecordFactoryInterface::class, TransactionRecordFactory::class);
	}
}
