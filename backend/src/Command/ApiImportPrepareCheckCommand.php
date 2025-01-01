<?php

declare(strict_types=1);

namespace FinGather\Command;

use FinGather\App\ApplicationFactory;
use FinGather\Service\Provider\ApiImportPrepareCheckProvider;
use FinGather\Service\Provider\ApiKeyProvider;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class ApiImportPrepareCheckCommand extends AbstractCommand
{
	protected function configure(): void
	{
		$this->setName('apiImport:prepareCheck');
	}

	protected function process(InputInterface $input, OutputInterface $output): int
	{
		$application = ApplicationFactory::create();

		$apiKeyProvider = $application->container->get(ApiKeyProvider::class);
		assert($apiKeyProvider instanceof ApiKeyProvider);

		$apiImportCheckProvider = $application->container->get(ApiImportPrepareCheckProvider::class);
		assert($apiImportCheckProvider instanceof ApiImportPrepareCheckProvider);

		$apiKeys = iterator_to_array($apiKeyProvider->getApiKeys(), false);
		foreach ($apiKeys as $apiKey) {
			$apiImportCheckProvider->createApiImportPrepareCheck($apiKey);
		}

		$this->writeln('Checked "' . count($apiKeys) . '" API keys.', $output);

		return self::SUCCESS;
	}
}
