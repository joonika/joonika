<?php
declare(strict_types=1);

namespace UnitTest;

use PHPUnit\Framework\TestCase;

final class UintTest extends TestCase
{


    /**
     *
     */
    public function testCanBeCreatedFromValidEmailAddress()
    {
        print "masood";
        $this->getActualOutput('mas');
        $this->expectOutputString("mas");
    }


    public static function suite()
    {

    }

    public function dddd()
    {
        return [
            [0, 8],
            [7, 1],
            [1, 9],
            [1, 1]
        ];
    }

    public function testCanBeUsedAsString($a): void
    {
        echo $a;

        $mm = [1];
        $this->assertNotEmpty($mm);
    }
}