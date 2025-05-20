<?php
	session_start();
	include("../handlers/initDB.php");
	$con = new myDB;
	
	$data = array();
	
	$datares = $con->dbquery("select lpad(sw_no,6,0) as docno, date_format(sw_date,'%m/%d/%Y') as sdate, withdrawn_by, remarks, format(amount,2) as amount, status from sw_header where branch = '$_SESSION[branchid]';");
	while($row = $datares->fetch_array()){
	  $row['withdrawn_by'] = html_entity_decode($row['withdrawn_by']);
	  $data[] = array_map('utf8_encode',$row);
	}
	$results = ["sEcho" => 1,"iTotalRecords" => count($data),"iTotalDisplayRecords" => count($data),"aaData" => $data];
	echo json_encode($results);

?>