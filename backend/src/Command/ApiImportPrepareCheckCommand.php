<?php

declare(strict_types=1);

namespace FinGather\Command;

use FinGather\App\ApplicationFactory;
use FinGather\Service\Provider\ApiImportPrepareCheckProviderInterface;
use FinGather\Service\Provider\ApiKeyProviderInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class ApiImportPrepareCheckCommand extends AbstractCommand
{
	private const string ApiKeyId = 'apiKeyId';

	protected function configure(): void
	{
		$this->setName('apiImport:prepareCheck');
		$this->addArgument(self::ApiKeyId, InputArgument::OPTIONAL, 'API Key ID');
	}

	protected function process(InputInterface $input, OutputInterface $output): int
	{
		$application = ApplicationFactory::create();

		$apiKeyProvider = $application->container->get(ApiKeyProviderInterface::class);
		assert($apiKeyProvider instanceof ApiKeyProviderInterface);

		$apiImportCheckProvider = $application->container->get(ApiImportPrepareCheckProviderInterface::class);
		assert($apiImportCheckProvider instanceof ApiImportPrepareCheckProviderInterface);

		$apiKeyId = $input->getArgument(self::ApiKeyId);
		if (is_numeric($apiKeyId)) {
			$apiKeyEntity = $apiKeyProvider->getApiKey((int) $apiKeyId);
			if ($apiKeyEntity === null) {
				$this->writeln('API key ' . $apiKeyId . ' not found.', $output);

				return self::FAILURE;
			}

			$apiKeys = [$apiKeyEntity];
		} else {
			$apiKeys = iterator_to_array($apiKeyProvider->getApiKeys(), false);
		}

		foreach ($apiKeys as $apiKey) {
			$apiImportCheckProvider->createApiImportPrepareCheck($apiKey);
		}

		$this->writeln('Checked "' . count($apiKeys) . '" API keys.', $output);

		return self::SUCCESS;
	}
}
