<?php
	session_start();
	include("handlers/initDB.php");
	$con = new myDB;
	$res = array();
	
	if(isset($_GET['id']) && $_GET['id'] != "") { 
		$res = $con->getArray("select * from services_master where id = '$_GET[id]';"); 
	}
	
	function getMod($def,$mod) {
		if($def == $mod) { return "class=\"float2\""; }
	}
	
	function setNavButtons($id) {
		global $con;
		
		$first = 1;	$fwd = $id+1; $prev = $id-1;

		list($last) = $con->getArray("select id from services_master order by id desc limit 1;");
		if($prev > 0)
			$nav = $nav . "<a href=# onclick=\"parent.showServiceInfo('$prev');\"><img src='images/resultset_previous.png'  title='Previous Record' /></a>";
		if($fwd > 0) 
			$nav = $nav . "<a href=# onclick=\"parent.showServiceInfo('$fwd');\"><img src='images/resultset_next.png' 'title='Next Record' /></a>";
		echo "<a href=# onclick=\"parent.showServiceInfo('$first');\"><img src='images/resultset_first.png' title='First Record' /><a>" . $nav . "<a href=# onclick=\"parent.showServiceInfo('$last');\"><img src='images/resultset_last.png' title='Last Record' /></a>";
	}
	
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title>Medgruppe Polyclinics & Diagnostic Center, Inc.</title>
<link href="style/style.css" rel="stylesheet" type="text/css" />
<link href="ui-assets/themes/smoothness/jquery-ui.css" rel="stylesheet" type="text/css" />
<link rel="stylesheet" type="text/css" href="ui-assets/datatables/css/jquery.dataTables.css">
<script type="text/javascript" charset="utf8" src="ui-assets/jquery/jquery-1.12.3.js"></script>
<script type="text/javascript" charset="utf8" src="ui-assets/themes/smoothness/jquery-ui.js"></script>
<script type="text/javascript" charset="utf8" src="ui-assets/datatables/js/jquery.dataTables.js"></script>
<script type="text/javascript" charset="utf8" src="ui-assets/datatables/js/dataTables.jqueryui.js"></script>
<script type="text/javascript" charset="utf8" src="ui-assets/datatables/js/dataTables.select.js"></script>
<script type="text/javascript" charset="utf8" src="js/tableH.js"></script>
<script>
	$(document).ready(function(){
		
		$('#bom_description').autocomplete({
			source:'suggestItemsCost.php', 
			minLength:3,
			select: function(event,ui) {
				$("#bom_code").val(ui.item.item_code);
				$("#bom_unit").val(decodeURIComponent(ui.item.unit));
				$("#bom_cost").val(decodeURIComponent(ui.item.unit_price));
			}
		});

		$('#sub_description').autocomplete({
			source:'suggestService.php', 
			minLength:3,
			select: function(event,ui) {
				$("#sub_code").val(ui.item.code);
			}
		});

		<?php 
			switch($_GET['mod']) {
				case "2":
					echo "$('#bomlist').dataTable({
						\"keys\": true,
						\"scrollY\":  \"220px\",
						\"select\":	\"single\",
						\"pagingType\": \"full_numbers\",
						\"pageLength\": \"50\",
						\"bProcessing\": false,
						\"sAjaxSource\": \"data/service.sublist.php?mod=bom&item=".$res['code']."\",
						\"order\": [[ 1, \"asc\" ]],
						\"aoColumns\": [
						  { mData: 'id' },
						  { mData: 'item_code' },
						  { mData: 'description' },
						  { mData: 'unit' },
						  { mData: 'unit_cost' },
						  { mData: 'qty' },
						  { mData: 'amount' },
						  { mData: 'remarks' }
						],
						\"aoColumnDefs\": [
							{className: \"dt-body-center\", \"targets\": [1,3]},
							{className: \"dt-body-right\", \"targets\": [4,5,6]},
							{ \"targets\": [0], \"visible\": false }
						]
					});";
				break;
				case "5":
					echo "$('#testvalues').dataTable({
						\"keys\": true,
						\"scrollY\":  \"220px\",
						\"select\":	\"single\",
						\"pagingType\": \"full_numbers\",
						\"pageLength\": \"50\",
						\"bProcessing\": false,
						\"sAjaxSource\": \"data/service.sublist.php?mod=testvalues&item=".$res['code']."\",
						\"order\": [[ 0, \"asc\" ]],
						\"aoColumns\": [
						  { mData: 'id' },
						  { mData: 'attribute_type' },
						  { mData: 'attribute' },
						  { mData: 'unit' },
						  { mData: 'min_value' },
						  { mData: 'max_value' },
						  { mData: 'low_critical' },
						  { mData: 'high_critical' }
						],
						\"aoColumnDefs\": [
							{ className: \"dt-body-center\", \"targets\": [1,3,4,5,6,7] },
							{ \"targets\": [0], \"visible\": false }
						]
					});";
				break;
			}
		?>
	});
	
	function savePInfo(rid) {
		
		if(confirm("Are you sure you want to save changes made to this product profile?") == true) {

			var msg = "";
			
			if($("#item_code").val() == "") { msg = msg + "- Invalid Service Code or Service Code is empty<br/>"; }
			if($("#description").val() == "") { msg = msg + "- Service <b>Short Description</b> is empty<br/>"; }


			if(msg!="") {
				parent.sendErrorMessage(msg);
			} else {
				$.post("src/sjerp.php", { mod: "checkServiceDupCode", rid: rid, item_code: $("#item_code").val(), barcode: $("#item_barcode").val(), sid: Math.random() }, function(data) {
					if(data == "NODUPLICATE") {
						var url = $(document.merchandise).serialize();
							url = "mod=saveSInfo&"+url;
						$.post("src/sjerp.php", url);
						alert("Record Successfuly Saved!"); 
						parent.closeDialog("#itemdetails");	
						parent.showServices($("#item_code").val());
					} else { parent.sendErrorMessage("Duplicate Code or Barcode has been detected!"); }
				},"html");
			}
		}
	}
	
	function deletePro(rid) {
		if(confirm("Are you sure you want to delete this record?") == true) {
			$.post("src/sjerp.php", { mod: "deletePro", rid: rid, sid: Math.random() }, function() { 
				alert("Merchandise Record Successfully Deleted!"); 
				parent.closeDialog("#itemdetails");	
				parent.showItems();
			});
		}
	}
	
	
	function getMyCode() {
		$.post("src/sjerp.php", { mod: "getServiceCode", mid: $("#item_category").val(), sgroup: $("#item_sgroup").val(), sid: Math.random() }, function(data) {
			$("#item_code").val(data);
		});
	}
	
	function getSgroup(parent) {
		$.post("src/sjerp.php", { mod: "getServiceSubgroup", parent: parent, sid: Math.random() }, function(data) {
			$("#item_sgroup").html(data);
		},"html");
	}
	
	function changeMod(mod) {
		document.changeModPage.mod.value = mod;
		document.changeModPage.submit();
	}

	function computeBomAmount() {
		var cost = parent.stripComma($("#bom_cost").val());
		var qty = parent.stripComma($("#bom_qty").val());

		if(isNaN(qty) == true) { parent.sendErrorMessage("- Invalid Qty!"); } else {
			var amount = parseFloat(cost) * parseFloat(qty);
				amount = amount.toFixed(2);
			$("#bom_amount").val(amount);

		}

	}

	function updateBom() {

		var table = $("#bomlist").DataTable();		
		var arr = [];
	    $.each(table.rows('.selected').data(), function() {
		   arr.push(this["id"]);
	    });

		if(!arr[0]) {
			parent.sendErrorMessage("Please select a record to update by clicking the line row...");
		} else {

			$.post("src/sjerp.php", { mod: "retrieveBoM", rid: arr[0], sid: Math.random() }, function(spdata) {
				
				$("#bom_code").val(spdata['item_code']);
				$("#bom_description").val(spdata['description']);
				$("#bom_unit").val(spdata['unit']);
				$("#bom_cost").val(spdata['ucost']);
				$("#bom_qty").val(spdata['qty']);
				$("#bom_amount").val(spdata['amt']);
				$("#bom_remarks").val(spdata['remarks']);

				$("#bom_description").attr({"readonly": true });


				$("#bomDiv").dialog({title: "Add B.O.M", width: 480, resizable: false, modal: true, buttons: {
						"Update Record": function() { 
							if(confirm("Are you sure you want update this file?") == true) {

									var msg = "";
									
									if($("#bom_code").val() == '') { msg = msg + "Invalid Item Code.<br/>"; }

									var bom_qty = parent.stripComma($("#bom_qty").val());
									if(isNaN(bom_qty) == true || $("#bom_qty").val() == '') {
										msg = msg + "- Invalid Quantity.<br/>"
									}

									if(msg == '') {
										$.post("src/sjerp.php", { 
												mod: "updateBoM",
												rid: arr[0],
												qty: $("#bom_qty").val(),
												amount: $("#bom_amount").val(),
												remarks: $("#bom_remarks").val(),
												sid: Math.random() 
											},function() { 
												alert("Record Successfully Saved!");
												changeMod(2);
											}
										);

									} else { parent.sendErrorMessage(msg); }
								}
							},
						"Cancel": function () { $(this).dialog("close"); }
					} 
				});
			},"json");
		}
	}

	function addBom() {

		$("#frmBoQ").trigger("reset");
		$("#bom_description").attr({readonly: false});

		$("#bomDiv").dialog({title: "Add B.O.M", width: 480, resizable: false, modal: true, buttons: {
				"Add Bill of Material": function() { 
					if(confirm("Are you sure you want add this file?") == true) {
						$.post("src/sjerp.php", { mod: "checkifBoM", scode: $("#item_code").val(), icode: $("#bom_code").val(), sid: Math.random() }, function (spdata) {

							if(spdata == 'ok') {

								var msg = "";
								
								if($("#bom_code").val() == '') { msg = msg + "Invalid Item Code.<br/>"; }

								var bom_qty = parent.stripComma($("#bom_qty").val());
								if(isNaN(bom_qty) == true || $("#bom_qty").val() == '') {
									msg = msg + "- Invalid Quantity.<br/>"
								}

								if(msg == '') {
									$.post("src/sjerp.php", { 
											mod: "newBOM",
											scode: $("#item_code").val(),
											icode: $("#bom_code").val(),
											description: $("#bom_description").val(),
											unit: $("#bom_unit").val(),
											cost: $("#bom_cost").val(),
											qty: $("#bom_qty").val(),
											amount: $("#bom_amount").val(),
											remarks: $("#bom_remarks").val(),
											sid: Math.random() 
										},function() { 
											alert("Record Successfully Saved!");
											changeMod(2);
										}
									);

								} else { parent.sendErrorMessage(msg); }
							} else { parent.sendErrorMessage("- Cannot save this record as new file as it appears that a similar item has already been added."); }

						},"html");
					}
				},
				"Cancel": function () { $(this).dialog("close"); }
			} 
		});
	}

	function removeBom() {
		var table = $("#bomlist").DataTable();		
		var arr = [];
	    $.each(table.rows('.selected').data(), function() {
		   arr.push(this["id"]);
	    });

		if(!arr[0]) {
			parent.sendErrorMessage("Please select a record to remove by clicking the line row...");
		} else {
			if(confirm("Are you sure you want to remove this entry?") == true) {
				$.post("src/sjerp.php", { mod: "removeBoM", rid: arr[0], sid: Math.random()},function() { changeMod(2); });
			}
		}
	}

	function addSubtest() {
		var subdiv = $("#subDiv").dialog({ 
			title: "Sub-Procedures",
			width: 480,
			resizable: false,
			modal: true,
			buttons: {
				"Add Procedure": function() {
					if($("#sub_code").val() != '') {
						$.post("src/sjerp.php", {
							mod: "addSublist",
							code: $("#sub_code").val(),
							description: $("#sub_description").val(),
							parent: $("#item_code").val(),
							sid: Math.random() },
							function() {
								alert("Sub-procedure successfully added!");
								changeMod(4);
							}
						);
					} else {
						parent.sendErrorMessage("Unable to continue as it appears you haven't properly indicated the correct procedure code.");
					}
				},
				"Cancel": function() { $(this).dialog("close"); }
			}
		});
	}

	function removeSubtest() {
		var table = $("#sublist").DataTable();		
		var arr = [];
	    $.each(table.rows('.selected').data(), function() {
		   arr.push(this["id"]);
	    });

		if(!arr[0]) {
			parent.sendErrorMessage("Please select a record to remove by clicking the line row...");
		} else {
			if(confirm("Are you sure you want to remove this entry?") == true) {
				$.post("src/sjerp.php", { mod: "removeSublist", lid: arr[0], sid: Math.random()},function() { changeMod(4); });
			}
		}
	}

	function addAttribute() {
		var attrdiv = $("#testDiv").dialog({ 
			title: "Result Attributes",
			width: 480,
			resizable: false,
			modal: true,
			buttons: {
				"Add Result Attribute": function() {
					
					var msg = '';
					if($("#test_attribute").val() == '') { msg = msg + "- Result attribute must not be empty!<br/>"; }
					
					if($("#test_attrtype").val() == 'NON-DESCRIPTIVE') {
						if($("#test_unit").val() == '') { msg = msg + "- Unit is invalid!<br/>"; }
						if($("#test_minvalue").val() == '') { msg = msg + "- Invalid \"Minimum Normal Value\"!<br/>"; }
						if($("#test_maxvalue").val() == '') { msg = msg + "- Invalid \"Maximum Normal Value\"!<br/>"; }
					}

					if(msg == '') {
						$.post("src/sjerp.php", {
								mod: "addAttribute",
								parent: $("#item_code").val(),
								attr_type: $("#test_attrtype").val(),
								attr: $("#test_attribute").val(),
								unit: $("#test_unit").val(),
								min: $("#test_minvalue").val(),
								max: $("#test_maxvalue").val(),
								low: $("#test_lowvalue").val(),
								high: $("#test_highvalue").val(),
								desc: $("#descriptive_value").val(),
								sid: Math.random()
							},
							function() {
								alert("Result Attribute successfully added!");
								changeMod(5);
							}
						);
					} else {
						parent.sendErrorMessage(msg);
					}
				},
				"Cancel": function() { $(this).dialog("close"); }
			}
		});
	}

	function editAttribute() {
		var table = $("#testvalues").DataTable();		
		var arr = [];
	    $.each(table.rows('.selected').data(), function() {
		   arr.push(this["id"]);
	    });

		if(!arr[0]) {
			parent.sendErrorMessage("It seems you haven't selected any record from the given list.");
		} else {
			$.post("src/sjerp.php", {
				mod: "retrieveTestValues",
				lid: arr[0],
				sid: Math.random()
			},
			function(data) {
				
				if(data['attribute'] != '') {
					
					$("#test_attrtype").val(data['attribute_type']);
					$("#test_attribute").val(data['attribute']);
					$("#test_unit").val(data['unit']);
					$("#test_minvalue").val(data['min_value']);
					$("#test_maxvalue").val(data['max_value']);
					$("#test_lowvalue").val(data['critical_low_value']);
					$("#test_highvalue").val(data['critical_high_value']);
					$("#descriptive_value").val(data['descriptive_value']);

					var testDiv = $("#testDiv").dialog({ 
						title: "Update Result Attribute",
						width: 480,
						modal: true,
						resizable: false,
						buttons: {
							"Update Record": function() {

								var msg = '';
								if($("#test_attribute").val() == '') { msg = msg + "- Result attribute must not be empty!<br/>"; }
								
								if($("#test_attrtype").val() == 'NON-DESCRIPTIVE') {
									if($("#test_unit").val() == '') { msg = msg + "- Unit is invalid!<br/>"; }
									if($("#test_minvalue").val() == '') { msg = msg + "- Invalid \"Minimum Normal Value\"!<br/>"; }
									if($("#test_maxvalue").val() == '') { msg = msg + "- Invalid \"Maximum Normal Value\"!<br/>"; }
								}

								if(msg == '') {

									$.post("src/sjerp.php", {
									
										mod: "updateAttribute",
										lid: arr[0],
										parent: $("#item_code").val(),
										attr_type: $("#test_attrtype").val(),
										attr: $("#test_attribute").val(),
										unit: $("#test_unit").val(),
										min: $("#test_minvalue").val(),
										max: $("#test_maxvalue").val(),
										low: $("#test_lowvalue").val(),
										high: $("#test_highvalue").val(),
										desc: $("#descriptive_value").val(),
										sid: Math.random()
									}, function() { alert("Changes made to the file was successfully saved..."); changeMod(5); });

								} else { parent.sendErrorMessage(msg); }
							},
							"Cancel": function() { $(this).dialog("close"); }
						}
					});
				} else {
					parent.sendErrorMessage("There was an error trying to retrieve the data. Try refreshing the page window to resolve this problem.");
				}

			},"json");
		}
	}

	function removeAttr() {
		var table = $("#testvalues").DataTable();		
		var arr = [];
	    $.each(table.rows('.selected').data(), function() {
		   arr.push(this["id"]);
	    });

		if(!arr[0]) {
			parent.sendErrorMessage("It seems you haven't selected any record from the given list.");
		} else {
			if(confirm("Are you sure you want to remove this test attribute?") == true) {
				$.post("src/sjerp.php", {
					mod: "removeAttribute",
					lid: arr[0],
					sid: Math.random()
				}, function() { alert("Attribute successfully removed!"); changeMod(5); });

			}

		}
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
<form name="merchandise" id="merchandise">
	<input type="hidden" name="rid" id="rid" value="<?php echo $res['id']; ?>">
	<table width="100%" cellspacing="0" cellpadding="5" style="border-bottom: 1px solid black; margin-bottom: 5px;">
		<tr>
			<td align=left>
				<?php
					if($_GET['mod'] == 1) {
						echo '<a href="#" onClick="savePInfo(\''. $res['id'] . '\');" class="topClickers"><img src="images/icons/floppy.png" width=18 height=18 align=absmiddle />&nbsp;Add/Save Changes Made</a>';
						if($res['id'] != "") {
							echo '&nbsp;&nbsp;<a href="#" onClick="deletePro(\'' . $_GET['id'] . '\');" class="topClickers"><img src="images/icons/delete.png" width=18 height=18 align=absmiddle />&nbsp;Delete Record</a>';
						} 
					}
				?>
			</td>
			<td align=right>
				<?php if($_GET['id']) { setNavButtons($_GET['id']); } ?>
			</td>
		</tr>
	</table>
	<table width="100%" border="0" cellspacing="0" cellpadding="0" >
		<tr>
			<td width="10%" align=left class="spandix-l">
				Service Code :
			</td>
			<td width="40%" align=left>
				<input type="text" name="item_code" id="item_code" style="width: 80%;" class="nInput" value="<?php echo $res['code']; ?>" readonly>
			</td>
			<td width="10%" class="spandix-l">
				Product Category :
			</td>
			<td width="40%">
				<select name="item_category" id="item_category" style="width: 70%;" class="nInput" <?php if($res['id'] == '') { echo "onChange=\"javascript: getMyCode(); getSgroup(this.value);\""; } ?>>
					<option value="">- Select Category -</option>
					<?php
						$mit = $con->dbquery("select id,category from options_servicecat;");
						while(list($o,$oo) = $mit->fetch_array()) {
							echo "<option value='$o' ";
								if($res['category'] == $o) { echo "selected"; }
							echo ">$oo</option>";
						}
						unset($mit);
					?>
				</select>
			</td>
		</tr>
		<tr><td height=1></td></tr>
		<tr>
			<td width="10%" align=left class="spandix-l">
				Description :
			</td>
			<td width="40%" align=left>
				<input type="text" name="item_description" id="item_description" style="width: 80%;" class="nInput" value="<?php echo $res['description']; ?>">
			</td>
			<td width="10%" class="spandix-l"></td>
			<td width="40%">
				<select name="item_sgroup" id="item_sgroup" style="width: 70%;" class="nInput" <?php if($res['id'] == '') { echo "onChange=\"javascript: getMyCode();\""; } ?>>
					<option value="">- Not Applicable -</option>
					<?php
						
						if($res['id'] != '') {
							$mit = $con->dbquery("select id, subcategory from options_servicesubcat where 1=1 and parent_id = '$res[category]' order by subcategory asc;");
							while(list($o,$oo) = $mit->fetch_array()) {
								echo "<option value='$o' ";
									if($res['subcategory'] == $o) { echo "selected"; }
								echo ">$oo</option>";
							}
						}
					?>
				</select>
			</td>
		</tr>
		<tr><td height=1></td></tr>
		<tr>
			<td width="10%" align=left class="spandix-l">
				Abbreviation. :
			</td>
			<td width="40%" align=left>
				<input type="text" name="item_shortdesc" id="item_shortdesc" style="width: 80%;" class="nInput" value="<?php echo $res['short_description']; ?>">
			</td>
			<td width="10%" class="spandix-l"></td>
			<td width="40%" class="spandix-l"><input type="checkbox" name="status" id="status" <?php if($res['active'] == 'N') { echo "checked"; } ?> value="N">&nbsp;Inactive</td>
		</tr>
		<tr><td height=1></td></tr>
		<tr>
			<td width="10%" align=left class="spandix-l">
				iHOMIS Code :
			</td>
			<td width="40%" align=left>
				<input type="text" name="item_barcode" id="item_barcode" style="width: 80%;" class="nInput" value="<?php echo $res['ihomis_code']; ?>">
			</td>
			<td width="10%" class="spandix-l"></td>
			<td width="40%" class="spandix-l"></td>
		</tr>
		<tr>
			<td width="10%" align=left class="spandix-l">
				Report Header (If Individual Result) :
			</td>
			<td width="40%" align=left>
				<input type="text" name="report_title" id="report_title" style="width: 80%;" class="nInput" value="<?php echo $res['report_title']; ?>">
			</td>
			<td width="10%" class="spandix-l"></td>
			<td width="40%" class="spandix-l"></td>
		</tr>
	</table>
	<table cellspacing=0 cellpadding=0 width=100% align=center style="margin-top: 10px;">
		<tr>
			<td style="padding: 0px 0px 1px 0px;">
				<div id="custmenu" align=left class="ddcolortabs">
					<ul class=float2>
						<li><a href="#" <?php echo getMod("1",$_GET['mod']); ?> onclick="javascript: changeMod(1);"><span id="tbbalance1">General Info</span></a></li>
						<?php
							 if($_GET['id'] != '') {
								if($res['with_bom'] == 'Y') {
									echo '<li><a href="#" ' . getMod("2",$_GET['mod']) . ' onclick="javascript: changeMod(2);"><span id="tbbalance2">Bill of Quantities</span></a></li>';
								}
								
								if($res['with_subtests'] == 'Y') {
									echo '<li><a href="#" ' . getMod("4",$_GET['mod']) . ' onclick="javascript: changeMod(4);"><span id="tbbalance4">Sub-test Inclusions</span></a></li>';
								}

								if($res['result_type'] == 'QUANTITATIVE') {
									echo '<li><a href="#" ' . getMod("5",$_GET['mod']) . ' onclick="javascript: changeMod(5);"><span id="tbbalance4">Manage Result Values</span></a></li>';
								}

							}
						?>
					</ul>
				</div>
			</td>
		</tr>
	</table>
	<?php switch($_GET['mod']) { case "1": ?>
	<table width="100%" cellpadding=0 cellspacing=1 class="td_content" style="padding:10px;" border=0>
		<tr>
			<td width=20% class="spandix-l" valign=top>Full Description or FAQ:</td>
			<td width=85% colspan=3><textarea name="item_fdescription" id="item_fdescription" style="width: 100%;" rows=3><?php echo $res['fulldescription']; ?></textarea></td>
		</tr>
		<tr>
			<td width=20% class="spandix-l">Unit :</td>
			<td width=35%> 
				<select name="item_unit" id="item_unit" style="width: 100%;" class="nInput">
					<?php
						$iun = $con->dbquery("select unit, description from options_units;");
						while(list($u,$uu) = $iun->fetch_array()) {
							echo "<option value='$u' ";
								if($res['unit'] == $u) { echo "selected"; }
							echo ">$uu</option>";
						}
					?>
				</select>
			</td>
			<td width=20% class="spandix-l"></td>
			<td width=35% align=right>
				
			</td>
		</tr>
		<tr>
			<td width=15% class="spandix-l">Specimen/Sample Required :</td>
			<td width=30%>
				<select name="with_specimen" id="with_specimen" style="width: 100%;" class="nInput">
					<option value="Y" <?php if($res['with_specimen'] == 'Y') { echo "selected"; } ?>>- Yes -</option>
					<option value="N"<?php if($res['with_specimen'] == 'N') { echo "selected"; } ?>>- No -</option>
				</select>
			</td>
			<td width=20% class="spandix-l"></td>
			<td width=35% align=right>
			
			</td>
		</tr>				

		<tr>
			<td width=15% class="spandix-l">Sample Type :</td>
			<td width=30%>
				<select name="sample_type" id="sample_type" style="width: 100%;" class="nInput">
					<option value=''>- Not Applicable -</option>
					<?php
						$iun = $con->dbquery("select id,sample_type from options_sampletype;");
						while(list($aa,$ab) = $iun->fetch_array()) {
							echo "<option value='$aa' ";
							if($res['sample_type'] == $aa) { echo "selected"; }
							echo ">$ab</option>";
						}
					?>
				</select>
			</td>
			<td width=20% class="spandix-l"></td>
			<td width=35% align=right>
			
			</td>
		</tr>
		<tr>
			<td width=15% class="spandix-l">Specimen Container :</td>
			<td width=30%>
				<select name="container_type" id="container_type" style="width: 100%;" class="nInput">
					<option value=''>- Not Applicable -</option>
					<?php
						$ctQuery = $con->dbquery("select id,type from options_containers;");
						while(list($cntId,$cntType) = $ctQuery->fetch_array()) {
							echo "<option value='$cntId' ";
							if($res['container_type'] == $cntId) { echo "selected"; }
							echo ">$cntType</option>";
						}
					?>
				</select>		
			</td>
			<td width=20% class="spandix-l"></td>
			<td width=35% align=right>
				
			</td>
		</tr>
		<tr>
			<td width=15% class="spandix-l">Sub-tests Required :</td>
			<td width=30%>
				<select name="with_subtests" id="with_subtests" style="width: 100%;" class="nInput">
					<option value="N"<?php if($res['with_subtests'] == 'N') { echo "selected"; } ?>>- No -</option>
					<option value="Y" <?php if($res['with_subtests'] == 'Y') { echo "selected"; } ?>>- Yes -</option>
				</select>		
			</td>
			<td width=20% class="spandix-l"></td>
			<td width=35% align=right>
				
			</td>
		</tr>
		<tr>
			<td width=15% class="spandix-l" >With Bill of Quantities :</td>
			<td width=30%>
				<select name="with_bom" id="with_bom" style="width: 100%;" class="nInput">
					<option value="Y" <?php if($res['with_bom'] == 'Y') { echo "selected"; } ?>>- Yes -</option>
					<option value="N"<?php if($res['with_bom'] == 'N') { echo "selected"; } ?>>- No -</option>
				</select>
			</td>
			<td width=20% class="spandix-l"></td>
			<td width=35% align=right>
				
			</td>
		</tr>
		<tr>
			<td width=15% class="spandix-l" >Analysis Type :</td>
			<td width=30%>
				<select name="result_type" id="result_type" style="width: 100%;" class="nInput">
					<option value="QUANTITATIVE" <?php if($res['result_type'] == 'QUANTITATIVE') { echo "selected"; } ?>>QUANTITATIVE</option>
					<option value="QUALITATIVE"<?php if($res['result_type'] == 'QUALITATIVE') { echo "selected"; } ?>>QUALITATIVE</option>
					<option value="BOTH"<?php if($res['result_type'] == 'BOTH') { echo "selected"; } ?>>BOTH QUALITATIVE & QUANTITATIVE</option>
					<option value="DESCRIPTIVE"<?php if($res['result_type'] == 'DESCRIPTIVE') { echo "selected"; } ?>>DESCRIPTIVE</option>
				</select>
			</td>
			<td width=20% class="spandix-l"></td>
			<td width=35% align=right>
				
		</tr>
		<tr>
			<td colspan=4 height=80>&nbsp;</td>
		</tr>
	</table>
	<?php break; case "2": ?>
		<table id="bomlist" style="font-size:11px;">
			<thead>
				<tr>
					<th>ID</th>
					<th width=10%>CODE</th>
					<th>DESCRIPTION</th>
					<th width=8%>UNIT</th>
					<th width=12%>UNIT COST</th>
					<th width=8%>QTY</th>
					<th width=12%>AMOUNT</th>
					<th width=25%>REMARKS</th>
				</tr>
			</thead>
		</table>
		<table>
			<tr>
				<td align=left colspan=2 style="padding-top:5px;">
					<a href="#" class="topClickers" onClick="javascript:addBom();"><img src="images/icons/add-2.png" width=16 height=16 border=0 align="absmiddle">&nbsp;Add Bill of Material</a>&nbsp;
					<a href="#" class="topClickers" onClick="javascript:updateBom();"><img src="images/icons/edit.png" width=16 height=16 border=0 align="absmiddle">&nbsp;Update Selected Record</a>&nbsp;
					<a href="#" class="topClickers" onClick="javascript:removeBom();"><img src="images/icons/delete.png" width=16 height=16 border=0 align="absmiddle">&nbsp;Remove Item from B.O.M</a>
				</td>
			</tr>
		</table>
		<?php break; case "5": ?>
		<table id="testvalues" style="font-size:11px;" width=100%>
				<thead>
					<tr>
						<th></th>
						<th width=15%>TYPE</th>
						<th width=15%>ATTRIBUTE</th>
						<th width=10%>UNIT</th>
						<th width=15%>MIN. VALUE</th>
						<th width=15%>MAX. VALUE</th>
						<th width=15%>LOW (CRITICAL)</th>
						<th width=15%>HIGH (CRITICAL)</th>
					</tr>
				</thead>
			</table>
			<table>
				<tr>
					<td align=left colspan=2 style="padding-top:5px;">
						<a href="#" class="topClickers" onClick="javascript:addAttribute();"><img src="images/icons/add-2.png" width=16 height=16 border=0 align="absmiddle">&nbsp;Add Attribute</a>&nbsp;
						<a href="#" class="topClickers" onClick="javascript:editAttribute();"><img src="images/icons/edit.png" width=16 height=16 border=0 align="absmiddle">&nbsp;Edit Attribute</a>&nbsp;
						<a href="#" class="topClickers" onClick="javascript:removeAttr();"><img src="images/icons/delete.png" width=16 height=16 border=0 align="absmiddle">&nbsp;Remove Attribute</a>
					</td>
				</tr>
			</table>

		<?php break; } ?>	
</form>
<div id="bomDiv" style="display: none;">
	<form name="frmBoQ" id="frmBoQ">
		<input type = "hidden" name = "bom_id" id = "bom_id">
		<table width=100% cellspacing=2 cellpadding=0>
			<tr>
				<td class="bareThin" align=left width=40%>Description :</td>
				<td align=left>
					<input type="text" name="bom_description" id="bom_description" class="inputSearch2" style="width: 80%; padding-left: 22px;">
				</td>
			</tr>
			<tr>
				<td class="bareThin" align=left width=40%>Code :</td>
				<td align=left>
					<input type="text" name="bom_code" id="bom_code" class="gridInput" style="width: 80%;" readonly>
				</td>
			</tr>
			<tr>
				<td class="bareThin" align=left width=40%>Unit :</td>
				<td align=left>
				<input type="text" name="bom_unit" id="bom_unit" class="gridInput" style="width: 80%; tex-align: right;" readonly>
				</td>
			</tr>
			<tr>
				<td class="bareThin" align=left width=40%>Unit Cost :</td>
				<td align=left>
				<input type="text" name="bom_cost" id="bom_cost" class="gridInput" style="width: 80%; tex-align: right;" value='0.00' readonly>
				</td>
			</tr>
			<tr>
				<td class="bareThin" align=left width=40%>Qty :</td>
				<td align=left>
				<input type="text" name="bom_qty" id="bom_qty" class="gridInput" style="width: 80%; tex-align: right;" value='0.00' onchange="javascript: computeBomAmount();">
				</td>
			</tr>
			<tr>
				<td class="bareThin" align=left width=40%>Amount :</td>
				<td align=left>
				<input type="text" name="bom_amount" id="bom_amount" class="gridInput" style="width: 80%;" value='0.00' readonly>
				</td>
			</tr>
			<tr>
				<td class="bareThin" align=left width=40% valign=top>Memo/Remarks :</td>
				<td align=left><textarea type="text" style="width: 80%; font-size: 11px;" rows=1 name = "bom_remarks" id = "bom_remarks"></textarea></td>
			</tr>
		</table>
	</form>
</div>
<div id="subDiv" style="display: none;">
	<form name="frmSubtest" id="frmSubtest">
		<input type = "hidden" name = "bom_id" id = "bom_id">
		<table width=100% cellspacing=2 cellpadding=0>
			<tr>
				<td class="bareThin" align=left width=40%>Description :</td>
				<td align=left>
					<input type="text" name="sub_description" id="sub_description" class="inputSearch2" style="width: 80%; padding-left: 22px;">
				</td>
			</tr>
			<tr>
				<td class="bareThin" align=left width=40%>Code :</td>
				<td align=left>
					<input type="text" name="sub_code" id="sub_code" class="gridInput" style="width: 80%;" readonly>
				</td>
			</tr>
		</table>
	</form>
</div>
<div id="testDiv" style="display: none;">
	<form name="frmTestvalue" id="frmTestvalue">
		<input type = "hidden" name = "bom_id" id = "bom_id">
		<table width=100% cellspacing=2 cellpadding=0>
			<tr>
				<td class="bareThin" align=left width=40%>Analysis Type :</td>
				<td align=left>
					<select name="test_attrtype" id="test_attrtype" class="gridInput" style="width: 80%;">
						<option value="QUANTITATIVE">QUANTITATIVE</option>
						<option value="QUALITATIVE">QUALITATIVE</option>
						<option value="BOTH">BOTH QUANTITATIVE & QUALITATIVE</option>
					</select>
				</td>
			</tr>
			<tr>
				<td class="bareThin" align=left width=40%>Attribute :</td>
				<td align=left>
					<input type="text" name="test_attribute" id="test_attribute" class="gridInput" style="width: 80%;">
				</td>
			</tr>
			<tr>
				<td class="bareThin" align=left width=40%>Unit :</td>
				<td align=left>
					<input type="text" name="test_unit" id="test_unit" class="gridInput" style="width: 80%;">
				</td>
			</tr>
			<tr>
				<td class="bareThin" align=left width=40%>Min. Normal Value :</td>
				<td align=left>
					<input type="text" name="test_minvalue" id="test_minvalue" class="gridInput" style="width: 80%;" >
				</td>
			</tr>
			<tr>
				<td class="bareThin" align=left width=40%>Max. Normal Value :</td>
				<td align=left>
					<input type="text" name="test_maxvalue" id="test_maxvalue" class="gridInput" style="width: 80%;" >
				</td>
			</tr>
			<tr>
				<td class="bareThin" align=left width=40%>Critical Low Value :</td>
				<td align=left>
					<input type="text" name="test_lowvalue" id="test_lowvalue" class="gridInput" style="width: 80%;" >
				</td>
			</tr>
			<tr>
				<td class="bareThin" align=left width=40%>Critical High Value :</td>
				<td align=left>
					<input type="text" name="test_highvalue" id="test_highvalue" class="gridInput" style="width: 80%;" >
				</td>
			</tr>
			<tr>
				<td class="bareThin" align=left width=40% valign=top>Descriptive Remarks :</td>
				<td align=left>
					<textarea name="descriptive_value" id="descriptive_value" style="width: 80%;" rows='3'></textarea>
				</td>
			</tr>
		</table>
	</form>
</div>
<form name="changeModPage" id="changeModPage" action="service.details.php" method="GET" >
	<input type="hidden" name="id" id="id" value="<?php echo $res['id']; ?>">
	<input type="hidden" name="mod" id="mod">
</form>
</body>
</html>