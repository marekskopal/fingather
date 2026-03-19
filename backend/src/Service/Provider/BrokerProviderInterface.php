<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use FinGather\Model\Entity\Broker;
use FinGather\Model\Entity\Enum\BrokerImportTypeEnum;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\User;
use Iterator;

interface BrokerProviderInterface
{
	/** @return Iterator<Broker> */
	public function getBrokers(User $user, Portfolio $portfolio): Iterator;

	public function getBroker(User $user, int $brokerId): ?Broker;

	public function getBrokerByImportType(User $user, Portfolio $portfolio, BrokerImportTypeEnum $importType): ?Broker;

	public function createBroker(User $user, Portfolio $portfolio, string $name, BrokerImportTypeEnum $importType): Broker;

	public function updateBroker(Broker $broker, string $name, BrokerImportTypeEnum $importType): Broker;

	public function deleteBroker(Broker $broker): void;
}
