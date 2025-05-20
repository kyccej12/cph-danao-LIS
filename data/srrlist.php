<?php
	session_start();
	include("../handlers/initDB.php");
	$con = new myDB;

	$data = array();
	
	$datares = $con->dbquery("select lpad(srr_no,6,0) as srr, date_format(srr_date,'%m/%d/%Y') as sdate, received_by, remarks, format(amount,2) as amount, status from srr_header where branch = '$_SESSION[branchid]';");
	while($row = $datares->fetch_array()){
	  $data[] = array_map('utf8_encode',$row);
	}
	$results = ["sEcho" => 1,"iTotalRecords" => count($data),"iTotalDisplayRecords" => count($data),"aaData" => $data];
	echo json_encode($results);
?>