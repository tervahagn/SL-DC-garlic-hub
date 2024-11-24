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

namespace Tests\Unit\Framework\Core\Cli;

use App\Framework\Core\Cli\CliColors;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

class CliColorsTest extends TestCase
{
    #[Group('units')]
    #[DataProvider('dataProviderColorizeString')]
    public function testColorizeString($message, $color, $expected)
    {
        $result = CliColors::colorizeString($message, $color);
        $this->assertEquals($expected, $result);
    }

    /**
     * @return array
     */
    public static function dataProviderColorizeString()
    {
        $message = 'test message';

        return array(
            array($message, CliColors::CLI_COLOR_DEFAULT,	"\033[0;39m$message\033[0m"),
            array($message, CliColors::CLI_COLOR_RED, 		"\033[0;31m$message\033[0m"),
            array($message, CliColors::CLI_COLOR_BLUE, 		"\033[1;34m$message\033[0m"),
            array($message, CliColors::CLI_COLOR_YELLOW, 	"\033[1;33m$message\033[0m"),
            array($message, CliColors::CLI_COLOR_GREEN, 	"\033[0;32m$message\033[0m"),
            array($message, CliColors::CLI_COLOR_BOLD, 		"\033[1m$message\033[0m"),
            array($message, CliColors::CLI_COLOR_DIM, 		"\033[2m$message\033[0m"),
            array($message, 'unknown_;Color',                "\033[0;39m$message\033[0m")    // switch back to default color
        );
    }

}
