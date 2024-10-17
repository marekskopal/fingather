<?php

declare(strict_types=1);

namespace FinGather\Email;

use FinGather\Dto\EmailVerifyDto;

final class VerifyEmail
{
	public static function getHtml(EmailVerifyDto $emailVerify): string
	{
		$url = self::getUrl($emailVerify);

		$colorGray = 'color: #b0b0b0';
		$colorWhite = 'color: #ffffff';
		$colorGreen = 'color: #a4e04f';
		$fontStyle = 'font-family: Geist, Helvetica, Arial, sans-serif; font-size: 16px; line-height: 1.5;';
		$fontStyleGray = $fontStyle . $colorGray;
		$fontStyleWhite = $fontStyle . $colorWhite;
		$fontStyleGreen = $fontStyle . $colorGreen;

		return <<<HTML
<html>
<body style="{$fontStyleGray} background-color:#000000">
	<div style="padding: 96px 24px;">
		<div style="margin: 0 auto 48px; width: 650px; text-align: center">
			<img src="https://www.fingather.com/app/images/fingather.png" alt="FinGather" title="FinGather" width="280" height="70" style="display:block" />
		</div>
		
		<div style="margin: 0 auto; padding: 24px; width: 650px; background-color: #262626; border-radius: 16px">
			<p style="{$fontStyleGray}">Hi,</p>
			<p style="{$fontStyleGray}">Thank you for registering with <b style="{$fontStyleWhite}">FinGather</b> – your ultimate portfolio tracker. We're excited to have you on board!</p>
			<p style="{$fontStyleGray}">To complete your registration, <b style="{$fontStyleWhite}">please confirm your email</b> by clicking the link below:</p>
		
			<p><a href="{$url}" style="{$fontStyleGreen} text-decoration: underline">{$url}</a></p>
		
			<p style="{$fontStyleGray}">Once your email is confirmed, you'll have full access to all <b>FinGather</b> features, including managing your assets, tracking gain and returns, and generating detailed portfolio reports.</p>
			<p style="{$fontStyleGray}">If you have any questions or need assistance, feel free to reach out to us.</p>
			<p style="{$fontStyleGray}">Thank you for trusting us with your investment journey,</p>
			<p style="{$fontStyleGray}">The FinGather Team</p>
		
			<hr>
		
			<p style="{$fontStyleGray}">This email was sent automatically.</p>
		</div>
	</div>
</body>
</html>
HTML;
	}

	private static function getUrl(EmailVerifyDto $emailVerify): string
	{
		$host = (string) getenv('PROXY_HOST');
		$port = (int) getenv('PROXY_PORT_SSL');

		return 'https://' . $host . ($port !== 443 ? ':' . $port : '') . '/email-verify/' . $emailVerify->token;
	}
}
