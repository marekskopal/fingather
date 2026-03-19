<?php

declare(strict_types=1);

namespace FinGather\App\ServiceProvider;

use FinGather\Service\Cache\CacheFactory;
use FinGather\Service\Cache\CacheFactoryInterface;
use FinGather\Service\Translator\TranslatorService;
use FinGather\Service\Translator\TranslatorServiceInterface;
use League\Container\ServiceProvider\AbstractServiceProvider;
use Predis\ClientInterface;

final class CacheServiceProvider extends AbstractServiceProvider
{
	public function provides(string $id): bool
	{
		return in_array($id, [
			CacheFactoryInterface::class,
			TranslatorServiceInterface::class,
		], true);
	}

	public function register(): void
	{
		$container = $this->getContainer();

		$container->add(CacheFactoryInterface::class, CacheFactory::class);

		$container->add(TranslatorServiceInterface::class, static function () use ($container): TranslatorService {
			$cacheFactory = $container->get(CacheFactoryInterface::class);
			if (!$cacheFactory instanceof CacheFactoryInterface) {
				throw new \RuntimeException('CacheFactory not found in container.');
			}
			return new TranslatorService(
				translationsDir: __DIR__ . '/../../../translations',
				cache: $cacheFactory->create(namespace: 'translator'),
			);
		});
	}
}
