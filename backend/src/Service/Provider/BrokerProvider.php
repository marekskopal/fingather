<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use Cycle\ORM\ORM;

class BrokerProvider
{
	public function __construct(
		private readonly ORM $orm,
	) {
	}

	public function getBrokers(): array
	{
		//$this->orm->getRepository()
	}
}
