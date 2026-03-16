<?php

declare(strict_types=1);

namespace FinGather\Validator;

final class PasswordValidator
{
	public static function isValid(string $password): bool
	{
		return strlen($password) >= 8
			&& (bool) preg_match('/[A-Z]/', $password)
			&& (bool) preg_match('/[a-z]/', $password)
			&& (bool) preg_match('/[0-9]/', $password)
			&& (bool) preg_match('/[^A-Za-z0-9]/', $password);
	}
}
