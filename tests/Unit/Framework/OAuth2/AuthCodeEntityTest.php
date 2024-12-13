<?php

namespace Tests\Unit\Framework\OAuth2;

use App\Framework\OAuth2\AuthCodeEntity;
use League\OAuth2\Server\Entities\AuthCodeEntityInterface;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

class AuthCodeEntityTest extends TestCase
{
	#[Group('units')]
	public function testImplementsAuthCodeEntityInterface(): void
	{
		$authCodeEntity = new AuthCodeEntity();
		$this->assertInstanceOf(AuthCodeEntityInterface::class, $authCodeEntity);
	}

	#[Group('units')]
	public function testSetAndGetRedirectUri(): void
	{
		$authCodeEntity = new AuthCodeEntity();
		$testUri = 'https://example.com/callback';

		$authCodeEntity->setRedirectUri($testUri);

		$this->assertSame($testUri, $authCodeEntity->getRedirectUri());
	}

}
