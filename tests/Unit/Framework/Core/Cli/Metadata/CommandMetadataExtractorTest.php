<?php

namespace Tests\Unit\Framework\Core\Cli\Metadata;

use App\Framework\Core\Cli\Metadata\CommandMetadataExtractor;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

class CommandMetadataExtractorTest extends TestCase
{
    private string $testDir;

    protected function setUp(): void
    {
        // Erstelle ein temporäres Testverzeichnis
        $this->testDir = sys_get_temp_dir() . '/commands_test';
        mkdir($this->testDir);

        // Erstelle Testdateien
        file_put_contents($this->testDir . '/ValidCommand.php', "<?php\n\$cli_meta['command'] = 'test:command';");
        file_put_contents($this->testDir . '/InvalidCommand.php', "<?php\n// No metadata defined");
        file_put_contents($this->testDir . '/NotPhp.txt', "This is not a PHP file.");
    }

    protected function tearDown(): void
    {
        // Lösche die Testdateien und das Verzeichnis
        array_map('unlink', glob($this->testDir . '/*'));
        rmdir($this->testDir);
    }

    #[Group('units')]
    public function testExtractWithValidFiles(): void
    {
        $extractor = new CommandMetadataExtractor();
        $result = $extractor->extract($this->testDir);

        $this->assertArrayHasKey('test:command', $result);
        $this->assertArrayHasKey('filepath', $result['test:command']);
        $this->assertEquals($this->testDir . '/ValidCommand.php', $result['test:command']['filepath']);
    }

    #[Group('units')]
    public function testExtractWithNoValidCommands(): void
    {
        // Leeres Testverzeichnis simulieren
        mkdir($this->testDir . '/empty');
        $extractor = new CommandMetadataExtractor();
        $result = $extractor->extract($this->testDir . '/empty');

        $this->assertEmpty($result);

        rmdir($this->testDir . '/empty');
    }

    #[Group('units')]
    public function testExtractIgnoresNonPhpFiles(): void
    {
        $extractor = new CommandMetadataExtractor();
        $result = $extractor->extract($this->testDir);

        $this->assertCount(1, $result); // Nur die Datei "ValidCommand.php" wird berücksichtigt
        $this->assertArrayHasKey('test:command', $result);
    }

    #[Group('units')]
    public function testExtractHandlesInvalidPhpFilesGracefully(): void
    {
        // Füge eine Datei hinzu, die einen Fehler verursacht
        file_put_contents($this->testDir . '/ErrorCommand.php', "<?php\nthrow new Exception('Test error');");

        $extractor = new CommandMetadataExtractor();
        $result = $extractor->extract($this->testDir);

        $this->assertArrayHasKey('test:command', $result); // Die gültige Datei sollte weiterhin verarbeitet werden
        $this->assertArrayNotHasKey('ErrorCommand', $result); // Fehlerhafte Datei wird ignoriert
    }
}
