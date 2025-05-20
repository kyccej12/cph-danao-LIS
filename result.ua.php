<?php 
	
	session_start();
	require_once "handlers/_generics.php";
	
    $o = new _init;

    $order = $o->getArray("select *, date_format(extractdate,'%m/%d/%Y') as exdate from lab_samples where record_id = '$_REQUEST[lid]';");
    $a = $o->getArray("SELECT docointkey, a.enccode, SUBSTR(enccode,8,15) AS hmrno, DATE_FORMAT(dodate,'%m/%d/%Y %h:%i %p') AS orderdate, DATE_FORMAT(dodate,'%Y-%m-%d') AS xorderdate, a.hpercode, concat(c.patlast,', ', c.patfirst,' ', c.patmiddle) as pname, DATE_FORMAT(c.patbdate,'%m/%d/%Y') AS bday, DATE_FORMAT(c.patbdate,'%Y-%m-%d') AS xbday, IF(c.patsex='F','FEMALE','MALE') AS sex, c.patsex as gender, a.proccode, b.procdesc, a.donotes AS remarks, a.licno, a.estatus, entby FROM hospital_dbo.hdocord a LEFT JOIN hospital_dbo.hprocm b ON a.proccode = b.proccode LEFT JOIN hospital_dbo.hperson c ON a.hpercode = c.hpercode WHERE a.enccode = '$order[enccode]';");
    $b = $o->getArray("select * from lab_uaresult where enccode = '$order[enccode]' and serialno = '$order[serialno]';");
   
    $age = $o->calculateAge($a['xorderdate'],$a['xbday']);

    /* SET DEFAULT VALUE */
    //if(!$b['glucose']) { $b['glucose'] = 'NEGATIVE'; }
    //if(!$b['protein']) { $b['protein'] = 'NEGATIVE'; }

    /* if($b['ph'] >= 7) { 
        $uratesDisabled = "disabled"; 
        $poDisabled = '';
    } else {
        $uratesDisabled = ''; 
        $poDisabled = "disabled";
    } */


?>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<title>Prime Care Cebu, Inc.</title>
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
            $("#ua_date").datepicker(); 

            var myTable = $('#itemlist').DataTable({
                "scrollY":  "580",
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

            
            var availableOptions = [
                "NEGATIVE",
                "POSITIVE"
            ];

            var availableOptions2 = [
                "POSITIVE",
                "NEGATIVE",
                "1+",
                "2+",
                "3+",
                "4+",
                "+",
                "++",
                "+++",
                "++++",
                "SMALL",
                "MODERATE",
                "LARGE",
                "TRACE"
            ];

            var availableOptions3 = [
                "AMORPHOUS URATE: ",
                "AMOURPHOUS PHOSPATE: ",
                "AMMONIUM BIURATE: ",
                "URIC ACID CRYSTAL: ",
                "TRIPLE PHOSPATE: ",
                "CALCIUM OXALATE: ",
                "BILIRUBIN CRYSTALS: ",
                "CHOLESTEROL CRYSTALS: "
            ];

            var availableOptions4 = [
                "HYALINE CAST: 0-1/LPF",
                "FINE GRANULAR CAST: 0-1/LPF",
                "COARSE GRANULAR CAST: 0-1/LPF",
                "RBC CAST: 0-1/LPF",
                "WBC CAST: 0-1/LPF",
                "WAXY CAST: 0-1/LPF",
                "CYLINDROIDS"
            ];

            var availableOptions5 = [
                "MODERATE",
                "FEW",
                "RARE",
                "ABUNDANT",
                "MANY"
            ];

            var availableOptions6 = [
                "0.2",
                "1.0",
                "2.0",
                "4.0",
                "12.0",
                "NEGATIVE",
                "1+",
                "2+",
                "3+",
                "4+",
                "+",
                "++",
                "+++",
                "TRACE"
            ];

            var availableOptions7 = [
                "NEGATIVE",
                "TRACE",
                "TRACE - INTACT",
                "TRACE - HEMOLYZED",
                "1+",
                "2+",
                "3+",
                "4+",
                "+",
                "++",
                "+++",
                "++++",
                "SMALL",
                "MODERATE",
                "LARGE",
                "TRACE"
            ];

            var colorSelection = [
                "Yellow",
                "Light Yellow",
                "Greenish Yellow",
                "Yellowish Green",
                "Pale Yellow",
                "Dark Yellow",
                "Amber",
                "Straw",
                "Dark Brown",
                "Bright Yellow",
                "Red",
                "Light Pink",
                "Light Orange",

            ];

            var remarksSelection = [
                "TEST DONE TWICE",
            ];

            $("#color").autocomplete({
                source: colorSelection,
                minLength: 0
            }).focus(function() {
                $(this).data("uiAutocomplete").search($(this).val());
            });

            $("#remarks").autocomplete({
                source: remarksSelection,
                minLength: 0
            }).focus(function() {
                $(this).data("uiAutocomplete").search($(this).val());
            });

            $("#protein,  #ketone, #bilirubin, #leukocyte" ).autocomplete({
                 source: availableOptions2,
                 minLength: 0
            }).focus(function() {
                $(this).data("uiAutocomplete").search($(this).val());
            });

            $("#nitrite" ).autocomplete({
                 source: availableOptions,
                 minLength: 0
            }).focus(function() {
                $(this).data("uiAutocomplete").search($(this).val());
            });

            $("#glucose, #blood" ).autocomplete({
                 source: availableOptions7,
                 minLength: 0
            }).focus(function() {
                $(this).data("uiAutocomplete").search($(this).val());
            });

            $("#urobilinogen" ).autocomplete({
                 source: availableOptions6,
                 minLength: 0
            }).focus(function() {
                $(this).data("uiAutocomplete").search($(this).val());
            });

            $("#crystals, #crystals1, #crystals2, #crystals3, #crystals4, #crystals5, #crystals5, #crystals6, #crystals7").autocomplete({
                 source: availableOptions3,
                 minLength: 0
            }).focus(function() {
                $(this).data("uiAutocomplete").search($(this).val());
            }); 

            $("#casts, #casts1, #casts2, #casts3, #casts4, #casts5, #casts6").autocomplete({
                 source: availableOptions4,
                 minLength: 0
            }).focus(function() {
                $(this).data("uiAutocomplete").search($(this).val());
            });

            $("#bacteria, #epith, #mucus_thread, #amorphous_urates, #amorphous_po4").autocomplete({
                source: availableOptions5,
                minLength: 0
            }).focus(function() {
                $(this).data("uiAutocomplete").search($(this).val());
            });
        
        });

        $(document).on('keypress', 'input', function(e) {
            if(e.keyCode == 13) {
                e.preventDefault();
                var inputs = $(this).closest('form').find(':input:visible');
                 inputs.eq( inputs.index(this)+ 1 ).focus();
            }
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
            border: none !important; text-align: center; width: 80%; background-color: inherit !important;
        }

    </style>
</head>
<body>
    <form name="frmUrinalysisReport" id="frmUrinalysisReport"> 
        <input type="hidden" name="ua_primecarecode" id="ua_primecarecode" value = '<?php echo $order['primecarecode']; ?>'>
        <table width=100% cellpadding=0 cellspacing=0 valign=top>
         <tr>
             <td width=35% valign=top>
                <table width=100% cellspacing=0 cellpadding=0><tr><td width=100% class=gridHead align=center>PATIENT & ORDER INFORMATION</td></tr></table>
                <table width=100% cellpadding=0 cellspacing=0 style="border: 1px solid #cdcdcd; padding: 10px; margin-bottom: 5px;">
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%"  class="bareBold" style="padding-left: 15px;">Reference Code&nbsp;:</td>
                        <td align=left>
                            <input class="gridInput" style="width:100%;" type=text name="ua_enccode" id="ua_enccode" value="<?php echo $a['enccode']; ?>">
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%"  class="bareBold" style="padding-left: 15px;">Request Date&nbsp;:</td>
                        <td align=left>
                            <input class="gridInput" style="width:100%;" type=text name="ua_orderdate" id="ua_orderdate" value="<?php echo $a['orderdate']; ?>">
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Hospital No.&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="ua_pid" id="ua_pid" value="<?php echo $a['hmrno']; ?>">
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%"  class="bareBold" style="padding-left: 15px;">Result Date&nbsp;:</td>
                        <td align=left>
                            <input class="gridInput" style="width:100%;" type=text name="ua_date" id="ua_date" value="<?php if($rdate !='') { echo $rdate; } else { echo date('m/d/Y'); } ?>">
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Patient Name&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="ua_pname" id="ua_pname" value="<?php echo $a['pname']; ?>">
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>

                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Gender&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="ua_gender" id="ua_gender" value="<?php echo $a['gender']; ?>">
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Birthdate&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="ua_birthdate" id="ua_birthdate" value="<?php echo $a['bday']; ?>">
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Age&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="ua_age" id="ua_age" value="<?php echo $age; ?>">
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Requesting Physician&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="ua_physician" id="ua_physician" value="<?php echo $order['physician']; ?>">
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                </table>
                <table width=100% cellspacing=0 cellpadding=0><tr><td width=100% class=gridHead align=center>SAMPLE DETAILS</td></tr></table>
                <table width=100% cellpadding=0 cellspacing=0 style="border: 1px solid #cdcdcd; padding: 10px;">
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Test or Procedure&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="ua_procedure" id="ua_procedure" value="<?php echo $order['procedure']; ?>">
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Procedure Code&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="ua_code" id="ua_code" value="<?php echo $order['code']; ?>">
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Specimen Type&nbsp;:</td>
                        <td align=left>
                            <select class="gridInput" style="width:100%;" name="ua_spectype" id="ua_spectype">
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
                            <input type="text" class="gridInput" style="width:100%;" name="ua_serialno" id="ua_serialno" value="<?php echo $order['serialno']; ?>">
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Date Extracted&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="ua_extractdate" id="ua_extractdate" value="<?php echo $order['exdate']; ?>">
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Time Extracted&nbsp;:</td>
                        <td align=left>
                
                            <input type="text" class="gridInput" style="width:100%;" name="ua_extracttime" id="ua_extracttime" value="<?php echo $order['extractime']; ?>" readonly>

                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Extracted By&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="ua_extractby" id="ua_extractby" value="<?php echo $a['extractby']; ?>" readonly>
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Extraction Site&nbsp;:</td>
                        <td align=left>
                            <select class="gridInput" style="width:100%;" name="ua_location" id="ua_location">
                                <?php
                                    $iun = $o->dbquery("select id,location from lab_locations;");
                                    while(list($aa,$ab) = $iun->fetch_array()) {
                                        echo "<option value='$aa' ";
                                        if($aa == $a['location']) { echo "selected"; }
                                        echo ">$ab</option>";
                                    }
                                ?>
                            </select>
                        </td>				
                    </tr>
                </table>   
            </td>
            <td width=1%>&nbsp;</td>
            <td width=64% valign=top >
                 <table width=100% id = "itemlist" class="cell-border" style="font-size:11px;">
                    <thead>
                        <tr>
                            <th>PARAMETER</th>
                            <th>CURRENT RESULT</th>
                            <th>PREVIOUS<?php echo $c['rdate']; ?></th>
                            <th>PREVIOUS<?php echo $d['rdate']; ?></th>
                            <th>PREVIOUS<?php echo $e['rdate']; ?></th>
                        </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td colspan=6><b>MACROSCOPIC EXAMINATION&nbsp;:</b></td>
                        <td style="display: none;"></td>
                        <td style="display: none;"></td>
                        <td style="display: none;"></td>
                        <td style="display: none;"></td>
                    </tr>
                    <tr>
                        <td style="padding-left: 35px;">Color&nbsp;:</td>
                        <td>

                        <input type="text" class="noBorders" name="color" id="color" value="<?php echo $b['color']; ?>">

                            <!-- <select name="color" id="color" class="gridInput" style="width:80%;">
                                <option value="Yellow" <?php if($b['color'] == 'Yellow') { echo "selected"; } ?> >Yellow</option>
                                <option value="Light Yellow" <?php if($b['color'] == 'Light Yellow') { echo "selected"; } ?>>Light Yellow</option>
                                <option value="Pale Yellow" <?php if($b['color'] == 'Pale Yellow') { echo "selected"; } ?>>Pale Yellow</option>
                                <option value="Dark Yellow" <?php if($b['color'] == 'Dark Yellow') { echo "selected"; } ?>>Dark Yellow</option>
                                <option value="Amber" <?php if($b['color'] == 'Amber') { echo "selected"; } ?>>Amber</option>
                                <option value="Straw" <?php if($b['color'] == 'Straw') { echo "selected"; } ?>>Straw</option>
                                <option value="Dark Brown" <?php if($b['color'] == 'Dark Brown') { echo "selected"; } ?>>Dark Brown</option>
                                <option value="Bright Yellow" <?php if($b['color'] == 'Bright Yellow') { echo "selected"; } ?>>Bright Yellow</option>
                                <option value="Red" <?php if($b['color'] == 'Red') { echo "selected"; } ?>>Red</option>
                            </select> -->
                        </td>
                        <td><?php echo $c['color'] ?></td>	
                        <td><?php echo $d['color'] ?></td>	
                        <td><?php echo $e['color'] ?></td>	
                    </tr>
                    <tr>
                        <td style="padding-left: 35px;">Transparency&nbsp;:</td>
                        <td>
                            <select name="transparency" id="transparency" class="gridInput" style="width:80%;">
                                <option value="Clear" <?php if($b['transparency'] == 'Clear') { echo "selected"; } ?>>Clear</option>
                                <option value="Hazy" <?php if($b['transparency'] == 'Hazy') { echo "selected"; } ?>>Hazy</option>
                                <option value="Slightly Hazy" <?php if($b['transparency'] == 'Slightly Hazy') { echo "selected"; } ?>>Slightly Hazy</option>
                                <option value="Cloudy" <?php if($b['transparency'] == 'Cloudy') { echo "selected"; } ?>>Cloudy</option>
                                <option value="Slightly Cloudy" <?php if($b['transparency'] == 'Slightly Cloudy') { echo "selected"; } ?>>Slightly Cloudy</option>
                                <option value="Turbid" <?php if($b['transparency'] == 'Turbid') { echo "selected"; } ?>>Turbid</option>
                            </select>
                        </td>
                        <td><?php echo $c['transparency'] ?></td>	
                        <td><?php echo $d['transparency'] ?></td>	
                        <td><?php echo $e['transparency'] ?></td>		
                    </tr>
                    <tr>
                        <td colspan=6><b>CHEMICAL EXAMINATION&nbsp;:</b></td>
                        <td style="display: none;"></td>
                        <td style="display: none;"></td>
                        <td style="display: none;"></td>
                        <td style="display: none;"></td>
                    </tr>
                    <tr>
                        <td style="padding-left: 35px;">Glucose&nbsp;:</td>
                        <td>
                            <input  type="text" class="noBorders" name="glucose" id="glucose" value="<?php echo $b['glucose']; ?>">
                        </td>
                        <td><?php echo $c['glucose'] ?></td>	
                        <td><?php echo $d['glucose'] ?></td>	
                        <td><?php echo $e['glucose'] ?></td>	
                    </tr>
                    <tr>
                        <td style="padding-left: 35px;">Bilirubin&nbsp;:</td>
                        <td>
                            <input type="text" class="noBorders" name="bilirubin" id="bilirubin" value="<?php echo $b['bilirubin']; ?>">
                        </td>
                        <td><?php echo $c['bilirubin'] ?></td>	
                        <td><?php echo $d['bilirubin'] ?></td>	
                        <td><?php echo $e['bilirubin'] ?></td>	
                    </tr>
                    <tr>
                        <td style="padding-left: 35px;">Ketone&nbsp;:</td>
                        <td>
                            <input type="text" class="noBorders" name="ketone" id="ketone" value="<?php echo $b['ketone']; ?>">
                        </td>
                        <td><?php echo $c['ketone'] ?></td>	
                        <td><?php echo $d['ketone'] ?></td>	
                        <td><?php echo $e['ketone'] ?></td>	
                    </tr>
                    <tr>
                        <td style="padding-left: 35px;">Specific Gravity&nbsp;:</td>
                        <td>
                            <select name="gravity" id="gravity" class="gridInput" style="width:80%;">
                            <?php
                                for($sgloop = 1.000; $sgloop <= 1.030; $sgloop+=0.005) {
                                    $valsg = number_format($sgloop, 3);

                                    echo "<option value='".$valsg."' "; 
                                    if($b['gravity'] == $valsg) { echo "selected"; }
                                    echo ">".$valsg."</option>";
                                }
                            ?>
                            </select>

                        </td>
                        <td><?php echo $c['gravity'] ?></td>	
                        <td><?php echo $d['gravity'] ?></td>	
                        <td><?php echo $e['gravity'] ?></td>		
                    </tr>
                    
                    <tr>
                        <td style="padding-left: 35px;">Blood&nbsp;:</td>
                        <td>
                            <input type="text" class="noBorders" name="blood" id="blood" value="<?php echo $b['blood']; ?>">
                        </td>
                        <td><?php echo $c['blood'] ?></td>	
                        <td><?php echo $d['blood'] ?></td>	
                        <td><?php echo $e['blood'] ?></td>	
                    </tr>

                    <tr>
                        <td style="padding-left: 35px;">pH&nbsp;:</td>
                        <td>
                            <select name="ph" id="ph" class="gridInput" style="width:80%;" onchange="javascript: checkPhValue(this.value);">
                            <?php
                                for($phloop = 4.5; $phloop <= 9; $phloop+=0.5) {
                                    echo "<option value='".number_format($phloop,1)."' "; 
                                    if($b['ph'] == $phloop) { echo "selected"; }
                                    echo ">".number_format($phloop,1)."</option>";
                                }

                            ?>
                            </select>
                        </td>
                        <td><?php echo $c['ph'] ?></td>	
                        <td><?php echo $d['ph'] ?></td>	
                        <td><?php echo $e['ph'] ?></td>		
                    </tr>

                    <tr>
                        <td style="padding-left: 35px;">Protein&nbsp;:</td>
                        <td>
                            <input type="text" class="noBorders" name="protein" id="protein" value="<?php echo $b['protein']; ?>">
                        </td>
                        <td><?php echo $c['protein'] ?></td>	
                        <td><?php echo $d['protein'] ?></td>	
                        <td><?php echo $e['protein'] ?></td>	
                    </tr>
                    
                    <tr>
                        <td style="padding-left: 35px;">Urobilinogen&nbsp;:</td>
                        <td>
                            <input type="text" class="noBorders" name="urobilinogen" id="urobilinogen" value="<?php echo $b['urobilinogen']; ?>">
                        </td>
                        <td><?php echo $c['urobilinogen'] ?></td>	
                        <td><?php echo $d['urobilinogen'] ?></td>	
                        <td><?php echo $e['urobilinogen'] ?></td>	
                    </tr>
                    
                    <tr>
                        <td style="padding-left: 35px;">Nitrite&nbsp;:</td>
                        <td>
                            <input type="text" class="noBorders" name="nitrite" id="nitrite" value="<?php echo $b['nitrite']; ?>">
                        </td>
                        <td><?php echo $c['nitrite'] ?></td>	
                        <td><?php echo $d['nitrite'] ?></td>	
                        <td><?php echo $e['nitrite'] ?></td>	
                    </tr>

                    <tr>
                        <td style="padding-left: 35px;">Leukocyte&nbsp;:</td>
                        <td>
                            <input type="text" class="noBorders" name="leukocyte" id="leukocyte" value="<?php echo $b['leukocyte']; ?>">
                        </td>
                        <td><?php echo $c['leukocyte'] ?></td>	
                        <td><?php echo $d['leukocyte'] ?></td>	
                        <td><?php echo $e['leukocyte'] ?></td>	
                    </tr>

                    <tr>
                        <td colspan=6><b>MICROSCOPIC EXAMINATION&nbsp;:</b></td>
                        <td style="display: none;"></td>
                        <td style="display: none;"></td>
                        <td style="display: none;"></td>
                        <td style="display: none;"></td>
                    </tr>

                    <tr>
                        <td style="padding-left: 35px;">PUS Cells&nbsp;:</td>
                        <td>
                            <input type="text" class="noBorders" style="width:50%;" name="pus" id="pus" value="<?php echo $b['pus']; ?>"> /HPF
                        </td>
                        <td><?php echo $c['pus'] ?></td>	
                        <td><?php echo $d['pus'] ?></td>	
                        <td><?php echo $e['pus'] ?></td>	
                    </tr>

                    <tr>
                        <td style="padding-left: 35px;">RBC&nbsp;:</td>
                        <td>
                            <input type="text" class="noBorders" style="width:50%;" name="rbc_hpf" id="rbc_hpf" value="<?php echo $b['rbc_hpf']; ?>"> /HPF
                        </td>
                        <td><?php echo $c['rbc_hpf'] ?></td>	
                        <td><?php echo $d['rbc_hpf'] ?></td>	
                        <td><?php echo $e['rbc_hpf'] ?></td>
                    </tr>
                    <tr>
                        <td style="padding-left: 35px;">Epithelial Cells&nbsp;:</td>
                        <td>
                            <input type="text" class="noBorders" name="epith" id="epith" value="<?php echo $b['epith']; ?>">
                        </td>
                        <td><?php echo $c['epith'] ?></td>	
                        <td><?php echo $d['epith'] ?></td>	
                        <td><?php echo $e['epith'] ?></td>	
                    </tr>

                    <tr>
                        <td style="padding-left: 35px;">Mucus Threads&nbsp;:</td>
                        <td>
                            <input type="text" class="noBorders" name="mucus_thread" id="mucus_thread" value="<?php echo $b['mucus_thread']; ?>">
                        </td>
                        <td><?php echo $c['mucus_thread'] ?></td>	
                        <td><?php echo $d['mucus_thread'] ?></td>	
                        <td><?php echo $e['mucus_thread'] ?></td>	
                    </tr>

                    <tr>
                        <td style="padding-left: 35px;">Bacteria&nbsp;:</td>
                        <td>
                            <input type="text" class="noBorders" name="bacteria" id="bacteria" value="<?php echo $b['bacteria']; ?>">
                        </td>
                        <td><?php echo $c['bacteria'] ?></td>	
                        <td><?php echo $d['bacteria'] ?></td>	
                        <td><?php echo $e['bacteria'] ?></td>	
                    </tr>
                    <tr>
                        <td style="padding-left: 35px;">Casts&nbsp;:</td>
                        <td>
                            <input type="text" class="noBorders" name="casts" id="casts" value="<?php echo $b['casts']; ?>">
                        </td>
                        <td><?php echo $c['casts'] ?></td>	
                        <td><?php echo $d['casts'] ?></td>	
                        <td><?php echo $e['casts'] ?></td>
                    </tr>
                    <tr>
                        <td style="padding-left: 35px;"></td>
                        <td>
                            <input type="text" class="noBorders" name="casts1" id="casts1" value="<?php echo $b['casts1']; ?>">
                        </td>
                        <td><?php echo $c['casts1'] ?></td>	
                        <td><?php echo $d['casts1'] ?></td>	
                        <td><?php echo $e['casts1'] ?></td>
                    </tr>
                    <tr>
                        <td style="padding-left: 35px;"></td>
                        <td>
                            <input type="text" class="noBorders" name="casts2" id="casts2" value="<?php echo $b['casts2']; ?>">
                        </td>
                        <td><?php echo $c['casts2'] ?></td>	
                        <td><?php echo $d['casts2'] ?></td>	
                        <td><?php echo $e['casts2'] ?></td>
                    </tr>
                    <tr>
                        <td style="padding-left: 35px;"></td>
                        <td>
                            <input type="text" class="noBorders" name="casts3" id="casts3" value="<?php echo $b['casts3']; ?>">
                        </td>
                        <td><?php echo $c['casts3'] ?></td>	
                        <td><?php echo $d['casts3'] ?></td>	
                        <td><?php echo $e['casts3'] ?></td>
                    </tr>
                    <tr>
                        <td style="padding-left: 35px;"></td>
                        <td>
                            <input type="text" class="noBorders" name="casts4" id="casts4" value="<?php echo $b['casts4']; ?>">
                        </td>
                        <td><?php echo $c['casts4'] ?></td>	
                        <td><?php echo $d['casts4'] ?></td>	
                        <td><?php echo $e['casts4'] ?></td>
                    </tr>
                    <tr>
                        <td style="padding-left: 35px;"></td>
                        <td>
                            <input type="text" class="noBorders" name="casts5" id="casts5" value="<?php echo $b['casts5']; ?>">
                        </td>
                        <td><?php echo $c['casts5'] ?></td>	
                        <td><?php echo $d['casts5'] ?></td>	
                        <td><?php echo $e['casts5'] ?></td>
                    </tr>
                    <tr>
                        <td style="padding-left: 35px;"></td>
                        <td>
                            <input type="text" class="noBorders" name="casts6" id="casts6" value="<?php echo $b['casts6']; ?>">
                        </td>
                        <td><?php echo $c['casts6'] ?></td>	
                        <td><?php echo $d['casts6'] ?></td>	
                        <td><?php echo $e['casts6'] ?></td>
                    </tr>
                    <tr>
                        <td style="padding-left: 35px;">Crystals&nbsp;:</td>
                        <td>
                            <input type="text" class="noBorders" name="crystals" id="crystals" value="<?php echo $b['crystals']; ?>">
                        </td>
                        <td><?php echo $c['crystals'] ?></td>	
                        <td><?php echo $d['crystals'] ?></td>	
                        <td><?php echo $e['crystals'] ?></td>
                    </tr>
                    <tr>
                        <td style="padding-left: 35px;"></td>
                        <td>
                            <input type="text" class="noBorders" name="crystals1" id="crystals1" value="<?php echo $b['crystals1']; ?>">
                        </td>
                        <td><?php echo $c['crystals1'] ?></td>	
                        <td><?php echo $d['crystals1'] ?></td>	
                        <td><?php echo $e['crystals1'] ?></td>
                    </tr>
                    <tr>
                        <td style="padding-left: 35px;"></td>
                        <td>
                            <input type="text" class="noBorders" name="crystals2" id="crystals2" value="<?php echo $b['crystals2']; ?>">
                        </td>
                        <td><?php echo $c['crystals2'] ?></td>	
                        <td><?php echo $d['crystals2'] ?></td>	
                        <td><?php echo $e['crystals2'] ?></td>
                    </tr>
                    <tr>
                        <td style="padding-left: 35px;"></td>
                        <td>
                            <input type="text" class="noBorders" name="crystals3" id="crystals3" value="<?php echo $b['crystals3']; ?>">
                        </td>
                        <td><?php echo $c['crystals3'] ?></td>	
                        <td><?php echo $d['crystals3'] ?></td>	
                        <td><?php echo $e['crystals3'] ?></td>
                    </tr>
                    <tr>
                        <td style="padding-left: 35px;"></td>
                        <td>
                            <input type="text" class="noBorders" name="crystals4" id="crystals4" value="<?php echo $b['crystals4']; ?>">
                        </td>
                        <td><?php echo $c['crystals4'] ?></td>	
                        <td><?php echo $d['crystals4'] ?></td>	
                        <td><?php echo $e['crystals4'] ?></td>
                    </tr>
                    <tr>
                        <td style="padding-left: 35px;"></td>
                        <td>
                            <input type="text" class="noBorders" name="crystals5" id="crystals5" value="<?php echo $b['crystals5']; ?>">
                        </td>
                        <td><?php echo $c['crystals5'] ?></td>	
                        <td><?php echo $d['crystals5'] ?></td>	
                        <td><?php echo $e['crystals5'] ?></td>
                    </tr>
                    <tr>
                        <td style="padding-left: 35px;"></td>
                        <td>
                            <input type="text" class="noBorders" name="crystals6" id="crystals6" value="<?php echo $b['crystals6']; ?>">
                        </td>
                        <td><?php echo $c['crystals6'] ?></td>	
                        <td><?php echo $d['crystals6'] ?></td>	
                        <td><?php echo $e['crystals6'] ?></td>
                    </tr>
                    <tr>
                        <td style="padding-left: 35px;"></td>
                        <td>
                            <input type="text" class="noBorders" name="crystals7" id="crystals7" value="<?php echo $b['crystals7']; ?>">
                        </td>
                        <td><?php echo $c['crystals7'] ?></td>	
                        <td><?php echo $d['crystals7'] ?></td>	
                        <td><?php echo $e['crystals7'] ?></td>
                    </tr>
                    <tr>
                        <td style="padding-left: 35px;">Amorphous (Urates)&nbsp;:</td>
                        <td>
                            <input type="text" class="noBorders" name="amorphous_urates" id="amorphous_urates" value="<?php echo $b['amorphous_urates']; ?>">
                        </td>
                        <td><?php echo $c['amorphous_urates'] ?></td>	
                        <td><?php echo $d['amorphous_urates'] ?></td>	
                        <td><?php echo $e['amorphous_urates'] ?></td>	
                    </tr>
                    <tr>
                        <td style="padding-left: 35px;">Amorphous (Phosphates)&nbsp;:</td>
                        <td>
                            <input type="text" class="noBorders" name="amorphous_po4" id="amorphous_po4" value="<?php echo $b['amorphous_po4']; ?>">
                        </td>
                        <td><?php echo $c['amorphous_po4'] ?></td>	
                        <td><?php echo $d['amorphous_po4'] ?></td>	
                        <td><?php echo $e['amorphous_po4'] ?></td>	
                    </tr>
                    <tr>
                        <td><b>Others&nbsp;:</b></td>
                        <td colspan=6>
                            <input type="text" class="noBorders" style="width:90%; " name="others" id="others" value="<?php echo $b['others']; ?>">
                        </td>
                        <td style="display: none;"></td>
                        <td style="display: none;"></td>
                        <td style="display: none;"></td>
                    </tr>             
                    <tr>
                        <td><b>Remarks&nbsp;:</b></td>
                        <td colspan=6>
                            <input type="text" class="noBorders" style="width:90%; " name="remarks" id="remarks" value="<?php echo $b['remarks']; ?>">
                        </td>
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
</body>
</html>