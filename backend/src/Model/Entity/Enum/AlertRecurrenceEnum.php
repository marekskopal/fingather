<?php

declare(strict_types=1);

namespace FinGather\Model\Entity\Enum;

enum AlertRecurrenceEnum: string
{
	case OneTime = 'OneTime';
	case Recurring = 'Recurring';
}
