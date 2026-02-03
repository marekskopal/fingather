<?php

declare(strict_types=1);

namespace FinGather\Email;

use FinGather\Service\DataCalculator\Dto\CalculatedDataDto;

final class PortfolioSummaryEmail
{
	public static function getHtml(string $portfolioName, string $currencySymbol, CalculatedDataDto $portfolioData,): string
	{
		$colorGray = 'color: #b0b0b0';
		$colorWhite = 'color: #ffffff';
		$colorGreen = 'color: #a4e04f';
		$colorRed = 'color: #e04f4f';
		$fontStyle = 'font-family: Geist, Helvetica, Arial, sans-serif; font-size: 16px; line-height: 1.5;';
		$fontStyleTable = 'font-family: Geist, Helvetica, Arial, sans-serif; font-size: 18px; line-height: 1.5;';
		$fontStyleTableLarge = 'font-family: Geist, Helvetica, Arial, sans-serif; font-size: 22px; line-height: 1.5;';
		$fontStyleGray = $fontStyle . $colorGray;
		$fontStyleWhite = $fontStyle . $colorWhite;

		$value = $currencySymbol . ' ' . $portfolioData->value->toFixed(2);
		$transactionValue = $currencySymbol . ' ' . $portfolioData->transactionValue->toFixed(2);

		$gainValue = $portfolioData->gain->toFixed(2);
		$gainIsPositive = !$portfolioData->gain->isNegative();
		$gainSign = $gainIsPositive ? '+' : '';
		$gainColor = $gainIsPositive ? $colorGreen : $colorRed;
		$gain = $gainSign . $currencySymbol . ' ' . $gainValue . ' (' . $gainSign . number_format(
			$portfolioData->gainPercentage,
			2,
		) . ' %)';

		$dividendYield = $currencySymbol . ' ' . $portfolioData->dividendYield->toFixed(2) . ' (' . number_format(
			$portfolioData->dividendYieldPercentage,
			2,
		) . ' %)';

		$returnValue = $portfolioData->return->toFixed(2);
		$returnIsPositive = !$portfolioData->return->isNegative();
		$returnSign = $returnIsPositive ? '+' : '';
		$returnColor = $returnIsPositive ? $colorGreen : $colorRed;
		$totalReturn = $returnSign . $currencySymbol . ' ' . $returnValue . ' (' . $returnSign . number_format(
			$portfolioData->returnPercentage,
			2,
		) . ' %)';

		$date = $portfolioData->date->format('F Y');

		$tdLabelStyle = "style=\"{$fontStyleTable}{$colorGray} padding: 8px 16px 8px 0;\"";
		$tdValueStyle = "style=\"{$fontStyleTable}{$colorWhite} padding: 8px 0; text-align: right;\"";
		$tdLabelStyleLarge = "style=\"{$fontStyleTableLarge}{$colorGray} padding: 12px 16px 12px 0;\"";
		$tdValueStyleLarge = "style=\"{$fontStyleTableLarge}{$colorWhite} padding: 12px 0; text-align: right;\"";

		return <<<HTML
<html>
<body style="{$fontStyleGray} background-color:#000000">
	<div style="padding: 96px 24px; background-color:#000000; border-radius: 16px">
		<div style="margin: 0 auto 48px; width: 650px; text-align: center">
			<img src="https://www.fingather.com/app/images/fingather.png" alt="FinGather" title="FinGather" width="280" height="70" style="display:block" />
		</div>

		<div style="margin: 0 auto; padding: 24px; width: 650px; background-color: #262626; border-radius: 16px">
			<p style="{$fontStyleWhite} font-size: 20px; font-weight: bold;">Monthly Portfolio Summary</p>
			<p style="{$fontStyleGray}">{$portfolioName} &mdash; {$date}</p>

			<table style="width: 100%; border-collapse: collapse; margin-top: 16px;">
				<tr style="border-bottom: 1px solid #333;">
					<td {$tdLabelStyleLarge}>Portfolio Value</td>
					<td {$tdValueStyleLarge}><b>{$value}</b></td>
				</tr>
				<tr style="border-bottom: 1px solid #333;">
					<td {$tdLabelStyle}>Invested Value</td>
					<td {$tdValueStyle}>{$transactionValue}</td>
				</tr>
				<tr style="border-bottom: 1px solid #333;">
					<td {$tdLabelStyle}>Gain/Loss</td>
					<td style="{$fontStyleTable}{$gainColor} padding: 8px 0; text-align: right;">{$gain}</td>
				</tr>
				<tr style="border-bottom: 1px solid #333;">
					<td {$tdLabelStyle}>Dividend Yield</td>
					<td {$tdValueStyle}>{$dividendYield}</td>
				</tr>
				<tr>
					<td {$tdLabelStyle}>Total Return</td>
					<td style="{$fontStyleTable}{$returnColor} padding: 8px 0; text-align: right;"><b>{$totalReturn}</b></td>
				</tr>
			</table>

			<hr style="border-color: #333; margin-top: 24px;">

			<p style="{$fontStyleGray}">This email was sent automatically. You can disable email notifications in your account settings.</p>
		</div>
	</div>
</body>
</html>
HTML;
	}
}
