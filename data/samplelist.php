<?php
	include("../handlers/_generics.php");
	//ini_set("display_errors","On");
	ini_set('max_execution_time',0);
	ini_set('memory_limit',-1);

	$con = new _init;
	$searchString = '';

	switch($_GET['displayType']) {

		case "1":
			$searchString = " and a.status = '1' ";
		break;
		case "2":
			$searchString = " and a.status = '2' ";
		break;
		case "3":
			$searchString = " and a.status = '3' ";
		break;
		case "4":
			$searchString = " and a.status = '4' ";
		break;
		case "5":
			$searchString = " and a.dopriority = 'STAT' ";
		break;
		default:
			
		break;
	}


	$data = array();
	$datares = $con->dbquery("SELECT enccode, hpercode AS hmrno, DATE_FORMAT(a.dotime,'%m/%d/%Y %h:%i %p') AS orderdate, a.code, a.procedure, a.primecarecode, serialno, patientname, physician, hpatroom as room, extractby, record_id AS id, `status` AS ostat, b.samplestatus, a.is_consolidated, a.dotime, CONCAT(extractdate,' ',extractime) AS tstamp, a.dopriority FROM lab_samples a LEFT JOIN  options_samplestatus b ON a.status = b.id WHERE dotime > '2024-08-01 00:00:00' $searchString GROUP BY a.enccode, a.dotime, a.code, a.serialno;");
	
    while($row = $datares->fetch_array()){
		
        $data[] = array_map('utf8_encode',$row);

	}
	$results = ["sEcho" => 1,"iTotalRecords" => count($data),"iTotalDisplayRecords" => count($data),"aaData" => $data];
	echo json_encode($results);

?>