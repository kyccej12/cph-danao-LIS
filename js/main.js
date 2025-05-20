/* Percentage of Screen Size */
var wWidth = $(window).width();
var yWidth = wWidth * 0.8;
var xWidth = wWidth * 0.95;

/* Silent Pringting Function Declaration */
var printService = new WebSocketPrinter();

var monthNames = [
	"January",
	"February",
	"March",
	"April",
	"May",
	"June",
    "July",
	"August",
	"September",
	"October",
	"November",
	"December"
];

var enumResultSelection = [
	"NEGATIVE",
	"POSITIVE",
	"REACTIVE",
	"NON-REACTIVE",
	"WEAKLY REACTIVE"
]

var posneg = [
	"NEGATIVE",
	"POSITIVE",
]

var patStatus = [
	"A.P.E",
	"FOR EMPLOYMENT",
	"WALKIN",
	"A.P.E COMPLETION",
	"CONSULTATION",
	"PERSONAL",
	"MANDATORY REQUIREMENT",
	"STUDENT",
	"SYMPTOMATIC",
	"ASSYMPTOMATIC"
]

var remarksSelection1 = [
	"FORWARD AND REVERSE BLOOD TYPING DONE"
]

var remarksSelection2 = [
	"TEST DONE TWICE"
]

/* Search Supplier on Reports */
$(document).ready(function($){

	$("#btype_remarks" ).autocomplete({
		source: remarksSelection1, minLength: 0
	}).focus(function() {
		$(this).data("uiAutocomplete").search($(this).val());
	});

	$("#ogtt_remarks, #typhoid_remarks" ).autocomplete({
		source: remarksSelection2, minLength: 0
	}).focus(function() {
		$(this).data("uiAutocomplete").search($(this).val());
	});

	$("#typhoid_igg, #typhoid_igm" ).autocomplete({
		source: posneg, minLength: 0
	}).focus(function() {
		$(this).data("uiAutocomplete").search($(this).val());
	});

	$("#pt_remarks" ).autocomplete({
		source: posneg, minLength: 0
	}).focus(function() {
		$(this).data("uiAutocomplete").search($(this).val());
	});
  
  
	/* Query for new file from Machines */
    myInterval = setInterval(checkHL7Messages, 30000);
	
	$("#tmp_date").datepicker();

	$('#phleb_by').autocomplete({
		source:'suggestEmployee.php', 
		minLength:3
	});

	$("#pt_result, #hav_result_igm, #hav_result_igg").autocomplete({
		 source: enumResultSelection
	});

	$("#dengue_result, #dengue_result2, #dengue_result3").autocomplete({
		source: posneg
   });

	$("#enum_patientstat, #btype_patientstat").autocomplete({
		source: patStatus
   });

});

function checkHL7Messages() {
	$.post("inbound/checkinbound.php");
	$.post("inbound/checklogger.php");
}

function popSaver() {
	$('#popSaver').fadeIn('fast').delay(1000).fadeOut('slow');
}

function closeDialog(frame) {
	$(frame).dialog("close");
}

function decodeEntities(encodedString) {
    var translate_re = /&(nbsp|amp|quot|lt|gt);/g;
    var translate = {
        "nbsp":" ",
        "amp" : "&",
        "quot": "\"",
        "lt"  : "<",
        "gt"  : ">"
    };
    return encodedString.replace(translate_re, function(match, entity) {
        return translate[entity];
    }).replace(/&#(\d+);/gi, function(match, numStr) {
        var num = parseInt(numStr, 10);
        return String.fromCharCode(num);
    });
}

function stripComma(val) {
	return val.replace(/,/g,"");
}

function kSeparator(val) {
	var val = parseFloat(val);
		val = val.toFixed(2);
	var a = val.split(".");
	var kValue = a[0];
	//if(a[1] == '' || a[1] == 'undefined') { a[1] = '00'; }

	var sRegExp = new RegExp('(-?[0-9]+)([0-9]{3})');
	while(sRegExp.test(kValue)) {
		kValue = kValue.replace(sRegExp, '$1,$2');
	}

	if(a[1] != "") {
		kValue = kValue + "." + a[1]; 
		return kValue;
	} else {
		return kValue + ".00";
	}
}
	
function addCommas(nStr)
{
	nStr += '';
	x = nStr.split('.');
	x1 = x[0];
	x2 = x.length > 1 ? '.' + x[1] : '';
	var rgx = /(\d+)(\d{3})/;
	while (rgx.test(x1)) {
		x1 = x1.replace(rgx, '$1' + ',' + '$2');
	}
	return x1 + x2;
}	

function sendErrorMessage(msg) {
	$("#message").html(msg);
	$("#errorMessage").dialog({
		width: 400,
		resizable: false,
		modal: true,
		buttons: {
			"OK": function() { $(this).dialog("close"); }
		}
	})
}

function showLoaderMessage() {
	$("#loaderMessage").dialog({ show: 'fade', width: 480, height: 180, closable: false, modal: true,  open: function(event, ui) {
        $(".ui-dialog-titlebar-close", ui.dialog | ui).hide();
    }});
}


function showMenu() {
	$("#mainMenu").dialog({show: 'fade', title: "Main Menu", width: 1024, resizable: false }).dialogExtend({
		"closable" : true,
		"maximizable" : false,
		"minimizable" : true
	});
}

/* Users */
function showUsers() {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='user.master.php'></iframe>";
	$("#userlist").html(txtHTML);
	$("#userlist").dialog({title: "System Users", width: xWidth, height: 540, resizable: false }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}

function showChangePass() {
	$("#userChangePass").dialog({ title: "Update Password", width: 480, height: 190, resizable: false, modal: true, buttons: {
					"Update my Password": function() {
						var msg = "";

						if($("#pass1").val() == "" || $("#pass2").val() == "") { msg = msg + "The system cannot accept empty password.<br/>"; }
						if($("#pass1").val() != $("#pass2").val()) { msg = msg + "New Passwords do not match.<br/>"; }
					
						if(msg!="") {
							sendErrorMessage(msg);
						} else {

							$.post("src/sjerp.php", { mod: "changePassword", uid:  $("#myUID").val(), pass: $("#pass1").val(), sid: Math.random() },function() {
								alert("You have successfully updated your password!");
								$("#userChangePass").dialog("close");
							});
						}
					},
					"Continue Without Changing Password": function () { $(this).dialog("close"); }
				} }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true,
	});
}
function addUser() {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='user.details.php'></iframe>";
	$("#userdetails").html(txtHTML);
	$("#userdetails").dialog({title: "System User Info.", width: 400, height: 260, resizable: false }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true,
	});
}

function viewUserInfo(eid) {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='user.update.php?eid="+eid+"'></iframe>";
	$("#userdetails").html(txtHTML);
	$("#userdetails").dialog({title: "System User Info.", width: 400, height: 260, resizable: false }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true,
	});
}


function showUserDetails(uid) {
	var uname;
	$.post("src/sjerp.php", { mod: "getUinfo", uid: uid, sid: Math.random() }, function(data) {
		var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='user.rights.php?uid="+uid+"'></iframe>";
		$("#userrights").html(txtHTML);
		$("#userrights").dialog({title: "User Access Rights ("+data+")", width: 800, height: 480, resizable: false}).dialogExtend({
			"closable" : true,
		    "maximizable" : false,
		    "minimizable" : true,
		});
	 },"html");
}

/* Inventory Management */
function showItems(icode) {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='items.master.php?sid="+Math.random()+"&icode="+icode+"'></iframe>";
	$("#itemlist").html(txtHTML);
	$("#itemlist").dialog({title: "Supplies & Materials", width: xWidth, height: 500,resizable: false }).dialogExtend({
		"closable" : true,
	    "maximizable" : true,
	    "minimizable" : true
	});
}

function showItemInfo(rid) {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='items.details.php?id="+rid+"&mod=1&sid="+Math.random()+"'></iframe>";
	$("#itemdetails").html(txtHTML);
	$("#itemdetails").dialog({title: "Product Details", width: 1120, height: 520, resizable: false }).dialogExtend({
		"closable" : true,
		"maximizable" : false,
		"minimizable" : true
	});
}

/* Laboratory Management */
function showServices(code) {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='services.php?sid="+Math.random()+"&code="+code+"'></iframe>";
	$("#serviceslist").html(txtHTML);
	$("#serviceslist").dialog({title: "List of Services", width: yWidth, height: 500,resizable: false }).dialogExtend({
		"closable" : true,
	    "maximizable" : true,
	    "minimizable" : true
	});
}

function showServiceInfo(id) {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='service.details.php?id="+id+"&mod=1&sid="+Math.random()+"'></iframe>";
	$("#servicesdetails").html(txtHTML);
	$("#servicesdetails").dialog({title: "Service Details", width: 1120, height: 520, resizable: false }).dialogExtend({
		"closable" : true,
		"maximizable" : false,
		"minimizable" : true
	});
}

function showLabCollection() {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='phleb.list.php?sid="+Math.random()+"'></iframe>";
	$("#report1").html(txtHTML);
	$("#report1").dialog({title: "Doctor's Laboratory Requests", width: xWidth, height: 640, resizable: false }).dialogExtend({
		"closable" : true,
		"maximizable" : false,
		"minimizable" : true
	});
}

function printBarcode(serialno) {
	$.post("src/sjerp.php", { mod: "checkSerialStatus", serialno: serialno, sid: Math.random() }, function(result) {
		if(parseFloat(result['mycount']) == 0) {
			sendErrorMessage("It appears that this specimen record hasn't been saved yet.. Please click save and try to print the barcode again.");
		} else {
			
			
			var myprint = $.post("print/specimenbarcode.php", { id: serialno, sid: Math.random() }, function() {
				var myURL = "http://192.168.11.15/images/qrcodes/"+serialno+".pdf";

				printService.submit({
					'type': 'LABEL',
					'url': myURL,
				});

			});

			setTimeout(function() {
				$.post("src/sjerp.php", { mod: "deleteCodes", serialno: serialno, sid: Math.random() });
			}, 5000);
			
		}
	});
}

function printBatchBarcode(filename) {

	var myURL = "http://192.168.11.15/"+filename;
	
	printService.submit({
		'type': 'LABEL',
		'url': myURL,
	});

	setTimeout(function() {
		$.post("src/sjerp.php", { mod: "deleteLabel", filename: filename, sid: Math.random() });
	}, 10000);

}

function showSamples() {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='samples.list.php?sid="+Math.random()+"'></iframe>";
	$("#samplelist").html(txtHTML);
	$("#samplelist").dialog({title: "Lab Samples", width: xWidth, height: 500,resizable: false }).dialogExtend({
		"closable" : true,
	    "maximizable" : true,
	    "minimizable" : true
	});
}

function showResults() {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='results.list.php?sid="+Math.random()+"'></iframe>";
	$("#resultlist").html(txtHTML);
	$("#resultlist").dialog({title: "Results & Releasing", width: xWidth, height: 500,resizable: false, autoOpen: true });
}

function showValidation() {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='validation.php?sid="+Math.random()+"'></iframe>";
	$("#validationList").html(txtHTML);
	$("#validationList").dialog({title: "Validate Lab Results", width: xWidth, height: 500,resizable: false }).dialogExtend({
		"closable" : true,
	    "maximizable" : true,
	    "minimizable" : true
	});
}

function writeResult(lid,code) {
	switch(code) {
		case "L007":
		case "L015":
		case "L086":
		case "L087":
		case "L096":	
		case "L099":
		case "L100":
			enumResult(lid,code);
		break;
		case "L037":
		case "L034":
			havResult(lid,code);
		break;
		case "L010":
			cbcResult(lid,code);
		break;
		case "L999":
		case "L071":
		case "L080":
		case "L021":
		case "L004":
		case "L005":
		case "L113":
		case "L132":
		case "L001":
		case "L020":
		case "L133":
		case "L003":
		case "L109":
		case "L029":
		case "L028":
		case "L134":
		case "L023":
		case "L135":
		case "L019":
		case "L032":
		case "L027":
		case "L110":
		case "L139":
		case "L016":
		case "L155":
		case "L158":
		case "L159":
		case "L025":
		case "L006":
		case "L161":
		case "L162":
			bloodChem(lid,code);
		break;
		case "L044":
		case "L045":
		case "L047":
		case "L048":
		case "L070":
		case "L072":
		case "L073":
		case "L074":
		case "L075":
		case "L078":
		case "L078":
		case "L081":
		case "L131":
		case "L137":
		case "L138":
		case "L022":
		case "L049":
		case "L140":
		case "L064":
		case "L075":
			spchem(lid,code);
		break;
		case "L141":
			ogttResult(lid,code);
		break;
		case "L012":
			uaResult(lid,code);
		break;
		case "L013":
			stoolExam(lid,code);
		break;
		case "L014":
			semenAnalysis(lid,code);
		break;
		case "L033":
		case "L039":
		case "L046":	
		case "L101":
		case "L063":
		case "L145":
		case "L043":
		case "L062":
			pregnancyResult(lid,code);
		break;
		case "L011":
		case "L052":
		case "L053":
			bloodTyping(lid,code);
		break;
		case "L079":
			dengueDuo(lid,code);
		break;
		case "L146":
		case "L050":
			coagulationResult(lid,code);
		break;
		case "L112":
		case "L136":
		case "L085":
			typhoidResult(lid,code);
		break;
		case "L152":
			crossMatchingResult(lid,code);
		break;
		case "L036":
		case "L154":
		case "L035":
		case "L038":
		case "L156":
			abgResult(lid,code);
		break;
		default:
			singleValueResult(lid,code);
		break;
	}
}

function validateResult(lid,code) {
	switch(code) {
		case "L007":
		case "L015":
		case "L086":
		case "L087":
		case "L096":	
		case "L099":
		case "L100":
			validateEnumResult(lid,code);
		break;
		case "L037":
		case "L034":
			validateHavResult(lid,code);
		break;
		case "L010":
			cbcResult(lid,code);
		break;
		case "L999":
		case "L080":
		case "L071":
		case "L021":
		case "L004":
		case "L005":
		case "L113":
		case "L132":
		case "L001":
		case "L020":
		case "L133":
		case "L003":
		case "L109":
		case "L029":
		case "L028":
		case "L134":
		case "L023":
		case "L135":
		case "L019":
		case "L032":
		case "L027":
		case "L110":
		case "L139":
		case "L016":
		case "L155":
		case "L158":
		case "L159":
		case "L025":
		case "L006":
		case "L161":
		case "L162":
			validateBloodChem(lid,code);
		break;
		case "L012":
			validateUaResult(lid,code);
		break;
		case "L013":
			validateStoolExam(lid,code);
		break;
		case "L014":
			validateSemenAnalysis(lid,code);
		break;
		case "L033":
		case "L039":
		case "L046":	
		case "L101":
		case "L063":
		case "L145":
		case "L043":
		case "L062":
			validatePregnancyResult(lid,code);
		break;
		case "L044":
		case "L045":
		case "L047":
		case "L048":
		case "L072":
		case "L073":
		case "L074":
		case "L075":
		case "L078":
		case "L078":
		case "L081":
		case "L131":
		case "L137":
		case "L138":
		case "L022":
		case "L070":
		case "L049":
		case "L140":
		case "L064":
		case "L075":
			validateSPChem(lid,code);
		break;
		case "L011":
		case "L052":
		case "L053":
			validateBloodtype(lid,code);
		break;
		case "L079":
			validateDengueDuo(lid,code);
		break;
		case "L146":
		case "L050":
			validateCoagulationResult(lid,code);
		break;
		case "L141":
			validateOGTTResult(lid,code);
		break;
		case "L112":
		case "L136":
		case "L085":
			validateTyphoidResult(lid,code);
		break;
		case "L152":
			validateCrossMatchingResult(lid,code);
		break;
		case "L036":
		case "L154":
		case "L035":
		case "L038":
		case "L156":
			validateAbgResult(lid,code);
		break;
		default:
			validateSingleValueResult(lid,code);
		break;
	}
}

function printResult(code,enccode,serialno) {
	let xCode = code.substring(0,1);
	if(xCode == 'X') {
		var txtHTML = "<iframe id='printResult' frameborder=0 width='100%' height='100%' src='print/result.xray.php?enccode="+enccode+"&code="+code+"&serialno="+serialno+"&sid="+Math.random()+"&sid="+Math.random()+"'></iframe>";
	} else {
		switch(code) {

			case "L010":
				var txtHTML = "<iframe id='printResult' frameborder=0 width='100%' height='100%' src='print/result.cbc.php?enccode="+enccode+"&code="+code+"&serialno="+serialno+"&sid="+Math.random()+"'></iframe>";
			break;
			case "L999":
			case "L080":
			case "L071":
			case "L021":
			case "L004":
			case "L005":
			case "L113":
			case "L132":
			case "L001":
			case "L020":
			case "L133":
			case "L003":
			case "L109":
			case "L029":
			case "L028":
			case "L134":
			case "L023":
			case "L135":
			case "L019":
			case "L032":
			case "L027":
			case "L110":
			case "L139":
			case "L016":
			case "L155":
			case "L158":
			case "L159":
			case "L025":
			case "L006":
			case "L161":
			case "L162":
				var txtHTML = "<iframe id='printResult' frameborder=0 width='100%' height='100%' src='print/result.bloodchem.php?enccode="+enccode+"&code="+code+"&serialno="+serialno+"&sid="+Math.random()+"'></iframe>";
			break;
			case "L141":
				var txtHTML = "<iframe id='printResult' frameborder=0 width='100%' height='100%' src='print/result.ogtt.php?enccode="+enccode+"&code="+code+"&serialno="+serialno+"&sid="+Math.random()+"'></iframe>";
			break;
			case "L012":
				var txtHTML = "<iframe id='printResult' frameborder=0 width='100%' height='100%' src='print/result.ua.php?enccode="+enccode+"&code="+code+"&serialno="+serialno+"&sid="+Math.random()+"&sid="+Math.random()+"'></iframe>";
			break;
			case "L013":
				var txtHTML = "<iframe id='printResult' frameborder=0 width='100%' height='100%' src='print/result.stool.php?enccode="+enccode+"&code="+code+"&serialno="+serialno+"&sid="+Math.random()+"'></iframe>";
			break;
			case "L014":
				semenAnalysis(lid,code);
			break;
			case "L007":
			case "L015":
			case "L086":
			case "L087":
			case "L100":
				var txtHTML = "<iframe id='printResult' frameborder=0 width='100%' height='100%' src='print/result.enum.php?so_no="+so_no+"&code="+code+"&serialno="+serialno+"&sid="+Math.random()+"&sid="+Math.random()+"'></iframe>";
			break;
			case "L037":
			case "L034":
				var txtHTML = "<iframe id='printResult' frameborder=0 width='100%' height='100%' src='print/result.hav.php?enccode="+enccode+"&code="+code+"&serialno="+serialno+"&sid="+Math.random()+"&sid="+Math.random()+"'></iframe>";
			break;
			
			case "L041":
				var txtHTML = "<iframe id='printResult' frameborder=0 width='100%' height='100%' src='print/result.reactives.php?so_no="+so_no+"&code="+code+"&serialno="+serialno+"&sid="+Math.random()+"&sid="+Math.random()+"'></iframe>";
			break;
			case "L011":
				var txtHTML = "<iframe id='printResult' frameborder=0 width='100%' height='100%' src='print/result.bt.php?enccode="+enccode+"&code="+code+"&serialno="+serialno+"&sid="+Math.random()+"&sid="+Math.random()+"'></iframe>";
			break;
			case "L033":
			case "L039":
			case "L046":	
			case "L101":
			case "L063":
			case "L145":
			case "L043":
			case "L062":
				var txtHTML = "<iframe id='printResult' frameborder=0 width='100%' height='100%' src='print/result.pt.php?enccode="+enccode+"&code="+code+"&serialno="+serialno+"&sid="+Math.random()+"&sid="+Math.random()+"'></iframe>";
			break;
			case "L044":
			case "L045":
			case "L047":
			case "L048":
			case "L072":
			case "L073":
			case "L074":
			case "L075":
			case "L078":
			case "L078":
			case "L081":
			case "L131":
			case "L137":
			case "L138":
			case "L022":
			case "L070":
			case "L049":
			case "L140":
			case "L064":
			case "L075":
				var txtHTML = "<iframe id='printResult' frameborder=0 width='100%' height='100%' src='print/result.specialchemistry.php?enccode="+enccode+"&code="+code+"&serialno="+serialno+"&sid="+Math.random()+"&sid="+Math.random()+"'></iframe>";
			break;
			case "L053":
			case "L053":
				var txtHTML = "<iframe id='printResult' frameborder=0 width='100%' height='100%' src='print/result.bt.php?enccode="+enccode+"&code="+code+"&serialno="+serialno+"&sid="+Math.random()+"&sid="+Math.random()+"'></iframe>";
			break;
			case "L079":
				var txtHTML = "<iframe id='printResult' frameborder=0 width='100%' height='100%' src='print/result.dengueduo.php?enccode="+enccode+"&code="+code+"&serialno="+serialno+"&sid="+Math.random()+"&sid="+Math.random()+"'></iframe>";
			break;
			case "L146":
			case "L050":
				var txtHTML = "<iframe id='printResult' frameborder=0 width='100%' height='100%' src='print/result.coagulation.php?enccode="+enccode+"&code="+code+"&serialno="+serialno+"&sid="+Math.random()+"&sid="+Math.random()+"'></iframe>";
			break;
			case "L112":
			case "L136":
			case "L085":
				var txtHTML = "<iframe id='printResult' frameborder=0 width='100%' height='100%' src='print/result.typhoid.php?enccode="+enccode+"&code="+code+"&serialno="+serialno+"&sid="+Math.random()+"&sid="+Math.random()+"'></iframe>";
			break;
			case "L152":
				var txtHTML = "<iframe id='printResult' frameborder=0 width='100%' height='100%' src='print/result.crossmatching.php?enccode="+enccode+"&code="+code+"&serialno="+serialno+"&sid="+Math.random()+"&sid="+Math.random()+"'></iframe>";
			break;
			case "L036":
			case "L154":
			case "L035":
			case "L038":
			case "L156":
				var txtHTML = "<iframe id='printResult' frameborder=0 width='100%' height='100%' src='print/result.abg.php?enccode="+enccode+"&code="+code+"&serialno="+serialno+"&sid="+Math.random()+"&sid="+Math.random()+"'></iframe>";
			break;
			default:
				var txtHTML = "<iframe id='printResult' frameborder=0 width='100%' height='100%' src='print/result.singlevalue.php?enccode="+enccode+"&code="+code+"&serialno="+serialno+"&sid="+Math.random()+"&sid="+Math.random()+"'></iframe>";
			break;
		}
	}
	
	$("#report3").html(txtHTML);
	$("#report3").dialog({title: "Print Result", width: 560, height: 620, resizable: true }).dialogExtend({
		"closable" : true,
		"maximizable" : true,
		"minimizable" : true
	});

}

function writeImagingResult(lid,code) {
	$("#descResult").html("<iframe id='frmResult' frameborder=0 width='100%' height='100%' src='result.descriptive.php?lid="+lid+"'></iframe>");
	$("#descResult").dialog({
		title: "Write Result",
		width: 1024,
		height: 695,
		resizeable: false,
		modal: false
	});
}

// function cbsResult(lid,code) {

// 	$("#cbs_date").datepicker();
	
// 	$.post("src/sjerp.php", {
// 		mod: "resultSingle",
// 		submod: "cbs",
// 		lid: lid,
// 		sid: Math.random() },
// 		function(data) {

// 			$("#cbs_enccode").val(data['enccode']);
// 			$("#cbs_sodate").val(data['orderdate']);
// 			$("#cbs_pid").val(data['hpercode']);
// 			$("#cbs_pname").val(decodeURIComponent(data['pname']));
// 			$("#cbs_gender").val(data['sex']);
// 			$("#cbs_birthdate").val(data['bday']);
// 			$("#cbs_age").val(data['age']);
// 			$("#cbs_physician").val(data['physician']);
// 			$("#cbs_procedure").val(data['procedure']);
// 			$("#cbs_code").val(data['code']);
// 			$("#cbs_primecarecode").val(data['primecarecode']);
// 			$("#cbs_spectype").val(data['sampletype']);
// 			$("#cbs_serialno").val(data['serialno']);
// 			$("#cbs_extractdate").val(data['exdate']);
// 			$("#cbs_extracttime").val(data['extractime']);
// 			$("#cbs_extractby").val(data['extractby']);
// 			$("#cbs_location").val(data['location']);
// 			$("#cbs_attribute").val(data['attribute']);
// 			$("#cbs_time").val(data['time']);
// 			$("#cbs_result").val(data['result']);
// 			$("#cbs_remarks").val(data['remarks']);

// 			var dis = $("#cbs").dialog({
// 				title: "Write Result",
// 				width: 1024,
// 				height: 600,
// 				resizeable: false,
// 				modal: true,
// 				buttons: [
// 					{
// 						text: "Save Result Pending Validation",
// 						icons: { primary: "ui-icon-check" },
// 						click: function() {
						
							
// 							if($("cbs_time").val() == '') {
// 								parent.sendErrorMessage("Time Value is empty!");
// 							} else {
// 								if(confirm("Are you sure you want save this data?") == true) {
// 									var dataString = $("#frmCBS").serialize();
// 										dataString = "mod=saveCBSResult&" + dataString;
// 										$.ajax({
// 											type: "POST",
// 											url: "src/sjerp.php",
// 											data: dataString,
// 											success: function() {
// 											alert("Result Successfully Saved!");
// 											dis.dialog("close");
// 											$("#frmCBS").trigger("reset");
// 										}
// 									});
// 								}
// 							}

// 						}
// 					},
// 					{
// 						text: "Print Result",
// 						icons: { primary: "ui-icon-print" },
// 						click: function() {
							
// 							var enccode = $("#cbs_enccode").val();
// 							var code = $("#cbs_code").val();
// 							var serialno = $("#cbs_serialno").val();

// 							var txtHTML = "<iframe id='printSingleValue' frameborder=0 width='100%' height='100%' src='print/result.cbs.php?enccode="+enccode+"&code="+code+"&serialno="+serialno+"&sid="+Math.random()+"&sid="+Math.random()+"'></iframe>";
// 							$("#report5").html(txtHTML);
// 							$("#report5").dialog({title: "Result - "+ $("#cbs_procedure").val() +"", width: 560, height: 620, resizable: true }).dialogExtend({
// 								"closable" : true,
// 								"maximizable" : true,
// 								"minimizable" : true
// 							});

// 						 }
// 					},
// 					{
// 						text: "Close",
// 						icons: { primary: "ui-icon-closethick" },
// 						click: function() { $(this).dialog("close"); $("#frmCBS").trigger("reset"); }
// 					}
// 				]
// 			});

// 		},"json"
// 	);
// }

// function validateCbsResult(lid,code) {

// 	$("#cbs_date").datepicker();
	
// 	$.post("src/sjerp.php", {
// 		mod: "resultSingle",
// 		submod: "cbs",
// 		lid: lid,
// 		sid: Math.random() },
// 		function(data) {

// 			$("#cbs_enccode").val(data['enccode']);
// 			$("#cbs_sodate").val(data['orderdate']);
// 			$("#cbs_pid").val(data['hpercode']);
// 			$("#cbs_pname").val(decodeURIComponent(data['pname']));
// 			$("#cbs_gender").val(data['sex']);
// 			$("#cbs_birthdate").val(data['bday']);
// 			$("#cbs_age").val(data['age']);
// 			$("#cbs_physician").val(data['physician']);
// 			$("#cbs_procedure").val(data['procedure']);
// 			$("#cbs_code").val(data['code']);
// 			$("#cbs_primecarecode").val(data['primecarecode']);
// 			$("#cbs_spectype").val(data['sampletype']);
// 			$("#cbs_serialno").val(data['serialno']);
// 			$("#cbs_extractdate").val(data['exdate']);
// 			$("#cbs_extracttime").val(data['extractime']);
// 			$("#cbs_extractby").val(data['extractby']);
// 			$("#cbs_location").val(data['location']);
// 			$("#cbs_attribute").val(data['attribute']);
// 			$("#cbs_time").val(data['time']);
// 			$("#cbs_result").val(data['result']);
// 			$("#cbs_remarks").val(data['remarks']);

// 			var dis = $("#cbs").dialog({
// 				title: "Write Result",
// 				width: 1024,
// 				height: 600,
// 				resizeable: false,
// 				modal: true,
// 				buttons: [
// 					{
// 						text: "Save Result Pending Validation",
// 						icons: { primary: "ui-icon-check" },
// 						click: function() {
						
							
// 							if($("cbs_result").val() == '') {
// 								parent.sendErrorMessage("Result Value is empty!");
// 							} else {
// 								if(confirm("Are you sure you want save this data?") == true) {
// 									var dataString = $("#frmCBS").serialize();
// 										dataString = "mod=validateCBSResult&" + dataString;
// 										$.ajax({
// 											type: "POST",
// 											url: "src/sjerp.php",
// 											data: dataString,
// 											success: function() {
// 											alert("Result Successfully Saved!");

// 											var enccode = $("#cbs_enccode").val();
// 											var code = $("#cbs_code").val();
// 											var serialno = $("#cbs_serialno").val();

// 											var txtHTML = "<iframe id='frmCBS' frameborder=0 width='100%' height='100%' src='print/result.cbs.php?enccode="+enccode+"&code="+code+"&serialno="+serialno+"&sid="+Math.random()+"&sid="+Math.random()+"'></iframe>";
// 											$("#report5").html(txtHTML);
// 											$("#report5").dialog({title: "Result - "+ $("#cbs_procedure").val() +"", width: 560, height: 620, resizable: true }).dialogExtend({
// 												"closable" : true,
// 												"maximizable" : true,
// 												"minimizable" : true
// 											});

// 											dis.dialog("close");
// 											$("#frmCBS").trigger("reset");
// 											showResults();
// 										}
// 									});
// 								}
// 							}

// 						}
// 					},
// 					{
// 						text: "Print Result",
// 						icons: { primary: "ui-icon-print" },
// 						click: function() {
							
// 							var enccode = $("#cbs_enccode").val();
// 							var code = $("#cbs_code").val();
// 							var serialno = $("#cbs_serialno").val();

// 							var txtHTML = "<iframe id='frmCBS' frameborder=0 width='100%' height='100%' src='print/result.cbs.php?enccode="+enccode+"&code="+code+"&serialno="+serialno+"&sid="+Math.random()+"&sid="+Math.random()+"'></iframe>";
// 							$("#report5").html(txtHTML);
// 							$("#report5").dialog({title: "Result - "+ $("#cbs_procedure").val() +"", width: 560, height: 620, resizable: true }).dialogExtend({
// 								"closable" : true,
// 								"maximizable" : true,
// 								"minimizable" : true
// 							});

// 						 }
// 					},
// 					{
// 						text: "Close",
// 						icons: { primary: "ui-icon-closethick" },
// 						click: function() { $(this).dialog("close"); $("#frmCBS").trigger("reset"); }
// 					}
// 				]
// 			});

// 		},"json"
// 	);
// }

/* Abg REsult */
function abgResult(lid,code) {
	
	$("#abgResult").html("<iframe id='frmABG' frameborder=0 width='100%' height='100%' src='result.abg.php?lid="+lid+"'></iframe>");
	
	$("#abgResult").dialog({
		title: "Write Result",
		width: 1124,
		height: 670,
		resizeable: false,
		modal: true,
		buttons: [
			{
				text: "Save Result Pending Validation",
				icons: { primary: "ui-icon-check" },
				click: function() {
					var msg = '';

					if(confirm("Are you sure you want save this data?") == true) {
						var dataString = $('#frmABG').contents().find('#frmABG').serialize();
						dataString = "mod=saveAbgResult&" + dataString;
						$.ajax({
							type: "POST",
							url: "src/sjerp.php",
							data: dataString,
							success: function() {
								alert("Result Successfully Saved!");
								dis.dialog("close");
								$("#frmABG").trigger("reset");
							}
						});
					}
				}
			},
			{
				text: "Print Result",
				icons: { primary: "ui-icon-print" },
				click: function() {
					
					var enccode = $("#abg_enccode").val();
					var code = $("#abg_proccode").val();
					var serialno = $("#abg_serialno").val();

					var txtHTML = "<iframe id='printSingleValue' frameborder=0 width='100%' height='100%' src='print/result.abg.php?enccode="+enccode+"&code="+code+"&serialno="+serialno+"&sid="+Math.random()+"&sid="+Math.random()+"'></iframe>";
					$("#report5").html(txtHTML);
					$("#report5").dialog({title: "Result - "+ $("#abg_procedure").val() +"", width: 560, height: 620, resizable: true }).dialogExtend({
						"closable" : true,
						"maximizable" : true,
						"minimizable" : true
					});

				 }
			},
			{
				text: "Close",
				icons: { primary: "ui-icon-closethick" },
				click: function() { $(this).dialog("close"); $("#frmProthrombin").trigger("reset"); }
			}
		]
	});
}

function validateAbgResult(lid,code) {
	
	$("#abgResult").html("<iframe id='frmABG' frameborder=0 width='100%' height='100%' src='result.abg.php?lid="+lid+"'></iframe>");
	
	var dis = $("#abgResult").dialog({
		title: "Write Result",
		width: 1124,
		height: 670,
		resizeable: false,
		modal: true,
		buttons: [
			{
				text: "Save Changes & Mark Result as Validated",
				icons: { primary: "ui-icon-check" },
				click: function() {
					var msg = '';

					if(confirm("Are you sure you want save this data?") == true) {
						var dataString = $('#frmABG').contents().find('#frmABG').serialize();
						dataString = "mod=validateAbgResult&" + dataString;
						$.ajax({
							type: "POST",
							url: "src/sjerp.php",
							data: dataString,
							success: function() {
								alert("Result Successfully Marked as Validated!");
								showValidation();

								var serialno = $('#frmABG').contents().find('#abg_serialno').val();
								var enccode = $('#frmABG').contents().find('#abg_enccode').val();
								var code = $('#frmABG').contents().find('#abg_ihomis_code').val();

								var txtHTML = "<iframe id='prntXrayResult' frameborder=0 width='100%' height='100%' src='print/result.abg.php?enccode="+enccode+"&serialno="+serialno+"&code="+code+"&sid="+Math.random()+"'></iframe>";
								$("#report3").html(txtHTML);
								$("#report3").dialog({title: "Print - ABG RESULT", width: 560, height: 620, resizable: true }).dialogExtend({
									"closable" : true,
									"maximizable" : true,
									"minimizable" : true
								});
				
								dis.dialog("close");
							}
						});
					}
				}
			},
			{
				text: "Print Result",
				icons: { primary: "ui-icon-print" },
				click: function() {
					
					var serialno = $('#frmABG').contents().find('#abg_serialno').val();
					var enccode = $('#frmABG').contents().find('#abg_enccode').val();
					var proccode = $('#frmABG').contents().find('#abg_ihomis_code').val();


					var txtHTML = "<iframe id='prntXrayResult' frameborder=0 width='100%' height='100%' src='print/result.abg.php?enccode="+enccode+"&serialno="+serialno+"&code="+proccode+"&sid="+Math.random()+"'></iframe>";
					$("#report3").html(txtHTML);
					$("#report3").dialog({title: "Print - ABG RESULT", width: 560, height: 620, resizable: true }).dialogExtend({
						"closable" : true,
						"maximizable" : true,
						"minimizable" : true
					});
					
				 }
			},
			{
				text: "Close",
				icons: { primary: "ui-icon-closethick" },
				click: function() { $(this).dialog("close"); }
			}
		]
	});
}

function singleValueResult(lid,code) {

	$("#sresult_date").datepicker();
	
	$.post("src/sjerp.php", {
		mod: "resultSingle",
		submod: "labSingle",
		lid: lid,
		sid: Math.random() },
		function(data) {

			$("#sresult_enccode").val(data['enccode']);
			$("#sresult_orderdate").val(data['orderdate']);
			$("#sresult_pid").val(data['hpercode']);
			$("#sresult_pname").val(decodeURIComponent(data['pname']));
			$("#sresult_gender").val(data['sex']);
			$("#sresult_birthdate").val(data['bday']);
			$("#sresult_age").val(data['age']);
			$("#sresult_physician").val(data['physician']);
			$("#sresult_procedure").val(data['procedure']);
			$("#sresult_code").val(data['code']);
			$("#sresult_primecarecode").val(data['primecarecode']);
			$("#sresult_spectype").val(data['sampletype']);
			$("#sresult_serialno").val(data['serialno']);
			$("#sresult_extractdate").val(data['exdate']);
			$("#sresult_extracttime").val(data['extractime']);
			$("#sresult_by").val(data['extractby']);
			$("#sresult_location").val(data['location']);
			$("#sresult_attribute").val(data['attribute']);
			$("#sresult_unit").val(data['unit']);
			$("#sresult_value").val(data['value']);
			$("#sresult_remarks").val(data['remarks']);
			$("#sresult_location").val(data['location']);

			var dis = $("#singleValueResult").dialog({
				title: "Write Result",
				width: 540,
				resizeable: false,
				modal: true,
				buttons: [
					{
						text: "Save Result Pending Validation",
						icons: { primary: "ui-icon-check" },
						click: function() {
						
							
							if($("#sresult_value").val() == '') {
								parent.sendErrorMessage("Result Value is empty!");
							} else {
								if(confirm("Are you sure you want save this data?") == true) {
									var dataString = $("#frmsingleValue").serialize();
										dataString = "mod=saveSingleValueResult&" + dataString;
										$.ajax({
											type: "POST",
											url: "src/sjerp.php",
											data: dataString,
											success: function() {
											alert("Result Successfully Saved!");
											dis.dialog("close");
											$("#singleValueResult").trigger("reset");
										}
									});
								}
							}

						}
					},
					{
						text: "Print Result",
						icons: { primary: "ui-icon-print" },
						click: function() {
							
							var enccode = $("#sresult_enccode").val();
							var code = $("#sresult_code").val();
							var serialno = $("#sresult_serialno").val();

							var txtHTML = "<iframe id='printSingleValue' frameborder=0 width='100%' height='100%' src='print/result.singlevalue.php?enccode="+enccode+"&code="+code+"&serialno="+serialno+"&sid="+Math.random()+"&sid="+Math.random()+"'></iframe>";
							$("#report5").html(txtHTML);
							$("#report5").dialog({title: "Result - "+ $("#sresult_procedure").val() +"", width: 560, height: 620, resizable: true }).dialogExtend({
								"closable" : true,
								"maximizable" : true,
								"minimizable" : true
							});

						 }
					},
					{
						text: "Close",
						icons: { primary: "ui-icon-closethick" },
						click: function() { $(this).dialog("close"); $("#frmsingleValue").trigger("reset"); }
					}
				]
			});

		},"json"
	);
}

function validateSingleValueResult(lid,code) {

	$.post("src/sjerp.php", {
		mod: "resultSingle",
		submod: "labSingle",
		lid: lid,
		sid: Math.random() },
		function(data) {

			$("#sresult_enccode").val(data['enccode']);
			$("#sresult_orderdate").val(data['orderdate']);
			$("#sresult_pid").val(data['hpercode']);
			$("#sresult_pname").val(decodeURIComponent(data['pname']));
			$("#sresult_gender").val(data['sex']);
			$("#sresult_birthdate").val(data['bday']);
			$("#sresult_age").val(data['age']);
			$("#sresult_physician").val(data['physician']);
			$("#sresult_procedure").val(data['procedure']);
			$("#sresult_code").val(data['code']);
			$("#sresult_primecarecode").val(data['primecarecode']);
			$("#sresult_spectype").val(data['sampletype']);
			$("#sresult_serialno").val(data['serialno']);
			$("#sresult_extractdate").val(data['exdate']);
			$("#sresult_extracttime").val(data['extractime']);
			$("#sresult_by").val(data['extractby']);
			$("#sresult_location").val(data['location']);
			$("#sresult_attribute").val(data['attribute']);
			$("#sresult_unit").val(data['unit']);
			$("#sresult_value").val(data['value']);
			$("#sresult_remarks").val(data['remarks']);
			$("#sresult_location").val(data['location']);

			var dis = $("#singleValueResult").dialog({
				title: "Validate Result",
				width: 540,
				resizeable: false,
				modal: true,
				buttons: [
					{
						text: "Save Changes & Mark as Validated",
						icons: { primary: "ui-icon-check" },
						click: function() {
						
							
							if($("#sresult_value").val() == '') {
								parent.sendErrorMessage("Result Value is empty!");
							} else {
								if(confirm("Are you sure you want save this data?") == true) {
									var dataString = $("#frmsingleValue").serialize();
										dataString = "mod=validateSingleValueResult&" + dataString;
										$.ajax({
											type: "POST",
											url: "src/sjerp.php",
											data: dataString,
											success: function() {
											alert("Result Successfully Marked as Validated!");

											var so_no = $("#sresult_sono").val();
											var code = $("#sresult_code").val();
											var serialno = $("#sresult_serialno").val();

											var txtHTML = "<iframe id='printSingleValue' frameborder=0 width='100%' height='100%' src='print/result.singlevalue.php?so_no="+so_no+"&code="+code+"&serialno="+serialno+"&sid="+Math.random()+"&sid="+Math.random()+"'></iframe>";
											$("#report5").html(txtHTML);
											$("#report5").dialog({title: "Result - "+ $("#sresult_procedure").val() +"", width: 560, height: 620, resizable: true }).dialogExtend({
												"closable" : true,
												"maximizable" : true,
												"minimizable" : true
											});
											
											dis.dialog("close");
											$("#singleValueResult").trigger("reset");
										}
									});
								}
							}

						}
					},
					{
						text: "Print Result",
						icons: { primary: "ui-icon-print" },
						click: function() {
							
							var so_no = $("#sresult_sono").val();
							var code = $("#sresult_code").val();
							var serialno = $("#sresult_serialno").val();

							var txtHTML = "<iframe id='printSingleValue' frameborder=0 width='100%' height='100%' src='print/result.singlevalue.php?so_no="+so_no+"&code="+code+"&serialno="+serialno+"&sid="+Math.random()+"&sid="+Math.random()+"'></iframe>";
							$("#report5").html(txtHTML);
							$("#report5").dialog({title: "Result - "+ $("#sresult_procedure").val() +"", width: 560, height: 620, resizable: true }).dialogExtend({
								"closable" : true,
								"maximizable" : true,
								"minimizable" : true
							});

						 }
					},
					{
						text: "Close",
						icons: { primary: "ui-icon-closethick" },
						click: function() { $(this).dialog("close"); $("#frmsingleValue").trigger("reset"); }
					}
				]
			});

		},"json"
	);
}

/* Crossmatching */

function crossMatchingResult(lid,code) {

	$("#crossMatching").html("<iframe id='frmCrossMatching' frameborder=0 width='100%' height='100%' src='result.crossMatching.php?lid="+lid+"'></iframe>");
	
	var dis = $("#crossMatching").dialog({
		title: "Write Result",
		width: 1124,
		height: 700,
		resizeable: false,
		modal: true,
		buttons: [
			{
				text: "Accept & Save Result Pending Validation",
				icons: { primary: "ui-icon-check" },
				click: function() {
					var msg = '';

					if(confirm("Are you sure you want save this data?") == true) {
						var msg = '';
						
						if($('#frmCrossMatching').contents().find("#cmr_examination").val() == '' ) { msg = msg + "- Invalid or Empty Value for <b>Examination</b> <br/>"; }
						if($('#frmCrossMatching').contents().find("#cmr_component").val() == '' ) { msg = msg + "- Invalid or Empty Value for <b>Blood Component</b><br/>"; }
						if($('#frmCrossMatching').contents().find("#cmr_compatibility").val() == '' ) { msg = msg + "- Invalid or Empty Value for <b>Compatibility Result</b><br/>"; }

						if(msg != '') {
							parent.sendErrorMessage(msg);
						} else {
							var dataString = $('#frmCrossMatching').contents().find('#frmCrossMatching').serialize();
							dataString = "mod=saveCrossMatching&" + dataString;
							$.ajax({
								type: "POST",
								url: "src/sjerp.php",
								data: dataString,
								success: function() {
									alert("Result Successfully Saved!");
									dis.dialog("close");
									$("#frmCrossMatching").trigger("reset");
								}
							});
						}
					}
				}
			},
			{
				text: "Print Result",
				icons: { primary: "ui-icon-print" },
				click: function() {
					
					var enccode = $('#frmCrossMatching').contents().find('#cmr_enccode').val();
					var serialno = $('#frmCrossMatching').contents().find('#cmr_serialno').val();
					var code = $('#frmCrossMatching').contents().find('#cmr_ihomis_code').val();

					var txtHTML = "<iframe id='prntXrayResult' frameborder=0 width='100%' height='100%' src='print/result.crossmatching.php?enccode="+enccode+"&serialno="+serialno+"&code="+code+"&sid="+Math.random()+"'></iframe>";
					$("#report3").html(txtHTML);
					$("#report3").dialog({title: "Print - CROSS MATCHING RESULT", width: 560, height: 620, resizable: true }).dialogExtend({
						"closable" : true,
						"maximizable" : true,
						"minimizable" : true
					});


				 }
			},
			{
				text: "Close",
				icons: { primary: "ui-icon-closethick" },
				click: function() { $(this).dialog("close"); }
			}
		]
	});
}

function validateCrossMatchingResult(lid,code) {

	$("#crossMatching").html("<iframe id='frmCrossMatching' frameborder=0 width='100%' height='100%' src='result.crossmatching.php?lid="+lid+"'></iframe>");
	
	var dis = $("#crossMatching").dialog({
		title: "Validate Result",
		width: 1124,
		height: 700,
		resizeable: false,
		modal: true,
		buttons: [
			{
				text: "Save Changes & Mark Result as Validated",
				icons: { primary: "ui-icon-check" },
				click: function() {
					var msg = '';

					if(confirm("Are you sure you want save this data?") == true) {
						var dataString = $('#frmCrossMatching').contents().find('#frmCrossMatching').serialize();
					
						//var dataString = $("#frmDescResult").serialize();
						dataString = "mod=validateCrossMatching&" + dataString;
						$.ajax({
							type: "POST",
							url: "src/sjerp.php",
							data: dataString,
							success: function() {
								alert("Result Successfully Marked as Validated!");
								showValidation();
								

								var enccode = $('#frmCrossMatching').contents().find('#cmr_enccode').val();
								var serialno = $('#frmCrossMatching').contents().find('#cmr_serialno').val();
								var code = $('#frmCrossMatching').contents().find('#cmr_ihomis_code').val();

								var txtHTML = "<iframe id='prntXrayResult' frameborder=0 width='100%' height='100%' src='print/result.crossmatching.php?enccode="+enccode+"&serialno="+serialno+"&code="+code+"&sid="+Math.random()+"'></iframe>";
								$("#report3").html(txtHTML);
								$("#report3").dialog({title: "Print - CROSS MATCHING RESULT", width: 560, height: 620, resizable: true }).dialogExtend({
									"closable" : true,
									"maximizable" : true,
									"minimizable" : true
								});

								dis.dialog("close");

							}
						});
					}
				}
			},
			{
				text: "Print Result",
				icons: { primary: "ui-icon-print" },
				click: function() {
					
					var enccode = $('#frmCrossMatching').contents().find('#cmr_enccode').val();
					var serialno = $('#frmCrossMatching').contents().find('#cmr_serialno').val();
					var code = $('#frmCrossMatching').contents().find('#cmr_ihomis_code').val();

					var txtHTML = "<iframe id='prntXrayResult' frameborder=0 width='100%' height='100%' src='print/result.crossmatching.php?enccode="+enccode+"&serialno="+serialno+"&code="+code+"&sid="+Math.random()+"'></iframe>";
					$("#report3").html(txtHTML);
					$("#report3").dialog({title: "Print - CROSS MATCHING RESULT", width: 560, height: 620, resizable: true }).dialogExtend({
						"closable" : true,
						"maximizable" : true,
						"minimizable" : true
					});


				 }
			},
			{
				text: "Close",
				icons: { primary: "ui-icon-closethick" },
				click: function() { $(this).dialog("close"); }
			}
		]
	});
}

/* Coagulation Test */

function coagulationResult(lid,code) {
	
	$("#coagulationResult").html("<iframe id='frmCoagulationResult' frameborder=0 width='100%' height='100%' src='result.coagulation.php?lid="+lid+"'></iframe>");
	
	$("#coagulationResult").dialog({
		title: "Write Result",
		width: xWidth,
		height: 640,
		resizeable: false,
		modal: true,
		buttons: [
			{
				text: "Save Result Pending Validation",
				icons: { primary: "ui-icon-check" },
				click: function() {
					var msg = '';

					if(confirm("Are you sure you want save this data?") == true) {
						var dataString = $('#frmCoagulationResult').contents().find('#frmCoagulationResult').serialize();
						dataString = "mod=saveCoagulationResult&" + dataString;
						$.ajax({
							type: "POST",
							url: "src/sjerp.php",
							data: dataString,
							success: function() {
								alert("Result Successfully Saved!");
								dis.dialog("close");
								$("#frmCoagulationResult").trigger("reset");
							}
						});
					}
				}
			},
			{
				text: "Print Result",
				icons: { primary: "ui-icon-print" },
				click: function() {
					
					var enccode = $("#coagulation_enccode").val();
					var code = $("#coagulation_proccode").val();
					var serialno = $("#coagulation_serialno").val();

					var txtHTML = "<iframe id='printSingleValue' frameborder=0 width='100%' height='100%' src='print/result.coagulation.php?enccode="+enccode+"&code="+code+"&serialno="+serialno+"&sid="+Math.random()+"&sid="+Math.random()+"'></iframe>";
					$("#report5").html(txtHTML);
					$("#report5").dialog({title: "Result - "+ $("#coagulation_procedure").val() +"", width: 560, height: 620, resizable: true }).dialogExtend({
						"closable" : true,
						"maximizable" : true,
						"minimizable" : true
					});

				 }
			},
			{
				text: "Close",
				icons: { primary: "ui-icon-closethick" },
				click: function() { $(this).dialog("close"); $("#frmProthrombin").trigger("reset"); }
			}
		]
	});
}

function validateCoagulationResult(lid,code) {
	
	$("#coagulationResult").html("<iframe id='frmCoagulationResult' frameborder=0 width='100%' height='100%' src='result.coagulation.php?lid="+lid+"'></iframe>");
	
	var dis = $("#coagulationResult").dialog({
		title: "Write Result",
		width: xWidth,
		height: 640,
		resizeable: false,
		modal: true,
		buttons: [
			{
				text: "Save Changes & Mark Result as Validated",
				icons: { primary: "ui-icon-check" },
				click: function() {
					var msg = '';

					if(confirm("Are you sure you want save this data?") == true) {
						var dataString = $('#frmCoagulationResult').contents().find('#frmCoagulationResult').serialize();
						dataString = "mod=validateCoagulationResult&" + dataString;
						$.ajax({
							type: "POST",
							url: "src/sjerp.php",
							data: dataString,
							success: function() {
								alert("Result Successfully Marked as Validated!");
								showValidation();

								var serialno = $('#frmCoagulationResult').contents().find('#coagulation_serialno').val();
								var enccode = $('#frmCoagulationResult').contents().find('#coagulation_enccode').val();
								var proccode = $('#frmCoagulationResult').contents().find('#coagulation_proccode').val();
								var dotime = $('#frmCoagulationResult').contents().find('#coagulation_dotime').val();


								$.post("src/sjerp.php", { mod: "retrieveSameSampleForPrint", proccode: proccode, enccode: enccode, serialno: serialno, dotime: dotime, sid: Math.random() }, function(res) {
									$("#otherTests").html(res);
								
									var dataString = $("#otherTests").serialize();
									window.open("print/result.coagulation.php?enccode="+enccode+"&serialno="+serialno+"&sid="+Math.random()+"&"+dataString+"","Coagulation Test Result","location=1,status=1,scrollbars=1,width=640,height=720");
								
								},"html");
				
								dis.dialog("close");
							}
						});
					}
				}
			},
			{
				text: "Print Result",
				icons: { primary: "ui-icon-print" },
				click: function() {
					
					var serialno = $('#frmCoagulationResult').contents().find('#coagulation_serialno').val();
					var enccode = $('#frmCoagulationResult').contents().find('#coagulation_enccode').val();
					var proccode = $('#frmCoagulationResult').contents().find('#coagulation_proccode').val();
					var dotime = $('#frmCoagulationResult').contents().find('#coagulation_dotime').val();


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
									window.open("print/result.coagulation.php?enccode="+enccode+"&serialno="+serialno+"&sid="+Math.random()+"&"+dataString+"","Coagulation Test Result","location=1,status=1,scrollbars=1,width=640,height=720");
								}
							}
						]
					});
					
				 }
			},
			{
				text: "Close",
				icons: { primary: "ui-icon-closethick" },
				click: function() { $(this).dialog("close"); }
			}
		]
	});
}

function enumResult(lid,code) {

	$("#enum_date").datepicker();
	
	$.post("src/sjerp.php", {
		mod: "resultSingle",
		submod: "enumResult",
		lid: lid,
		sid: Math.random() },
		function(data) {

			$("#enum_sono").val(data['myso']);
			$("#enum_sodate").val(data['sodate']);
			$("#enum_pid").val(data['mypid']);
			$("#enum_pname").val(decodeURIComponent(data['pname']));
			$("#enum_gender").val(data['gender']);
			$("#enum_birthdate").val(data['bday']);
			$("#enum_age").val(data['age']);
			$("#enum_patientstat").val(data['patientstatus']);
			$("#enum_physician").val(data['physician']);
			$("#enum_procedure").val(data['procedure']);
			$("#enum_code").val(data['code']);
			$("#enum_spectype").val(data['sampletype']);
			$("#enum_serialno").val(data['serialno']);
			$("#enum_testkit").val(data['testkit']);
			$("#enum_testkit_lotno").val(data['lotno']);
			$("#enum_testkit_expiry").val(data['expiry']);
			$("#enum_extractdate").val(data['exday']);
			$("#enum_extracttime").val(data['etime']);
			$("#enum_extractby").val(data['extractby']);
			$("#enum_result").val(data['result']);
			$("#enum_result_by").val(data['performed_by']);
			$("#enum_remarks").val(data['remarks']);

			var dis = $("#enumResult").dialog({
				title: "Write Result",
				width: 540,
				resizeable: false,
				modal: true,
				buttons: [
					{
						text: "Save Result Pending Validation",
						icons: { primary: "ui-icon-check" },
						click: function() {
							var msg = '';
	
							if(confirm("Are you sure you want save this data?") == true) {
							var dataString = $("#frmEnumResult").serialize();
								dataString = "mod=saveEnumResult&" + dataString;
								$.ajax({
									type: "POST",
									url: "src/sjerp.php",
									data: dataString,
									success: function() {
										alert("Result Successfully Saved!");
										$("#frmEnumResult").trigger("reset");
										dis.dialog("close");
										showValidation();
									}
								});
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

function validateEnumResult(lid,code) {
	
	$.post("src/sjerp.php", {
		mod: "resultSingle",
		submod: "enumResult",
		lid: lid,
		sid: Math.random() },
		function(data) {

			$("#enum_sono").val(data['myso']);
			$("#enum_sodate").val(data['sodate']);
			$("#enum_pid").val(data['mypid']);
			$("#enum_pname").val(decodeURIComponent(data['pname']));
			$("#enum_gender").val(data['gender']);
			$("#enum_birthdate").val(data['bday']);
			$("#enum_age").val(data['age']);
			$("#enum_patientstat").val(data['patient_stat']);
			$("#enum_physician").val(data['physician']);
			$("#enum_procedure").val(data['procedure']);
			$("#enum_code").val(data['code']);
			$("#enum_spectype").val(data['sampletype']);
			$("#enum_serialno").val(data['serialno']);
			$("#enum_testkit").val(data['testkit']);
			$("#enum_testkit_lotno").val(data['lotno']);
			$("#enum_testkit_expiry").val(data['expiry']);
			$("#enum_extractdate").val(data['exday']);
			$("#enum_extracttime").val(data['etime']);
			$("#enum_extractby").val(data['extractby']);
			$("#enum_result").val(data['result']);
			$("#enum_result_by").val(data['performed_by']);
			$("#enum_remarks").val(data['remarks']);

			var dis = $("#enumResult").dialog({
				title: "Validate Result",
				width: 540,
				resizeable: false,
				modal: true,
				buttons: [
					{
						text: "Save & Confirm Result",
						icons: { primary: "ui-icon-check" },
						click: function() {
							var msg = '';
	
							if(confirm("Are you sure you want save this data?") == true) {
							var dataString = $("#frmEnumResult").serialize();
								dataString = "mod=validateEnumResult&" + dataString;
								$.ajax({
									type: "POST",
									url: "src/sjerp.php",
									data: dataString,
									success: function() {
										alert("Result Successfully Confirmed & Published");
									}
								});
							}
						}
					},
					{
						text: "Print Result",
						icons: { primary: "ui-icon-print" },
						click: function() {
							
							var so_no = $("#sresult_sono").val();
							var code = $("#sresult_code").val();
							var serialno = $("#sresult_serialno").val();

							var txtHTML = "<iframe id='printSingleValue' frameborder=0 width='100%' height='100%' src='print/result.enum.php?so_no="+$("#enum_sono").val()+"&code="+$("#enum_code").val()+"&serialno="+$("#enum_serialno").val()+"&sid="+Math.random()+"&sid="+Math.random()+"'></iframe>";
							$("#report5").html(txtHTML);
							$("#report5").dialog({title: "Result - "+ $("#enum_procedure").val() +"", width: 560, height: 620, resizable: true }).dialogExtend({
								"closable" : true,
								"maximizable" : true,
								"minimizable" : true
							});

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

function havResult(lid,code) {

	$("#hav_date").datepicker();
	
	$.post("src/sjerp.php", {
		mod: "resultSingle",
		submod: "havResult",
		lid: lid,
		sid: Math.random() },
		function(data) {

			$("#hav_enccode").val(data['enccode']);
			$("#hav_sodate").val(data['orderdate']);
			$("#hav_pid").val(data['hmrno']);
			$("#hav_pname").val(decodeURIComponent(data['pname']));
			$("#hav_gender").val(data['sex']);
			$("#hav_birthdate").val(data['bday']);
			$("#hav_age").val(data['age']);
			$("#hav_patientstat").val(data['patientstatus']);
			$("#hav_physician").val(data['physician']);
			$("#hav_procedure").val(data['procedure']);
			$("#hav_code").val(data['code']);
			$("#hav_spectype").val(data['sampletype']);
			$("#hav_serialno").val(data['serialno']);
			$("#hav_extractdate").val(data['exdate']);
			$("#hav_extracttime").val(data['extractime']);
			$("#hav_extractby").val(data['extractby']);
			$("#hav_result_igm").val(data['hav_result_igm']);
			$("#hav_result_igg").val(data['hav_result_igg']);
			$("#hav_remarks").val(data['remarks']);
			$("#hav_location").val(data['location']);

			var dis = $("#havResult").dialog({
				title: "Write Result",
				width: 540,
				resizeable: false,
				modal: true,
				buttons: [
					{
						text: "Save Result Pending Validation",
						icons: { primary: "ui-icon-check" },
						click: function() {
							var msg = '';
	
							if(confirm("Are you sure you want save this data?") == true) {
							var dataString = $("#frmHavResult").serialize();
								dataString = "mod=saveHavResult&" + dataString;
								$.ajax({
									type: "POST",
									url: "src/sjerp.php",
									data: dataString,
									success: function() {
										alert("Result Successfully Saved!");
										$("#frmHavResult").trigger("reset");
										dis.dialog("close");
										// showValidation();
									}
								});
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

function validateHavResult(lid,code) {
	
	$.post("src/sjerp.php", {
		mod: "resultSingle",
		submod: "havResult",
		lid: lid,
		sid: Math.random() },
		function(data) {

			$("#hav_enccode").val(data['enccode']);
			$("#hav_sodate").val(data['orderdate']);
			$("#hav_pid").val(data['hmrno']);
			$("#hav_pname").val(decodeURIComponent(data['pname']));
			$("#hav_gender").val(data['sex']);
			$("#hav_birthdate").val(data['bday']);
			$("#hav_age").val(data['age']);
			$("#hav_patientstat").val(data['patientstatus']);
			$("#hav_physician").val(data['physician']);
			$("#hav_procedure").val(data['procedure']);
			$("#hav_code").val(data['code']);
			$("#hav_spectype").val(data['sampletype']);
			$("#hav_serialno").val(data['serialno']);
			$("#hav_extractdate").val(data['exdate']);
			$("#hav_extracttime").val(data['extractime']);
			$("#hav_extractby").val(data['extractby']);
			$("#hav_result_igm").val(data['hav_result_igm']);
			$("#hav_result_igg").val(data['hav_result_igg']);
			$("#hav_remarks").val(data['remarks']);
			$("#hav_location").val(data['location']);

			var dis = $("#havResult").dialog({
				title: "Validate Result",
				width: 540,
				resizeable: false,
				modal: true,
				buttons: [
					{
						text: "Save & Confirm Result",
						icons: { primary: "ui-icon-check" },
						click: function() {
							var msg = '';
	
							if(confirm("Are you sure you want save this data?") == true) {
							var dataString = $("#frmHavResult").serialize();
								dataString = "mod=validateHavResult&" + dataString;
								$.ajax({
									type: "POST",
									url: "src/sjerp.php",
									data: dataString,
									success: function() {
										alert("Result Successfully Confirmed & Published");
										showValidation();

										var enccode = $("#hav_enccode").val();
										var code = $("#hav_code").val();
										var serialno = $("#hav_serialno").val();
										var pid = $("#hav_pid").val();

										var txtHTML = "<iframe id='printSingleValue' frameborder=0 width='100%' height='100%' src='print/result.hav.php?enccode="+enccode+"&code="+code+"&serialno="+serialno+"&pid="+pid+"&sid="+Math.random()+"&sid="+Math.random()+"'></iframe>";
										$("#report5").html(txtHTML);
										$("#report5").dialog({title: "Result - "+ $("#hav_procedure").val() +"", width: 560, height: 620, resizable: true }).dialogExtend({
											"closable" : true,
											"maximizable" : true,
											"minimizable" : true
										});

										dis.dialog("close");
									}
								});
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

function dengueDuo(lid,code) {

	$("#dengue_date").datepicker();
	
	$.post("src/sjerp.php", {
		mod: "resultSingle",
		submod: "dengueDuo",
		lid: lid,
		sid: Math.random() },
		function(data) {

			$("#dengue_enccode").val(data['enccode']);
			$("#dengue_sodate").val(data['orderdate']);
			$("#dengue_pid").val(data['hpercode']);
			$("#dengue_pname").val(decodeURIComponent(data['pname']));
			$("#dengue_gender").val(data['sex']);
			$("#dengue_birthdate").val(data['bday']);
			$("#dengue_age").val(data['age']);
			$("#dengue_physician").val(data['physician']);
			$("#dengue_procedure").val(data['procedure']);
			$("#dengue_code").val(data['code']);
			$("#dengue_primecarecode").val(data['primecarecode']);
			$("#dengue_spectype").val(data['sampletype']);
			$("#dengue_serialno").val(data['serialno']);
			$("#dengue_extractdate").val(data['exdate']);
			$("#dengue_extracttime").val(data['extractime']);
			$("#dengue_by").val(data['extractby']);
			$("#dengue_location").val(data['location']);
			$("#dengue_result").val(data['result']);
			$("#dengue_result2").val(data['result2']);
			$("#dengue_result3").val(data['result3']);
			$("#dengue_remarks").val(data['remarks']);

			var dis = $("#denguetest").dialog({
				title: "Write Result",
				width: 540,
				resizeable: false,
				modal: true,
				buttons: [
					{
						text: "Save Result Pending Validation",
						icons: { primary: "ui-icon-check" },
						click: function() {
						
							
							if($("#dengue_value").val() == '') {
								parent.sendErrorMessage("Result Value is empty!");
							} else {
								if(confirm("Are you sure you want save this data?") == true) {
									var dataString = $("#frmDengueDuo").serialize();
										dataString = "mod=saveDengueDuo&" + dataString;
										$.ajax({
											type: "POST",
											url: "src/sjerp.php",
											data: dataString,
											success: function() {
											alert("Result Successfully Saved!");
											dis.dialog("close");
											$("#frmDengueDuo").trigger("reset");
										}
									});
								}
							}

						}
					},
					{
						text: "Print Result",
						icons: { primary: "ui-icon-print" },
						click: function() {
							
							var enccode = $("#dengue_enccode").val();
							var code = $("#dengue_code").val();
							var serialno = $("#dengue_serialno").val();

							var txtHTML = "<iframe id='printSingleValue' frameborder=0 width='100%' height='100%' src='print/result.dengueduo.php?enccode="+enccode+"&code="+code+"&serialno="+serialno+"&sid="+Math.random()+"&sid="+Math.random()+"'></iframe>";
							$("#report5").html(txtHTML);
							$("#report5").dialog({title: "Result - "+ $("#dengue_procedure").val() +"", width: 560, height: 620, resizable: true }).dialogExtend({
								"closable" : true,
								"maximizable" : true,
								"minimizable" : true
							});

						 }
					},
					{
						text: "Close",
						icons: { primary: "ui-icon-closethick" },
						click: function() { $(this).dialog("close"); $("#frmsingleValue").trigger("reset"); }
					}
				]
			});

		},"json"
	);
}

function validateDengueDuo(lid,code) {

	$.post("src/sjerp.php", {
		mod: "resultSingle",
		submod: "dengueDuo",
		lid: lid,
		sid: Math.random() },
		function(data) {

			$("#dengue_enccode").val(data['enccode']);
			$("#dengue_sodate").val(data['orderdate']);
			$("#dengue_pid").val(data['hpercode']);
			$("#dengue_pname").val(decodeURIComponent(data['pname']));
			$("#dengue_gender").val(data['sex']);
			$("#dengue_birthdate").val(data['bday']);
			$("#dengue_age").val(data['age']);
			$("#dengue_physician").val(data['physician']);
			$("#dengue_procedure").val(data['procedure']);
			$("#dengue_code").val(data['code']);
			$("#dengue_primecarecode").val(data['primecarecode']);
			$("#dengue_spectype").val(data['sampletype']);
			$("#dengue_serialno").val(data['serialno']);
			$("#dengue_extractdate").val(data['exdate']);
			$("#dengue_extracttime").val(data['extractime']);
			$("#dengue_by").val(data['extractby']);
			$("#dengue_location").val(data['location']);
			$("#dengue_result").val(data['result']);
			$("#dengue_result2").val(data['result2']);
			$("#dengue_result3").val(data['result3']);
			$("#dengue_remarks").val(data['remarks']);

			var dis = $("#denguetest").dialog({
				title: "Validate Result",
				width: 540,
				resizeable: false,
				modal: true,
				buttons: [
					{
						text: "Save Changes & Mark as Validated",
						icons: { primary: "ui-icon-check" },
						click: function() {
						
							
							if($("#dengue_value").val() == '') {
								parent.sendErrorMessage("Result Value is empty!");
							} else {
								if(confirm("Are you sure you want save this data?") == true) {
									var dataString = $("#frmDengueDuo").serialize();
										dataString = "mod=validateDengueDuo&" + dataString;
										$.ajax({
											type: "POST",
											url: "src/sjerp.php",
											data: dataString,
											success: function() {
											alert("Result Successfully Marked as Validated!");
											showValidation();

											var enccode = $("#dengue_enccode").val();
											var code = $("#dengue_code").val();
											var serialno = $("#dengue_serialno").val();
											var pid = $("#dengue_pid").val();

											var txtHTML = "<iframe id='printSingleValue' frameborder=0 width='100%' height='100%' src='print/result.dengueduo.php?enccode="+enccode+"&code="+code+"&serialno="+serialno+"&pid="+pid+"&sid="+Math.random()+"&sid="+Math.random()+"'></iframe>";
											$("#report5").html(txtHTML);
											$("#report5").dialog({title: "Result - "+ $("#dengue_procedure").val() +"", width: 560, height: 620, resizable: true }).dialogExtend({
												"closable" : true,
												"maximizable" : true,
												"minimizable" : true
											});

											dis.dialog("close");
										}
									});
								}
							}

						}
					},
					{
						text: "Close",
						icons: { primary: "ui-icon-closethick" },
						click: function() { $(this).dialog("close"); $("#frmDengueDuo").trigger("reset"); }
					}
				]
			});

		},"json"
	);
}

function ogttResult(lid,code) {

	$("#ogttResult").html("<iframe id='frmOGTT' frameborder=0 width='100%' height='100%' src='result.ogtt.php?lid="+lid+"'></iframe>");
	
	$("#ogttResult").dialog({
		title: "Write Result",
		width: xWidth,
		height: 640,
		resizeable: false,
		modal: true,
		buttons: [
			{
				text: "Save Result Pending Validation",
				icons: { primary: "ui-icon-check" },
				click: function() {
				
					
					if($("ogtt_fbs").val() == '') {
						parent.sendErrorMessage("Result Value is empty!");
					} else {
						if(confirm("Are you sure you want save this data?") == true) {
							var dataString = $('#frmOGTT').contents().find('#frmOGTT').serialize();
							dataString = "mod=saveOGTT&" + dataString;
								$.ajax({
									type: "POST",
									url: "src/sjerp.php",
									data: dataString,
									success: function() {
									alert("Result Successfully Saved!");
									dis.dialog("close");
									$("#frmOGTT").trigger("reset");
								}
							});
						}
					}

				}
			},
			{
				text: "Print Result",
				icons: { primary: "ui-icon-print" },
				click: function() {
					
					var enccode = $("#ogtt_enccode").val();
					var code = $("#ogtt_code").val();
					var serialno = $("#ogtt_serialno").val();

					var txtHTML = "<iframe id='printSingleValue' frameborder=0 width='100%' height='100%' src='print/result.ogtt.php?enccode="+enccode+"&code="+code+"&serialno="+serialno+"&sid="+Math.random()+"&sid="+Math.random()+"'></iframe>";
					$("#report5").html(txtHTML);
					$("#report5").dialog({title: "Result - "+ $("#sresult_procedure").val() +"", width: 560, height: 620, resizable: true }).dialogExtend({
						"closable" : true,
						"maximizable" : true,
						"minimizable" : true
					});

					}
			},
			{
				text: "Close",
				icons: { primary: "ui-icon-closethick" },
				click: function() { $(this).dialog("close"); $("#frmOGTT").trigger("reset"); }
			}
		]
	});

}

function validateOGTTResult(lid,code) {

	$("#ogttResult").html("<iframe id='frmOGTT' frameborder=0 width='100%' height='100%' src='result.ogtt.php?lid="+lid+"'></iframe>");

	$("#ogttResult").dialog({
		title: "Write Result",
		width: xWidth,
		height: 640,
		resizeable: false,
		modal: true,
		buttons: [
			{
				text: "Confirm & Validate Result",
				icons: { primary: "ui-icon-check" },
				click: function() {
				
					
					if($("ogtt_fbs").val() == '') {
						parent.sendErrorMessage("Result Value is empty!");
					} else {
						if(confirm("Are you sure you want save this data?") == true) {
							var dataString = $('#frmOGTT').contents().find('#frmOGTT').serialize();
								dataString = "mod=validateOGTT&" + dataString;
								$.ajax({
									type: "POST",
									url: "src/sjerp.php",
									data: dataString,
									success: function() {
										alert("Result Successfully Marked as Validated!");
										showValidation();

										var enccode = $('#frmOGTT').contents().find('#ogtt_enccode').val();
										var serialno = $('#frmOGTT').contents().find('#ogtt_serialno').val();
										var code = $('#frmOGTT').contents().find('#ogtt_code').val();

										var txtHTML = "<iframe id='prntXrayResult' frameborder=0 width='100%' height='100%' src='print/result.ogtt.php?enccode="+enccode+"&serialno="+serialno+"&code="+code+"&sid="+Math.random()+"'></iframe>";
										$("#report3").html(txtHTML);
										$("#report3").dialog({title: "Print - OGTT RESULT", width: 560, height: 620, resizable: true }).dialogExtend({
											"closable" : true,
											"maximizable" : true,
											"minimizable" : true
										});

										dis.dialog("close");
								}
							});
						}
					}

				}
			},
			{
				text: "Close",
				icons: { primary: "ui-icon-closethick" },
				click: function() { $(this).dialog("close"); $("#frmOGTT").trigger("reset"); }
			}
		]
	});
}

function typhoidResult(lid,code) {

	$("#typhoid_date").datepicker();
	
	$.post("src/sjerp.php", {
		mod: "resultSingle",
		submod: "typhoid",
		lid: lid,
		sid: Math.random() },
		function(data) {

			$("#typhoid_enccode").val(data['enccode']);
			$("#typhoid_sodate").val(data['orderdate']);
			$("#typhoid_pid").val(data['hpercode']);
			$("#typhoid_pname").val(decodeURIComponent(data['pname']));
			$("#typhoid_gender").val(data['sex']);
			$("#typhoid_birthdate").val(data['bday']);
			$("#typhoid_age").val(data['age']);
			$("#typhoid_physician").val(data['physician']);
			$("#typhoid_procedure").val(data['procedure']);
			$("#typhoid_code").val(data['code']);
			$("#typhoid_primecarecode").val(data['primecarecode']);
			$("#typhoid_spectype").val(data['sampletype']);
			$("#typhoid_serialno").val(data['serialno']);
			$("#typhoid_extractdate").val(data['exdate']);
			$("#typhoid_extracttime").val(data['extractime']);
			$("#typhoid_extractby").val(data['extractby']);
			$("#typhoid_location").val(data['location']);
			$("#typhoid_attribute").val(data['attribute']);
			$("#typhoid_igm").val(data['typhoid_igm']);
			$("#typhoid_igg").val(data['typhoid_igg']);
			$("#typhoid_remarks").val(data['remarks']);
			$("#typhoid_location").val(data['location']);

			var dis = $("#typhoid").dialog({
				title: "Write Result",
				width: 1024,
				height: 600,
				resizeable: false,
				modal: true,
				buttons: [
					{
						text: "Save Result Pending Validation",
						icons: { primary: "ui-icon-check" },
						click: function() {
						
							
							if($("typhoid_igm").val() == '') {
								parent.sendErrorMessage("Result Value is empty!");
							} else {
								if(confirm("Are you sure you want save this data?") == true) {
									var dataString = $("#frmTyphoid").serialize();
										dataString = "mod=saveTyphoid&" + dataString;
										$.ajax({
											type: "POST",
											url: "src/sjerp.php",
											data: dataString,
											success: function() {
											alert("Result Successfully Saved!");
											dis.dialog("close");
											$("#frmTyphoid").trigger("reset");
										}
									});
								}
							}

						}
					},
					{
						text: "Print Result",
						icons: { primary: "ui-icon-print" },
						click: function() {
							
							var enccode = $("#typhoid_enccode").val();
							var code = $("#typhoid_code").val();
							var serialno = $("#typhoid_serialno").val();

							var txtHTML = "<iframe id='printSingleValue' frameborder=0 width='100%' height='100%' src='print/result.typhoid.php?enccode="+enccode+"&code="+code+"&serialno="+serialno+"&sid="+Math.random()+"&sid="+Math.random()+"'></iframe>";
							$("#report5").html(txtHTML);
							$("#report5").dialog({title: "Result - "+ $("#sresult_procedure").val() +"", width: 560, height: 620, resizable: true }).dialogExtend({
								"closable" : true,
								"maximizable" : true,
								"minimizable" : true
							});

						 }
					},
					{
						text: "Close",
						icons: { primary: "ui-icon-closethick" },
						click: function() { $(this).dialog("close"); $("#frmTyphoid").trigger("reset"); }
					}
				]
			});

		},"json"
	);
}

function validateTyphoidResult(lid,code) {

	$("#typhoid_date").datepicker();
	
	$.post("src/sjerp.php", {
		mod: "resultSingle",
		submod: "typhoid",
		lid: lid,
		sid: Math.random() },
		function(data) {

			$("#typhoid_enccode").val(data['enccode']);
			$("#typhoid_sodate").val(data['orderdate']);
			$("#typhoid_pid").val(data['hpercode']);
			$("#typhoid_pname").val(decodeURIComponent(data['pname']));
			$("#typhoid_gender").val(data['sex']);
			$("#typhoid_birthdate").val(data['bday']);
			$("#typhoid_age").val(data['age']);
			$("#typhoid_physician").val(data['physician']);
			$("#typhoid_procedure").val(data['procedure']);
			$("#typhoid_code").val(data['code']);
			$("#typhoid_primecarecode").val(data['primecarecode']);
			$("#typhoid_spectype").val(data['sampletype']);
			$("#typhoid_serialno").val(data['serialno']);
			$("#typhoid_extractdate").val(data['exdate']);
			$("#typhoid_extracttime").val(data['extractime']);
			$("#typhoid_extractby").val(data['extractby']);
			$("#typhoid_location").val(data['location']);
			$("#typhoid_attribute").val(data['attribute']);
			$("#typhoid_igm").val(data['typhoid_igm']);
			$("#typhoid_igg").val(data['typhoid_igg']);
			$("#typhoid_remarks").val(data['remarks']);
			$("#typhoid_location").val(data['location']);

			var dis = $("#typhoid").dialog({
				title: "Validate Result",
				width: 1024,
				height: 600,
				resizeable: false,
				modal: true,
				buttons: [
					{
						text: "Confirm & Validate Result",
						icons: { primary: "ui-icon-check" },
						click: function() {
						
							
							if($("typhoid_igm").val() == '') {
								parent.sendErrorMessage("Result Value is empty!");
							} else {
								if(confirm("Are you sure you want save this result?") == true) {
									var dataString = $("#frmTyphoid").serialize();
										dataString = "mod=validateTyphoid&" + dataString;
										$.ajax({
											type: "POST",
											url: "src/sjerp.php",
											data: dataString,
											success: function() {
												alert("Result Successfully Marked as Validated!");
												showValidation();

												var enccode = $('#frmTyphoid').contents().find('#typhoid_enccode').val();
												var serialno = $('#frmTyphoid').contents().find('#typhoid_serialno').val();
												var code = $('#frmTyphoid').contents().find('#typhoid_code').val();

												var txtHTML = "<iframe id='prntXrayResult' frameborder=0 width='100%' height='100%' src='print/result.typhoid.php?enccode="+enccode+"&serialno="+serialno+"&code="+code+"&sid="+Math.random()+"'></iframe>";
												$("#report3").html(txtHTML);
												$("#report3").dialog({title: "Print - TYPHOID RESULT", width: 560, height: 620, resizable: true }).dialogExtend({
													"closable" : true,
													"maximizable" : true,
													"minimizable" : true
												});

												dis.dialog("close");
										}
									});
								}
							}

						}
					},
					{
						text: "Close",
						icons: { primary: "ui-icon-closethick" },
						click: function() { $(this).dialog("close"); $("#frmTyphoid").trigger("reset"); }
					}
				]
			});

		},"json"
	);
}

function cbcResult(lid,code) {
	$("#cbcResult").html("<iframe id='frmCbcResult' frameborder=0 width='100%' height='100%' src='result.cbc.php?lid="+lid+"'></iframe>");
	var dis = $("#cbcResult").dialog({
		title: "Write Result",
		width: xWidth,
		height: 680,
		resizeable: false,
		modal: true
	});
}

function printCBCResult(enccode,serialno,code) {
	var txtHTML = "<iframe id='prntXrayResult' frameborder=0 width='100%' height='100%' src='print/result.cbc.php?enccode="+enccode+"&serialno="+serialno+"&code="+code+"&sid="+Math.random()+"'></iframe>";
	$("#report3").html(txtHTML);
	$("#report3").dialog({title: "Print - CBC RESULT", width: 560, height: 620, resizable: true }).dialogExtend({
		"closable" : true,
		"maximizable" : true,
		"minimizable" : true
	});
}

function bloodChem(lid,code) {
	
	$("#bloodChemResult").html("<iframe id='frmBloodChem' frameborder=0 width='100%' height='100%' src='result.bloodchem.php?lid="+lid+"'></iframe>");
	
	$("#bloodChemResult").dialog({
		title: "Write Result",
		width: xWidth,
		height: 640,
		resizeable: false,
		modal: true,
		buttons: [
			{
				text: "Save Result Pending Validation",
				icons: { primary: "ui-icon-check" },
				click: function() {
					
					var msg = '';
					var result = new Array();

					$('#frmBloodChem').contents().find('input[class="noBorders"]').each(function(){
						var input = $(this);

						if(input.val() == '' || input.val() == '0.00') {
							result.push(input.val());
						} 
					});

					if(result.length > 0) {
						var confirmMessage = "It appears that there are result paraments on this test with \"Zero\" or Null. Zero or Null values will not be displayed during printing of result. Do you wish to continue saving this result?";
					} else {
						var confirmMessage = "Are you sure you want save this data?";
					}


					if(confirm(confirmMessage) == true) {
						var dataString = $('#frmBloodChem').contents().find('#frmBloodChemResult').serialize();
							dataString = "mod=saveBloodChem&" + dataString;
						
							$.ajax({
							type: "POST",
							url: "src/sjerp.php",
							data: dataString,
							success: function() {
								alert("Result Successfully Saved!");
							}
						});
					}
				}
			},
			{
				text: "Print Result",
				icons: { primary: "ui-icon-print" },
				click: function() {
					
					var serialno = $('#frmBloodChem').contents().find('#bloodchem_serialno').val();
					var enccode = $('#frmBloodChem').contents().find('#bloodchem_enccode').val();
					var proccode = $('#frmBloodChem').contents().find('#bloodchem_proccode').val();
					var dotime = $('#frmBloodChem').contents().find('#bloodchem_dotime').val();


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
									window.open("print/result.bloodchem.php?enccode="+enccode+"&serialno="+serialno+"&sid="+Math.random()+"&"+dataString+"","Blood Chemistry Result","location=1,status=1,scrollbars=1,width=640,height=720");
								}
							}
						]
					});

				 }
			},
			{
				text: "Close",
				icons: { primary: "ui-icon-closethick" },
				click: function() { $(this).dialog("close"); }
			}
		]
	});
}

function validateBloodChem(lid,code) {
	
	$("#bloodChemResult").html("<iframe id='frmBloodChem' frameborder=0 width='100%' height='100%' src='result.bloodchem.php?lid="+lid+"'></iframe>");
	
	var dis = $("#bloodChemResult").dialog({
		title: "Write Result",
		width: xWidth,
		height: 640,
		resizeable: false,
		modal: true,
		buttons: [
			{
				text: "Save Changes & Mark Result as Validated",
				icons: { primary: "ui-icon-check" },
				click: function() {

					var msg = '';
					var result = new Array();

					$('#frmBloodChem').contents().find('input[class="noBorders"]').each(function(){
						var input = $(this);

						if(input.val() == '' || input.val() == '0.00') {
							result.push(input.val());
						} 
					});

					if(result.length > 0) {
						var confirmMessage = "It appears that there are result paraments on this test with \"Zero\" or Null. Zero or Null values will not be displayed during printing of result. Do you wish to continue validating this result?";
					} else {
						var confirmMessage = "Are you sure you want save changes & validate this result?";
					}


					if(confirm(confirmMessage) == true) {
						var dataString = $('#frmBloodChem').contents().find('#frmBloodChemResult').serialize();
							dataString = "mod=validateBloodChem&" + dataString;
						
							$.ajax({
							type: "POST",
							url: "src/sjerp.php",
							data: dataString,
							success: function() {
								alert("Result Successfully Saved & Validated!");

								showValidation();

								var serialno = $('#frmBloodChem').contents().find('#bloodchem_serialno').val();
								var enccode = $('#frmBloodChem').contents().find('#bloodchem_enccode').val();
								var proccode = $('#frmBloodChem').contents().find('#bloodchem_proccode').val();
								var dotime = $('#frmBloodChem').contents().find('#bloodchem_dotime').val();


								$.post("src/sjerp.php", { mod: "retrieveSameSampleForPrint", proccode: proccode, enccode: enccode, serialno: serialno, dotime: dotime, sid: Math.random() }, function(res) {
									$("#otherTests").html(res);
									var dataString = $("#otherTests").serialize();
									window.open("print/result.bloodchem.php?enccode="+enccode+"&serialno="+serialno+"&sid="+Math.random()+"&"+dataString+"","Blood Chemistry Result","location=1,status=1,scrollbars=1,width=640,height=720");
								},"html");
				
								dis.dialog("close");	

							}
						});
					}



					/* if(confirm("Are you sure you want save this data?") == true) {
						var dataString = $('#frmBloodChem').contents().find('#frmBloodChemResult').serialize();
						dataString = "mod=validateBloodChem&" + dataString;
						$.ajax({
							type: "POST",
							url: "src/sjerp.php",
							data: dataString,
							success: function() {
								alert("Result Successfully Marked as Validated!");
								showValidation();

								var serialno = $('#frmBloodChem').contents().find('#bloodchem_serialno').val();
								var enccode = $('#frmBloodChem').contents().find('#bloodchem_enccode').val();
								var proccode = $('#frmBloodChem').contents().find('#bloodchem_proccode').val();
								var dotime = $('#frmBloodChem').contents().find('#bloodchem_dotime').val();


								$.post("src/sjerp.php", { mod: "retrieveSameSampleForPrint", proccode: proccode, enccode: enccode, serialno: serialno, dotime: dotime, sid: Math.random() }, function(res) {
									$("#otherTests").html(res);
								
									var dataString = $("#otherTests").serialize();
									window.open("print/result.bloodchem.php?enccode="+enccode+"&serialno="+serialno+"&sid="+Math.random()+"&"+dataString+"","Blood Chemistry Result","location=1,status=1,scrollbars=1,width=640,height=720");
								
								},"html");
				
								dis.dialog("close");
							}
						});
					} */
				}
			},
			{
				text: "Print Result",
				icons: { primary: "ui-icon-print" },
				click: function() {
					
					var serialno = $('#frmBloodChem').contents().find('#bloodchem_serialno').val();
					var enccode = $('#frmBloodChem').contents().find('#bloodchem_enccode').val();
					var proccode = $('#frmBloodChem').contents().find('#bloodchem_proccode').val();
					var dotime = $('#frmBloodChem').contents().find('#bloodchem_dotime').val();


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
									window.open("print/result.bloodchem.php?enccode="+enccode+"&serialno="+serialno+"&sid="+Math.random()+"&"+dataString+"","Blood Chemistry Result","location=1,status=1,scrollbars=1,width=640,height=720");
								}
							}
						]
					});
					
				 }
			},
			{
				text: "Close",
				icons: { primary: "ui-icon-closethick" },
				click: function() { $(this).dialog("close"); }
			}
		]
	});
}

/* Special Chemistry */
function spchem(lid,code) {
	
	$("#specialChemistryResult").html("<iframe id='frmSpBloodChem' frameborder=0 width='100%' height='100%' src='result.spchem.php?lid="+lid+"'></iframe>");
	
	$("#specialChemistryResult").dialog({
		title: "Write Result",
		width: xWidth,
		height: 640,
		resizeable: false,
		modal: true,
		buttons: [
			{
				text: "Save Result Pending Validation",
				icons: { primary: "ui-icon-check" },
				click: function() {

					var msg = '';
					var result = new Array();

					$('#frmSpBloodChem').contents().find('input[class="noBorders"]').each(function(){
						var input = $(this);

						if(input.val() == '' || input.val() == '0.00') {
							result.push(input.val());
						} 
					});

					if(result.length > 0) {
						var confirmMessage = "It appears that there are result paraments on this test with \"Zero\" or Null. Zero or Null values will not be displayed during printing of result. Do you wish to continue validating this result?";
					} else {
						var confirmMessage = "Are you sure you want save this data?";
					}


					if(confirm(confirmMessage) == true) {
						var dataString = $('#frmSpBloodChem').contents().find('#frmSpecialChemResult').serialize();
							dataString = "mod=saveSPChem&" + dataString;
						$.ajax({
							type: "POST",
							url: "src/sjerp.php",
							data: dataString,
							success: function() {
								alert("Result Successfully Saved!");
							}
						});
					}
				}
				
			},
			{
				text: "Print Result",
				icons: { primary: "ui-icon-print" },
				click: function() {
					
					var serialno = $('#frmSpBloodChem').contents().find('#spchem_serialno').val();
					var enccode = $('#frmSpBloodChem').contents().find('#spchem_enccode').val();
					var proccode = $('#frmSpBloodChem').contents().find('#spchem_proccode').val();
					var dotime = $('#frmSpBloodChem').contents().find('#spchem_dotime').val();


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
									window.open("print/result.specialchemistry.php?enccode="+enccode+"&serialno="+serialno+"&sid="+Math.random()+"&"+dataString+"","Special Chemistry Result","location=1,status=1,scrollbars=1,width=640,height=720");
								}
							}
						]
					});

				 }
			},
			{
				text: "Close",
				icons: { primary: "ui-icon-closethick" },
				click: function() { $(this).dialog("close"); }
			}
		]
	});
}

/* Special Chemistry */
function validateSPChem(lid,code) {
	
	$("#specialChemistryResult").html("<iframe id='frmSpBloodChem' frameborder=0 width='100%' height='100%' src='result.spchem.php?lid="+lid+"'></iframe>");
	
	var disDialog = $("#specialChemistryResult").dialog({
		title: "Write Result",
		width: xWidth,
		height: 640,
		resizeable: false,
		modal: true,
		buttons: [
			{
				text: "Confirm & Validate Result",
				icons: { primary: "ui-icon-check" },
				click: function() {
					
					var msg = '';
					var result = new Array();
				
					$('#frmSpBloodChem').contents().find("input[type=text]").each(function(){
						var input = $(this);
						if(input.val() == '' || input.val() == '0.00') {
							result.push(input.val());
						} 
					});
					
					if(result.length > 0) {
						
						if(confirm("It appears that there are result paraments on this test with \"Zero\" or blank values. Do you wish to continue validating this result?") == true) {
							var dataString = $('#frmSpBloodChem').contents().find('#frmSpecialChemResult').serialize();
							dataString = "mod=validateSPChem&" + dataString;
							$.ajax({
								type: "POST",
								url: "src/sjerp.php",
								data: dataString,
								success: function() {

									alert("Result Successfully Validated!");
									
									var serialno = $('#frmSpBloodChem').contents().find('#spchem_serialno').val();
									var enccode = $('#frmSpBloodChem').contents().find('#spchem_enccode').val();
									var otherTests = $("#otherTests").serialize();
									
									window.open("print/result.specialchemistry.php?enccode="+enccode+"&serialno="+serialno+"&sid="+Math.random()+"&"+otherTests+"","Special Chemistry Result","location=1,status=1,scrollbars=1,width=640,height=720");
									disDialog.dialog("close");
								}
							});
							
						}
					} else {
						if(confirm("Are you sure you want to confirm and validate this result?") == true) {

							var dataString = $('#frmSpBloodChem').contents().find('#frmSpecialChemResult').serialize();
							    dataString = "mod=validateSPChem&" + dataString;
							
								$.ajax({
								type: "POST",
								url: "src/sjerp.php",
								data: dataString,
								success: function() {
									alert("Result Successfully Validated!");
									
									var serialno = $('#frmSpBloodChem').contents().find('#spchem_serialno').val();
									var enccode = $('#frmSpBloodChem').contents().find('#spchem_enccode').val();
									var otherTests = $("#otherTests").serialize();

									window.open("print/result.specialchemistry.php?enccode="+enccode+"&serialno="+serialno+"&sid="+Math.random()+"&"+otherTests+"","Special Chemistry Result","location=1,status=1,scrollbars=1,width=640,height=720");
									disDialog.dialog("close");
								}
							});
						}
						
					}
				}
			},
			{
				text: "Print Result",
				icons: { primary: "ui-icon-print" },
				click: function() {
					
					var serialno = $('#frmSpBloodChem').contents().find('#spchem_serialno').val();
					var enccode = $('#frmSpBloodChem').contents().find('#spchem_enccode').val();
					var proccode = $('#frmSpBloodChem').contents().find('#spchem_proccode').val();
					var dotime = $('#frmSpBloodChem').contents().find('#spchem_dotime').val();

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
									var otherTests = $("#otherTests").serialize();
									window.open("print/result.specialchemistry.php?enccode="+enccode+"&serialno="+serialno+"&sid="+Math.random()+"&"+otherTests+"","Special Chemistry Result","location=1,status=1,scrollbars=1,width=640,height=720");
								}
							}
						]
					});

				 }
			},
			{
				text: "Close",
				icons: { primary: "ui-icon-closethick" },
				click: function() { $(this).dialog("close"); }
			}
		]
	});
}

/* Blood Chem Consolidated */
function writeChemistryResult(so_no) {
	
	$("#bloodChemResult").html("<iframe id='frmBloodChem' frameborder=0 width='100%' height='100%' src='result.bloodchem.conso.php?so_no="+so_no+"'></iframe>");
	
	$("#bloodChemResult").dialog({
		title: "Write Result",
		width: 1024,
		height: 920,
		resizeable: false,
		modal: true,
		buttons: [
			{
				text: "Save Result Pending Validation",
				icons: { primary: "ui-icon-check" },
				click: function() {
					
					var msg = '';
					var result = new Array();

					$('#frmBloodChem').contents().find('input[class="noBorders"]').each(function(){
						var input = $(this);

						if(input.val() == '' || input.val() == '0.00') {
							result.push(input.val());
						} 
					});

					if(result.length > 0) {
						var confirmMessage = "It appears that there are result paraments on this test with \"Zero\" or Null. Zero or Null values will not be displayed during printing of result. Do you wish to continue validating this result?";
					} else {
						var confirmMessage = "Are you sure you want save this data?";
					}


					if(confirm(confirmMessage) == true) {
						var dataString = $('#frmBloodChem').contents().find('#frmBloodChemResult').serialize();
							dataString = "mod=saveBloodChem&" + dataString;
						
							$.ajax({
							type: "POST",
							url: "src/sjerp.php",
							data: dataString,
							success: function() {
								alert("Result Successfully Saved!");
							}
						});
					}
				}
			},
			{
				text: "Print Result",
				icons: { primary: "ui-icon-print" },
				click: function() {
					
					var so_no = $('#frmBloodChem').contents().find('#bloodchem_sono').val();
					var serialno = $('#frmBloodChem').contents().find('#bloodchem_serialno').val();

					var txtHTML = "<iframe id='printBloodChemResult' frameborder=0 width='100%' height='100%' src='print/result.bloodchem.php?so_no="+so_no+"&code="+code+"&serialno="+serialno+"&sid="+Math.random()+"&sid="+Math.random()+"'></iframe>";
					$("#report3").html(txtHTML);
					$("#report3").dialog({title: "Print - BLOOD CHEMISTRY RESULT", width: 560, height: 620, resizable: true });


				 }
			},
			{
				text: "Close",
				icons: { primary: "ui-icon-closethick" },
				click: function() { $(this).dialog("close"); }
			}
		]
	});
}


function uaResult(lid,code) {
	
	$("#uaResult").html("<iframe id='frmUA' frameborder=0 width='100%' height='100%' src='result.ua.php?lid="+lid+"'></iframe>");
	
	$("#uaResult").dialog({
		title: "Write Result",
		width: xWidth,
		height: 760,
		resizeable: false,
		modal: true,
		buttons: [
			{
				text: "Save Result Pending Validation",
				icons: { primary: "ui-icon-check" },
				click: function() {
					var msg = '';

					if(confirm("Are you sure you want save this data?") == true) {
						var dataString = $('#frmUA').contents().find('#frmUrinalysisReport').serialize();
						    dataString = "mod=saveUAReport&" + dataString;
						$.ajax({
							type: "POST",
							url: "src/sjerp.php",
							data: dataString,
							success: function() {
								alert("Result Successfully Saved!");
							}
						});
					}
				}
			},
			{
				text: "Print Result",
				icons: { primary: "ui-icon-print" },
				click: function() {
					
					var enccode = $('#frmUA').contents().find('#ua_enccode').val();
					var serialno = $('#frmUA').contents().find('#ua_serialno').val();

					var txtHTML = "<iframe id='printUAResult' frameborder=0 width='100%' height='100%' src='print/result.ua.php?enccode="+enccode+"&serialno="+serialno+"&sid="+Math.random()+"'></iframe>";
					$("#report1").html(txtHTML);
					$("#report1").dialog({title: "Print - Uranilysis (UA)", width: 560, height: 620, resizable: true });

				 }
			},
			{
				text: "Close",
				icons: { primary: "ui-icon-closethick" },
				click: function() { $(this).dialog("close"); }
			}
		]
	});
}

function validateUaResult(lid,code) {
	
	$("#uaResult").html("<iframe id='frmUA' frameborder=0 width='100%' height='100%' src='result.ua.php?lid="+lid+"'></iframe>");
	
	var dis = $("#uaResult").dialog({
		title: "Validate Result",
		width: xWidth,
		height: 760,
		resizeable: false,
		modal: true,
		buttons: [
			{
				text: "Save Changes & Mark Resullt as Validated",
				icons: { primary: "ui-icon-check" },
				click: function() {
					var msg = '';

					if(confirm("Are you sure you want save this data?") == true) {
						var dataString = $('#frmUA').contents().find('#frmUrinalysisReport').serialize();
						dataString = "mod=validateUAReport&" + dataString;
						$.ajax({
							type: "POST",
							url: "src/sjerp.php",
							data: dataString,
							success: function() {
								alert("Result Successfully Marked as Validated!");
								showValidation();

								var enccode = $('#frmUA').contents().find('#ua_enccode').val();
								var serialno = $('#frmUA').contents().find('#ua_serialno').val();
								var code = $('#frmUA').contents().find('#ua_code').val();

								var txtHTML = "<iframe id='printUAResult' frameborder=0 width='100%' height='100%' src='print/result.ua.php?enccode="+enccode+"&serialno="+serialno+"&code="+code+"&sid="+Math.random()+"'></iframe>";
								$("#report3").html(txtHTML);
								$("#report3").dialog({title: "Print - UA RESULT", width: 560, height: 620, resizable: true }).dialogExtend({
									"closable" : true,
									"maximizable" : true,
									"minimizable" : true
								});


								dis.dialog("close");
								
							}
						});
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
}


function stoolExam(lid,code) {
	
	$("#stoolResult").html("<iframe id='frmStoolExam' frameborder=0 width='100%' height='100%' src='result.stool.php?lid="+lid+"'></iframe>");
	
	$("#stoolResult").dialog({
		title: "Write Result",
		width: 1024,
		height: 740,
		resizeable: false,
		modal: true,
		buttons: [
			{
				text: "Save Result Pending Validation",
				icons: { primary: "ui-icon-check" },
				click: function() {
					var msg = '';

					if(confirm("Are you sure you want save this data?") == true) {
						var dataString = $('#frmStoolExam').contents().find('#frmStoolReport').serialize();
						    dataString = "mod=saveStoolExam&" + dataString;
						$.ajax({
							type: "POST",
							url: "src/sjerp.php",
							data: dataString,
							success: function() {
								alert("Result Successfully Saved!");
							}
						});
					}
				}
			},
			{
				text: "Print Result",
				icons: { primary: "ui-icon-print" },
				click: function() {
					
					var enccode = $('#frmStoolExam').contents().find('#stool_enccode').val();
					var serialno = $('#frmStoolExam').contents().find('#stool_serialno').val();

					var txtHTML = "<iframe id='prntXrayResult' frameborder=0 width='100%' height='100%' src='print/result.stool.php?enccode="+enccode+"&serialno="+serialno+"&sid="+Math.random()+"'></iframe>";
					$("#report2").html(txtHTML);
					$("#report2").dialog({title: "Print - Stool Exam", width: 560, height: 620, resizable: true });


				 }
			},
			{
				text: "Close",
				icons: { primary: "ui-icon-closethick" },
				click: function() { $(this).dialog("close"); }
			}
		]
	});
}

function validateStoolExam(lid,code) {
	
	$("#stoolResult").html("<iframe id='frmStoolExam' frameborder=0 width='100%' height='100%' src='result.stool.php?lid="+lid+"'></iframe>");
	
	var dis = $("#stoolResult").dialog({
		title: "Write Result",
		width: 1024,
		height: 680,
		resizeable: false,
		modal: true,
		buttons: [
			{
				text: "Confirm & Validate Result",
				icons: { primary: "ui-icon-check" },
				click: function() {
					var msg = '';

					if(confirm("Are you sure you want confirm and publish this result?") == true) {
						var dataString = $('#frmStoolExam').contents().find('#frmStoolReport').serialize();
						dataString = "mod=validateStoolExam&" + dataString;
						$.ajax({
							type: "POST",
							url: "src/sjerp.php",
							data: dataString,
							success: function() {
								alert("Result Successfully Marked as Validated!");
								showValidation();

								var enccode = $('#frmStoolExam').contents().find('#stool_enccode').val();
								var serialno = $('#frmStoolExam').contents().find('#stool_serialno').val();
								var code = $('#frmStoolExam').contents().find('#stool_code').val();

								var txtHTML = "<iframe id='prntXrayResult' frameborder=0 width='100%' height='100%' src='print/result.stool.php?enccode="+enccode+"&serialno="+serialno+"&sid="+Math.random()+"'></iframe>";
								$("#report3").html(txtHTML);
								$("#report3").dialog({title: "Print - Stool Exam", width: 560, height: 620, resizable: true });
								dis.dialog("close");
							}
						});
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
}

function semenAnalysis(lid,code) {
	
	$("#semAnalReport").html("<iframe id='frmSemenAnalysis' frameborder=0 width='100%' height='100%' src='result.sar.php?lid="+lid+"'></iframe>");
	
	$("#stoolResult").dialog({
		title: "Write Result",
		width: 1024,
		height: 680,
		resizeable: false,
		modal: true,
		buttons: [
			{
				text: "Save Result Pending Validation",
				icons: { primary: "ui-icon-check" },
				click: function() {
					var msg = '';

					if(confirm("Are you sure you want save this data?") == true) {
						var dataString = $('#frmSemenAnalysis').contents().find('#frmSemenAnalysisReport').serialize();
						dataString = "mod=saveSemenAnalysis&" + dataString;
						$.ajax({
							type: "POST",
							url: "src/sjerp.php",
							data: dataString,
							success: function() {
								alert("Result Successfully Saved!");
							}
						});
					}
				}
			},
			{
				text: "Print Result",
				icons: { primary: "ui-icon-print" },
				click: function() {
					
					var so_no = $('#frmSemenAnalysis').contents().find('#semen_sono').val();
					var serialno = $('#frmSemenAnalysis').contents().find('#semen_serialno').val();

					var txtHTML = "<iframe id='prntXrayResult' frameborder=0 width='100%' height='100%' src='print/result.sar.php?so_no="+so_no+"&code="+code+"&serialno="+serialno+"&sid="+Math.random()+"&sid="+Math.random()+"'></iframe>";
					$("#report2").html(txtHTML);
					$("#report2").dialog({title: "Print - Stool Exam", width: 560, height: 620, resizable: true }).dialogExtend({
						"closable" : true,
						"maximizable" : true,
						"minimizable" : true
					});


				 }
			},
			{
				text: "Close",
				icons: { primary: "ui-icon-closethick" },
				click: function() { $(this).dialog("close"); }
			}
		]
	});
}

function pregnancyResult(lid,code) {

	$("#pt_date").datepicker();
	
	$.post("src/sjerp.php", {
		mod: "resultSingle",
		submod: "enumResult",
		lid: lid,
		sid: Math.random() },
		function(data) {
		
			$("#pt_enccode").val(data['enccode']);
			$("#pt_sodate").val(data['orderdate']);
			$("#pt_pid").val(data['hmrno']);
			$("#pt_pname").val(decodeURIComponent(data['pname']));
			$("#pt_gender").val(data['sex']);
			$("#pt_birthdate").val(data['bday']);
			$("#pt_age").val(data['age']);
			$("#pt_patientstat").val(data['patientstatus']);
			$("#pt_physician").val(data['physician']);
			$("#pt_procedure").val(data['procedure']);
			$("#pt_code").val(data['code']);
			$("#pt_spectype").val(data['sampletype']);
			$("#pt_serialno").val(data['serialno']);
			$("#pt_extractdate").val(data['exdate']);
			$("#pt_extracttime").val(data['extractime']);
			$("#pt_extractby").val(data['extractby']);
			$("#pt_result").val(data['result']);
			$("#pt_remarks").val(data['remarks']);
			$("#pt_location").val(data['location']);

			var dis = $("#pregnancyResult").dialog({
				title: "Write Result",
				width: 540,
				resizeable: false,
				modal: true,
				buttons: [
					{
						text: "Save Result Pending Validation",
						icons: { primary: "ui-icon-check" },
						click: function() {
							var msg = '';
	
							if(confirm("Are you sure you want save this data?") == true) {
							var dataString = $("#frmPregnancyResult").serialize();
								dataString = "mod=savePregnancyResult&" + dataString;
								$.ajax({
									type: "POST",
									url: "src/sjerp.php",
									data: dataString,
									success: function() {
										alert("Result Successfully Saved!");
										$("#frmPregnancyResult").trigger("reset");
										dis.dialog("close");
										showValidation();
				
									}
								});
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

function validatePregnancyResult(lid,code) {
	
	$.post("src/sjerp.php", {
		mod: "resultSingle",
		submod: "enumResult",
		lid: lid,
		sid: Math.random() },
		function(data) {

			$("#pt_enccode").val(data['enccode']);
			$("#pt_sodate").val(data['orderdate']);
			$("#pt_pid").val(data['hmrno']);
			$("#pt_pname").val(decodeURIComponent(data['pname']));
			$("#pt_gender").val(data['sex']);
			$("#pt_birthdate").val(data['bday']);
			$("#pt_age").val(data['age']);
			$("#pt_patientstat").val(data['patientstatus']);
			$("#pt_physician").val(data['physician']);
			$("#pt_procedure").val(data['procedure']);
			$("#pt_code").val(data['code']);
			$("#pt_spectype").val(data['sampletype']);
			$("#pt_serialno").val(data['serialno']);
			$("#pt_extractdate").val(data['exday']);
			$("#pt_extracttime").val(data['extractime']);
			$("#pt_extractby").val(data['extractby']);
			$("#pt_result").val(data['result']);
			$("#pt_remarks").val(data['remarks']);

			var dis = $("#pregnancyResult").dialog({
				title: "Validate Result",
				width: 540,
				resizeable: false,
				modal: true,
				buttons: [
					{
						text: "Save & Confirm Result",
						icons: { primary: "ui-icon-check" },
						click: function() {
							var msg = '';
	
							if(confirm("Are you sure you want confirm and publish this result?") == true) {
							var dataString = $("#frmPregnancyResult").serialize();
								dataString = "mod=validatePregnancyResult&" + dataString;
								$.ajax({
									type: "POST",
									url: "src/sjerp.php",
									data: dataString,
									success: function() {
										alert("Result Successfully Confirmed & Published");										
										showValidation();

										var enccode = $('#frmPregnancyResult').contents().find('#pt_enccode').val();
										var serialno = $('#frmPregnancyResult').contents().find('#pt_serialno').val();
										var code = $('#frmPregnancyResult').contents().find('#pt_code').val();

										var txtHTML = "<iframe id='prntXrayResult' frameborder=0 width='100%' height='100%' src='print/result.pt.php?enccode="+enccode+"&serialno="+serialno+"&code="+code+"&sid="+Math.random()+"'></iframe>";
										$("#report3").html(txtHTML);
										$("#report3").dialog({title: "Print - RESULT", width: 560, height: 620, resizable: true }).dialogExtend({
											"closable" : true,
											"maximizable" : true,
											"minimizable" : true
										});

										dis.dialog("close");
									}
								});
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

function bloodTyping(lid,code) {

	$("#btype_date").datepicker();
	
	$.post("src/sjerp.php", {
		mod: "resultSingle",
		submod: "bloodType",
		lid: lid,
		sid: Math.random() },
		function(data) {

			$("#btype_enccode").val(data['enccode']);
			$("#btype_sodate").val(data['orderdate']);
			$("#btype_pid").val(data['hmrno']);
			$("#btype_pname").val(decodeURIComponent(data['pname']));
			$("#btype_gender").val(data['sex']);
			$("#btype_birthdate").val(data['bday']);
			$("#btype_age").val(data['age']);
			$("#btype_physician").val(data['physician']);
			$("#btype_procedure").val(data['procedure']);
			$("#btype_code").val(data['code']);
			$("#btype_spectype").val(data['sampletype']);
			$("#btype_serialno").val(data['serialno']);
			$("#btype_extractdate").val(data['exdate']);
			$("#btype_extracttime").val(data['extractime']);
			$("#btype_extractby").val(data['extractby']);
			$("#btype_result").val(data['result']);
			$("#btype_rh").val(data['rh']);
			$("#btype_remarks").val(data['remarks']);

			var dis = $("#bloodtypeResult").dialog({
				title: "Write Result",
				width: 540,
				resizeable: false,
				modal: true,
				buttons: [
					{
						text: "Save Result Pending Validation",
						icons: { primary: "ui-icon-check" },
						click: function() {
							var msg = '';
	
							if(confirm("Are you sure you want save this data?") == true) {
							var dataString = $("#frmBloodType").serialize();
								dataString = "mod=saveBloodType&" + dataString;
								$.ajax({
									type: "POST",
									url: "src/sjerp.php",
									data: dataString,
									success: function() {
										alert("Result Successfully Saved!");
										$("#frmBloodType").trigger("reset");
										dis.dialog("close");
										showValidation();
									}
								});
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

function validateBloodtype(lid,code) {

	$("#btype_date").datepicker();
	
	$.post("src/sjerp.php", {
		mod: "resultSingle",
		submod: "bloodType",
		lid: lid,
		sid: Math.random() },
		function(data) {

			$("#btype_enccode").val(data['enccode']);
			$("#btype_sodate").val(data['orderdate']);
			$("#btype_pid").val(data['hmrno']);
			$("#btype_pname").val(decodeURIComponent(data['pname']));
			$("#btype_gender").val(data['sex']);
			$("#btype_birthdate").val(data['bday']);
			$("#btype_age").val(data['age']);
			$("#btype_physician").val(data['physician']);
			$("#btype_procedure").val(data['procedure']);
			$("#btype_code").val(data['code']);
			$("#btype_spectype").val(data['sampletype']);
			$("#btype_serialno").val(data['serialno']);
			$("#btype_extractdate").val(data['exdate']);
			$("#btype_extracttime").val(data['extractime']);
			$("#btype_extractby").val(data['extractby']);
			$("#btype_result").val(data['result']);
			$("#btype_rh").val(data['rh']);
			$("#btype_remarks").val(data['remarks']);

			var dis = $("#bloodtypeResult").dialog({
				title: "Write Result",
				width: 540,
				resizeable: false,
				modal: true,
				buttons: [
					{
						text: "Save & Confirm Result",
						icons: { primary: "ui-icon-check" },
						click: function() {
							var msg = '';
	
							if(confirm("Are you sure you want confirm and publish this result?") == true) {
							var dataString = $("#frmBloodType").serialize();
								dataString = "mod=validateBloodType&" + dataString;
								$.ajax({
									type: "POST",
									url: "src/sjerp.php",
									data: dataString,
									success: function() {
										alert("Result Successfully Validated!");
										showValidation();

										var enccode = $('#frmBloodType').contents().find('#btype_enccode').val();
										var serialno = $('#frmBloodType').contents().find('#btype_serialno').val();
										var code = $('#frmBloodType').contents().find('#btype_code').val();

										var txtHTML = "<iframe id='prntXrayResult' frameborder=0 width='100%' height='100%' src='print/result.bt.php?enccode="+enccode+"&serialno="+serialno+"&code="+code+"&sid="+Math.random()+"'></iframe>";
										$("#report3").html(txtHTML);
										$("#report3").dialog({title: "Print - BLOODTYPING RESULT", width: 560, height: 620, resizable: true }).dialogExtend({
											"closable" : true,
											"maximizable" : true,
											"minimizable" : true
										});

										dis.dialog("close");
									}
								});
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

function showUsers() {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='user.master.php'></iframe>";
	$("#userlist").html(txtHTML);
	$("#userlist").dialog({title: "System Users", width: xWidth, height: 540, resizable: false }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}

/* INVENTORY MANAGEMENT */
function showSRR() {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='srr.list.php'></iframe>";
	$("#srrlist").html(txtHTML);
	$("#srrlist").dialog({title: "Stocks Receiving Receipt Summary", width: xWidth, height: 540, resizable: false }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}

function viewSRR(srr_no) {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='srr.details.php?srr_no="+srr_no+"'></iframe>";
	$("#srrdetails").html(txtHTML);
	$("#srrdetails").dialog({title: "Stocks Receiving Receipt Details", width: 1120, height: 560, resizable: true }).dialogExtend({
		"closable" : true,
	    "maximizable" : true,
	    "minimizable" : true
	});
}

function printSRR(srr_no,uid,rePrint) {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='print/srr.print.php?srr_no="+srr_no+"&uid="+uid+"&rePrint="+rePrint+"&sid="+Math.random()+"'></iframe>";
	$("#srrprint").html(txtHTML);
	$("#srrprint").dialog({title: "PRINT >> Stocks Receiving Receipt", width: 560, height: 620, resizable: true }).dialogExtend({
		"closable" : true,
	    "maximizable" : true,
	    "minimizable" : true
	});
}

function showPhy() {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='phy.list.php'></iframe>";
	$("#phylist").html(txtHTML);
	$("#phylist").dialog({title: "Physical Inventory Summary", width: xWidth, height: 540, resizable: false }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}

function viewPhy(doc_no) {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='phy.details.php?doc_no="+doc_no+"'></iframe>";
	$("#phydetails").html(txtHTML);
	$("#phydetails").dialog({title: "Physical Inventory Form", width: 1120, height: 560, resizable: true }).dialogExtend({
		"closable" : true,
	    "maximizable" : true,
	    "minimizable" : true
	});
}


function printPhy(doc_no) {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='print/phy.print.php?doc_no="+doc_no+"&sid="+Math.random()+"'></iframe>";
	$("#srrprint").html(txtHTML);
	$("#srrprint").dialog({title: "PRINT >> Physical Inventory Form", width: 560, height: 620, resizable: true }).dialogExtend({
		"closable" : true,
	    "maximizable" : true,
	    "minimizable" : true
	});
}

function showSW() {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='sw.list.php'></iframe>";
	$("#swlist").html(txtHTML);
	$("#swlist").dialog({title: "Stocks Withdrawal Slip Summary", width: xWidth, height: 540, resizable: false }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}

function viewSW(sw_no) {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='sw.details.php?sw_no="+sw_no+"'></iframe>";
	$("#swdetails").html(txtHTML);
	$("#swdetails").dialog({title: "Stocks Withdrawal Slip Details", width: 1120, height: 560, resizable: true }).dialogExtend({
		"closable" : true,
	    "maximizable" : true,
	    "minimizable" : true
	});
}

function printSW(sw_no,uid,rePrint) {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='print/sw.print.php?sw_no="+sw_no+"&uid="+uid+"&rePrint="+rePrint+"&sid="+Math.random()+"'></iframe>";
	$("#srrprint").html(txtHTML);
	$("#srrprint").dialog({title: "PRINT >> STOCKS WITHDRAWAL SLIP", width: 560, height: 620, resizable: true }).dialogExtend({
		"closable" : true,
	    "maximizable" : true,
	    "minimizable" : true
	});
}

function showMRS() {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='mrs.list.php'></iframe>";
	$("#swlist").html(txtHTML);
	$("#swlist").dialog({title: "Material Request Slip", width: xWidth, height: 540, resizable: false }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}

function viewMRS(mrs_no) {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='mrs.details.php?mrs_no="+mrs_no+"'></iframe>";
	$("#swdetails").html(txtHTML);
	$("#swdetails").dialog({title: "Materials Request Details", width: 1120, height: 560, resizable: true }).dialogExtend({
		"closable" : true,
	    "maximizable" : true,
	    "minimizable" : true
	});
}

function printMRS(mrs_no,uid,rePrint) {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='print/mrs.print.php?mrs_no="+mrs_no+"&uid="+uid+"&rePrint="+rePrint+"&sid="+Math.random()+"'></iframe>";
	$("#srrprint").html(txtHTML);
	$("#srrprint").dialog({title: "PRINT >> Materials Requests", width: 560, height: 620, resizable: true }).dialogExtend({
		"closable" : true,
	    "maximizable" : true,
	    "minimizable" : true
	});
}

function showSTR() {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='str.list.php'></iframe>";
	$("#strlist").html(txtHTML);
	$("#strlist").dialog({title: "Stocks Transfer Receipt Summary", width: xWidth, height: 540, resizable: false }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}

function viewSTR(str_no) {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='str.details.php?str_no="+str_no+"'></iframe>";
	$("#strdetails").html(txtHTML);
	$("#strdetails").dialog({title: "Stocks Transfer Receipt Details", width: xWidth, height: 560, resizable: true }).dialogExtend({
		"closable" : true,
	    "maximizable" : true,
	    "minimizable" : true
	});
}

function printSTR(str_no,uid,rePrint) {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='print/str.print.php?str_no="+str_no+"&uid="+uid+"&rePrint="+rePrint+"&sid="+Math.random()+"'></iframe>";
	$("#srrprint").html(txtHTML);
	$("#srrprint").dialog({title: "PRINT >> STOCKS TRANSFER RECEIPT", width: 560, height: 620, resizable: true }).dialogExtend({
		"closable" : true,
	    "maximizable" : true,
	    "minimizable" : true
	});
}

function showIBook() {
	$("#ibook_dtf").datepicker(); $("#ibook_dt2").datepicker(); 
	$("#inventorybook").dialog({title: "Inventory Summary", width: 480 }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}

function processInventory() {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='ibook.php?group="+$("#ibook_group").val()+"&dtf="+$("#ibook_dtf").val()+"&dt2="+$("#ibook_dt2").val()+"'></iframe>";
	$("#ibook").html(txtHTML);
	$("#ibook").dialog({title: "Inventory Summary", width: xWidth, height: 540, resizable: false }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}

function jumpIBookPage(page,stxt,group,dtf,dt2) {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='ibook.php?page="+page+"&searchtext="+stxt+"&group="+group+"&dtf="+dtf+"&dt2="+dt2+"'></iframe>";
	$("#ibook").html(txtHTML);
	$("#ibook").dialog({title: "Inventory Summary", width: xWidth, height: 540, resizable: false }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}

function viewStockcard(item_code,lot_no,expiry,dtf,dt2) {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='stockcard.php?item_code="+item_code+"&lot_no="+lot_no+"&expiry="+expiry+"&dtf="+dtf+"&dt2="+dt2+"'></iframe>";
	$("#stockcard").html(txtHTML);
	$("#stockcard").dialog({title: "Inventory Stockcard", width: xWidth, height: 540, resizable: false }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}

function exportStockcard(item_code,unit,dtf,dt2) {
	window.open("export/stockard.php?item_code="+item_code+"&unit="+unit+"&dtf="+dtf+"&dt2="+dt2+"&sid="+Math.random()+"","Inventory Stockcard","location=1,status=1,scrollbars=1,width=640,height=720");
}

function exportInventoryNow() {
	window.open("export/ibook.php?group="+$("#ibook_group").val()+"&dtf="+$("#ibook_dtf").val()+"&dt2="+$("#ibook_dt2").val()+"&sid="+Math.random()+"","Inventory Book","location=1,status=1,scrollbars=1,width=640,height=720");
}

function showLabSummary() {
	$("#census_dtf").datepicker(); $("#census_dt2").datepicker(); 
	$("#censusReport").dialog({
		title: "Summary of Performed Test", 
		width: 480,
		modal: true,
		resizable: false,
		buttons: {
			"Generate Report": function() {
				var type = $("#census_type").val();
				if(type == 1) {
					window.open("export/census_summary.php?category="+$("#census_category").val()+"&dtf="+$("#census_dtf").val()+"&dt2="+$("#census_dt2").val()+"&sid="+Math.random()+"","Summary of Performed Tests","location=1,status=1,scrollbars=1,width=640,height=720");
				} else {
					window.open("export/census_detailed.php?category="+$("#census_category").val()+"&dtf="+$("#census_dtf").val()+"&dt2="+$("#census_dt2").val()+"&sid="+Math.random()+"","Summary of Performed Tests","location=1,status=1,scrollbars=1,width=640,height=720");
				}
			},
			"Close": function() {
				$(this).dialog("close");
			}
		} 
	});
}