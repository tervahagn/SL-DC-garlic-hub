<?php

namespace Tests\Unit\Framework\Helper;

use App\Framework\Helper\Cookie;
use App\Framework\Helper\Crypt;
use App\Framework\Exceptions\FrameworkException;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

#[Group('units')]
class CookieTest extends TestCase
{
	private Cookie $cookie;
	private Crypt $cryptMock;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		$this->cryptMock = $this->createMock(Crypt::class);
		$this->cookie = new Cookie($this->cryptMock);
	}

	#[Group('units')]
	public function testCreateCookie(): void
	{
		$this->cryptMock->expects($this->once())->method('createSha256Hash')->willReturn('mocked_hash');

		$contents = ['UID' => 123, 'LSID' => 'test_session'];
		$expire = new \DateTime('+1 day');

		$this->expectOutputRegex('/.*/'); // Prevent PHP warnings from setcookie()

		$this->cookie->createHashedCookie('test_cookie', $contents, $expire);

		$this->assertTrue(true); // If no exception is thrown, the test passes.
	}

	#[Group('units')]
	public function testGetCookie(): void
	{
		$this->cryptMock->expects($this->once())->method('createSha256Hash')->willReturn('mocked_hash');

		$contents = ['UID' => 123, 'LSID' => 'test_session'];
		$serializedContent = serialize([serialize($contents), 'mocked_hash']);

		$_COOKIE['test_cookie'] = $serializedContent;

		$result = $this->cookie->getHashedCookie('test_cookie');

		$this->assertIsArray($result);
		$this->assertEquals($contents, $result);
	}

	#[Group('units')]
	public function testGetCookieNotExists(): void
	{
		$result = $this->cookie->getHashedCookie('nonexistent_cookie');

		$this->assertNull($result);
	}

	#[Group('units')]
	public function testDeleteCookie(): void
	{
		$this->expectOutputRegex('/.*/'); // Prevent PHP warnings from setcookie()

		$this->cookie->deleteCookie('test_cookie');

		$this->assertTrue(true); // If no exception is thrown, the test passes.
	}

	#[Group('units')]
	public function testHasCookie(): void
	{
		$_COOKIE['test_cookie'] = 'some_value';

		$this->assertTrue($this->cookie->hasCookie('test_cookie'));
		$this->assertFalse($this->cookie->hasCookie('nonexistent_cookie'));
	}

	#[Group('units')]
	public function testGetCookieWithManipulatedContent(): void
	{
		$this->cryptMock->expects($this->once())->method('createSha256Hash')->willReturn('mocked_hash');

		$contents = ['UID' => 123, 'LSID' => 'test_session'];
		$manipulatedContent = serialize([serialize($contents), 'wrong_hash']);

		$_COOKIE['test_cookie'] = $manipulatedContent;

		$this->expectException(FrameworkException::class);
		$this->expectExceptionMessage('Possible cookie manipulation detected.');

		$this->cookie->getHashedCookie('test_cookie');
	}
}
