<?php

namespace Tests\Unit\Framework\Core\Acl;

use App\Framework\Core\Acl\AclHelper;
use PHPUnit\Framework\TestCase;

class AclHelperTest extends TestCase
{


	private function mockConfigValues(): void
	{
		$this->configMock->method('getConfigValue')
			->willReturnCallback(function($key)
			{
				return match ($key)
				{
					'moduleadmin' => 8,
					'subadmin' => 4,
					'editor' => 2,
					'viewer' => 1,
					default => 0,
				};
			});
	}

}
