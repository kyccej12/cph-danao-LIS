<?php
	session_start();
	include("handlers/_generics.php");
	$con = new _init;

	function updateAmount($sw_no) {
		global $con;
		list($total) = $con->getArray("select ifnull(sum(qty*cost),0) from sw_details where sw_no = '$sw_no' and branch = '$_SESSION[branchid]';");
		$con->dbquery("update sw_header set amount = '$total' where sw_no = '$sw_no' and branch = '$_SESSION[branchid]';");
		echo number_format($total,2);
	}

	switch($_REQUEST['mod']) {
		case "saveHeader":

			if($_POST['sw_no'] != '') {
				$s = "update ignore sw_header set withdrawn_by = '".$con->escapeString(htmlentities($_POST['wby']))."', cost_center = '$_POST[cost_center]', ppp_id = '$_POST[ppp_id]', sw_date = '".$con->formatDate($_POST['sw_date'])."', ref_type = '$_POST[ref_type]', request_date ='".$con->formatDate($_POST['request_date'])."', mr_no = '$_POST[mr_no]', remarks = '".$con->escapeString(htmlentities($_POST['remarks']))."', updated_by = '$_SESSION[userid]', updated_on = now() where sw_no = '$_POST[sw_no]' and branch = '$_SESSION[branchid]';";
				$sw_no = $_POST['sw_no'];
			} else {
				list($sw_no) = $con->getArray("select ifnull(max(sw_no),0)+1 from sw_header where branch = '$_SESSION[branchid]';"); 
				$s = "insert ignore into sw_header (branch,sw_no,sw_date,withdrawn_by,cost_center,ppp_id,request_date,ref_type,mr_no,remarks,trace_no,created_by,created_on) values ('$_SESSION[branchid]','$sw_no','".$con->formatDate($_POST['sw_date'])."','".$con->escapeString(htmlentities($_POST['wby']))."','$_POST[cost_center]','$_POST[ppp_id]','".$con->formatDate($_POST['request_date'])."','$_POST[ref_type]','$_POST[mr_no]','".$con->escapeString(htmlentities($_POST['remarks']))."','$_POST[trace_no]','$_SESSION[userid]',now());";
			}
			//echo $s;
			$con->dbquery($s);
			echo str_pad($sw_no,6,'0',STR_PAD_LEFT);
		break;

		case "addItem":

			if($_POST['expiry'] != '') { $expiry = $con->formatDate($_POST['expiry']); } else { $expiry = '0000-00-00'; }

			list($isE) = $con->getArray("select count(*) from sw_details where trace_no = '$_POST[trace_no]' and item_code = '$_POST[item]' and branch = '$_SESSION[branchid]' and expiry = '$expiry' and lot_no = '$_POST[lot_no]';");
			if($isE > 0) {
				$s = "update ignore sw_details set qty = qty + ".$con->formatDigit($_POST['qty'])." where trace_no = '$_POST[trace_no]' and item_code = '$_POST[item]' and and branch = '$_SESSION[branchid]' and expiry = '$expiry' and lot_no = '$_POST[lot_no]';";
			} else {
				$s = "insert ignore into sw_details (branch,sw_no,item_code,description,qty,unit,expiry,lot_no,trace_no) values ('$_SESSION[branchid]','$_POST[sw_no]','$_POST[item]','".$con->escapeString(htmlentities($_POST['description']))."','".$con->formatDigit($_POST['qty'])."','$_POST[unit]','$expiry','$_POST[lot_no]','$_POST[trace_no]');";
			}
		
			$con->dbquery($s);
			updateAmount($_POST['sw_no']);
		break;
		
		case "deleteLine":
			$con->dbquery("delete from sw_details where line_id = '$_POST[lid]';");
			updateAmount($_POST['sw_no']);
		break;
	
		case "retrieveLine":
			echo json_encode($con->getArray("select *,if(expiry!='0000-00-00',date_format(expiry,'%m/%d/%Y'),'') as exp from sw_details where line_id = '$_POST[lid]';"));
		break;

		case "updateItem":
			$con->dbquery("update sw_details set item_code = '$_POST[item]', description = '".$con->escapeString(htmlentities($_POST['description']))."', qty = '".$con->formatDigit($_POST['qty'])."', unit = '$_POST[unit]', lot_no = '$_POST[lot_no]', expiry = '".$con->formatDate($_POST['expiry'])."' where line_id = '$_POST[lid]';");
			updateAmount($_POST['sw_no']);
		break;

		case "usabQty":
			$con->dbquery("update sw_details set qty = '".$con->formatDigit($_POST['val'])."' where line_id = '$_POST[lid]' and branch = '$_SESSION[branchid]';");
		break;
		case "check4print":
			list($a) = $con->getArray("select count(*) from sw_header where sw_no = '$_POST[sw_no]' and branch = '$_SESSION[branchid]';");
			list($b) = $con->getArray("select count(*) from sw_details where sw_no = '$_POST[sw_no]' and branch = '$_SESSION[branchid]';");
			
			if($a == 0 && $b > 0) { echo "head"; }
			if($b == 0 && $a > 0) { echo "det"; }
			if($a == 0 && $b == 0) { echo "both"; }
			if($a > 0 && $b > 0) { echo "noerror"; }
		break;
		case "finalizeSW":
			$con->dbquery("update sw_header set status = 'Finalized', updated_by = '$_SESSION[userid]', updated_on = now() where sw_no ='$_POST[sw_no]' and branch = '$_SESSION[branchid]';");
			$iquery = $con->dbquery("SELECT a.branch, a.sw_no AS doc_no, a.sw_date AS doc_date, withdrawn_by AS customer, b.item_code, b.unit, b.qty, b.lot_no, b.expiry FROM sw_header a INNER JOIN sw_details b ON a.sw_no = b.sw_no AND a.branch = b.branch WHERE a.sw_no = '$_POST[sw_no]' AND a.branch = '$_SESSION[branchid]';");
			while($ibook = $iquery->fetch_array()) {
				$con->dbquery("INSERT IGNORE INTO ibook (doc_no,doc_date,doc_type,doc_branch,cname,item_code,uom,pullouts,lot_no,expiry,posted_by,posted_on) VALUES ('$ibook[doc_no]','$ibook[doc_date]','SW','$ibook[branch]','".$con->escapeString($ibook['customer'])."','$ibook[item_code]','$ibook[unit]','$ibook[qty]','$ibook[lot_no]','$ibook[expiry]','$_SESSION[userid]',now());");
			}
		break;
		case "reopenSW":
			$con->dbquery("update ignore sw_header set `status` = 'Active', updated_by = '$_SESSION[userid]', updated_on = now() where sw_no = '$_POST[sw_no]' and branch = '$_SESSION[branchid]';");
			$con->dbquery("delete from ibook where doc_no = '$_POST[sw_no]' and doc_branch = '$_SESSION[branchid]' and doc_type = 'SW';");
		break;
		case "cancel":
			$con->dbquery("update sw_header set status = 'Cancelled', updated_by = '$_SESSION[userid]', updated_on = now() where sw_no = '$_POST[sw_no]' and branch = '$_SESSION[branchid]';");
		break;

		case "retrieve":
			$data = array();
	
			$query = $con->dbquery("SELECT line_id as id, description, item_code, unit, lot_no, if(expiry!='0000-00-00',date_format(expiry,'%m/%d/%Y'),'') as expiry, qty FROM sw_details where trace_no = '$_REQUEST[trace_no]';");
			while($row = $query->fetch_array()) {
				//$row['description'] = $row['description'];
				$data[] = array_map('utf8_encode',$row);
			}
			
			$results = ["sEcho" => 1,"iTotalRecords" => count($data),"iTotalDisplayRecords" => count($data),"aaData" => $data];
			echo json_encode($results);	
		break;
		
	}

?>