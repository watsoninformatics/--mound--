<?php

//Get all includes
include "IncludeMaster.php";

//Create session
session_start();

//Turns off stupid magic quotes
$session = new Control();
$session->Turn_Off_Magic_Quotes();

//Create smarty
$smarty = new Smarty();

//Check username and password
if(!$_POST["username"] or !$_POST["password"]) {

    //If there is no user_id in session, send user to login
    $smarty->assign('warning', 'Please enter a username AND password.');
    $smarty->assign('title', 'mound: login');
    $smarty->display('login.xhtml');


}
else {

    //Clean out SQL injection
    $esc_username = mysql_escape_string($_POST["username"]);
    $esc_password = crypt(mysql_escape_string($_POST["password"]), $salt);

    //create new ezsql object
    $db = new ezSQL_mysql();
    //Get variables from config/config.php
    $db->ezSQL_mysql($dbuser, $dbpass, $dbname, $dbserver);
    //$rez = new smart_ez_results();
    $id = $db->get_var("SELECT id FROM user WHERE name = '{$esc_username}' and password = '{$esc_password}' and active = 1 LIMIT 1");

    //If there is an id returned
    if ($id >= 1) {
        $_SESSION["user_id"] = $id;
        header('Location: index.php');
    }
    else {

        //If username and password pair did not match
        $smarty->assign('warning', 'Invalid username or password.');
        $smarty->assign('title', 'mound: login');
        $smarty->display('login.xhtml');
    }
}

?>

