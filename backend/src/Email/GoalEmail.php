<?php

declare(strict_types=1);

namespace FinGather\Email;

use FinGather\Model\Entity\Enum\GoalTypeEnum;
use FinGather\Model\Entity\Goal;

final class GoalEmail
{
	/** @param array<string, string> $t */
	public static function getHtml(Goal $goal, string $currentValue, array $t): string
	{
		$colorGray = 'color: #b0b0b0;';
		$colorWhite = 'color: #ffffff;';
		$colorGreen = 'color: #a4e04f;';
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

		$tdLabelStyle = 'style="' . $fontStyleTable . $colorGray . 'padding: 8px 16px 8px 0;"';
		$tdValueStyle = 'style="' . $fontStyleTable . $colorWhite . 'padding: 8px 0; text-align: right;"';

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
