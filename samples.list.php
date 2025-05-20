<?php
	session_start();
	include("handlers/_generics.php");
	$o = new _init;

	if(isset($_GET['displayType']) && $_GET['displayType'] != '') {
		$displayType = $_GET['displayType'];
	} else { $displayType = '1'; }

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
			"keys": true,
			"scrollY":  "310px",
			"select":	'single',
			"pagingType": "full_numbers",
			"pageLength": 50,
			"bProcessing": true,
			"responsive": true,
			"sAjaxSource": "data/samplelist.php?displayType=" + <?php echo $displayType; ?> + "&sid=" + Math.random() + "",
			"scroller": true,
			"order": [[16, "desc"]],
			<?php if($_GET['code'] != 'undefined') { ?>
				"initComplete": function() {
					this.api().page.jumpToData("<?php echo $_GET['code']; ?>",1);
				},
			<?php } ?>
			"aoColumns": [
			  	
			  { mData: 'enccode' },
			  { mData: 'hmrno' },
			  { mData: 'orderdate' },
			  { mData: 'code' },
			  { mData: 'procedure' },
			  { mData: 'primecarecode' },
			  { mData: 'serialno' }, 
			  { mData: 'patientname' },
			  { mData: 'physician' },
              { mData: 'room' },
			  { mData: 'extractby' },
			  { mData: 'id' },
			  { mData: 'ostat' },
			  { mData: 'samplestatus' },
			  { mData: 'is_consolidated' },
			  { mData: 'dotime' },
			  { mData: 'tstamp' },
			  { mData: 'dopriority' }
			],
			"aoColumnDefs": [
			    { "className": "dt-body-center", "targets": [1,2,3,6,9,10]},
				{ "className": "dt-body-left", "targets": [7,8,13]},
			    { "targets": [0,3,5,11,12,14,15,16,17], "visible": false }
            ]<?php if($displayType != '5') { ?>,
			"createdRow": function( row, data, dataIndex){
                if(data.dopriority ==  'STAT'){
                    $(row).addClass('redClass');
                }
            }
			<?php } ?>
		});

		/* Pinger */
		myInterval = setInterval(pingStat, 90000);

	});

	function pingStat() {
		$.post("src/sjerp.php", { mod: "checkStatPing", sid: Math.random() }, function(data) {
			
			var statCount = parseFloat(data);

			if(statCount > 0) {

				var audio = new Audio('audio/pinger.mp3');
				audio.play();
			
				$.post("src/sjerp.php", { mod: "setPinged", enccode: data['enccode'], dotime: data['dotime'], code: data['code'] });
				
		
			}

		},"json");
	}

	function refreshList() {
		var myURL = "data/samplelist.php?displayType=" + <?php echo $displayType; ?> + "&sid=" + Math.random() + "";
		$('#itemlist').DataTable().ajax.url(myURL).load();
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

	function writeResult() {
		var table = $("#itemlist").DataTable();		
		var lid; 
		var stat; 
		var code; 
		var serialno;
	   	$.each(table.rows('.selected').data(), function() {
		    lid = this["id"];
		    stat = this['ostat'];
			code = this['primecarecode'];
			serialno = this['serialno'];
	   	});

		if(!lid) {
			parent.sendErrorMessage("- It appears you have not selected any orders from the given list yet...");
		} else {
			var msg ='';

			if(stat == '2') { msg = "It appears this sample has already been marked as \"Rejected\"!"; }
			if(stat == '4') { msg = "It appears that a result is already available for the sample."; }

			if(msg != '') {
				parent.sendErrorMessage(msg);
			} else {
				$.post("src/sjerp.php", { mod: "checkMultipleChem", code: code, serialno: serialno, sid: Math.random() }, function(data) {
					var scount = parseInt(data[0]);
					if(scount > 1) {

						var dis = $("#message").dialog({ 
							title: "System Message",
							width: "480",
							modal: true,
							resizeable: false,
							buttons: [
								{
									icons: { primary: "ui-icon-copy" },
									text: "Consolidate Result",
									click: function() { 
										parent.writeResult(lid,'L999');
										dis.dialog('close');
									}
								},
								{
									icons: { primary: "ui-icon-pencil" },
									text: "Make Indiviual Result for this test",
									click: function() { 
										parent.writeResult(lid,code);
									}	

								}
							]
						});
					} else {
						parent.writeResult(lid,code);
						
					}
					
				},"json");
			}
		}

	}

	function printBarcode() {
		var table = $("#itemlist").DataTable();		
		var lid; var stat;
	   	$.each(table.rows('.selected').data(), function() {
		    serialno = this["serialno"];

	   	});

		if(!serialno) {
			parent.sendErrorMessage("- It appears you have not selected any record yet for barcode printing...");
		} else {
			var msg ='';

			if(msg != '') {
				parent.sendErrorMessage(msg);
			} else {
				parent.printBarcode(serialno);
			}
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
			sstatus = this['samplestatus'];
	   	});

		if(sstatus == 'For Processing') {

			parent.sendErrorMessage("Selected request is still for processing...")

		} else {

			if(enccode) {
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

	}

	function restoreSample() {
		var table = $("#itemlist").DataTable();
		$.each(table.rows('.selected').data(), function() {
			enccode = this['enccode'];
			serialno = this['serialno'];
			proccode = this['code'];
			dotime = this['dotime'];
		});

		if(!enccode) {
			parent.sendErrorMessage("Please select a sample to restore...");
		}else {
			if(confirm("Are you sure you want to restore this sample?") == true) {
				$.post("src/sjerp.php", { mod: "resultRestore", enccode: enccode, serialno: serialno, sid: Math.random() }, function() {
					alert("Sample Successfully Restored!");
					refreshList();
				});
			}
		}
	}

	function changeDiplay(mod) {
		document.changeModPage.displayType.value = mod;
		document.changeModPage.submit();
	}

</script>
<style>
	body{ margin:0 auto; }

	.dataTables_wrapper {
		display: inline-block;
	    font-size: 11px;
		width: 100%;
		margin-left: auto;
 		margin-right: auto;
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
<body bgcolor="#ffffff" >

	<table width=100% align=center>
		<tr>
			<td width=70% align=left style="padding-bottom: 2px;">
				<button class="ui-button ui-widget ui-corner-all" onClick="writeResult();">
					<span class="ui-icon ui-icon-pencil"></span> Write or Update Result
				</button>
				<button class="ui-button ui-widget ui-corner-all" onClick="printResult();">
					<span class="ui-icon ui-icon-print"></span> Print Result
				</button>
				<button class="ui-button ui-widget ui-corner-all" onClick="printBarcode();">
					<img src="images/icons/barcode-scanner.png" width=15 height=15 align=absmiddle /> Print Barcode
				</button>
				<button class="ui-button ui-widget ui-corner-all" onClick="rejectSample();">
					<img src="images/icons/cancel48.png" width=15 height=15 align=absmiddle /> Reject Sample
				</button>
				<?php if($displayType == '2') { ?>
				<button class="ui-button ui-widget ui-corner-all" onClick="restoreSample();">
					<img src="images/icons/refresh.png" width=15 height=15 align=absmiddle /> Restore Sample
				</button>
				<?php  } ?>
				<button class="ui-button ui-widget ui-corner-all" onClick="refreshList();">
					<span class="ui-icon ui-icon-refresh"></span> Reload List
				</button>
			</td>
			<td align=right>
				<span class="spandix-l"><b>Display Type :</b> &nbsp;&nbsp;
				<select name="displayType" id="displayType" class="gridInput" style="width: 250px;" onchange="javascript: changeDiplay(this.value);">
					<option value=''>All</option>
					<option value='5' <?php if($displayType == "5") { echo "selected"; } ?>>Stat Priority</option>
					<option value='1' <?php if($displayType == 1) { echo "selected"; } ?>>For Processing</option>
					<option value='2' <?php if($displayType == 2) { echo "selected"; } ?>>Rejected Samples</option>
					<option value='3' <?php if($displayType == 3) { echo "selected"; } ?>>Results For Validation</option>
					<option value='4' <?php if($displayType == 4) { echo "selected"; } ?>>Validated Results</option>
				</select>
			</td>
		</tr>
	</table>
	<table id="itemlist" class="cell-border" style="font-size:11px;">
		<thead>
			<tr>
				<th></th>
				<th width=7%>HMR #</th>
				<th width=8%>ORDER DATE</th>
				<th width=10%>CODE</th>
				<th width=12%>PROCEDURE</th>
				<th></th>
				<th width=8%>SAMPLE #</th>
				<th width=15%>PATIENT NAME</th>
				<th width=10%>PHYSICIAN</th>
				<th width=10%>DEPT/WARD/ROOM#</th>
				<th width=10%>PROCESSED BY</th>
				<th></th>
				<th></th>
				<th width=10%>STATUS</th>
				<th></th>
				<th></th>
				<th></th>
				<th></th>
			</tr>
		</thead>
	</table>
	<div id="sampleDetails" style="display: none;">
		<form name="frmReject" id="frmReject">
			<table width=100% cellspacing=0 cellpadding=0><tr><td width=100% class=gridHead align=center>SAMPLE DETAILS</td></tr></table>
			<table width=100% cellpadding=0 cellspacing=0 style="border: 1px solid #cdcdcd; padding: 10px;">
				<tr>
					<td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Patient&nbsp;:</td>
					<td align=left>
						<input type="text" class="gridInput" style="width:100%;" name="phleb_pname" id="phleb_pname">
					</td>				
				</tr>
				<tr><td height=3></td></tr>
				<tr>
					<td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Required Procedure&nbsp;:</td>
					<td align=left>
						<input type="text" class="gridInput" style="width:100%;" name="phleb_procedure" id="phleb_procedure">
					</td>				
				</tr>
				<tr><td height=3></td></tr>
				<tr>
					<td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Procedure Code&nbsp;:</td>
					<td align=left>
						<input type="text" class="gridInput" style="width:100%;" name="phleb_code" id="phleb_code">
						<input type="hidden" name="primecode" id="primcode">
					</td>				
				</tr>
				<tr><td height=3></td></tr>
				<tr>
					<td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Specimen Type&nbsp;:</td>
					<td align=left>
						<select class="gridInput" style="width:100%;" name="phleb_spectype" id="phleb_spectype">
							<?php
								$iun = $o->dbquery("select id,sample_type from options_sampletype;");
								while(list($aa,$ab) = $iun->fetch_array()) {
									echo "<option value='$aa'>$ab</option>";
								}
							?>
						</select>
					</td>				
				</tr>
				<tr><td height=3></td></tr>
				<tr>
					<td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Sample Serial No.&nbsp;:</td>
					<td align=left>
						<input type="text" class="gridInput" style="width:100%;" name="phleb_serialno" id="phleb_serialno">
					</td>				
				</tr>
				<tr><td height=3></td></tr>
				<tr>
					<td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Date Extracted&nbsp;:</td>
					<td align=left>
						<input type="text" class="gridInput" style="width:100%;" name="phleb_date" id="phleb_date" value="<?php echo date('m/d/Y'); ?>">
					</td>				
				</tr>
				<tr><td height=3></td></tr>
				<tr>
					<td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Time Extracted&nbsp;:</td>
					<td align=left>
			
						<?php
							$o->timify("phleb",$w="");
						?>

					</td>				
				</tr>
				<tr><td height=3></td></tr>
				<tr>
					<td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Extracted By&nbsp;:</td>
					<td align=left>
						<input type="text" class="inputSearch2" style="width:100%;padding-left:22px;" name="phleb_by" id="phleb_by">
					</td>				
				</tr>
				<tr><td height=3></td></tr>
				<tr>
					<td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Room/Ward #&nbsp;:</td>
					<td align=left>
						<input type="text" class="inputSearch2" style="width:100%;padding-left:22px;" name="phleb_location" id="phleb_location" readonly>
					</td>				
				</tr>
				<tr><td height=3></td></tr>
				<tr>
					<td align="left" width="35%" class="bareBold" style="padding-right: 15px;" valign=top>Reason of Rejection&nbsp;:</td>
					<td align=left>
						<textarea name="phleb_remarks" id="phleb_remarks" style="width:100%;" rows=3></textarea>
					</td>				
				</tr>
			</table>
		</form>
	</div>
	<div id="singleResult" style="display: none;"></div>
	<div id="message" name="message" style="display: none;">
		<p style="margin-left: 20px; text-align: justify;" id="message">It appears that this Laboratory Request is accompanied with other Chemistry test that maybe consolidated into one Report Sheet. Select the appropriate button action below to continue.</span></p>
	</div>
	<div id="printConsolidation" name="printConsolidation" style="display: none;">
		<p style="margin-left: 20px; text-align: justify;" id="message">It appears that the selected result belongs to one consolidated result sheet. You may select from the given list w/c result you wish to print.</span></p><br/>
		<form name="otherTests" id="otherTests">

		</form>
	</div>
	<form name="changeModPage" id="changeModPage" action="samples.list.php" method="GET" >
		<input type="hidden" name="displayType" id="displayType">
	</form>
</body>
</html>