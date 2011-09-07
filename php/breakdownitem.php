<?php

//Get all includes
include "IncludeMaster.php";

//Create session and check user
$session = new Control();
$session->CheckSession();

//Turns off stupid magic quotes
$session->Turn_Off_Magic_Quotes();

/* * *************************Get vars****************************************** */

//Get breakdown number
$breakdown_id = mysql_escape_string($_GET["breakdown_id"]);
//Get ticket number
$ticket_id = mysql_escape_string($_GET["ticket_id"]);


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

if ($_GET["mode"] == "edit_breakdown" and !empty($breakdown_id)) {
//If users is deleting
    if (!empty($_POST["delete"])) {
        //Just mark the record inactive
        $db->get_results("update ticketbreakdown
        set active = 0
        where id = {$breakdown_id}");

        header("Location: ticket.php?ticket_id={$ticket_id}&tab=breakdown");
    }

    //Edit breakdown item
    if (!empty($_POST["edit_breakdown_item"])) {

        $breakdown_description = mysql_escape_string($_POST["breakdown_description"]);
        if (!empty($breakdown_description)) {
            //Runs the actual insert
            $db->get_results("update ticketbreakdown
            set description = '$breakdown_description'
                where id = {$breakdown_id}");
        } else {
            $warning = "Cannot insert blank description.";
        }
    }

    //If user chooses to mark the item complete
    if (!empty($_POST["mark_complete"])) {

        //Just mark the record completed
        $db->get_results("update ticketbreakdown
        set complete = 1,
        completed_by = {$_SESSION['user_id']}
        where id = {$breakdown_id}");
    }

    //If user chooses to mark back to incomplete
    if (!empty($_POST["mark_incomplete"])) {

        //Just mark the record completed
        $db->get_results("update ticketbreakdown
        set complete = 0
        where id = {$breakdown_id}");
    }


    //If users adds a note
    if (!empty($_POST["add_note"])) {

        $note = mysql_escape_string(strip_tags($_POST["note"]));
        if (!empty($note)) {
            //If users input a note, then insert it
            $db->get_results("insert into breakdownnote (breakdown_id, user_id, note)
            values ('{$breakdown_id}', '{$_SESSION['user_id']}', '{$note}')");
            if ($_POST['statusnote'] == true) {
                $db->get_results("insert into userstatus (user_id, user_status)
                 values ({$_SESSION['user_id']}, 'tk::{$ticket_id}::{$note}')");
            }
        } else {

            //If the user failed to input a note, return a warning.
            $warning = "Cannot post blank notes.";
        }
    }
}



/* * ****************************************************************** */

/* * ***********************************DB Connection and Query************************ */


///View breakdown details
$breakdown = $db->get_results("select bd.id,
    bd.ticket_id,
    bd.created_at,
    u2.name as created_by,
    bd.description,
    case when bd.complete = 1 then 1 else 0 end as complete,
    case bd.complete when 1 then concat('Complete by ', u.name)  else 'Incomplete' end as complete_text
    from ticketbreakdown as bd
    left join user as u
    on u.id = bd.completed_by
    left join user as u2
    on u2.id = bd.created_by
    where bd.id = {$breakdown_id}
    and bd.active = 1
    order by bd.id");

//Returns breakdown notes
$breakdown_notes = $db->get_results("select bd.id,
                            replace(bd.note, char(13), '<br />') as note,
                            bd.created_at,
                            u.name as user_name
                            from breakdownnote as bd
                            left join user as u
                            on u.id = bd.user_id
                           where bd.active = 1
                           and bd.breakdown_id = {$breakdown_id}
                           order by bd.id desc");

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


//Create title
$title = "mound: breakdown: " . $control->isnull($breakdown_id, 'new');
/* * ******************************************************************************************* */

//Send to Smarty
$smarty->assign('releasecontainer_dropdown', $releasecontainer_dropdown);
$smarty->assign('mode', $_GET["mode"]);
$smarty->assign('breakdown', $breakdown);
$smarty->assign('breakdown_id', $breakdown_id);
$smarty->assign('ticket_id', $ticket_id);
$smarty->assign('breakdown_notes', $breakdown_notes);
$smarty->assign('warning', $warning);
$smarty->assign('title', $title);
$smarty->assign('breakdown_notes', $breakdown_notes);
$smarty->display('breakdownitem.xhtml');
?>

