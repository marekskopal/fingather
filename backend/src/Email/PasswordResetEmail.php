<?php

declare(strict_types=1);

namespace FinGather\Email;

use FinGather\Dto\PasswordResetQueueDto;

final class PasswordResetEmail
{
	/** @param array<string, string> $t */
	public static function getHtml(PasswordResetQueueDto $passwordReset, array $t): string
	{
		$url = self::getUrl($passwordReset);

		$colorGray = 'color: #b0b0b0;';
		$colorWhite = 'color: #ffffff;';
		$colorGreen = 'color: #a4e04f;';
		$fontStyle = 'font-family: Geist, Helvetica, Arial, sans-serif; font-size: 16px; line-height: 1.5;';
		$fontStyleGray = $fontStyle . $colorGray;
		$fontStyleWhite = $fontStyle . $colorWhite;
		$fontStyleGreen = $fontStyle . $colorGreen;

		$intro = str_replace('{colorWhite}', $fontStyleWhite, $t['intro']);
		$action = str_replace('{colorWhite}', $fontStyleWhite, $t['action']);

		return <<<HTML
<html>
<body style="{$fontStyleGray} background-color:#000000">
	<div style="padding: 96px 24px; background-color:#000000; border-radius: 16px">
		<div style="margin: 0 auto 48px; width: 650px; text-align: center">
			<img src="https://www.fingather.com/app/images/fingather.png" alt="FinGather" title="FinGather" width="280" height="70" style="display:block" />
		</div>

		<div style="margin: 0 auto; padding: 24px; width: 650px; background-color: #262626; border-radius: 16px">
			<p style="{$fontStyleGray}">{$t['hi']}</p>
			<p style="{$fontStyleGray}">{$intro}</p>
			<p style="{$fontStyleGray}">{$action}</p>

			<p><a href="{$url}" style="{$fontStyleGreen} text-decoration: underline">{$url}</a></p>

			<p style="{$fontStyleGray}">{$t['ignore']}</p>
			<p style="{$fontStyleGray}">{$t['team']}</p>

			<hr>

			<p style="{$fontStyleGray}">{$t['auto']}</p>
		</div>
	</div>
</body>
</html>
HTML;
	}

	private static function getUrl(PasswordResetQueueDto $passwordReset): string
	{
		$host = (string) getenv('PROXY_HOST');
		$port = (int) getenv('PROXY_PORT_SSL');

		return 'https://' . $host . ($port !== 443 ? ':' . $port : '') . '/authentication/reset-password/' . $passwordReset->token;
	}
}
