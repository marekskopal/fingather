<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use FinGather\Dto\BrokerDto;
use FinGather\Model\Entity\Broker;
use FinGather\Model\Entity\Enum\BrokerImportTypeEnum;
use FinGather\Model\Entity\User;
use FinGather\Model\Repository\BrokerRepository;

class BrokerProvider
{
	public function __construct(private readonly BrokerRepository $brokerRepository)
	{
	}

	/** @return list<BrokerDto> */
	public function getBrokers(User $user): array
	{
		$brokers = [];

		foreach ($this->brokerRepository->findBrokers($user->getId()) as $broker) {
			$brokers[] = BrokerDto::fromEntity($broker);
		}

		return $brokers;
	}

	public function getBroker(User $user, int $brokerId): ?BrokerDto
	{
		$broker = $this->brokerRepository->findBroker($brokerId, $user->getId());
		if ($broker === null) {
			return null;
		}

		return BrokerDto::fromEntity($broker);
	}

	public function createBroker(User $user, string $name, BrokerImportTypeEnum $importType): BrokerDto
	{
		$broker = new Broker(user: $user, name: $name, importType: $importType->value);
		$this->brokerRepository->persist($broker);

		return BrokerDto::fromEntity($broker);
	}

	public function updateBroker(BrokerDto $broker, string $name, BrokerImportTypeEnum $importType): BrokerDto
	{
		$broker = $this->brokerRepository->findBroker($broker->id, $broker->userId);
		assert($broker instanceof Broker);

		$broker->setName($name);
		$broker->setImportType($importType->value);
		$this->brokerRepository->persist($broker);

		return BrokerDto::fromEntity($broker);
	}

	public function deleteBroker(BrokerDto $broker): void
	{
		$broker = $this->brokerRepository->findBroker($broker->id, $broker->userId);
		assert($broker instanceof Broker);
		$this->brokerRepository->delete($broker);
	}
}
