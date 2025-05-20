<?php

	require_once "_generics.php";
	class imod extends _init {
		
		function setSRRClickers($status,$lock,$srr_no,$uid,$dS,$urights) {
			if($lock != 'Y') {
				switch($status) {
					case "Finalized":
						list($posted_by,$posted_on) = parent::getArray("select fullname as name, date_format(updated_on,'%m/%d/%Y %p') as date_posted from srr_header a left join user_info b on a.updated_by = b.emp_id where a.srr_no='$srr_no';");
						if($urights == "admin") {
							$headerControls = "<a href=\"#\" class=\"topClickers\" onclick=\"javascript: reopenSRR('$srr_no');\"><img src='images/icons/edit.png' align=absmiddle width=16 height=16 />&nbsp;Set this Document to Active Status</a>&nbsp;";
						}
						$headerControls = $headerControls . "&nbsp;<a href=\"#\" class=\"topClickers\" onClick=\"javascript: parent.printSRR('$srr_no','$uid','N');\"><img src=\"images/icons/print.png\" width=16 height=16 border=0 align=\"absmiddle\">&nbsp;Print Stocks Receiving Receipt</a>&nbsp;";
					break;
					case "Cancelled":
						if($urights == "admin") {
							$headerControls = $headerControls . "&nbsp;<a href=\"#\" class=\"topClickers\" onclick=\"javascript:reusePO('$srr_no');\" style=\"padding: 5px;\"><img src=\"images/icons/refresh.png\" width=16 height=16 border=0 align=\"absmiddle\">&nbsp;Recycle this Document</a>&nbsp;";	
						}
					break;
					case "Active": default:
						$headerControls = "<a href=\"#\" class=\"topClickers\" onClick=\"javascript:finalizeSRR('$srr_no','$_SESSION[userid]');\"><img src=\"images/icons/ok.png\" width=16 height=16 border=0 align=\"absmiddle\">&nbsp;Finalize Stocks Receiving Receipt</a>&nbsp;&nbsp;<a href=\"#\" class=\"topClickers\" onclick=\"javascript:saveSRRHeader();\"><img src=\"images/save.png\" width=16 height=16 border=0 align=\"absmiddle\">&nbsp;Save Changes</a>&nbsp;";
						if($urights == "admin" && $dS != 1) {
							$headerControls = $headerControls . "&nbsp;<a href=\"#\" class=\"topClickers\" onclick=\"javascript:cancelSRR('$srr_no');\"><img src=\"images/icons/cancel.png\" width=16 height=16 border=0 align=\"absmiddle\">&nbsp;Cancel this Document</a>";
						}
					break;
				}
			} else {
				$headerControls = $headerControls . "&nbsp;<a href=\"#\" class=\"topClickers\" onClick=\"javascript: parent.printSRR('$srr_no','$uid','Y');\"><img src=\"images/icons/print.png\" width=16 height=16 border=0 align=\"absmiddle\">&nbsp;Re-Print Stocks Receiving Receipt</a>";
			}
			echo $headerControls;
		}
		
		function setSRRNavs($srr_no) {
			list($fwd) = parent::getArray("select srr_no from srr_header where srr_no > $srr_no and branch = '$_SESSION[branchid]' limit 1;");
			list($prev) = parent::getArray("select srr_no from srr_header where srr_no < $srr_no and branch = '$_SESSION[branchid]' order by srr_no desc limit 1;");
			list($last) = parent::getArray("select srr_no from srr_header where branch = '$_SESSION[branchid]' order by srr_no desc limit 1;");
			list($first) = parent::getArray("select srr_no from srr_header where branch = '$_SESSION[branchid]' order by srr_no asc limit 1;");
			if($prev)
				$nav = $nav . "<a href=# onclick=\"parent.viewSRR('$prev');\"><img src='images/resultset_previous.png'  title='Previous Record' /></a>";
			if($fwd) 
				$nav = $nav . "<a href=# onclick=\"parent.viewSRR('$fwd');\"><img src='images/resultset_next.png' 'title='Next Record' /></a>";
			echo "<a href=# onclick=\"parent.viewSRR('$first');\"><img src='images/resultset_first.png' title='First Record' /><a>" . $nav . "<a href=# onclick=\"parent.viewSRR('$last');\"><img src='images/resultset_last.png' title='Last Record' /></a>";
		}
		
		function SRRDETAILS($srr_no,$status,$lock) {
			$i = 1;
			$details = parent::dbquery("select line_id, srr_no, item_code, description, qty, unit from srr_details where srr_no='$srr_no' and branch = '$_SESSION[branchid]';");
			echo '<table width=100% cellspacing=0 cellpadding=0 onMouseOut="javascript:highlightTableRowVersionA(0);">';
			while(list($line_id, $srr_no, $item_code, $description, $qty, $unit) = $details->fetch_array()) {
			   echo '<tr bgcolor="'.parent::initBackground($i).'" onmouseover="highlightTableRowVersionA(this, \'#3399ff\');" id="obj_'.$lined_id.'" onclick="selectLine(this,\''.$line_id.'\');">
						<td align=left class="grid" width="15%">'.$item_code.'</td>
						<td align=left class="grid" width="41%">'.strtoupper($description).'</td>
						<td align=center class="grid" width="15%">'.parent::identStockCode($item_code).'</td>
						<td align=center class="grid" width="16%">'.parent::identUnit($unit).'</td>
						<td align=center class="grid">';
						if(($status == 'Active' || $status == '') && $lock != 'Y') {
							echo '<input type="text" id="qty['.$line_id.']" class="gridInput" style="width: 70%; text-align: center;" value="'.number_format($qty,2).'" onchange="updateQty(this.value,\''.$srr_no.'\',\''.$line_id.'\',\''.$qty.'\');"></td>';
						} else { echo number_format($qty,2); }
						echo '</td>
					 </tr>';	
				$i++;				
			}
			
			
			if($i < 8) { for($i; $i <= 7; $i++) {
				echo '<tr bgcolor='.parent::initBackground($i).'>
							<td align=left class="grid" width="100%" colspan=5>&nbsp;</td>
					</tr>';
				}
			}
			echo '</table>';
		}
	}
	

?>