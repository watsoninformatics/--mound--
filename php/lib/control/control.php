<?php

class Control {

    function isnull($var, $default=null) {
        return is_null($var) ? $default : $var;
    }

    function NumberArray($total) {
        while ($count <= $total) {
            $numberarray[$count] = $count;
            $count++;
        }
        return $numberarray;
    }

    function CheckSession() {
        //Create session
        session_start();

        //Check for active session value
        if (empty($_SESSION["user_id"])) {
            header('Location: login.php');
            exit;
        }
    }

    function Turn_Off_Magic_Quotes() {
        if (get_magic_quotes_gpc ()) {
            $process = array(&$_GET, &$_POST, &$_COOKIE, &$_REQUEST);
            while (list($key, $val) = each($process)) {
                foreach ($val as $k => $v) {
                    unset($process[$key][$k]);
                    if (is_array($v)) {
                        $process[$key][stripslashes($k)] = $v;
                        $process[] = &$process[$key][stripslashes($k)];
                    } else {
                        $process[$key][stripslashes($k)] = stripslashes($v);
                    }
                }
            }
            unset($process);
        }
        set_magic_quotes_runtime(0);
    }

}

?>
