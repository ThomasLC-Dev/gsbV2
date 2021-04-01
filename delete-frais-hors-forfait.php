<?php

//Session
session_start();
$userId = $_SESSION['id'];
$fraisHorsForfaitId = $_GET['id'];

//Includes
include('utils/Database.php');

$database = new Database();

//Login / Disconnect
if(!isset($_SESSION['login'])){
    header('Location: index.php');
}
else{
    //Delete out-of-pocket expense with a specific ID
    $database->deleteFraisHorsForfait($userId, $fraisHorsForfaitId);
    header('Location: dashboard.php');
}

?>