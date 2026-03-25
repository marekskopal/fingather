<?php

declare(strict_types=1);

namespace FinGather\App\ServiceProvider;

use FinGather\Service\Logger\Logger;
use GuzzleHttp\Client as GuzzleClient;
use Http\Discovery\Psr17FactoryDiscovery;
use League\Container\ServiceProvider\AbstractServiceProvider;
use MarekSkopal\BuggregatorClient\Middleware\XhprofMiddleware;
use MarekSkopal\OpenFigi\OpenFigi;
use MarekSkopal\TwelveData\Config\Config;
use MarekSkopal\TwelveData\TwelveData;
use Predis\Client;
use Predis\ClientInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Log\LoggerInterface;

final class InfrastructureServiceProvider extends AbstractServiceProvider
{
	private const int TwelveDataTooManyRequestsRepeat = 20;

	public function provides(string $id): bool
	{
		$services = [
			LoggerInterface::class,
			ResponseFactoryInterface::class,
			GuzzleClient::class,
			ClientInterface::class,
			TwelveData::class,
			OpenFigi::class,
		];

		if ((bool) getenv('PROFILER_ENABLE') === true) {
			$services[] = XhprofMiddleware::class;
		}

		return in_array($id, $services, true);
	}

	public function register(): void
	{
		$container = $this->getContainer();

		$container->add(LoggerInterface::class, fn () => Logger::initLogger(__DIR__ . '/../../../log'));

		$container->add(
			ResponseFactoryInterface::class,
			fn (): ResponseFactoryInterface => Psr17FactoryDiscovery::findResponseFactory(),
		);

		$container->add(GuzzleClient::class, fn () => new GuzzleClient());

		$container->add(
			ClientInterface::class,
			fn (): ClientInterface => new Client('tcp://' . getenv('REDIS_HOST') . ':' . getenv('REDIS_PORT'), [
				'parameters' => [
					'password' => (string) getenv('REDIS_PASSWORD'),
				],
			]),
		);

		$container->add(
			TwelveData::class,
			fn (): TwelveData => new TwelveData(
				new Config((string) getenv('TWELVEDATA_API_KEY'), tooManyRequestsRepeat: self::TwelveDataTooManyRequestsRepeat),
			),
		);

		$openfigiApiKey = (string) getenv('OPENFIGI_API_KEY');
		$container->add(
			OpenFigi::class,
			fn (): OpenFigi => new OpenFigi(new \MarekSkopal\OpenFigi\Config\Config($openfigiApiKey !== '' ? $openfigiApiKey : null)),
		);

		if ((bool) getenv('PROFILER_ENABLE') !== true) {
			return;
		}

		$container->add(XhprofMiddleware::class, fn () => new XhprofMiddleware(
			appName: 'FinGather',
			url: (string) getenv('PROFILER_ENDPOINT'),
		));
	}
}
