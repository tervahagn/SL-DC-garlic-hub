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

namespace Tests\Unit\Framework\Core\Translate;

use App\Framework\Core\Locales\Locales;
use App\Framework\Core\Translate\MessageFormatterFactory;
use App\Framework\Core\Translate\TranslationLoaderInterface;
use App\Framework\Core\Translate\Translator;
use App\Framework\Exceptions\CoreException;
use App\Framework\Exceptions\FrameworkException;
use MessageFormatter;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;

class TranslatorTest extends TestCase
{
    private Translator $translator;
    private Locales $localesMock;
    private TranslationLoaderInterface $loaderMock;
    private CacheInterface $cacheMock;
    private MessageFormatterFactory $formatterFactoryMock;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->localesMock = $this->createMock(Locales::class);
        $this->loaderMock = $this->createMock(TranslationLoaderInterface::class);
        $this->cacheMock = $this->createMock(CacheInterface::class);
        $this->formatterFactoryMock = $this->createMock(MessageFormatterFactory::class);

        $this->translator = new Translator(
            $this->localesMock,
            $this->loaderMock,
            $this->formatterFactoryMock,
            $this->cacheMock
        );
    }

    /**
     * @throws InvalidArgumentException
     * @throws CoreException
     * @throws FrameworkException
     */
    #[Group('units')]
    public function testTranslateReturnsTranslation(): void
    {
        $this->localesMock->method('getLanguageCode')->willReturn('en');
        $this->cacheMock->method('has')->willReturn(false);
        $this->loaderMock->method('load')->willReturn(['greeting' => 'Hello, {name}!']);
        $this->cacheMock->expects($this->once())->method('set');
        $this->cacheMock->expects($this->never())->method('get');

        $result = $this->translator->translate('greeting', 'test_module', ['{name}' => 'John']);

        $this->assertEquals('Hello, John!', $result);
    }

    /**
     * @throws InvalidArgumentException
     * @throws CoreException
     * @throws FrameworkException
     */
    #[Group('units')]
    public function testTranslateSucceedWithCacheKey(): void
    {
        $this->localesMock->method('getLanguageCode')->willReturn('en');
        $this->cacheMock->method('has')->willReturn(true);
        $this->cacheMock->method('get')->willReturn(['greeting' => 'Hello, {name}!']);
        $this->loaderMock->expects($this->never())->method('load');
        $this->cacheMock->expects($this->never())->method('set');

        $result = $this->translator->translate('greeting', 'test_module', ['{name}' => 'John']);
        $this->assertEquals('Hello, John!', $result);
    }

    /**
     * @throws CoreException
     * @throws InvalidArgumentException
     * @throws FrameworkException
     */
    #[Group('units')]
    public function testTranslateThrowsExceptionForMissingKey(): void
    {
        $this->localesMock->method('getLanguageCode')->willReturn('en');
        $this->cacheMock->method('has')->willReturn(false);
        $this->loaderMock->method('load')->willReturn(['other_key' => 'Some value']);
        $this->cacheMock->expects($this->once())->method('set');

        $result = $this->translator->translate('missing_key', 'test_module');
        $this->assertEmpty($result);
    }

    /**
     * @throws CoreException
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws FrameworkException
     */
    #[Group('units')]
    public function testTranslatePluralReturnsFormattedString(): void
    {
        $this->localesMock->method('getLanguageCode')->willReturn('en');
        $this->cacheMock->method('has')->willReturn(false);
        $this->loaderMock->method('load')->willReturn(['item_count' => '{count} items']);

        $formatterMock = $this->createMock(MessageFormatter::class);
        $formatterMock->method('format')->willReturn('5 items');
        $this->formatterFactoryMock->method('create')->willReturn($formatterMock);

        $result = $this->translator->translateWithPlural('item_count', 'test_module', 5);
        $this->assertEquals('5 items', $result);
    }

    #[Group('units')]
    public function testTranslatePluralReturnsFrameworkException(): void
    {
        $this->localesMock->method('getLanguageCode')->willReturn('en');
        $this->cacheMock->method('has')->willReturn(false);
        $this->loaderMock->method('load')->willReturn(['item_count' => '{count} items']);

        $formatterMock = $this->createMock(MessageFormatter::class);
        $formatterMock->method('format')->willReturn(false);

        $this->expectException(FrameworkException::class);
        $this->expectExceptionMessage('MessageFormatter error: ');
        $this->translator->translateWithPlural('item_count', 'test_module', 5);
    }


    /**
     * @throws InvalidArgumentException
     * @throws CoreException
     */
    #[Group('units')]
    public function testTranslateArrayForOptionsReturnsArray(): void
    {
        $this->localesMock->method('getLanguageCode')->willReturn('en');
        $this->cacheMock->method('has')->willReturn(false);
        $this->loaderMock->method('load')->willReturn(['options' => ['opt1' => 'Option 1', 'opt2' => 'Option 2']]);

        $result = $this->translator->translateArrayForOptions('options', 'test_module');
        $this->assertIsArray($result);
        $this->assertArrayHasKey('opt1', $result);
        $this->assertEquals('Option 1', $result['opt1']);
    }

    /**
     * @throws CoreException
     * @throws InvalidArgumentException
     */
    #[Group('units')]
    public function testTranslateArrayForOptionsHandlesNonArrayGracefully(): void
    {
        $this->localesMock->method('getLanguageCode')->willReturn('en');
        $this->cacheMock->method('has')->willReturn(false);
        $this->loaderMock->method('load')->willReturn(['options' => 'Not an array']);

        $result = $this->translator->translateArrayForOptions('options', 'test_module');
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }
}
