<?php
	session_start();
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
<script type="text/javascript" charset="utf8" src="ui-assets/datatables/js/jquery.dataTables.js"></script>
<script type="text/javascript" charset="utf8" src="ui-assets/datatables/js/dataTables.jqueryui.js"></script>
<script type="text/javascript" charset="utf8" src="ui-assets/datatables/js/dataTables.select.js"></script>
<script type="text/javascript" charset="utf8" src="ui-assets/datatables/js/page.jumpToData().js"></script>
<script type="text/javascript" charset="utf8" src="ui-assets/keytable/js/dataTables.keyTable.min.js"></script>
<script>
	
	function showPrintMaster() {
		parent.showFilterDiv();
	}
	
	function retrieveGroups(type) {
		if(type != "") {
			if(type == 1 || type == 2 || type == 4) {
				$.post("src/sjerp.php", { mod: "getGroups", type: type, sid: Math.random() }, function(data) {
					$("#item_group").html(data);
					$("#item_code").val('');	
				},"html");
			} else { $("#item_group").html('<option value="">- All Groups -</option>'); }
		} else {
			$("#item_group").html('<option value="">- All Groups -</option>');
		}
	}
	
	function printMaster() {
		window.open("reports/itemmaster.php?category="+$("#item_mgroup").val()+"&group="+$("#item_group").val()+"&sid="+Math.random()+"","Item Master List","location=1,status=1,scrollbars=1,width=640,height=720");
	}
	
	function editRecord(){
		var table = $("#itemlist").DataTable();		
		var arr = [];
	   $.each(table.rows('.selected').data(), function() {
		   arr.push(this["id"]);
	   });
	  
		if(!arr[0] || arr[0] == "undefined") {
			parent.sendErrorMessage("Please select a record from the list, and once highlighted, click \"<b><i>Edit Selected Record</i></b>\" again...");
		} else {
			parent.showServiceInfo(arr[0]);	
		}
	}

	$(document).ready(function() {
		var myTable = $('#itemlist').DataTable({
			"keys": true,
			"scrollY":  "300px",
			"select":	'single',
			"pagingType": "full_numbers",
			"bProcessing": true,
			"responsive": true,
			"sAjaxSource": "data/servicelist.php",
			"scroller": true,
			"order": [[3,"asc"],[1,"asc"]],
			<?php if($_GET['code'] != 'undefined') { ?>
				"initComplete": function() {
					this.api().page.jumpToData("<?php echo $_GET['code']; ?>",1);
				},
			<?php } ?>
			"aoColumns": [
			  { mData: 'id' } ,
			  { mData: 'code' } ,
			  { mData: 'ihomis_code' } ,
			  { mData: 'description' },
			  { mData: 'category' },
			  { mData: 'subcategory' },
			  { mData: 'unit_price', render: $.fn.dataTable.render.number(',', '.', 2, '') },
			  { mData: 'sample_type' }, 
			  { mData: 'container_type' },
			  { mData: 'result_type' },
			  { mData: 'result_tat' },
			  { mData: 'wtest' },
			],
			"aoColumnDefs": [
			    { "className": "dt-body-center", "targets": [2,4,5,7,8]},
			    { "targets": [0,6,9,10,11], "visible": false }
            ]
		});

		$('#itemlist tbody').on('dblclick', 'tr', function () {
			var data = myTable.row(this).data();
			parent.showServiceInfo(data['id']);		
		});
	});
	
	function refreshList() {
		$('#itemlist').DataTable().ajax.url("data/servicelist.php").load();
	}

</script>
<style>
	.dataTables_wrapper {
		display: inline-block;
	    font-size: 11px; padding: 3px;
		width: 99%; 
	}
	
	table.dataTable tr.odd { background-color: #f5f5f5;  }
	table.dataTable tr.even { background-color: white; }
	.dataTables_filter input { width: 250px; }
</style>
</head>
<body bgcolor="#ffffff" leftmargin="0" bottommargin="0" rightmargin="0" topmargin="0">
 <table height="100%" width="100%" border="0" cellspacing="0" cellpadding="0" >
	<tr>
		<td style="padding:0px;" valign=top>
			<table width="100%" cellspacing="0" cellpadding="0" style="padding-left: 5px; margin-bottom: 2px;">
				<tr>
					<td>
						<button class="ui-button ui-widget ui-corner-all" onClick="parent.showServiceInfo('');">
							<span class="ui-icon ui-icon-plus"></span> Create New Service Offering
						</button>
						<button class="ui-button ui-widget ui-corner-all" onClick="editRecord();">
							<span class="ui-icon ui-icon-newwin"></span> Make Changes to Selected Record
						</button>
						<button class="ui-button ui-widget ui-corner-all" onClick="refreshList();">
							<span class="ui-icon ui-icon-refresh"></span> Reload List
						</button>
					</td>
				</tr>
			</table>
			<table id="itemlist" style="font-size:11px;">
				<thead>
					<tr>
						<th></th>
						<th></th>
						<th width=8%>CODE</th>
						<th>DESCRIPTION</th>
						<th width=12%>CATEGORY</th>
						<th width=12%>SUB-CATEGORY</th>
						<th width=10%>UNIT PRICE</th>
						<th width=12%>SPECIMEN</th>
						<th width=12%>CONTAINER</th>
						<th>RESULT TYPE</th>
						<th>RESULT AVAILABILITY</th>
						<th>MULTIPLE PROCEDURE</th>
					</tr>
				</thead>
			</table>
		</td>
	</tr>
 </table>
</body>
</html>