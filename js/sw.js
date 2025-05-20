
function computeItemAmount(qty) {
	if(isNaN(qty) == true) { parent.sendErrorMessage("-Invalid Quantity!"); $("#itemQty").val(''); }
	var cost = parseFloat(parent.stripComma($("#itemCost").val()));
	
	var amount = qty * cost;
		amount = amount.toFixed(2);

	$("#itemAmount").val(parent.kSeparator(amount));
}

function saveSWHeader() {
	var msg = "";
	if($("#withdrawn_by").val() == "") { msg = msg + "- Please specify the person who will withdraw these items."; }
	if(msg != "") {
		parent.sendErrorMessage(msg);
	} else {
		$.post("sw.datacontrol.php", { mod: "saveHeader", trace_no: $("#trace_no").val(), sw_no: $("#sw_no").val(), sw_date: $("#sw_date").val(), wby: $("#withdrawn_by").val(), cost_center: $("#cost_center").val(), ppp_id: $("#ppp_id").val(), request_date: $("#request_date").val(), ref_type: $("#ref_type").val(), mr_no: $("#mr_no").val(), remarks: $("#remarks").val(), sid: Math.random() }, function(data) {  $("#sw_no").val(data); parent.popSaver(); },"html");
	}
}

function addItem() {
	$("#itemEntry").dialog({title: "Add Item", width: 440, resizable: false, modal: true, buttons: { 
			"Add Item": function() { 
				var msg = "";

				if($("#itemCode").val() == "") { msg = msg + "- Invalid Item Code<br/>"; }
				if(isNaN($("#itemQty").val()) == true) { msg = msg + "- Invalid Quantity<br/>"; }

				if(msg != '') {
					parent.sendErrorMessage(msg);
				
				} else {
					$.post("sw.datacontrol.php", { 
						mod: "addItem", 
						sw_no: $("#sw_no").val(), 
						trace_no: $("#trace_no").val(), 
						item: $("#itemCode").val(), 
						description: $("#itemDescription").val(), 
						unit: $("#itemUnit").val(), 
						qty: $("#itemQty").val(),
						lot_no: $("#itemLotNo").val(), 
						expiry: $("#itemExpiry").val(), 
						sid: Math.random() }, 
					function(gt) {
						redrawDataTable();
						$("#grandTotal").val(gt);
						$("#frmItemEntry").trigger("reset");
						
					});
				}
			},
			"Cancel": function() { $(this).dialog("close"); $("#frmItemEntry").trigger("rest"); }
		}
	
	});

	$('#itemDescription').autocomplete({
		source:'suggestItemsCost.php', 
		minLength:3,
		select: function(event,ui) {
			$("#itemCode").val(ui.item.item_code);
			$("#itemUnit").val(decodeURIComponent(ui.item.unit));
		}
	});
}

function deleteItem(){
	var table = $("#details").DataTable();
	var arr = [];
   $.each(table.rows('.selected').data(), function() {
	   arr.push(this["id"]);
   });
  
	if(!arr[0]) {
		parent.sendErrorMessage("Please select a record to delete.");
	} else {
		if(confirm("Are you sure you want to remove this line entry?") == true) {
			$.post("sw.datacontrol.php", { mod: "deleteLine", lid: arr[0], sw_no: $("#sw_no").val(), sid: Math.random() }, function(gt) { redrawDataTable(); $("#grandTotal").val(gt); });
		}
	}
}

function updateItem() {
	var table = $("#details").DataTable();
	var arr = [];
	$.each(table.rows('.selected').data(), function() {
		arr.push(this["id"]);
	});

	if(!arr[0]) {
		parent.sendErrorMessage("Please select a record to update.");
	} else {
		
		$.post("sw.datacontrol.php", { mod: "retrieveLine", lid: arr[0], sid: Math.random() }, function(data) { 
			$("#itemDescription").val(parent.decodeEntities(data['description']));
			$("#itemCode").val(data['item_code']);
			$("#itemUnit").val(data['unit']);
			$("#itemQty").val(data['qty']);
			$("#itemLotNo").val(data['lot_no']);
			$("#itemExpiry").val(data['exp']);
		
			$("#itemEntry").dialog({title: "Update Line Entry", width: 440, resizable: false, modal: true, buttons: { 
					"Save Changes": function() { 
						var msg = "";
		
						if($("#itemCode").val() == "") { msg = msg + "- Invalid Item Code<br/>"; }
						if(isNaN($("#itemQty").val()) == true) { msg = msg + "- Invalid Quantity<br/>"; }
		
						if(msg != '') {
							parent.sendErrorMessage(msg);
						
						} else {
							if(confirm("Are you sure you want to save changes made to this entry?") == true) {
								$.post("sw.datacontrol.php", { 
									mod: "updateItem", 
									lid: arr[0],
									sw_no: $("#sw_no").val(), 
									trace_no: $("#trace_no").val(), 
									item: $("#itemCode").val(), 
									description: $("#itemDescription").val(), 
									unit: $("#itemUnit").val(), 
									qty: $("#itemQty").val(),
									lot_no: $("#itemLotNo").val(), 
									expiry: $("#itemExpiry").val(), 
									sid: Math.random() }, 
								function(gt) {
									redrawDataTable();
									$("#grandTotal").val(gt);
									$("#frmItemEntry").trigger("reset");
									
								});
							}
						}
					},
					"Cancel": function() { $(this).dialog("close"); $("#frmItemEntry").trigger("reset"); }
				}
			
			});	
		
		},"json");

		$('#itemDescription').autocomplete({
			source:'suggestItemsCost.php', 
			minLength:3,
			select: function(event,ui) {
				$("#itemCode").val(ui.item.item_code);
				$("#itemUnit").val(decodeURIComponent(ui.item.unit));
			}
		});
	}
}

function finalizeSW(sw_no,uid) {
	$.post("sw.datacontrol.php", { mod: "check4print", sw_no: $("#sw_no").val(), sid: Math.random() }, function(data) { 
		if(data == "noerror") {
			if(confirm("Are you sure you want to finalize this Stocks Withdrawal Slip?") == true) {
				$.post("sw.datacontrol.php", { mod: "finalizeSW", sw_no: $("#sw_no").val(), type: $("#ref_type").val(), sw_date: $("#sw_date").val(), remarks: $("#remarks").val(), sid: Math.random() }, function() {
					parent.viewSW($("#sw_no").val());
				});
			}
		} else {
			switch(data) {
				case "head": parent.sendErrorMessage("Unable to print document. Document is not yet saved..."); break;
				case "det": parent.sendErrorMessage("Unable to print document. It seems that you have not added any items yet to this Withdrawal Slip..."); break;
				case "both": parent.sendErrorMessage("There is nothing to print. Please make it sure you have saved entries you've made in this Withdrawal Slip..."); break;
			}
		}
	},"html");
}

function reopenSW(sw_no) {
	if(confirm("Are you sure you want to set this document to active status?") == true) {
		$.post("sw.datacontrol.php", { mod: "reopenSW", sw_no: sw_no, sid: Math.random() }, function() {
			parent.viewSW(sw_no);
		});
	}
}

function cancelSW(sw_no) {
	if(confirm("Are you sure you want to Cancel this document?") == true) {
		$.post("sw.datacontrol.php", { mod: "cancel", sw_no: sw_no, sid: Math.random() }, function(){
			alert("Stocks Withdrawal Slip Successfully Cancelled!");
			parent.showSW();
		});
	}
}

function reuseSW(sw_no) {
	if(confirm("Are you sure you want to Recycle this document?") == true) {
		$.post("sw.datacontrol.php", { mod: "reopenSW", sw_no: sw_no, sid: Math.random() }, function(){
			parent.viewSW(sw_no);
		});
	}
}