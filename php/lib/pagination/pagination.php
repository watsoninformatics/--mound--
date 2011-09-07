<?php

class Pagination {

    function BotttomLimit($perpage, $pagenum) {
        $lim = (($pagenum - 1) * $perpage);
        if ($lim < 0) {
            $lim = 0;
        }
        return $lim;
    }

    function PageArray($totalpages) {
        while ($count <= $totalpages) {
            $pagearray[$count] = $count;
            $count++;
        }
        return $pagearray;
    }

}

?>
