<?php

declare(strict_types=1);

namespace FinGather\Email;

use FinGather\Model\Entity\Enum\AlertConditionEnum;
use FinGather\Model\Entity\Enum\PriceAlertTypeEnum;
use FinGather\Model\Entity\PriceAlert;

final class PriceAlertEmail
{
	/** @param array<string, string> $t */
	public static function getHtml(PriceAlert $alert, string $currentValue, array $t): string
	{
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

		$tdLabelStyle = 'style="' . $fontStyleTable . $colorGray . 'padding: 8px 16px 8px 0;"';
		$tdValueStyle = 'style="' . $fontStyleTable . $colorWhite . 'padding: 8px 0; text-align: right;"';

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
