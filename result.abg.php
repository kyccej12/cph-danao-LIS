<?php 
	
	session_start();
	require_once "handlers/_generics.php";
	
    $o = new _init;

    $order = $o->getArray("select *, date_format(extractdate,'%m/%d/%Y') as exdate from lab_samples where record_id = '$_REQUEST[lid]';");
    $a = $o->getArray("SELECT docointkey, a.enccode, SUBSTR(enccode,8,15) AS hmrno, DATE_FORMAT(dodate,'%m/%d/%Y %h:%i %p') AS orderdate, DATE_FORMAT(dodate,'%Y-%m-%d') AS xorderdate, a.hpercode, CONCAT(c.patlast,', ', c.patfirst,', ', c.patmiddle) AS pname, DATE_FORMAT(c.patbdate,'%m/%d/%Y') AS bday, DATE_FORMAT(c.patbdate,'%Y-%m-%d') AS xbday, IF(c.patsex='F','FEMALE','MALE') AS sex, c.patsex AS gender, a.proccode, b.procdesc, a.donotes AS remarks, a.licno, a.estatus, entby FROM hospital_dbo.hdocord a LEFT JOIN hospital_dbo.hprocm b ON a.proccode = b.proccode LEFT JOIN hospital_dbo.hperson c ON a.hpercode = c.hpercode WHERE a.enccode = '$order[enccode]' AND a.dodate = '$order[dotime]' AND a.proccode = '$order[code]'; ");
    $b = $o->getArray("SELECT * FROM lab_abgresult WHERE enccode = '$order[enccode]' and serialno = '$order[serialno]';");
    $o->calculateAge($a['xorderdate'],$a['xbday']);

    /* Previous Results */
    // $c = $o->getArray("select *, concat('<br/>',date_format(result_date,'%m/%d/%Y')) as rdate from lab_cbcresult where SUBSTR(enccode,8,15) = '$a[hmrno]' and result_date < '$a[xorderdate]' limit 1,1;");
    // $d = $o->getArray("select *, concat('<br/>',date_format(result_date,'%m/%d/%Y')) as rdate from lab_cbcresult where SUBSTR(enccode,8,15) = '$a[hmrno]' and result_date < '$a[xorderdate]' limit 2,1;");
    // $e = $o->getArray("select *, concat('<br/>',date_format(result_date,'%m/%d/%Y')) as rdate from lab_cbcresult where SUBSTR(enccode,8,15) = '$a[hmrno]' and result_date < '$a[xorderdate]' limit 3,1;");
    

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
            
            $("#cmr_date, #cmr_datecollected, #cmr_dateexpiry").datepicker(); 
        
            var myTable = $('#itemlist').DataTable({
                "scrollY":  "540",
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

            var compatibilitySelection = [
                "COMPATIBLE"
            ];

            var componentSelection = [
                "PRBC"
            ];

            var examSelection = [
                "MAJOR CROSSMATCHING (GEL TECHNOLOGY)"
            ];

            $("#cmr_component").autocomplete({
                source: componentSelection,
                minLength: 0
            }).focus(function() {
                $(this).data("uiAutocomplete").search($(this).val());
            });

            $("#cmr_examination").autocomplete({
                source: examSelection,
                minLength: 0
            }).focus(function() {
                $(this).data("uiAutocomplete").search($(this).val());
            });

            $("#cmr_compatibility").autocomplete({
                source: compatibilitySelection,
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

        // function separateMe(val) {

        //     valu = parseFloat(parent.stripComma(val));

        //     $("#platelate").val(parent.kSeparator(valu));
        // }

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
    <form name="frmABG" id="frmABG"> 
        <table width=100% cellpadding=0 cellspacing=0 valign=top>
         <tr>
             <td width=40% valign=top>
                <table width=100% cellspacing=0 cellpadding=0><tr><td width=100% class=gridHead align=center>PATIENT & ORDER INFORMATION</td></tr></table>
                <table width=100% cellpadding=0 cellspacing=0 style="border: 1px solid #cdcdcd; padding: 10px; margin-bottom: 5px;">
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%"  class="bareBold" style="padding-left: 15px;">REFERENCE CODE&nbsp;:</td>
                        <td align=left>
                            <input class="gridInput" style="width:100%;" type=text name="abg_enccode" id="abg_enccode" value="<?php echo $a['enccode']; ?>">
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%"  class="bareBold" style="padding-left: 15px;">Request Date&nbsp;:</td>
                        <td align=left>
                            <input class="gridInput" style="width:100%;" type=text name="abg_sodate" id="abg_sodate" value="<?php echo $a['orderdate']; ?>">
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">HMR #&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="abg_pid" id="abg_pid" value="<?php echo $a['hmrno']; ?>">
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%"  class="bareBold" style="padding-left: 15px;">Result Date&nbsp;:</td>
                        <td align=left>
                            <input class="gridInput" style="width:100%;" type=text name="abg_date" id="abg_date" value="<?php if($rdate !='') { echo $rdate; } else { echo date('m/d/Y'); } ?>">
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Patient Name&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="abg_pname" id="abg_pname" value="<?php echo $a['pname']; ?>">
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>

                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Gender&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="abg_gender" id="abg_gender" value="<?php echo $a['sex']; ?>">
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Birthdate&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="abg_birthdate" id="abg_birthdate" value="<?php echo $a['bday']; ?>">
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Age&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="abg_age" id="abg_age" value="<?php echo $o->ageDisplay; ?>">
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Requesting Physician&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="abg_physician" id="abg_physician" value="<?php echo $order['physician']; ?>">
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                </table>
                <table width=100% cellspacing=0 cellpadding=0><tr><td width=100% class=gridHead align=center>SAMPLE DETAILS</td></tr></table>
                <table width=100% cellpadding=0 cellspacing=0 style="border: 1px solid #cdcdcd; padding: 10px;">
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Test or Procedure&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="abg_procedure" id="abg_procedure" value="<?php echo $order['procedure']; ?>">
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Procedure Code&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="abg_ihomis_code" id="abg_ihomis_code" value="<?php echo $order['code']; ?>">
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Specimen Type&nbsp;:</td>
                        <td align=left>
                            <select class="gridInput" style="width:100%;" name="abg_sampletype" id="abg_sampletype">
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
                            <select class="gridInput" style="width:100%;" name="bloodchem_machine" id="bloodchem_machine" onchange="javascript: changeMachine(this.value);">
                                <option value ="FUJI" <?php if ($order['machine'] == 'FUJI') { echo "selected"; } ?>>FUJI NX-700</option>
                                <option value ="MINDRAY" <?php if ($order['machine'] == 'MINDRAY') { echo "selected"; } ?>>MINDRAY</option>
                                <!-- <option value ="BIOBASE" <?php if ($b['machine'] == 'BIOBASE') { echo "selected"; } ?>>BIOBASE BK Series</option> -->
                            </select>
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Sample Serial No.&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="abg_serialno" id="abg_serialno" value="<?php echo $order['serialno']; ?>">
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Date Extracted&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="abg_extractdate" id="abg_extractdate" value="<?php echo $order['exdate']; ?>">
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Time Extracted&nbsp;:</td>
                        <td align=left>
                
                            <input type="text" class="gridInput" style="width:100%;" name="abg_extracttime" id="abg_extracttime" value="<?php echo $order['extractime']; ?>" readonly>

                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Extracted By&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="abg_extractby" id="abg_extractby" value="<?php echo $order['extractby']; ?>" readonly>
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Section&nbsp;:</td>
                        <td align=left>
                            <select class="gridInput" style="width:100%;" name="abg_location" id="abg_location">
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
                <td width=69% valign=top >
                    <table width=100% cellspacing=0 cellpadding=0><tr><td width=100% class=gridHead align=center>RESULT DETAILS</td></tr></table>
                        <table width=100% cellpadding=0 cellspacing=3 class="td_content">
                            <tr>
                                <td align="left" width="35%"  class="bareBold" style="padding-right: 15px;">Result Date&nbsp;:</td>
                                <td align=left>
                                    <input class="gridInput" style="width:100%;font-size:11px;" type=text name="abg_date" id="abg_date" value="<?php echo date('m/d/Y'); ?>">
                                </td>				
                            </tr>
                            <tr><td height=2></td></tr>
                            <tr>
                                <td align="center" width="35%"  class="bareBold" style="padding-right: 15px; font-weight:bold;">MEASURED&nbsp;</td>			
                            </tr>
                            <tr><td height=2></td></tr>
                            <tr>
                                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">tCO2&nbsp;:</td>
                                <td align=left>
                                    <input class="gridInput" style="width:100%; font-size: 11px;" type=text name="abg_tco2" id="abg_tco2" value="<?php echo $b['tc02']; ?>">
                                </td>
                            </tr>
                            <tr><td height=2></td></tr>
                            <tr>
                                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">HCO3&nbsp;:</td>
                                <td align=left>
                                    <input class="gridInput" style="width:100%; font-size: 11px;" type=text name="abg_hco3" id="abg_hco3" value="<?php echo $b['hco3']; ?>">
                                </td>
                            </tr>
                            <tr><td height=2></td></tr>
                            <tr>
                                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;" valign=top>tHb&nbsp;:</td>
                                <td align=left>
                                    <input class="gridInput" style="width:100%; font-size: 11px;" type=text name="abg_thb" id="abg_thb" value="<?php echo $b['thb']; ?>">
                                </td>
                                <td></td>				
                            </tr>
                            <tr><td height=2></td></tr>
                            <tr>
                                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;" valign=top>SO2&nbsp;:</td>
                                <td align=left>
                                    <input class="gridInput" style="width:100%; font-size: 11px;" type=text name="abg_so2" id="abg_so2" value="<?php echo $b['so2']; ?>">
                                </td>
                                <td></td>				
                            </tr>
                            <tr><td height=2></td></tr>
                            <tr>
                                <td align="center" width="35%"  class="bareBold" style="padding-right: 15px; font-weight:bold;">TEMPERATURE CORRECTED&nbsp;</td>			
                            </tr>
                            <tr><td height=2></td></tr>
                            <tr>
                                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;" valign=top>pH&nbsp;:</td>
                                <td align=left>
                                    <input class="gridInput" style="width:100%; font-size: 11px;" type=text name="abg_ph" id="abg_ph" value="<?php echo $b['ph']; ?>">
                                </td>
                                <td></td>				
                            </tr>
                            <tr><td height=2></td></tr>
                            <tr>
                                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;" valign=top>PCO2&nbsp;:</td>
                                <td align=left>
                                    <input class="gridInput" style="width:100%; font-size: 11px;" type=text name="abg_pco2" id="abg_pco2" value="<?php echo $b['pco2']; ?>">
                                </td>
                                <td></td>				
                            </tr>
                            <tr><td height=2></td></tr>
                            <tr>
                                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;" valign=top>PO2&nbsp;:</td>
                                <td align=left>
                                    <input class="gridInput" style="width:100%; font-size: 11px;" type=text name="abg_po2" id="abg_po2" value="<?php echo $b['po2']; ?>">
                                </td>
                                <td></td>				
                            </tr>
                            <tr><td height=2></td></tr>
                            <tr>
                                <td align="center" width="35%"  class="bareBold" style="padding-right: 15px; font-weight:bold;">ENTERED DATA&nbsp;</td>			
                            </tr>
                            <tr><td height=2></td></tr>
                            <tr>
                                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;" valign=top>TEMP&nbsp;:</td>
                                <td align=left>
                                    <input class="gridInput" style="width:100%; font-size: 11px;" type=text name="abg_temp" id="abg_temp" value="<?php echo $b['temp']; ?>">
                                </td>
                                <td></td>				
                            </tr>
                            <tr><td height=2></td></tr>
                            <tr>
                                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;" valign=top>FiO2&nbsp;:</td>
                                <td align=left>
                                    <input class="gridInput" style="width:100%; font-size: 11px;" type=text name="abg_fio2" id="abg_fio2" value="<?php echo $b['fi02']; ?>">
                                </td>
                                <td></td>				
                            </tr>
                            <tr><td height=2></td></tr>
                            <tr>
                                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;" valign=top>Other Notable Remarks&nbsp;:</td>
                                <td align=left>
                                    <textarea name="abg_remarks" id="abg_remarks" style="width:100%;" rows=3><?php echo $b['remarks']; ?></textarea>
                                </td>
                                <td></td>				
                            </tr>
                            <tr><td height=2></td></tr>
                            <tr>
                                <td align="center" width="35%"  class="bareBold" style="padding-right: 15px; font-weight:bold;">REFERANGE RANGES&nbsp;</td>			
                            </tr>
                            <tr><td height=2></td></tr>
                            <tr>
                                <td align="center" width="40%"  class="bareBold" style="padding-right: 15px; font-weight:bold;">PH&nbsp;</td>
                                <td style="font-size:11px;"><?php echo $o->getAttribute('PH',$a['age'],$a['xgender']); ?></td>	
                               							
                            </tr>
                            <tr>
                                <td align="center" width="40%"  class="bareBold" style="padding-right: 15px; font-weight:bold;">PCO2&nbsp;</td>
                                <td style="font-size:11px;"><?php echo $o->getAttribute('PCO2',$a['age'],$a['xgender']); ?></td>							
                            </tr>
                            <tr>
                                <td align="center" width="40%"  class="bareBold" style="padding-right: 15px; font-weight:bold;">TCO2&nbsp;</td>
                                <td style="font-size:11px;"><?php echo $o->getAttribute('TC02',$a['age'],$a['xgender']); ?></td>													
                            </tr>
                            <tr>
                                <td align="center" width="40%"  class="bareBold" style="padding-right: 15px; font-weight:bold;">PO2&nbsp;</td>
                                <td style="font-size:11px;"><?php echo $o->getAttribute('PO2',$a['age'],$a['xgender']); ?></td>													
                            </tr>
                            <tr>
                                <td align="center" width="40%"  class="bareBold" style="padding-right: 15px; font-weight:bold;">tHB&nbsp;</td>
                                <td style="font-size:11px;"><?php echo $o->getAttribute('THB',$a['age'],$a['xgender']); ?></td>														
                            </tr>
                            <tr>
                                <td align="center" width="40%"  class="bareBold" style="padding-right: 15px; font-weight:bold;">SO2&nbsp;</td>
                                <td style="font-size:11px;"><?php echo $o->getAttribute('SO2',$a['age'],$a['xgender']); ?></td>												
                            </tr>
                            <tr>
                                <td align="center" width="40%"  class="bareBold" style="padding-right: 15px; font-weight:bold;">HCO3&nbsp;</td>
                                <td style="font-size:11px;"><?php echo $o->getAttribute('HCO3',$a['age'],$a['xgender']); ?></td>												
                            </tr>
                    </table>
                </table>
            </td>
        </tr>
    </table>              
</form>
</body>
</html>