<?php

//Get all includes
include "IncludeMaster.php";

//Create session and check user
$session = new Control();
$session->CheckSession();

//Turns off stupid magic quotes
$session->Turn_Off_Magic_Quotes();

/* * *************************Get vars****************************************** */

//Get ticket number
$ticket_id = mysql_escape_string($_GET["ticket_id"]);

//Get release container
$releasecontainer_id = mysql_escape_string($_GET["releasecontainer_id"]);

/* * ******************************************************************* */

/* * **************************Create Objects************************************** */
//Create smarty
$smarty = new Smarty();
//create new ezsql object
$db = new ezSQL_mysql();
//create controls object
$control = new Control();
/* * ********************************************************************************* */

/* * ****************************Connect to DB****************************************** */
//Get variables from config/config.php
$db->ezSQL_mysql($dbuser, $dbpass, $dbname, $dbserver);
/* * ********************************************************************************** */

/* * **************************Inserts, Updates and Deletes*********************************** */

if ($_GET["mode"] == "edit_releasecontainer") {
    //If user is deleting from container
    if (!empty($_POST["remove_from_container"]) and !empty($releasecontainer_id) and !empty($ticket_id)) {
        //Just mark the record inactive
        $db->get_results("update assignreleasecontainer
        set active = 0
        where releasecontainer_id = {$releasecontainer_id}
        and ticket_id = {$ticket_id}");
    }

    //If user is creating a new release container
    if (!empty($_POST["add_release_container"])) {

        $releasecontainer_name = mysql_escape_string(strip_tags($_POST["releasecontainer_name"]));

        if (!empty($releasecontainer_name)) {

            $releasecontainer_id = $db->get_var("select max(id)
                                            from releasecontainer
                                            where active = 1
                                            and name = '{$releasecontainer_name}'");

            if (empty($releasecontainer_id)) {
                //Runs the actual insert
                $db->get_results("insert into releasecontainer (name)
                                    values ('{$releasecontainer_name}')");

                $releasecontainer_id = $db->get_var("select max(id)
                                            from releasecontainer
                                            where active = 1
                                            and name = '{$releasecontainer_name}'");
            } else {
                $warning = "Container already exists.";
            }
        } else {
            $warning = "Must enter a name for new release container.";
        }
    }

    //If user is releting release container
    if (!empty($_POST["delete_release_container"])) {

        //Remove all items from container
        $db->get_results("update assignreleasecontainer set active = 0 where releasecontainer_id = {$releasecontainer_id}");
        //Remove container
        $db->get_results("update releasecontainer set active = 0 where id = {$releasecontainer_id}");
        //Show warning
        $warning = "Release Container removed.";
        $releasecontainer_id = null;
    }
}

/* * ****************************************************************** */

/* * ***********************************DB Connection and Query************************ */
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

if (!empty($releasecontainer_id)) {

//Get ticket data to display
    $ticket = $db->get_results("select t.id as ticket_id,
                                        t.name as ticket_name,
                                        s.name as ticket_status,
                                        round(t.predicted_hours,0) as predicted_hours,
                                        round(ifnull(time.hours_spent,0),0) as hours_spent,
                                        round(case when t.predicted_hours = 0 then 0 else ifnull((round(ifnull(time.hours_spent,0),0)/round(t.predicted_hours,0))*100,0) end,0) as percent_done
    from ticket as t
    inner join status as s
    on s.id = t.status_id
    inner join assignreleasecontainer as arc
    on arc.ticket_id = t.id
    and arc.active = 1
    and t.active = 1
    and arc.releasecontainer_id = {$releasecontainer_id}
    left join (select ticket_id, (((sum(hour)*60) + sum(minute))/60) as hours_spent from tickettime where active = 1 group by ticket_id) as time
    on time.ticket_id = t.id
    order by t.id");

    //Get attachments
    $attachments = $db->get_results("select ta.id as attachment_id,
                                ta.ticket_id,
                                ta.source,
                                ta.description,
                                ta.created_at
                                from ticketattachment as ta
                                inner join assignreleasecontainer as arc
                                on arc.ticket_id = ta.ticket_id
                                and arc.active = 1
                                and ta.active = 1
                                and arc.releasecontainer_id = {$releasecontainer_id}
                                order by ta.created_at");

    //Get time total
    $time_total = $db->get_results("select
                           ifnull(floor(((sum(ti.hour)*60) + sum(ti.minute)) / 60),0) as total_hours,
                           ifnull(((sum(ti.hour)*60) + sum(ti.minute)) % 60,0) as total_minutes
                           from tickettime as ti
                           inner join assignreleasecontainer as arc
                                on arc.ticket_id = ti.ticket_id
                                and arc.active = 1
                                and ti.active = 1
                                and arc.releasecontainer_id = {$releasecontainer_id}");

    //Get Notes
    $notes = $db->get_results("select t.id as ticket_id,
                            concat(left(t.name, 20), '...') as ticket_name,
                            replace(tn.note, char(13), '<br />') as note,
                            tn.created_at,
                            u.name as user_name
                            from ticketnote as tn
                            inner join ticket as t
                            on t.id = tn.ticket_id
                            inner join assignreleasecontainer as arc
                            on arc.ticket_id = tn.ticket_id
                                and arc.active = 1
                                and tn.active = 1
                                and arc.releasecontainer_id = {$releasecontainer_id}
                            left join user as u
                            on u.id = tn.user_id
                            order by tn.id desc");

    //Get the name of the container
    $releasecontainer_name = $db->get_var("select name from releasecontainer where id = {$releasecontainer_id}");
}


//Create title
$title = "mound: release container: " . $control->isnull($releasecontainer_id, 'new');
/* * ******************************************************************************************* */

//Send to Smarty
$smarty->assign('releasecontainer_dropdown', $releasecontainer_dropdown);
$smarty->assign('warning', $warning);
$smarty->assign('releasecontainer_id', $releasecontainer_id);
$smarty->assign('releasecontainer_name', $releasecontainer_name);
$smarty->assign('title', $title);
$smarty->assign('attachments', $attachments);
$smarty->assign('notes', $notes);
$smarty->assign('time_total', $time_total);
$smarty->assign('ticket', $ticket);
$smarty->display('releasecontainer.xhtml');
?>

