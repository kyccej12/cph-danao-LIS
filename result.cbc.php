<?php 
	
	session_start();
	require_once "handlers/_generics.php";
	
    $o = new _init;

    $order = $o->getArray("select a.*, date_format(extractdate,'%m/%d/%Y') as exdate, a.hpercode as hmrno,  DATE_FORMAT(dotime,'%m/%d/%Y %h:%i %p') AS orderdate, DATE_FORMAT(dotime,'%Y-%m-%d') AS xorderdate, CONCAT(b.patlast,', ', b.patfirst,', ', b.patmiddle) AS pname,DATE_FORMAT(b.patbdate,'%m/%d/%Y') AS bday, DATE_FORMAT(b.patbdate,'%Y-%m-%d') AS xbday, IF(b.patsex='F','FEMALE','MALE') AS sex, patsex AS gender from lab_samples a left join hospital_dbo.hperson b on a.hpercode = b.hpercode where a.record_id = '$_REQUEST[lid]';");
    
    list($isResult) = $o->getArray("select count(*) from lab_cbcresult where enccode = '$order[enccode]' and serialno = '$order[serialno]';");
    $o->calculateAge($order['xorderdate'],$order['xbday']);

    if($isResult > 0) {   
        $b = $o->getArray("select * from lab_cbcresult where enccode = '$order[enccode]' and serialno = '$order[serialno]';");
    } else {
        list($tCount) = $o->getArray("select count(*) FROM (SELECT DISTINCT wbc,rbc,hemoglobin,hematocrit,neutrophils,lymphocytes,monocytes,eosinophils,basophils,platelate,mcv,mch,mchc,rdwcv,rdwsd,mpv,pdwcv,pdwsd,pct,plcc,plcr FROM lab_cbcresult_temp WHERE serialno = '$order[serialno]') a;");
        if($tCount > 1) {

        } else {
            $b = $o->getArray("select * from lab_cbcresult_temp where serialno = '$order[serialno]' limit 1;");
        }    
    }

    /* Previous Results */
    $c = $o->getArray("select *, concat('<br/>',date_format(result_date,'%m/%d/%Y')) as rdate from lab_cbcresult where SUBSTR(enccode,8,15) = '$order[hmrno]' and result_date < '$order[xorderdate]' limit 0,1;");
    $d = $o->getArray("select *, concat('<br/>',date_format(result_date,'%m/%d/%Y')) as rdate from lab_cbcresult where SUBSTR(enccode,8,15) = '$order[hmrno]' and result_date < '$order[xorderdate]' limit 1,1;");
    $e = $o->getArray("select *, concat('<br/>',date_format(result_date,'%m/%d/%Y')) as rdate from lab_cbcresult where SUBSTR(enccode,8,15) = '$order[hmrno]' and result_date < '$order[xorderdate]' limit 2,1;");
    

?>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<title>Primecare Cebu WebLIS System Ver. 1.0b</title>
	<link rel="icon" type="image/x-icon" href="favicon.ico" />
	<link href="ui-assets/themes/smoothness/jquery-ui.css" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" type="text/css" href="ui-assets/datatables/css/jquery.dataTables.css">
	<link href="style/style.css" rel="stylesheet" type="text/css" />
	<script language="javascript" src="ui-assets/jquery/jquery-1.12.3.js"></script>
	<script language="javascript" src="ui-assets/themes/smoothness/jquery-ui.js"></script>
    <script type="text/javascript" charset="utf8" src="ui-assets/datatables/js/jquery.dataTables.js"></script>
    <script type="text/javascript" charset="utf8" src="ui-assets/datatables/js/dataTables.jqueryui.js"></script>
    <script type="text/javascript" charset="utf8" src="ui-assets/datatables/js/dataTables.select.js"></script>
	<script language="javascript" src="js/main.js?sid=<?php echo uniqid(); ?>"></script>
    <script>
        $(function() { 

            <?php if($tCount > 1) { ?>
            if(confirm("It appears this request has been performed multiple times. Do you wish to choose from the available results?") == true) {
                var dis = $("#multiResult").dialog({
                    title: "Result Selection", 
                    width: 720, 
                    height: 580,
                    resizable: false,
                    modal: true,
                    buttons: {
                        "Close Window": function() {
                            dis.dialog("close");
                        }

                    } 
                });
            }
            <?php } ?>

            $("#cbc_date").datepicker(); 
        
            var myTable = $('#itemlist').DataTable({
                "scrollY":  "505",
                "scrollCollapse": true,
                "select":	'single',
                "searching": false,
                "bSort": false,
                "paging": false,
                "info": false,
              
                "aoColumnDefs": [
                    { "className": "dt-body-center", "targets": [1,2,3,4,5,6] },
                ]
            });

            var myTable2 = $('#itemlist2').DataTable({
                "scrollY":  "540",
                "scrollCollapse": true,
                "select":	'single',
                "searching": false,
                "bSort": false,
                "paging": false,
                "info": false
            });


            var remarksSelection = [
                "MANUAL PLATELET DONE",
            ];

            $("#remarks").autocomplete({
                source: remarksSelection,
                minLength: 0
            }).focus(function() {
                $(this).data("uiAutocomplete").search($(this).val());
            });
        
        });

        $(document).on('keydown', 'input[pattern]', function(e){
            var input = $(this);
            var oldVal = input.val();
            var regex = new RegExp(input.attr('pattern'), 'g');

            setTimeout(function(){
                var newVal = input.val();
                if(!regex.test(newVal)){
                input.val(oldVal); 
                }
            }, 1);
        });

        $(document).on('keypress', 'input', function(e) {
            if(e.keyCode == 13) {
                e.preventDefault();
                var inputs = $(this).closest('form').find(':input:visible');
                inputs.eq( inputs.index(this)+ 1 ).focus();
            }
        });

        $('input.number').keyup(function (event) {
                // skip for arrow keys
                if (event.which >= 37 && event.which <= 40) return;
                // format number
                $(this).val(function (index, value) {
                    return value
                    .replace(/\D/g, "")
                    .replace(/\B(?=(\d{3})+(?!\d))/g, ",")
                    ;
                });
            });

        function separateMe(val) {

            valu = parseFloat(parent.stripComma(val));

            $("#platelate").val(parent.kSeparator(valu));
        }


        function save() {
            if(confirm("Are you sure you want save changes made to this result?") == true) {
                var msg = '';
                
                if($("#wbc").val() == '' ) { msg = msg + "- Invalid or Empty Value for <b>WBC</b> count<br/>"; }
                if($("#rbc").val() == '' ) { msg = msg + "- Invalid or Empty Value for <b>RBC</b> count<br/>"; }
                if($("#hemoglobin").val() == '' ) { msg = msg + "- Invalid or Empty Value for <b>Hemoglobin</b> count<br/>"; }
                if($("#hematocrit").val() == '' ) { msg = msg + "- Invalid or Empty Value for <b>Hematocrit</b> count<br/>"; }
                if($("#platelate").val() == '' ) { msg = msg + "- Invalid or Empty Value for <b>Platelate</b> count<br/>"; }

                var totalDifferential = parseFloat($("#neutrophils").val()) + parseFloat($("#lymphocytes").val()) + parseFloat($("#monocytes").val()) + parseFloat($("#eosinophils").val()) + parseFloat($("#basophils").val());
                    totalDifferential = totalDifferential.toFixed(2);
                if(totalDifferential != 100) { msg = msg + "- <b>Total Differential Count</b> != <b>100%</b><br/>"; }


                if(msg != '') {
                    parent.sendErrorMessage(msg);
                } else {
                    var dataString = $('#frmCBCResult').serialize();
                    dataString = "mod=saveCBCResult&" + dataString;
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
        }

        function validate() {
            if(confirm("Are you sure you want validate & finalize this result?") == true) {
                var dataString = $('#frmCBCResult').serialize();
                dataString = "mod=validateCBCResult&" + dataString;
                $.ajax({
                    type: "POST",
                    url: "src/sjerp.php",
                    data: dataString,
                    success: function() {
                        alert("Result Successfully Marked as Validated!");
                        parent.showValidation();
                        parent.closeDialog("#cbcResult");
                        print();
                    }
                });
            }
        }

        function print() {
            var enccode = $('#cbc_enccode').val();
            var serialno = $('#cbc_serialno').val();
            var code = $('#cbc_code').val();

            parent.printCBCResult(enccode,serialno,code);

        }

        function showOptions() {
            var dis = $("#multiResult").dialog({
                    title: "Result Selection", 
                    width: 720, 
                    height: 580,
                    resizable: false,
                    modal: true,
                    buttons: {
                        "Close Window": function() {
                            dis.dialog("close");
                        }

                    } 
                });
        }

        function computeMCV() {
            var a = $("#hematocrit").val();
            var b = $("#rbc").val();

            if(a != '' && b != '') {
                var c = (parseFloat(a) / parseFloat(b)) * 10;
                $("#mcv").val(c.toFixed(2));

            }
        }

        function changeMachine(val) {
            $.post("src/sjerp.php", { mod: "changeCbcMachine", enccode: $("#cbc_enccode").val(), serialno: $("#cbc_serialno").val(), machine: val, sid: Math.random() }, function() {
                setTimeout(function(){ 
                    //location.reload();
                },350);
            });
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


    </style>
</head>
<body >
    <form name="frmCBCResult" id="frmCBCResult"> 
        <table width=100% cellpadding=0 cellspacing=0 valign=top>
         <tr>
             <td width=30% valign=top>
                <table width=100% cellspacing=0 cellpadding=0><tr><td width=100% class=gridHead align=center>PATIENT & ORDER INFORMATION</td></tr></table>
                <table width=100% cellpadding=0 cellspacing=0 style="border: 1px solid #cdcdcd; padding: 10px; margin-bottom: 5px;">
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%"  class="bareBold" style="padding-left: 15px;">REFERENCE CODE&nbsp;:</td>
                        <td align=left>
                            <input class="gridInput" style="width:100%;" type=text name="cbc_enccode" id="cbc_enccode" value="<?php echo $order['enccode']; ?>">
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%"  class="bareBold" style="padding-left: 15px;">Request Date&nbsp;:</td>
                        <td align=left>
                            <input class="gridInput" style="width:100%;" type=text name="cbc_sodate" id="cbc_sodate" value="<?php echo $order['orderdate']; ?>">
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">HMR #&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="cbc_pid" id="cbc_pid" value="<?php echo $order['hmrno']; ?>">
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%"  class="bareBold" style="padding-left: 15px;">Result Date&nbsp;:</td>
                        <td align=left>
                            <input class="gridInput" style="width:100%;" type=text name="cbc_date" id="cbc_date" value="<?php if($rdate !='') { echo $rdate; } else { echo date('m/d/Y'); } ?>">
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Patient Name&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="cbc_pname" id="cbc_pname" value="<?php echo $order['pname']; ?>">
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>

                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Gender&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="cbc_gender" id="cbc_gender" value="<?php echo $order['sex']; ?>">
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Birthdate&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="cbc_birthdate" id="cbc_birthdate" value="<?php echo $order['bday']; ?>">
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Age&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="cbc_age" id="cbc_age" value="<?php echo $o->ageDisplay; ?>">
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Requesting Physician&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="cbc_physician" id="cbc_physician" value="<?php echo $order['physician']; ?>">
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                </table>
                <table width=100% cellspacing=0 cellpadding=0><tr><td width=100% class=gridHead align=center>SAMPLE DETAILS</td></tr></table>
                <table width=100% cellpadding=0 cellspacing=0 style="border: 1px solid #cdcdcd; padding: 10px;">
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Test or Procedure&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="cbc_procedure" id="cbc_procedure" value="<?php echo $order['procedure']; ?>">
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Procedure Code&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="cbc_ihomis_code" id="cbc_ihomis_code" value="<?php echo $order['code']; ?>">
                            <input type="hidden" class="gridInput" style="width:100%;" name="cbc_code" id="cbc_code" value="<?php echo $order['primecarecode']; ?>">
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Specimen Type&nbsp;:</td>
                        <td align=left>
                            <select class="gridInput" style="width:100%;" name="cbc_spectype" id="cbc_spectype">
                                <?php
                                    $iun = $o->dbquery("select id,sample_type from options_sampletype;");
                                    while(list($aa,$ab) = $iun->fetch_array()) {
                                        echo "<option value='$aa'";
                                        if($aa == $order['sampletype']) { echo "selected"; }
                                       echo ">$ab</option>";
                                    }
                                ?>
                            </select>
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Machine&nbsp;:</td>
                        <td align=left>
                            <select class="gridInput" style="width:100%;" name="cbc_machine" id="cbc_machine" onchange="javascript: changeMachine(this.value);">
                               <option value = 'GENRUI' <?php if($b['machine'] == 'GENRUI') { echo "selected"; } ?>>Genrui KT-6610</option>
                               <option value = 'H500' <?php if($b['machine'] == 'H500') { echo "selected"; } ?>>Yumizen H500</option>
                            </select>
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Sample Serial No.&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="cbc_serialno" id="cbc_serialno" value="<?php echo $order['serialno']; ?>">
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Date Extracted&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="cbc_extractdate" id="cbc_extractdate" value="<?php echo $order['exdate']; ?>">
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Time Extracted&nbsp;:</td>
                        <td align=left>
                
                            <input type="text" class="gridInput" style="width:100%;" name="cbc_extracttime" id="cbc_extracttime" value="<?php echo $order['extractime']; ?>" readonly>

                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Extracted By&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="cbc_extractby" id="cbc_extractby" value="<?php echo $order['extractby']; ?>" readonly>
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Section&nbsp;:</td>
                        <td align=left>
                            <select class="gridInput" style="width:100%;" name="cbc_location" id="cbc_location">
                                <?php
                                    $iun = $o->dbquery("select id,location from lab_locations;");
                                    while(list($aa,$ab) = $iun->fetch_array()) {
                                        echo "<option value='$aa' ";
                                        if($aa == $order['location']) { echo "selected"; }
                                        echo ">$ab</option>";
                                    }
                                ?>
                            </select>
                        </td>				
                    </tr>
                </table>   
            </td>
            <td width=1%>&nbsp;</td>
            <td width=69% valign=top >
                <table width=100% id = "itemlist" class="cell-border" style="font-size:11px;">
                    <thead>
                        <tr>
                            <th>PARAMETER</th>
                            <th>CURRENT</th>
                            <th>FLAG</th>
                            <th>PREVIOUS <?php echo $c['rdate']; ?></th>
                            <th>PREVIOUS <?php echo $d['rdate']; ?></th>
                            <th>PREVIOUS <?php echo $e['rdate']; ?></th>
                            <th>REFERENCE VALUES</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>WBC</td>
                            <td><input type="text" style="border: none; text-align: center; background-color: inherit !important;"  name="wbc" id="wbc" pattern="^\d*(\.\d{0,2})?$" value="<?php echo $b['wbc']; ?>"></td>
                            <td><?php echo $o->checkCBCValues($o->age,$order['gender'],"WBC",$b['wbc']); ?></td>
                            <td><?php echo $c['wbc']; ?></td>
                            <td><?php echo $d['wbc']; ?></td>
                            <td><?php echo $e['wbc']; ?></td>
                            <td align="left"><?php echo $o->getCBCAttribute2($o->age,$order['gender'],"WBC",$order['machine']); ?></td>	
                        </tr>
                        <tr>
                            <td>RBC</td>
                            <td><input type="text" style="border: none; text-align: center; background-color: inherit !important;" name="rbc" id="rbc" pattern="^\d*(\.\d{0,2})?$" value="<?php echo $b['rbc']; ?>"></td>
                            <td><?php echo $o->checkCBCValues($o->age,$order['gender'],"RBC",$b['rbc']); ?></td>
                            <td><?php echo $c['rbc']; ?></td>
                            <td><?php echo $d['rbc']; ?></td>
                            <td><?php echo $e['rbc']; ?></td>
                            <td><?php echo $o->getCBCAttribute2($o->age,$order['gender'],"RBC",$order['machine']); ?></td>	
                        </tr>
                        <tr>
                            <td>Hemoglobin</td>
                            <td align=left><input type="text" style="border: none; text-align: center; background-color: inherit !important;" name="hemoglobin" id="hemoglobin" pattern="^\d*(\.\d{0,2})?$" value="<?php echo $b['hemoglobin']; ?>"></td>
                            <td><?php echo $o->checkCBCValues($o->age,$order['gender'],"HEMOGLOBIN",$b['hemoglobin']); ?></td>
                            <td><?php echo $c['hemoglobin']; ?></td>
                            <td><?php echo $d['hemoglobin']; ?></td>
                            <td><?php echo $e['hemoglobin']; ?></td>
                            <td align="left"><?php echo $o->getCBCAttribute2($o->age,$order['gender'],"HEMOGLOBIN",$order['machine']); ?></td>	
                        </tr>
                        <tr>
                            <td>HEMATOCRIT</td>
                            <td><input type="text" style="border: none; text-align: center; background-color: inherit !important;" name="hematocrit" id="hematocrit" pattern="^\d*(\.\d{0,2})?$" value="<?php echo $b['hematocrit']; ?>"></td>
                            <td><?php echo $o->checkCBCValues($o->age,$order['gender'],"HEMATOCRIT",$b['hematocrit']); ?></td>
                            <td><?php echo $c['hematocrit']; ?></td>
                            <td><?php echo $d['hematocrit']; ?></td>
                            <td><?php echo $e['hematocrit']; ?></td>
                            <td><?php echo $o->getCBCAttribute2($o->age,$order['gender'],"HEMATOCRIT",$order['machine']); ?></td>	
                        </tr>
                        <tr>
                            <td colspan=7><b>DIFFERENTIAL COUNT</b></td>
                            <td style="display: none;"></td>
                            <td style="display: none;"></td>
                            <td style="display: none;"></td>
                            <td style="display: none;"></td>
                            <td style="display: none;"></td>
                            <td style="display: none;"></td>
                        </tr>
                        <tr>
                            <td style="padding-left: 35px;">Neutrophils</td>
                            <td><input style="border: none; text-align: center; background-color: inherit !important;" name="neutrophils" id="neutrophils" pattern="^\d*(\.\d{0,2})?$" value="<?php echo $b['neutrophils']; ?>"></td>
                            <td><?php echo $o->checkCBCValues($o->age,$order['gender'],"NEUTROPHILS",$b['neutrophils']); ?></td>
                            <td><?php echo $c['neutrophils']; ?></td>
                            <td><?php echo $d['neutrophils']; ?></td>
                            <td><?php echo $e['neutrophils']; ?></td>
                            <td><?php echo $o->getCBCAttribute2($o->age,$order['gender'],"NEUTROPHILS",$order['machine']); ?></td>	
                        </tr>
                        <tr>
                            <td style="padding-left: 35px;">Lymphocytes</td>
                            <td><input type="text" style="border: none; text-align: center; background-color: inherit !important;" name="lymphocytes" id="lymphocytes" pattern="^\d*(\.\d{0,2})?$" value="<?php echo $b['lymphocytes']; ?>"></td>
                            <td><?php echo $o->checkCBCValues($o->age,$order['gender'],"LYMPHOCYTES",$b['lymphocytes']); ?></td>
                            <td><?php echo $c['lymphocytes']; ?></td>
                            <td><?php echo $d['lymphocytes']; ?></td>
                            <td><?php echo $e['lymphocytes']; ?></td>
                            <td><?php echo $o->getCBCAttribute2($o->age,$order['gender'],"LYMPHOCYTES",$order['machine']); ?></td>	
                        </tr>
                        <tr>
                            <td style="padding-left: 35px;">Monocytes</td>
                            <td><input type="text" style="border: none; text-align: center; background-color: inherit !important;" name="monocytes" id="monocytes" pattern="^\d*(\.\d{0,2})?$" value="<?php echo $b['monocytes']; ?>"></td>
                            <td><?php echo $o->checkCBCValues($o->age,$order['gender'],"MONOCYTES",$b['monocytes']); ?></td>
                            <td><?php echo $c['monocytes']; ?></td>
                            <td><?php echo $d['monocytes']; ?></td>
                            <td><?php echo $e['monocytes']; ?></td>
                            <td><?php echo $o->getCBCAttribute2($o->age,$order['gender'],"MONOCYTES",$order['machine']); ?></td>	
                        </tr>
                        <tr>
                            <td style="padding-left: 35px;">Eosinophils</td>
                            <td><input type="text" style="border: none; text-align: center; background-color: inherit !important;" name="eosinophils" id="eosinophils" pattern="^\d*(\.\d{0,2})?$" value="<?php echo $b['eosinophils']; ?>"></td>
                            <td><?php echo $o->checkCBCValues($o->age,$order['gender'],"EOSINOPHILS",$b['eosinophils']); ?></td>
                            <td><?php echo $c['eosinophils']; ?></td>
                            <td><?php echo $d['eosinophils']; ?></td>
                            <td><?php echo $e['eosinophils']; ?></td>
                            <td><?php echo $o->getCBCAttribute2($o->age,$order['gender'],"EOSINOPHILS",$order['machine']); ?></td>	
                        </tr>
                        <tr>
                            <td style="padding-left: 35px;">Basophils</td>
                            <td><input style="border: none; text-align: center; background-color: inherit !important;" name="basophils" id="basophils" pattern="^\d*(\.\d{0,2})?$" value="<?php echo $b['basophils']; ?>"></td>
                            <td><?php echo $o->checkCBCValues($o->age,$order['gender'],"BASOPHILS",$b['basophils']); ?></td>
                            <td><?php echo $c['basophils']; ?></td>
                            <td><?php echo $d['basophils']; ?></td>
                            <td><?php echo $e['basophils']; ?></td>
                            <td><?php echo $o->getCBCAttribute2($o->age,$order['gender'],"BASOPHILS",$order['machine']); ?></td>	
                        </tr>
                        <tr>
                            <td>MCV</td>
                            <td>
                                <input type="text" style="border: none; text-align: center; background-color: inherit !important;" name="mcv" class="number" id="mcv" pattern="^\d*(\.\d{0,2})?$" value="<?php if($b['mcv'] > 0) { echo $b['mcv']; } ?>">
                            </td>
                            <td><?php echo $o->checkCBCValues($o->age,$order['gender'],"MCV",$b['mcv']) ?></td>
                            <td><?php echo $c['mcv']; ?></td>
                            <td><?php echo $d['mcv']; ?></td>
                            <td><?php echo $e['mcv']; ?></td>
                            <td><?php echo $o->getCBCAttribute2($o->age,$order['gender'],"MCV",$order['machine']); ?></td>	
                        </tr>
                        <tr>
                            <td>MCH</td>
                            <td>
                                <input type="text" style="border: none; text-align: center; background-color: inherit !important;" name="mch" class="number" id="mch" pattern="^\d*(\.\d{0,2})?$" value="<?php if($b['mch'] > 0) { echo $b['mch']; } ?>">
                            </td>
                            <td><?php echo $o->checkCBCValues($o->age,$order['gender'],"MCH",$b['mch']); ?></td>
                            <td><?php echo $c['mch']; ?></td>
                            <td><?php echo $d['mch']; ?></td>
                            <td><?php echo $e['mch']; ?></td>
                            <td><?php echo $o->getCBCAttribute2($o->age,$order['gender'],"MCH",$order['machine']); ?></td>	
                        </tr>
                        <tr>
                            <td>MCHC</td>
                            <td>
                                <input type="text" style="border: none; text-align: center; background-color: inherit !important;" name="mchc" class="number" id="mchc" pattern="^\d*(\.\d{0,2})?$" value="<?php if($b['mchc'] > 0) { echo $b['mchc']; } ?>">
                            </td>
                            <td><?php echo $o->checkCBCValues($o->age,$order['gender'],"MCHC",$b['mchc']); ?></td>
                            <td><?php echo $c['mchc']; ?></td>
                            <td><?php echo $d['mchc']; ?></td>
                            <td><?php echo $e['mchc']; ?></td>
                            <td><?php echo $o->getCBCAttribute2($o->age,$order['gender'],"MCHC",$order['machine']); ?></td>	
                        </tr>
                        <tr>
                            <td>RDW-CV</td>
                            <td>
                                <input type="text" style="border: none; text-align: center; background-color: inherit !important;" name="rdwcv" class="number" id="rdwcv" pattern="^\d*(\.\d{0,2})?$" value="<?php if($b['rdwcv'] > 0) { echo $b['rdwcv']; } ?>">
                            </td>
                            <td><?php echo $o->checkCBCValues($o->age,$order['gender'],"RDW-CV",$b['rdwcv']) ?></td>
                            <td><?php echo $c['rdwcv']; ?></td>
                            <td><?php echo $d['rdwcv']; ?></td>
                            <td><?php echo $e['rdwcv']; ?></td>
                            <td><?php echo $o->getCBCAttribute2($o->age,$order['gender'],"RDW-CV",$order['machine']); ?></td>	
                        </tr>
                        <tr>
                            <td>RDW-SD</td>
                            <td>
                                <input type="text" style="border: none; text-align: center; background-color: inherit !important;" name="rdwsd" class="number" id="rdwsd" pattern="^\d*(\.\d{0,2})?$" value="<?php if($b['rdwsd'] > 0) { echo $b['rdwsd']; } ?>">
                            </td>
                            <td><?php echo $o->checkCBCValues($o->age,$order['gender'],"RDW-SD",$b['rdwsd']); ?></td>
                            <td><?php echo $c['rdwsd']; ?></td>
                            <td><?php echo $d['rdwsd']; ?></td>
                            <td><?php echo $e['rdwsd']; ?></td>
                            <td><?php echo $o->getCBCAttribute2($o->age,$order['gender'],"RDW-SD",$order['machine']); ?></td>	
                        </tr>
                        <tr>
                            <td>Platelet Count</td>
                            <td>
                                <input type="text" style="border: none; text-align: center; background-color: inherit !important;" name="platelate" class="number" id="platelate" pattern="^\d*(\.\d{0,2})?$" value="<?php if($b['platelate'] > 0) { echo $b['platelate']; } ?>" onchange="javascript: separateMe(this.value);">
                            </td>
                            <td><?php echo $o->checkCBCValues($o->age,$order['gender'],"PLATELATE",$b['platelate']); ?></td>
                            <td><?php echo $c['platelate']; ?></td>
                            <td><?php echo $d['platelate']; ?></td>
                            <td><?php echo $e['platelate']; ?></td>
                            <td><?php echo $o->getCBCAttribute2($o->age,$order['gender'],"PLATELATE",$order['machine']); ?></td>	
                        </tr>
                        <tr>
                            <td>MPV</td>
                            <td>
                                <input type="text" style="border: none; text-align: center; background-color: inherit !important;" name="mpv" class="number" id="mpv" pattern="^\d*(\.\d{0,2})?$" value="<?php if($b['mpv'] > 0) { echo $b['mpv']; } ?>">
                            </td>
                            <td><?php echo $o->checkCBCValues($o->age,$order['gender'],"MPV",$b['mpv']); ?></td>
                            <td><?php echo $c['mpv']; ?></td>
                            <td><?php echo $d['mpv']; ?></td>
                            <td><?php echo $e['mpv']; ?></td>
                            <td><?php echo $o->getCBCAttribute2($o->age,$order['gender'],"MPV",$order['machine']); ?></td>	
                        </tr>
                        <tr>
                            <td>PDW-CV</td>
                            <td>
                                <input type="text" style="border: none; text-align: center; background-color: inherit !important;" name="pdwcv" class="number" id="pdwcv" pattern="^\d*(\.\d{0,2})?$" value="<?php if($b['pdwcv'] > 0) { echo $b['pdwcv']; } ?>">
                            </td>
                            <td><?php echo $o->checkCBCValues($o->age,$order['gender'],"PDW-CV",$b['pdwcv']); ?></td>
                            <td><?php echo $c['pdwcv']; ?></td>
                            <td><?php echo $d['pdwcv']; ?></td>
                            <td><?php echo $e['pdwcv']; ?></td>
                            <td><?php echo $o->getCBCAttribute2($o->age,$order['gender'],"PDW-CV",$order['machine']); ?></td>	
                        </tr>
                        <tr>
                            <td>PDW-SD</td>
                            <td>
                                <input type="text" style="border: none; text-align: center; background-color: inherit !important;" name="pdwsd" class="number" id="pdwsd" pattern="^\d*(\.\d{0,2})?$" value="<?php if($b['pdwsd'] > 0) { echo $b['pdwsd']; } ?>">
                            </td>
                            <td><?php echo $o->checkCBCValues($o->age,$order['gender'],"PDW-SD",$b['pdwsd']); ?></td>
                            <td><?php echo $c['pdwsd']; ?></td>
                            <td><?php echo $d['pdwsd']; ?></td>
                            <td><?php echo $e['pdwsd']; ?></td>
                            <td><?php echo $o->getCBCAttribute2($o->age,$order['gender'],"PDW-SD",$order['machine']); ?></td>	
                        </tr>
                        <tr>
                            <td>PCT</td>
                            <td>
                                <input type="text" style="border: none; text-align: center; background-color: inherit !important;" name="pct" class="number" id="pct" pattern="^\d*(\.\d{0,2})?$" value="<?php if($b['pct'] > 0) { echo $b['pct']; } ?>">
                            </td>
                            <td><?php echo $o->checkCBCValues($o->age,$order['gender'],"PCT",$b['pct']); ?></td>
                            <td><?php echo $c['pct']; ?></td>
                            <td><?php echo $d['pct']; ?></td>
                            <td><?php echo $e['pct']; ?></td>
                            <td><?php echo $o->getCBCAttribute2($o->age,$order['gender'],"PCT",$order['machine']); ?></td>	
                        </tr>
                        <tr>
                            <td>P-LCC</td>
                            <td>
                                <input type="text" style="border: none; text-align: center; background-color: inherit !important;" name="plcc" class="number" id="plcc" pattern="^\d*(\.\d{0,2})?$" value="<?php if($b['plcc'] > 0) { echo $b['plcc']; } ?>">
                            </td>
                            <td><?php echo $o->checkCBCValues($o->age,$order['gender'],"P-LCC",$b['plcc']); ?></td>
                            <td><?php echo $c['plcc']; ?></td>
                            <td><?php echo $d['plcc']; ?></td>
                            <td><?php echo $e['plcc']; ?></td>
                            <td><?php echo $o->getCBCAttribute2($o->age,$order['gender'],"P-LCC",$order['machine']); ?></td>	
                        </tr>
                        <tr>
                            <td>P-LCR</td>
                            <td>
                                <input type="text" style="border: none; text-align: center; background-color: inherit !important;" name="plcr" class="number" id="plcr" pattern="^\d*(\.\d{0,2})?$" value="<?php if($b['plcr'] > 0) { echo $b['plcr']; } ?>">
                            </td>
                            <td><?php echo $o->checkCBCValues($o->age,$order['gender'],"P-LCR",$b['plcr']); ?></td>
                            <td><?php echo $c['plcr']; ?></td>
                            <td><?php echo $d['plcr']; ?></td>
                            <td><?php echo $e['plcr']; ?></td>
                            <td><?php echo $o->getCBCAttribute2($o->age,$order['gender'],"P-LCR",$order['machine']); ?></td>	
                        </tr>
                        <tr>
                            <td valign=top>Remarks</td>
                            <td colspan=6>
                                <textarea name="remarks" id="remarks" style="width: 90%;" rows=3><?php echo $b['remarks']; ?></textarea>
                            </td>
                            <td style="display: none;"></td>
                            <td style="display: none;"></td>
                            <td style="display: none;"></td>
                            <td style="display: none;"></td>
                            <td style="display: none;"></td>
                            <td style="display: none;"></td>
                            <td style="display: none;"></td>
                        </tr>
                    </tbody>
                </table>
            </td>
        </tr>
        <tr>
            <td colspan=3 align=right style="padding-top: 10px;">
                <?php

                    $buttons = '';

                    switch($order['status']) {
                        case "1":
                            $buttons .= '
                                <button type = "button" class="ui-button ui-widget ui-corner-all" onClick="save();">
                                    <span class="ui-icon ui-icon-disk"></span> Accept & Save Result Pending validation
                                </button>  
                            ';

                        break;
                        case "3":
                            $buttons .= '
                            <button type = "button" class="ui-button ui-widget ui-corner-all" onClick="validate();">
                                <span class="ui-icon ui-icon-check"></span> Validate & Print Result
                            </button> 
                            <button type = "button" class="ui-button ui-widget ui-corner-all" onClick="save();">
                                <span class="ui-icon ui-icon-disk"></span> Save Changes Made
                            </button>
                            <button type = "button" class="ui-button ui-widget ui-corner-all" onClick="print();">
                                <span class="ui-icon ui-icon-print"></span> Result Preview
                            </button>
                            ';
                        break;

                    }

                    if($tCount > 1) {
                       $buttons .= '<button type = "button" class="ui-button ui-widget ui-corner-all" onClick="javascript: showOptions();">
                            <span class="ui-icon ui-icon-script"></span> Show Multiple Result Options
                        </button>';

                    }


                    $buttons .= '
                        <button type = "button" class="ui-button ui-widget ui-corner-all" onClick="javascript: parent.closeDialog(\'#cbcResult\');">
                            <span class="ui-icon ui-icon-close"></span> Close Window
                        </button>
                    ';

                    echo $buttons;
                ?>
            </td>
        </tr>
    </table>              
</form>

<?php  if($tCount > 1) {
  /* Previous Results */
  $x = $o->getArray("select * from (SELECT DISTINCT wbc,rbc,hemoglobin,hematocrit,neutrophils,lymphocytes,monocytes,eosinophils,basophils,platelate,mcv,mch,mchc,rdwcv,rdwsd,mpv,pdwcv,pdwsd,pct,plcc,plcr FROM lab_cbcresult_temp WHERE serialno = '$order[serialno]') a limit 0,1;");
  $y = $o->getArray("select * from (SELECT DISTINCT wbc,rbc,hemoglobin,hematocrit,neutrophils,lymphocytes,monocytes,eosinophils,basophils,platelate,mcv,mch,mchc,rdwcv,rdwsd,mpv,pdwcv,pdwsd,pct,plcc,plcr FROM lab_cbcresult_temp WHERE serialno = '$order[serialno]') a limit 1,1;");
  $z = $o->getArray("select * from (SELECT DISTINCT wbc,rbc,hemoglobin,hematocrit,neutrophils,lymphocytes,monocytes,eosinophils,basophils,platelate,mcv,mch,mchc,rdwcv,rdwsd,mpv,pdwcv,pdwsd,pct,plcc,plcr FROM lab_cbcresult_temp WHERE serialno = '$order[serialno]') a limit 2,1;");   
?>
<div id="multiResult" style="display: none;">
    <table width=100% id = "itemlist2" class="cell-border" style="font-size:11px;">
        <thead>
            <tr>
                <th>PARAMETER</th>
               
                <?php
                    if(count($x) > 0) {
                        echo '<th>RESULT 1</th>';
                    }
                    if(count($y) > 0) {
                        echo '<th>RESULT 2</th>';
                    }
                    if(count($z) > 0) {
                        echo '<th>RESULT 3</th>';
                    }

                ?>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>WBC</td>
                
                <?php
                    if(count($x) > 0) {
                        echo '<td align=center><input type="radio" name="rad1" id="rad1" value="'. $x['wbc'] . '" onclick="$(\'#wbc\').val(this.value);">&nbsp;' . $x['wbc'] . '</td>';
                    }

                    if(count($y) > 0) {
                        echo '<td align=center><input type="radio" name="rad1" id="rad1" value="'. $y['wbc'] . '" onclick="$(\'#wbc\').val(this.value);">&nbsp;' . $y['wbc'] . '</td>';
                    }

                    if(count($z) > 0) {
                        echo '<td align=center><input type="radio" name="rad1" id="rad1" value="'. $z['wbc'] . '" onclick="$(\'#wbc\').val(this.value);">&nbsp;' . $z['wbc'] . '</td>';
                    }
                ?>
            </tr>
            <tr>
                <td>RBC</td>
                <?php
                    if(count($x) > 0) {
                        echo '<td align=center><input type="radio" name="rad2" id="rad2" value="'. $x['rbc'] . '" onclick="$(\'#rbc\').val(this.value);">&nbsp;' . $x['rbc'] . '</td>';
                    }

                    if(count($y) > 0) {
                        echo '<td align=center><input type="radio" name="rad2" id="rad2" value="'. $y['rbc'] . '" onclick="$(\'#rbc\').val(this.value);">&nbsp;' . $y['rbc'] . '</td>';
                    }

                    if(count($z) > 0) {
                        echo '<td align=center><input type="radio" name="rad2" id="rad2" value="'. $z['rbc'] . '" onclick="$(\'#rbc\').val(this.value);">&nbsp;' . $z['rbc'] . '</td>';
                    }
                ?>
            </tr>
            <tr>
                <td>Hemoglobin</td>
                <?php
                    if(count($x) > 0) {
                        echo '<td align=center><input type="radio" name="rad3" id="rad3" value="'. $x['hemoglobin'] . '" onclick="$(\'#hemoglobin\').val(this.value);">&nbsp;' . $x['hemoglobin'] . '</td>';
                    }

                    if(count($y) > 0) {
                        echo '<td align=center><input type="radio" name="rad3" id="rad3" value="'. $y['hemoglobin'] . '" onclick="$(\'#hemoglobin\').val(this.value);">&nbsp;' . $y['hemoglobin'] . '</td>';
                    }

                    if(count($z) > 0) {
                        echo '<td align=center><input type="radio" name="rad3" id="rad3" value="'. $z['hemoglobin'] . '" onclick="$(\'#hemoglobin\').val(this.value);">&nbsp;' . $z['hemoglobin'] . '</td>';
                    }
                ?>
            </tr>
            <tr>
                <td>Hematocrit</td>
                <?php
                    if(count($x) > 0) {
                        echo '<td align=center><input type="radio" name="rad4" id="rad4" value="'. $x['hematocrit'] . '" onclick="$(\'#hematocrit\').val(this.value);">&nbsp;' . $x['hematocrit'] . '</td>';
                    }

                    if(count($y) > 0) {
                        echo '<td align=center><input type="radio" name="rad4" id="rad4" value="'. $y['hematocrit'] . '" onclick="$(\'#hematocrit\').val(this.value);">&nbsp;' . $y['hematocrit'] . '</td>';
                    }

                    if(count($z) > 0) {
                        echo '<td align=center><input type="radio" name="rad4" id="rad4" value="'. $z['hematocrit'] . '" onclick="$(\'#hematocrit\').val(this.value);">&nbsp;' . $z['hematocrit'] . '</td>';
                    }
                ?>
            </tr>
            <tr>
                <td style="padding-left: 35px;">Neutrophils</td>
                <?php
                    if(count($x) > 0) {
                        echo '<td align=center><input type="radio" name="rad5" id="rad5" value="'. $x['neutrophils'] . '" onclick="$(\'#neutrophils\').val(this.value);">&nbsp;' . $x['neutrophils'] . '</td>';
                    }

                    if(count($y) > 0) {
                        echo '<td align=center><input type="radio" name="rad5" id="rad5" value="'. $y['neutrophils'] . '" onclick="$(\'#neutrophils\').val(this.value);">&nbsp;' . $y['neutrophils'] . '</td>';
                    }

                    if(count($z) > 0) {
                        echo '<td align=center><input type="radio" name="rad5" id="rad5" value="'. $z['neutrophils'] . '" onclick="$(\'#neutrophils\').val(this.value);">&nbsp;' . $z['neutrophils'] . '</td>';
                    }
                ?>
            </tr>
            <tr>
                <td style="padding-left: 35px;">Lymphocytes</td>
                <?php
                    if(count($x) > 0) {
                        echo '<td align=center><input type="radio" name="rad6" id="rad6" value="'. $x['lymphocytes'] . '" onclick="$(\'#lymphocytes\').val(this.value);">&nbsp;' . $x['lymphocytes'] . '</td>';
                    }

                    if(count($y) > 0) {
                        echo '<td align=center><input type="radio" name="rad6" id="rad6" value="'. $y['lymphocytes'] . '" onclick="$(\'#lymphocytes\').val(this.value);">&nbsp;' . $y['lymphocytes'] . '</td>';
                    }

                    if(count($z) > 0) {
                        echo '<td align=center><input type="radio" name="rad6" id="rad6" value="'. $z['lymphocytes'] . '" onclick="$(\'#lymphocytes\').val(this.value);">&nbsp;' . $z['lymphocytes'] . '</td>';
                    }
                ?>
            </tr>
            <tr>
                <td style="padding-left: 35px;">Monocytes</td>
                <?php
                    if(count($x) > 0) {
                        echo '<td align=center><input type="radio" name="rad7" id="rad7" value="'. $x['monocytes'] . '" onclick="$(\'#monocytes\').val(this.value);">&nbsp;' . $x['monocytes'] . '</td>';
                    }

                    if(count($y) > 0) {
                        echo '<td align=center><input type="radio" name="rad7" id="rad7" value="'. $y['monocytes'] . '" onclick="$(\'#monocytes\').val(this.value);">&nbsp;' . $y['monocytes'] . '</td>';
                    }

                    if(count($z) > 0) {
                        echo '<td align=center><input type="radio" name="rad7" id="rad7" value="'. $z['monocytes'] . '" onclick="$(\'#monocytes\').val(this.value);">&nbsp;' . $z['monocytes'] . '</td>';
                    }
                ?>
            </tr>
            <tr>
                <td style="padding-left: 35px;">Eosinophils</td>
                <?php
                    if(count($x) > 0) {
                        echo '<td align=center><input type="radio" name="rad8" id="rad8" value="'. $x['eosinophils'] . '" onclick="$(\'#eosinophils\').val(this.value);">&nbsp;' . $x['eosinophils'] . '</td>';
                    }

                    if(count($y) > 0) {
                        echo '<td align=center><input type="radio" name="rad8" id="rad8" value="'. $y['eosinophils'] . '" onclick="$(\'#eosinophils\').val(this.value);">&nbsp;' . $y['eosinophils'] . '</td>';
                    }

                    if(count($z) > 0) {
                        echo '<td align=center><input type="radio" name="rad8" id="rad8" value="'. $z['eosinophils'] . '" onclick="$(\'#eosinophils\').val(this.value);">&nbsp;' . $z['eosinophils'] . '</td>';
                    }
                ?>
            </tr>
            <tr>
                <td style="padding-left: 35px;">Basophils</td>
                <?php
                    if(count($x) > 0) {
                        echo '<td align=center><input type="radio" name="rad9" id="rad9" value="'. $x['basophils'] . '" onclick="$(\'#basophils\').val(this.value);">&nbsp;' . $x['basophils'] . '</td>';
                    }

                    if(count($y) > 0) {
                        echo '<td align=center><input type="radio" name="rad9" id="rad9" value="'. $y['basophils'] . '" onclick="$(\'#basophils\').val(this.value);">&nbsp;' . $y['basophils'] . '</td>';
                    }

                    if(count($z) > 0) {
                        echo '<td align=center><input type="radio" name="rad9" id="rad9" value="'. $z['basophils'] . '" onclick="$(\'#basophils\').val(this.value);">&nbsp;' . $z['basophils'] . '</td>';
                    }
                ?>
            </tr>
            <tr>
                <td>MCV</td>
                <?php
                    if(count($x) > 0) {
                        echo '<td align=center><input type="radio" name="rad10" id="rad10" value="'. $x['mcv'] . '" onclick="$(\'#mcv\').val(this.value);">&nbsp;' . $x['mcv'] . '</td>';
                    }

                    if(count($y) > 0) {
                        echo '<td align=center><input type="radio" name="rad10" id="rad10" value="'. $y['mcv'] . '" onclick="$(\'#mcv\').val(this.value);">&nbsp;' . $y['mcv'] . '</td>';
                    }

                    if(count($z) > 0) {
                        echo '<td align=center><input type="radio" name="rad10" id="rad10" value="'. $z['mcv'] . '" onclick="$(\'#mcv\').val(this.value);">&nbsp;' . $z['mcv'] . '</td>';
                    }
                ?>
            </tr>
            <tr>
                <td>MCH</td>
                <?php
                    if(count($x) > 0) {
                        echo '<td align=center><input type="radio" name="rad11" id="rad11" value="'. $x['mch'] . '" onclick="$(\'#mch\').val(this.value);">&nbsp;' . $x['mch'] . '</td>';
                    }

                    if(count($y) > 0) {
                        echo '<td align=center><input type="radio" name="rad11" id="rad11" value="'. $y['mch'] . '" onclick="$(\'#mch\').val(this.value);">&nbsp;' . $y['mch'] . '</td>';
                    }

                    if(count($z) > 0) {
                        echo '<td align=center><input type="radio" name="rad11" id="rad11" value="'. $z['mch'] . '" onclick="$(\'#mch\').val(this.value);">&nbsp;' . $z['mch'] . '</td>';
                    }
                ?>
            </tr>
            <tr>
                <td>MCHC</td>
                <?php
                    if(count($x) > 0) {
                        echo '<td align=center><input type="radio" name="rad12" id="rad12" value="'. $x['mchc'] . '" onclick="$(\'#mchc\').val(this.value);">&nbsp;' . $x['mchc'] . '</td>';
                    }

                    if(count($y) > 0) {
                        echo '<td align=center><input type="radio" name="rad12" id="rad12"  value="'. $y['mchc'] . '" onclick="$(\'#mchc\').val(this.value);">&nbsp;' . $y['mchc'] . '</td>';
                    }

                    if(count($z) > 0) {
                        echo '<td align=center><input type="radio" name="rad12" id="rad12"  value="'. $z['mchc'] . '" onclick="$(\'#mchc\').val(this.value);">&nbsp;' . $z['mchc'] . '</td>';
                    }
                ?>
            </tr>
            <tr>
                <td>RDW-CV</td>
                <?php
                    if(count($x) > 0) {
                        echo '<td align=center><input type="radio" name="rad13" id="rad13" value="'. $x['rdwcv'] . '" onclick="$(\'#rdwcv\').val(this.value);">&nbsp;' . $x['rdwcv'] . '</td>';
                    }

                    if(count($y) > 0) {
                        echo '<td align=center><input type="radio" name="rad13" id="rad13"  value="'. $y['rdwcv'] . '" onclick="$(\'#rdwcv\').val(this.value);">&nbsp;' . $y['rdwcv'] . '</td>';
                    }

                    if(count($z) > 0) {
                        echo '<td align=center><input type="radio" name="rad13" id="rad13"  value="'. $z['rdwcv'] . '" onclick="$(\'#rdwcv\').val(this.value);">&nbsp;' . $z['rdwcv'] . '</td>';
                    }
                ?>
            </tr>
            <tr>
                <td>RDW-SD</td>
                <?php
                    if(count($x) > 0) {
                        echo '<td align=center><input type="radio" name="rad14" id="rad14" value="'. $x['rdwsd'] . '" onclick="$(\'#rdwsd\').val(this.value);">&nbsp;' . $x['rdwsd'] . '</td>';
                    }

                    if(count($y) > 0) {
                        echo '<td align=center><input type="radio" name="rad14" id="rad14"  value="'. $y['rdwsd'] . '" onclick="$(\'#rdwsd\').val(this.value);">&nbsp;' . $y['rdwsd'] . '</td>';
                    }

                    if(count($z) > 0) {
                        echo '<td align=center><input type="radio" name="rad14" id="rad14"  value="'. $z['rdwsd'] . '" onclick="$(\'#rdwsd\').val(this.value);">&nbsp;' . $z['rdwsd'] . '</td>';
                    }
                ?>
            </tr>
            <tr>
                <td>Platelet Count</td>
                <?php
                    if(count($x) > 0) {
                        echo '<td align=center><input type="radio" name="rad15" id="rad15" value="'. $x['platelate'] . '" onclick="$(\'#platelate\').val(this.value);">&nbsp;' . $x['platelate'] . '</td>';
                    }

                    if(count($y) > 0) {
                        echo '<td align=center><input type="radio" name="rad15" id="rad15"  value="'. $y['platelate'] . '" onclick="$(\'#platelate\').val(this.value);">&nbsp;' . $y['platelate'] . '</td>';
                    }

                    if(count($z) > 0) {
                        echo '<td align=center><input type="radio" name="rad15" id="rad15"  value="'. $z['platelate'] . '" onclick="$(\'#platelate\').val(this.value);">&nbsp;' . $z['platelate'] . '</td>';
                    }
                ?>
            </tr>
            <tr>
                <td>MPV</td>
                <?php
                    if(count($x) > 0) {
                        echo '<td align=center><input type="radio" name="rad16" id="rad16" value="'. $x['mpv'] . '" onclick="$(\'#mpv\').val(this.value);">&nbsp;' . $x['mpv'] . '</td>';
                    }

                    if(count($y) > 0) {
                        echo '<td align=center><input type="radio" name="rad16" id="rad16"  value="'. $y['mpv'] . '" onclick="$(\'#mpv\').val(this.value);">&nbsp;' . $y['mpv'] . '</td>';
                    }

                    if(count($z) > 0) {
                        echo '<td align=center><input type="radio" name="rad16" id="rad16"  value="'. $z['mpv'] . '" onclick="$(\'#mpv\').val(this.value);">&nbsp;' . $z['mpv'] . '</td>';
                    }
                ?>
            </tr>
            <tr>
                <td>PDW-CV</td>
                <?php
                    if(count($x) > 0) {
                        echo '<td align=center><input type="radio" name="rad17" id="rad17" value="'. $x['pdwcv'] . '" onclick="$(\'#pdwcv\').val(this.value);">&nbsp;' . $x['pdwcv'] . '</td>';
                    }

                    if(count($y) > 0) {
                        echo '<td align=center><input type="radio" name="rad17" id="rad17"  value="'. $y['pdwcv'] . '" onclick="$(\'#pdwcv\').val(this.value);">&nbsp;' . $y['pdwcv'] . '</td>';
                    }

                    if(count($z) > 0) {
                        echo '<td align=center><input type="radio" name="rad17" id="rad17"  value="'. $z['pdwcv'] . '" onclick="$(\'#pdwcv\').val(this.value);">&nbsp;' . $z['pdwcv'] . '</td>';
                    }
                ?>
            </tr>
            <tr>
                <td>PDW-SD</td>
                <?php
                    if(count($x) > 0) {
                        echo '<td align=center><input type="radio" name="rad18" id="rad18" value="'. $x['pdwsd'] . '" onclick="$(\'#pdwsd\').val(this.value);">&nbsp;' . $x['pdwsd'] . '</td>';
                    }

                    if(count($y) > 0) {
                        echo '<td align=center><input type="radio" name="rad18" id="rad18"  value="'. $y['pdwsd'] . '" onclick="$(\'#pdwsd\').val(this.value);">&nbsp;' . $y['pdwsd'] . '</td>';
                    }

                    if(count($z) > 0) {
                        echo '<td align=center><input type="radio" name="rad18" id="rad18"  value="'. $z['pdwsd'] . '" onclick="$(\'#pdwsd\').val(this.value);">&nbsp;' . $z['pdwsd'] . '</td>';
                    }
                ?>
            </tr>
            <tr>
                <td>PCT</td>
                <?php
                    if(count($x) > 0) {
                        echo '<td align=center><input type="radio" name="rad19" id="rad19" value="'. $x['pct'] . '" onclick="$(\'#pct\').val(this.value);">&nbsp;' . $x['pct'] . '</td>';
                    }

                    if(count($y) > 0) {
                        echo '<td align=center><input type="radio" name="rad19" id="rad19"  value="'. $y['pct'] . '" onclick="$(\'#pct\').val(this.value);">&nbsp;' . $y['pct'] . '</td>';
                    }

                    if(count($z) > 0) {
                        echo '<td align=center><input type="radio" name="rad19" id="rad19"  value="'. $z['pct'] . '" onclick="$(\'#pct\').val(this.value);">&nbsp;' . $z['pct'] . '</td>';
                    }
                ?>
            </tr>
            <tr>
                <td>P-LCC</td>
                <?php
                    if(count($x) > 0) {
                        echo '<td align=center><input type="radio" name="rad20" id="rad20" value="'. $x['plcc'] . '" onclick="$(\'#plcc\').val(this.value);">&nbsp;' . $x['plcc'] . '</td>';
                    }

                    if(count($y) > 0) {
                        echo '<td align=center><input type="radio" name="rad20" id="rad20"  value="'. $y['plcc'] . '" onclick="$(\'#plcc\').val(this.value);">&nbsp;' . $y['plcc'] . '</td>';
                    }

                    if(count($z) > 0) {
                        echo '<td align=center><input type="radio" name="rad20" id="rad20"  value="'. $z['plcc'] . '" onclick="$(\'#plcc\').val(this.value);">&nbsp;' . $z['plcc'] . '</td>';
                    }
                ?>
            </tr>
            <tr>
                <td>P-LCR</td>
                <?php
                    if(count($x) > 0) {
                        echo '<td align=center><input type="radio" name="rad21" id="rad21" value="'. $x['plcr'] . '" onclick="$(\'#plcr\').val(this.value);">&nbsp;' . $x['plcr'] . '</td>';
                    }

                    if(count($y) > 0) {
                        echo '<td align=center><input type="radio" name="rad21" id="rad21"  value="'. $y['plcr'] . '" onclick="$(\'#plcr\').val(this.value);">&nbsp;' . $y['plcr'] . '</td>';
                    }

                    if(count($z) > 0) {
                        echo '<td align=center><input type="radio" name="rad21" id="rad21"  value="'. $z['plcr'] . '" onclick="$(\'#plcr\').val(this.value);">&nbsp;' . $z['plcr'] . '</td>';
                    }
                ?>
            </tr>
        </tbody>
    </table>

</div>

 <?php  } ?>

</body>
</html>