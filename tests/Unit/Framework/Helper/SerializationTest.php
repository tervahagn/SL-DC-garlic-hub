<?php

namespace Tests\Unit\Framework\Helper;

use App\Framework\Helper\Serialization;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

class SerializationTest extends TestCase
{
    #[Group('units')]
    public function testUnSerializeSecureSucceed(): void
    {
        $expected = ['a', 'b', 'c', 'd'];
        $result   = Serialization::unserializeSecure(serialize($expected));
        $this->assertEquals($expected, $result);
    }

    #[Group('units')]
    public function testUnSerializeSecureFails(): void
    {
        $result   = Serialization::unserializeSecure('');
        $this->assertEquals([], $result);
    }

}
