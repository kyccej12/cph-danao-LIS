<?php 
	
	session_start();
	require_once "handlers/_generics.php";
	
    $o = new _init;
    $order = $o->getArray("select *, date_format(extractdate,'%m/%d/%Y') as exdate from lab_samples where record_id = '$_REQUEST[lid]';");
    $a = $o->getArray("SELECT docointkey, a.enccode, SUBSTR(enccode,8,15) AS hmrno, DATE_FORMAT(dodate,'%m/%d/%Y %h:%i %p') AS orderdate, DATE_FORMAT(dodate,'%Y-%m-%d') AS xorderdate, a.hpercode, concat(c.patlast,', ', c.patfirst,', ', c.patmiddle) as pname, DATE_FORMAT(c.patbdate,'%m/%d/%Y') AS bday, DATE_FORMAT(c.patbdate,'%Y-%m-%d') AS xbday, IF(c.patsex='F','FEMALE','MALE') AS sex, c.patsex as gender, a.proccode, b.procdesc, a.donotes AS remarks, a.licno, a.estatus, entby FROM hospital_dbo.hdocord a LEFT JOIN hospital_dbo.hprocm b ON a.proccode = b.proccode LEFT JOIN hospital_dbo.hperson c ON a.hpercode = c.hpercode WHERE a.enccode = '$order[enccode]';");
 
    $b = $o->getArray("select * from lab_coagulation where enccode = '$a[enccode]' and serialno = '$order[serialno]';");


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
            $("#coagulation_date").datepicker(); 

            var remarksSelection = [
                "TEST DONE TWICE",
            ];

            $("#remarks").autocomplete({
                source: remarksSelection,
                minLength: 0
            }).focus(function() {
                $(this).data("uiAutocomplete").search($(this).val());
            });
        
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


    </style>
</head>
<body>
    <form name="frmCoagulationResult" id="frmCoagulationResult"> 
        <table width=100% cellpadding=0 cellspacing=0 valign=top>
         <tr>
             <td width=35% valign=top>
                <table width=100% cellspacing=0 cellpadding=0><tr><td width=100% class=gridHead align=center>PATIENT & ORDER INFORMATION</td></tr></table>
                <table width=100% cellpadding=0 cellspacing=0 style="border: 1px solid #cdcdcd; padding: 10px; margin-bottom: 5px;">
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%"  class="bareBold" style="padding-left: 15px;">Rerecence #&nbsp;:</td>
                        <td align=left>
                            <input class="gridInput" style="width:100%;" type=text name="coagulation_enccode" id="coagulation_enccode" value="<?php echo $a['enccode']; ?>" readonly>
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%"  class="bareBold" style="padding-left: 15px;">Service Order Date&nbsp;:</td>
                        <td align=left>
                            <input class="gridInput" style="width:100%;" type=text name="coagulation_sodate" id="coagulation_sodate" value="<?php echo $a['orderdate']; ?>" readonly>
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">HMR No.&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="coagulation_pid" id="coagulation_pid" value="<?php echo $a['hmrno']; ?>" readonly>
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%"  class="bareBold" style="padding-left: 15px;">Result Date&nbsp;:</td>
                        <td align=left>
                            <input class="gridInput" style="width:100%;" type=text name="coagulation_date" id="coagulation_date" value="<?php if($rdate !='') { echo $rdate; } else { echo date('m/d/Y'); } ?>">
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Patient Name&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="coagulation_pname" id="coagulation_pname" value="<?php echo $a['pname']; ?>" readonly>
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>

                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Gender&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="coagulation_gender" id="coagulation_gender" value="<?php echo $a['sex']; ?>" readonly>
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Birthdate&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="coagulation_birthdate" id="coagulation_birthdate" value="<?php echo $a['bday']; ?>" readonly>
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Age&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="coagulation_age" id="coagulation_age" value="<?php echo $o->ageDisplay; ?>" readonly>
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Requesting Physician&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="coagulation_physician" id="coagulation_physician" value="<?php echo $order['physician']; ?>" readonly>
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                </table>
                <table width=100% cellspacing=0 cellpadding=0><tr><td width=100% class=gridHead align=center>SAMPLE DETAILS</td></tr></table>
                <table width=100% cellpadding=0 cellspacing=0 style="border: 1px solid #cdcdcd; padding: 10px;">
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Test or Procedure&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="coagulation_procedure" id="coagulation_procedure" value="<?php echo $procedure ?>" readonly>
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Specimen Type&nbsp;:</td>
                        <td align=left>
                            <select class="gridInput" style="width:100%;" name="coagulation_spectype" id="coagulation_spectype">
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
                            <input type="text" class="gridInput" style="width:100%;" name="coagulation_serialno" id="coagulation_serialno" value="<?php echo $order['serialno']; ?>" readonly>
                            <input type="hidden" name="coagulation_proccode" id="coagulation_proccode" value="<?php echo $order['code']; ?>">
                            <input type="hidden" name="coagulation_dotime" id="coagulation_dotime" value="<?php echo $order['dotime']; ?>">
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Date Extracted&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="coagulation_extractdate" id="coagulation_extractdate" value="<?php echo $order['exdate']; ?>" readonly>
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Time Extracted&nbsp;:</td>
                        <td align=left>
                
                            <input type="text" class="gridInput" style="width:100%;" name="coagulation_extracttime" id="coagulation_extracttime" value="<?php echo $order['extractime']; ?>" readonly>

                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Extracted By&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="coagulation_extractby" id="coagulation_extractby" value="<?php echo $order['extractby'];  ?>" readonly>
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Collection Site&nbsp;:</td>
                        <td align=left>
                             <input type="text" class="gridInput" style="width:100%;" name="coagulation_location" id="coagulation_location" value="<?php echo $order['location'];  ?>" readonly>
                        </td>				
                    </tr>
                </table>   
            </td>
            <td width=1%>&nbsp;</td>
            <td width=64% valign=top>
                <table width=100% id = "itemlist" class="cell-border" style="font-size:11px; font-weight: bold;">
                    <thead>
                        <tr>
                            <th>PARAMETER</th>
                            <th width=33%>RESULT</th>
                            <th>REFERENCE VALUES</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if(checkTest('L146',$order['serialno'])) { ?>
                    <tr>
                        <td>Prothrombin Time</td>
                        <td></td>
                        <td></td>	
                    </tr>
                    <tr>
                        <td align=center>Seconds</td>
                        <td align=center>
                            <input type="text" style="border: none; text-align: center; background-color: inherit !important;" name="proth_seconds" id="proth_seconds" value="<?php echo number_format($b['proth_seconds'],2); ?>" >
                        </td>
                        <td align=center>10 - 14</td>	
                    </tr>
                    <tr>
                        <td align=center>%</td>
                        <td align=center>
                            <input type="text" style="border: none; text-align: center; background-color: inherit !important;" name="proth_percent" id="proth_percent" value="<?php echo number_format($b['proth_percent'],2); ?>" >
                        </td>
                        <td align=center>0.07 - 1.30</td>	
                    </tr>
                    <tr>
                        <td align=center>INR</td>
                        <td align=center>
                            <input type="text" style="border: none; text-align: center; background-color: inherit !important;" name="proth_inr" id="proth_inr" value="<?php echo number_format($b['proth_inr'],2); ?>" >
                        </td>
                        <td align=center>0.7 - 1.3</td>	
                    </tr>
                    <?php } if(checkTest('L050',$order['serialno'])) { ?>
                        <tr>
                        <td>ACTIVATED THROMBOPLASTIN TIME (APTT)</td>
                        <td></td>
                        <td></td>	
                    </tr>   
                    <tr>
                        <td align=center>Seconds</td>
                        <td align=center>
                            <input type="text" style="border: none; text-align: center; background-color: inherit !important;" name="aptt_seconds" id="aptt_seconds" value="<?php echo number_format($b['aptt_seconds'],2); ?>" >
                        </td>
                        <td align=center>22 - 38</td>	
                    </tr>
                    <?php } ?>

                    <tr>
                        <td>Remarks&nbsp;:</td>
                        <td colspan=4>
                            <textarea name="remarks" id="remarks" style="width: 99%;" rows=3><?php echo $b['remarks']; ?></textarea>
                        </td>
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