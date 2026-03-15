<?php

declare(strict_types=1);

namespace FinGather\Email;

use FinGather\Dto\PasswordResetQueueDto;

final class PasswordResetEmail
{
	private const array Translations = [
		'en' => [
			'hi' => 'Hi,',
			'intro' => 'We received a request to reset the password for your <b style="{colorWhite}">FinGather</b> account.',
			'action' => 'To reset your password, click the link below. The link is valid for <b style="{colorWhite}">24 hours</b>.',
			'ignore' => 'If you did not request a password reset, you can safely ignore this email. Your password will not be changed.',
			'team' => 'The FinGather Team',
			'auto' => 'This email was sent automatically.',
		],
		'cs' => [
			'hi' => 'Ahoj,',
			'intro' => 'Obdrželi jsme žádost o reset hesla pro váš účet <b style="{colorWhite}">FinGather</b>.',
			'action' => 'Pro reset hesla klikněte na odkaz níže. Odkaz je platný po dobu <b style="{colorWhite}">24 hodin</b>.',
			'ignore' => 'Pokud jste o reset hesla nežádali, můžete tento e-mail bezpečně ignorovat. Vaše heslo nebude změněno.',
			'team' => 'Tým FinGather',
			'auto' => 'Tento e-mail byl odeslán automaticky.',
		],
		'de' => [
			'hi' => 'Hallo,',
			'intro' => 'Wir haben eine Anfrage erhalten, das Passwort für Ihr <b style="{colorWhite}">FinGather</b>-Konto zurückzusetzen.',
			'action' => 'Um Ihr Passwort zurückzusetzen, klicken Sie auf den untenstehenden Link. Der Link ist <b style="{colorWhite}">24 Stunden</b> gültig.',
			'ignore' => 'Wenn Sie keine Passwortzurücksetzung angefordert haben, können Sie diese E-Mail ignorieren. Ihr Passwort wird nicht geändert.',
			'team' => 'Das FinGather-Team',
			'auto' => 'Diese E-Mail wurde automatisch gesendet.',
		],
		'es' => [
			'hi' => 'Hola,',
			'intro' => 'Recibimos una solicitud para restablecer la contraseña de tu cuenta de <b style="{colorWhite}">FinGather</b>.',
			'action' => 'Para restablecer tu contraseña, haz clic en el enlace de abajo. El enlace es válido durante <b style="{colorWhite}">24 horas</b>.',
			'ignore' => 'Si no solicitaste un restablecimiento de contraseña, puedes ignorar este correo electrónico de forma segura. Tu contraseña no cambiará.',
			'team' => 'El equipo de FinGather',
			'auto' => 'Este correo electrónico fue enviado automáticamente.',
		],
		'fr' => [
			'hi' => 'Bonjour,',
			'intro' => 'Nous avons reçu une demande de réinitialisation du mot de passe de votre compte <b style="{colorWhite}">FinGather</b>.',
			'action' => 'Pour réinitialiser votre mot de passe, cliquez sur le lien ci-dessous. Le lien est valable <b style="{colorWhite}">24 heures</b>.',
			'ignore' => 'Si vous n\'avez pas demandé de réinitialisation de mot de passe, vous pouvez ignorer cet e-mail en toute sécurité. Votre mot de passe ne sera pas modifié.',
			'team' => 'L\'équipe FinGather',
			'auto' => 'Cet e-mail a été envoyé automatiquement.',
		],
	];

	public static function getHtml(PasswordResetQueueDto $passwordReset): string
	{
		$url = self::getUrl($passwordReset);
		$t = self::Translations[$passwordReset->user->locale->value];

		$colorGray = 'color: #b0b0b0';
		$colorWhite = 'color: #ffffff';
		$colorGreen = 'color: #a4e04f';
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
