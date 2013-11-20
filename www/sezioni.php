<?php
require("pg_conn.php");

function sessione_utente(){
    session_start();
    if(!isset($_SESSION['email'])){
        header("Location: index.php");
    }
    $usr = $_SESSION['email'];
    echo $usr."  ";
}

function logout(){
    echo "<a href=?action=logout class = 'anone'> Logout </a></br>";
    if(isset($_GET['action']) && $_GET['action']== 'logout'){
    session_start();
    if(!isset($_SESSION['email'])){
        header("Location: index.php");
        echo "devi loggarti per accedere a queste informazioni";
        exit;
    }
    session_unset();
    header("Location: index.php");
    }
}  


function menu(){
    echo "<a href=?action=profilo> Profilo </a><br/><br/>";
    echo "<a href=?action=amici> Amici </a><br/><br/>";
    echo "<a href=?action=bacheca> Bacheca </a><br/><br/>";
    echo "<a href=?action=eventi> Eventi </a><br/><br/>";
    echo "<a href=?action=inviti> Inviti </a><br/><br/>";
    echo "<a href=?action=ricerca> Ricerca... </a><br/><br/>";



}

function welcome(){
    echo "Benvenuto ".$_SESSION["email"];
}


?>
