<?php

declare(strict_types=1);

namespace FinGather\Email;

use FinGather\Model\Entity\Enum\AlertConditionEnum;
use FinGather\Model\Entity\Enum\LocaleEnum;
use FinGather\Model\Entity\Enum\PriceAlertTypeEnum;
use FinGather\Model\Entity\PriceAlert;

final class PriceAlertEmail
{
	private const array Translations = [
		'en' => [
			'priceAlert' => 'Price Alert',
			'portfolioAlert' => 'Portfolio Alert',
			'above' => 'above',
			'below' => 'below',
			'targetPrice' => 'Target Price',
			'currentPrice' => 'Current Price',
			'targetGain' => 'Target Gain %',
			'currentGain' => 'Current Gain %',
			'priceWent' => 'Price went',
			'gainWent' => 'Gain went',
			'condition' => 'Condition',
			'auto' => 'This email was sent automatically. You can disable email notifications in your account settings.',
		],
		'cs' => [
			'priceAlert' => 'Cenové upozornění',
			'portfolioAlert' => 'Upozornění portfolia',
			'above' => 'nad',
			'below' => 'pod',
			'targetPrice' => 'Cílová cena',
			'currentPrice' => 'Aktuální cena',
			'targetGain' => 'Cílový zisk %',
			'currentGain' => 'Aktuální zisk %',
			'priceWent' => 'Cena přešla',
			'gainWent' => 'Zisk přešel',
			'condition' => 'Podmínka',
			'auto' => 'Tento e-mail byl odeslán automaticky. Emailová upozornění můžete vypnout v nastavení účtu.',
		],
		'de' => [
			'priceAlert' => 'Kursalarm',
			'portfolioAlert' => 'Portfolio-Alarm',
			'above' => 'über',
			'below' => 'unter',
			'targetPrice' => 'Zielkurs',
			'currentPrice' => 'Aktueller Kurs',
			'targetGain' => 'Zielgewinn %',
			'currentGain' => 'Aktueller Gewinn %',
			'priceWent' => 'Kurs ging',
			'gainWent' => 'Gewinn ging',
			'condition' => 'Bedingung',
			'auto' => 'Diese E-Mail wurde automatisch gesendet. Sie können E-Mail-Benachrichtigungen in Ihren Kontoeinstellungen deaktivieren.',
		],
		'es' => [
			'priceAlert' => 'Alerta de precio',
			'portfolioAlert' => 'Alerta de portafolio',
			'above' => 'por encima de',
			'below' => 'por debajo de',
			'targetPrice' => 'Precio objetivo',
			'currentPrice' => 'Precio actual',
			'targetGain' => 'Ganancia objetivo %',
			'currentGain' => 'Ganancia actual %',
			'priceWent' => 'El precio fue',
			'gainWent' => 'La ganancia fue',
			'condition' => 'Condición',
			'auto' => 'Este correo electrónico fue enviado automáticamente. Puedes desactivar las notificaciones por correo electrónico en la configuración de tu cuenta.',
		],
		'fr' => [
			'priceAlert' => 'Alerte de prix',
			'portfolioAlert' => 'Alerte de portefeuille',
			'above' => 'au-dessus de',
			'below' => 'en dessous de',
			'targetPrice' => 'Prix cible',
			'currentPrice' => 'Prix actuel',
			'targetGain' => 'Gain cible %',
			'currentGain' => 'Gain actuel %',
			'priceWent' => 'Le prix est passé',
			'gainWent' => 'Le gain est passé',
			'condition' => 'Condition',
			'auto' => 'Cet e-mail a été envoyé automatiquement. Vous pouvez désactiver les notifications par e-mail dans les paramètres de votre compte.',
		],
	];

	public static function getHtml(PriceAlert $alert, string $currentValue, LocaleEnum $locale = LocaleEnum::En): string
	{
		$t = self::Translations[$locale->value];

		$colorGray = 'color: #b0b0b0';
		$colorWhite = 'color: #ffffff';
		$colorGreen = 'color: #a4e04f';
		$colorRed = 'color: #e04f4f';
		$fontStyle = 'font-family: Geist, Helvetica, Arial, sans-serif; font-size: 16px; line-height: 1.5;';
		$fontStyleTable = 'font-family: Geist, Helvetica, Arial, sans-serif; font-size: 18px; line-height: 1.5;';
		$fontStyleGray = $fontStyle . $colorGray;
		$fontStyleWhite = $fontStyle . $colorWhite;

		$alertTypeLabel = match ($alert->type) {
			PriceAlertTypeEnum::Price => $t['priceAlert'],
			PriceAlertTypeEnum::Portfolio => $t['portfolioAlert'],
		};

		$conditionLabel = match ($alert->condition) {
			AlertConditionEnum::Above => $t['above'],
			AlertConditionEnum::Below => $t['below'],
		};

		$conditionColor = match ($alert->condition) {
			AlertConditionEnum::Above => $colorGreen,
			AlertConditionEnum::Below => $colorRed,
		};

		if ($alert->type === PriceAlertTypeEnum::Price && $alert->ticker !== null) {
			$subjectName = $alert->ticker->name . ' (' . $alert->ticker->ticker . ')';
			$targetLabel = $t['targetPrice'];
			$currentLabel = $t['currentPrice'];
			$targetFormatted = $alert->targetValue->toFixed(2);
			$currentFormatted = $currentValue;
		} else {
			$subjectName = $alert->portfolio->name ?? 'Default Portfolio';
			$targetLabel = $t['targetGain'];
			$currentLabel = $t['currentGain'];
			$targetFormatted = $alert->targetValue->toFixed(2) . ' %';
			$currentFormatted = $currentValue . ' %';
		}

		$conditionText = $t['priceWent'] . ' ' . $conditionLabel . ' ' . $targetFormatted;
		if ($alert->type === PriceAlertTypeEnum::Portfolio) {
			$conditionText = $t['gainWent'] . ' ' . $conditionLabel . ' ' . $targetFormatted;
		}

		$tdLabelStyle = 'style=' . $fontStyleTable . $colorGray . 'padding: 8px 16px 8px 0;';
		$tdValueStyle = 'style=' . $fontStyleTable . $colorWhite . 'padding: 8px 0; text-align: right;';

		return <<<HTML
<html>
<body style="{$fontStyleGray} background-color:#000000">
	<div style="padding: 96px 24px; background-color:#000000; border-radius: 16px">
		<div style="margin: 0 auto 48px; width: 650px; text-align: center">
			<img src="https://www.fingather.com/app/images/fingather.png" alt="FinGather" title="FinGather" width="280" height="70" style="display:block" />
		</div>

		<div style="margin: 0 auto; padding: 24px; width: 650px; background-color: #262626; border-radius: 16px">
			<p style="{$fontStyleWhite} font-size: 20px; font-weight: bold;">{$alertTypeLabel}</p>
			<p style="{$fontStyleGray}">{$subjectName} &mdash; {$conditionText}</p>

			<table style="width: 100%; border-collapse: collapse; margin-top: 16px;">
				<tr style="border-bottom: 1px solid #333;">
					<td {$tdLabelStyle}>{$targetLabel}</td>
					<td {$tdValueStyle}>{$targetFormatted}</td>
				</tr>
				<tr style="border-bottom: 1px solid #333;">
					<td {$tdLabelStyle}>{$currentLabel}</td>
					<td style="{$fontStyleTable}{$conditionColor} padding: 8px 0; text-align: right;"><b>{$currentFormatted}</b></td>
				</tr>
				<tr>
					<td {$tdLabelStyle}>{$t['condition']}</td>
					<td {$tdValueStyle}>{$conditionLabel}</td>
				</tr>
			</table>

			<hr style="border-color: #333; margin-top: 24px;">

			<p style="{$fontStyleGray}">{$t['auto']}</p>
		</div>
	</div>
</body>
</html>
HTML;
	}
}
