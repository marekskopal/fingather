<?php

declare(strict_types=1);

namespace FinGather\Command;

use FinGather\App\ApplicationFactory;
use FinGather\Model\Entity\Enum\ApiImportStatusEnum;
use FinGather\Service\Provider\ApiImportProcessCheckProviderInterface;
use FinGather\Service\Provider\ApiImportProviderInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class ApiImportProcessCheckCommand extends AbstractCommand
{
	protected function configure(): void
	{
		$this->setName('apiImport:processCheck');
	}

	protected function process(InputInterface $input, OutputInterface $output): int
	{
		$application = ApplicationFactory::create();

		$apiImportProvider = $application->container->get(ApiImportProviderInterface::class);
		assert($apiImportProvider instanceof ApiImportProviderInterface);

		$apiImportProcessCheckProvider = $application->container->get(ApiImportProcessCheckProviderInterface::class);
		assert($apiImportProcessCheckProvider instanceof ApiImportProcessCheckProviderInterface);

		foreach ($apiImportProvider->getApiImports(apiImportStatus: ApiImportStatusEnum::Waiting) as $apiImport) {
			$apiImportProcessCheckProvider->createApiImportProcessCheck($apiImport);
		}

		return self::SUCCESS;
	}
}
