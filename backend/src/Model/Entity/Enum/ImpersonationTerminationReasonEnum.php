<?php

declare(strict_types=1);

namespace FinGather\Model\Entity\Enum;

enum ImpersonationTerminationReasonEnum: string
{
	case StoppedByAdmin = 'StoppedByAdmin';
	case Expired = 'Expired';
	case AdminRoleRevoked = 'AdminRoleRevoked';
	case ForcedLogout = 'ForcedLogout';
}
