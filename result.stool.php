<?php 
	
	session_start();
	require_once "handlers/_generics.php";
	
    $o = new _init;
    $order = $o->getArray("select *, date_format(extractdate,'%m/%d/%Y') as exdate, date_format(release_date,'%m/%d/%Y') as rdate from lab_samples where record_id = '$_REQUEST[lid]';");
    $a = $o->getArray("SELECT docointkey, a.enccode, SUBSTR(enccode,8,15) AS hmrno, DATE_FORMAT(dodate,'%m/%d/%Y %h:%i %p') AS orderdate, DATE_FORMAT(dodate,'%Y-%m-%d') AS xorderdate, a.hpercode, concat(c.patlast,', ', c.patfirst,', ', c.patmiddle) as pname,  DATE_FORMAT(c.patbdate,'%m/%d/%Y') AS bday, DATE_FORMAT(c.patbdate,'%Y-%m-%d') AS xbday, IF(c.patsex='F','FEMALE','MALE') AS sex, c.patsex as gender, a.proccode, b.procdesc, a.donotes AS remarks, a.licno, a.estatus, entby FROM hospital_dbo.hdocord a LEFT JOIN hospital_dbo.hprocm b ON a.proccode = b.proccode LEFT JOIN hospital_dbo.hperson c ON a.hpercode = c.hpercode WHERE a.enccode = '$order[enccode]';");
    $b = $o->getArray("select * from lab_stoolexam where enccode = '$order[enccode]' and serialno = '$order[serialno]';");
    $age = $o->calculateAge($a['xorderdate'],$a['xbday']);
    if($b['ova_parasites'] == '') { $b['ova_parasites'] = 'NO OVA & PARASITES SEEN'; }
?>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<title>Prime Care Cebu, Inc.</title>
	<link rel="icon" type="image/x-icon" href="favicon.ico" />
	<link href="ui-assets/themes/smoothness/jquery-ui.css" rel="stylesheet" type="text/css" />
	<link href="ui-assets/texteditor/jquery-te-1.4.0.css" rel="stylesheet" type="text/css" />
	<link href="style/style.css" rel="stylesheet" type="text/css" />
	<script language="javascript" src="ui-assets/jquery/jquery-1.12.3.js"></script>
	<script language="javascript" src="ui-assets/themes/smoothness/jquery-ui.js"></script>
	<script language="javascript" src="ui-assets/texteditor/jquery-te-1.4.0.min.js"></script>
	<script language="javascript" src="js/main.js?sid=<?php echo uniqid(); ?>"></script>
    <script>
        $(function() { 
            
            $("#stool_date").datepicker(); 
            var availableOptions = [
                "NEGATIVE",
                "TRACE"
            ];

            var availableOptions2 = [
                "POSITIVE",
                "NEGATIVE"
            ];

            var availableOptions3 = [
                "MODERATE",
                "FEW",
                "RARE",
                "ABUNDANT",
                "MANY"
            ];

            var colorSelection = [
                "Brown",
                "Yellowish Brown",
                "Yellow",
                "Reddish Brown",
                "Light Brown",
                "Greenish Brown",
                "Dark Brown",
                "Brown Black",
                "Green",
                "Black",
                "Grayish White",
                "Yellowish Green",
                "Red",
                "Gray",
                "Dark Gray",
            ];

            $("#color").autocomplete({
                source: colorSelection,
                minLength: 0
            }).focus(function() {
                $(this).data("uiAutocomplete").search($(this).val());
            });


            $("#blood, #mucus" ).autocomplete({
                 source: availableOptions, minLength: 0
            }).focus(function() {
                $(this).data("uiAutocomplete").search($(this).val());
            });

            $("#yeast_cells, #globules, #bacteria, #occult_blood, #starch, #muscle_fibers").autocomplete({
                 source: availableOptions3,
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
    </script>
</head>
<body>
    <form name="frmStoolReport" id="frmStoolReport"> 
        <table width=100% cellpadding=0 cellspacing=0 valign=top>
         <tr>
             <td width=35% valign=top>
                <table width=100% cellspacing=0 cellpadding=0><tr><td width=100% class=gridHead align=center>PATIENT & ORDER INFORMATION</td></tr></table>
                <table width=100% cellpadding=0 cellspacing=0 style="border: 1px solid #cdcdcd; padding: 10px; margin-bottom: 5px;">
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%"  class="bareBold" style="padding-left: 15px;">REFERENCE #&nbsp;:</td>
                        <td align=left>
                            <input class="gridInput" style="width:100%;" type=text name="stool_enccode" id="stool_enccode" value="<?php echo $order['enccode']; ?>">
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%"  class="bareBold" style="padding-left: 15px;">Request Date&nbsp;:</td>
                        <td align=left>
                            <input class="gridInput" style="width:100%;" type=text name="stool_sodate" id="stool_sodate" value="<?php echo $a['orderdate']; ?>">
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Hospital Record No.&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="stool_pid" id="stool_pid" value="<?php echo $a['hmrno']; ?>">
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%"  class="bareBold" style="padding-left: 15px;">Result Date&nbsp;:</td>
                        <td align=left>
                            <input class="gridInput" style="width:100%;" type=text name="stool_date" id="stool_date" value="<?php if($rdate !='') { echo $rdate; } else { echo date('m/d/Y'); } ?>">
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Patient Name&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="stool_pname" id="stool_pname" value="<?php echo $a['pname']; ?>">
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>

                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Gender&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="stool_gender" id="stool_gender" value="<?php echo $a['sex']; ?>">
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Birthdate&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="stool_birthdate" id="stool_birthdate" value="<?php echo $a['bday']; ?>">
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Age&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="stool_age" id="stool_age" value="<?php echo $age; ?>">
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Requesting Physician&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="stool_physician" id="stool_physician" value="<?php echo $order['physician']; ?>">
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                </table>
                <table width=100% cellspacing=0 cellpadding=0><tr><td width=100% class=gridHead align=center>SAMPLE DETAILS</td></tr></table>
                <table width=100% cellpadding=0 cellspacing=0 style="border: 1px solid #cdcdcd; padding: 10px;">
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Test or Procedure&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="stool_procedure" id="stool_procedure" value="<?php echo $order['procedure']; ?>">
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Procedure Code&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="stool_code" id="stool_code" value="<?php echo $order['code']; ?>">
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Specimen Type&nbsp;:</td>
                        <td align=left>
                            <select class="gridInput" style="width:100%;" name="stool_spectype" id="stool_spectype">
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
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Sample Serial No.&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="stool_serialno" id="stool_serialno" value="<?php echo $order['serialno']; ?>">
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Date Extracted&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="stool_extractdate" id="stool_extractdate" value="<?php echo $order['exdate']; ?>">
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Time Extracted&nbsp;:</td>
                        <td align=left>
                
                            <input type="text" class="gridInput" style="width:100%;" name="stool_extracttime" id="stool_extracttime" value="<?php echo $order['extractime']; ?>" readonly>

                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Extracted By&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="stool_extractby" id="stool_extractby" value="<?php echo $order['extractby']; ?>" readonly>
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Site&nbsp;:</td>
                        <td align=left>
                            <select class="gridInput" style="width:100%;" name="stool_location" id="stool_location">
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
                 <table width=100% cellspacing=0 cellpadding=0><tr><td width=100% class=gridHead align=center>RESULT DETAILS</td></tr></table>
                 <table width=100% cellpadding=0 cellspacing=3 class="td_content">
                    <tr>
                        <td align="left" colspan=3 class="bareBold" style="padding-left: 15px;"><b>MACROSCOPIC&nbsp;:</b></td>
                    </tr>
                    <tr>
                        <td align="left" width=25% class="bareBold" style="padding-left: 25px;">Color&nbsp;:</td>
                        <td align=left width=30%>

                        <input type="text" class="noBorders" name="color" id="color" style="width:100%;" value="<?php echo $b['color']; ?>">

                            <!-- <select name="color" id="color" class="gridInput" style="width:100%;">
                                <option value="Brown" <?php if($b['color'] == 'Brown') { echo "selected"; } ?>>Brown</option>
                                <option value="Yellowish Brown" <?php if($b['color'] == 'Yellowish Brown') { echo "selected"; } ?>>Yellowish Brown</option>
                                <option value="Yellow" <?php if($b['color'] == 'Yellow') { echo "selected"; } ?>>Yellow</option>
                                <option value="Reddish Brown" <?php if($b['color'] == 'Reddish Brown') { echo "selected"; } ?>>Reddish Brown</option>
                                <option value="Light Brown" <?php if($b['color'] == 'Light Brown') { echo "selected"; } ?>>Light Brown</option>
                                <option value="Greenish Brown" <?php if($b['color'] == 'Greenish Brown') { echo "selected"; } ?>>Greenish Brown</option>
                                <option value="Dark Brown" <?php if($b['color'] == 'Dark Brown') { echo "selected"; } ?>>Dark Brown</option>
                                <option value="Brown Black" <?php if($b['color'] == 'Brown Black') { echo "selected"; } ?>>Brown Black</option>
                                <option value="Green" <?php if($b['color'] == 'Green') { echo "selected"; } ?>>Green</option>
                                <option value="Black" <?php if($b['color'] == 'Black') { echo "selected"; } ?>>Black</option>
                                <option value="Grayish white" <?php if($b['color'] == 'Grayish White') { echo "selected"; } ?>>Grayish White</option>
                                <option value="Yellowish Green" <?php if($b['color'] == 'Yellowish Green') { echo "selected"; } ?>>Yellowish Green</option>
                                <option value="Red" <?php if($b['color'] == 'Red') { echo "selected"; } ?>>Red</option>
                            </select> -->
                        </td>
                        <td align="left" class="bareBold" style="padding-left: 15px;"></td>	
                    </tr>
                    <tr>
                        <td align="left" width=25% class="bareBold" style="padding-left: 25px;">Consistency&nbsp;:</td>
                        <td align=left>
                            <select name="consistency" id="consistency" class="gridInput" style="width:100%;">
                                <option value="Formed" <?php if($b['consistency'] == 'Formed') { echo "selected"; } ?>>Formed</option>
                                <option value="Semi Formed" <?php if($b['appearance'] == 'Semi Formed') { echo "selected"; } ?>>Semi Formed</option>
                                <option value="Soft" <?php if($b['consistency'] == 'Soft') { echo "selected"; } ?>>Soft</option>
                                <option value="Watery" <?php if($b['consistency'] == 'Watery') { echo "selected"; } ?>>Watery</option>
                                <option value="Mucoid" <?php if($b['consistency'] == 'Mucoid') { echo "selected"; } ?>>Mucoid</option>
                                <option value="Mushy" <?php if($b['consistency'] == 'Mushy') { echo "selected"; } ?>>Mushy</option>
                                <option value="Loose" <?php if($b['consistency'] == 'Loose') { echo "selected"; } ?>>Loose</option>
                                <option value="Bloody & Mucoid" <?php if($b['consistency'] == 'Bloody & Mucoid') { echo "selected"; } ?>>Bloody & Mucoid</option>
                            </select>
                        </td>
                        <td align="left" class="bareBold" style="padding-left: 25px;"></td>	
                    </tr>

                    <tr>
                        <td align="left" colspan=3 class="bareBold" style="padding-left: 15px;"><b>MICROSCOPIC&nbsp;:</b></td>
                    </tr>

                    <tr>
                        <td align="left" width=25% class="bareBold" style="padding-left: 25px;">PUS Cells&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="pus_cells" id="pus_cells" value="<?php echo $b['pus_cells']; ?>">
                        </td>
                        <td align="left" class="bareBold" style="padding-left: 15px;">/HPF</td>	
                    </tr>
                    
                    <tr>
                        <td align="left" class="bareBold" style="padding-left: 25px;">Red Blood Cells&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="rbc_hpf" id="rbc_hpf" value="<?php echo $b['rbc']; ?>">
                        </td>
                        <td align="left" class="bareBold" style="padding-left: 15px;">/HPF</td>	
                    </tr>

                    <tr>
                        <td align="left" class="bareBold" style="padding-left: 25px;">Yeast Cells&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="yeast_cells" id="yeast_cells" value="<?php echo $b['yeast_cells']; ?>">
                        </td>
                        <td align="left" class="bareBold" style="padding-left: 15px;"></td>	
                    </tr>

                    <tr>
                        <td align="left" width=25% class="bareBold" style="padding-left: 25px;">Starch&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="starch" id="starch" value="<?php echo $b['starch']; ?>">
                        </td>
                        <td align="left" class="bareBold" style="padding-left: 15px;"></td>	
                    </tr>
                    
                    <tr>
                        <td align="left" class="bareBold" style="padding-left: 25px;">Fat Globules&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="globules" id="globules" value="<?php echo $b['globules']; ?>">
                        </td>
                        <td align="left" class="bareBold" style="padding-left: 15px;"></td>	
                    </tr>

                   
                    <tr>
                        <td align="left" class="bareBold" style="padding-left: 25px;">Muscle Fibers&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="muscle_fibers" id="muscle_fibers" value="<?php echo $b['muscle_fibers']; ?>">
                        </td>
                        <td align="left" class="bareBold" style="padding-left: 25px;"></td>	
                    </tr>

                    <tr>
                        <td align="left" class="bareBold" style="padding-left: 25px;">Bacteria&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="bacteria" id="bacteria" value="<?php echo $b['bacteria']; ?>">
                        </td>
                        <td align="left" class="bareBold" style="padding-left: 15px;"></td>	
                    </tr>

                    <tr>
                        <td align="left" colspan=3 class="bareBold" style="padding-left: 15px;"><b>OVA/PARASITES/CYST&nbsp;:</b></td>
                    </tr>

                    <tr>
                        <td align="left" class="bareBold" style="padding-left: 25px;">Ascaris lumbricoides&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="ascaris" id="ascaris" value="<?php echo $b['ascaris']; ?>">
                        </td>
                        <td align="left" class="bareBold" style="padding-left: 15px;">/LPF</td>	
                    </tr>
                    
                    <tr>
                        <td align="left" class="bareBold" style="padding-left: 25px;">Trichiuris trichiuria&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="trichiuris" id="trichiuris" value="<?php echo $b['trichiuris']; ?>">
                        </td>
                        <td align="left" class="bareBold" style="padding-left: 15px;">/LPF</td>	
                    </tr>
                    
                    <tr>
                        <td align="left" class="bareBold" style="padding-left: 25px;">Hookworm spp&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="hookworm" id="hookworm" value="<?php echo $b['hookworm']; ?>">
                        </td>
                        <td align="left" class="bareBold" style="padding-left: 15px;">/LPF</td>	
                    </tr>

                    <tr>
                        <td align="left" class="bareBold" style="padding-left: 25px;">Tichomonas spp&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="trichomonas" id="trichomonas" value="<?php echo $b['trichomonas']; ?>">
                        </td>
                        <td align="left" class="bareBold" style="padding-left: 15px;">/LPF</td>	
                    </tr>       
                    
                    <tr>
                        <td align="left" class="bareBold" style="padding-left: 25px;">Strongyloides spp&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="strongyloides" id="strongyloides" value="<?php echo $b['strongyloides']; ?>">
                        </td>
                        <td align="left" class="bareBold" style="padding-left: 15px;">/LPF</td>	
                    </tr> 

                    <tr>
                        <td align="left" class="bareBold" style="padding-left: 25px;">E. histolytica cyst/E. dispar&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="histolytica_c" id="histolytica_c" value="<?php echo $b['histolytica_c']; ?>">
                        </td>
                        <td align="left" class="bareBold" style="padding-left: 15px;">/HPF</td>	
                    </tr> 

                    <tr>
                        <td align="left" class="bareBold" style="padding-left: 25px;">E. histolytica troph/E. dispar&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="histolytica_t" id="histolytica_t" value="<?php echo $b['histolytica_t']; ?>">
                        </td>
                        <td align="left" class="bareBold" style="padding-left: 15px;">/HPF</td>	
                    </tr> 

                    <tr>
                        <td align="left" colspan=3 class="bareBold" style="padding-left: 15px;"><b>Clinical Examination&nbsp;:</b></td>
                    </tr>

                    <tr>
                        <td align="left" class="bareBold" style="padding-left: 25px;">Occult Blood&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="occult_blood" id="occult_blood" value="<?php echo $b['occult_blood']; ?>">
                        </td>
                        <td align="left" class="bareBold" style="padding-left: 15px;"></td>	
                    </tr>

                    <tr>
                        <td align="left" class="bareBold" style="padding-left: 25px;">Others&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="others" id="others" value="<?php echo $b['others']; ?>">
                        </td>
                        <td align="left" class="bareBold" style="padding-left: 15px;"></td>	
                    </tr>

                    <tr>
                        <td align="left" width=25% class="bareBold" style="padding-left: 15px; font-weight: bold;" valign=top>Remarks&nbsp;:</td>
                        <td align=left width=75% colspan=3>
                            <input type="text" name="remarks" id="remarks" style="width: 90%; height: 60px; text-align: center;" value="<?php echo $b['remarks']; ?>">
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>              
</form>
</body>
</html>