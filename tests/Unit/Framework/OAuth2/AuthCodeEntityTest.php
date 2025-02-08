<?php
/*
 garlic-hub: Digital Signage Management Platform

 Copyright (C) 2024 Nikolaos Sagiadinos <garlic@saghiadinos.de>
 This file is part of the garlic-hub source code

 This program is free software: you can redistribute it and/or  modify
 it under the terms of the GNU Affero General Public License, version 3,
 as published by the Free Software Foundation.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU Affero General Public License for more details.

 You should have received a copy of the GNU Affero General Public License
 along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

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
