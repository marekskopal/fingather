<?php

declare(strict_types=1);

namespace FinGather\Service\Authentication;

final class ImpersonationDenylist
{
	/**
	 * Endpoints that must NOT be callable while impersonating another user.
	 * Each entry is `[method, pathPattern]`. The path pattern is matched as a
	 * literal prefix when it ends with `/`, otherwise as an exact match. Use
	 * `*` as a placeholder for `{id:number}` style parameters.
	 */
	private const Rules = [
		['DELETE', '/api/current-user'],
		['PUT', '/api/current-user'],
		['PUT', '/api/current-user/locale'],
		['POST', '/api/authentication/password-reset-request'],
		['POST', '/api/authentication/password-reset'],
		['POST', '/api/api-keys/'],
		['DELETE', '/api/api-key/'],
	];

	public static function isBlocked(string $method, string $path): bool
	{
		$method = strtoupper($method);

		foreach (self::Rules as [$ruleMethod, $rulePath]) {
			if ($ruleMethod !== $method) {
				continue;
			}

			if (str_ends_with($rulePath, '/')) {
				if (str_starts_with($path, $rulePath)) {
					return true;
				}
				continue;
			}

			if ($path === $rulePath) {
				return true;
			}
		}

		return false;
	}
}
