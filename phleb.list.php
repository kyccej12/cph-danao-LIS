<?php
	session_start();
	include('handlers/_generics.php');
	ini_set("max_execution_time",0);
	ini_set("memory_limit",-1);
	$con = new _init();

	$con->dbquery("SET GLOBAL sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));");

?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<title>Medgruppe Polyclinics & Diagnostic Center, Inc.</title>
	<link href="ui-assets/themes/smoothness/jquery-ui.css" rel="stylesheet" type="text/css" />
	<link href="style/style.css" rel="stylesheet" type="text/css" />
	<link rel="stylesheet" type="text/css" href="ui-assets/datatables/css/jquery.dataTables.css">
	<link rel="stylesheet" type="text/css" href="ui-assets/keytable/css/keyTable.jqueryui.css">
	<script type="text/javascript" charset="utf8" src="ui-assets/jquery/jquery-1.12.3.js"></script>
	<script type="text/javascript" charset="utf8" src="ui-assets/themes/smoothness/jquery-ui.js"></script>
	<script type="text/javascript" charset="utf8" src="ui-assets/datatables/js/jquery.dataTables.js"></script>
	<script type="text/javascript" charset="utf8" src="ui-assets/datatables/js/dataTables.jqueryui.js"></script>
	<script type="text/javascript" charset="utf8" src="ui-assets/datatables/js/dataTables.select.js"></script>
	<script type="text/javascript" charset="utf8" src="ui-assets/datatables/js/page.jumpToData().js"></script>
	<script type="text/javascript" charset="utf8" src="ui-assets/keytable/js/dataTables.keyTable.min.js"></script>
<script>

	$(document).ready(function() {
		var myTable = $('#itemlist').DataTable({
			"scrollY":  "460",
			"searching": false,
			"bSort": false,
			"paging": false,
			"info": false,
			"scroller": true,
			"aoColumnDefs": [
			    { "className": "dt-body-center", "targets": [1,2,4,5,6,7,10] },
				{ "targets": [0,7,12,13,14], "visible": false }
            ],
			"createdRow": function( row, data, dataIndex){
                if( data[14] ==  'STAT'){
                    $(row).addClass('redClass');
                }
            }
		});
	});

	function refreshList() {
		$('#itemlist').DataTable().ajax.url("data/phleblist.php").load();
	}

	function assignBarcode() {

		$.post("src/sjerp.php", { mod: "check4checked", sid: Math.random() }, function(result) {
			var i = parseInt(result);

			if(i > 0) {

				$("#mainLoading").css("z-index","999");
				$("#mainLoading").show();

				$.ajax({
				type: "POST",
				url: "barcoder.php",
				cache: false,
				success: function(data) {
					parent.printBatchBarcode(data);
					setTimeout(function() {
						parent.showLabCollection();
					}, 3000);
				}
			});

			} else {
				parent.sendErrorMessage("It appears you have yet to select any doctor's request to process...");
			}


		});

	}

	function toggleMe(id,val) {
		var vox = document.getElementById(id);
		if(vox.checked == true) { var isToggle = 'Y'; } else { var isToggle = 'N'; }

		$.post("src/sjerp.php", { mod: "requestChecked", val: val, toggle: isToggle, sid: Math.random() });
	}

	function searchRecord() {
		$("#mainLoading").css("z-index","999");
		$("#mainLoading").show();

		var stxt = $("#stxt").val();
		document.frmSearch.searchtext.value = stxt;
		document.frmSearch.submit();
	}

	function jumpPage(page,stxt) {

		$("#mainLoading").css("z-index","999");
		$("#mainLoading").show();

		document.frmPaging.page.value = page;
		document.frmPaging.searchtext.value = stxt;
		document.frmPaging.submit();
	}
</script>
<style>
	.dataTables_wrapper {
		display: inline-block;
	    font-size: 11px;
		width: 100%;
	}
	
	table.dataTable tr.odd { background-color: #f5f5f5;  }
	table.dataTable tr.even { background-color: white; }
	.dataTables_filter input { width: 250px; }
	.redClass { background-color: red !important; color: white; }

</style>
</head>
<body bgcolor="#ffffff" leftmargin="0" bottommargin="0" rightmargin="0" topmargin="0">
<div id = "main">
	<table width="100%" cellspacing="0" cellpadding="0" style="padding-left: 5px; margin-bottom: 2px;">
		<tr>
			<td>
				<!--button class="ui-button ui-widget ui-corner-all" onClick="assignBarcode();">
					<img src="images/icons/syringe.png" width=12 height=12 align=absmiddle /> Collect Sample or Specimen
				</button-->
				<button class="ui-button ui-widget ui-corner-all" onClick="assignBarcode();">
					<img src="images/icons/barcode-scanner.png" width=12 height=12 align=absmiddle /> Assign Barcode For the Selected Request(s)
				</button>
				<button class="ui-button ui-widget ui-corner-all" onClick="parent.showLabCollection();">
					<span class="ui-icon ui-icon-refresh"></span> Reload List
				</button>
			</td>
			<td align=right>
					<input name="stxt" id="stxt" type="text" class="gridInput" style="width: 240px; height: 24px;" value="<?php echo $_REQUEST['searchtext']; ?>" placeholder="Search Record">
					<button class="ui-button ui-widget ui-corner-all" onClick="javascript: searchRecord();">
						<span class="ui-icon ui-icon-search"></span> Search Record
					</button>
				</td>
			</tr>
		</tr>
	</table>
	<table id="itemlist" class="cell-border" style="font-size:11px;">
		<thead>
			<tr>
				<th></th>
				<th width=10%>DATE/TIME</th>
				<th width=7%>HMR #</th>
				<th width=15%>PATIENT NAME</th>
				<th width=7%>GENDER</th>
				<th width=7%>BIRTHDATE</th>
				<th width=5%>AGE</th>
				<th width=5%>CODE</th>
				<th>PROCEDURE</th>
				<th width=15%>REQUESTING DOCTOR</th>
				<th width=10%>DEPT / WARD / ROOM#</th>
				<th width=10%>CURRENT STATUS</th>
				<th></th>
				<th></th>
				<th></th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			<?php
				$rowsPerPage = 15;
				if(isset($_REQUEST['page'])) { if($_REQUEST['page'] <= 0) { $pageNum = 1; } else { $pageNum = $_REQUEST['page']; }} else { $pageNum = 1; }
				$offset = ($pageNum - 1) * $rowsPerPage;
				$searchString = '';

				if($_REQUEST['searchtext'] && $_REQUEST['searchtext'] != '') {
					$searchString .= " and (a.docointkey like '$_REQUEST[searchtext]' || a.hpercode = '$_REQUEST[searchtext]' || c.patlast like '%$_REQUEST[searchtext]%' || c.patfirst like '%$_REQUEST[searchtext]%' || a.donotes like '%$_REQUEST[searchtext]%' || a.licno like '%$_REQUEST[searchtext]%' || b.procdesc like '%$_REQUEST[searchtext]%') ";
				}
				$query = "SELECT docointkey, a.enccode, DATE_FORMAT(dodate,'%m/%d/%Y %h:%i %p') AS orderdate, DATE_FORMAT(dodate,'%Y-%m-%d') AS xorderdate, a.hpercode, c.patlast, c.patfirst, c.patmiddle, DATE_FORMAT(c.patbdate,'%m/%d/%Y') AS bday, DATE_FORMAT(c.patbdate,'%Y-%m-%d') AS xbday, IF(c.patsex='F','FEMALE','MALE') AS sex, c.patsex, a.proccode, b.procdesc, a.donotes AS remarks, a.licno, a.estatus, entby, a.dotime, a.dopriority, a.donotes FROM hospital_dbo.hdocord a LEFT JOIN hospital_dbo.hprocm b ON a.proccode = b.proccode LEFT JOIN hospital_dbo.hperson c ON a.hpercode = c.hpercode WHERE a.ordcon != 'CANOR' and dodate > '2024-11-01' AND b.proccode LIKE '%LABOR%' $searchString";
				//echo $query;
				/* Paging Section */
				$numrows = $con->getArray("SELECT count(*) FROM hospital_dbo.hdocord a LEFT JOIN hospital_dbo.hprocm b ON a.proccode = b.proccode LEFT JOIN hospital_dbo.hperson c ON a.hpercode = c.hpercode WHERE dodate > '2024-11-01' $searchString;");
				$maxPage = ceil($numrows[0]/$rowsPerPage);
				$_i = $con->dbquery("$query order by dodate desc, docointkey desc LIMIT $offset,$rowsPerPage");
				$z = 0;
				while($row = $_i->fetch_array()) {

					$refKey = split('-',$row['docointkey']);
					$age = $con->calculateAge($row['xorderdate'],$row['xbday']);
				
					$hcord = substr($row['enccode'],7,15);
					list($provider) =  $con->getArray("SELECT CONCAT(empprefix,'. ',firstname, ' ',middlename, ' ', lastname) FROM hospital_dbo.hprovider a LEFT JOIN hospital_dbo.hpersonal b ON a.employeeid = b.employeeid WHERE a.licno = '$row[licno]';");

					$room = $con->identRoom($row['enccode']);
					if($room == '') { 
						list($room) = $con->getArray("select toecode from hospital_dbo.henctr where enccode = '$row[enccode]';");
					}

					/* AUTOMATIC AS STAT FOR TROP I & ABG*/
					if($row['proccode'] == 'LABOR00262' || $row['proccode'] == 'LABOR1042') {
						$dopriority = "STAT";
					} else {
						$dopriority = trim($row['dopriority']);
					}


					/* Check if Barcode already assigned */
					list($isCollected) = $con->getArray("select count(*) from lab_samples where enccode = '$row[enccode]' and `code` = '$row[proccode]' and dotime = '$row[dotime]';");
					if($isCollected > 0) {

						/* Flush Data from TMP_BARCODER */
						$con->dbquery("delete from tmp_barcoder where enccode = '$row[enccode]' and `proccode` = '$row[proccode]' and dotime = '$row[dotime]';");

						if($dopriority == 'STAT') {
							$status = "<font color=white>Barcode Provided</font>";
						} else {
							$status = "<font color=green>Barcode Provided</font>";
						}

						
						$cbox = '';
					} else { 
						$status = "For Processing"; 
						list($isChecked,$uid) = $con->getArray("select count(*),uid from tmp_barcoder where enccode = '$row[enccode]' and proccode = '$row[proccode]' and dotime = '$row[dotime]' GROUP BY enccode, proccode, dotime;");
						if($isChecked > 0) { 
							$checked = "checked";
							if($uid != $_SESSION['userid']) {
								$disabled = "disabled";
							} else { $disabled = ''; }
							
						} else { 
							$checked = ""; 
						}
						$val = "$row[enccode]^$row[dotime]^$row[proccode]^$row[hpercode]^$dopriority^$provider^$row[patsex]^$room";
						$cbox = "<input type=\"checkbox\" name=\"myvox_$z\" id=\"myvox_$z\" value=\"$val\" $checked $disabled onclick=\"toggleMe(this.id,this.value);\">";
					}
					
					echo "<tr>
							<td>$row[enccode]</td>
							<td>$row[orderdate]</td>
							<td>$row[hpercode]</td>
							<td>$row[patlast], $row[patfirst] , $row[patmiddle]</td>
							<td>$row[sex]</td>
							<td>$row[bday]</td>
							<td>$age</td>
							<td>$row[proccode]</td>
							<td>$row[procdesc]</td>
							<td>$provider</td>
							<td>$room</td>
							<td>$status</td>
							<td>$row[estatus]</td>
							<td>$row[dotime]</td>
							<td>$dopriority</td>
							<td align=center>$cbox</td>
					</tr>"; $z++;

				}
			?>
		</tbdoy>	
	</table>
	<table bgcolor="#e9e9e9" width=100% cellpadding=5 cellspacing=0>
		<tr>
			<?php if($numrows[0] > 0) { ?>
			<td>
				
			</td>
			<td align=right style="padding-right: 10px;"><?php if ($pageNum > 1) { ?><a href="javascript:jumpPage('<?php echo ($pageNum - 1); ?>','<?php echo $_REQUEST['searchtext']; ?>')" class="a_link" title="Previous Page"><span style="font-size: 18px;">&laquo;</span></a>&nbsp;<?php } ?>
				<span style="font-size: 11px;">Page <?php echo $pageNum; ?> of <?php echo $maxPage; ?></span>&nbsp;
					<?php if($pageNum != $maxPage) { ?><a href="javascript:jumpPage('<?php echo ($pageNum + 1); ?>','<?php echo $_REQUEST['searchtext']; ?>')" class="a_link" title="Next Page"><span style="font-size: 18px;">&raquo;</span></a><?php } ?>&nbsp;&nbsp;
						<?php if($maxPage > 1) { ?>
						<span style="font-size: 11px;">Jump To: </span>
							<select id="jpage" name="jpage" style="width: 40px; padding: 0px;" onchange="javascript:jumpPage(this.value,'<?php echo $_REQUEST['searchtext']; ?>');">
							<?php
									for ($x = 1; $x <= $maxPage; $x++) {
										echo "<option value='$x' ";
										if($pageNum == $x) { echo "selected"; }
										echo ">$x</option>";
									}
								?>
								</select>
					<?php } ?>
			</td> 
			<?php } ?>
		</tr>
	</table>
</div>
<div id="sampleFormDetails" style="display: none;">
	<form name="frmSample" id="frmSample">
		
        <input type="hidden" name = "phleb_primecode" id = "phleb_primecode">
		<input type="hidden" name = "phleb_code" id = "phleb_code">
		<input type="hidden" name = "phleb_description" id = "phleb_description">
		<input type="hidden" name = "phleb_spectype" id = "phleb_spectype">
		<input type="hidden" name = "phleb_serialno" id = "phleb_serialno">
		<input type="hidden" name = "phleb_dotime" id = "phleb_dotime">
		<table width=100% cellpadding=0 cellspacing=0 style="border: 1px solid #cdcdcd; padding: 10px;">
			<tr>
				<td align="right" width="30%"  class="bareBold" style="padding-right: 15px;">Encounter Ref #&nbsp;:</td>
				<td align=left colspan=2>
					<input class="gridInput" style="width:100%;" type=text name="phleb_sodetails" id="phleb_sodetails" readonly>
				</td>				
			</tr>
			<tr><td height=3></td></tr>
			<tr>
				<td align="right" width="30%" class="bareBold" style="padding-right: 15px;">Patient Details&nbsp;:</td>
				<td align=left colspan=2>
					<input type="text" class="gridInput" style="width:100%;" name="phleb_pname" id="phleb_pname" readonly>
				</td>				
			</tr>
			<tr><td height=3></td></tr>
			<tr>
				<td align="right" width="30%" class="bareBold" style="padding-right: 15px;">Requesting Physician&nbsp;:</td>
				<td align=left colspan=2>
					<input type="text" class="gridInput" style="width:100%;" name="phleb_physician" id="phleb_physician">
				</td>					
			</tr>
			<tr><td height=3></td></tr>
			<tr>
				<td align="right" width="30%" class="bareBold" style="padding-right: 15px;">Requested Procedure&nbsp;:</td>
				<td align=left width=40%>
					<input type="text" class="gridInput" style="width:95%;" name="phleb_procedure" id="phleb_procedure" readonly>
				</td>			
				<td rowspan=20 id="sampleField" title="Other lab requests from customer that may use the same sample." valign=top>


				</td>		
			</tr>
			<tr><td height=3></td></tr>
			<tr>
				<td align="right" width="30%" class="bareBold" style="padding-right: 15px;">Sample Details&nbsp;:</td>
				<td align=left width=30%>
					<input type="text" class="gridInput" style="width:95%;" name="phleb_sampledetails" id="phleb_sampledetails">
				</td>	
					
			</tr>
			<tr><td height=3></td></tr>
			<tr>
				<td align="right" width="30%" class="bareBold" style="padding-right: 15px;">Preferred Container&nbsp;:</td>
				<td align=left>
					<select class="gridInput" style="width:95%;" name="phleb_containertype" id="phleb_containertype">
						<?php
							$cquery = $con->dbquery("select id,type from options_containers;");
							while(list($cid,$ctype) = $cquery->fetch_array()) {
								echo "<option value='$cid'>$ctype</option>";
							}
						?>
					</select>
				</td>				
			</tr>
			<tr><td height=3></td></tr>
			<tr>
				<td align="right" width="30%" class="bareBold" style="padding-right: 15px;">Date & Time of Collection&nbsp;:</td>
				<td align=left>
					<input type="text" class="gridInput" style="width:95%;" name="phleb_date" id="phleb_date" value="<?php echo date('m/d/Y H:i'); ?>">
				</td>				
			</tr>
			<tr><td height=3></td></tr>
			<tr>
				<td align="right" width="30%" class="bareBold" style="padding-right: 15px;">Test Kit Vendor&nbsp;:</td>
				<td align=left>
					<input type="text" class="gridInput" style="width:95%;" name="phleb_testkit" id="phleb_testkit">
				</td>				
			</tr>
			<tr><td height=3></td></tr>
			<tr>
				<td align="right" width="30%" class="bareBold" style="padding-right: 15px;">Lot # (If Applicable)&nbsp;:</td>
				<td align=left>
					<input type="text" class="gridInput" style="width:95%;" name="phleb_testkit_lotno" id="phleb_testkit_lotno">
				</td>				
			</tr>
			<tr><td height=3></td></tr>
			<tr>
				<td align="right" width="30%" class="bareBold" style="padding-right: 15px;">Expiry Date&nbsp;:</td>
				<td align=left>
					<input type="text" class="gridInput" style="width:95%;" name="phleb_testkit_expiry" id="phleb_testkit_expiry">
				</td>				
			</tr>
			<tr><td height=3></td></tr>
			<tr>
				<td align="right" width="30%" class="bareBold" style="padding-right: 15px;">Ward/Room #&nbsp;:</td>
				<td align=left>
					<input type="text" class="gridInput" style="width:95%;" name="phleb_location" id="phleb_location" readonly>
				</td>				
			</tr>
			<tr><td height=3></td></tr>
			<tr>
				<td align="right" width="30%" class="bareBold" style="padding-right: 15px;">Extracted By&nbsp;:</td>
				<td align=left>
					<input type="text" class="inputSearch2" style="width:95%;padding-left:22px;" name="phleb_by" id="phleb_by">
				</td>				
			</tr>
			<tr><td height=3></td></tr>
			<tr>
				<td align="right" width="30%" class="bareBold" style="padding-right: 15px;" valign=top>Remarks/Memo&nbsp;:</td>
				<td align=left colspan=2>
					<textarea name="phleb_remarks" id="phleb_remarks" style="width:100%;" rows=3></textarea>
				</td>				
			</tr>
		</table>
	</form>
</div>
<div id="mainLoading" style="display:none; width:100%;height:100%;position:absolute;top:0;margin:auto;"> 
	<div style="background-color:white;width:10%;height:20%;;margin:auto;position:relative;top:100;">
		<img style="display:block;margin-left:auto;margin-right:auto;" src="images/ajax-loader.gif" width=128 height=128 align=absmiddle /> 
	</div>
	<div id="mainLoading2" style="background-color:white;width:100%;height:100%;position:absolute;top:0;margin:auto;opacity:0.5;"> </div>
</div>
<form name="frmSearch" id="frmSearch" action="phleb.list.php" method="POST">
	<input type="hidden" name="isSearch" id="isSearch" value="Y">
	<input type="hidden" name="searchtext" id="searchtext" value="<?php echo $_REQUEST['searchtext']; ?>">
</form>
<form name="frmPaging" id="frmPaging" action="phleb.list.php" method="POST">
	<input type="hidden" name="page" id="page" value="<?php echo $pageNum; ?>">
	<input type="hidden" name="searchtext" id="searchtext" value="<?php echo $_REQUEST['searchtext']; ?>">	
</form>
</body>
</html>