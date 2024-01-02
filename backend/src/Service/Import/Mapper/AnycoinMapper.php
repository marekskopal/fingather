<?php

declare(strict_types=1);

namespace FinGather\Service\Import\Mapper;

use FinGather\Utils\DateTimeUtils;
use Safe\DateTimeImmutable;

class AnycoinMapper implements MapperInterface
{
	/** @return array<string, string|callable> */
	public function getCsvMapping(): array
	{
		return [
			'actionType' => 'ACTION',
			'created' => fn (array $record): string => DateTimeUtils::formatZulu(
				DateTimeImmutable::createFromFormat('j. n. Y H:i:s', $record['DATE'])
			),
			'ticker' => fn (array $record): string => substr($record['SYMBOL'], 0, 3),
			'units' => 'QUANTY',
			'price' => 'PRICE',
			'currency' => fn (array $record): string => substr($record['SYMBOL'], 4, 3),
			'importIdentifier' => 'UID',
		];
	}

	/** @return array<string, string> */
	public function getTickerMapping(): array
	{
		return [
			'F.XNYS' => 'F',
			'META.XNAS' => 'META',
			'LMND.XNYS' => 'LMND',
			'TSLA.XNAS' => 'TSLA',
			'BA.XNYS' => 'BA',
			'UAL.XNAS' => 'UAL',
			'AAL.XNAS' => 'AAL',
			'AF.XPAR' => 'AF',
			'AIR.XPAR' => 'AIR',
			'LHA.XFRA' => 'LHA',
			'IAG.XLON' => 'IAG',
			'RY4C.XFRA' => 'RYA',
			'EZJ.XLON' => 'EZJ',
			'XOM.XNYS' => 'XOM',
			'PM.XNYS' => 'PM',
			'COIN.XNAS' => 'COIN',
			'MSFT.XNAS' => 'MSFT',
			'AAPL.XNAS' => 'AAPL',
			'ACB.XCNQ' => 'ACB',
			'NKLA.XNAS' => 'NKLA',
			'SPCE.XNYS' => 'SPCE',
			'NKE.XNYS' => 'NKE',
			'OTLY.XNAS' => 'OTLY',
			'BATS.XLON' => 'BATS',
			'CRSP.XNAS' => 'CRSP',
			'SPI.XNAS' => 'SPI',
			'TSM.XNYS' => 'TSM',
			'LCID.XNAS' => 'LCID',
			'INFY.XNYS' => 'INFY',
			'VUSA.XLON' => 'VUSA',
			'BRK-B.XNYS' => 'BRK.B',
			'RIVN.XNAS' => 'RIVN',
			'MO.XNYS' => 'MO',
			'IGLN.XLON' => 'IGLN',
			'IITU.XLON' => 'IITU',
			'INRG.XLON' => 'INRG',
			'ABBV.XNYS' => 'ABBV',
			'O.XNYS' => 'O',
			'NNN.XNYS' => 'NNN',
			'NFLX.XNAS' => 'NFLX',
			'DIS.XNYS' => 'DIS',
			'GOOGL.XNAS' => 'GOOGL',
			'VZ.XNYS' => 'VZ',
			'CVX.XNYS' => 'CVX',
			'GLE.XPAR' => 'GLE',
			'WPC.XNYS' => 'WPC',
			'PLTR.XNYS' => 'PLTR',
			'T.XNYS' => 'T',
			'GSBD.XNYS' => 'GSBD',
			'BNP.XPAR' => 'BNP',
			'VOD.XLON' => 'VOD',
			'BP.XLON' => 'BP',
			'WBD.XNAS' => 'WBD',
			'GLE.XFRA' => 'GLE',
			'VHYL.XLON' => 'VHYL',
			'VOW3.XFRA' => 'VOW3',
			'MC.XPAR' => 'MC',
			'CVS.XNYS' => 'CVS',
			'VICI.XNYS' => 'VICI',
			'NVDA.XNAS' => 'NVDA',
			'NIO.XNYS' => 'NIO',
		];
	}
}
