<?php
    ini_set("max_execution_time",0);
    require_once("../handlers/_generics.php");

    $con = new _init();

    $i = 1;
    $a = $con->dbquery("select distinct enccode from lab_samples where hpatroom = '';");
    while(list($enccode) = $a->fetch_array()) {
        $room = $con->identRoom($enccode);
        if($room == '') { 
            list($room) = $con->getArray("select toecode from hospital_dbo.henctr where enccode = '$enccode';");
        }     

        $con->dbquery("UPDATE IGNORE lab_samples set hpatroom = '$room' where enccode = '$enccode';");
        echo "($i) UPDATE IGNORE lab_samples set hpatroom = '$room' where enccode = '$enccode';<br/>";

        $i++;
    }

?>