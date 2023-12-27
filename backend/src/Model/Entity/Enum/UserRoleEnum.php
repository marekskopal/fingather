<?php

declare(strict_types=1);

namespace FinGather\Model\Entity\Enum;

enum UserRoleEnum: string
{
	case User = 'User';
	case Admin = 'Admin';
}
