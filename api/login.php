<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 24/03/19
 * Time: 18:02
 */
require_once __DIR__ . "/../vendor/autoload.php";
require_once __DIR__ . "/resources/User.php";

$response = '';

if (isset($_POST["email"], $_POST["password"])){
    session_start();
    $user = SupportTicketManager\User::getUser($_POST["email"], $_POST['password']);
    if ($user != false){
        $_SESSION['user'] = $user->getId();
        $response = ["response" => 200,
            'message' => 'Access granted ' . $user->getName(),
            'permissions' => $user->getUserType()];
        $response = json_encode($response);
    }else{
        $response = ["response" => 403, 'message' => 'Access denied, username & password do not match'];
        $response = json_encode($response);
    }
}else {
    $response = ["response" => 403, 'message' => 'Access denied, username & password do not match'];
    $response = json_encode($response);
}

echo $response;
