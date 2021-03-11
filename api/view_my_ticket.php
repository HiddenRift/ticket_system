<?php
require_once __DIR__ . '/resources/User.php';
require_once __DIR__ . '/resources/ViewTickets.php';

session_start();

if(!isset($_SESSION['user'])){
    // error no user
    echo json_encode(['response' => 403, 'message' => 'Please log in']);
    die(0);
}

$sessionOwnerId = $_SESSION['user'];

$session_owner = \SupportTicketManager\User::getUserById($sessionOwnerId);

if ($session_owner === false){
    echo json_encode(['response' => 403, 'message' => 'User account not found']);
    die(0);
}

if(!isset($_POST['ticketId'])){
    echo json_encode(['response' => 422, 'message' => 'content is missing']);
    die(0);
}

$ticketId = new \MongoDB\BSON\ObjectId($_POST['ticketId']);

$mngr = new \SupportTicketManager\ViewTickets($session_owner);

echo json_encode($mngr->viewOwnTicket($ticketId));
