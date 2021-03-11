<?php

require_once __DIR__ . '/resources/User.php';
require_once __DIR__ . '/resources/ManageTickets.php';
require_once __DIR__ . '/resources/ViewTickets.php';
session_start();
if (!isset($_SESSION['user'])) {
    // error no user
    echo json_encode(['response' => 403, 'message' => 'Please log in']);
    die(0);
}

$sessionOwnerId = $_SESSION['user'];

$session_owner = \SupportTicketManager\User::getUserById($sessionOwnerId);

if ($session_owner === false) {
    echo json_encode(['response' => 403, 'message' => 'User account not found']);
    die(0);
}

if (!isset($_POST['ticket'])){
    echo json_encode(['response' => 422, 'message' => 'ticket is missing']);
    die(0);
}

if (!isset($_POST['category'])){
    echo json_encode(['response' => 422, 'message' => 'category is missing']);
    die(0);
}

if ($session_owner->getUserType() == \SupportTicketManager\User::USER_TYPE_NORMAL){
    echo json_encode(["response" => 403, 'message' => 'You are not authorized to access this ticket']);
    die(0);
}

$ticket = new \MongoDB\BSON\ObjectId($_POST['ticket']);

$category = $_POST['category'];

$result = \SupportTicketManager\ManageTickets::closeTicket($ticket, $category);

echo json_encode($result);
