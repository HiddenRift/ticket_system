<?php
require_once __DIR__ . '/resources/User.php';
require_once __DIR__ . '/resources/ManageUser.php';

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

if (!isset($_POST['user'], $_POST['email'], $_POST['password'], $_POST['permissions'])){
    echo json_encode(['response' => 422, 'message' => 'Post Data is missing']);
    die(0);
}
// TODO: validate inputs

// TODO: check added permission is lower or equal to user

// TODO: check user is not of type user

// add user
$response = \SupportTicketManager\ManageUser::addUser($_POST['email'], $_POST['user'],  $_POST['password'], $_POST['permissions'], true);

echo json_encode($response);
