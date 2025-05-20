<?php 
	
	session_start();
	require_once "handlers/_generics.php";
	
    $o = new _init;
    $order = $o->getArray("select *, date_format(extractdate,'%m/%d/%Y') as exdate from lab_samples where record_id = '$_REQUEST[lid]';");
    $a = $o->getArray("SELECT docointkey, a.enccode, SUBSTR(enccode,8,15) AS hmrno, DATE_FORMAT(dodate,'%m/%d/%Y %h:%i %p') AS orderdate, DATE_FORMAT(dodate,'%Y-%m-%d') AS xorderdate, a.hpercode, concat(c.patlast,', ', c.patfirst,', ', c.patmiddle) as pname, DATE_FORMAT(c.patbdate,'%m/%d/%Y') AS bday, DATE_FORMAT(c.patbdate,'%Y-%m-%d') AS xbday, IF(c.patsex='F','FEMALE','MALE') AS sex, c.patsex as gender, a.proccode, b.procdesc, a.donotes AS remarks, a.licno, a.estatus, entby FROM hospital_dbo.hdocord a LEFT JOIN hospital_dbo.hprocm b ON a.proccode = b.proccode LEFT JOIN hospital_dbo.hperson c ON a.hpercode = c.hpercode WHERE a.enccode = '$order[enccode]';");
 
    $b = $o->getArray("select * from lab_spchem where enccode = '$a[enccode]' and serialno = '$order[serialno]';");
    if(count($b) == 0) {
        $b = $o->getArray("select * from lab_spchem_temp where serialno = '$order[serialno]';");
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
    $c = $o->getArray("select *, concat('<br/>',date_format(result_date,'%m/%d/%Y')) as rdate from lab_cbcresult where SUBSTR(enccode,8,15) = '$a[hmrno]' and result_date < '$a[xorderdate]' limit 1,1;");
    $d = $o->getArray("select *, concat('<br/>',date_format(result_date,'%m/%d/%Y')) as rdate from lab_cbcresult where SUBSTR(enccode,8,15) = '$a[hmrno]' and result_date < '$a[xorderdate]' limit 2,1;");
    $e = $o->getArray("select *, concat('<br/>',date_format(result_date,'%m/%d/%Y')) as rdate from lab_cbcresult where SUBSTR(enccode,8,15) = '$a[hmrno]' and result_date < '$a[xorderdate]' limit 3,1;");

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
            $("#spchem_date").datepicker(); 
        
            var myTable = $('#itemlist').DataTable({
                "scrollY":  "540",
                "scrollCollapse": true,
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
    <form name="frmSpecialChemResult" id="frmSpecialChemResult"> 
        <table width=100% cellpadding=0 cellspacing=0 valign=top>
         <tr>
             <td width=35% valign=top>
                <table width=100% cellspacing=0 cellpadding=0><tr><td width=100% class=gridHead align=center>PATIENT & ORDER INFORMATION</td></tr></table>
                <table width=100% cellpadding=0 cellspacing=0 style="border: 1px solid #cdcdcd; padding: 10px; margin-bottom: 5px;">
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%"  class="bareBold" style="padding-left: 15px;">Rerecence #&nbsp;:</td>
                        <td align=left>
                            <input class="gridInput" style="width:100%;" type=text name="spchem_enccode" id="spchem_enccode" value="<?php echo $a['enccode']; ?>" readonly>
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%"  class="bareBold" style="padding-left: 15px;">Service Order Date&nbsp;:</td>
                        <td align=left>
                            <input class="gridInput" style="width:100%;" type=text name="spchem_sodate" id="spchem_sodate" value="<?php echo $a['orderdate']; ?>" readonly>
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">HMR No.&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="spchem_pid" id="spchem_pid" value="<?php echo $a['hmrno']; ?>" readonly>
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%"  class="bareBold" style="padding-left: 15px;">Result Date&nbsp;:</td>
                        <td align=left>
                            <input class="gridInput" style="width:100%;" type=text name="spchem_date" id="spchem_date" value="<?php if($rdate !='') { echo $rdate; } else { echo date('m/d/Y'); } ?>">
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Patient Name&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="spchem_pname" id="spchem_pname" value="<?php echo $a['pname']; ?>" readonly>
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>

                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Gender&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="spchem_gender" id="spchem_gender" value="<?php echo $a['sex']; ?>" readonly>
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Birthdate&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="spchem_birthdate" id="spchem_birthdate" value="<?php echo $a['bday']; ?>" readonly>
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Age&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="spchem_age" id="spchem_age" value="<?php echo $o->ageDisplay; ?>" readonly>
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Requesting Physician&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="spchem_physician" id="spchem_physician" value="<?php echo $order['physician']; ?>" readonly>
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                </table>
                <table width=100% cellspacing=0 cellpadding=0><tr><td width=100% class=gridHead align=center>SAMPLE DETAILS</td></tr></table>
                <table width=100% cellpadding=0 cellspacing=0 style="border: 1px solid #cdcdcd; padding: 10px;">
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Test or Procedure&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="spchem_procedure" id="spchem_procedure" value="<?php echo $procedure ?>" readonly>
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Specimen Type&nbsp;:</td>
                        <td align=left>
                            <select class="gridInput" style="width:100%;" name="spchem_spectype" id="spchem_spectype">
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
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Sample Serial No.&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="spchem_serialno" id="spchem_serialno" value="<?php echo $order['serialno']; ?>" readonly>
                            <input type="hidden" name="spchem_proccode" id="spchem_proccode" value="<?php echo $order['code']; ?>">
                            <input type="hidden" name="spchem_dotime" id="spchem_dotime" value="<?php echo $order['dotime']; ?>">
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Date Extracted&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="spchem_extractdate" id="spchem_extractdate" value="<?php echo $order['exdate']; ?>" readonly>
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Time Extracted&nbsp;:</td>
                        <td align=left>
                
                            <input type="text" class="gridInput" style="width:100%;" name="spchem_extracttime" id="spchem_extracttime" value="<?php echo $order['extractime']; ?>" readonly>

                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Extracted By&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="spchem_extractby" id="spchem_extractby" value="<?php echo $order['extractby'];  ?>" readonly>
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Collection Site&nbsp;:</td>
                        <td align=left>
                             <input type="text" class="gridInput" style="width:100%;" name="spchem_location" id="spchem_location" value="<?php echo $order['location'];  ?>" readonly>
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
                            <th align=center>REFERENCE VALUES</th>
                        </tr>
                    </thead>
                    <tbody>

                    <?php if(checkTest('L072',$order['serialno'])) { ?>
                    <tr>
                        <td>AFP</td>
                        <td>
                            <input type="text" class="noBorders" name="afp" id="afp" value="<?php echo number_format($b['afp'],2); ?>" pattern="^\d*(\.\d{0,2})?$">
                        </td>
                        <td><?php echo $o->checkChemValues($o->age,$a['xgender'],'L072',$b['afp']); ?></td>
                        <td><?php echo $c['afp']; ?></td>
                        <td><?php echo $d['afp']; ?></td>
                        <td><?php echo $e['afp']; ?></td>
                        <td><?php echo $o->getAttribute('L072',$o->age,$a['xgender']); ?></td>	
                    </tr>
                    <?php } if(checkTest('L073',$order['serialno']) || checkTest('L140',$order['serialno']) || checkTest('L064',$order['serialno'])) { ?>
                    <tr>
                        <td>B-HCG</td>
                        <td>
                            <input type="text" class="noBorders" name="bhcg" id="bhcg" value="<?php echo $b['bhcg']; ?>">
                        </td>
                        <td><?php echo $o->checkChemValues($o->age,$a['xgender'],'L073',$b['bhcg']); ?></td>
                        <td><?php echo $c['bhcg']; ?></td>
                        <td><?php echo $d['bhcg']; ?></td>
                        <td><?php echo $e['bhcg']; ?></td>
                        <td><?php echo $o->getAttribute('L073',$o->age,$a['xgender']); ?></td>	
                    </tr>
                    <?php } if(checkTest('L074',$order['serialno'])) { ?>   
                    <tr>
                        <td>B-HCG w/ Titer</td>
                        <td>
                            <input type="text" class="noBorders" name="bhcgt" id="bhcgt" value="<?php echo number_format($b['bhcgt'],2); ?>" pattern="^\d*(\.\d{0,2})?$">
                        </td>
                        <td><?php echo $o->checkChemValues($o->age,$a['xgender'],'L074',$b['bhcgt']); ?></td>
                        <td><?php echo $c['bhcgt']; ?></td>
                        <td><?php echo $d['bhcgt']; ?></td>
                        <td><?php echo $e['bhcgt']; ?></td>
                        <td><?php echo $o->getAttribute('L074',$o->age,$a['xgender']); ?></td>	
                    </tr>
                    <?php } if(checkTest('L075',$order['serialno'])) { ?>        
                    <tr>
                        <td>Carcino Embryonic Antigen (CEA)</td>
                        <td>
                            <input type="text" class="noBorders" name="cea" id="cea" value="<?php echo number_format($b['cea'],2); ?>" pattern="^\d*(\.\d{0,2})?$">
                        </td>
                        <td><?php echo $o->checkChemValues($o->age,$a['xgender'],'L075',$b['cea']); ?></td>
                        <td><?php echo $c['cea']; ?></td>
                        <td><?php echo $d['cea']; ?></td>
                        <td><?php echo $e['cea']; ?></td>
                        <td><?php echo $o->getAttribute('L075',$o->age,$a['xgender']); ?></td>	
                    </tr>
                    <?php } if(checkTest('L078',$order['serialno']) || checkTest('L070',$order['serialno'])) { ?>        
                    <tr>
                        <td>CRP</td>
                        <td>
                            <input type="text" class="noBorders" name="crp" id="crp" value="<?php echo $b['crp']; ?>" />
                        </td>
                        <td><?php echo $o->checkChemValues2($o->age,$a['xgender'],'L078',preg_replace("/^(\d{1,2}[^0-9])/"," ",$b['crp'])); ?></td>
                        <td><?php echo $c['crp']; ?></td>
                        <td><?php echo $d['crp']; ?></td>
                        <td><?php echo $e['crp']; ?></td>
                        <td><?php echo $o->getAttribute2('L078',$o->age,$a['xgender']); ?></td>	
                    </tr>
                    <tr>
                        <td>HS-CRP</td>
                        <td>
                            <input type="text" class="noBorders" name="hscrp" id="hscrp" value="<?php echo $b['hscrp']; ?>" />
                        </td>
                        <td><?php echo $o->checkChemValues2($o->age,$a['xgender'],'HSCRP',preg_replace("/^(\d{1,2}[^0-9])/"," ",$b['hscrp'])); ?></td>
                        <td><?php echo $c['hscrp']; ?></td>
                        <td><?php echo $d['hscrp']; ?></td>
                        <td><?php echo $e['hscrp']; ?></td>
                        <td><?php echo $o->getAttribute2('HSCRP',$o->age,$a['xgender']); ?></td>	
                    </tr>
                    <?php } if(checkTest('L138',$order['serialno'])) { ?>
                    <tr>
                        <td>D-Dimer</td>
                        <td>
                            <input type="text" class="noBorders" name="dimer" id="dimer" value="<?php echo number_format($b['dimer'],2); ?>" pattern="^\d*(\.\d{0,2})?$">
                        </td>
                        <td><?php echo $o->checkChemValues($o->age,$a['xgender'],'L138',$b['dimer']); ?></td>
                        <td><?php echo $c['dimer']; ?></td>
                        <td><?php echo $d['dimer']; ?></td>
                        <td><?php echo $e['dimer']; ?></td>
                        <td><?php echo $o->getAttribute('L138',$o->age,$a['xgender']); ?></td>	
                    </tr>

                    <?php } if(checkTest('L044',$order['serialno'])) { ?>
                    <tr>
                        <td>FT3</td>
                        <td>
                            <input type="text" class="noBorders" name="ft3" id="ft3" value="<?php echo number_format($b['ft3'],2); ?>" pattern="^\d*(\.\d{0,2})?$">
                        </td>
                        <td><?php echo $o->checkChemValues($o->age,$a['xgender'],'L044',$b['ft3']); ?></td>
                        <td><?php echo $c['ft3']; ?></td>
                        <td><?php echo $d['ft3']; ?></td>
                        <td><?php echo $e['ft3']; ?></td>
                        <td><?php echo $o->getAttribute('L044',$o->age,$a['xgender']); ?></td>	
                    </tr>
                    <?php } if(checkTest('L045',$order['serialno'])) { ?>
                    <tr>
                        <td>FT4</td>
                        <td>
                            <input type="text" class="noBorders" name="ft4" id="ft4" value="<?php echo number_format($b['ft4'],2); ?>" pattern="^\d*(\.\d{0,2})?$">
                        </td>
                        <td><?php echo $o->checkChemValues($o->age,$a['xgender'],'L045',$b['ft4']); ?></td>
                        <td><?php echo $c['ft4']; ?></td>
                        <td><?php echo $d['ft4']; ?></td>
                        <td><?php echo $e['ft4']; ?></td>
                        <td><?php echo $o->getAttribute('L045',$o->age,$a['xgender']); ?></td>	
                    </tr>
                    <?php } if(checkTest('L081',$order['serialno'])) { ?>
                    <tr>
                        <td>GGT&nbsp;:</td>
                        <td>
                            <input type="text" class="noBorders" name="ggt" id="ggt" value="<?php echo number_format($b['ggt'],2); ?>" pattern="^\d*(\.\d{0,2})?$">
                        </td>
                        <td><?php echo $o->checkChemValues($o->age,$a['xgender'],'L081',$b['ggt']); ?></td>
                        <td><?php echo $c['ggt']; ?></td>
                        <td><?php echo $d['ggt']; ?></td>
                        <td><?php echo $e['ggt']; ?></td>
                        <td><?php echo $o->getAttribute('L081',$o->age,$a['xgender']); ?></td>	
                    </tr>
                    <?php } if(checkTest('L049',$order['serialno'])) { ?>    
                    <tr>
                        <td>TSH</td>
                        <td>
                            <input type="text" class="noBorders" name="tsh" id="tsh" value="<?php echo number_format($b['tsh'],2); ?>">
                        </td>
                        <td><?php echo $o->checkChemValues($o->age,$a['xgender'],'L049',$b['tsh']); ?></td>
                        <td><?php echo $c['tsh']; ?></td>
                        <td><?php echo $d['tsh']; ?></td>
                        <td><?php echo $e['tsh']; ?></td>
                        <td><?php echo $o->getAttribute('L049',$o->age,$a['xgender']); ?></td>	
                    </tr>
                    
                    <?php } if(checkTest('L022',$order['serialno'])) { ?>
                    <tr>
                        <td>HbA1c&nbsp;:</td>
                        <td>
                            <input type="text" class="noBorders" name="hba1c" id="hba1c" value="<?php echo $b['hba1c']; ?>">
                        </td>
                        <td><?php echo $o->checkChemValues($o->age,$a['xgender'],'L022',$b['hba1c']); ?></td>
                        <td><?php echo $c['hba1c']; ?></td>
                        <td><?php echo $d['hba1c']; ?></td>
                        <td><?php echo $e['hba1c']; ?></td>
                        <td><?php echo $o->getAttribute('L022',$o->age,$a['xgender']); ?></td>	
                    </tr>
                   
                    <?php } if(checkTest('L048',$order['serialno'])) { ?>
                    <tr>
                        <td>T4</td>
                        <td>
                            <input type="text" class="noBorders" name="t4" id="t4" value="<?php echo number_format($b['t4'],2); ?>">
                        </td>
                        <td><?php echo $o->checkChemValues($o->age,$a['xgender'],'L048',$b['t4']); ?></td>
                        <td><?php echo $c['t4']; ?></td>
                        <td><?php echo $d['t4']; ?></td>
                        <td><?php echo $e['t4']; ?></td>
                        <td><?php echo $o->getAttribute('L048',$o->age,$a['xgender']); ?></td>	
                    </tr>
                    <?php } if(checkTest('L047',$order['serialno'])) { ?>                
                    <tr>
                        <td>T3</td>
                        <td>
                            <input type="text" class="noBorders" name="t3" id="t3" value="<?php echo number_format($b['t3'],2); ?>" pattern="^\d*(\.\d{0,2})?$">
                        </td>
                        <td><?php echo $o->checkChemValues($o->age,$a['xgender'],'L047',$b['t3']); ?></td>
                        <td><?php echo $c['t3']; ?></td>
                        <td><?php echo $d['t3']; ?></td>
                        <td><?php echo $e['t3']; ?></td>
                        <td><?php echo $o->getAttribute('L047',$o->age,$a['xgender']); ?></td>	
                    </tr>
                    
                    <?php } if(checkTest('L131',$order['serialno'])) { ?>
                    <tr>
                        <td>TROP-I (Quantitative)</td>
                        <td>
                            <input type="text" class="noBorders" name="tropi_qn" id="tropi_qn" value="<?php echo $b['tropi_qn']; ?>">
                        </td>
                        <td><?php echo $o->checkChemValues($o->age,$a['xgender'],'L131',trim($b['tropi_qn'],'<')); ?></td>
                        <td><?php echo $c['tropi_qn']; ?></td>
                        <td><?php echo $d['tropi_qn']; ?></td>
                        <td><?php echo $e['tropi_qn']; ?></td>
                        <td><?php echo $o->getAttribute('L131',$o->age,$a['xgender']); ?></td>	
                    </tr>
                    
                    <?php } if(checkTest('L137',$order['serialno'])) { ?>
                    <tr>
                        <td>TROP-I (Qualitative)</td>
                        <td>
                            <select name="tropi_ql" id="tropi_ql" class="gridInput">
                                <option value="NEGATIVE" <?php if($a['tropi_ql'] == 'NEGATIVE') { echo "selected"; } ?>>NEGATIVE</option>
                                <option value="POSITIVE" <?php if($a['tropi_ql'] == 'POSITIVE') { echo "selected"; } ?>>POSITIVE</option>
                            </select>
                        </td>
                        <td><?php echo $o->checkChemValues($o->age,$a['xgender'],'L131',trim($b['tropi_qn'],'<')); ?></td>
                        <td><?php echo $c['tropi_ql']; ?></td>
                        <td><?php echo $d['tropi_ql']; ?></td>
                        <td><?php echo $e['tropi_ql']; ?></td>
                        <td></td>	
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