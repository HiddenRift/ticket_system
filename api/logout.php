<?php
session_start();
if (isset($_SESSION['user'])){
    unset($_SESSION['user']);
}
$response = ['response' => 200, 'message' => 'logout successful'];
echo(json_encode($response));
