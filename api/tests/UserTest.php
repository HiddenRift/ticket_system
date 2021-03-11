<?php

namespace SupportTicketManager;

require_once __DIR__ . "/../../vendor/autoload.php";
require_once  __DIR__ . '/../resources/User.php';

use MongoDB\BSON\ObjectId;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testUserList(){
        $list = User::userList();
        $this->assertIsArray($list, 'is an array?');
        print_r($list);
    }

    public function testUserID(){

        $test = new ObjectId("5c9fc3b0b20f3b7cd0561a22");
        $list = User::getUserById($test);
        var_dump($list);

    }
}
