<?php

declare(strict_types=1);

namespace FinGather\Service\Import\Factory;

use FinGather\Model\Entity\Enum\BrokerImportTypeEnum;
use FinGather\Service\Import\Mapper\AnycoinMapper;
use FinGather\Service\Import\Mapper\BinanceMapper;
use FinGather\Service\Import\Mapper\CoinbaseMapper;
use FinGather\Service\Import\Mapper\DegiroMapper;
use FinGather\Service\Import\Mapper\EtoroMapper;
use FinGather\Service\Import\Mapper\InteractiveBrokersMapper;
use FinGather\Service\Import\Mapper\MapperInterface;
use FinGather\Service\Import\Mapper\PortuMapper;
use FinGather\Service\Import\Mapper\RevolutMapper;
use FinGather\Service\Import\Mapper\Trading212Mapper;
use FinGather\Service\Import\Mapper\XtbMapper;

final class ImportMapperFactory
{
	private const ImportMappers = [
		BrokerImportTypeEnum::Trading212->value => Trading212Mapper::class,
		BrokerImportTypeEnum::InteractiveBrokers->value => InteractiveBrokersMapper::class,
		BrokerImportTypeEnum::Xtb->value => XtbMapper::class,
		BrokerImportTypeEnum::Etoro->value => EtoroMapper::class,
		BrokerImportTypeEnum::Revolut->value => RevolutMapper::class,
		BrokerImportTypeEnum::Anycoin->value => AnycoinMapper::class,
		BrokerImportTypeEnum::Degiro->value => DegiroMapper::class,
		BrokerImportTypeEnum::Portu->value => PortuMapper::class,
		BrokerImportTypeEnum::Coinbase->value => CoinbaseMapper::class,
		BrokerImportTypeEnum::Binance->value => BinanceMapper::class,
	];

	public function createImportMapper(string $fileName, string $contents): MapperInterface
	{
		foreach (self::ImportMappers as $mapperClass) {
			$importMapper = new $mapperClass();
			if ($importMapper->check($contents, $fileName)) {
				return $importMapper;
			}
		}

		throw new \RuntimeException('Import mapper not found');
	}
}
