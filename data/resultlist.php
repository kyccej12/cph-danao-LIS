<?php
	include("../handlers/_generics.php");
	ini_set('max_execution_time',0);
	ini_set('memory_limit',-1);
	$con = new _init;

	switch($_GET['mod']) {
		case "1":
			$searchString = " and a.dopriority = 'STAT' ";
		break;
		case "2":
			$searchString = " and b.subcategory = '4' ";
		break;
		case "3":
			$searchString = " and b.subcategory = '1' ";
		break;
		case "4":
			$searchString = " and b.subcategory = '8' ";
		break;
		case "5":
			$searchString = " and b.subcategory = '5' ";
		break;
		case "6":
			$searchString = " and b.subcategory = '2' ";
		break;
		case "7":
			$searchString = " and b.subcategory not in (1,2,4,5,8) ";
		break;
	}

	$data = array();
	$datares = $con->dbquery("SELECT record_id AS id, enccode, hpercode AS hmrno, SUBSTR(enccode,23,10) AS orderdate, a.code, a.procedure, serialno, patientname, DATE_FORMAT(CONCAT(extractdate,' ',extractime),'%m/%d/%Y %h:%i %p') AS tstamp, hpatroom as room, a.physician, a.primecarecode, a.is_consolidated, a.released, a.dotime, DATE_FORMAT(a.printed_on,'%m/%d/%Y %h:%i %p') AS printed_on FROM lab_samples a LEFT JOIN services_master b on a.primecarecode = b.code left join user_info c ON a.created_by = c.emp_id WHERE a.status = '4' and a.dotime > '2024-06-01' $searchString;");
	
    while($row = $datares->fetch_array(MYSQLI_ASSOC)){

        $data[] = array_map('utf8_encode',$row);

	}
	$results = ["sEcho" => 1,"iTotalRecords" => count($data),"iTotalDisplayRecords" => count($data),"aaData" => $data];
	echo json_encode($results);

?>