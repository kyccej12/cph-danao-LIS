
function computeItemAmount(qty) {
	if(isNaN(qty) == true) { parent.sendErrorMessage("-Invalid Quantity!"); $("#itemQty").val(''); }
	var cost = parseFloat(parent.stripComma($("#itemCost").val()));
	
	var amount = qty * cost;
		amount = amount.toFixed(2);

	$("#itemAmount").val(parent.kSeparator(amount));
}

function saveMRSHeader() {
	var msg = "";
	if($("#requested_by").val() == "") { msg = msg + "- Please specify the person who requested these items."; }
	if(msg != "") {
		parent.sendErrorMessage(msg);
	} else {
		$.post("mrs.datacontrol.php", { mod: "saveHeader", trace_no: $("#trace_no").val(), mrs_no: $("#mrs_no").val(), mrs_date: $("#mrs_date").val(),  needed_on: $("#needed_on").val(), requested_by: $("#requested_by").val(), remarks: $("#remarks").val(), sid: Math.random() }, function(data) {  $("#mrs_no").val(data); parent.popSaver(); },"html");
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
					$.post("mrs.datacontrol.php", { 
						mod: "addItem", 
						mrs_no: $("#mrs_no").val(), 
						trace_no: $("#trace_no").val(), 
						item: $("#itemCode").val(), 
						description: $("#itemDescription").val(), 
						unit: $("#itemUnit").val(), 
						qty: $("#itemQty").val(),
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
			$.post("mrs.datacontrol.php", { mod: "deleteLine", lid: arr[0], mrs_no: $("#mrs_no").val(), sid: Math.random() }, function(gt) { redrawDataTable(); $("#grandTotal").val(gt); });
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
		
		$.post("mrs.datacontrol.php", { mod: "retrieveLine", lid: arr[0], sid: Math.random() }, function(data) { 
			$("#itemDescription").val(parent.decodeEntities(data['description']));
			$("#itemCode").val(data['item_code']);
			$("#itemUnit").val(data['unit']);
			$("#itemQty").val(data['qty']);
		
			$("#itemEntry").dialog({title: "Update Line Entry", width: 440, resizable: false, modal: true, buttons: { 
					"Save Changes": function() { 
						var msg = "";
		
						if($("#itemCode").val() == "") { msg = msg + "- Invalid Item Code<br/>"; }
						if(isNaN($("#itemQty").val()) == true) { msg = msg + "- Invalid Quantity<br/>"; }
		
						if(msg != '') {
							parent.sendErrorMessage(msg);
						
						} else {
							if(confirm("Are you sure you want to save changes made to this entry?") == true) {
								$.post("mrs.datacontrol.php", { 
									mod: "updateItem", 
									lid: arr[0],
									mrs_no: $("#mrs_no").val(), 
									trace_no: $("#trace_no").val(), 
									item: $("#itemCode").val(), 
									description: $("#itemDescription").val(), 
									unit: $("#itemUnit").val(), 
									qty: $("#itemQty").val(),
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

function finalizeMRS(mrs_no,uid) {
	$.post("mrs.datacontrol.php", { mod: "check4print", mrs_no: $("#mrs_no").val(), sid: Math.random() }, function(data) { 
		if(data == "noerror") {
			if(confirm("Are you sure you want to finalize this Material Request Slip?") == true) {
				$.post("mrs.datacontrol.php", { mod: "finalize", mrs_no: $("#mrs_no").val(), sid: Math.random() }, function() {
					parent.viewMRS($("#mrs_no").val());
				});
			}
		} else {
			switch(data) {
				case "head": parent.sendErrorMessage("Unable to print document. Document is not yet saved..."); break;
				case "det": parent.sendErrorMessage("Unable to print document. It seems that you have not added any items yet to this Material Request Slip..."); break;
				case "both": parent.sendErrorMessage("There is nothing to print. Please make it sure you have saved entries you've made in this Material Request Slip..."); break;
			}
		}
	},"html");
}

function reopenMRS(mrs_no) {
	if(confirm("Are you sure you want to set this document to active status?") == true) {
		$.post("mrs.datacontrol.php", { mod: "reopen", mrs_no: mrs_no, sid: Math.random() }, function() {
			parent.viewMRS(mrs_no);
		});
	}
}

function cancelMRS(mrs_no) {
	if(confirm("Are you sure you want to Cancel this document?") == true) {
		$.post("mrs.datacontrol.php", { mod: "cancel", mrs_no: mrs_no, sid: Math.random() }, function(){
			alert("Stocks Withdrawal Slip Successfully Cancelled!");
			parent.showSW();
		});
	}
}

function reuseMRS(mrs_no) {
	if(confirm("Are you sure you want to Recycle this document?") == true) {
		$.post("mrs.datacontrol.php", { mod: "reopenSW", mrs_no: mrs_no, sid: Math.random() }, function(){
			parent.viewMRS(mrs_no);
		});
	}
}