<?php
	session_start();
	include("handlers/initDB.php");
	$con = new myDB;

	unset($my_arr);
	unset($my_arr_row);

	$term = trim(strip_tags($_GET['term'])); 
	$r = $con->dbquery("select concat('(',item_code,') ',brand,' ',description,' [UOM = ',unit,' ^ Current Available = ') as item, item_code,description,unit_cost,unit from products_master where (locate('$term',description) > 0 or locate('$term',item_code)) and file_status = 'Active' limit 30;");
	$my_arr = array();
	$my_arr_row = array();

	if($r) {
		while($row = $r->fetch_array()) {

			$pi = $con->getArray("select ifnull(sum(b.qty),0) from phy_header a left join phy_details b on a.doc_no = b.doc_no and a.branch = b.branch where a.branch = '$_SESSION[branchid]' and b.item_code = '$row[item_code]' and a.status = 'Finalized' and a.posting_date = '2022-02-09' GROUP BY b.item_code;");				
			$cur = $con->getArray("select sum(purchases+inbound-outbound-pullouts-sold) as currentbalance from ibook where item_code = '$row[item_code]' and doc_date between '2022-02-09' and '".date('Y-m-d')."' and doc_branch = '$_SESSION[branchid]';");
		    $qoh = ROUND($pi[0]+$cur['currentbalance'],2);

			$my_arr_row['item_code'] = $row['item_code'];
			$my_arr_row['value'] = html_entity_decode($row['description']);
			$my_arr_row['unit_price'] = $row['unit_cost'];
			$my_arr_row['unit'] = $row['unit'];
			$my_arr_row['label'] = html_entity_decode($row['item']) . $qoh . ']';

			array_push($my_arr,$my_arr_row);
		}
	}

	echo json_encode($my_arr);

?>