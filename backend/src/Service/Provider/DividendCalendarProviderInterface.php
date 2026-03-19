<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use FinGather\Dto\DividendCalendarItemDto;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\User;

interface DividendCalendarProviderInterface
{
	/**
	 * @param callable(): void|null $onApiCall
	 * @return list<DividendCalendarItemDto>
	 */
	public function getDividendCalendar(User $user, Portfolio $portfolio, ?callable $onApiCall = null): array;
}
