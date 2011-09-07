<?php

//Get all includes
include "IncludeMaster.php";

/* * *************************Get vars and create objects************************************ */

//Create session
session_start();

//Turns off stupid magic quotes
$session = new Control();
$session->Turn_Off_Magic_Quotes();

//Check if admin
if ($_SESSION['admin_id'] != $admin_user) {
    header('Location: admin.php');
    exit;
}

//create new ezsql object
$db = new ezSQL_mysql();

//Get variables from config/config.php
$db->ezSQL_mysql($dbuser, $dbpass, $dbname, $dbserver);

//Create smarty
$smarty = new Smarty();

/* * *************************Updates****************************************** */

//When users are added
if (!empty($_POST["add_user"])) {

    //Check username and password
    if (!$_POST["username"] or !$_POST["password"]) {

        $warning = 'Username and password required.';
    } else {

        //Clean out SQL injection
        $esc_username = mysql_escape_string($_POST["username"]);
        $esc_password = crypt(mysql_escape_string($_POST["password"]), $salt);
        $esc_email = mysql_escape_string($_POST["email"]);
        $esc_first_name = mysql_escape_string($_POST["first_name"]);
        $esc_last_name = mysql_escape_string($_POST["last_name"]);

        //Insert user
        $id = $db->get_var("insert into user (name, password, email, first_name, last_name)
        values ('{$esc_username}', '{$esc_password}', '{$esc_email}', '{$esc_first_name}', '{$esc_last_name}')");

        $warning = 'User created.';
    }
}

//When users are edited
if (!empty($_POST["edit_user"])) {

    //Clean out SQL injection
    $esc_username = mysql_escape_string($_POST["username"]);
    $esc_password = crypt(mysql_escape_string($_POST["password"]), $salt);
    $esc_email = mysql_escape_string($_POST["email"]);
    $esc_first_name = mysql_escape_string($_POST["first_name"]);
    $esc_last_name = mysql_escape_string($_POST["last_name"]);
    $esc_id = $_POST["id"];

    if (isset($_POST["active"])) {
        $esc_active = 1;
    }

    if (empty($_POST["password"])) {

        //update user w/o pass
        $id = $db->get_var("update user
            set name = '{$esc_username}',
            email = '{$esc_email}',
            first_name = '{$esc_first_name}',
            last_name = '{$esc_last_name}',
            active = '{$esc_active}'
            where id = {$esc_id}");

        $warning = "User {$esc_username} has been updated.";
    } else {

        //update user and pass
        $id = $db->get_var("update user
            set name = '{$esc_username}',
            password = '{$esc_password}',
            email = '{$esc_email}',
            first_name = '{$esc_first_name}',
            last_name = '{$esc_last_name}',
            active = '{$esc_active}'
            where id = {$esc_id}");

        $warning = "User {$esc_username} has been updated.";
    }
}

/* * *************************Query User Data****************************************** */

//Get user list
$user_list = $db->get_results("select id, name, password, email, first_name, last_name, case when active = 1 then 1 else 0 end as active
                                    from user
                                    order by id");


/* * *************************Call Smarty****************************************** */
$smarty->assign('warning', $warning);
$smarty->assign('title', 'mound: user admin');
$smarty->assign('user_list', $user_list);
$smarty->display('admin_user.xhtml');
?>

