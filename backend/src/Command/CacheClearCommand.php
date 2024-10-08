<?php

declare(strict_types=1);

namespace FinGather\Command;

use FinGather\Service\Cache\CacheFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class CacheClearCommand extends Command
{
	protected function configure(): void
	{
		$this->setName('cache:clear');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$cache = CacheFactory::createPsrCache();
		$cache->clear();

		return 0;
	}
}
