<?php
    include("../handlers/_generics.php");
    ini_set("display_errors","On");

    $con = new _init();

    $a = $con->dbquery("SELECT dotime,enccode FROM lab_samples WHERE physician = '' ORDER BY dotime;");
    while($b = $a->fetch_array()) {
        
        list($licno) = $con->getArray("select licno from hospital_dbo.hdocord where enccode = '$b[enccode]' and dodate = '$b[dotime]';");
        list($provider) =  $con->getArray("SELECT CONCAT(empprefix,'. ',firstname, ' ',middlename, ' ', lastname) FROM hospital_dbo.hprovider a LEFT JOIN hospital_dbo.hpersonal b ON a.employeeid = b.employeeid WHERE a.licno = '$licno';");

    
        $sqlQuery = "UPDATE IGNORE lab_samples set physician = '$provider' where enccode = '$b[enccode]' and dotime = '$b[dotime]';";
        $con->dbquery($sqlQuery);
    
    }

?>