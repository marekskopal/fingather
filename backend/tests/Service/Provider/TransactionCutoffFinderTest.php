<?php

declare(strict_types=1);

namespace FinGather\Tests\Service\Provider;

use DateTimeImmutable;
use FinGather\Service\Provider\TransactionCutoffFinder;
use FinGather\Tests\Fixtures\Model\Entity\TransactionFixture;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use FinGather\Model\Entity\Country;
use FinGather\Model\Entity\Currency;
use FinGather\Model\Entity\Group;
use FinGather\Model\Entity\Industry;
use FinGather\Model\Entity\Market;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\Sector;
use FinGather\Model\Entity\Ticker;
use FinGather\Model\Entity\Transaction;
use FinGather\Model\Entity\User;
use PHPUnit\Framework\Attributes\UsesClass;
use FinGather\Model\Entity\Asset;

#[CoversClass(TransactionCutoffFinder::class)]
#[UsesClass(Country::class)]
#[UsesClass(Currency::class)]
#[UsesClass(Group::class)]
#[UsesClass(Industry::class)]
#[UsesClass(Market::class)]
#[UsesClass(Portfolio::class)]
#[UsesClass(Sector::class)]
#[UsesClass(Ticker::class)]
#[UsesClass(Transaction::class)]
#[UsesClass(User::class)]
#[UsesClass(Asset::class)]
final class TransactionCutoffFinderTest extends TestCase
{
	private TransactionCutoffFinder $transactionCutoffFinder;

	protected function setUp(): void
	{
		$this->transactionCutoffFinder = new TransactionCutoffFinder();
	}

	public function testEmptyArray(): void
	{
		$result = $this->transactionCutoffFinder->findCutoffIndex([], new DateTimeImmutable('2021-06-01'));

		self::assertSame(0, $result);
	}

	public function testAllTransactionsBeforeDate(): void
	{
		$transactions = [
			TransactionFixture::getTransaction(id: 1, actionCreated: new DateTimeImmutable('2021-01-01')),
			TransactionFixture::getTransaction(id: 2, actionCreated: new DateTimeImmutable('2021-02-01')),
			TransactionFixture::getTransaction(id: 3, actionCreated: new DateTimeImmutable('2021-03-01')),
		];

		$result = $this->transactionCutoffFinder->findCutoffIndex($transactions, new DateTimeImmutable('2021-12-31'));

		self::assertSame(3, $result);
	}

	public function testAllTransactionsAfterDate(): void
	{
		$transactions = [
			TransactionFixture::getTransaction(id: 1, actionCreated: new DateTimeImmutable('2021-06-01')),
			TransactionFixture::getTransaction(id: 2, actionCreated: new DateTimeImmutable('2021-07-01')),
			TransactionFixture::getTransaction(id: 3, actionCreated: new DateTimeImmutable('2021-08-01')),
		];

		$result = $this->transactionCutoffFinder->findCutoffIndex($transactions, new DateTimeImmutable('2021-01-01'));

		self::assertSame(0, $result);
	}

	public function testCutoffInMiddle(): void
	{
		$transactions = [
			TransactionFixture::getTransaction(id: 1, actionCreated: new DateTimeImmutable('2021-01-01')),
			TransactionFixture::getTransaction(id: 2, actionCreated: new DateTimeImmutable('2021-03-01')),
			TransactionFixture::getTransaction(id: 3, actionCreated: new DateTimeImmutable('2021-06-01')),
			TransactionFixture::getTransaction(id: 4, actionCreated: new DateTimeImmutable('2021-09-01')),
		];

		$result = $this->transactionCutoffFinder->findCutoffIndex($transactions, new DateTimeImmutable('2021-05-01'));

		self::assertSame(2, $result);
	}

	public function testExactDateMatch(): void
	{
		$transactions = [
			TransactionFixture::getTransaction(id: 1, actionCreated: new DateTimeImmutable('2021-01-01')),
			TransactionFixture::getTransaction(id: 2, actionCreated: new DateTimeImmutable('2021-03-01')),
			TransactionFixture::getTransaction(id: 3, actionCreated: new DateTimeImmutable('2021-06-01')),
		];

		$result = $this->transactionCutoffFinder->findCutoffIndex($transactions, new DateTimeImmutable('2021-03-01'));

		self::assertSame(2, $result);
	}

	public function testSingleTransactionBeforeDate(): void
	{
		$transactions = [
			TransactionFixture::getTransaction(id: 1, actionCreated: new DateTimeImmutable('2021-01-01')),
		];

		$result = $this->transactionCutoffFinder->findCutoffIndex($transactions, new DateTimeImmutable('2021-06-01'));

		self::assertSame(1, $result);
	}

	public function testSingleTransactionAfterDate(): void
	{
		$transactions = [
			TransactionFixture::getTransaction(id: 1, actionCreated: new DateTimeImmutable('2021-06-01')),
		];

		$result = $this->transactionCutoffFinder->findCutoffIndex($transactions, new DateTimeImmutable('2021-01-01'));

		self::assertSame(0, $result);
	}

	public function testDuplicateDates(): void
	{
		$transactions = [
			TransactionFixture::getTransaction(id: 1, actionCreated: new DateTimeImmutable('2021-01-01')),
			TransactionFixture::getTransaction(id: 2, actionCreated: new DateTimeImmutable('2021-03-01')),
			TransactionFixture::getTransaction(id: 3, actionCreated: new DateTimeImmutable('2021-03-01')),
			TransactionFixture::getTransaction(id: 4, actionCreated: new DateTimeImmutable('2021-06-01')),
		];

		$result = $this->transactionCutoffFinder->findCutoffIndex($transactions, new DateTimeImmutable('2021-03-01'));

		self::assertSame(3, $result);
	}
}
