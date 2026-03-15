<?php

declare(strict_types=1);

namespace FinGather\Service\Translator;

use FinGather\Model\Entity\Enum\LocaleEnum;
use FinGather\Service\Cache\Cache;

final class TranslatorService
{
	public function __construct(private readonly string $translationsDir, private readonly Cache $cache)
	{
	}

	public function translate(string $key, LocaleEnum $locale): string
	{
		$value = $this->lookup($key, $locale);

		if ($value === null && $locale !== LocaleEnum::En) {
			$value = $this->lookup($key, LocaleEnum::En);
		}

		return is_string($value) ? $value : $key;
	}

	/** @return array<string, string> */
	public function section(string $key, LocaleEnum $locale): array
	{
		$data = $this->load($locale);
		$value = $this->traverse($data, $key);

		if (!is_array($value) && $locale !== LocaleEnum::En) {
			$data = $this->load(LocaleEnum::En);
			$value = $this->traverse($data, $key);
		}

		/** @var array<string, string> */
		return is_array($value) ? $value : [];
	}

	private function lookup(string $key, LocaleEnum $locale): mixed
	{
		$data = $this->load($locale);
		return $this->traverse($data, $key);
	}

	/** @return array<string, mixed> */
	private function load(LocaleEnum $locale): array
	{
		/** @var array<string, mixed>|null $cached */
		$cached = $this->cache->load($locale->value);
		if ($cached !== null) {
			return $cached;
		}

		$file = $this->translationsDir . '/' . $locale->value . '.json';

		if (!file_exists($file)) {
			return [];
		}

		$content = file_get_contents($file);
		if ($content === false) {
			return [];
		}

		/** @var array<string, mixed>|null $decoded */
		$decoded = json_decode($content, true);
		$data = $decoded ?? [];

		$this->cache->save($locale->value, $data);

		return $data;
	}

	/** @param array<string, mixed> $data */
	private function traverse(array $data, string $key): mixed
	{
		$parts = explode('.', $key);
		$value = $data;

		foreach ($parts as $part) {
			if (!is_array($value) || !array_key_exists($part, $value)) {
				return null;
			}
			$value = $value[$part];
		}

		return $value;
	}
}
