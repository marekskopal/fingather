<?php

declare(strict_types=1);

namespace FinGather\Service\Email;

use FinGather\Dto\EmailVerifyDto;
use FinGather\Email\GoalEmail;
use FinGather\Email\PortfolioSummaryEmail;
use FinGather\Email\PriceAlertEmail;
use FinGather\Email\VerifyEmail;
use FinGather\Model\Entity\Goal;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\PriceAlert;
use FinGather\Model\Entity\User;
use FinGather\Service\DataCalculator\Dto\CalculatedDataDto;
use Symfony\Component\Mime\Email;

final readonly class EmailFactory
{
	private string $from;

	public function __construct()
	{
		$this->from = (string) getenv('EMAIL_FROM');
	}

	public function createEmailVerifyEmail(EmailVerifyDto $emailVerify): Email
	{
		return new Email()
			->from($this->from)
			->to($emailVerify->user->email)
			->subject('FinGather - Verify your email.')
			->html(VerifyEmail::getHtml($emailVerify));
	}

	public function createPriceAlertEmail(User $user, PriceAlert $priceAlert, string $currentValue): Email
	{
		$html = PriceAlertEmail::getHtml(alert: $priceAlert, currentValue: $currentValue);

		return new Email()
			->from($this->from)
			->to($user->email)
			->subject('FinGather - Price Alert Triggered')
			->html($html);
	}

	public function createGoalEmail(User $user, Goal $goal, string $currentValue): Email
	{
		$html = GoalEmail::getHtml(goal: $goal, currentValue: $currentValue);

		return new Email()
			->from($this->from)
			->to($user->email)
			->subject('FinGather - Goal Achieved!')
			->html($html);
	}

	public function createPortfolioSummaryEmail(User $user, Portfolio $portfolio, CalculatedDataDto $portfolioData): Email
	{
		$html = PortfolioSummaryEmail::getHtml(
			portfolioName: $portfolio->name,
			currencySymbol: $portfolio->currency->symbol,
			portfolioData: $portfolioData,
		);

		return new Email()
			->from($this->from)
			->to($user->email)
			->subject('FinGather - Monthly Portfolio Summary')
			->html($html);
	}
}
