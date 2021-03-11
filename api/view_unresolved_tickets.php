<?php

require_once __DIR__ . '/resources/User.php';
require_once __DIR__ . '/resources/ManageTickets.php';
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

// assert user type == staff or above
if ($session_owner->getUserType() == \SupportTicketManager\User::USER_TYPE_NORMAL){
    echo json_encode(['response' => 403, 'message' => 'You do not have permission to perform that action']);
    die(0);
}

$result = \SupportTicketManager\ManageTickets::getUnsolvedTickets();

echo json_encode($result);
