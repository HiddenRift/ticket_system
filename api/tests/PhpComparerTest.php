<?php


require_once __DIR__ . "/../../vendor/autoload.php";
require_once __DIR__ . '/../resources/PhpComparer.php';


use PHPUnit\Framework\TestCase;

class PhpComparerTest extends TestCase
{
    public function testCompareStrings()
    {
        $result = 100.0;
        $tester = \SupportTicketManager\PhpComparer::compareStrings("the cat Sat on the mat", "the cat Sat on the mat");
        $this->assertSame($result,$tester,"strings are fully equal when they should be");

        $tester2 = \SupportTicketManager\PhpComparer::compareStrings("the cat Sat on the mat", "the dog is asleep");

        $this->assertNotSame($result, $tester2, "strings are not equal");
    }
}
