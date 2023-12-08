<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use FinGather\Dto\BrokerDto;
use FinGather\Model\Entity\Enum\BrokerImportTypeEnum;
use FinGather\Model\Repository\BrokerRepository;

class BrokerProvider
{
	public function __construct(private readonly BrokerRepository $brokerRepository)
	{
	}

	/** @return list<BrokerDto> */
	public function getBrokers(): array
	{
		$brokers = [];

		foreach ($this->brokerRepository->findBrokers(1) as $broker) {
			$brokers[] = new BrokerDto(
				id: $broker->getId(),
				userId: $broker->getUser()->getId(),
				name: $broker->getName(),
				importType: BrokerImportTypeEnum::from($broker->getImportType()),
			);
		}

		return $brokers;
	}

	public function getBroker(int $brokerId): ?BrokerDto
	{
		$broker = $this->brokerRepository->findBroker($brokerId, 1);
		if ($broker === null) {
			return null;
		}

		return new BrokerDto(
			id: $broker->getId(),
			userId: $broker->getUser()->getId(),
			name: $broker->getName(),
			importType: BrokerImportTypeEnum::from($broker->getImportType()),
		);
	}
}
