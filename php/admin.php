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
    $smarty->assign('warning', 'Please enter username AND password.');
    $smarty->assign('title', 'mound: admin login');
    $smarty->display('admin.xhtml');


}
else {

    $esc_username = $_POST["username"];
    $esc_password = $_POST["password"];

    //If there is a id returned
    if ($esc_username == $admin_user and $esc_password == $admin_pass) {
        $_SESSION["admin_id"] = $esc_username;
        header('Location: admin_user.php');
    }
    else {

        //If username and password pair did not match
        $smarty->assign('warning', 'Invalid username or password.');
        $smarty->assign('title', 'mound: admin login');
        $smarty->display('admin.xhtml');
    }
}

?>



