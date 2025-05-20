<?php
	session_start();
	include("../handlers/initDB.php");
	$con = new myDB;
	
	$data = array();
	
	$datares = $con->dbquery("select lpad(mrs_no,6,0) as docno, date_format(mrs_date,'%m/%d/%Y') as sdate, requested_by, date_format(needed_on,'%m/%d/%Y') as nd8, remarks, status from mrs_header where branch = '$_SESSION[branchid]';");
	while($row = $datares->fetch_array()){
	  $row['requested_by'] = html_entity_decode($row['requested_by']);
	  $data[] = array_map('utf8_encode',$row);
	}
	$results = ["sEcho" => 1,"iTotalRecords" => count($data),"iTotalDisplayRecords" => count($data),"aaData" => $data];
	echo json_encode($results);

?>