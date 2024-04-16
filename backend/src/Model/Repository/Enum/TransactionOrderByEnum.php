<?php

declare(strict_types=1);

namespace FinGather\Model\Repository\Enum;

enum TransactionOrderByEnum: string
{
	case ActionCreated = 'action_created';
	case BrokerId = 'broker_id';
}
