<?php 
	//ini_set("display_errors","on");
	session_start();
	require_once "handlers/_generics.php";
	
    $o = new _init;
    $order = $o->getArray("select *, date_format(extractdate,'%m/%d/%Y') as exdate from lab_samples where record_id = '$_REQUEST[lid]';");
    $a = $o->getArray("SELECT docointkey, a.enccode, SUBSTR(enccode,8,15) AS hmrno, DATE_FORMAT(dodate,'%m/%d/%Y %h:%i %p') AS orderdate, DATE_FORMAT(dodate,'%Y-%m-%d') AS xorderdate, a.hpercode, concat(c.patlast,', ', c.patfirst,', ', c.patmiddle) as pname, DATE_FORMAT(c.patbdate,'%m/%d/%Y') AS bday, DATE_FORMAT(c.patbdate,'%Y-%m-%d') AS xbday, IF(c.patsex='F','FEMALE','MALE') AS sex, c.patsex as gender, a.proccode, b.procdesc, a.donotes AS remarks, a.licno, a.estatus, entby FROM hospital_dbo.hdocord a LEFT JOIN hospital_dbo.hprocm b ON a.proccode = b.proccode LEFT JOIN hospital_dbo.hperson c ON a.hpercode = c.hpercode WHERE a.enccode = '$order[enccode]';");
 
    $b = $o->getArray("select * from lab_ogtt where enccode = '$a[enccode]' and serialno = '$order[serialno]';");

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
            $("#ogtt_date").datepicker(); 

            var remarksSelection = [
                "TEST DONE TWICE",
            ];

            var enumResultSelection = [
                "NEGATIVE",
                "POSITIVE",
                "REACTIVE",
                "NON-REACTIVE",
                "WEAKLY REACTIVE"
            ];

            $("#fbs_urine, #fhours_urine, #shours_urine").autocomplete({
                source: enumResultSelection,
                minLength: 0
            }).focus(function() {
                $(this).data("uiAutocomplete").search($(this).val());
            });

            $("#ogtt_remarks").autocomplete({
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
<form name="frmOGTT" id="frmOGTT">
    <table width=100% cellpadding=0 cellspacing=0 valign=top>
        <tr>
            <td width=35% valign=top>  
                <table width=100% cellspacing=0 cellpadding=0><tr><td width=100% class=gridHead align=center>PATIENT & ORDER INFORMATION</td></tr></table>
                <table width=100% cellpadding=0 cellspacing=0 style="border: 1px solid #cdcdcd; padding: 10px; margin-bottom: 5px;">
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%"  class="bareBold" style="padding-right: 15px;">Order Reference No.&nbsp;:</td>
                        <td align=left>
                            <input class="gridInput" style="width:100%;" type=text name="ogtt_enccode" id="ogtt_enccode" value="<?php echo $a['enccode'] ?>" readonly>
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%"  class="bareBold" style="padding-right: 15px;">Request Date&nbsp;:</td>
                        <td align=left>
                            <input class="gridInput" style="width:100%;" type=text name="ogtt_sodate" id="ogtt_sodate" value="<?php echo $a['orderdate']; ?>" readonly>
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Patient ID&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="ogtt_pid" id="ogtt_pid" value="<?php echo $a['hmrno']; ?>" readonly>
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%"  class="bareBold" style="padding-right: 15px;">Result Date&nbsp;:</td>
                        <td align=left>
                            <input class="gridInput" style="width:100%;" type=text name="ogtt_date" id="ogtt_date" value="<?php if($rdate !='') { echo $rdate; } else { echo date('m/d/Y'); } ?>">
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Patient Name&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="ogtt_pname" id="ogtt_pname" value="<?php echo $a['pname']; ?>" readonly>
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>

                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Gender&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="ogtt_gender" id="ogtt_gender" value="<?php echo $a['sex']; ?>" readonly>
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Birthdate&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="ogtt_birthdate" id="ogtt_birthdate" value="<?php echo $a['bday']; ?>" readonly>
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Age&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="ogtt_age" id="ogtt_age" value="<?php echo $o->ageDisplay; ?>" readonly>
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Requesting Physician&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="ogtt_physician" id="ogtt_physician" value="<?php echo $order['physician']; ?>" readonly>
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                </table>
                <table width=100% cellspacing=0 cellpadding=0><tr><td width=100% class=gridHead align=center>SAMPLE DETAILS</td></tr></table>
                <table width=100% cellpadding=0 cellspacing=0 style="border: 1px solid #cdcdcd; padding: 10px;">
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Test or Procedure&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="ogtt_procedure" id="ogtt_procedure" value="<?php echo $procedure ?>" readonly>
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Procedure Code&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="ogtt_code" id="ogtt_code" value="<?php echo $order['code']; ?>">
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Specimen Type&nbsp;:</td>
                        <td align=left>
                            <select class="gridInput" style="width:100%; font-size:11px;" name="ogtt_spectype" id="ogtt_spectype">
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
                            <input type="text" class="gridInput" style="width:100%;" name="ogtt_serialno" id="ogtt_serialno" value="<?php echo $order['serialno']; ?>" readonly>
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Date Extracted&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="ogtt_extractdate" id="ogtt_extractdate" value="<?php echo $order['exdate']; ?>" readonly>
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Time Extracted&nbsp;:</td>
                        <td align=left>
                
                            <input type="text" class="gridInput" style="width:100%;" name="ogtt_extracttime" id="ogtt_extracttime" value="<?php echo $order['extractime']; ?>" readonly>

                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>               
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Extracted By&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="ogtt_extractby" id="ogtt_extractby" value="<?php echo $order['extractby'];  ?>" readonly>
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Location&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="ogtt_location" id="ogtt_location" value="<?php echo $order['location'];  ?>" readonly>
                        </td>				
                    </tr>
            </table>
        </td> 
        <td width=1%>&nbsp;</td>
        <td width=64% valign=top >                
            <table width=100% id = "itemlist" class="cell-border" style="font-size:11px; font-weight: bold;">
                <thead>
                    <tr>
                        <th>PARAMETER</th>
                        <th>VALUE</th>
                        <th>URINE</th>
                        <th>FLAG</th>
                        <th>REFERENCE VALUES</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>FBS</td>
                        <td>
                            <input type="text" class="noBorders" name="ogtt_fbs" id="ogtt_fbs" style="border: none; text-align: center; background-color: inherit !important;" value="<?php echo number_format($b['result'],2); ?>" pattern="^\d*(\.\d{0,2})?$">
                        </td>
                        <td>
                            <input type="text" class="noBorders" name="fbs_urine" id="fbs_urine" style="border: none; text-align: center; background-color: inherit !important;" value="<?php echo $b['fbs_urine']; ?>" pattern="^\d*(\.\d{0,2})?$">
                        </td>
                        <td><?php echo $o->checkChemValues($o->age,$a['xgender'],'OGTT_FBS',$b['result']); ?></td>
                        <td><?php echo $o->getAttribute('OGTT_FBS',$a['age'],$a['xgender']); ?></td>	
                    </tr>
                    <tr>
                        <td>After 1 Hour</td>
                        <td>
                            <input type="text" class="noBorders" name="ogtt_fhours" id="ogtt_fhours" style="border: none; text-align: center; background-color: inherit !important;" value="<?php echo number_format($b['result2'],2); ?>" pattern="^\d*(\.\d{0,2})?$">
                        </td>
                        <td>
                            <input type="text" class="noBorders" name="fhours_urine" id="fhours_urine" style="border: none; text-align: center; background-color: inherit !important;" value="<?php echo $b['fhours_urine']; ?>" pattern="^\d*(\.\d{0,2})?$">
                        </td>
                        <td><?php echo $o->checkChemValues($o->age,$a['gender'],'OGTT_1HR',$b['result2']); ?></td>
                        <td><?php echo $o->getAttribute('OGTT_1HR',$o->age,$a['gender']); ?></td>	
                    </tr>
                    <tr>
                        <td>After 2 Hours</td>
                        <td>
                            <input type="text" class="noBorders" name="ogtt_2hours" id="ogtt_2hours" style="border: none; text-align: center; background-color: inherit !important;" value="<?php echo number_format($b['result3'],2); ?>" pattern="^\d*(\.\d{0,2})?$">
                        </td>
                        <td>
                            <input type="text" class="noBorders" name="shours_urine" id="shours_urine" style="border: none; text-align: center; background-color: inherit !important;" value="<?php echo $b['shours_urine']; ?>" pattern="^\d*(\.\d{0,2})?$">
                        </td>
                        <td><?php echo $o->checkChemValues($o->age,$a['gender'],'OGTT_2HR',$b['result3']); ?></td>
                        <td><?php echo $o->getAttribute('OGTT_2HR',$o->age,$a['gender']); ?></td>	
                    </tr>
                    <tr>
                        <td>Remarks&nbsp;:</td>
                        <td colspan=4>
                            <textarea name="ogtt_remarks" id="ogtt_remarks" style="width: 99%;" rows=3><?php echo $b['remarks']; ?></textarea>
                        </td>
                        <td style="display: none;"></td>
                        <td style="display: none;"></td>
                        <td style="display: none;"></td>
                        <td style="display: none;"></td>
                    </tr>
                </tbody>
        </table>
    </form>
</body>
</html>