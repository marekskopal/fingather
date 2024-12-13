<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use FinGather\Model\Entity\Broker;
use FinGather\Model\Entity\Enum\BrokerImportTypeEnum;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\User;
use FinGather\Model\Repository\BrokerRepository;

class BrokerProvider
{
	public function __construct(private readonly BrokerRepository $brokerRepository)
	{
	}

	/** @return \Iterator<Broker> */
	public function getBrokers(User $user, Portfolio $portfolio): \Iterator
	{
		return $this->brokerRepository->findBrokers($user->id, $portfolio->id);
	}

	public function getBroker(User $user, int $brokerId): ?Broker
	{
		return $this->brokerRepository->findBroker($brokerId, $user->id);
	}

	public function getBrokerByImportType(User $user, Portfolio $portfolio, BrokerImportTypeEnum $importType): ?Broker
	{
		return $this->brokerRepository->findBrokerByImportType($user->id, $portfolio->id, $importType);
	}

	public function createBroker(User $user, Portfolio $portfolio, string $name, BrokerImportTypeEnum $importType): Broker
	{
		$broker = new Broker(user: $user, portfolio: $portfolio, name: $name, importType: $importType);
		$this->brokerRepository->persist($broker);

		return $broker;
	}

	public function updateBroker(Broker $broker, string $name, BrokerImportTypeEnum $importType): Broker
	{
		$broker->name = $name;
		$broker->importType = $importType;
		$this->brokerRepository->persist($broker);

		return $broker;
	}

	public function deleteBroker(Broker $broker): void
	{
		$this->brokerRepository->delete($broker);
	}
}
