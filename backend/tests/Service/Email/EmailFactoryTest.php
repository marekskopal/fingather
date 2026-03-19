<?php

declare(strict_types=1);

namespace FinGather\Tests\Service\Email;

use FinGather\Dto\EmailVerifyDto;
use FinGather\Dto\PasswordResetQueueDto;
use FinGather\Dto\UserDto;
use FinGather\Model\Entity\Enum\LocaleEnum;
use FinGather\Service\Cache\Cache;
use FinGather\Service\Email\EmailFactory;
use FinGather\Service\Translator\TranslatorService;
use FinGather\Tests\Fixtures\Model\Entity\GoalFixture;
use FinGather\Tests\Fixtures\Model\Entity\PriceAlertFixture;
use FinGather\Tests\Fixtures\Model\Entity\UserFixture;
use Nette\Caching\Storages\DevNullStorage;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(EmailFactory::class)]
#[UsesClass(TranslatorService::class)]
#[UsesClass(Cache::class)]
final class EmailFactoryTest extends TestCase
{
	private function makeFactory(string $from = 'noreply@fingather.com'): EmailFactory
	{
		$cache = new Cache(new DevNullStorage(), 'test');
		$translator = new TranslatorService(
			translationsDir: dirname(__DIR__, 3) . '/translations',
			cache: $cache,
		);

		putenv("EMAIL_FROM={$from}");

		return new EmailFactory($translator);
	}

	public function testCreateGoalEmailHasCorrectSubjectAndFrom(): void
	{
		$from = 'alerts@fingather.com';
		$factory = $this->makeFactory($from);

		$user = UserFixture::getUser(email: 'user@example.com');
		$goal = GoalFixture::getGoal(user: $user);

		$email = $factory->createGoalEmail($user, $goal, '1500.00');

		self::assertNotEmpty($email->getSubject());
		self::assertCount(1, $email->getFrom());
		self::assertSame($from, $email->getFrom()[0]->getAddress());
	}

	public function testCreateGoalEmailHasCorrectRecipient(): void
	{
		$factory = $this->makeFactory();
		$user = UserFixture::getUser(email: 'user@example.com');
		$goal = GoalFixture::getGoal(user: $user);

		$email = $factory->createGoalEmail($user, $goal, '1500.00');

		self::assertCount(1, $email->getTo());
		self::assertSame('user@example.com', $email->getTo()[0]->getAddress());
	}

	public function testCreatePriceAlertEmailHasCorrectRecipient(): void
	{
		$factory = $this->makeFactory();
		$user = UserFixture::getUser(email: 'investor@example.com');
		$priceAlert = PriceAlertFixture::getPriceAlert(user: $user);

		$email = $factory->createPriceAlertEmail($user, $priceAlert, '155.00');

		self::assertCount(1, $email->getTo());
		self::assertSame('investor@example.com', $email->getTo()[0]->getAddress());
	}

	public function testCreatePriceAlertEmailHasCorrectSubject(): void
	{
		$factory = $this->makeFactory();
		$user = UserFixture::getUser();
		$priceAlert = PriceAlertFixture::getPriceAlert(user: $user);

		$email = $factory->createPriceAlertEmail($user, $priceAlert, '100.00');

		self::assertNotEmpty($email->getSubject());
	}

	public function testCreateEmailVerifyEmailHasCorrectRecipient(): void
	{
		$factory = $this->makeFactory();
		$user = UserFixture::getUser(email: 'newuser@example.com');

		$emailVerify = new EmailVerifyDto(
			user: UserDto::fromEntity($user),
			token: 'verify-token-123',
		);

		$email = $factory->createEmailVerifyEmail($emailVerify);

		self::assertCount(1, $email->getTo());
		self::assertSame('newuser@example.com', $email->getTo()[0]->getAddress());
	}

	public function testCreateEmailVerifyEmailHasCorrectSubject(): void
	{
		$factory = $this->makeFactory();
		$user = UserFixture::getUser();

		$emailVerify = new EmailVerifyDto(
			user: UserDto::fromEntity($user),
			token: 'verify-token-123',
		);

		$email = $factory->createEmailVerifyEmail($emailVerify);

		self::assertNotEmpty($email->getSubject());
	}

	public function testCreatePasswordResetEmailHasCorrectRecipient(): void
	{
		$factory = $this->makeFactory();
		$user = UserFixture::getUser(email: 'forgot@example.com');

		$passwordReset = new PasswordResetQueueDto(
			user: UserDto::fromEntity($user),
			token: 'reset-token-456',
		);

		$email = $factory->createPasswordResetEmail($passwordReset);

		self::assertCount(1, $email->getTo());
		self::assertSame('forgot@example.com', $email->getTo()[0]->getAddress());
	}

	public function testCreateEmailUsesFromAddress(): void
	{
		$factory = $this->makeFactory('custom-from@fingather.com');
		$user = UserFixture::getUser(email: 'user@example.com');
		$goal = GoalFixture::getGoal(user: $user);

		$email = $factory->createGoalEmail($user, $goal, '500.00');

		self::assertSame('custom-from@fingather.com', $email->getFrom()[0]->getAddress());
	}
}
