<?php

declare(strict_types=1);

namespace FinGather\Tests\Validator;

use FinGather\Validator\PasswordValidator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

#[CoversClass(PasswordValidator::class)]
final class PasswordValidatorTest extends TestCase
{
	#[TestWith(['ValidPass1!', true, 'all requirements met'])]
	#[TestWith(['Abcdefg1!', true, 'exactly 8 characters'])]
	#[TestWith(['validpass1!', false, 'missing uppercase'])]
	#[TestWith(['VALIDPASS1!', false, 'missing lowercase'])]
	#[TestWith(['ValidPassAB!', false, 'missing digit'])]
	#[TestWith(['ValidPass123', false, 'missing special character'])]
	#[TestWith(['Val1!xy', false, 'too short (7 characters)'])]
	#[TestWith(['', false, 'empty string'])]
	public function testIsValid(string $password, bool $expected, string $description): void
	{
		self::assertSame($expected, PasswordValidator::isValid($password), $description);
	}
}
