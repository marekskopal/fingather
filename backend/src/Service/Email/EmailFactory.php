<?php

declare(strict_types=1);

namespace FinGather\Service\Email;

use FinGather\Dto\DividendCalendarItemDto;
use FinGather\Dto\EmailVerifyDto;
use FinGather\Dto\PasswordResetQueueDto;
use FinGather\Email\GoalEmail;
use FinGather\Email\PasswordResetEmail;
use FinGather\Email\PortfolioSummaryEmail;
use FinGather\Email\PriceAlertEmail;
use FinGather\Email\VerifyEmail;
use FinGather\Model\Entity\Goal;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\PriceAlert;
use FinGather\Model\Entity\User;
use FinGather\Service\DataCalculator\Dto\CalculatedDataDto;
use FinGather\Service\Translator\TranslatorService;
use Symfony\Component\Mime\Email;

final readonly class EmailFactory
{
	private string $from;

	public function __construct(private TranslatorService $translator)
	{
		$this->from = (string) getenv('EMAIL_FROM');
	}

	public function createPasswordResetEmail(PasswordResetQueueDto $passwordReset): Email
	{
		$locale = $passwordReset->user->locale;

		return new Email()
			->from($this->from)
			->to($passwordReset->user->email)
			->subject($this->translator->translate('email.subject.passwordReset', $locale))
			->html(PasswordResetEmail::getHtml($passwordReset, $this->translator->section('email.passwordReset', $locale)));
	}

	public function createEmailVerifyEmail(EmailVerifyDto $emailVerify): Email
	{
		$locale = $emailVerify->user->locale;

		return new Email()
			->from($this->from)
			->to($emailVerify->user->email)
			->subject($this->translator->translate('email.subject.emailVerify', $locale))
			->html(VerifyEmail::getHtml($emailVerify, $this->translator->section('email.verify', $locale)));
	}

	public function createPriceAlertEmail(User $user, PriceAlert $priceAlert, string $currentValue): Email
	{
		$locale = $user->locale;

		return new Email()
			->from($this->from)
			->to($user->email)
			->subject($this->translator->translate('email.subject.priceAlert', $locale))
			->html(PriceAlertEmail::getHtml($priceAlert, $currentValue, $this->translator->section('email.priceAlert', $locale)));
	}

	public function createGoalEmail(User $user, Goal $goal, string $currentValue): Email
	{
		$locale = $user->locale;

		return new Email()
			->from($this->from)
			->to($user->email)
			->subject($this->translator->translate('email.subject.goalAchieved', $locale))
			->html(GoalEmail::getHtml($goal, $currentValue, $this->translator->section('email.goal', $locale)));
	}

	/**
	 * @param list<DividendCalendarItemDto> $upcomingDividends
	 * @param list<array{goal: Goal, progress: float}> $activeGoalsWithProgress
	 */
	public function createPortfolioSummaryEmail(
		User $user,
		Portfolio $portfolio,
		CalculatedDataDto $portfolioData,
		?CalculatedDataDto $previousMonthPortfolioData,
		array $upcomingDividends,
		array $activeGoalsWithProgress,
	): Email {
		$locale = $user->locale;

		$html = PortfolioSummaryEmail::getHtml(
			portfolioName: $portfolio->name,
			currencySymbol: $portfolio->currency->symbol,
			portfolioData: $portfolioData,
			previousMonthPortfolioData: $previousMonthPortfolioData,
			upcomingDividends: $upcomingDividends,
			activeGoalsWithProgress: $activeGoalsWithProgress,
			t: $this->translator->section('email.portfolioSummary', $locale),
		);

		return new Email()
			->from($this->from)
			->to($user->email)
			->subject($this->translator->translate('email.subject.portfolioSummary', $locale))
			->html($html);
	}
}
