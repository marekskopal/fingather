<?php

declare(strict_types=1);

namespace FinGather\Email;

use FinGather\Model\Entity\Enum\GoalTypeEnum;
use FinGather\Model\Entity\Enum\LocaleEnum;
use FinGather\Model\Entity\Goal;

final class GoalEmail
{
	private const array Translations = [
		'en' => [
			'portfolioValueGoal' => 'Portfolio Value Goal',
			'returnPercentageGoal' => 'Return % Goal',
			'investedAmountGoal' => 'Invested Amount Goal',
			'targetPortfolioValue' => 'Target Portfolio Value',
			'targetReturn' => 'Target Return %',
			'targetInvested' => 'Target Invested Amount',
			'currentPortfolioValue' => 'Current Portfolio Value',
			'currentReturn' => 'Current Return %',
			'currentInvested' => 'Current Invested Amount',
			'achieved' => 'Achieved!',
			'reached' => 'Your goal has been reached.',
			'deadline' => 'Deadline',
			'auto' => 'This email was sent automatically. You can disable email notifications in your account settings.',
		],
		'cs' => [
			'portfolioValueGoal' => 'Cíl hodnoty portfolia',
			'returnPercentageGoal' => 'Cíl výnosu %',
			'investedAmountGoal' => 'Cíl investované částky',
			'targetPortfolioValue' => 'Cílová hodnota portfolia',
			'targetReturn' => 'Cílový výnos %',
			'targetInvested' => 'Cílová investovaná částka',
			'currentPortfolioValue' => 'Aktuální hodnota portfolia',
			'currentReturn' => 'Aktuální výnos %',
			'currentInvested' => 'Aktuální investovaná částka',
			'achieved' => 'Dosaženo!',
			'reached' => 'Váš cíl byl dosažen.',
			'deadline' => 'Termín',
			'auto' => 'Tento e-mail byl odeslán automaticky. Emailová upozornění můžete vypnout v nastavení účtu.',
		],
		'de' => [
			'portfolioValueGoal' => 'Portfolio-Wert Ziel',
			'returnPercentageGoal' => 'Rendite % Ziel',
			'investedAmountGoal' => 'Investierter Betrag Ziel',
			'targetPortfolioValue' => 'Ziel-Portfolio-Wert',
			'targetReturn' => 'Zielrendite %',
			'targetInvested' => 'Ziel investierter Betrag',
			'currentPortfolioValue' => 'Aktueller Portfolio-Wert',
			'currentReturn' => 'Aktuelle Rendite %',
			'currentInvested' => 'Aktuell investierter Betrag',
			'achieved' => 'Erreicht!',
			'reached' => 'Ihr Ziel wurde erreicht.',
			'deadline' => 'Frist',
			'auto' => 'Diese E-Mail wurde automatisch gesendet. Sie können E-Mail-Benachrichtigungen in Ihren Kontoeinstellungen deaktivieren.',
		],
		'es' => [
			'portfolioValueGoal' => 'Meta de valor de portafolio',
			'returnPercentageGoal' => 'Meta de retorno %',
			'investedAmountGoal' => 'Meta de monto invertido',
			'targetPortfolioValue' => 'Valor objetivo del portafolio',
			'targetReturn' => 'Retorno objetivo %',
			'targetInvested' => 'Monto invertido objetivo',
			'currentPortfolioValue' => 'Valor actual del portafolio',
			'currentReturn' => 'Retorno actual %',
			'currentInvested' => 'Monto invertido actual',
			'achieved' => '¡Logrado!',
			'reached' => 'Tu meta ha sido alcanzada.',
			'deadline' => 'Fecha límite',
			'auto' => 'Este correo electrónico fue enviado automáticamente. Puedes desactivar las notificaciones por correo electrónico en la configuración de tu cuenta.',
		],
		'fr' => [
			'portfolioValueGoal' => 'Objectif de valeur du portefeuille',
			'returnPercentageGoal' => 'Objectif de rendement %',
			'investedAmountGoal' => 'Objectif de montant investi',
			'targetPortfolioValue' => 'Valeur cible du portefeuille',
			'targetReturn' => 'Rendement cible %',
			'targetInvested' => 'Montant investi cible',
			'currentPortfolioValue' => 'Valeur actuelle du portefeuille',
			'currentReturn' => 'Rendement actuel %',
			'currentInvested' => 'Montant investi actuel',
			'achieved' => 'Atteint !',
			'reached' => 'Votre objectif a été atteint.',
			'deadline' => 'Échéance',
			'auto' => 'Cet e-mail a été envoyé automatiquement. Vous pouvez désactiver les notifications par e-mail dans les paramètres de votre compte.',
		],
	];

	public static function getHtml(Goal $goal, string $currentValue, LocaleEnum $locale = LocaleEnum::En): string
	{
		$t = self::Translations[$locale->value];

		$colorGray = 'color: #b0b0b0';
		$colorWhite = 'color: #ffffff';
		$colorGreen = 'color: #a4e04f';
		$fontStyle = 'font-family: Geist, Helvetica, Arial, sans-serif; font-size: 16px; line-height: 1.5;';
		$fontStyleTable = 'font-family: Geist, Helvetica, Arial, sans-serif; font-size: 18px; line-height: 1.5;';
		$fontStyleGray = $fontStyle . $colorGray;
		$fontStyleWhite = $fontStyle . $colorWhite;

		$goalTypeLabel = match ($goal->type) {
			GoalTypeEnum::PortfolioValue => $t['portfolioValueGoal'],
			GoalTypeEnum::ReturnPercentage => $t['returnPercentageGoal'],
			GoalTypeEnum::InvestedAmount => $t['investedAmountGoal'],
		};

		$targetLabel = match ($goal->type) {
			GoalTypeEnum::PortfolioValue => $t['targetPortfolioValue'],
			GoalTypeEnum::ReturnPercentage => $t['targetReturn'],
			GoalTypeEnum::InvestedAmount => $t['targetInvested'],
		};

		$currentLabel = match ($goal->type) {
			GoalTypeEnum::PortfolioValue => $t['currentPortfolioValue'],
			GoalTypeEnum::ReturnPercentage => $t['currentReturn'],
			GoalTypeEnum::InvestedAmount => $t['currentInvested'],
		};

		$suffix = $goal->type === GoalTypeEnum::ReturnPercentage ? ' %' : '';
		$targetFormatted = number_format((float) $goal->targetValue->toString(), 2) . $suffix;
		$currentFormatted = number_format((float) $currentValue, 2) . $suffix;

		$portfolioName = $goal->portfolio->name;

		$tdLabelStyle = 'style=' . $fontStyleTable . $colorGray . 'padding: 8px 16px 8px 0;';
		$tdValueStyle = 'style=' . $fontStyleTable . $colorWhite . 'padding: 8px 0; text-align: right;';

		$deadlineRow = '';
		if ($goal->deadline !== null) {
			$deadlineFormatted = $goal->deadline->format('Y-m-d');
			$deadlineLabel = $t['deadline'];
			$deadlineRow = <<<HTML
			<tr>
				<td {$tdLabelStyle}>{$deadlineLabel}</td>
				<td {$tdValueStyle}>{$deadlineFormatted}</td>
			</tr>
HTML;
		}

		return <<<HTML
<html>
<body style="{$fontStyleGray} background-color:#000000">
	<div style="padding: 96px 24px; background-color:#000000; border-radius: 16px">
		<div style="margin: 0 auto 48px; width: 650px; text-align: center">
			<img src="https://www.fingather.com/app/images/fingather.png" alt="FinGather" title="FinGather" width="280" height="70" style="display:block" />
		</div>

		<div style="margin: 0 auto; padding: 24px; width: 650px; background-color: #262626; border-radius: 16px">
			<p style="{$fontStyleWhite} font-size: 20px; font-weight: bold;">{$goalTypeLabel} {$t['achieved']}</p>
			<p style="{$fontStyleGray}">{$portfolioName} &mdash; {$t['reached']}</p>

			<table style="width: 100%; border-collapse: collapse; margin-top: 16px;">
				<tr style="border-bottom: 1px solid #333;">
					<td {$tdLabelStyle}>{$targetLabel}</td>
					<td {$tdValueStyle}>{$targetFormatted}</td>
				</tr>
				<tr style="border-bottom: 1px solid #333;">
					<td {$tdLabelStyle}>{$currentLabel}</td>
					<td style="{$fontStyleTable}{$colorGreen} padding: 8px 0; text-align: right;"><b>{$currentFormatted}</b></td>
				</tr>
				{$deadlineRow}
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
