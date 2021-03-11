<?php

namespace SupportTicketManager;
require_once  __DIR__ . '/../resources/Ticket.php';
use MongoDB\BSON\ObjectId;
use PHPUnit\Framework\TestCase;

class TicketTest extends TestCase
{
    public function testUsersTicketList(){
        $id = new ObjectId('5c97b991c03b2a0e7a51a4b2');
        $results = Ticket::getUsersTickets($id);
        $this->assertIsArray($results, 'is an array');
    }

    public function testStaffTicketList(){
        $id = new ObjectId('5c97b991c03b2a0e7a51a4b2');
        $results = Ticket::getTicketsAttendingTo($id);
        $this->assertIsArray($results, 'is an array of staff tickets');
        $this->assertTrue(sizeof($results)>0, 'staff array has results');
    }

    public function testGetUnattendedTickets(){
        $results = Ticket::getUnattendedTickets();
        $this->assertIsArray($results, 'is an array of unattended tickets');
        $this->assertTrue(sizeof($results)>0, 'staff unattended array has results');
    }

    public function testgetticket(){
        $id = new ObjectId('5ca4d5fbb20f3b3ac16211a2');
        $result = Ticket::getTicket($id);
        var_dump($result);
    }
}
