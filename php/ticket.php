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

//Get tab, default to ticket info
$tab = $_GET["tab"];
if (empty($tab)) {
    $tab = "ticket";
}


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

//This section deals with updates to ticket info
if (($_GET["mode"] == "edit_ticket" and !empty($ticket_id)) or ($_GET["mode"] == "new_ticket")) {

    //Clean up variables
    $_POST['ticket_name'] = mysql_escape_string(strip_tags($_POST['ticket_name']));
    $_POST['predicted_hours'] = mysql_escape_string(strip_tags($_POST['predicted_hours']));
    $_POST['tickettype_id'] = mysql_escape_string(strip_tags($_POST['tickettype_id']));
    $_POST['status_id'] = mysql_escape_string(strip_tags($_POST['status_id']));
    $_POST['ticket_info'] = mysql_escape_string(strip_tags($_POST['ticket_info']));
    $_POST['business_value_rating'] = mysql_escape_string($_POST['business_value_rating']);
    $_POST['complexity_rating'] = mysql_escape_string($_POST['complexity_rating']);
    $_POST['priority'] = mysql_escape_string($_POST['priority']);

    //Default corrupt or null values
    if (empty($_POST['ticket_name'])) {
        $_POST['ticket_name'] = "No Ticket Name";
    }
    if (empty($_POST['predicted_hours']) or !is_numeric($_POST['predicted_hours'])) {
        $_POST['predicted_hours'] = 0;
    }
    if (empty($_POST['business_value_rating'])) {
        $_POST['business_value_rating'] = 1;
    }
    if (empty($_POST['priority'])) {
        $_POST['priority'] = 1;
    }
    if (empty($_POST['complexity_rating'])) {
        $_POST['complexity_rating'] = 1;
    }
    if (empty($_POST['ticket_info'])) {
        $_POST['ticket_info'] = "No description given.";
    }
    if (empty($_POST['status_id'])) {
        $_POST['status_id'] = 1;
    }
    if (empty($_POST['tickettype_id'])) {
        $_POST['tickettype_id'] = 1;
    }



    //If user is editing ticket data
    if (!empty($_POST["edit_ticket"])) {

        //Run the SQL update
        $db->get_results("update ticket
        set name = '{$_POST['ticket_name']}',
        predicted_hours = {$_POST['predicted_hours']},
        business_value_rating = {$_POST['business_value_rating']},
        complexity_rating = {$_POST['complexity_rating']},
        description = '{$_POST['ticket_info']}',
        tickettype_id = '{$_POST['tickettype_id']}',
        status_id = '{$_POST['status_id']}',
        priority = '{$_POST['priority']}'
        where id = {$ticket_id}");
    }

    //If user is adding a new ticket
    if (!empty($_POST["add_ticket"])) {
        //Run the insert for the new ticket
        $db->get_results("insert into ticket (name, predicted_hours, business_value_rating, complexity_rating, description, created_by, priority, tickettype_id, status_id)
            values ('{$_POST['ticket_name']}',
        {$_POST['predicted_hours']},
        {$_POST['business_value_rating']},
        {$_POST['complexity_rating']},
        '{$_POST['ticket_info']}',
        {$_SESSION['user_id']},
        {$_POST['priority']},
        {$_POST['tickettype_id']},
        {$_POST['status_id']})");

        //Get the new ticket's ID
        $ticket_id = $db->get_var("select max(id) from ticket where created_by = {$_SESSION['user_id']}");
    }


    //If users adds a note
    if (!empty($_POST["add_note"])) {

        $note = mysql_escape_string(strip_tags($_POST["note"]));
        if (!empty($note)) {
            //If users input a note, then insert it
            $db->get_results("insert into ticketnote (ticket_id, user_id, note)
            values ('{$ticket_id}', '{$_SESSION['user_id']}', '{$note}')");
            if ($_POST['statusnote'] == true) {
                $db->get_results("insert into userstatus (user_id, user_status)
                 values ({$_SESSION['user_id']}, 'tk::{$ticket_id}::{$note}')");
            }
        } else {

            //If the user failed to input a note, return a warning.
            $warning = "Cannot post blank notes.";
        }
    }

    //Send the user back to the main ticket tab
    $tab = "ticket";
} else if ($_GET["mode"] == "edit_attachment" and !empty($ticket_id)) { //This is where users can add or delete attachments
    //If user chooses to delete the attachment
    if (!empty($_POST["delete"])) {
        //All I am doing here is setting the record to inactive, not deleting the actual file or record
        $attachment_id = $_GET["attachment_id"];
        $db->get_results("update ticketattachment
        set active = 0
        where id = {$attachment_id}");
    }
    //If the user is uploading a new file
    if (!empty($_POST["upload"])) {
        //Getting a guid to keep the file name unique
        $guid = uniqid();
        $upload = new Upload($_FILES['attachment_file']);
        $attachment_description = mysql_escape_string($_POST['attachment_description']);

        // save uploaded image with a new name
        $upload->file_new_name_body = $guid;
        $upload->Process($attachment_folder);
        if ($upload->processed) {
            if (empty($attachment_description)) {
                $attachment_description = mysql_escape_string($upload->file_src_name);
            };
            //If I was able to process the attachment, I insert the record
            $db->get_results("insert into ticketattachment (ticket_id, source, description)
                   values ({$ticket_id}, '{$attachment_folder}{$upload->file_dst_name}', '{$attachment_description}')");
            $warning = "Attachment successfully uploaded.";
        } else {
            //If processing the attachment failed, send a warning
            $warning = "Could not upload attachment.  Please try again.";
        }
    }
    //Send back to attachments tab
    $tab = "attachments";
} else if ($_GET["mode"] == "edit_time" and !empty($ticket_id)) { //Where user's can add or remove time tracking
    //If users is deleting
    if (!empty($_POST["delete"])) {
        $time_id = $_GET["time_id"];

        //Just mark the record inactive
        $db->get_results("update tickettime
        set active = 0
        where id = {$time_id}");
    }

    //Adds new time
    if (!empty($_POST["add_time"])) {

        //if stopwatch value was passed, use it
        if (!empty($_POST["stopwatch"])) {
            list($_POST["hour"], $_POST["minute"], $sec, $msec) = explode(':', $_POST["stopwatch"]);
            if ($sec > 29) {
                $_POST["minute"] = ((int) $_POST["minute"]) + 1;
            }
        }

        $hour = mysql_escape_string($_POST["hour"]);
        $minute = mysql_escape_string($_POST["minute"]);
        $time_description = mysql_escape_string(strip_tags($_POST["time_description"]));
        if (!is_numeric($hour)) { //If the user puts crap in the number field, it defaults to 0
            $hour = 0;
        }
        if (!is_numeric($minute)) { //Same crap check for minutes
            $minute = 0;
        }

        //Runs the actual insert
        $db->get_results("insert into tickettime (ticket_id, user_id, hour, minute, description)
            values ('{$ticket_id}', '{$_SESSION['user_id']}', '{$hour}', '{$minute}', '{$time_description}')");
    }

    //Send user back to the time tab
    $tab = "time";
} else if ($_GET["mode"] == "edit_role" and !empty($ticket_id)) { //Where user's can add or remove user roles
    //If users is deleting
    if (!empty($_POST["delete"])) {
        $tur_id = $_GET["tur_id"];

        //Just mark the record inactive
        $db->get_results("update ticketuserrole
        set active = 0
        where id = {$tur_id}");
    }

    //Adds new role record
    if (!empty($_POST["add_userrole"])) {

        $tur_user_id = mysql_escape_string($_POST["tur_user_id"]);
        $tur_role_id = mysql_escape_string($_POST["tur_role_id"]);

        //Check if user already has a role on this ticket
        $tur_exists = $db->get_var("select count(*) from ticketuserrole where active = 1 and user_id = {$tur_user_id} and ticket_id = {$ticket_id}");

        //If no role record exist on this ticket for this user, then inssert, else warn the user
        if ($tur_exists == 0) {
            //Runs the actual insert
            $db->get_results("insert into ticketuserrole (ticket_id, user_id, ticketrole_id)
            values ('{$ticket_id}', '{$tur_user_id}', '{$tur_role_id}')");
        } else {
            $warning = "Cannot add multiple roles for single user.";
        }
    }

    //Send user back to the user role tab
    $tab = "role";
} else if ($_GET["mode"] == "edit_breakdown" and !empty($ticket_id)) { //Where user's can add or remove user breakdown items
    $breakdown_id = mysql_escape_string($_GET["breakdown_id"]);

//If users is deleting
    if (!empty($_POST["delete"])) {

        if (!empty($breakdown_id)) {
            //Just mark the record inactive
            $db->get_results("update ticketbreakdown
                            set active = 0
                            where id = {$breakdown_id}");
        } else {
            $warning = "An error occured deleting breakdown item.";
        }
    }

    //Adds new breakdown item
    if (!empty($_POST["add_breakdown_item"])) {

        $breakdown_description = mysql_escape_string($_POST["breakdown_description"]);

        if (!empty($breakdown_description)) {
            //Runs the actual insert
            $db->get_results("insert into ticketbreakdown (ticket_id, description, created_by)
            values ('{$ticket_id}', '{$breakdown_description}', '{$_SESSION['user_id']}')");
        } else {
            $warning = "No breakdown name specified.";
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


    //Send user back to the breakdown tab
    $tab = "breakdown";
} else if ($_GET["mode"] == "edit_releasecontainer" and !empty($ticket_id)) { //Where user's can add or remove release containers
    //Get release container id
    $releasecontainer_id = mysql_escape_string($_GET["releasecontainer_id"]);

    //If user is deleting
    if (!empty($_POST["delete"]) and !empty($releasecontainer_id)) {
        //Just mark the record inactive
        $db->get_results("update assignreleasecontainer
        set active = 0
        where releasecontainer_id = {$releasecontainer_id}
        and ticket_id = {$ticket_id}");
    }

    //Adds new release container
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

        $assignreleasecontainer_exists = $db->get_var("select count(*)
                                            from assignreleasecontainer as arc
                                            where arc.active = 1
                                            and arc.ticket_id = '{$ticket_id}'");

        //If ticket not currently in a container ... add to new container
        if ($assignreleasecontainer_exists == 0 and !empty($releasecontainer_name)) {
            $db->get_results("insert into assignreleasecontainer (ticket_id, releasecontainer_id)
                            values ({$ticket_id}, {$releasecontainer_id})");
            $warning = "New container created and assigned.";
        } else if (empty($releasecontainer_name)) {
            //do nothing it release container name was null
        } else {
            $warning = "New container created, but not assigned to current ticket.";
        }
    }

    //If the user chooses to assign this ticket to a container
    if (!empty($_POST["assign_release_container"]) and !empty($_POST["releasecontainer_id"])) {

        $releasecontainer_id = mysql_escape_string($_POST["releasecontainer_id"]);

        $assignreleasecontainer_exists = $db->get_var("select count(*)
                                            from assignreleasecontainer as arc
                                            where arc.active = 1
                                            and arc.ticket_id = '{$ticket_id}'");

        if ($assignreleasecontainer_exists == 0) {
            //Just insert into the assign table
            $db->get_results("insert into assignreleasecontainer (ticket_id, releasecontainer_id)
                values ({$ticket_id}, {$releasecontainer_id})");
            $warning = "Release container assigned.";
        } else {
            $warning = "A ticket can only be assigned to a single release container.";
        }
    }

    //Send user back to the rc tab
    $tab = "releasecontainer";
}



/* * ****************************************************************** */

/* * ***********************************DB Connection and Query************************ */
//Get data to display

if (!empty($ticket_id)) {
//Returns ticket notes
    $ticket_notes = $db->get_results("select tn.id,
                            replace(tn.note, char(13), '<br />') as note,
                            tn.created_at,
                            u.name as user_name
                            from ticketnote as tn
                            left join user as u
                            on u.id = tn.user_id
                           where tn.active = 1
                           and tn.ticket_id = {$ticket_id}
                           order by tn.id desc");
}

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

if ($tab == "ticket" or $tab == "ticket_edit" or $tab == "ticket_new") {

    if (is_numeric($ticket_id) and !empty($ticket_id)) {

        //Main ticket data
        $ticket = $db->get_results("SELECT t.id,
                            t.name,
                            case when t.complexity_rating = 0
                                then 0
                                else round(((t.business_value_rating * {$bvr_weight}) + (t.priority * {$urgency_weight}) /(t.complexity_rating * {$complexity_weight})), 2)
                                end as value_ratio,
                            t.business_value_rating,
                            t.complexity_rating,
                            DATE_FORMAT(t.created_at, '%m/%d/%Y') as created_at,
                            s.name as status_name,
                            s.id as status_id,
                            tt.name as ticket_type,
                            t.tickettype_id as tickettype_id,
                            u.name as user_name,
                            t.description as ticket_info,
                            replace(t.description, char(13), '<br />') as ticket_info_html,
                            t.predicted_hours,
                            t.priority
                            FROM ticket as t
                            left join user as u
                            on u.id = t.created_by
                            left join status as s
                            on t.status_id = s.id
                            left join tickettype as tt
                            on tt.id = t.tickettype_id
                            WHERE t.id = {$ticket_id}
                            LIMIT 1");
    }

    //Values for the status dropdown box
    $status_dropdown = $db->get_results("select id, name
                            from status
                           where active = 1
                           order by id");

    //Values for the Ticket Type dropdown box
    $tickettype_dropdown = $db->get_results("select id, name
                            from tickettype
                           where active = 1
                           order by id");
} else if ($tab == "attachments") { //view attachments
//Get attachments
    $attachments = $db->get_results("select id as attachment_id,
                                ticket_id,
                                source,
                                description,
                                created_at
                                from ticketattachment
                                where ticket_id = {$ticket_id}
                                and active = 1
                                order by id");
} else if ($tab == "time") { //view time data
//Get time data
    $time = $db->get_results("select ti.id as time_id,
                           u.name as user_name,
                           ti.hour,
                           ti.minute,
                           ti.description,
                           ti.created_at 
                           from tickettime as ti
                           left join user as u
                           on u.id = ti.user_id
                           where ti.ticket_id = {$ticket_id}
                           and ti.active = 1
                           order by ti.id");

//Get time total
    $time_total = $db->get_results("select
                           floor(((sum(ti.hour)*60) + sum(ti.minute)) / 60) as total_hours,
                           ((sum(ti.hour)*60) + sum(ti.minute)) % 60 as total_minutes
                           from tickettime as ti
                           where ti.ticket_id = {$ticket_id}
                           and ti.active = 1");
} else if ($tab == "role") { //view user roles
    $users_dropdown = $db->get_results("select id, name from user where active = 1 order by name");

    $roles_dropdown = $db->get_results("select id, name from ticketrole where active = 1 order by id");

    $ticketuserrole_dropdown = $db->get_results("select tur.id,
                                                    u.name as ticket_user,
                                                    tr.name as ticket_role
                                                    from ticketuserrole as tur
                                                    inner join user as u
                                                    on u.id = tur.user_id
                                                    inner join ticketrole as tr
                                                    on tr.id = tur.ticketrole_id
                                                    where tur.active = 1 
                                                    and tur.ticket_id = {$ticket_id}
                                                    order by tur.id");
} else if ($tab == "breakdown") {

    ///View breakdown details
    $breakdown = $db->get_results("select bd.id,
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
    where bd.ticket_id = {$ticket_id}
    and bd.active = 1
    order by bd.id");
} else if ($tab == "releasecontainer") {

    //Get the release container
    $releasecontainer = $db->get_results("select rc.id, rc.name as container_name
                                    from assignreleasecontainer as arc
                                    inner join releasecontainer as rc
                                    on rc.id = arc.releasecontainer_id
                                    and arc.ticket_id = {$ticket_id} 
                                    and rc.active = 1
                                    and arc.active = 1");
}

//Create title
$title = "mound: ticket: " . $control->isnull($ticket_id, 'new');
/* * ******************************************************************************************* */

//Send to Smarty
$smarty->assign('releasecontainer', $releasecontainer);
$smarty->assign('releasecontainer_dropdown', $releasecontainer_dropdown);
$smarty->assign('breakdown', $breakdown);
$smarty->assign('users_dropdown', $users_dropdown);
$smarty->assign('roles_dropdown', $roles_dropdown);
$smarty->assign('ticketuserrole_dropdown', $ticketuserrole_dropdown);
$smarty->assign('tab', $tab);
$smarty->assign('warning', $warning);
$smarty->assign('rating_dropdown', $control->NumberArray(10));
$smarty->assign('ticket_id', $ticket_id);
$smarty->assign('title', $title);
$smarty->assign('ticket', $ticket);
$smarty->assign('tickettype_dropdown', $tickettype_dropdown);
$smarty->assign('status_dropdown', $status_dropdown);
$smarty->assign('ticket_notes', $ticket_notes);
$smarty->assign('time', $time);
$smarty->assign('time_total', $time_total);
$smarty->assign('attachments', $attachments);
$smarty->display('ticket.xhtml');
?>

