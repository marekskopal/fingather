<?php

declare(strict_types=1);

namespace FinGather\Tests\Service\Translator;

use FinGather\Model\Entity\Enum\LocaleEnum;
use FinGather\Service\Cache\Cache;
use FinGather\Service\Translator\TranslatorService;
use Nette\Caching\Storages\MemoryStorage;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(TranslatorService::class)]
final class TranslatorServiceTest extends TestCase
{
	private string $translationsDir;

	private TranslatorService $translator;

	protected function setUp(): void
	{
		$this->translationsDir = sys_get_temp_dir() . '/fingather_translator_test_' . uniqid();
		mkdir($this->translationsDir);

		file_put_contents($this->translationsDir . '/en.json', json_encode([
			'email' => [
				'subject' => ['welcome' => 'Welcome to FinGather'],
				'verify' => ['hi' => 'Hi,', 'auto' => 'This email was sent automatically.'],
			],
		]));

		file_put_contents($this->translationsDir . '/cs.json', json_encode([
			'email' => [
				'subject' => ['welcome' => 'Vítejte v FinGather'],
				'verify' => ['hi' => 'Ahoj,', 'auto' => 'Tento e-mail byl odeslán automaticky.'],
			],
		]));

		$cache = new Cache(new MemoryStorage(), 'translator_test');
		$this->translator = new TranslatorService($this->translationsDir, $cache);
	}

	protected function tearDown(): void
	{
		array_map('unlink', glob($this->translationsDir . '/*.json') ?: []);
		rmdir($this->translationsDir);
	}

	public function testTranslateReturnsTranslatedString(): void
	{
		self::assertSame('Hi,', $this->translator->translate('email.verify.hi', LocaleEnum::En));
		self::assertSame('Ahoj,', $this->translator->translate('email.verify.hi', LocaleEnum::Cs));
	}

	public function testTranslateFallsBackToEnglishWhenKeyMissing(): void
	{
		// 'email.subject.welcome' exists in English but not overridden in cs with a missing key
		// Remove cs file and add one without the key to simulate a missing key
		file_put_contents($this->translationsDir . '/cs.json', json_encode([
			'email' => [
				'subject' => [],
				'verify' => ['hi' => 'Ahoj,'],
			],
		]));

		$cache = new Cache(new MemoryStorage(), 'translator_test_fallback');
		$translator = new TranslatorService($this->translationsDir, $cache);

		self::assertSame('Welcome to FinGather', $translator->translate('email.subject.welcome', LocaleEnum::Cs));
	}

	public function testTranslateReturnsKeyWhenMissingInAllLocales(): void
	{
		self::assertSame('email.nonexistent.key', $this->translator->translate('email.nonexistent.key', LocaleEnum::En));
	}

	public function testTranslateFallsBackToEnglishWhenFileDoesNotExist(): void
	{
		// De locale file does not exist → falls back to English
		self::assertSame('Hi,', $this->translator->translate('email.verify.hi', LocaleEnum::De));
	}

	public function testSectionReturnsCorrectArray(): void
	{
		$section = $this->translator->section('email.verify', LocaleEnum::En);

		self::assertSame(['hi' => 'Hi,', 'auto' => 'This email was sent automatically.'], $section);
	}

	public function testSectionReturnsTranslatedArray(): void
	{
		$section = $this->translator->section('email.verify', LocaleEnum::Cs);

		self::assertSame(['hi' => 'Ahoj,', 'auto' => 'Tento e-mail byl odeslán automaticky.'], $section);
	}

	public function testSectionFallsBackToEnglishWhenSectionMissing(): void
	{
		file_put_contents($this->translationsDir . '/cs.json', json_encode([
			'email' => ['subject' => ['welcome' => 'Vítejte v FinGather']],
		]));

		$cache = new Cache(new MemoryStorage(), 'translator_test_section_fallback');
		$translator = new TranslatorService($this->translationsDir, $cache);

		$section = $translator->section('email.verify', LocaleEnum::Cs);

		self::assertSame(['hi' => 'Hi,', 'auto' => 'This email was sent automatically.'], $section);
	}

	public function testSectionReturnsEmptyArrayWhenKeyMissingInAllLocales(): void
	{
		$section = $this->translator->section('email.nonexistent', LocaleEnum::En);

		self::assertSame([], $section);
	}

	public function testSectionFallsBackToEnglishWhenFileDoesNotExist(): void
	{
		// De locale file does not exist → falls back to English
		$section = $this->translator->section('email.verify', LocaleEnum::De);

		self::assertSame(['hi' => 'Hi,', 'auto' => 'This email was sent automatically.'], $section);
	}

	public function testTranslationIsCachedOnSecondCall(): void
	{
		$this->translator->translate('email.verify.hi', LocaleEnum::En);

		// Delete file — second call must still succeed via cache
		unlink($this->translationsDir . '/en.json');

		self::assertSame('Hi,', $this->translator->translate('email.verify.hi', LocaleEnum::En));
	}

	public function testTranslateWithInvalidJsonFallsBackToEnglish(): void
	{
		file_put_contents($this->translationsDir . '/de.json', 'not valid json');

		$cache = new Cache(new MemoryStorage(), 'translator_test_invalid_json');
		$translator = new TranslatorService($this->translationsDir, $cache);

		// Invalid JSON → empty data → falls back to English
		self::assertSame('Hi,', $translator->translate('email.verify.hi', LocaleEnum::De));
	}
}
