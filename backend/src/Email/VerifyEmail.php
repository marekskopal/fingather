<?php

declare(strict_types=1);

namespace FinGather\Email;

use FinGather\Dto\EmailVerifyDto;

final class VerifyEmail
{
	private const array Translations = [
		'en' => [
			'hi' => 'Hi,',
			'intro' => 'Thank you for registering with <b style="{colorWhite}">FinGather</b> – your ultimate portfolio tracker. We\'re excited to have you on board!',
			'action' => 'To complete your registration, <b style="{colorWhite}">please confirm your email</b> by clicking the link below:',
			'features' => 'Once your email is confirmed, you\'ll have full access to all <b>FinGather</b> features, including managing your assets, tracking gain and returns, and generating detailed portfolio reports.',
			'support' => 'If you have any questions or need assistance, feel free to reach out to us.',
			'thanks' => 'Thank you for trusting us with your investment journey,',
			'team' => 'The FinGather Team',
			'auto' => 'This email was sent automatically.',
		],
		'cs' => [
			'hi' => 'Ahoj,',
			'intro' => 'Děkujeme za registraci do <b style="{colorWhite}">FinGather</b> – vašeho dokonalého sledovače portfolia. Jsme rádi, že jste s námi!',
			'action' => 'Chcete-li dokončit registraci, <b style="{colorWhite}">potvrďte prosím svůj e-mail</b> kliknutím na odkaz níže:',
			'features' => 'Po potvrzení e-mailu budete mít plný přístup ke všem funkcím <b>FinGather</b>, včetně správy aktiv, sledování zisku a výnosů a generování podrobných zpráv o portfoliu.',
			'support' => 'Pokud máte jakékoli dotazy nebo potřebujete pomoc, neváhejte nás kontaktovat.',
			'thanks' => 'Děkujeme, že nám svěřujete svou investiční cestu,',
			'team' => 'Tým FinGather',
			'auto' => 'Tento e-mail byl odeslán automaticky.',
		],
		'de' => [
			'hi' => 'Hallo,',
			'intro' => 'Vielen Dank für Ihre Registrierung bei <b style="{colorWhite}">FinGather</b> – Ihrem ultimativen Portfolio-Tracker. Wir freuen uns, Sie an Bord zu haben!',
			'action' => 'Um Ihre Registrierung abzuschließen, <b style="{colorWhite}">bestätigen Sie bitte Ihre E-Mail</b> durch Klicken auf den unten stehenden Link:',
			'features' => 'Nach der Bestätigung Ihrer E-Mail haben Sie vollen Zugriff auf alle <b>FinGather</b>-Funktionen, einschließlich der Verwaltung Ihrer Assets, der Verfolgung von Gewinnen und Renditen sowie der Erstellung detaillierter Portfolio-Berichte.',
			'support' => 'Wenn Sie Fragen haben oder Hilfe benötigen, zögern Sie nicht, uns zu kontaktieren.',
			'thanks' => 'Vielen Dank, dass Sie uns Ihre Investitionsreise anvertrauen,',
			'team' => 'Das FinGather-Team',
			'auto' => 'Diese E-Mail wurde automatisch gesendet.',
		],
		'es' => [
			'hi' => 'Hola,',
			'intro' => 'Gracias por registrarte en <b style="{colorWhite}">FinGather</b> – tu rastreador de portafolio definitivo. ¡Estamos emocionados de tenerte a bordo!',
			'action' => 'Para completar tu registro, <b style="{colorWhite}">por favor confirma tu correo electrónico</b> haciendo clic en el enlace de abajo:',
			'features' => 'Una vez que confirmes tu correo electrónico, tendrás acceso completo a todas las funciones de <b>FinGather</b>, incluyendo la gestión de tus activos, el seguimiento de ganancias y rendimientos, y la generación de informes detallados de portafolio.',
			'support' => 'Si tienes alguna pregunta o necesitas ayuda, no dudes en contactarnos.',
			'thanks' => 'Gracias por confiar en nosotros con tu viaje de inversión,',
			'team' => 'El equipo de FinGather',
			'auto' => 'Este correo electrónico fue enviado automáticamente.',
		],
		'fr' => [
			'hi' => 'Bonjour,',
			'intro' => 'Merci de vous être inscrit sur <b style="{colorWhite}">FinGather</b> – votre outil de suivi de portefeuille ultime. Nous sommes ravis de vous accueillir !',
			'action' => 'Pour finaliser votre inscription, <b style="{colorWhite}">veuillez confirmer votre e-mail</b> en cliquant sur le lien ci-dessous :',
			'features' => 'Une fois votre e-mail confirmé, vous aurez un accès complet à toutes les fonctionnalités de <b>FinGather</b>, notamment la gestion de vos actifs, le suivi des gains et des rendements, et la génération de rapports de portefeuille détaillés.',
			'support' => 'Si vous avez des questions ou besoin d\'aide, n\'hésitez pas à nous contacter.',
			'thanks' => 'Merci de nous faire confiance pour votre parcours d\'investissement,',
			'team' => 'L\'équipe FinGather',
			'auto' => 'Cet e-mail a été envoyé automatiquement.',
		],
	];

	public static function getHtml(EmailVerifyDto $emailVerify): string
	{
		$url = self::getUrl($emailVerify);
		$t = self::Translations[$emailVerify->user->locale->value];

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

			<p style="{$fontStyleGray}">{$t['features']}</p>
			<p style="{$fontStyleGray}">{$t['support']}</p>
			<p style="{$fontStyleGray}">{$t['thanks']}</p>
			<p style="{$fontStyleGray}">{$t['team']}</p>

			<hr>

			<p style="{$fontStyleGray}">{$t['auto']}</p>
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
