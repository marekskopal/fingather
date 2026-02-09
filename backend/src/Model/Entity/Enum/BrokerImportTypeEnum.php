<?php

declare(strict_types=1);

namespace FinGather\Model\Entity\Enum;

enum BrokerImportTypeEnum: string
{
	case Trading212 = 'Trading212';
	case InteractiveBrokers = 'InteractiveBrokers';
	case Xtb = 'Xtb';
	case Etoro = 'Etoro';
	case Revolut = 'Revolut';
	case Anycoin = 'Anycoin';
	case Degiro = 'Degiro';
	case Portu = 'Portu';
	case Coinbase = 'Coinbase';
	case Binance = 'Binance';
	case FioBanka = 'FioBanka';
	case Patria = 'Patria';
}
