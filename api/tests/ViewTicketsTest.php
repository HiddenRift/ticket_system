<?php

namespace SupportTicketManager;

require_once __DIR__ . "/../../vendor/autoload.php";
require_once  __DIR__ . '/../resources/User.php';
require_once  __DIR__ . '/../resources/ViewTickets.php';

use MongoDB\BSON\ObjectId;
use PHPUnit\Framework\TestCase;

class ViewTicketsTest extends TestCase
{

    public function test__construct()
    {

    }

    public function testGetOwnTicketList()
    {

    }

    public function testViewOwnTicket()
    {
        $user = User::getUser('admin@server.com','password');
        $mngr = new ViewTickets($user);
        $id = new ObjectId('5c9fc540438b3c6924f78eec');
        $response = $mngr->viewOwnTicket($id);
        self::assertIsArray($response);
        self::assertTrue($response['response'] === 200);

    }

    public function testCreateTicket()
    {

    }

    public function testCancelOwnTicket()
    {

    }

    public function testRespondToOwnTicket()
    {

    }
}
