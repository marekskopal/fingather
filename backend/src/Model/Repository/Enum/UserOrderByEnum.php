<?php

declare(strict_types=1);

namespace FinGather\Model\Repository\Enum;

enum UserOrderByEnum: string
{
	case Id = 'id';
	case Email = 'email';
	case Plan = 'plan';
	case LastLoggedIn = 'last_logged_in';
	case LastRefreshTokenGenerated = 'last_refresh_token_generated';
}
