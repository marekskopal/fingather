<?php

declare(strict_types=1);

namespace FinGather\Service\Translator;

use FinGather\Model\Entity\Enum\LocaleEnum;

interface TranslatorServiceInterface
{
	public function translate(string $key, LocaleEnum $locale): string;

	/** @return array<string, string> */
	public function section(string $key, LocaleEnum $locale): array;
}
