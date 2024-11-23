<?php

namespace Tests\Unit\Framework\Core\Config;

use App\Framework\Core\Config\IniConfigLoader;
use App\Framework\Exceptions\CoreException;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

class IniConfigLoaderTest extends TestCase
{

    protected function setUp(): void
    {
        $this->baseDirectory = getenv('TEST_BASE_DIR') . '/resources/config_tests/';
    }

    /**
     * @throws CoreException
     */
    #[Group('units')]
    public function testLoadForValidFile(): void
    {
        $loader = new IniConfigLoader($this->baseDirectory);
        $result = $loader->load('valid');
        $this->assertIsArray($result);
        $this->assertArrayHasKey('edition', $result);
    }


    #[Group('units')]
    public function testLoadThrowsExceptionForMissingFile(): void
    {
        $loader = new IniConfigLoader($this->baseDirectory);

        $this->expectException(CoreException::class);
        $this->expectExceptionMessage('Unable to access configuration file: '.$this->baseDirectory.'config_nonexistent.ini');

        $loader->load('nonexistent');
    }

    #[Group('units')]
    public function testLoadThrowsExceptionForUnreadableFile(): void
    {
        $loader = new IniConfigLoader($this->baseDirectory);

        $this->expectException(CoreException::class);
        $this->expectExceptionMessage('Error parsing configuration file: '.$this->baseDirectory.'config_invalid.ini');

        $loader->load('invalid');
    }
}
