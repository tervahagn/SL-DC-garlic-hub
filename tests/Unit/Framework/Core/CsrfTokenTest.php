<?php

namespace Tests\Unit\Framework\Core;

use App\Framework\Core\Crypt;
use App\Framework\Core\CsrfToken;
use App\Framework\Core\Session;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

class CsrfTokenTest extends TestCase
{
	private Session $sessionMock;
	private Crypt $cryptMock;
	private CsrfToken $csrfToken;

	protected function setUp(): void
	{
		$this->sessionMock = $this->createMock(Session::class);
		$this->cryptMock = $this->createMock(Crypt::class);
		$this->csrfToken = new CsrfToken($this->cryptMock, $this->sessionMock);
	}

	#[Group('units')]
	public function testGetTokenGeneratesNewTokenWhenNoTokenExists(): void
	{
		$this->sessionMock->method('get')
			->with(CsrfToken::CSRF_TOKEN_SESSION_KEY)
			->willReturn(null);

		$this->cryptMock->method('generateRandomString')
			->with(CsrfToken::CSRF_TOKEN_LENGTH)
			->willReturn('newGeneratedToken');

		$this->sessionMock
			->expects($this->once())
			->method('set')
			->with(CsrfToken::CSRF_TOKEN_SESSION_KEY, 'newGeneratedToken');

		$result = $this->csrfToken->getToken();

		$this->assertSame('newGeneratedToken', $result);
	}

	#[Group('units')]
	public function testGetTokenGeneratesTokenWhenSessionTokenIsNotAString(): void
	{
		$this->sessionMock->method('get')
			->with(CsrfToken::CSRF_TOKEN_SESSION_KEY)
			->willReturn(['invalidType']);

		$this->cryptMock->method('generateRandomString')
			->with(CsrfToken::CSRF_TOKEN_LENGTH)
			->willReturn('newGeneratedToken');

		$this->sessionMock->expects($this->once())
			->method('set')
			->with(CsrfToken::CSRF_TOKEN_SESSION_KEY, 'newGeneratedToken');

		$result = $this->csrfToken->getToken();

		$this->assertSame('newGeneratedToken', $result);
	}

	#[Group('units')]
	public function testValidateTokenReturnsTrueForMatchingToken(): void
	{
		$validToken = 'validToken';
		$this->sessionMock->method('get')
			->with(CsrfToken::CSRF_TOKEN_SESSION_KEY)
			->willReturn($validToken);

		$result = $this->csrfToken->validateToken($validToken);

		$this->assertTrue($result);
	}

	#[Group('units')]
	public function testValidateTokenReturnsFalseForNonMatchingToken(): void
	{
		$sessionToken = 'validToken';
		$receivedToken = 'invalidToken';

		$this->sessionMock->method('get')
			->with(CsrfToken::CSRF_TOKEN_SESSION_KEY)
			->willReturn($sessionToken);

		$result = $this->csrfToken->validateToken($receivedToken);

		$this->assertFalse($result);
	}

	#[Group('units')]
	public function testValidateTokenReturnsFalseIfSessionTokenIsInvalid(): void
	{
		$this->sessionMock->method('get')
			->with(CsrfToken::CSRF_TOKEN_SESSION_KEY)
			->willReturn(null);

		$result = $this->csrfToken->validateToken('someToken');

		$this->assertFalse($result);
	}

	#[Group('units')]
	public function testGenerateTokenSetsValidToken(): void
	{
		$this->cryptMock->method('generateRandomString')
			->with(CsrfToken::CSRF_TOKEN_LENGTH)
			->willReturn('newGeneratedToken');

		$this->sessionMock->expects($this->once())->method('set')
			->with(CsrfToken::CSRF_TOKEN_SESSION_KEY, 'newGeneratedToken');

		$this->csrfToken->generateToken();

		$this->assertSame('newGeneratedToken', $this->csrfToken->getToken());
	}

	#[Group('units')]
	public function testGenerateTokenReplacesExistingToken(): void
	{
		$this->sessionMock->method('get')
			->with(CsrfToken::CSRF_TOKEN_SESSION_KEY)
			->willReturn('existingToken');

		$this->cryptMock->method('generateRandomString')
			->with(CsrfToken::CSRF_TOKEN_LENGTH)
			->willReturn('replacedToken');

		$this->sessionMock->expects($this->once())->method('set')
			->with(CsrfToken::CSRF_TOKEN_SESSION_KEY, 'replacedToken');

		$this->csrfToken->generateToken();

		$this->assertSame('replacedToken', $this->csrfToken->getToken());
	}


	#[Group('units')]
	public function testDestroyTokenRemovesTokenFromSession(): void
	{
		$this->sessionMock->expects($this->once())->method('delete')
			->with(CsrfToken::CSRF_TOKEN_SESSION_KEY);

		$this->csrfToken->destroyToken();
	}

}
