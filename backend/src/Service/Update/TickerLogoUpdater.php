<?php

declare(strict_types=1);

namespace FinGather\Service\Update;

use FinGather\Model\Entity\Enum\MarketTypeEnum;
use FinGather\Model\Entity\Ticker;
use FinGather\Model\Repository\TickerRepository;
use MarekSkopal\TwelveData\Dto\Fundamentals\Logo;
use MarekSkopal\TwelveData\Exception\NotFoundException;
use MarekSkopal\TwelveData\TwelveData;

final class TickerLogoUpdater
{
	private const string LOGOS_PATH = '/app/public/images/logos/';
	private const string LOGOS_API_DIR = 'api/';

	public function __construct(private readonly TickerRepository $tickerRepository, private readonly TwelveData $twelveData,)
	{
	}

	public function updateTickerLogo(Ticker $ticker): void
	{
		$currentLogo = $ticker->logo;
		if ($currentLogo !== null && !str_starts_with($currentLogo, self::LOGOS_API_DIR)) {
			return;
		}

		// If the logo is in the filesystem, we use them
		if (file_exists(self::LOGOS_PATH . $ticker->ticker . '.svg')) {
			$ticker->logo = $ticker->ticker . '.svg';
			$this->tickerRepository->persist($ticker);
			return;
		}

		// If the logo is not in the filesystem, we download it from API
		try {
			$logo = $ticker->market->type === MarketTypeEnum::Crypto ? $this->twelveData->getFundamentals()->logo(
				symbol: $ticker->ticker . '/USD',
			) : $this->twelveData->getFundamentals()->logo(symbol: $ticker->ticker, micCode: $ticker->market->mic);
		} catch (NotFoundException) {
			return;
		}

		$url = $this->getUrlFromLogo($logo);
		if ($url === null) {
			return;
		}

		$fileContents = file_get_contents($url);
		if ($fileContents === false) {
			return;
		}

		if (!is_dir(self::LOGOS_PATH . self::LOGOS_API_DIR)) {
			mkdir(self::LOGOS_PATH . self::LOGOS_API_DIR, recursive: true);
		}

		$filename = strtolower($ticker->market->mic . '-' . $ticker->ticker) . '.png';
		file_put_contents(self::LOGOS_PATH . self::LOGOS_API_DIR . $filename, $fileContents);

		$ticker->logo = self::LOGOS_API_DIR . $filename;
		$this->tickerRepository->persist($ticker);
	}

	private function getUrlFromLogo(Logo $logo): ?string
	{
		if ($logo->url !== null && $logo->url !== '') {
			return $logo->url;
		}

		if ($logo->logoBase !== null && $logo->logoBase !== '') {
			return $logo->logoBase;
		}

		return null;
	}
}
