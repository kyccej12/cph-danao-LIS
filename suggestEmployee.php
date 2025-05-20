<?php
	session_start();
	require_once 'handlers/initDB.php';
	$db = new myDB;

	unset($my_arr);
	unset($my_arr_row);
	
	$term = trim(strip_tags($_GET['term'])); 
	$r = $db->dbquery("select * from (SELECT employeeid as emp_id, CONCAT(firstname, ' ',middlename, ' ',lastname) as emp_name FROM hospital_dbo.hpersonal WHERE  (LOCATE('$term', lastname) > 0 OR LOCATE('$term',firstname) > 0) AND empstat = 'A') a where emp_name is not null;");
	$my_arr = array();
	$my_arr_row = array();
	if($r) {
		while($row = $r->fetch_array(MYSQLI_ASSOC)) {
			$my_arr_row['emp_id'] = $row['emp_id'];
			$my_arr_row['value'] = $row['emp_name'];
			$my_arr_row['label'] = $row['emp_name'];
			array_push($my_arr,$my_arr_row);
		}
	}

	echo json_encode($my_arr);
?>