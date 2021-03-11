<?php

require_once __DIR__ . '/resources/User.php';
require_once __DIR__ . '/resources/ManageUser.php';

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

echo json_encode(\SupportTicketManager\ManageUser::getUserList());
