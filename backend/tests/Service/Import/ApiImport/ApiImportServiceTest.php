<?php

declare(strict_types=1);

namespace FinGather\Tests\Service\Import\ApiImport;

use DateTimeImmutable;
use FinGather\Dto\ApiImportPrepareCheckDto;
use FinGather\Dto\ApiImportProcessCheckDto;
use FinGather\Model\Entity\ApiImport;
use FinGather\Model\Entity\ApiKey;
use FinGather\Model\Entity\Broker;
use FinGather\Model\Entity\Currency;
use FinGather\Model\Entity\Enum\ApiImportStatusEnum;
use FinGather\Model\Entity\Enum\ApiKeyTypeEnum;
use FinGather\Model\Entity\Enum\BrokerImportTypeEnum;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\User;
use FinGather\Service\Import\ApiImport\ApiImportService;
use FinGather\Service\Import\ApiImport\Exception\ApiKeyUnauthorizedException;
use FinGather\Service\Import\ApiImport\Factory\ProcessorFactoryInterface;
use FinGather\Service\Import\ApiImport\Processor\ProcessorInterface;
use FinGather\Service\Provider\ApiImportProviderInterface;
use FinGather\Service\Provider\ApiKeyProviderInterface;
use FinGather\Service\Provider\BrokerProviderInterface;
use FinGather\Tests\Fixtures\Model\Entity\BrokerFixture;
use FinGather\Tests\Fixtures\Model\Entity\PortfolioFixture;
use FinGather\Tests\Fixtures\Model\Entity\UserFixture;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

#[CoversClass(ApiImportService::class)]
#[UsesClass(ApiImportPrepareCheckDto::class)]
#[UsesClass(ApiImportProcessCheckDto::class)]
#[UsesClass(ApiImport::class)]
#[UsesClass(ApiKey::class)]
#[UsesClass(Broker::class)]
#[UsesClass(Currency::class)]
#[UsesClass(Portfolio::class)]
#[UsesClass(User::class)]
#[UsesClass(BrokerImportTypeEnum::class)]
final class ApiImportServiceTest extends TestCase
{
	public function testPrepareImportSkipsApiKeyWithError(): void
	{
		$apiKey = $this->createApiKey(error: 'Unauthorized');

		$processorFactory = $this->createMock(ProcessorFactoryInterface::class);
		$processorFactory->expects(self::never())->method('create');

		$apiKeyProvider = $this->createMock(ApiKeyProviderInterface::class);
		$apiKeyProvider->method('getApiKey')->willReturn($apiKey);
		$apiKeyProvider->expects(self::never())->method('setApiKeyError');

		$service = $this->createService(processorFactory: $processorFactory, apiKeyProvider: $apiKeyProvider);
		$service->prepareImport(ApiImportPrepareCheckDto::fromApiKeyEntity($apiKey));
	}

	public function testPrepareImportMarksApiKeyOnUnauthorized(): void
	{
		$apiKey = $this->createApiKey();

		$processor = self::createStub(ProcessorInterface::class);
		$processor->method('prepare')->willThrowException(new ApiKeyUnauthorizedException('Unauthorized'));

		$apiKeyProvider = $this->createMock(ApiKeyProviderInterface::class);
		$apiKeyProvider->method('getApiKey')->willReturn($apiKey);
		$apiKeyProvider->expects(self::once())->method('setApiKeyError')->with($apiKey, 'Unauthorized');

		$logger = $this->createMock(LoggerInterface::class);
		$logger->expects(self::once())->method('warning');

		$service = $this->createService(
			processorFactory: $this->createProcessorFactory($processor),
			apiKeyProvider: $apiKeyProvider,
			logger: $logger,
		);
		$service->prepareImport(ApiImportPrepareCheckDto::fromApiKeyEntity($apiKey));
	}

	public function testPrepareImportKeepsApiKeyOnSuccess(): void
	{
		$apiKey = $this->createApiKey();

		$processor = $this->createMock(ProcessorInterface::class);
		$processor->expects(self::once())->method('prepare')->with($apiKey);

		$apiKeyProvider = $this->createMock(ApiKeyProviderInterface::class);
		$apiKeyProvider->method('getApiKey')->willReturn($apiKey);
		$apiKeyProvider->expects(self::never())->method('setApiKeyError');

		$service = $this->createService(processorFactory: $this->createProcessorFactory($processor), apiKeyProvider: $apiKeyProvider);
		$service->prepareImport(ApiImportPrepareCheckDto::fromApiKeyEntity($apiKey));
	}

	public function testProcessImportMarksApiImportAndApiKeyOnUnauthorized(): void
	{
		$apiKey = $this->createApiKey();
		$apiImport = new ApiImport(
			user: $apiKey->user,
			portfolio: $apiKey->portfolio,
			apiKey: $apiKey,
			status: ApiImportStatusEnum::Waiting,
			dateFrom: new DateTimeImmutable('2026-01-01'),
			dateTo: new DateTimeImmutable('2026-09-01'),
			reportId: 123,
			error: null,
		);
		$apiImport->id = 5;

		$processor = self::createStub(ProcessorInterface::class);
		$processor->method('process')->willThrowException(new ApiKeyUnauthorizedException('Unauthorized'));

		$apiKeyProvider = $this->createMock(ApiKeyProviderInterface::class);
		$apiKeyProvider->method('getApiKey')->willReturn($apiKey);
		$apiKeyProvider->expects(self::once())->method('setApiKeyError')->with($apiKey, 'Unauthorized');

		$apiImportProvider = $this->createMock(ApiImportProviderInterface::class);
		$apiImportProvider->method('getApiImport')->willReturn($apiImport);
		$apiImportProvider->expects(self::once())
			->method('updateApiImport')
			->with($apiImport, ApiImportStatusEnum::Error, 'Unauthorized')
			->willReturn($apiImport);

		$service = $this->createService(
			processorFactory: $this->createProcessorFactory($processor),
			apiKeyProvider: $apiKeyProvider,
			apiImportProvider: $apiImportProvider,
		);
		$service->processImport(new ApiImportProcessCheckDto(apiImportId: $apiImport->id));
	}

	private function createApiKey(?string $error = null): ApiKey
	{
		$user = UserFixture::getUser();
		$apiKey = new ApiKey(
			user: $user,
			portfolio: PortfolioFixture::getPortfolio(user: $user),
			type: ApiKeyTypeEnum::Trading212,
			apiKey: 'encrypted',
			error: $error,
		);
		$apiKey->id = 9;

		return $apiKey;
	}

	private function createProcessorFactory(ProcessorInterface $processor): ProcessorFactoryInterface
	{
		$processorFactory = self::createStub(ProcessorFactoryInterface::class);
		$processorFactory->method('create')->willReturn($processor);

		return $processorFactory;
	}

	private function createService(
		ProcessorFactoryInterface $processorFactory,
		ApiKeyProviderInterface $apiKeyProvider,
		?ApiImportProviderInterface $apiImportProvider = null,
		?LoggerInterface $logger = null,
	): ApiImportService {
		$brokerProvider = self::createStub(BrokerProviderInterface::class);
		$brokerProvider->method('getBrokerByImportType')->willReturn(BrokerFixture::getBroker());

		return new ApiImportService(
			processorFactory: $processorFactory,
			apiKeyProvider: $apiKeyProvider,
			apiImportProvider: $apiImportProvider ?? self::createStub(ApiImportProviderInterface::class),
			brokerProvider: $brokerProvider,
			logger: $logger ?? self::createStub(LoggerInterface::class),
		);
	}
}
