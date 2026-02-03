<?php

declare(strict_types=1);

namespace FinGather\Service\Email;

use FinGather\Dto\EmailVerifyDto;
use FinGather\Email\PortfolioSummaryEmail;
use FinGather\Email\VerifyEmail;
use FinGather\Model\Entity\Portfolio;
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
