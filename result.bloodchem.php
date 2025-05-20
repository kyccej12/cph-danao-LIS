<?php 
	
	session_start();
	require_once "handlers/_generics.php";
	
    $o = new _init;
    $order = $o->getArray("select *, date_format(extractdate,'%m/%d/%Y') as exdate from lab_samples where record_id = '$_REQUEST[lid]';");
    $a = $o->getArray("SELECT docointkey, a.enccode, SUBSTR(enccode,8,15) AS hmrno, DATE_FORMAT(dodate,'%m/%d/%Y %h:%i %p') AS orderdate, DATE_FORMAT(dodate,'%Y-%m-%d') AS xorderdate, a.hpercode, concat(c.patlast,', ', c.patfirst,', ', c.patmiddle) as pname, DATE_FORMAT(c.patbdate,'%m/%d/%Y') AS bday, DATE_FORMAT(c.patbdate,'%Y-%m-%d') AS xbday, IF(c.patsex='F','FEMALE','MALE') AS sex, c.patsex as gender, a.proccode, b.procdesc, a.donotes AS remarks, a.licno, a.estatus, entby FROM hospital_dbo.hdocord a LEFT JOIN hospital_dbo.hprocm b ON a.proccode = b.proccode LEFT JOIN hospital_dbo.hperson c ON a.hpercode = c.hpercode WHERE a.enccode = '$order[enccode]';");
 
    $b = $o->getArray("select * from lab_bloodchem where enccode = '$a[enccode]' and serialno = '$order[serialno]';");
    if(count($b) == 0) {
        $b = $o->getArray("select * from lab_bloodchem_temp where serialno = '$order[serialno]';");
    }

    $o->calculateAge($a['xorderdate'],$a['xbday']);

    list($testCount) = $o->getArray("select count(*) from lab_samples where serialno = '$order[serialno]';");
    if($testCount > 1) {
        $procedure = '';
        $testQuery = $o->dbquery("select `procedure` from lab_samples where serialno = '$order[serialno]';");
        while($testRow = $testQuery->fetch_array()) {
            $procedure .= $testRow[0] . ",";
        }
        $procedure = substr($procedure,0,-1);
    } else { $procedure = $order['procedure']; }

    function checkTest($code,$serialno) {
        global $o;

        list($isTested) = $o->getArray("select count(*) from lab_samples where `primecarecode` = '$code' and serialno = '$serialno';");
        if($isTested > 0 ) { return true; } else { return false; }

    }

    /* Previous Results */
    $c = $o->getArray("select *, concat('<br/>',date_format(result_date,'%m/%d/%Y')) as rdate from lab_bloodchem where SUBSTR(enccode,8,15) = '$a[hmrno]' and result_date < '$a[xorderdate]' limit 1,1;");
    $d = $o->getArray("select *, concat('<br/>',date_format(result_date,'%m/%d/%Y')) as rdate from lab_bloodchem where SUBSTR(enccode,8,15) = '$a[hmrno]' and result_date < '$a[xorderdate]' limit 2,1;");
    $e = $o->getArray("select *, concat('<br/>',date_format(result_date,'%m/%d/%Y')) as rdate from lab_bloodchem where SUBSTR(enccode,8,15) = '$a[hmrno]' and result_date < '$a[xorderdate]' limit 3,1;");



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
            $("#bloodchem_date").datepicker(); 
        
            var myTable = $('#itemlist').DataTable({
                "scrollY":  "540",
                "scrollCollapse": true,
                "select":	'single',
                "searching": false,
                "bSort": false,
                "paging": false,
                "info": false,
              
                "aoColumnDefs": [
                    { "className": "dt-body-center", "targets": [1,2,3,4] },
                ]
            });

            var remarksSelection = [
                "TEST DONE TWICE",
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

        function computeBilirubin() {
            
        
            
            var a = $("#bilirubin").val();
            var b = $("#bilirubin_direct").val();

            if(a != '' && b != '') {
                var c = parseFloat(a) - parseFloat(b);
                $("#bilirubin_indirect").val(c.toFixed(2));
            }

        }

        function computeTotalProtein() {
            var a = $("#albumin").val();
            var b = $("#globulin").val();

            if(a != '' && b != '') {
                var c = parseFloat(a) + parseFloat(b);
                $("#protein").val(c.toFixed(2));

                var agRatio = parseFloat(a) / parseFloat(b);
                $("#agratio").val(agRatio.toFixed(2));
            }
        }

        function computeLDL() {
            var a = $("#triglycerides").val();
            var b = $("#hdl").val();
            var c = $("#cholesterol").val();
            var f = 5;

            if(a != '' && b != '' && c != '') {
                var e = (parseFloat(a) / parseFloat(f)) + parseFloat(b);
                var d = parseFloat(c) - parseFloat(e);
                $("#ldl").val(d.toFixed(2));
            }
        }

        function computeVLDL() {
            var a = $("#triglycerides").val();

            if(a != '') {
                var b = parseFloat(a) / 5;
                $("#vldl").val(b.toFixed(2));
            }
        }

        function changeMachine(val) {
            $.post("src/sjerp.php", { mod: "changeChemMachine", enccode: $("#bloodchem_enccode").val(), serialno: $("#bloodchem_serialno").val(), machine: val, sid: Math.random() }, function() {
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

        .noBorders {
            border: none !important; text-align: center; background-color: inherit !important;
        }

    </style>
</head>
<body>
    <form name="frmBloodChemResult" id="frmBloodChemResult"> 
        <table width=100% cellpadding=0 cellspacing=0 valign=top>
         <tr>
             <td width=35% valign=top>
                <table width=100% cellspacing=0 cellpadding=0><tr><td width=100% class=gridHead align=center>PATIENT & ORDER INFORMATION</td></tr></table>
                <table width=100% cellpadding=0 cellspacing=0 style="border: 1px solid #cdcdcd; padding: 10px; margin-bottom: 5px;">
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%"  class="bareBold" style="padding-left: 15px;">Rerecence #&nbsp;:</td>
                        <td align=left>
                            <input class="gridInput" style="width:100%;" type=text name="bloodchem_enccode" id="bloodchem_enccode" value="<?php echo $a['enccode']; ?>" readonly>
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%"  class="bareBold" style="padding-left: 15px;">Service Order Date&nbsp;:</td>
                        <td align=left>
                            <input class="gridInput" style="width:100%;" type=text name="bloodchem_sodate" id="bloodchem_sodate" value="<?php echo $a['orderdate']; ?>" readonly>
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">HMR No.&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="bloodchem_pid" id="bloodchem_pid" value="<?php echo $a['hmrno']; ?>" readonly>
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%"  class="bareBold" style="padding-left: 15px;">Result Date&nbsp;:</td>
                        <td align=left>
                            <input class="gridInput" style="width:100%;" type=text name="bloodchem_date" id="bloodchem_date" value="<?php if($rdate !='') { echo $rdate; } else { echo date('m/d/Y'); } ?>">
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Patient Name&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="bloodchem_pname" id="bloodchem_pname" value="<?php echo $a['pname']; ?>" readonly>
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>

                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Gender&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="bloodchem_gender" id="bloodchem_gender" value="<?php echo $a['sex']; ?>" readonly>
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Birthdate&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="bloodchem_birthdate" id="bloodchem_birthdate" value="<?php echo $a['bday']; ?>" readonly>
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Age&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="bloodchem_age" id="bloodchem_age" value="<?php echo $o->ageDisplay; ?>" readonly>
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Requesting Physician&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="bloodchem_physician" id="bloodchem_physician" value="<?php echo $order['physician']; ?>" readonly>
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                </table>
                <table width=100% cellspacing=0 cellpadding=0><tr><td width=100% class=gridHead align=center>SAMPLE DETAILS</td></tr></table>
                <table width=100% cellpadding=0 cellspacing=0 style="border: 1px solid #cdcdcd; padding: 10px;">
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Test or Procedure&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="bloodchem_procedure" id="bloodchem_procedure" value="<?php echo $procedure ?>" readonly>
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Specimen Type&nbsp;:</td>
                        <td align=left>
                            <select class="gridInput" style="width:100%;" name="bloodchem_spectype" id="bloodchem_spectype">
                                <?php
                                    $iun = $o->dbquery("select id,sample_type from options_sampletype;");
                                    while(list($aa,$ab) = $iun->fetch_array()) {
                                        echo "<option value='$aa'";
                                        if($aa == $a['sampletype']) { echo "selected"; }
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
                            <select class="gridInput" style="width:100%;" name="bloodchem_machine" id="bloodchem_machine" onchange="javascript: changeMachine(this.value);">
                                <option value ="MINDRAY" <?php if ($order['machine'] == 'MINDRAY') { echo "selected"; } ?>>MINDRAY</option>
                                <option value ="FUJI" <?php if ($order['machine'] == 'FUJI') { echo "selected"; } ?>>FUJI NX-700</option>
                                <!-- <option value ="BIOBASE" <?php if ($b['machine'] == 'BIOBASE') { echo "selected"; } ?>>BIOBASE BK Series</option> -->
                            </select>
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Sample Serial No.&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="bloodchem_serialno" id="bloodchem_serialno" value="<?php echo $order['serialno']; ?>" readonly>
                            <input type="hidden" name="bloodchem_proccode" id="bloodchem_proccode" value="<?php echo $order['code']; ?>">
                            <input type="hidden" name="bloodchem_dotime" id="bloodchem_dotime" value="<?php echo $order['dotime']; ?>">
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Date Extracted&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="bloodchem_extractdate" id="bloodchem_extractdate" value="<?php echo $order['exdate']; ?>" readonly>
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Time Extracted&nbsp;:</td>
                        <td align=left>
                
                            <input type="text" class="gridInput" style="width:100%;" name="bloodchem_extracttime" id="bloodchem_extracttime" value="<?php echo $order['extractime']; ?>" readonly>

                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Extracted By&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="bloodchem_extractby" id="bloodchem_extractby" value="<?php echo $order['extractby'];  ?>" readonly>
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Collection Site&nbsp;:</td>
                        <td align=left>
                             <input type="text" class="gridInput" style="width:100%;" name="bloodchem_location" id="bloodchem_location" value="<?php echo $order['location'];  ?>" readonly>
                        </td>				
                    </tr>
                </table>   
            </td>
            <td width=1%>&nbsp;</td>
            <td width=64% valign=top>
                <table width=100% id = "itemlist" class="cell-border" style="font-size:11px;">
                    <thead>
                        <tr>
                            <th>PARAMETER</th>
                            <th>CURRENT RESULT</th>
                            <th>FLAG</th>
                            <th>PREVIOUS<?php echo $c['rdate']; ?></th>
                            <th>PREVIOUS<?php echo $d['rdate']; ?></th>
                            <th>PREVIOUS<?php echo $e['rdate']; ?></th>
                            <th>REFERENCE VALUES</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if(checkTest('L113',$order['serialno'])) { ?>
                    <tr>
                        <td>Glucose/FBS</td>
                        <td>
                            <input type="text" class="noBorders" name="glucose" id="glucose" value="<?php echo number_format($b['glucose'],2); ?>" pattern="^\d*(\.\d{0,2})?$" >
                        </td>
                        <td><?php echo $o->checkChemValues2($o->age,$a['xgender'],'L113',$b['glucose'],$order['machine']); ?></td>
                        <td><?php echo $c['glucose']; ?></td>
                        <td><?php echo $d['glucose']; ?></td>
                        <td><?php echo $e['glucose']; ?></td>
                        <td><?php echo $o->getAttribute2('L113',$a['age'],$a['xgender'],$order['machine']); ?></td>	
                    </tr>
                    <?php } if(checkTest('L132',$order['serialno']) || checkTest('L110',$order['serialno'])) { ?>
                    <tr>
                        <td>Glucose Random (RBS)</td>
                        <td>
                            <input type="text" class="noBorders" name="rbs" id="rbs" value="<?php echo number_format($b['rbs'],2); ?>" pattern="^\d*(\.\d{0,2})?$">
                        </td>
                        <td><?php echo $o->checkChemValues2($o->age,$a['xgender'],'L132',$b['rbs'],$order['machine']); ?></td>
                        <td><?php echo $c['rbs']; ?></td>
                        <td><?php echo $d['rbs']; ?></td>
                        <td><?php echo $e['rbs']; ?></td>
                        <td><?php echo $o->getAttribute2('L132',$o->age,$a['xgender'],$order['machine']); ?></td>	
                    </tr>
                    <?php } if(checkTest('L004',$order['serialno'])) { ?>
                    <tr>
                        <td>Blood Uric Acid</td>
                        <td>
                            <input type="text" class="noBorders" name="uric" id="uric" value="<?php echo number_format($b['uric'],2); ?>" pattern="^\d*(\.\d{0,2})?$">
                        </td>
                        <td><?php echo $o->checkChemValues2($o->age,$a['xgender'],'L004',$b['uric'],$order['machine']); ?></td>
                        <td><?php echo $c['uric']; ?></td>
                        <td><?php echo $d['uric']; ?></td>
                        <td><?php echo $e['uric']; ?></td>
                        <td><?php echo $o->getAttribute2('L004',$o->age,$a['xgender'],$order['machine']); ?></td>	
                    </tr>
                    <?php } if(checkTest('L005',$order['serialno'])) { ?>   
                    <tr>
                        <td>Blood Urea Nitrogen (BUN)</td>
                        <td>
                            <input type="text" class="noBorders" name="bun" id="bun" value="<?php echo $b['bun']; ?>">
                        </td>
                        <td><?php echo $o->checkChemValues2($o->age,$a['xgender'],'L005',trim($b['bun'],'<'),$order['machine']); ?></td>
                        <td><?php echo $c['bun']; ?></td>
                        <td><?php echo $d['bun']; ?></td>
                        <td><?php echo $e['bun']; ?></td>
                        <td><?php echo $o->getAttribute2('L005',$o->age,$a['xgender'],$order['machine']); ?></td>	
                    </tr>
                    <?php } if(checkTest('L020',$order['serialno'])) { ?>        
                    <tr>
                        <td>Creatinine</td>
                        <td>
                            <input type="text" class="noBorders" name="creatinine" id="creatinine" value="<?php echo number_format($b['creatinine'],2); ?>" pattern="^\d*(\.\d{0,2})?$">
                        </td>
                        <td><?php echo $o->checkChemValues2($o->age,$a['xgender'],'L020',$b['creatinine'],$order['machine']); ?></td>
                        <td><?php echo $c['creatinine']; ?></td>
                        <td><?php echo $d['creatinine']; ?></td>
                        <td><?php echo $e['creatinine']; ?></td>
                        <td><?php echo $o->getAttribute2('L020',$o->age,$a['xgender'],$order['machine']); ?></td>	
                    </tr>
                    <?php } if(checkTest('L023',$order['serialno'])) { ?>        
                    <tr>
                        <td>Lactate Dehydrogenase (LDH)</td>
                        <td>
                            <input type="text" class="noBorders" name="ldh" id="ldh" value="<?php echo number_format($b['ldh'],2); ?>" pattern="^\d*(\.\d{0,2})?$">
                        </td>
                        <td><?php echo $o->checkChemValues2($o->age,$a['xgender'],'L023',$b['ldh'],$order['machine']); ?></td>
                        <td><?php echo $c['ldh']; ?></td>
                        <td><?php echo $d['ldh']; ?></td>
                        <td><?php echo $e['ldh']; ?></td>
                        <td><?php echo $o->getAttribute2('L023',$o->age,$a['xgender'],$order['machine']); ?></td>	
                    </tr>
                    <?php } if(checkTest('L071',$order['serialno']) || checkTest('L139',$order['serialno'])) { ?>
                    <tr>
                        <td>Total Cholesterol</td>
                        <td>
                            <input type="text" class="noBorders" name="cholesterol" id="cholesterol" value="<?php echo number_format($b['cholesterol'],2); ?>" pattern="^\d*(\.\d{0,2})?$" onchange="javascript: computeLDL();">
                        </td>
                        <td><?php echo $o->checkChemValues2($o->age,$a['xgender'],'L019',$b['cholesterol'],$order['machine']); ?></td>
                        <td><?php echo $c['cholesterol']; ?></td>
                        <td><?php echo $d['cholesterol']; ?></td>
                        <td><?php echo $e['cholesterol']; ?></td>
                        <td><?php echo $o->getAttribute2('L019',$o->age,$a['xgender'],$order['machine']); ?></td>	
                    </tr>
                    <tr>
                        <td>HDL - Chol&nbsp;:</td>
                        <td>
                            <input type="text" class="noBorders" name="hdl" id="hdl" value="<?php echo number_format($b['hdl'],2); ?>" pattern="^\d*(\.\d{0,2})?$" onchange="javascript: computeLDL();">
                        </td>
                        <td><?php echo $o->checkChemValues2($o->age,$a['xgender'],'L134',$b['hdl'],$order['machine']); ?></td>
                        <td><?php echo $c['hdl']; ?></td>
                        <td><?php echo $d['hdl']; ?></td>
                        <td><?php echo $e['hdl']; ?></td>
                        <td><?php echo $o->getAttribute2('L134',$o->age,$a['xgender'],$order['machine']); ?></td>	
                    </tr>
                    <tr>
                        <td>Triglycerides</td>
                        <td>
                            <input type="text" class="noBorders" name="triglycerides" id="triglycerides" value="<?php echo number_format($b['triglycerides'],2); ?>" pattern="^\d*(\.\d{0,2})?$" onchange="javascript: computeVLDL(); computeLDL();">
                        </td>
                        <td><?php echo $o->checkChemValues2($o->age,$a['xgender'],'L032',$b['triglycerides'],$order['machine']); ?></td>
                        <td><?php echo $c['triglycerides']; ?></td>
                        <td><?php echo $d['triglycerides']; ?></td>
                        <td><?php echo $e['triglycerides']; ?></td>
                        <td><?php echo $o->getAttribute2('L032',$o->age,$a['xgender'],$order['machine']); ?></td>	
                    </tr>                   
                    <tr>
                        <td>LDL - Chol</td>
                        <td >
                            <input type="text" class="noBorders" name="ldl" id="ldl" value="<?php echo number_format($b['ldl'],2); ?>" pattern="^\d*(\.\d{0,2})?$"  />
                        </td>
                        <td><?php echo $o->checkChemValues2($o->age,$a['xgender'],'L018',$b['ldl'],$order['machine']); ?></td>
                        <td><?php echo $c['ldl']; ?></td>
                        <td><?php echo $d['ldl']; ?></td>
                        <td><?php echo $e['ldl']; ?></td>
                        <td><?php echo $o->getAttribute2('L018',$o->age,$a['xgender'],$order['machine']); ?></td>
                    </tr>   
                    <tr>
                        <td>VLDL</td>
                        <td>
                            <input type="text" class="noBorders" name="vldl" id="vldl" value="<?php echo number_format($b['vldl'],2); ?>" pattern="^\d*(\.\d{0,2})?$"  />
                        </td>
                        <td><?php echo $o->checkChemValues2($o->age,$a['xgender'],'VLDL',$b['vldl'],$order['machine']); ?></td>
                        <td><?php echo $c['vldl']; ?></td>
                        <td><?php echo $d['vldl']; ?></td>
                        <td><?php echo $e['vldl']; ?></td>
                        <td><?php echo $o->getAttribute2('VLDL',$o->age,$a['xgender'],$order['machine']); ?></td>	
                    </tr>     
                    <?php } if(checkTest('L028',$order['serialno'])) { ?>
                    <tr>
                        <td>SGOT/AST</td>
                        <td>
                            <input type="text" class="noBorders" name="sgot" id="sgot" value="<?php echo number_format($b['sgot'],2); ?>" pattern="^\d*(\.\d{0,2})?$">
                        </td>
                        <td><?php echo $o->checkChemValues2($o->age,$a['xgender'],'L028',$b['sgot'],$order['machine']); ?></td>
                        <td><?php echo $c['sgot']; ?></td>
                        <td><?php echo $d['sgot']; ?></td>
                        <td><?php echo $e['sgot']; ?></td>
                        <td><?php echo $o->getAttribute2('L028',$o->age,$a['xgender'],$order['machine']); ?></td>	
                    </tr>
                    <?php } if(checkTest('L029',$order['serialno'])) { ?>
                    <tr>
                        <td>SGPT/ALT</td>
                        <td>
                            <input type="text" class="noBorders" name="sgpt" id="sgpt" value="<?php echo number_format($b['sgpt'],2); ?>" pattern="^\d*(\.\d{0,2})?$">
                        </td>
                        <td><?php echo $o->checkChemValues2($o->age,$a['xgender'],'L029',$b['sgpt'],$order['machine']); ?></td>
                        <td><?php echo $c['sgpt']; ?></td>
                        <td><?php echo $d['sgpt']; ?></td>
                        <td><?php echo $e['sgpt']; ?></td>
                        <td><?php echo $o->getAttribute2('L029',$o->age,$a['xgender'],$order['machine']); ?></td>	
                    </tr>
                    <?php } if(checkTest('L016',$order['serialno'])) { ?>
                    <tr>
                        <td>Alkaline Phosphatase&nbsp;:</td>
                        <td>
                            <input type="text" class="noBorders" name="alkaline" id="alkaline" value="<?php echo number_format($b['alkaline'],2); ?>" pattern="^\d*(\.\d{0,2})?$">
                        </td>
                        <td><?php echo $o->checkChemValues2($o->age,$a['xgender'],'L016',$b['sgpt'],$order['machine']); ?></td>
                        <td><?php echo $c['alkaline']; ?></td>
                        <td><?php echo $d['alkaline']; ?></td>
                        <td><?php echo $e['alkaline']; ?></td>
                        <td><?php echo $o->getAttribute2('L016',$o->age,$a['xgender'],$order['machine']); ?></td>	
                    </tr>
                    <?php } if(checkTest('L003',$order['serialno']) || checkTest('L149',$order['serialno']) ) { ?>    
                    <tr>
                        <td>Total Bilirubin</td>
                        <td>
                            <input type="text" class="noBorders" name="bilirubin" id="bilirubin" value="<?php echo number_format($b['bilirubin'],2); ?>" onchange="javascript: computeBilirubin();">
                        </td>
                        <td><?php echo $o->checkChemValues2($o->age,$a['xgender'],'L109',$b['bilirubin'],$order['machine']); ?></td>
                        <td><?php echo $c['bilirubin']; ?></td>
                        <td><?php echo $d['bilirubin']; ?></td>
                        <td><?php echo $e['bilirubin']; ?></td>
                        <td><?php echo $o->getAttribute2('L109',$o->age,$a['xgender'],$order['machine']); ?></td>	
                    </tr>           
                    <tr>
                        <td>Direct Bilirubin&nbsp;:</td>
                        <td>
                            <input type="text" class="noBorders" name="bilirubin_direct" id="bilirubin_direct" value="<?php echo number_format($b['bilirubin_direct'],2); ?>" pattern="^\d*(\.\d{0,2})?$" onchange = "javascript: computeBilirubin();">
                        </td>
                        <td><?php echo $o->checkChemValues2($o->age,$a['xgender'],'L003',$b['bilirubin_direct'],$order['machine']); ?></td>
                        <td><?php echo $c['bilirubin_direct']; ?></td>
                        <td><?php echo $d['bilirubin_direct']; ?></td>
                        <td><?php echo $e['bilirubin_direct']; ?></td>
                        <td><?php echo $o->getAttribute2('L003',$o->age,$a['xgender'],$order['machine']); ?></td>	
                    </tr>
                    <tr>
                        <td>Indirect Bilirubin&nbsp;:</td>
                        <td>
                            <input type="text" class="noBorders" name="bilirubin_indirect" id="bilirubin_indirect" value="<?php echo number_format($b['bilirubin_indirect'],2); ?>" pattern="^\d*(\.\d{0,2})?$" readonly>
                        </td>
                        <td><?php echo $o->checkChemValues2($o->age,$a['xgender'],'L149',$b['bilirubin_indirect'],$order['machine']); ?></td>
                        <td><?php echo $c['bilirubin_indirect']; ?></td>
                        <td><?php echo $d['bilirubin_indirect']; ?></td>
                        <td><?php echo $e['bilirubin_indirect']; ?></td>
                        <td><?php echo $o->getAttribute2('L149',$o->age,$a['xgender'],$order['machine']); ?></td>	
                    </tr>
                    
                    <?php } if(checkTest('L027',$order['serialno'])) { ?>
                    <tr>
                        <td>Total Protein</td>
                        <td>
                            <input type="text" class="noBorders" name="protein" id="protein" value="<?php echo number_format($b['protein'],2); ?>">
                        </td>
                        <td><?php echo $o->checkChemValues2($o->age,$a['xgender'],'L027',$b['protein'],$order['machine']); ?></td>
                        <td><?php echo $c['protein']; ?></td>
                        <td><?php echo $d['protein']; ?></td>
                        <td><?php echo $e['protein']; ?></td>
                        <td><?php echo $o->getAttribute2('L027',$o->age,$a['xgender'],$order['machine']); ?></td>	
                    </tr>
                    <?php } if(checkTest('L001',$order['serialno'])) { ?>                
                    <tr>
                        <td>Albumin</td>
                        <td>
                            <input type="text" class="noBorders" name="albumin" id="albumin" value="<?php echo number_format($b['albumin'],2); ?>" pattern="^\d*(\.\d{0,2})?$">
                        </td>
                        <td><?php echo $o->checkChemValues2($o->age,$a['xgender'],'L001',$b['albumin'],$order['machine']); ?></td>
                        <td><?php echo $c['albumin']; ?></td>
                        <td><?php echo $d['albumin']; ?></td>
                        <td><?php echo $e['albumin']; ?></td>
                        <td><?php echo $o->getAttribute2('L001',$o->age,$a['xgender'],$order['machine']); ?></td>	
                    </tr>
                    <?php } if(checkTest('L001',$order['serialno']) && checkTest('L027',$order['serialno'])) { ?>
                    <tr>
                        <td>A/G Ratio</td>
                        <td>
                            <input type="text" class="noBorders" name="agratio" id="agratio" value="<?php echo number_format($b['agratio'],2); ?>">
                        </td>
                        <td>&nbsp;</td>
                        <td><?php echo $c['agratio']; ?></td>
                        <td><?php echo $d['agratio']; ?></td>
                        <td><?php echo $e['agratio']; ?></td>
                        <td><?php echo $o->getAttribute2('AGRATIO',$o->age,$a['xgender'],$order['machine']); ?></td>	
                    </tr>
                    <?php } if(checkTest('L080',$order['serialno'])) { ?>        
                    <tr>
                        <td colspan=7><b>Electrolytes&nbsp;:</b></td>
                        <td style="display: none;"></td>
                        <td style="display: none;"></td>
                        <td style="display: none;"></td>
                        <td style="display: none;"></td>
                        <td style="display: none;"></td>
                        <td style="display: none;"></td>
                    </tr>                
                    <tr>
                        <td style="padding-left: 35px;">Sodium (Na)</td>
                        <td>
                            <input type="text" class="noBorders" name="electrolytes_na" id="electrolytes_na" pattern="^\d*(\.\d{0,2})?$" value="<?php echo number_format($b['electrolytes_na'],2); ?>">
                        </td>
                        <td><?php echo $o->checkChemValues2($o->age,$a['xgender'],'L030',$b['electrolytes_na'],$order['machine']); ?></td>
                        <td><?php echo $c['electrolytes_na']; ?></td>
                        <td><?php echo $d['electrolytes_na']; ?></td>
                        <td><?php echo $e['electrolytes_na']; ?></td>
                        <td><?php echo $o->getAttribute2('L030',$o->age,$a['xgender'],$order['machine']); ?></td>
                    </tr>
                    <tr>
                        <td style="padding-left: 35px;">Potassium (K)</td>
                        <td>
                            <input type="text" class="noBorders" name="electrolytes_k" id="electrolytes_k" pattern="^\d*(\.\d{0,2})?$" value="<?php echo number_format($b['electrolytes_k'],2); ?>">
                        </td>
                        <td><?php echo $o->checkChemValues2($o->age,$a['xgender'],'L026',$b['electrolytes_k'],$order['machine']); ?></td>
                        <td><?php echo $c['electrolytes_k']; ?></td>
                        <td><?php echo $d['electrolytes_k']; ?></td>
                        <td><?php echo $e['electrolytes_k']; ?></td>
                        <td><?php echo $o->getAttribute2('L026',$o->age,$a['xgender'],$order['machine']); ?></td>
                    </tr>    
                    <tr>
                        <td style="padding-left: 35px;">Chloride (CI)</td>
                        <td>
                            <input type="text" class="noBorders" name="electrolytes_ci" id="electrolytes_ci" pattern="^\d*(\.\d{0,2})?$" value="<?php echo number_format($b['electrolytes_ci'],2); ?>">
                        </td>
                        <td><?php echo $o->checkChemValues2($o->age,$a['xgender'],'L017',$b['electrolytes_ci'],$order['machine']); ?></td>
                        <td><?php echo $c['electrolytes_ci']; ?></td>
                        <td><?php echo $d['electrolytes_ci']; ?></td>
                        <td><?php echo $e['electrolytes_ci']; ?></td>
                        <td><?php echo $o->getAttribute2('L017',$o->age,$a['xgender'],$order['machine']); ?></td>
                    </tr>
                    <tr>
                        <td style="padding-left: 35px;">Ionized Calcium</td>
                        <td>
                            <input type="text" class="noBorders" name="ion_calcium" id="ion_calcium" pattern="^\d*(\.\d{0,2})?$" value="<?php echo number_format($b['ion_calcium'],2); ?>">
                        </td>
                        <td><?php echo $o->checkChemValues2($o->age,$a['xgender'],'L130',$b['ion_calcium'],$order['machine']); ?></td>
                        <td><?php echo $c['ion_calcium']; ?></td>
                        <td><?php echo $d['ion_calcium']; ?></td>
                        <td><?php echo $e['ion_calcium']; ?></td>
                        <td><?php echo $o->getAttribute2('L130',$o->age,$a['xgender'],$order['machine']); ?></td>	
                    </tr>
                    <?php } if(checkTest('L006',$order['serialno'])) { ?>
                    <tr>
                        <td style="padding-left: 35px;">Total Calcium</td>
                        <td>
                            <input type="text" class="noBorders" name="total_calcium" id="total_calcium" pattern="^\d*(\.\d{0,2})?$" value="<?php echo number_format($b['total_calcium'],2); ?>">
                        </td>
                        <td><?php echo $o->checkChemValues2($o->age,$a['xgender'],'L006',$b['total_calcium'],$order['machine']); ?></td>
                        <td><?php echo $c['calcium']; ?></td>
                        <td><?php echo $d['calcium']; ?></td>
                        <td><?php echo $e['calcium']; ?></td>
                        <td><?php echo $o->getAttribute2('L006',$o->age,$a['xgender'],$order['machine']); ?></td>	
                    </tr>
                    <?php } if(checkTest('L030',$order['serialno'])) { ?>
                    <tr>
                        <td>Sodium (Na)</td>
                        <td>
                            <input type="text" class="noBorders" name="electrolytes_na" id="electrolytes_na" pattern="^\d*(\.\d{0,2})?$" value="<?php echo number_format($b['electrolytes_na'],2); ?>">
                        </td>
                        <td><?php echo $o->checkChemValues2($o->age,$a['xgender'],'L030',$b['electrolytes_na'],$order['machine']); ?></td>
                        <td><?php echo $c['electrolytes_na']; ?></td>
                        <td><?php echo $d['electrolytes_na']; ?></td>
                        <td><?php echo $e['electrolytes_na']; ?></td>
                        <td><?php echo $o->getAttribute2('L030',$o->age,$a['xgender'],$order['machine']); ?></td>
                    </tr>
                    <?php } if(checkTest('L026',$order['serialno'])) { ?>
                    <tr>
                        <td>Potassium (K)</td>
                        <td>
                            <input type="text" class="noBorders" name="electrolytes_k" id="electrolytes_k" pattern="^\d*(\.\d{0,2})?$" value="<?php echo number_format($b['electrolytes_k'],2); ?>">
                        </td>
                        <td><?php echo $o->checkChemValues2($o->age,$a['xgender'],'L026',$b['electrolytes_k'],$order['machine']); ?></td>
                        <td><?php echo $c['electrolytes_k']; ?></td>
                        <td><?php echo $d['electrolytes_k']; ?></td>
                        <td><?php echo $e['electrolytes_k']; ?></td>
                        <td><?php echo $o->getAttribute2('L026',$o->age,$a['xgender'],$order['machine']); ?></td>
                    </tr>    
                    <?php } if(checkTest('LO17',$order['serialno'])) { ?>    
                    <tr>
                        <td>Chemical Ionization (CI)</td>
                        <td >
                            <input type="text" class="noBorders" name="electrolytes_ci" id="electrolytes_ci" pattern="^\d*(\.\d{0,2})?$" value="<?php echo number_format($b['electrolytes_ci'],2); ?>">
                        </td>
                        <td><?php echo $o->checkChemValues2($o->age,$a['xgender'],'L017',$b['electrolytes_ci'],$order['machine']); ?></td>
                        <td><?php echo $c['electrolytes_ci']; ?></td>
                        <td><?php echo $d['electrolytes_ci']; ?></td>
                        <td><?php echo $e['electrolytes_ci']; ?></td>
                        <td><?php echo $o->getAttribute2('L017',$o->age,$a['xgender'],$order['machine']); ?></td>
                    </tr>
                    <?php } if(checkTest('L130',$order['serialno'])) {  ?>
                    <tr>
                        <td>Ionized Calcium</td>
                        <td>
                            <input type="text" class="noBorders" name="ion_calcium" id="ion_calcium" pattern="^\d*(\.\d{0,2})?$" value="<?php echo number_format($b['ion_calcium'],2); ?>">
                        </td>
                        <td><?php echo $o->checkChemValues2($o->age,$a['xgender'],'L130',$b['ion_calcium'],$order['machine']); ?></td>
                        <td><?php echo $c['ion_calcium']; ?></td>
                        <td><?php echo $d['ion_calcium']; ?></td>
                        <td><?php echo $e['ion_calcium']; ?></td>
                        <td><?php echo $o->getAttribute2('L130',$o->age,$a['xgender'],$order['machine']); ?></td>	
                    </tr>
                    <?php } if(checkTest('L025',$order['serialno'])) { ?>
                    <tr>
                        <td>Phosphorus</td>
                        <td>
                            <input type="text" class="noBorders" name="phosphorus" id="phosphorus" pattern="^\d*(\.\d{0,2})?$" value="<?php echo number_format($b['phosphorus'],2); ?>">
                        </td>
                        <td><?php echo $o->checkChemValues($o->age,$a['xgender'],'L025',$b['phosphorus'],$order['machine']); ?></td>
                        <td><?php echo $c['phosphorus']; ?></td>
                        <td><?php echo $d['phosphorus']; ?></td>
                        <td><?php echo $e['phosphorus']; ?></td>
                        <td><?php echo $o->getAttribute('L025',$o->age,$a['xgender'],$order['machine']); ?></td>	
                    </tr>
                    <?php } if(checkTest('L021',$order['serialno'])) { ?>
                    <tr>
                        <td>CBG</td>
                        <td>
                            <input type="text" class="noBorders" name="cbg" id="cbg" pattern="^\d*(\.\d{0,2})?$" value="<?php echo number_format($b['cbg'],2); ?>">
                        </td>
                        <td><?php echo $o->checkChemValues2($o->age,$a['xgender'],'L021',$b['cbg'],$order['machine']); ?></td>
                        <td><?php echo $c['cbg']; ?></td>
                        <td><?php echo $d['cbg']; ?></td>
                        <td><?php echo $e['cbg']; ?></td>
                        <td><?php echo $o->getAttribute2('L021',$o->age,$a['xgender'],$order['machine']); ?></td>	
                    </tr>
                    <?php } if(checkTest('L081',$order['serialno'])) { ?>
                    <tr>
                        <td>GGT</td>
                        <td>
                            <input type="text" class="noBorders" name="ggt" id="ggt" pattern="^\d*(\.\d{0,2})?$" value="<?php echo number_format($b['ggt'],2); ?>">
                        </td>
                        <td><?php echo $o->checkChemValues2($o->age,$a['xgender'],'L081',$b['ggt'],$order['machine']); ?></td>
                        <td><?php echo $c['ggt']; ?></td>
                        <td><?php echo $d['ggt']; ?></td>
                        <td><?php echo $e['ggt']; ?></td>
                        <td><?php echo $o->getAttribute2('L081',$o->age,$a['xgender'],$order['machine']); ?></td>	
                    </tr>
                    <?php } if(checkTest('L131',$order['serialno'])) { 
                        if($b['troponin'] > 0.04) { $tflag = '<font color=red><b>H</b></font>'; }    
                        
                    ?>
                    <tr>
                        <td>Troponin I</td>
                        <td>
                            <input type="text" class="noBorders" name="troponin" id="troponin" pattern="^\d*(\.\d{0,2})?$" value="<?php echo number_format($b['troponin'],2); ?>">
                        </td>
                        <td><?php echo $tflag; ?></td>
                        <td><?php echo $c['troponin']; ?></td>
                        <td><?php echo $d['troponin']; ?></td>
                        <td><?php echo $e['troponin']; ?></td>
                        <td>> 0.04 ng/mL</td>	
                    </tr>
                    <?php } if(checkTest('L133',$order['serialno'])) { ?>
                    <tr>
                        <td>Amylase&nbsp;:</td>
                        <td>
                            <input type="text" class="noBorders" name="amylase" id="amylase" pattern="^\d*(\.\d{0,2})?$" value="<?php echo number_format($b['amylase'],2); ?>">
                        </td>
                        <td><?php echo $o->checkChemValues2($o->age,$a['xgender'],'L133',$b['amylase'],$order['machine']); ?></td>
                        <td><?php echo $c['amylase']; ?></td>
                        <td><?php echo $d['amylase']; ?></td>
                        <td><?php echo $e['amylase']; ?></td>
                        <td><?php echo $o->getAttribute2('L133',$o->age,$a['xgender'],$order['machine']); ?></td>	
                    </tr>
                    <?php } if(checkTest('L135',$order['serialno'])) { ?>
                    <tr>
                        <td>Lipase&nbsp;:</td>
                        <td>
                            <input type="text" class="noBorders" name="lipase" id="lipase" pattern="^\d*(\.\d{0,2})?$" value="<?php echo number_format($b['lipase'],2); ?>">
                        </td>
                        <td><?php echo $o->checkChemValues2($o->age,$a['xgender'],'L135',$b['lipase'],$order['machine']); ?></td>
                        <td><?php echo $c['lipase']; ?></td>
                        <td><?php echo $d['lipase']; ?></td>
                        <td><?php echo $e['lipase']; ?></td>
                        <td><?php echo $o->getAttribute2('L135',$o->age,$a['xgender'],$order['machine']); ?></td>	
                    </tr>
                    <?php } if(checkTest('L022',$order['serialno'])) { ?>
                    <tr>
                        <td>HbA1c&nbsp;:</td>
                        <td>
                            <input type="text" class="noBorders" name="hba1c" id="hba1c" pattern="^\d*(\.\d{0,2})?$" value="<?php echo number_format($b['hba1c'],2); ?>">
                        </td>
                        <td><?php echo $o->checkChemValues2($o->age,$a['xgender'],'L022',$b['hba1c'],$order['machine']); ?></td>
                        <td><?php echo $c['hba1c']; ?></td>
                        <td><?php echo $d['hba1c']; ?></td>
                        <td><?php echo $e['hba1c']; ?></td>
                        <td><?php echo $o->getAttribute2('L022',$o->age,$a['xgender'],$order['machine']); ?></td>	
                    </tr>
                    <?php } if(checkTest('L147',$order['serialno'])) { ?>
                    <tr>
                        <td>Procalcitonin&nbsp;:</td>
                        <td>
                            <input type="text" class="noBorders" name="procalcitonin" id="procalcitonin" pattern="^\d*(\.\d{0,2})?$" value="<?php echo number_format($b['procalcitonin'],2); ?>">
                        </td>
                        <td><?php echo $o->checkChemValues2($o->age,$a['xgender'],'L147',$b['procalcitonin'],$order['machine']); ?></td>
                        <td><?php echo $c['procalcitonin']; ?></td>
                        <td><?php echo $d['procalcitonin']; ?></td>
                        <td><?php echo $e['procalcitonin']; ?></td>
                        <td><?php echo $o->getAttribute2('L147',$o->age,$a['xgender'],$order['machine']); ?></td>	
                    </tr>
                    <?php } if(checkTest('L158',$order['serialno'])) { ?>
                    <tr>
                        <td>Magnesium&nbsp;:</td>
                        <td>
                            <input type="text" class="noBorders" name="magnesium" id="magnesium" pattern="^\d*(\.\d{0,2})?$" value="<?php echo number_format($b['magnesium'],2); ?>">
                        </td>
                        <td><?php echo $o->checkChemValues2($o->age,$a['xgender'],'L158',$b['magnesium'],$order['machine']); ?></td>
                        <td><?php echo $c['magnesium']; ?></td>
                        <td><?php echo $d['magnesium']; ?></td>
                        <td><?php echo $e['magnesium']; ?></td>
                        <td><?php echo $o->getAttribute2('L158',$o->age,$a['xgender'],$order['machine']); ?></td>	
                    </tr>
                    <?php } if(checkTest('L159',$order['serialno'])) { ?>
                    <tr>
                        <td>Inorganic Phospharous&nbsp;:</td>
                        <td>
                            <input type="text" class="noBorders" name="inorganic_phos" id="inorganic_phos" pattern="^\d*(\.\d{0,2})?$" value="<?php echo number_format($b['inorganic_phos'],2); ?>">
                        </td>
                        <td><?php echo $o->checkChemValues2($o->age,$a['xgender'],'L159',$b['inorganic_phos'],$order['machine']); ?></td>
                        <td><?php echo $c['inorganic_phos']; ?></td>
                        <td><?php echo $d['inorganic_phos']; ?></td>
                        <td><?php echo $e['inorganic_phos']; ?></td>
                        <td><?php echo $o->getAttribute2('L159',$o->age,$a['xgender'],$order['machine']); ?></td>	
                    </tr>
                    <?php } if(checkTest('L161',$order['serialno'])) { ?>
                    <tr>
                        <td>GLUCOSE 2hrs Post Prandial&nbsp;:</td>
                        <td>
                            <input type="text" class="noBorders" name="prandial" id="prandial" pattern="^\d*(\.\d{0,2})?$" value="<?php echo number_format($b['prandial'],2); ?>">
                        </td>
                        <td><?php echo $o->checkChemValues2($o->age,$a['xgender'],'L161',$b['prandial'],$order['machine']); ?></td>
                        <td><?php echo $c['prandial']; ?></td>
                        <td><?php echo $d['prandial']; ?></td>
                        <td><?php echo $e['prandial']; ?></td>
                        <td><?php echo $o->getAttribute2('L161',$o->age,$a['xgender'],$order['machine']); ?></td>	
                    </tr>
                    <?php } ?>

                    <tr>
                        <td>Remarks&nbsp;:</td>
                        <td colspan=6>
                            <textarea name="remarks" id="remarks" style="width: 99%;" rows=3><?php echo $b['remarks']; ?></textarea>
                        </td>
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
    </table>              
</form>

<div id="printConsolidation" name="printConsolidation" style="display: none;">
	<p style="margin-left: 20px; text-align: justify;" id="message">It appears that the selected result belongs to one consolidated result sheet. You may select from the given list w/c result you wish to print.</span></p><br/>
	<form name="otherTests" id="otherTests">

	</form>
</div>


</body>
</html>