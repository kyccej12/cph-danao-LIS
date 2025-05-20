<?php
	session_start();
	include("handlers/_generics.php");
	$o = new _init;

	/* if(isset($_GET['mod']) && $_GET['mod'] != '') {
		$mod = $_GET['mod'];
	} else { $mod = '1'; }

	function getMod($def,$mod) {
		if($def == $mod) { return "class=\"float2\""; }
	} */
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title>Opon Medical Diagnostic Corporation</title>
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
			"scrollY":  "300px",
			"select":	'single',
			"pagingType": "full_numbers",
			"pageLength": 50,
			"bProcessing": true,
			"responsive": true,
			"sAjaxSource": "data/validationlist.php",
			"scroller": true,
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
			  { mData: 'dopriority' },

			],
			"aoColumnDefs": [
			    { "className": "dt-body-center", "targets": [2,3,4,6,8]},
				{ "className": "dt-body-left", "targets": [5,7,9,10]},
			    { "targets": [0,1,11,12,13], "visible": false }
            ]<?php if($mod != '1') { ?>,
			"createdRow": function( row, data, dataIndex){
                if(data.dopriority ==  'STAT'){
                    $(row).addClass('redClass');
                }
            }
			<?php } ?>
		});
	});
	
	function refreshList() {
		$('#itemlist').DataTable().ajax.url("data/validationlist.php").load();
	}

	function rejectSample() {

		var table = $("#itemlist").DataTable();		
		var lid; var stat;
	   	$.each(table.rows('.selected').data(), function() {
		    lid = this["id"]; stat = this["ostat"]; 
	   	});

		if(!lid) {
			parent.sendErrorMessage("Please select record from the given list!");
		} else {

			var msg ='';

			if(stat == '2') { msg = "Sample has already been marked as \"Rejected\"!"; }
			/* if(stat == '3') { msg = "Result is already available for this procedure."; } */
			if(stat == '4') { msg = "Result is already available for this procedure."; }

			if(msg != '') {
				parent.sendErrorMessage(msg);
			} else {

				$.post("src/sjerp.php", {
					mod: "retrieveSample",
					lid: lid,
					sid: Math.random() },
					function(data) {

						$("#phleb_pname").val(data[0]);
						$("#phleb_procedure").val(data[1]);
						$("#phleb_code").val(data[2]);
						$("#phleb_spectype").val(data[3]);
						$("#phleb_serialno").val(data[4]);
						$("#phleb_location").val(data[5]);
						$("#phleb_date").val(data[6]);
						$("#phleb_hr").val(data[7]);
						$("#phleb_min").val(data[8]);
						$("#phleb_by").val(data[9]);

						var dis = $("#sampleDetails").dialog({
							title: "Sample Rejection",
							width: 540,
							resizeable: false,
							modal: true,
							buttons: [
								{
									text: "Reject Sample",
									icons: { primary: "ui-icon-check" },
									click: function() {
								
										if(confirm("Are you sure you want to mark this sample as Rejected?") == true) {
										
											$.post("src/sjerp.php", { 
												mod: "rejectSample",
												lid: lid,
												reason: $("#phleb_remarks").val(),
												sid: Math.random() }, 
												function() {
													alert("Sample successfully marked as \"REJECTED\"!");
													dis.dialog("close");
													refreshList();
												}
											);
										}	
									}
										
								},
								{
									text: "Close",
									icons: { primary: "ui-icon-closethick" },
									click: function() { $(this).dialog("close"); }
								}
							]
						});
					},"json"
				);
			}
		}
	}

	function validateResult() {
		var table = $("#itemlist").DataTable();		
		var lid; var stat;
	   	$.each(table.rows('.selected').data(), function() {
		    lid = this["id"];
			code = this['primecarecode'];
			enccode = this['enccode'];
			serialno = this['serialno'];
			is_consolidated = this['is_consolidated'];
	   	});

		if(!lid) {
			parent.sendErrorMessage("- It appears you have not selected any orders from the given list yet...");
		} else {
			if(is_consolidated  == 'Y') {
				parent.validateResult(lid,'L999');
			} else {
				parent.validateResult(lid,code);
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
	.redClass { color: red !important; }
</style>
</head>
<body bgcolor="#ffffff" leftmargin="0" bottommargin="0" rightmargin="0" topmargin="0">
<div id = "main">

	<table width="100%" cellspacing="0" cellpadding="0">
		<tr>
			<td width=100% align=left style="padding-bottom: 2px;">
				<button class="ui-button ui-widget ui-corner-all" onClick="validateResult();">
					<img src="images/icons/crr.png" width=12 height=12 align=absmiddle /> Validate Selected Result
				</button>
				<button class="ui-button ui-widget ui-corner-all" onClick="parent.showValidation();">
					<span class="ui-icon ui-icon-refresh"></span> Reload List
				</button>
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
				<th width=8%>CODE</th>
				<th width=15%>PROCEDURE</th>
				<th width=7%>ID</th>
				<th>PATIENT DETAILS</th>
				<th width=15%>DATE/TIME PROCESSED</th>
				<th width=12%>DEPT / WARD / ROOM#</th>
				<th width=15%>REQUESTING PHYSICIAN</th>
				<th></th>
				<th></th>
				<th></th>
			</tr>
		</thead>
	</table>
</div>
<div id="singleResult" style="display: none;"></div>
<form name="changeModPage" id="changeModPage" action="validation.php" method="GET" >
	<input type="hidden" name="mod" id="mod">
</form>
</body>
</html>