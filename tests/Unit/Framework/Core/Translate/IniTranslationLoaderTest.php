<?php

namespace Tests\Unit\Framework\Core\Translate;

use App\Framework\Core\Translate\IniTranslationLoader;
use App\Framework\Exceptions\FrameworkException;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

class IniTranslationLoaderTest extends TestCase
{
    private string $baseDirectory;

    protected function setUp(): void
    {
        $this->baseDirectory = getenv('TEST_BASE_DIR') . '/resources/translations/';
    }

    /**
     * @throws FrameworkException
     */
    #[Group('units')]
    public function testLoadForValidFile(): void
    {
        $loader = new IniTranslationLoader($this->baseDirectory);
        $result = $loader->load('en', 'valid');
        $this->assertIsArray($result);
        $this->assertArrayHasKey('username', $result);
    }

    #[Group('units')]
    public function testLoadThrowsExceptionForNonExistentFile()
    {
        $this->expectException(FrameworkException::class);
        $this->expectExceptionMessage('Translation file not found');
        $loader = new IniTranslationLoader($this->baseDirectory);
        $loader->load('en', 'nonexistent');
    }

    #[Group('units')]
    public function testLoadThrowsExceptionForInvalidIniFile()
    {
        $this->expectException(FrameworkException::class);
        $this->expectExceptionMessage('Invalid INI file format');
        $loader = new IniTranslationLoader($this->baseDirectory);
        $loader->load('en', 'invalid');
    }

}
