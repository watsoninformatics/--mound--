<?php

//Get all includes
include "IncludeMaster.php";

//Create session and check user
$session = new Control();
$session->CheckSession();

//Turns off stupid magic quotes
$session->Turn_Off_Magic_Quotes();

/* * *************************Get vars****************************************** */

//Get order by, default to id, get overrides session, session overrides default
if (!$_GET['orderby']) {
    if (!$_SESSION['orderby']) {
        $_GET['orderby'] = "id";
    } else {
        $_GET['orderby'] = $_SESSION['orderby'];
    }
}
$orderby = mysql_escape_string($_GET['orderby']);
$_SESSION['orderby'] = $orderby;

//Get order ascending or descending, get overrides session, session overrides default
if (!$_GET['ad']) {
    if (!$_SESSION['ad']) {
        $_GET['ad'] = "asc";
    } else {
        $_GET['ad'] = $_SESSION['ad'];
    }
}
$ad = mysql_escape_string($_GET['ad']);
$_SESSION['ad'] = $ad;

//Get page number
if (!$_GET['pages']) {
    if (!$_POST['pages']) {
        $_GET['pages'] = 1;
    } else {
        $_GET['pages'] = $_POST['pages'];
    }
}
$pagenum = mysql_escape_string($_GET['pages']);
$_SESSION['pages'] = $pagenum;

//Get status, get overrides session, session overrides default
if (!$_GET['status']) {
    if (!$_SESSION['status']) {
        $_GET['status'] = 0;
    } else {
        $_GET['status'] = $_SESSION['status'];
    }
}
$status_id = mysql_escape_string($_GET['status']);
$_SESSION['status'] = $status_id;

//Get tickettype, get overrides session, session overrides default
if (!$_GET['type']) {
    if (!$_SESSION['type']) {
        $_GET['type'] = 0;
    } else {
        $_GET['type'] = $_SESSION['type'];
    }
}
$tickettype_id = mysql_escape_string($_GET['type']);
$_SESSION['type'] = $tickettype_id;


//Get assigned, get overrides session, session overrides default
if (!$_GET['assigned']) {
    if (!$_SESSION['assigned']) {
        $_GET['assigned'] = "maybe";
    } else {
        $_GET['assigned'] = $_SESSION['assigned'];
    }
}
$assigned = mysql_escape_string($_GET['assigned']);
$_SESSION['assigned'] = $assigned;

//Get mytickets, get overrides session, session overrides default
if (!$_GET['mytickets']) {
    if (!$_SESSION['mytickets']) {
        $_GET['mytickets'] = "maybe";
    } else {
        $_GET['mytickets'] = $_SESSION['mytickets'];
    }
}
$mytickets = mysql_escape_string($_GET['mytickets']);
$_SESSION['mytickets'] = $mytickets;

//Get search string, get overrides session, session overrides default
if (!$_POST['search_string']) {
    if (!$_SESSION['search_string']) {
        $_POST['search_string'] = null;
    } else {
        $_POST['search_string'] = $_SESSION['search_string'];
    }
}
$search_string = mysql_escape_string($_POST['search_string']);
$_SESSION['search_string'] = $search_string;

//Get search type, get overrides session, session overrides default
if (!$_POST['search_type']) {
    if (!$_SESSION['search_type']) {
        $_POST['search_type'] = "search_content";
    } else {
        $_POST['search_type'] = $_SESSION['search_type'];
    }
}
$search_type = mysql_escape_string($_POST['search_type']);
$_SESSION['search_type'] = $search_type;


/* * ******************************************************************* */

/* * **************************Create Objects************************************** */
//Create smarty
$smarty = new Smarty();
//create new ezsql object
$db = new ezSQL_mysql();
//Get bottom and top limit
$pagination = new Pagination();
/* * ********************************************************************************* */

/* * ***************************Mode Handling************************************************** */

$mode = $_GET["mode"];

//If user clicks button to remove search filter, then clear everything out for search
if ($mode == "nosearch") {
    $search_type = null;
    $_SESSION['search_type'] = null;
    $search_string = null;
    $_SESSION['search_string'] = null;
}

//If initial load, show user just his/her tickets
if ($mode == "init") {
    $assigned = "yes";
    $_SESSION['assigned'] = $assigned;
    $mytickets = "yes";
    $_SESSION['mytickets'] = $mytickets;
    $status_id = "all";
    $_SESSION['status'] = $status_id;
    $tickettype_id = "all";
    $_SESSION['type'] = $tickettype_id;
}

//Clear session and log out
if ($mode == "logout") {
    session_unset();
    $session->CheckSession();
}


/* * ********************************************************************************************* */

/* * *******************************Set Filters**************************************************** */

//If mytickets is yes, show user just his/her tickets
if ($mytickets == "yes") {
    $rolefilter = $rolefilter . " and user_id = " . mysql_escape_string($_SESSION['user_id']);
}

//If status filter is set then use it
if (!empty($status_id) and is_numeric($status_id)) {
    $filter = $filter . " and status_id = " . $status_id;
}

//If user is not asking for cancelled or complete, then do not show them
if (($status_id != 7 and $status_id != 8) or empty($status_id)) {
    $filter = $filter . " and status_id not in (7, 8)";
}

//If tickettype is set, use it
if (!empty($tickettype_id) and is_numeric($tickettype_id)) {
    $filter = $filter . " and tickettype_id = " . $tickettype_id;
}

//If user wants to see assigned tickets
if ($assigned == "yes") {
    $filter = $filter . " and assigned.ticket_id is not null";
}

//If user wants to see unassigned tickets
if ($assigned == "no") {
    $filter = $filter . " and assigned.ticket_id is null";
    $rolefilter = null;
}

//Apply search filters
if (!empty($search_string) and $search_type == "ticket_content") {
    $filter = $filter . " and (t.name like '%{$search_string}%' or t.description like '%{$search_string}%')";
}

if (!empty($search_string) and $search_type == "ticket_number") {
    if (is_numeric($search_string)) {
        $filter = $filter . " and t.id = {$search_string}";
    } else {
        $filter = $filter . " and t.id = 0";
        $warning = "Ticket number must be a number.";
    }
}



/* * *********************************************************************************** */

/* * ****************************Connect to DB****************************************** */
//Get variables from config/config.php
$db->ezSQL_mysql($dbuser, $dbpass, $dbname, $dbserver);
/* * ********************************************************************************** */

/* * **********************************Pagination************************************** */
//Results per page
$perpage = 25;

//Get bottom limit for page
$bottomlimit = $pagination->BotttomLimit($perpage, $pagenum);

//Get total number of tickets
$totaltickets = $db->get_var("SELECT count(*)
                            FROM ticket as t
                            left join (select distinct ticket_id from ticketuserrole where active = 1 {$rolefilter}) as assigned
                            on t.id = assigned.ticket_id
                            WHERE t.active = 1
                            {$filter}");

//Get total number of pages
$totalpages = ceil($totaltickets / $perpage);

//Get page array for dropdown
$pagearray = $pagination->PageArray($totalpages);
/* * ********************************************************************************* */

/* * ***********************************DB Connection and Query************************ */
//get mountain data aka all tickets
$mountain = $db->get_results("SELECT t.id,
                            t.name,
                            case 
                                when t.complexity_rating = 0
                                then 0
                                else round(((t.business_value_rating * {$bvr_weight}) + (t.priority * {$urgency_weight}) /(t.complexity_rating * {$complexity_weight})), 2)
                                end as value_ratio,
                            t.business_value_rating,
                            t.complexity_rating,
                            t.priority,
                            DATE_FORMAT(t.created_at, '%m/%d/%Y') as created_at,
                            s.name as status,
                            tt.name as tickettype,
                            u.name as user_name,
                            arc.releasecontainer_id,
                            rc.name as releasecontainer_name,
                            round(t.predicted_hours,0) as predicted_hours,
                            round(ifnull(time.hours_spent,0),0) as hours_spent,
                            round(case when t.predicted_hours = 0 then 0 else ifnull((round(ifnull(time.hours_spent,0),0)/round(t.predicted_hours,0))*100,0) end,0) as percent_done
                            FROM ticket as t
                            left join user as u
                            on u.id = t.created_by
                            left join status as s
                            on t.status_id = s.id
                            left join tickettype as tt
                            on tt.id = t.tickettype_id
                            left join (select distinct ticket_id from ticketuserrole where active = 1 {$rolefilter}) as assigned
                            on t.id = assigned.ticket_id
                            left join (select ticket_id, (((sum(hour)*60) + sum(minute))/60) as hours_spent from tickettime where active = 1 group by ticket_id) as time
                            on time.ticket_id = t.id
                            left join assignreleasecontainer as arc
                            on arc.ticket_id = t.id
                            and arc.active = 1
                            left join releasecontainer as rc
                            on arc.releasecontainer_id = rc.id
                            WHERE t.active = 1
                            {$filter}
                            ORDER BY {$orderby} {$ad}, t.id asc
                            LIMIT {$bottomlimit}, {$perpage}");

//get userstatus display data
$user_status = $db->get_results("select
                            us.id as userstatus_id,
                            u.name as user_name,
                            case when us.user_status like 'tk::%::%' then
                            concat(replace(
                                    replace(us.user_status, 'tk::', '<a class=status_link target=_self href=ticket.php?ticket_id='), '::', '>'), '</a>')
                            else us.user_status end as user_status,
                            us.created_at,
                            us.user_id
                            from userstatus as us
                            left join user as u
                            on us.user_id = u.id
                            inner join (select max(id)
                            as id, user_id from userstatus
                            where active = 1 group by user_id) as current
                            on current.id = us.id
                            where us.active = 1
                            and u.active = 1
                            order by us.id desc
                            limit 0, 25");

//Get ticketstats
$ticketstats = $db->get_results("SELECT
                            ifnull(sum(case when t.tickettype_id = 1  and t.status_id = 1 then 1 else 0 end),0) as projects_new,
                            ifnull(sum(case when t.tickettype_id = 1  and t.status_id = 2 then 1 else 0 end),0) as projects_ar,
                            ifnull(sum(case when t.tickettype_id = 1  and t.status_id = 3 then 1 else 0 end),0) as projects_dev,
                            ifnull(sum(case when t.tickettype_id = 1  and t.status_id = 4 then 1 else 0 end),0) as projects_test,
                            ifnull(sum(case when t.tickettype_id = 1  and t.status_id = 5 then 1 else 0 end),0) as projects_imp,
                            ifnull(sum(case when t.tickettype_id = 1  and t.status_id = 6 then 1 else 0 end),0) as projects_pend,
                            ifnull(sum(case when t.tickettype_id = 2  and t.status_id = 1 then 1 else 0 end),0) as tasks_new,
                            ifnull(sum(case when t.tickettype_id = 2  and t.status_id = 2 then 1 else 0 end),0) as tasks_ar,
                            ifnull(sum(case when t.tickettype_id = 2  and t.status_id = 3 then 1 else 0 end),0) as tasks_dev,
                            ifnull(sum(case when t.tickettype_id = 2  and t.status_id = 4 then 1 else 0 end),0) as tasks_test,
                            ifnull(sum(case when t.tickettype_id = 2  and t.status_id = 5 then 1 else 0 end),0) as tasks_imp,
                            ifnull(sum(case when t.tickettype_id = 2  and t.status_id = 6 then 1 else 0 end),0) as tasks_pend,
                            ifnull(sum(case when t.tickettype_id = 3  and t.status_id = 1 then 1 else 0 end),0) as bugs_new,
                            ifnull(sum(case when t.tickettype_id = 3  and t.status_id = 2 then 1 else 0 end),0) as bugs_ar,
                            ifnull(sum(case when t.tickettype_id = 3  and t.status_id = 3 then 1 else 0 end),0) as bugs_dev,
                            ifnull(sum(case when t.tickettype_id = 3  and t.status_id = 4 then 1 else 0 end),0) as bugs_test,
                            ifnull(sum(case when t.tickettype_id = 3  and t.status_id = 5 then 1 else 0 end),0) as bugs_imp,
                            ifnull(sum(case when t.tickettype_id = 3  and t.status_id = 6 then 1 else 0 end),0) as bugs_pend
                            FROM ticket as t
                            left join (select distinct ticket_id from ticketuserrole where active = 1 {$rolefilter}) as assigned
                            on t.id = assigned.ticket_id
                            WHERE t.active = 1
                            {$filter}");

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

//set title
$title = "mound: mountain";
/* * ******************************************************************************************* */

//Send to Smarty
$smarty->assign('releasecontainer_dropdown', $releasecontainer_dropdown);
$smarty->assign('status_id', $status_id);
$smarty->assign('tickettype_id', $tickettype_id);
$smarty->assign('mytickets', $mytickets);
$smarty->assign('assigned', $assigned);
$smarty->assign('user_id', $_SESSION['user_id']);
$smarty->assign('user_status', $user_status);
$smarty->assign('search_string', $search_string);
$smarty->assign('search_type', $search_type);
$smarty->assign('mountain', $mountain);
$smarty->assign('pages', $pagearray);
$smarty->assign('pagenum', $pagenum);
$smarty->assign('totalpages', $totalpages);
$smarty->assign('totaltickets', $totaltickets);
$smarty->assign('ticketstats', $ticketstats);
$smarty->assign('title', $title);
$smarty->display('mountain.xhtml');
?>

