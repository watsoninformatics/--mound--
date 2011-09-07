<?php

//Get all includes
include "IncludeMaster.php";

//Create session and check user
$session = new Control();
$session->CheckSession();

//Turns off stupid magic quotes
$session->Turn_Off_Magic_Quotes();

/* * *************************Get vars****************************************** */

$mode = $_GET["mode"];
$tab = $_GET["tab"];

/* * ******************************************************************* */

/* * **************************Create Objects************************************** */
//Create smarty
$smarty = new Smarty();
//create new ezsql object
$db = new ezSQL_mysql();
/* * ********************************************************************************* */

/* * ****************************Connect to DB****************************************** */
//Get variables from config/config.php
$db->ezSQL_mysql($dbuser, $dbpass, $dbname, $dbserver);
/* * ********************************************************************************** */

/* * ***************************Mode Handling************************************************** */


//If initial load, do no filters
if ($mode == "logout") {
    session_unset();
    $session->CheckSession();
}

//Default mode to current
if (empty($mode)) {
    $mode = "current";
}

//If current, get last record for each
if ($mode == "current") {
    $limiting_join = " inner join (select max(id) as id, user_id from userstatus where active = 1 group by user_id) as current
        on current.id = us.id ";
}

//If user, get the user's last 25
if ($mode == "user") {
    $limit = " LIMIT 0, 25 ";
    if (!$_GET['user']) {
        $_GET['user'] = $_SESSION['user_id'];
    }
    $filter = " and us.user_id = {$_GET['user']}";
}

//Everything from today
if ($mode == "today") {
    $filter = " and datediff(current_date(), us.created_at) = 0 ";
}

//Everything since yesterday
if ($mode == "yesterday") {
    $filter = " and datediff(current_date(), us.created_at) < 2 ";
}

//Everthing in the last 7 days
if ($mode == "week") {
    $filter = " and datediff(current_date(), us.created_at) < 8 ";
}

//Everything in 30 days
if ($mode == "month") {
    $filter = " and datediff(current_date(), us.created_at) < 31 ";
}

//Everything to me
if ($mode == "tome") {
    $me = $db->get_var("select name from user where id = {$_SESSION["user_id"]}");
    $filter = " and us.user_status like '%@:{$me}%' ";
}


/* * ********************************************************************************************* */


/* * ***********************************DB Connection and Query************************ */

//update on post
if ($_POST['status_text']) {
    $status_text = mysql_escape_string(strip_tags($_POST['status_text']));

    $db->get_results("insert into userstatus (user_id, user_status)
        values ({$_SESSION['user_id']}, '{$status_text}')");
}

//Delete record for user
if ($_GET['command'] == "delete" and $_GET['userstatus_id']) {
    $userstatus_id = mysql_escape_string($_GET['userstatus_id']);

    $db->get_results("update userstatus
                    set active = 0
                    where id = {$userstatus_id}
                    and user_id = {$_SESSION['user_id']}");
}

//get display data
$user_status = $db->get_results("select
                            us.id as userstatus_id,
                            u.name as user_name,
                            case when us.user_status like 'tk::%::%' then
                            concat(replace(
                                    replace(us.user_status, 'tk::', '<a class=status_link target=_blank href=ticket.php?ticket_id='), '::', '>'), '</a>')
                            else us.user_status end as user_status,
                            us.created_at,
                            us.user_id
                            from userstatus as us
                            left join user as u
                            on us.user_id = u.id
                            {$limiting_join}
                            where us.active = 1
                            and u.active = 1
                            {$filter}
                            order by us.id desc
                            {$limit}");

//Get data for the release container list dropdown
$releasecontainer_dropdown = $db->get_results("select distinct rc.id, rc.name
                                                    from releasecontainer as rc
                                                    left join assignreleasecontainer as arc
                                                    on arc.releasecontainer_id = rc.id
                                                    and arc.active = 1
                                                    left join ticket as t
                                                    on arc.ticket_id = t.id
                                                    where (t.status_id not in (7, 8)
                                                    or t.status_id is null)
                                                    and rc.active = 1");

//Set the title
$title = "mound: user status";
/* * ******************************************************************************************* */

//Send to Smarty
$smarty->assign('releasecontainer_dropdown', $releasecontainer_dropdown);
$smarty->assign('mode', $mode);
$smarty->assign('tab', $tab);
$smarty->assign('user_status', $user_status);
$smarty->assign('user_id', $_SESSION['user_id']);
$smarty->assign('title', $title);
$smarty->display('userstatus.xhtml');
?>

