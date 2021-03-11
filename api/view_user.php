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

// assert user type == staff or above
if ($session_owner->getUserType() == \SupportTicketManager\User::USER_TYPE_NORMAL) {
    echo json_encode(['response' => 403, 'message' => 'You do not have permission to perform that action']);
    die(0);
}

if (isset($_POST['userId']) xor isset($_POST['email'])) {
    echo json_encode(['response' => 422, 'message' => 'please onle set either a userId or email']);
    die(0);
}

if (isset($_POST['userId'])){
    $userId = new \MongoDB\BSON\ObjectId($_POST['userId']);

    $result = \SupportTicketManager\ManageUser::getUserDetails($userId);

    echo json_encode($result);
}else{
    $email = $_POST['email'];

    $result = \SupportTicketManager\ManageUser::getUserDetailsByEmail($email);

    echo json_encode($result);
}
