<?php

declare(strict_types=1);

namespace FinGather\Command;

use FinGather\App\ApplicationFactory;
use FinGather\Model\Entity\Enum\ApiImportStatusEnum;
use FinGather\Service\Provider\ApiImportProcessCheckProvider;
use FinGather\Service\Provider\ApiImportProvider;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class ApiImportProcessCheckCommand extends AbstractCommand
{
	protected function configure(): void
	{
		$this->setName('apiImport:processCheck');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$this->writeln('API import process check was started.', $output);

		$application = ApplicationFactory::create();

		$apiImportProvider = $application->container->get(ApiImportProvider::class);
		assert($apiImportProvider instanceof ApiImportProvider);

		$apiImportProcessCheckProvider = $application->container->get(ApiImportProcessCheckProvider::class);
		assert($apiImportProcessCheckProvider instanceof ApiImportProcessCheckProvider);

		foreach ($apiImportProvider->getApiImports(apiImportStatus: ApiImportStatusEnum::Waiting) as $apiImport) {
			$apiImportProcessCheckProvider->createApiImportProcessCheck($apiImport);
		}

		$this->writeln('API import process check was ended.', $output);

		return 0;
	}
}
