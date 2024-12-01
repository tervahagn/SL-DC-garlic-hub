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

namespace Tests\Unit\Framework\Core\Config;

use App\Framework\Core\Config\Config;
use App\Framework\Core\Config\ConfigLoaderInterface;
use App\Framework\Exceptions\CoreException;
use Monolog\Level;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    private Config $config;
    private ConfigLoaderInterface $configLoaderMock;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->configLoaderMock = $this->createMock(ConfigLoaderInterface::class);
        $this->config           = new Config($this->configLoaderMock, ['key_path' => 'value_path'], ['key_env' => 'value_env']);
    }


    /**
     * @throws CoreException
     */
    #[Group('units')]
    public function testGetConfigValueReturnsValue(): void
    {
        $module = 'test_module';
        $key    = 'test_key';
        $value  = 'test_value';

        $this->configLoaderMock
            ->method('load')
            ->with($module)
            ->willReturn(['section' => [$key => $value]]);

        $result = $this->config->getConfigValue($key, $module, 'section');

        $this->assertEquals($value, $result);
    }

	#[Group('units')]
	public function testGetEnv(): void
	{
		$this->assertEquals('value_env', $this->config->getEnv('key_env'));
		$this->assertEmpty($this->config->getEnv('manamana'));
	}

	#[Group('units')]
	public function testGetPaths(): void
	{
		$this->assertEquals('value_path', $this->config->getPaths('key_path'));
		$this->assertEmpty($this->config->getPaths('manamana'));

	}

		/**
     * @throws CoreException
     */
    #[Group('units')]
    public function testGetConfigValueReturnsNullForNonExistentKey(): void
    {
        $module = 'test_module';
        $key = 'nonexistent_key';

        $this->configLoaderMock
            ->method('load')
            ->with($module)
            ->willReturn(['section' => ['existing_key' => 'value']]);

        $result = $this->config->getConfigValue($key, $module, 'section');

        $this->assertNull($result);
    }

	#[Group('units')]
	public function testLogLevelIsDebugInDevEnvironment(): void
	{
		$config = new Config($this->configLoaderMock, [], ['APP_ENV' => 'dev']);
		$this->assertEquals(Level::Debug, $config->getLogLevel());
	}

	#[Group('units')]
	public function testLogLevelIsInfoInTestEnvironment(): void
	{
		$config = new Config($this->configLoaderMock, [], ['APP_ENV' => 'test']);
		$this->assertEquals(Level::Info, $config->getLogLevel());
	}

	#[Group('units')]
	public function testLogLevelIsErrorInProdEnvironment(): void
	{
		$config = new Config($this->configLoaderMock, [], ['APP_ENV' => 'prod']);
		$this->assertEquals(Level::Error, $config->getLogLevel());
	}

	#[Group('units')]
	public function testLogLevelIsWarningInUnknownEnvironment(): void
	{
		$config = new Config($this->configLoaderMock, [], ['APP_ENV' => 'unknown']);
		$this->assertEquals(Level::Warning, $config->getLogLevel());
	}

	#[Group('units')]
	public function logLevelIsWarningWhenEnvIsNotSet(): void
	{
		$config = new Config($this->configLoaderMock, [], []);
		$this->assertEquals(Level::Warning, $config->getLogLevel());
	}

	/**
     * @throws CoreException
     */
    #[Group('units')]
    public function testGetFullConfigDataByModule(): void
    {
        $module = 'test_module';
        $configData = ['key1' => 'value1', 'key2' => 'value2'];

        $this->configLoaderMock
            ->method('load')
            ->with($module)
            ->willReturn($configData);

        $result = $this->config->getFullConfigDataByModule($module);

        $this->assertEquals($configData, $result);
    }

    /**
     * @throws CoreException
     */
    #[Group('units')]
    public function testPreloadModulesCachesConfigurations(): void
    {
        $modules = ['module1', 'module2'];
        $configData = [
            'module1' => ['key1' => 'value1'],
            'module2' => ['key2' => 'value2'],
        ];

        $this->configLoaderMock
            ->method('load')
            ->willReturnCallback(function ($module) use ($configData) {
                return $configData[$module] ?? [];
            });

        // Preload Module
        $this->config->preloadModules($modules);

        // check if configuration loaded correctly
        $this->assertEquals($configData['module1'], $this->config->getFullConfigDataByModule('module1'));
        $this->assertEquals($configData['module2'], $this->config->getFullConfigDataByModule('module2'));
    }

    /**
     * @throws CoreException
     */
    #[Group('units')]
    public function testGetConfigForModuleCachesResults(): void
    {
        $module = 'test_module';
        $configData = ['key1' => 'value1'];

        $this->configLoaderMock
            ->expects($this->once())
            ->method('load')
            ->with($module)
            ->willReturn($configData);

        // request from loader
        $result1 = $this->config->getFullConfigDataByModule($module);

        // 2nd request from cache
        $result2 = $this->config->getFullConfigDataByModule($module);

        $this->assertEquals($configData, $result1);
        $this->assertEquals($configData, $result2);
    }

    #[Group('units')]
    public function testGetConfigForModuleThrowsExceptionIfLoaderFails(): void
    {
        $module = 'test_module';

        $this->configLoaderMock
            ->method('load')
            ->with($module)
            ->willThrowException(new CoreException("Error loading module"));

        $this->expectException(CoreException::class);
        $this->expectExceptionMessage("Error loading module");

        $this->config->getFullConfigDataByModule($module);
    }
}
