<?php

declare(strict_types=1);

namespace FinGather\Tests\Translations;

use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversNothing]

final class TranslationsTest extends TestCase
{
	private const string TranslationsDir = __DIR__ . '/../../translations';

	/** @return array<string, array{string}> */
	public static function localeProvider(): array
	{
		$locales = [];
		foreach (glob(self::TranslationsDir . '/*.json') as $file) {
			$locale = basename($file, '.json');
			if ($locale !== 'en') {
				$locales[$locale] = [$locale];
			}
		}
		return $locales;
	}

	#[DataProvider('localeProvider')]
	public function testLocaleHasAllEnglishKeys(string $locale): void
	{
		$en = $this->loadJson('en');
		$translated = $this->loadJson($locale);

		$missingKeys = $this->findMissingKeys($en, $translated);

		self::assertSame(
			[],
			$missingKeys,
			sprintf('Locale "%s" is missing translation keys: %s', $locale, implode(', ', $missingKeys)),
		);
	}

	#[DataProvider('localeProvider')]
	public function testLocaleHasNoExtraKeys(string $locale): void
	{
		$en = $this->loadJson('en');
		$translated = $this->loadJson($locale);

		$extraKeys = $this->findMissingKeys($translated, $en);

		self::assertSame(
			[],
			$extraKeys,
			sprintf('Locale "%s" has extra keys not present in English: %s', $locale, implode(', ', $extraKeys)),
		);
	}

	/** @return array<string, mixed> */
	private function loadJson(string $locale): array
	{
		$file = self::TranslationsDir . '/' . $locale . '.json';
		self::assertFileExists($file, sprintf('Translation file for locale "%s" does not exist.', $locale));

		$content = file_get_contents($file);
		self::assertNotFalse($content);

		/** @var array<string, mixed>|null $decoded */
		$decoded = json_decode($content, true);
		self::assertIsArray($decoded, sprintf('Translation file for locale "%s" is not valid JSON.', $locale));

		return $decoded;
	}

	/**
	 * @param array<string, mixed> $expected
	 * @param array<string, mixed> $actual
	 * @return list<string>
	 */
	private function findMissingKeys(array $expected, array $actual, string $prefix = ''): array
	{
		$missing = [];
		foreach ($expected as $key => $value) {
			$fullKey = $prefix !== '' ? $prefix . '.' . $key : $key;
			if (!array_key_exists($key, $actual)) {
				$missing[] = $fullKey;
			} elseif (is_array($value) && is_array($actual[$key])) {
				$missing = array_merge($missing, $this->findMissingKeys($value, $actual[$key], $fullKey));
			}
		}
		return $missing;
	}
}
