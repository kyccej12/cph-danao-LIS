<?php
	session_start();
	include("handlers/_generics.php");
	$o = new _init;

	if(isset($_GET['mod']) && $_GET['mod'] != '') {
		$mod = $_GET['mod'];
	} else { $mod = '1'; }

	function getMod($def,$mod) {
		if($def == $mod) { return "class=\"float2\""; }
	}
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title></title>
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
			"keys": true,
			"scrollY":  "290px",
			"select":	'single',
			"pagingType": "full_numbers",
			"pageLength": 50,
			"bProcessing": true,
			"responsive": true,
			"sAjaxSource": "data/resultlist.php?mod=" + <?php echo $mod; ?> + "&sid=" + Math.random(),
			"scroller": true,
			"pageLength": 50,
			"order": [[6, "desc"]],
			<?php if($_GET['code'] != 'undefined') { ?>
				"initComplete": function() {
					this.api().page.jumpToData("<?php echo $_GET['code']; ?>",1);
				},
			<?php } ?>
			"aoColumns": [
			  { mData: 'id' } ,
			  { mData: 'enccode' } ,
			  { mData: 'hmrno' },
			  { mData: 'orderdate' },
			  { mData: 'code' },
			  { mData: 'procedure' },
			  { mData: 'serialno' },
			  { mData: 'patientname' },
              { mData: 'tstamp' },
			  { mData: 'room' },
			  { mData: 'physician' },
			  { mData: 'primecarecode' },
			  { mData: 'is_consolidated' },
			  { mData: 'released' },
			  { mData: 'dotime' },
			  { mData: 'printed_on' }

			],
			"aoColumnDefs": [
			    { "className": "dt-body-center", "targets": [2,3,4,6,8,13,15]},
				{ "className": "dt-body-left", "targets": [5,7,9,10]},
			    { "targets": [0,1,4,11,12,14], "visible": false }
            ]
		});
	});
	
	function refreshList() {
		$('#itemlist').DataTable().ajax.url("data/resultlist.php").load();
	}

	function releaseResult() {
		var table = $("#itemlist").DataTable();		
		var lid; var stat;
	   	$.each(table.rows('.selected').data(), function() {
		    lid = this["id"];
			code = this['primecarecode'];
			enccode = this['enccode'];
			isRelease = this['released'];
	   	});

		if(lid) {

			if(isRelease == 'Y') {
				parent.sendErrorMessage("It appears that the selected record was already released to Patient or its authorized representative.");
			} else {
				var irelease = $("#releasing").dialog({
					title: "Process Result for Release",
					width: 480,
					resizable: false,
					modal: true,
					buttons: [
						{
							
														
							icons: { primary: "ui-icon-pencil" },
							text: "Release Selected Result Only",
							click: function() { 
								$.post("src/sjerp.php", { mod: "releaseResult", id: lid, code: code, mode: $("#release_mode").val(), date: $("#release_date").val(), to: $("#release_to").val(), remarks: $("#release_remarks").val(), sid: Math.random() }, function() {
									alert("Result successfully released to patient!");
									irelease.dialog("close"); 
									$("#frmRelease").trigger("reset");
									disMessage.dialog("close");
									refreshList();
								});
							}
						},
						{
							text: "Close",
							icons: { primary: "ui-icon-closethick" },
							click: function() {
								$(this).dialog("close");
							}
						}
					]
				});
			}

		} else {
			parent.sendErrorMessage("Please select result to release...")
		}

	}

	function printResult() {
		var table = $("#itemlist").DataTable();		
	   	$.each(table.rows('.selected').data(), function() {
		    enccode = this["enccode"];
			code = this['primecarecode'];
			serialno = this['serialno'];
			is_consolidated = this['is_consolidated'];
			proccode = this['code'];
			dotime = this['dotime'];
			printed_on = this['printed_on'];

	   	});


		if(enccode) {

			if(printed_on == '') {
				$.post("src/sjerp.php", { mod: "printresult", enccode: enccode, serialno: serialno, printed_on: printed_on, sid: Math.random() }, function(res) {
				("#otherTests").html(res);
				},"html");

			}

		   if(is_consolidated == 'Y') {

				$.post("src/sjerp.php", { mod: "retrieveSameSampleForPrint", proccode: proccode, enccode: enccode, serialno: serialno, dotime: dotime, sid: Math.random() }, function(res) {
					$("#otherTests").html(res);
				},"html");



				var dis = $("#printConsolidation").dialog({ 
					title: "System Message",
					width: "480",
					modal: true,
					resizeable: false,
					buttons: [
						{
							icons: { primary: "ui-icon-print" },
							text: "Print Result",
							click: function() { 
								var dataString = $("#otherTests").serialize();
								window.open("print/result.bloodchem.php?enccode="+enccode+"&serialno="+serialno+"&sid="+Math.random()+"&"+dataString+"","Inventory Stockcard","location=1,status=1,scrollbars=1,width=640,height=720");
								dis.dialog('close');
							
							}
						}
					]
				});
		   } else {
				parent.printResult(code,enccode,serialno);
		   }
			
		} else {
			parent.sendErrorMessage("Please select Result to Print!")
		}

	}

	function unpublishResult() {
		var table = $("#itemlist").DataTable();
		$.each(table.rows('.selected').data(), function() {
			enccode = this["enccode"];
			serialno = this['serialno'];
			proccode = this['code'];
			dotime = this['dotime'];
		});

		if(!enccode) {
			parent.sendErrorMessage("Please select a result to unpublish...");
		}else {
			if(confirm("Are you sure you want to unpublish this result?") == true) {
				$.post("src/sjerp.php", { mod: "resultUnpublishForm", enccode: enccode, serialno: serialno, sid: Math.random() }, function() {
					alert("Result Successfully Unpublish!");
					refreshList();
				});
			}
		}
	}

	function changeMod(mod) {
		document.changeModPage.mod.value = mod;
		document.changeModPage.submit();
	}

</script>
<style>
	.dataTables_wrapper {
		display: inline-block;
	    font-size: 11px;
		width: 100%; 
	}

	.dataTable thead .sorting_asc,
	.dataTable thead .sorting_desc,
	.dataTable thead .sorting {
		font-size: 11px !important;
		padding-left: 6px !important;
    	padding-right: 6px !important;
	}

	table.dataTable td {
		font-size: 11px;
	  	padding: 6px !important;
	}

	table.dataTable tr.dtrg-level-0 td {
		font-size: 11px;
		padding: 6px !important;
	}
	
	table.dataTable tr.odd { background-color: #f5f5f5;  }
	table.dataTable tr.even { background-color: white; }
	.dataTables_filter input { width: 250px; }
</style>
</head>
<body bgcolor="#ffffff" leftmargin="0" bottommargin="0" rightmargin="0" topmargin="0">
<div id = "main">

	<table width="100%" cellspacing="0" cellpadding="0">
		<tr>
			<td align=right valign=bottom style="padding-bottom: 1px;">
				<div id="custmenu" class="ddcolortabs">
					<ul class=float2>
						<li><a href="#" <?php echo getMod("1",$mod); ?> onclick="javascript: changeMod(1);"><span id="tbbalance1">STAT REQUESTS</span></a></li>
						<li><a href="#" <?php echo getMod("2",$mod); ?> onclick="javascript: changeMod(2);"><span id="tbbalance1">Hematology</span></a></li>
						<li><a href="#" <?php echo getMod("3",$mod); ?> onclick="javascript: changeMod(3);"><span id="tbbalance3">Chemistry</span></a></li>
						<li><a href="#" <?php echo getMod("4",$mod); ?> onclick="javascript: changeMod(4);"><span id="tbbalance3">Special Chemistry</span></a></li>
						<li><a href="#" <?php echo getMod("5",$mod); ?> onclick="javascript: changeMod(5);"><span id="tbbalance3">Clinical Microscopy</span></a></li>
						<li><a href="#" <?php echo getMod("6",$mod); ?> onclick="javascript: changeMod(6);"><span id="tbbalance3">Immunology & Serology</span></a></li>
						<li><a href="#" <?php echo getMod("7",$mod); ?> onclick="javascript: changeMod(7);"><span id="tbbalance3">Others</span></a></li>
					</ul>
				</div>
			</td>
		</tr>
	</table>
	<table id="itemlist" class="cell-border" style="font-size:11px;">
		<thead>
			<tr>
				<th></th>
				<th></th>
				<th width=5%>HMR #</th>
				<th width=8%>DATE</th>
				<th>CODE</th>
				<th width=15%>PROCEDURE</th>
				<th width=10%>SAMPLE NO.</th>
				<th>PATIENT DETAILS</th>
				<th width=12%>DATE/TIME PROCESSED</th>
				<th width=12%>DEPT/WARD/ROOM#</th>
				<th width=15%>PHYSICIAN</th>
				<th></th>
				<th></th>
				<th width=10%>RELEASED</th>
				<th></th>
				<th  width=5%>PRINTED ON</th>
			</tr>
		</thead>
	</table>
	<table width=100% style="margin-top: 5px;">
		<tr>
			<td width=100% align=left>
				<button class="ui-button ui-widget ui-corner-all" onClick="releaseResult();">
					<img src="images/icons/crr.png" width=16 height=16 align=absmiddle /> Process Result for Release
				</button>
				<button class="ui-button ui-widget ui-corner-all" onClick="printResult();">
					<span class="ui-icon ui-icon-print"></span> Print Result
				</button>
				<button type=button class="ui-button ui-widget ui-corner-all" name="unpublishResult" id="unpublishResult" onClick="unpublishResult();">
					<span class="ui-icon ui-icon-cancel"></span> Unpublish Result
				</button>
				<button class="ui-button ui-widget ui-corner-all" onClick="refreshList();">
					<span class="ui-icon ui-icon-refresh"></span> Reload List
				</button>
			</td>
		</tr>
	</table>

</div>
<div id="singleResult" style="display: none;"></div>
<div id="releasing" style="display: none;">
	<form name="frmRelease" id="frmRelease">
		<table width=100% callpaddin=0 cellspacing=3>
			<tr>
				<td width=35% class="spandix-l">Mode of Releasing :</td>
				<td>
					<select class=gridInput style="width: 80%;" name="release_mode" id="release_mode">
						<option value='PICKUP'>Pickup by Patient</option>
						<option value="EMAILED">Emailed to Patient</option>
						<option value="DELIVERED">Delivered to Patient</option>
						<option value="DOCTOR">Released to Attending Physician</option>
					</select>
				</td>
			</tr>
			<tr>
				<td width=35% class="spandix-l">Releasing Date :</td>
				<td><input type="text" class="gridInput" style="width: 80%;" id="release_date" name="release_date" value = "<?php echo date('m/d/Y'); ?>"></td>
			</tr>
		
			<tr>
				<td width=35% class="spandix-l">Released To :</td>
				<td><input type="text" class="gridInput" style="width: 80%;" id="release_to" name="release_to"></td>
			</tr>
			<tr>
				<td width=35% class="spandix-l" valign=top>Other Remarks :</td>
				<td><textarea style="width: 80%;" id="release_remarks" name="release_remarks" rows=3></textarea></td>
			</tr>
		</table>
	</form>
</div>
<div id="printConsolidation" name="printConsolidation" style="display: none;">
	<p style="margin-left: 20px; text-align: justify;" id="message">It appears that the selected result belongs to one consolidated result sheet. You may select from the given list w/c result you wish to print.</span></p><br/>
	<form name="otherTests" id="otherTests">

	</form>
</div>
<div id="systemMessage" title="System Message" style="display: none;">
	<p style="margin-left: 20px; text-align: justify;" id="message">It appears that other results for this patient are also due for release. Do you wish to tag it and release it in batch?</span></p>
</div>
<form name="changeModPage" id="changeModPage" action="results.list.php" method="GET" >
	<input type="hidden" name="mod" id="mod">
</form>
</body>
</html>