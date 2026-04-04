<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use FinGather\Model\Entity\Ticker;
use FinGather\Service\DataCalculator\Dcf\Dto\DcfAssumptions;
use FinGather\Service\Provider\Dto\DcfValuationChipDto;
use FinGather\Service\Provider\Dto\DcfValuationView;

interface TickerDcfValuationProviderInterface
{
	public function getDcfValuationView(Ticker $ticker, ?DcfAssumptions $assumptions = null): ?DcfValuationView;

	public function getDcfValuationChip(Ticker $ticker): DcfValuationChipDto;

	public function createOrUpdateTickerDcfValuation(Ticker $ticker): void;
}
