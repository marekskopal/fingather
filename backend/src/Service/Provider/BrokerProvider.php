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

	/** @return list<Broker> */
	public function getBrokers(User $user, Portfolio $portfolio): array
	{
		return $this->brokerRepository->findBrokers($user->getId(), $portfolio->getId());
	}

	public function getBroker(User $user, int $brokerId): ?Broker
	{
		return $this->brokerRepository->findBroker($brokerId, $user->getId());
	}

	public function getBrokerByImportType(User $user, Portfolio $portfolio, BrokerImportTypeEnum $importType): ?Broker
	{
		return $this->brokerRepository->findBrokerByImportType($user->getId(), $portfolio->getId(), $importType);
	}

	public function createBroker(User $user, Portfolio $portfolio, string $name, BrokerImportTypeEnum $importType): Broker
	{
		$broker = new Broker(user: $user, portfolio: $portfolio, name: $name, importType: $importType);
		$this->brokerRepository->persist($broker);

		return $broker;
	}

	public function updateBroker(Broker $broker, string $name, BrokerImportTypeEnum $importType): Broker
	{
		$broker->setName($name);
		$broker->setImportType($importType);
		$this->brokerRepository->persist($broker);

		return $broker;
	}

	public function deleteBroker(Broker $broker): void
	{
		$this->brokerRepository->delete($broker);
	}
}
