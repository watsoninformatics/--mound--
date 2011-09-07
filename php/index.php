<?php

//Load all includes
include "IncludeMaster.php";

//Start new session
session_start();

//Check for active session value
if (!empty($_SESSION["user_id"])) {
    header('Location: mountain.php?mode=init');
}
else {
    header('Location: login.php');
}

?>
