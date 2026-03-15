<?php

declare(strict_types=1);

namespace FinGather\Email;

use DateTimeImmutable;
use Decimal\Decimal;
use FinGather\Dto\DividendCalendarItemDto;
use FinGather\Model\Entity\Enum\GoalTypeEnum;
use FinGather\Model\Entity\Goal;
use FinGather\Service\DataCalculator\Dto\CalculatedDataDto;

final class PortfolioSummaryEmail
{
	/**
	 * @param list<DividendCalendarItemDto> $upcomingDividends
	 * @param list<array{goal: Goal, progress: float}> $activeGoalsWithProgress
	 */
	public static function getHtml(
		string $portfolioName,
		string $currencySymbol,
		CalculatedDataDto $portfolioData,
		?CalculatedDataDto $previousMonthPortfolioData,
		array $upcomingDividends,
		array $activeGoalsWithProgress,
	): string {
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

		$tdLabelStyle = 'style=' . $fontStyleTable . $colorGray . 'padding: 8px 16px 8px 0;';
		$tdValueStyle = 'style=' . $fontStyleTable . $colorWhite . 'padding: 8px 0; text-align: right;';
		$tdLabelStyleLarge = 'style=' . $fontStyleTableLarge . $colorGray . 'padding: 12px 16px 12px 0;';
		$tdValueStyleLarge = 'style=' . $fontStyleTableLarge . $colorWhite . 'padding: 12px 0; text-align: right;';

		$monthChangeRowData = self::getMonthChangeRow(
			currencySymbol: $currencySymbol,
			portfolioData: $portfolioData,
			previousMonthPortfolioData: $previousMonthPortfolioData,
			fontStyleTable: $fontStyleTable,
			colorGray: $colorGray,
			colorGreen: $colorGreen,
			colorRed: $colorRed,
		);
		$monthChangeRowHtml = $monthChangeRowData['html'];
		$monthChangeRowSeparator = $monthChangeRowData['separator'];

		$dividendsSection = self::getDividendsSection(
			currencySymbol: $currencySymbol,
			upcomingDividends: $upcomingDividends,
			fontStyle: $fontStyle,
			fontStyleTable: $fontStyleTable,
			fontStyleWhite: $fontStyleWhite,
			colorGray: $colorGray,
			colorWhite: $colorWhite,
			colorGreen: $colorGreen,
		);

		$goalsSection = self::getGoalsSection(
			currencySymbol: $currencySymbol,
			activeGoalsWithProgress: $activeGoalsWithProgress,
			fontStyle: $fontStyle,
			fontStyleTable: $fontStyleTable,
			fontStyleWhite: $fontStyleWhite,
			colorGray: $colorGray,
			colorWhite: $colorWhite,
			colorGreen: $colorGreen,
		);

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
				<tr{$monthChangeRowSeparator}>
					<td {$tdLabelStyle}>Total Return</td>
					<td style="{$fontStyleTable}{$returnColor} padding: 8px 0; text-align: right;"><b>{$totalReturn}</b></td>
				</tr>
				{$monthChangeRowHtml}
			</table>

			{$dividendsSection}
			{$goalsSection}

			<hr style="border-color: #333; margin-top: 24px;">

			<p style="{$fontStyleGray}">This email was sent automatically. You can disable email notifications in your account settings.</p>
		</div>
	</div>
</body>
</html>
HTML;
	}

	/** @return array{html: string, separator: string} */
	private static function getMonthChangeRow(
		string $currencySymbol,
		CalculatedDataDto $portfolioData,
		?CalculatedDataDto $previousMonthPortfolioData,
		string $fontStyleTable,
		string $colorGray,
		string $colorGreen,
		string $colorRed,
	): array {
		if ($previousMonthPortfolioData === null) {
			return ['html' => '', 'separator' => ' style="border-bottom: 1px solid #333;"'];
		}

		$monthDelta = $portfolioData->value->sub($previousMonthPortfolioData->value);
		$monthDeltaIsPositive = !$monthDelta->isNegative();
		$monthDeltaSign = $monthDeltaIsPositive ? '+' : '';
		$monthDeltaColor = $monthDeltaIsPositive ? $colorGreen : $colorRed;

		$previousValue = $previousMonthPortfolioData->value;
		$monthDeltaPercentage = $previousValue->isZero()
			? 0.0
			: (float) $monthDelta->div($previousValue)->mul(new Decimal('100'))->toString();

		$monthChange = $monthDeltaSign . $currencySymbol . ' ' . $monthDelta->toFixed(2) . ' (' . $monthDeltaSign . number_format(
			$monthDeltaPercentage,
			2,
		) . ' %)';

		$tdLabelStyle = 'style=' . $fontStyleTable . $colorGray . 'padding: 8px 16px 8px 0;';

		$html = <<<HTML
			<tr>
				<td {$tdLabelStyle}>Month Change</td>
				<td style="{$fontStyleTable}{$monthDeltaColor} padding: 8px 0; text-align: right;">{$monthChange}</td>
			</tr>
HTML;

		return ['html' => $html, 'separator' => ' style="border-bottom: 1px solid #333;"'];
	}

	/** @param list<DividendCalendarItemDto> $upcomingDividends */
	private static function getDividendsSection(
		string $currencySymbol,
		array $upcomingDividends,
		string $fontStyle,
		string $fontStyleTable,
		string $fontStyleWhite,
		string $colorGray,
		string $colorWhite,
		string $colorGreen,
	): string {
		if ($upcomingDividends === []) {
			return '';
		}

		$rows = '';
		foreach ($upcomingDividends as $dividend) {
			$exDate = (new DateTimeImmutable($dividend->exDate))->format('d M');
			$ticker = htmlspecialchars($dividend->ticker->ticker);
			$amount = $currencySymbol . ' ' . $dividend->totalAmountDefaultCurrency->toFixed(2);

			$rows .= <<<HTML
				<tr style="border-bottom: 1px solid #333;">
					<td style="{$fontStyleTable}{$colorWhite} padding: 8px 16px 8px 0;">{$ticker}</td>
					<td style="{$fontStyleTable}{$colorGray} padding: 8px 16px 8px 0;">{$exDate}</td>
					<td style="{$fontStyleTable}{$colorGreen} padding: 8px 0; text-align: right;">+{$amount}</td>
				</tr>
HTML;
		}

		return <<<HTML

			<hr style="border-color: #333; margin-top: 24px;">
			<p style="{$fontStyleWhite} font-size: 18px; font-weight: bold; margin-bottom: 8px;">Upcoming Dividends</p>
			<p style="{$fontStyle}{$colorGray} margin-top: 0;">Next 30 days</p>
			<table style="width: 100%; border-collapse: collapse;">
				{$rows}
			</table>
HTML;
	}

	/** @param list<array{goal: Goal, progress: float}> $activeGoalsWithProgress */
	private static function getGoalsSection(
		string $currencySymbol,
		array $activeGoalsWithProgress,
		string $fontStyle,
		string $fontStyleTable,
		string $fontStyleWhite,
		string $colorGray,
		string $colorWhite,
		string $colorGreen,
	): string {
		if ($activeGoalsWithProgress === []) {
			return '';
		}

		$rows = '';
		foreach ($activeGoalsWithProgress as ['goal' => $goal, 'progress' => $progress]) {
			$label = match ($goal->type) {
				GoalTypeEnum::PortfolioValue => 'Portfolio Value ' . $currencySymbol . ' ' . $goal->targetValue->toFixed(0),
				GoalTypeEnum::ReturnPercentage => 'Return ' . $goal->targetValue->toFixed(1) . ' %',
				GoalTypeEnum::InvestedAmount => 'Invested ' . $currencySymbol . ' ' . $goal->targetValue->toFixed(0),
			};

			$progressClamped = min(100.0, max(0.0, $progress));
			$progressFormatted = number_format($progressClamped, 1) . ' %';
			$progressColor = $progressClamped >= 100.0 ? $colorGreen : $colorGray;
			$filledBlocks = (int) round($progressClamped / 10);
			$emptyBlocks = 10 - $filledBlocks;
			$progressBar = str_repeat('█', $filledBlocks) . str_repeat('░', $emptyBlocks);

			$deadlineHtml = '';
			if ($goal->deadline !== null) {
				$deadlineHtml = ' &mdash; due ' . $goal->deadline->format('d M Y');
			}

			$rows .= <<<HTML
				<tr style="border-bottom: 1px solid #333;">
					<td style="{$fontStyleTable}{$colorWhite} padding: 8px 16px 8px 0;">{$label}{$deadlineHtml}</td>
					<td style="{$fontStyleTable}{$progressColor} padding: 8px 0; text-align: right; white-space: nowrap;">{$progressBar} {$progressFormatted}</td>
				</tr>
HTML;
		}

		return <<<HTML

			<hr style="border-color: #333; margin-top: 24px;">
			<p style="{$fontStyleWhite} font-size: 18px; font-weight: bold; margin-bottom: 8px;">Goal Progress</p>
			<table style="width: 100%; border-collapse: collapse;">
				{$rows}
			</table>
HTML;
	}
}
