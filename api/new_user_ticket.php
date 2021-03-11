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

if(!isset($_POST['content'])){
    echo json_encode(['response' => 422, 'message' => 'content is missing']);
    die(0);
}

$content = $_POST['content'];

$ticketManager = new \SupportTicketManager\ViewTickets($session_owner);

$response = $ticketManager->createTicket($content);

echo json_encode($response);
