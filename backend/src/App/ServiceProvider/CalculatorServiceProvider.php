<?php

declare(strict_types=1);

namespace FinGather\App\ServiceProvider;

use FinGather\Service\DataCalculator\AssetDataCalculator;
use FinGather\Service\DataCalculator\AssetDataCalculatorInterface;
use FinGather\Service\DataCalculator\DataCalculator;
use FinGather\Service\DataCalculator\DataCalculatorInterface;
use FinGather\Service\DataCalculator\TaxReportRealizedGainsCalculator;
use FinGather\Service\DataCalculator\TaxReportRealizedGainsCalculatorInterface;
use League\Container\ServiceProvider\AbstractServiceProvider;

final class CalculatorServiceProvider extends AbstractServiceProvider
{
	public function provides(string $id): bool
	{
		return in_array($id, [
			AssetDataCalculatorInterface::class,
			DataCalculatorInterface::class,
			TaxReportRealizedGainsCalculatorInterface::class,
		], true);
	}

	public function register(): void
	{
		$container = $this->getContainer();

		$container->add(AssetDataCalculatorInterface::class, AssetDataCalculator::class);

		$container->add(DataCalculatorInterface::class, DataCalculator::class);

		$container->add(TaxReportRealizedGainsCalculatorInterface::class, TaxReportRealizedGainsCalculator::class);
	}
}
