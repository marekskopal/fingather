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
	 * @param array<string, string> $t
	 */
	public static function getHtml(
		string $portfolioName,
		string $currencySymbol,
		CalculatedDataDto $portfolioData,
		?CalculatedDataDto $previousMonthPortfolioData,
		array $upcomingDividends,
		array $activeGoalsWithProgress,
		array $t,
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
			monthChangeLabel: $t['monthChange'],
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
			upcomingDividendsLabel: $t['upcomingDividends'],
			next30DaysLabel: $t['next30Days'],
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
			goalProgressLabel: $t['goalProgress'],
			portfolioValueLabel: $t['portfolioValueGoal'],
			returnLabel: $t['returnGoal'],
			investedLabel: $t['investedGoal'],
			dueLabel: $t['due'],
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
			<p style="{$fontStyleWhite} font-size: 20px; font-weight: bold;">{$t['title']}</p>
			<p style="{$fontStyleGray}">{$portfolioName} &mdash; {$date}</p>

			<table style="width: 100%; border-collapse: collapse; margin-top: 16px;">
				<tr style="border-bottom: 1px solid #333;">
					<td {$tdLabelStyleLarge}>{$t['portfolioValue']}</td>
					<td {$tdValueStyleLarge}><b>{$value}</b></td>
				</tr>
				<tr style="border-bottom: 1px solid #333;">
					<td {$tdLabelStyle}>{$t['investedValue']}</td>
					<td {$tdValueStyle}>{$transactionValue}</td>
				</tr>
				<tr style="border-bottom: 1px solid #333;">
					<td {$tdLabelStyle}>{$t['gainLoss']}</td>
					<td style="{$fontStyleTable}{$gainColor} padding: 8px 0; text-align: right;">{$gain}</td>
				</tr>
				<tr style="border-bottom: 1px solid #333;">
					<td {$tdLabelStyle}>{$t['dividendYield']}</td>
					<td {$tdValueStyle}>{$dividendYield}</td>
				</tr>
				<tr{$monthChangeRowSeparator}>
					<td {$tdLabelStyle}>{$t['totalReturn']}</td>
					<td style="{$fontStyleTable}{$returnColor} padding: 8px 0; text-align: right;"><b>{$totalReturn}</b></td>
				</tr>
				{$monthChangeRowHtml}
			</table>

			{$dividendsSection}
			{$goalsSection}

			<hr style="border-color: #333; margin-top: 24px;">

			<p style="{$fontStyleGray}">{$t['auto']}</p>
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
		string $monthChangeLabel,
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
				<td {$tdLabelStyle}>{$monthChangeLabel}</td>
				<td style="{$fontStyleTable}{$monthDeltaColor} padding: 8px 0; text-align: right;">{$monthChange}</td>
			</tr>
HTML;

		return ['html' => $html, 'separator' => ' style="border-bottom: 1px solid #333;"'];
	}

	/** @param list<DividendCalendarItemDto> $upcomingDividends */
	private static function getDividendsSection(
		string $currencySymbol,
		array $upcomingDividends,
		string $upcomingDividendsLabel,
		string $next30DaysLabel,
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
			<p style="{$fontStyleWhite} font-size: 18px; font-weight: bold; margin-bottom: 8px;">{$upcomingDividendsLabel}</p>
			<p style="{$fontStyle}{$colorGray} margin-top: 0;">{$next30DaysLabel}</p>
			<table style="width: 100%; border-collapse: collapse;">
				{$rows}
			</table>
HTML;
	}

	/** @param list<array{goal: Goal, progress: float}> $activeGoalsWithProgress */
	private static function getGoalsSection(
		string $currencySymbol,
		array $activeGoalsWithProgress,
		string $goalProgressLabel,
		string $portfolioValueLabel,
		string $returnLabel,
		string $investedLabel,
		string $dueLabel,
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
				GoalTypeEnum::PortfolioValue => $portfolioValueLabel . ' ' . $currencySymbol . ' ' . $goal->targetValue->toFixed(0),
				GoalTypeEnum::ReturnPercentage => $returnLabel . ' ' . $goal->targetValue->toFixed(1) . ' %',
				GoalTypeEnum::InvestedAmount => $investedLabel . ' ' . $currencySymbol . ' ' . $goal->targetValue->toFixed(0),
			};

			$progressClamped = min(100.0, max(0.0, $progress));
			$progressFormatted = number_format($progressClamped, 1) . ' %';
			$progressColor = $progressClamped >= 100.0 ? $colorGreen : $colorGray;
			$filledBlocks = (int) round($progressClamped / 10);
			$emptyBlocks = 10 - $filledBlocks;
			$progressBar = str_repeat('█', $filledBlocks) . str_repeat('░', $emptyBlocks);

			$deadlineHtml = '';
			if ($goal->deadline !== null) {
				$deadlineHtml = ' &mdash; ' . $dueLabel . ' ' . $goal->deadline->format('d M Y');
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
			<p style="{$fontStyleWhite} font-size: 18px; font-weight: bold; margin-bottom: 8px;">{$goalProgressLabel}</p>
			<table style="width: 100%; border-collapse: collapse;">
				{$rows}
			</table>
HTML;
	}
}
