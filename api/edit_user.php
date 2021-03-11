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
if(!isset($_POST['id'])){
    echo json_encode(['response' => 403, 'message' => 'no user has been specified']);
    die(0);
}

if ($session_owner->getUserType() == \SupportTicketManager\User::USER_TYPE_NORMAL){
    // insufficient permissions
    echo json_encode(['response' => 403, 'message' => 'You do not have permission to access this command']);
    die(0);
}

// id is only required field
$id = new \MongoDB\BSON\ObjectId($_POST['id']);

// retrieve variables from post safely;
$name = (isset($_POST['name']))? $_POST['name'] :null;
$email = (isset($_POST['email']))? $_POST['email'] :null;
$password = (isset($_POST['password']))? $_POST['password'] :null;
$permissions = (isset($_POST['permissions']))? $_POST['permissions'] :null;
$isActive = (isset($_POST['active']))? (boolean)$_POST['active'] :null;

$result = \SupportTicketManager\ManageUser::editUserDetails($id, $name, $email, $permissions, $password, $isActive);

echo json_encode($result);
