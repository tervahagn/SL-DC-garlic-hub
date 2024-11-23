<?php

namespace Tests\Framework\Core\Translate;

use App\Framework\Core\Translate\MessageFormatterFactory;
use App\Framework\Exceptions\FrameworkException;
use MessageFormatter;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

class MessageFormatterFactoryTest extends TestCase
{
    private MessageFormatterFactory $factory;

    protected function setUp(): void
    {
        $this->factory = new MessageFormatterFactory();
    }

    #[Group('units')]
    public function testCreateReturnsMessageFormatter(): void
    {
        $locale = 'en_US';
        $pattern = '{count} items';

        $formatter = $this->factory->create($locale, $pattern);

        $this->assertInstanceOf(MessageFormatter::class, $formatter);
        $this->assertEquals('5 items', $formatter->format(['count' => 5]));
    }

    #[Group('units')]
    public function testCreateThrowsFrameworkExceptionOnInvalidPattern(): void
    {
        $this->expectException(FrameworkException::class);
        $this->expectExceptionMessage('MessageFormatter instantiation error');

        $this->factory->create('en_US', '{count items'); // Missing closing brace
    }
}
