<?php
	session_start();
	require_once("../handlers/_generics.php");

	$con = new _init;
	$data = array();
	$searchString = '';
	
	switch($_REQUEST['mod']) {
		case "userlist":
			$txt = "SELECT emp_id AS id, LPAD(emp_id,3,'0') AS uid, username AS uname, fullname, IF(STATUS='A','Active','Disabled') AS stat, IF(user_type='admin','Super User','Limited') AS utype, DATE_FORMAT(last_logged_in,'%m/%d/%Y %r') AS lastlogged FROM user_info";
		break;
	}
	
	$datares = $con->dbquery($txt);
	while($row = $datares->fetch_array(MYSQLI_ASSOC)){
	  $data[] = array_map('utf8_encode',$row);
	}
	$results = ["sEcho" => 1,"iTotalRecords" => count($data),"iTotalDisplayRecords" => count($data),"aaData" => $data];
	echo json_encode($results);
?>