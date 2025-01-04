<?php

declare(strict_types=1);

namespace FinGather\Command;

use FinGather\Service\Cache\CacheFactory;
use FinGather\Service\Cache\CacheStorageEnum;
use Predis\Client;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class CacheClearCommand extends AbstractCommand
{
	private const OptionStorage = 'storage';

	protected function configure(): void
	{
		$this->setName('cache:clear');

		$this->addOption(self::OptionStorage, 's', InputOption::VALUE_OPTIONAL, 'Storage type (memcached, redis)');
	}

	protected function process(InputInterface $input, OutputInterface $output): int
	{
		$client = new Client('tcp://' . getenv('REDIS_HOST') . ':' . getenv('REDIS_PORT'), [
			'parameters' => [
				'password' => (string) getenv('REDIS_PASSWORD'),
			],
		]);

		$cacheFactory = new CacheFactory($client);

		/** @var string|null $storageOption */
		$storageOption = $input->getOption(self::OptionStorage);

		$storages = $storageOption === null ? [
			CacheStorageEnum::Memcached,
			CacheStorageEnum::Redis,
		] : [
			CacheStorageEnum::from($storageOption),
		];

		foreach ($storages as $storage) {
			$this->writeln(sprintf('Clearing cache for storage: %s', $storage->value), $output);
			$cache = $cacheFactory->create($storage);
			$cache->cleanAll();
		}

		return self::SUCCESS;
	}
}
