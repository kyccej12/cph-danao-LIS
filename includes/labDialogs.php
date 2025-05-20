
<div id="singleValueResult" style="display: none;">
    <form name="frmsingleValue" id="frmsingleValue">  
        <table width=100% cellspacing=0 cellpadding=0><tr><td width=100% class=gridHead align=center>PATIENT & ORDER INFORMATION</td></tr></table>
        <table width=100% cellpadding=0 cellspacing=0 style="border: 1px solid #cdcdcd; padding: 10px; margin-bottom: 5px;">
            <tr><td height=3></td></tr>
            <tr>
                <td align="left" width="35%"  class="bareBold" style="padding-right: 15px;">Order Reference No.&nbsp;:</td>
                <td align=left>
                    <input class="gridInput" style="width:100%;" type=text name="sresult_enccode" id="sresult_enccode" readonly>
                </td>				
            </tr>
            <tr><td height=3></td></tr>
            <tr>
                <td align="left" width="35%"  class="bareBold" style="padding-right: 15px;">Order Reference Date&nbsp;:</td>
                <td align=left>
                    <input class="gridInput" style="width:100%;" type=text name="sresult_orderdate" id="sresult_orderdate" readonly>
                </td>				
            </tr>
            <tr><td height=3></td></tr>
            <tr>
                <td align="left" width="35%"  class="bareBold" style="padding-right: 15px;">Result Date&nbsp;:</td>
                <td align=left>
                    <input class="gridInput" style="width:100%;" type=text name="sresult_date" id="sresult_date" value="<?php echo date('m/d/Y'); ?>">
                </td>				
            </tr>
            <tr><td height=3></td></tr>
            <tr>
                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Hospital Record #&nbsp;:</td>
                <td align=left>
                    <input type="text" class="gridInput" style="width:100%;" name="sresult_pid" id="sresult_pid" readonly>
                </td>				
            </tr>
            <tr><td height=3></td></tr>
            <tr>
                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Patient Name&nbsp;:</td>
                <td align=left>
                    <input type="text" class="gridInput" style="width:100%;" name="sresult_pname" id="sresult_pname" readonly>
                </td>				
            </tr>
            <tr><td height=3></td></tr>

            <tr>
                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Gender&nbsp;:</td>
                <td align=left>
                    <input type="text" class="gridInput" style="width:100%;" name="sresult_gender" id="sresult_gender" readonly>
                </td>				
            </tr>
            <tr><td height=3></td></tr>
            <tr>
                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Birthdate&nbsp;:</td>
                <td align=left>
                    <input type="text" class="gridInput" style="width:100%;" name="sresult_birthdate" id="sresult_birthdate" readonly>
                </td>				
            </tr>
            <tr><td height=3></td></tr>
            <tr>
                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Age&nbsp;:</td>
                <td align=left>
                    <input type="text" class="gridInput" style="width:100%;" name="sresult_age" id="sresult_age" readonly>
                </td>				
            </tr>
            <tr><td height=3></td></tr>
            <tr>
                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Requesting Physician&nbsp;:</td>
                <td align=left>
                    <input type="text" class="gridInput" style="width:100%;" name="sresult_physician" id="sresult_physician">
                </td>				
            </tr>
            <tr><td height=3></td></tr>
        </table>
        <table width=100% cellspacing=0 cellpadding=0><tr><td width=100% class=gridHead align=center>SAMPLE DETAILS</td></tr></table>
        <table width=100% cellpadding=0 cellspacing=0 style="border: 1px solid #cdcdcd; padding: 10px;">
            <tr>
                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Test or Procedure&nbsp;:</td>
                <td align=left>
                    <input type="text" class="gridInput" style="width:100%;" name="sresult_procedure" id="sresult_procedure" readonly>
                </td>				
            </tr>
            <tr><td height=3></td></tr>
            <tr>
                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Procedure Code&nbsp;:</td>
                <td align=left>
                    <input type="text" class="gridInput" style="width:100%;" name="sresult_code" id="sresult_code" readonly>
                    <input type="hidden" name="sresult_primecarecode" id="sresult_primecarecode">
                </td>				
            </tr>
            <tr><td height=3></td></tr>
            <tr>
                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Specimen Type&nbsp;:</td>
                <td align=left>
                    <select class="gridInput" style="width:100%; font-size: 11px;" name="sresult_spectype" id="sresult_spectype">
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
                    <input type="text" class="gridInput" style="width:100%;" name="sresult_serialno" id="sresult_serialno" readonly>
                </td>				
            </tr>
            <tr><td height=3></td></tr>
            <tr>
                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Date Extracted&nbsp;:</td>
                <td align=left>
                    <input type="text" class="gridInput" style="width:100%;" name="sresult_extractdate" id="sresult_extractdate" readonly>
                </td>				
            </tr>
            <tr><td height=3></td></tr>
            <tr>
                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Time Extracted&nbsp;:</td>
                <td align=left>
                 <input type="text" class="gridInput" style="width:100%;" name="sresult_extracttime" id="sresult_extracttime" readonly>
                </td>				
            </tr>
            <tr><td height=3></td></tr>
            <tr>
                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Extracted By&nbsp;:</td>
                <td align=left>
                    <input type="text" class="gridInput" style="width:100%;" name="sresult_by" id="sresult_by" readonly>
                </td>				
            </tr>
            <tr><td height=3></td></tr>
            <tr>
                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Extraction Site&nbsp;:</td>
                <td align=left>
                    <input type="text" class="gridInput" style="width:100%;" name="sresult_location" id="sresult_location" readonly>
                </td>				
            </tr>
        </table>                  
        <table width=100% cellspacing=0 cellpadding=0><tr><td width=100% class=gridHead align=center>RESULT DETAILS</td></tr></table>
        <table width=100% cellpadding=0 cellspacing=0 style="border: 1px solid #cdcdcd; padding: 10px;">
            <tr><td height=3></td></tr>
            <tr>
                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Result Attribute&nbsp;:</td>
                <td align=left>
                    <input type="text" class="gridInput" style="width:50%;" name="sresult_attribute" id="sresult_attribute" readonly>
                </td>				
            </tr>
            <tr><td height=3></td></tr>               
            <tr>
                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Unit of Measure (UoM)&nbsp;:</td>
                <td align=left>
                    <input type="text" class="gridInput" style="width:50%;" name="sresult_unit" id="sresult_unit">
                </td>				
            </tr>
            <tr><td height=3></td></tr>
            <tr>
                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Result Value&nbsp;:</td>
                <td align=left>
                    <input type="text" class="gridInput" style="width:50%;" name="sresult_value" id="sresult_value">
                </td>				
            </tr>
            <tr><td height=3></td></tr>
            <tr>
                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;" valign=top>Other Notable Remarks&nbsp;:</td>
                <td align=left>
                    <textarea name="sresult_remarks" id="sresult_remarks" style="width:100%; font-size: 11px;" rows=3></textarea>
                </td>				
            </tr>
        </table>
    </form>
</div>

<div id="enumResult" style="display: none;">
    <form name="frmEnumResult" id="frmEnumResult">  
    <table width=100% cellspacing=0 cellpadding=0><tr><td width=100% class=gridHead align=center>PATIENT & ORDER INFORMATION</td></tr></table>
        <table width=100% cellpadding=0 cellspacing=0 style="border: 1px solid #cdcdcd; padding: 10px; margin-bottom: 5px;">
            <tr><td height=3></td></tr>
            <tr>
                <td align="left" width="35%"  class="bareBold" style="padding-right: 15px;">Order Reference No.&nbsp;:</td>
                <td align=left>
                    <input class="gridInput" style="width:100%;" type=text name="enum_sono" id="enum_sono">
                </td>				
            </tr>
            <tr><td height=3></td></tr>
            <tr>
                <td align="left" width="35%"  class="bareBold" style="padding-right: 15px;">Order Reference Date&nbsp;:</td>
                <td align=left>
                    <input class="gridInput" style="width:100%;" type=text name="enum_sodate" id="enum_sodate">
                </td>				
            </tr>
            <tr><td height=3></td></tr>
            <tr>
                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Patient ID&nbsp;:</td>
                <td align=left>
                    <input type="text" class="gridInput" style="width:100%;" name="enum_pid" id="enum_pid">
                </td>				
            </tr>
            <tr><td height=3></td></tr>
            <tr>
                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Patient Name&nbsp;:</td>
                <td align=left>
                    <input type="text" class="gridInput" style="width:100%;" name="enum_pname" id="enum_pname">
                </td>				
            </tr>
            <tr><td height=3></td></tr>

            <tr>
                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Gender&nbsp;:</td>
                <td align=left>
                    <input type="text" class="gridInput" style="width:100%;" name="enum_gender" id="enum_gender">
                </td>				
            </tr>
            <tr><td height=3></td></tr>
            <tr>
                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Birthdate&nbsp;:</td>
                <td align=left>
                    <input type="text" class="gridInput" style="width:100%;" name="enum_birthdate" id="enum_birthdate">
                </td>				
            </tr>
            <tr><td height=3></td></tr>
            <tr>
                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Age&nbsp;:</td>
                <td align=left>
                    <input type="text" class="gridInput" style="width:100%;" name="enum_age" id="enum_age">
                </td>				
            </tr>
            <tr><td height=3></td></tr>
            <tr>
                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Patient Status&nbsp;:</td>
                <td align=left>
                    <input type="text" class="gridInput" style="width:100%;" name="enum_patientstat" id="enum_patientstat">
                </td>				
            </tr>
            <tr><td height=3></td></tr>
            <tr>
                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Requesting Physician&nbsp;:</td>
                <td align=left>
                    <input type="text" class="gridInput" style="width:100%;" name="enum_physician" id="enum_physician">
                </td>				
            </tr>
            <tr><td height=3></td></tr>
        </table>
        <table width=100% cellspacing=0 cellpadding=0><tr><td width=100% class=gridHead align=center>SAMPLE DETAILS</td></tr></table>
        <table width=100% cellpadding=0 cellspacing=0 style="border: 1px solid #cdcdcd; padding: 10px;">
            <tr>
                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Test or Procedure&nbsp;:</td>
                <td align=left>
                    <input type="text" class="gridInput" style="width:100%;" name="enum_procedure" id="enum_procedure">
                </td>				
            </tr>
            <tr><td height=3></td></tr>
            <tr>
                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Procedure Code&nbsp;:</td>
                <td align=left>
                    <input type="text" class="gridInput" style="width:100%;" name="enum_code" id="enum_code">
                </td>				
            </tr>
            <tr><td height=3></td></tr>
            <tr>
                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Specimen Type&nbsp;:</td>
                <td align=left>
                    <select class="gridInput" style="width:100%;" name="enum_spectype" id="enum_spectype">
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
                    <input type="text" class="gridInput" style="width:100%;" name="enum_serialno" id="enum_serialno">
                </td>				
            </tr>
            <tr><td height=3></td></tr>
            <tr>
                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Date Extracted&nbsp;:</td>
                <td align=left>
                    <input type="text" class="gridInput" style="width:100%;" name="enum_extractdate" id="enum_extractdate">
                </td>				
            </tr>
            <tr><td height=3></td></tr>
            <tr>
                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Time Extracted&nbsp;:</td>
                <td align=left>
        
                    <input type="text" class="gridInput" style="width:100%;" name="enum_extracttime" id="enum_extracttime" readonly>

                </td>				
            </tr>
            <tr><td height=3></td></tr>
            <tr>
                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Test Kit Type (If Applicable)&nbsp;:</td>
                <td align=left>
                    <input type="text" class="gridInput" style="width:100%;" name="enum_testkit" id="enum_testkit" readonly>
                </td>				
            </tr>
            <tr><td height=3></td></tr>
            <tr>
                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Lot No. (If Applicable)&nbsp;:</td>
                <td align=left>
                    <input type="text" class="gridInput" style="width:100%;" name="enum_testkit_lotno" id="enum_testkit_lotno" readonly>
                </td>				
            </tr>
            <tr><td height=3></td></tr>
            <tr>
                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Expiry (If Applicable&nbsp;):</td>
                <td align=left>
                    <input type="text" class="gridInput" style="width:100%;" name="enum_testkit_expiry" id="enum_testkit_expiry" readonly>
                </td>				
            </tr>
            <tr><td height=3></td></tr>
            <tr>
                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Extracted By&nbsp;:</td>
                <td align=left>
                    <input type="text" class="gridInput" style="width:100%;" name="enum_extractby" id="enum_extractby" readonly>
                </td>				
            </tr>
            <tr><td height=3></td></tr>
            <tr>
                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Phleb/Imaging Site&nbsp;:</td>
                <td align=left>
                    <input type="text" class="gridInput" style="width:100%;" name="enum_location" id="enum_location" readonly>
                </td>				
            </tr>
        </table>                  
        <table width=100% cellspacing=0 cellpadding=0><tr><td width=100% class=gridHead align=center>RESULT DETAILS</td></tr></table>
        <table width=100% cellpadding=0 cellspacing=0 style="border: 1px solid #cdcdcd; padding: 10px;">
            <tr>
                <td align="left" width="35%"  class="bareBold" style="padding-right: 15px;">Result Date&nbsp;:</td>
                <td align=left>
                    <input class="gridInput" style="width:100%;" type=text name="enum_date" id="enum_date" value="<?php echo date('m/d/Y'); ?>">
                </td>				
            </tr>
            <tr><td height=3></td></tr>
            <tr><td height=3></td></tr>
            <tr>
                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Result&nbsp;:</td>
                <td align=left>
                    <select name="enum_result" id="enum_result" class="gridInput" style="width:100%">
                        <option value="NEGATIVE">NEGATIVE</option>
                        <option value="POSITIVE">POSITVE</option>
                    </select>
                </td>				
            </tr>
            <tr><td height=3></td></tr>
            <tr>
                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Test Performed By&nbsp;:</td>
                <td align=left>
                    <select name="enum_result_by" id="enum_result_by" class="gridInput" style="width:100%">
                        <option value="">- Not Applicable -</option>
                        <?php
                            $pbyQuery = $o->dbquery("select emp_id, fullname from user_info where role like '%MEDICAL TECH%';");
                            while($pbyRow = $pbyQuery->fetch_array()) {
                                echo "<option value = '$pbyRow[0]'>$pbyRow[1]</option>";
                            }
                        ?>
                    </select>
                </td>				
            </tr>
            <tr><td height=3></td></tr>
            <tr>
                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;" valign=top>Other Notable Remarks&nbsp;:</td>
                <td align=left>
                    <textarea name="enum_remarks" id="enum_remarks" style="width:100%;" rows=3></textarea>
                </td>				
            </tr>
        </table>
    </form>
</div>

<div id="havResult" style="display: none;">
    <form name="frmHavResult" id="frmHavResult">  
    <table width=100% cellspacing=0 cellpadding=0><tr><td width=100% class=gridHead align=center>PATIENT & ORDER INFORMATION</td></tr></table>
        <table width=100% cellpadding=0 cellspacing=0 style="border: 1px solid #cdcdcd; padding: 10px; margin-bottom: 5px;">
            <tr><td height=3></td></tr>
            <tr>
                <td align="left" width="35%"  class="bareBold" style="padding-right: 15px;">Order Reference No.&nbsp;:</td>
                <td align=left>
                    <input class="gridInput" style="width:100%;" type=text name="hav_enccode" id="hav_enccode">
                </td>				
            </tr>
            <tr><td height=3></td></tr>
            <tr>
                <td align="left" width="35%"  class="bareBold" style="padding-right: 15px;">Order Reference Date&nbsp;:</td>
                <td align=left>
                    <input class="gridInput" style="width:100%;" type=text name="hav_sodate" id="hav_sodate">
                </td>				
            </tr>
            <tr><td height=3></td></tr>
            <tr>
                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Patient ID&nbsp;:</td>
                <td align=left>
                    <input type="text" class="gridInput" style="width:100%;" name="hav_pid" id="hav_pid">
                </td>				
            </tr>
            <tr><td height=3></td></tr>
            <tr>
                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Patient Name&nbsp;:</td>
                <td align=left>
                    <input type="text" class="gridInput" style="width:100%;" name="hav_pname" id="hav_pname">
                </td>				
            </tr>
            <tr><td height=3></td></tr>

            <tr>
                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Gender&nbsp;:</td>
                <td align=left>
                    <input type="text" class="gridInput" style="width:100%;" name="hav_gender" id="hav_gender">
                </td>				
            </tr>
            <tr><td height=3></td></tr>
            <tr>
                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Birthdate&nbsp;:</td>
                <td align=left>
                    <input type="text" class="gridInput" style="width:100%;" name="hav_birthdate" id="hav_birthdate">
                </td>				
            </tr>
            <tr><td height=3></td></tr>
            <tr>
                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Age&nbsp;:</td>
                <td align=left>
                    <input type="text" class="gridInput" style="width:100%;" name="hav_age" id="hav_age">
                </td>				
            </tr>
            <tr><td height=3></td></tr>
            <tr>
                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Patient Status&nbsp;:</td>
                <td align=left>
                    <input type="text" class="gridInput" style="width:100%;" name="hav_patientstat" id="hav_patientstat">
                </td>				
            </tr>
            <tr><td height=3></td></tr>
            <tr>
                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Requesting Physician&nbsp;:</td>
                <td align=left>
                    <input type="text" class="gridInput" style="width:100%;" name="hav_physician" id="hav_physician">
                </td>				
            </tr>
            <tr><td height=3></td></tr>
        </table>
        <table width=100% cellspacing=0 cellpadding=0><tr><td width=100% class=gridHead align=center>SAMPLE DETAILS</td></tr></table>
        <table width=100% cellpadding=0 cellspacing=0 style="border: 1px solid #cdcdcd; padding: 10px;">
            <tr>
                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Test or Procedure&nbsp;:</td>
                <td align=left>
                    <input type="text" class="gridInput" style="width:100%;" name="hav_procedure" id="hav_procedure">
                </td>				
            </tr>
            <tr><td height=3></td></tr>
            <tr>
                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Procedure Code&nbsp;:</td>
                <td align=left>
                    <input type="text" class="gridInput" style="width:100%;" name="hav_code" id="hav_code">
                </td>				
            </tr>
            <tr><td height=3></td></tr>
            <tr>
                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Specimen Type&nbsp;:</td>
                <td align=left>
                    <select class="gridInput" style="width:100%;" name="hav_spectype" id="hav_spectype">
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
                    <input type="text" class="gridInput" style="width:100%;" name="hav_serialno" id="hav_serialno">
                </td>				
            </tr>
            <tr><td height=3></td></tr>
            <tr>
                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Date Extracted&nbsp;:</td>
                <td align=left>
                    <input type="text" class="gridInput" style="width:100%;" name="hav_extractdate" id="hav_extractdate">
                </td>				
            </tr>
            <tr><td height=3></td></tr>
            <tr>
                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Time Extracted&nbsp;:</td>
                <td align=left>
        
                    <input type="text" class="gridInput" style="width:100%;" name="hav_extracttime" id="hav_extracttime" readonly>

                </td>				
            </tr>
            <tr><td height=3></td></tr>
            <tr>
                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Extracted By&nbsp;:</td>
                <td align=left>
                    <input type="text" class="gridInput" style="width:100%;" name="hav_extractby" id="hav_extractby" readonly>
                </td>				
            </tr>
            <tr><td height=3></td></tr>
            <tr>
                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Phleb/Imaging Site&nbsp;:</td>
                <td align=left>
                    <input type="text" class="gridInput" style="width:100%;" name="hav_location" id="hav_location" readonly>
                </td>				
            </tr>
        </table>                  
        <table width=100% cellspacing=0 cellpadding=0><tr><td width=100% class=gridHead align=center>RESULT DETAILS</td></tr></table>
        <table width=100% cellpadding=0 cellspacing=0 style="border: 1px solid #cdcdcd; padding: 10px;">
            <tr>
                <td align="left" width="35%"  class="bareBold" style="padding-right: 15px;">Result Date&nbsp;:</td>
                <td align=left>
                    <input class="gridInput" style="width:100%;" type=text name="hav_date" id="hav_date" value="<?php echo date('m/d/Y'); ?>">
                </td>				
            </tr>
            <tr><td height=3></td></tr>
            <tr><td height=3></td></tr>
            <tr>
                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">IGM ANTI-HAV&nbsp;:</td>
                <td align=left>
                    <input class="gridInput" style="width:100%;" type=text name="hav_result_igm" id="hav_result_igm">
                </td>				
            </tr>
            <tr><td height=3></td></tr>
            <tr>
                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">IGG ANTI-HAV&nbsp;:</td>
                <td align=left>
                    <input class="gridInput" style="width:100%;" type=text name="hav_result_igg" id="hav_result_igg">
                </td>				
            </tr>
            <tr><td height=3></td></tr>
            <tr>
                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;" valign=top>Other Notable Remarks&nbsp;:</td>
                <td align=left>
                    <textarea name="hav_remarks" id="hav_remarks" style="width:100%;" rows=3></textarea>
                </td>				
            </tr>
        </table>
    </form>
</div>

<div id="pregnancyResult" style="display: none;">
    <form name="frmPregnancyResult" id="frmPregnancyResult">  
    <table width=100% cellspacing=0 cellpadding=0><tr><td width=100% class=gridHead align=center>PATIENT & ORDER INFORMATION</td></tr></table>
        <table width=100% cellpadding=0 cellspacing=0 style="border: 1px solid #cdcdcd; padding: 10px; margin-bottom: 5px;">
            <tr><td height=3></td></tr>
            <tr>
                <td align="left" width="35%"  class="bareBold" style="padding-right: 15px;">Order Reference No.&nbsp;:</td>
                <td align=left>
                    <input class="gridInput" style="width:100%;" type=text name="pt_enccode" id="pt_enccode">
                </td>				
            </tr>
            <tr><td height=3></td></tr>
            <tr>
                <td align="left" width="35%"  class="bareBold" style="padding-right: 15px;">Request Date&nbsp;:</td>
                <td align=left>
                    <input class="gridInput" style="width:100%;" type=text name="pt_sodate" id="pt_sodate">
                </td>				
            </tr>
            <tr><td height=3></td></tr>
            <tr>
                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Patient ID&nbsp;:</td>
                <td align=left>
                    <input type="text" class="gridInput" style="width:100%;" name="pt_pid" id="pt_pid">
                </td>				
            </tr>
            <tr><td height=3></td></tr>
            <tr>
                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Patient Name&nbsp;:</td>
                <td align=left>
                    <input type="text" class="gridInput" style="width:100%;" name="pt_pname" id="pt_pname">
                </td>				
            </tr>
            <tr><td height=3></td></tr>

            <tr>
                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Gender&nbsp;:</td>
                <td align=left>
                    <input type="text" class="gridInput" style="width:100%;" name="pt_gender" id="pt_gender">
                </td>				
            </tr>
            <tr><td height=3></td></tr>
            <tr>
                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Birthdate&nbsp;:</td>
                <td align=left>
                    <input type="text" class="gridInput" style="width:100%;" name="pt_birthdate" id="pt_birthdate">
                </td>				
            </tr>
            <tr><td height=3></td></tr>
            <tr>
                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Age&nbsp;:</td>
                <td align=left>
                    <input type="text" class="gridInput" style="width:100%;" name="pt_age" id="pt_age">
                </td>				
            </tr>
            <tr><td height=3></td></tr>
            <tr>
                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Requesting Physician&nbsp;:</td>
                <td align=left>
                    <input type="text" class="gridInput" style="width:100%;" name="pt_physician" id="pt_physician">
                </td>				
            </tr>
            <tr><td height=3></td></tr>
        </table>
        <table width=100% cellspacing=0 cellpadding=0><tr><td width=100% class=gridHead align=center>SAMPLE DETAILS</td></tr></table>
        <table width=100% cellpadding=0 cellspacing=0 style="border: 1px solid #cdcdcd; padding: 10px;">
            <tr>
                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Test or Procedure&nbsp;:</td>
                <td align=left>
                    <input type="text" class="gridInput" style="width:100%;" name="pt_procedure" id="pt_procedure">
                </td>				
            </tr>
            <tr><td height=3></td></tr>
            <tr>
                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Procedure Code&nbsp;:</td>
                <td align=left>
                    <input type="text" class="gridInput" style="width:100%;" name="pt_code" id="pt_code">
                </td>				
            </tr>
            <tr><td height=3></td></tr>
            <tr>
                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Specimen Type&nbsp;:</td>
                <td align=left>
                    <select class="gridInput" style="width:100%; font-size:11px;" name="pt_spectype" id="pt_spectype">
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
                    <input type="text" class="gridInput" style="width:100%;" name="pt_serialno" id="pt_serialno">
                </td>				
            </tr>
            <tr><td height=3></td></tr>
            <tr>
                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Date Extracted&nbsp;:</td>
                <td align=left>
                    <input type="text" class="gridInput" style="width:100%;" name="pt_extractdate" id="pt_extractdate">
                </td>				
            </tr>
            <tr><td height=3></td></tr>
            <tr>
                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Time Extracted&nbsp;:</td>
                <td align=left>
        
                    <input type="text" class="gridInput" style="width:100%;" name="pt_extracttime" id="pt_extracttime" readonly>

                </td>				
            </tr>
            <tr><td height=3></td></tr>               
            <tr>
                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Extracted By&nbsp;:</td>
                <td align=left>
                    <input type="text" class="gridInput" style="width:100%;" name="pt_extractby" id="pt_extractby" readonly>
                </td>				
            </tr>
            <tr><td height=3></td></tr>
            <tr>
                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Location&nbsp;:</td>
                <td align=left>
                    <input type="text" class="gridInput" style="width:100%;" name="pt_location" id="pt_location" readonly>
                </td>				
            </tr>
        </table>                  
        <table width=100% cellspacing=0 cellpadding=0><tr><td width=100% class=gridHead align=center>RESULT DETAILS</td></tr></table>
        <table width=100% cellpadding=0 cellspacing=0 style="border: 1px solid #cdcdcd; padding: 10px;">
            <tr>
                <td align="left" width="35%"  class="bareBold" style="padding-right: 15px;">Result Date&nbsp;:</td>
                <td align=left>
                    <input class="gridInput" style="width:100%;font-size:11px;" type=text name="pt_date" id="pt_date" value="<?php echo date('m/d/Y'); ?>">
                </td>				
            </tr>
            <tr><td height=3></td></tr>
            <tr><td height=3></td></tr>
            <tr>
                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Result&nbsp;:</td>
                <td align=left>
                    <input class="gridInput" style="width:100%;" type=text name="pt_result" id="pt_result">
                </td>				
            </tr>
            <tr><td height=3></td></tr>
            <tr>
                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;" valign=top>Other Notable Remarks&nbsp;:</td>
                <td align=left>
                    <textarea name="pt_remarks" id="pt_remarks" style="width:100%;" rows=3></textarea>
                </td>				
            </tr>
        </table>
    </form>
</div>

<div id="bloodtypeResult" style="display: none;">
    <form name="frmBloodType" id="frmBloodType">  
    <table width=100% cellspacing=0 cellpadding=0><tr><td width=100% class=gridHead align=center>PATIENT & ORDER INFORMATION</td></tr></table>
        <table width=100% cellpadding=0 cellspacing=0 style="border: 1px solid #cdcdcd; padding: 10px; margin-bottom: 5px;">
            <tr><td height=3></td></tr>
            <tr>
                <td align="left" width="35%"  class="bareBold" style="padding-right: 15px;">Order Reference No.&nbsp;:</td>
                <td align=left>
                    <input class="gridInput" style="width:100%;" type=text name="btype_enccode" id="btype_enccode">
                </td>				
            </tr>
            <tr><td height=3></td></tr>
            <tr>
                <td align="left" width="35%"  class="bareBold" style="padding-right: 15px;">Order Reference Date&nbsp;:</td>
                <td align=left>
                    <input class="gridInput" style="width:100%;" type=text name="btype_sodate" id="btype_sodate">
                </td>				
            </tr>
            <tr><td height=3></td></tr>
            <tr>
                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Patient ID&nbsp;:</td>
                <td align=left>
                    <input type="text" class="gridInput" style="width:100%;" name="btype_pid" id="btype_pid">
                </td>				
            </tr>
            <tr><td height=3></td></tr>
            <tr>
                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Patient Name&nbsp;:</td>
                <td align=left>
                    <input type="text" class="gridInput" style="width:100%;" name="btype_pname" id="btype_pname">
                </td>				
            </tr>
            <tr><td height=3></td></tr>

            <tr>
                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Gender&nbsp;:</td>
                <td align=left>
                    <input type="text" class="gridInput" style="width:100%;" name="btype_gender" id="btype_gender">
                </td>				
            </tr>
            <tr><td height=3></td></tr>
            <tr>
                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Birthdate&nbsp;:</td>
                <td align=left>
                    <input type="text" class="gridInput" style="width:100%;" name="btype_birthdate" id="btype_birthdate">
                </td>				
            </tr>
            <tr><td height=3></td></tr>
            <tr>
                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Age&nbsp;:</td>
                <td align=left>
                    <input type="text" class="gridInput" style="width:100%;" name="btype_age" id="btype_age">
                </td>				
            </tr>
            <tr><td height=3></td></tr>
            <tr>
                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Requesting Physician&nbsp;:</td>
                <td align=left>
                    <input type="text" class="gridInput" style="width:100%;" name="btype_physician" id="btype_physician">
                </td>				
            </tr>
            <tr><td height=3></td></tr>
        </table>
        <table width=100% cellspacing=0 cellpadding=0><tr><td width=100% class=gridHead align=center>SAMPLE DETAILS</td></tr></table>
        <table width=100% cellpadding=0 cellspacing=0 style="border: 1px solid #cdcdcd; padding: 10px;">
            <tr>
                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Test or Procedure&nbsp;:</td>
                <td align=left>
                    <input type="text" class="gridInput" style="width:100%;" name="btype_procedure" id="btype_procedure">
                </td>				
            </tr>
            <tr><td height=3></td></tr>
            <tr>
                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Procedure Code&nbsp;:</td>
                <td align=left>
                    <input type="text" class="gridInput" style="width:100%;" name="btype_code" id="btype_code">
                </td>				
            </tr>
            <tr><td height=3></td></tr>
            <tr>
                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Specimen Type&nbsp;:</td>
                <td align=left>
                    <select class="gridInput" style="width:100%;font-size:11px;" name="btype_spectype" id="btype_spectype">
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
                    <input type="text" class="gridInput" style="width:100%;" name="btype_serialno" id="btype_serialno">
                </td>				
            </tr>
            <tr><td height=3></td></tr>
            <tr>
                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Date Extracted&nbsp;:</td>
                <td align=left>
                    <input type="text" class="gridInput" style="width:100%;" name="btype_extractdate" id="btype_extractdate">
                </td>				
            </tr>
            <tr><td height=3></td></tr>
            <tr>
                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Time Extracted&nbsp;:</td>
                <td align=left>
        
                    <input type="text" class="gridInput" style="width:100%;" name="btype_extracttime" id="btype_extracttime" readonly>

                </td>				
            </tr>
            <tr><td height=3></td></tr>
            <tr>
                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Extracted By&nbsp;:</td>
                <td align=left>
                    <input type="text" class="gridInput" style="width:100%;" name="btype_extractby" id="btype_extractby" readonly>
                </td>				
            </tr>
            <tr><td height=3></td></tr>
            <tr>
                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Collection Site&nbsp;:</td>
                <td align=left>
                  <input type="text" class="gridInput" style="width:100%;" name="btype_location" id="btype_location" readonly>
                </td>				
            </tr>
        </table>                  
        <table width=100% cellspacing=0 cellpadding=0><tr><td width=100% class=gridHead align=center>RESULT DETAILS</td></tr></table>
        <table width=100% cellpadding=0 cellspacing=0 style="border: 1px solid #cdcdcd; padding: 10px;">
            <tr>
                <td align="left" width="35%"  class="bareBold" style="padding-right: 15px;">Result Date&nbsp;:</td>
                <td align=left>
                    <input class="gridInput" style="width:100%;" type=text name="btype_date" id="btype_date" value="<?php echo date('m/d/Y'); ?>">
                </td>				
            </tr>
            <tr><td height=3></td></tr>
            <tr><td height=3></td></tr>
            <tr>
                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Blood Group&nbsp;:</td>
                <td align=left>
                    <select name="btype_result" id="btype_result" class="gridInput" style="width:100%;font-size:11px;">
                       <option value='A'>A</option>
                       <option vlaue='B'>B</option>
                       <option value='O'>O</option>
                       <option value='AB'>AB</option>
                    </select>
                </td>				
            </tr>
            <tr><td height=3></td></tr>
            <tr>
                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Rh Factor&nbsp;:</td>
                <td align=left>
                    <select name="btype_rh" id="btype_rh" class="gridInput" style="width:100%;font-size:11px;">
                      <option value='Positive'>Positive</option>
                      <option value='Negative'>Negative</option>
                    </select>
                </td>				
            </tr>
            <tr><td height=3></td></tr>
            <tr>
                <td align="left" width="35%" class="bareBold" valign=top>Other Notable Remarks&nbsp;:</td>
                <td align=left>
                    <textarea name="btype_remarks" id="btype_remarks" style="width:100%;" rows=3></textarea>
                </td>				
            </tr>
        </table>
    </form>
</div>

<div id="denguetest" style="display: none;">
    <form name="frmDengueDuo" id="frmDengueDuo">  
    <table width=100% cellspacing=0 cellpadding=0><tr><td width=100% class=gridHead align=center>PATIENT & ORDER INFORMATION</td></tr></table>
        <table width=100% cellpadding=0 cellspacing=0 style="border: 1px solid #cdcdcd; padding: 10px; margin-bottom: 5px;">
            <tr><td height=3></td></tr>
            <tr>
                <td align="left" width="35%"  class="bareBold" style="padding-right: 15px;">Order Reference No.&nbsp;:</td>
                <td align=left>
                    <input class="gridInput" style="width:100%;" type=text name="dengue_enccode" id="dengue_enccode">
                </td>				
            </tr>
            <tr><td height=3></td></tr>
            <tr>
                <td align="left" width="35%"  class="bareBold" style="padding-right: 15px;">Request Date&nbsp;:</td>
                <td align=left>
                    <input class="gridInput" style="width:100%;" type=text name="dengue_sodate" id="dengue_sodate">
                </td>				
            </tr>
            <tr><td height=3></td></tr>
            <tr>
                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Patient ID&nbsp;:</td>
                <td align=left>
                    <input type="text" class="gridInput" style="width:100%;" name="dengue_pid" id="dengue_pid">
                </td>				
            </tr>
            <tr><td height=3></td></tr>
            <tr>
                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Patient Name&nbsp;:</td>
                <td align=left>
                    <input type="text" class="gridInput" style="width:100%;" name="dengue_pname" id="dengue_pname">
                </td>				
            </tr>
            <tr><td height=3></td></tr>

            <tr>
                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Gender&nbsp;:</td>
                <td align=left>
                    <input type="text" class="gridInput" style="width:100%;" name="dengue_gender" id="dengue_gender">
                </td>				
            </tr>
            <tr><td height=3></td></tr>
            <tr>
                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Birthdate&nbsp;:</td>
                <td align=left>
                    <input type="text" class="gridInput" style="width:100%;" name="dengue_birthdate" id="dengue_birthdate">
                </td>				
            </tr>
            <tr><td height=3></td></tr>
            <tr>
                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Age&nbsp;:</td>
                <td align=left>
                    <input type="text" class="gridInput" style="width:100%;" name="dengue_age" id="dengue_age">
                </td>				
            </tr>
            <tr><td height=3></td></tr>
            <tr>
                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Requesting Physician&nbsp;:</td>
                <td align=left>
                    <input type="text" class="gridInput" style="width:100%;" name="dengue_physician" id="dengue_physician">
                </td>				
            </tr>
            <tr><td height=3></td></tr>
        </table>
        <table width=100% cellspacing=0 cellpadding=0><tr><td width=100% class=gridHead align=center>SAMPLE DETAILS</td></tr></table>
        <table width=100% cellpadding=0 cellspacing=0 style="border: 1px solid #cdcdcd; padding: 10px;">
            <tr>
                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Test or Procedure&nbsp;:</td>
                <td align=left>
                    <input type="text" class="gridInput" style="width:100%;" name="dengue_procedure" id="dengue_procedure">
                </td>				
            </tr>
            <tr><td height=3></td></tr>
            <tr>
                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Procedure Code&nbsp;:</td>
                <td align=left>
                    <input type="text" class="gridInput" style="width:100%;" name="dengue_code" id="dengue_code">
                </td>				
            </tr>
            <tr><td height=3></td></tr>
            <tr>
                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Specimen Type&nbsp;:</td>
                <td align=left>
                    <select class="gridInput" style="width:100%; font-size:11px;" name="dengue_spectype" id="dengue_spectype">
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
                    <input type="text" class="gridInput" style="width:100%;" name="dengue_serialno" id="dengue_serialno">
                </td>				
            </tr>
            <tr><td height=3></td></tr>
            <tr>
                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Date Extracted&nbsp;:</td>
                <td align=left>
                    <input type="text" class="gridInput" style="width:100%;" name="dengue_extractdate" id="dengue_extractdate">
                </td>				
            </tr>
            <tr><td height=3></td></tr>
            <tr>
                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Time Extracted&nbsp;:</td>
                <td align=left>
        
                    <input type="text" class="gridInput" style="width:100%;" name="dengue_extracttime" id="dengue_extracttime" readonly>

                </td>				
            </tr>
            <tr><td height=3></td></tr>               
            <tr>
                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Extracted By&nbsp;:</td>
                <td align=left>
                    <input type="text" class="gridInput" style="width:100%;" name="dengue_extractby" id="dengue_extractby" readonly>
                </td>				
            </tr>
            <tr><td height=3></td></tr>
            <tr>
                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Location&nbsp;:</td>
                <td align=left>
                    <input type="text" class="gridInput" style="width:100%;" name="dengue_location" id="dengue_location" readonly>
                </td>				
            </tr>
        </table>                  
        <table width=100% cellspacing=0 cellpadding=0><tr><td width=100% class=gridHead align=center>RESULT DETAILS</td></tr></table>
        <table width=100% cellpadding=0 cellspacing=0 style="border: 1px solid #cdcdcd; padding: 10px;">
            <tr>
                <td align="left" width="35%"  class="bareBold" style="padding-right: 15px;">Result Date&nbsp;:</td>
                <td align=left>
                    <input class="gridInput" style="width:100%;font-size:11px;" type=text name="dengue_date" id="dengue_date" value="<?php echo date('m/d/Y'); ?>">
                </td>				
            </tr>
            <tr><td height=3></td></tr>
            <tr><td height=3></td></tr>
            <tr>
                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Dengue NS1 Antigen&nbsp;:</td>
                <td align=left>
                    <input class="gridInput" style="width:100%;" type=text name="dengue_result" id="dengue_result">
                </td>				
            </tr>
            <tr><td height=3></td></tr>
            <tr>
                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Dengue IgG Antibody&nbsp;:</td>
                <td align=left>
                    <input class="gridInput" style="width:100%;" type=text name="dengue_result2" id="dengue_result2">
                </td>				
            </tr>
            <tr><td height=3></td></tr>
            <tr>
                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Dengue IgM Antibody&nbsp;:</td>
                <td align=left>
                    <input class="gridInput" style="width:100%;" type=text name="dengue_result3" id="dengue_result3">
                </td>				
            </tr>
            <tr><td height=3></td></tr>
            <tr>
                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;" valign=top>Other Notable Remarks&nbsp;:</td>
                <td align=left>
                    <textarea name="dengue_remarks" id="dengue_remarks" style="width:100%;" rows=3></textarea>
                </td>				
            </tr>
        </table>
    </form>
</div>

<div id="typhoid" style="display: none;">
    <form name="frmTyphoid" id="frmTyphoid">
    <table width=100% cellpadding=0 cellspacing=0 valign=top>
        <tr>
            <td width=45% valign=top>  
                <table width=100% cellspacing=0 cellpadding=0><tr><td width=100% class=gridHead align=center>PATIENT & ORDER INFORMATION</td></tr></table>
                <table width=100% cellpadding=0 cellspacing=0 style="border: 1px solid #cdcdcd; padding: 10px; margin-bottom: 5px;">
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%"  class="bareBold" style="padding-right: 15px;">Order Reference No.&nbsp;:</td>
                        <td align=left>
                            <input class="gridInput" style="width:100%;" type=text name="typhoid_enccode" id="typhoid_enccode">
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%"  class="bareBold" style="padding-right: 15px;">Request Date&nbsp;:</td>
                        <td align=left>
                            <input class="gridInput" style="width:100%;" type=text name="typhoid_sodate" id="typhoid_sodate">
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Patient ID&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="typhoid_pid" id="typhoid_pid">
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Patient Name&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="typhoid_pname" id="typhoid_pname">
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>

                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Gender&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="typhoid_gender" id="typhoid_gender">
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Birthdate&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="typhoid_birthdate" id="typhoid_birthdate">
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Age&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="typhoid_age" id="typhoid_age">
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Requesting Physician&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="typhoid_physician" id="typhoid_physician">
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                </table>
                <table width=100% cellspacing=0 cellpadding=0><tr><td width=100% class=gridHead align=center>SAMPLE DETAILS</td></tr></table>
                <table width=100% cellpadding=0 cellspacing=0 style="border: 1px solid #cdcdcd; padding: 10px;">
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Test or Procedure&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="typhoid_procedure" id="typhoid_procedure">
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Procedure Code&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="typhoid_code" id="typhoid_code">
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Specimen Type&nbsp;:</td>
                        <td align=left>
                            <select class="gridInput" style="width:100%; font-size:11px;" name="typhoid_spectype" id="typhoid_spectype">
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
                            <input type="text" class="gridInput" style="width:100%;" name="typhoid_serialno" id="typhoid_serialno">
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Date Extracted&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="typhoid_extractdate" id="typhoid_extractdate">
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Time Extracted&nbsp;:</td>
                        <td align=left>
                
                            <input type="text" class="gridInput" style="width:100%;" name="typhoid_extracttime" id="typhoid_extracttime" readonly>

                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>               
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Extracted By&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="typhoid_extractby" id="typhoid_extractby" readonly>
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Location&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="typhoid_location" id="typhoid_location" readonly>
                        </td>				
                    </tr>
            </table>
        </td> 
        <td width=1%>&nbsp;</td>
        <td width=64% valign=top >                
            <table width=100% cellspacing=0 cellpadding=0><tr><td width=100% class=gridHead align=center>RESULT DETAILS</td></tr></table>
            <table width=100% cellpadding=0 cellspacing=3 class="td_content">
             <tr>
                <td align="left" width="35%"  class="bareBold" style="padding-right: 15px;">Result Date&nbsp;:</td>
                <td align=left>
                    <input class="gridInput" style="width:100%;font-size:11px;" type=text name="typhoid_date" id="typhoid_date" value="<?php echo date('m/d/Y'); ?>">
                </td>				
            </tr>
            <tr><td height=2></td></tr>
            <tr>
                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">IgM Anti-S. Typhi/Paratyphi&nbsp;:</td>
                <td align=left>
                    <input class="gridInput" style="width:100%; font-size: 11px;" type=text name="typhoid_igm" id="typhoid_igm">
                </td>				
            </tr>
            <tr><td height=2></td></tr>
            <tr>
                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">IgG Anti-S. Typhi/Paratyphi&nbsp;:</td>
                <td align=left>
                    <input class="gridInput" style="width:100%; font-size: 11px;" type=text name="typhoid_igg" id="typhoid_igg">
                </td>				
            </tr>
            <tr><td height=2></td></tr>
            <tr>
                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;" valign=top>Other Notable Remarks&nbsp;:</td>
                <td align=left>
                    <textarea name="typhoid_remarks" id="typhoid_remarks" style="width:100%;" rows=3></textarea>
                </td>				
            </tr>
            </table>
            </td>
            </tr>
        </table>
    </form>
</div>

<div id="prothrombin" style="display: none;">
    <form name="frmProthrombin" id="frmProthrombin">  
    <table width=100% cellspacing=0 cellpadding=0><tr><td width=100% class=gridHead align=center>PATIENT & ORDER INFORMATION</td></tr></table>
        <table width=100% cellpadding=0 cellspacing=0 style="border: 1px solid #cdcdcd; padding: 10px; margin-bottom: 5px;">
            <tr><td height=3></td></tr>
            <tr>
                <td align="left" width="35%"  class="bareBold" style="padding-right: 15px;">Order Reference No.&nbsp;:</td>
                <td align=left>
                    <input class="gridInput" style="width:100%;" type=text name="prothrombin_enccode" id="prothrombin_enccode">
                </td>				
            </tr>
            <tr><td height=3></td></tr>
            <tr>
                <td align="left" width="35%"  class="bareBold" style="padding-right: 15px;">Request Date&nbsp;:</td>
                <td align=left>
                    <input class="gridInput" style="width:100%;" type=text name="prothrombin_sodate" id="prothrombin_sodate">
                </td>				
            </tr>
            <tr><td height=3></td></tr>
            <tr>
                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Patient ID&nbsp;:</td>
                <td align=left>
                    <input type="text" class="gridInput" style="width:100%;" name="prothrombin_pid" id="prothrombin_pid">
                </td>				
            </tr>
            <tr><td height=3></td></tr>
            <tr>
                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Patient Name&nbsp;:</td>
                <td align=left>
                    <input type="text" class="gridInput" style="width:100%;" name="prothrombin_pname" id="prothrombin_pname">
                </td>				
            </tr>
            <tr><td height=3></td></tr>

            <tr>
                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Gender&nbsp;:</td>
                <td align=left>
                    <input type="text" class="gridInput" style="width:100%;" name="prothrombin_gender" id="prothrombin_gender">
                </td>				
            </tr>
            <tr><td height=3></td></tr>
            <tr>
                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Birthdate&nbsp;:</td>
                <td align=left>
                    <input type="text" class="gridInput" style="width:100%;" name="prothrombin_birthdate" id="prothrombin_birthdate">
                </td>				
            </tr>
            <tr><td height=3></td></tr>
            <tr>
                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Age&nbsp;:</td>
                <td align=left>
                    <input type="text" class="gridInput" style="width:100%;" name="prothrombin_age" id="prothrombin_age">
                </td>				
            </tr>
            <tr><td height=3></td></tr>
            <tr>
                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Requesting Physician&nbsp;:</td>
                <td align=left>
                    <input type="text" class="gridInput" style="width:100%;" name="prothrombin_physician" id="prothrombin_physician">
                </td>				
            </tr>
            <tr><td height=3></td></tr>
        </table>
        <table width=100% cellspacing=0 cellpadding=0><tr><td width=100% class=gridHead align=center>SAMPLE DETAILS</td></tr></table>
        <table width=100% cellpadding=0 cellspacing=0 style="border: 1px solid #cdcdcd; padding: 10px;">
            <tr>
                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Test or Procedure&nbsp;:</td>
                <td align=left>
                    <input type="text" class="gridInput" style="width:100%;" name="prothrombin_procedure" id="prothrombin_procedure">
                </td>				
            </tr>
            <tr><td height=3></td></tr>
            <tr>
                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Procedure Code&nbsp;:</td>
                <td align=left>
                    <input type="text" class="gridInput" style="width:100%;" name="prothrombin_code" id="prothrombin_code">
                </td>				
            </tr>
            <tr><td height=3></td></tr>
            <tr>
                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Specimen Type&nbsp;:</td>
                <td align=left>
                    <select class="gridInput" style="width:100%; font-size:11px;" name="prothrombin_spectype" id="prothrombin_spectype">
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
                    <input type="text" class="gridInput" style="width:100%;" name="prothrombin_serialno" id="prothrombin_serialno">
                </td>				
            </tr>
            <tr><td height=3></td></tr>
            <tr>
                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Date Extracted&nbsp;:</td>
                <td align=left>
                    <input type="text" class="gridInput" style="width:100%;" name="prothrombin_extractdate" id="prothrombin_extractdate">
                </td>				
            </tr>
            <tr><td height=3></td></tr>
            <tr>
                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Time Extracted&nbsp;:</td>
                <td align=left>
        
                    <input type="text" class="gridInput" style="width:100%;" name="prothrombin_extracttime" id="prothrombin_extracttime" readonly>

                </td>				
            </tr>
            <tr><td height=3></td></tr>               
            <tr>
                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Extracted By&nbsp;:</td>
                <td align=left>
                    <input type="text" class="gridInput" style="width:100%;" name="prothrombin_extractby" id="prothrombin_extractby" readonly>
                </td>				
            </tr>
            <tr><td height=3></td></tr>
            <tr>
                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Location&nbsp;:</td>
                <td align=left>
                    <input type="text" class="gridInput" style="width:100%;" name="prothrombin_location" id="prothrombin_location" readonly>
                </td>				
            </tr>
        </table>                  
        <table width=100% cellspacing=0 cellpadding=0><tr><td width=100% class=gridHead align=center>RESULT DETAILS</td></tr></table>
        <table width=100% cellpadding=0 cellspacing=0 style="border: 1px solid #cdcdcd; padding: 10px;">
            <tr>
                <td align="left" width="35%"  class="bareBold" style="padding-right: 15px;">Result Date&nbsp;:</td>
                <td align=left>
                    <input class="gridInput" style="width:100%;font-size:11px;" type=text name="prothrombin_date" id="prothrombin_date" value="<?php echo date('m/d/Y'); ?>">
                </td>				
            </tr>
            <tr><td height=3></td></tr>
            <tr><td height=3></td></tr>
            <tr>
                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Seconds&nbsp;:</td>
                <td align=left>
                    <input class="gridInput" style="width:100%; font-size: 11px;" type=number name="prothrombin_seconds" id="prothrombin_seconds">
                </td>				
            </tr>
            <tr><td height=3></td></tr>
            <tr>
                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">%&nbsp;:</td>
                <td align=left>
                    <input class="gridInput" style="width:100%; font-size: 11px;" type=number name="prothrombin_percent" id="prothrombin_percent">
                </td>				
            </tr>
            <tr><td height=3></td></tr>
            <tr>
                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">INR&nbsp;:</td>
                <td align=left>
                    <input class="gridInput" style="width:100%; font-size: 11px;" type=number name="prothrombin_inr" id="prothrombin_inr">
                </td>				
            </tr>
            <tr><td height=3></td></tr>
            <tr>
                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;" valign=top>Other Notable Remarks&nbsp;:</td>
                <td align=left>
                    <textarea name="prothrombin_remarks" id="prothrombin_remarks" style="width:100%;" rows=3></textarea>
                </td>				
            </tr>
        </table>
    </form>
</div>

<div id="sgwsummary" style="display:none;">	
	<table border="0" cellpadding="0" cellspacing="0" width=100% class="td_content" style="padding: 10px;">
		<tr>
			<td width=35%><span class="spandix-l">Type :</span></td>
			<td>
				<select id="sgw_type" style="width: 80%; font-size: 11px;" class="gridInput" />
					<option value="">- All Types -</option>
					<?php
						$tQuery = $o->dbquery("select id, `type` from options_wtype order by `type`;");
						while($tRow = $tQuery->fetch_array()) {
							echo "<option value='$tRow[0]' ";
							if($res['ref_type'] == $tRow[0]) { echo "selected"; }
							echo ">$tRow[1]</option>";
						}
					?>
				</select>
			</td>
		</tr>
		<tr><td height=4></td></tr>
		<tr>
			<td width=35%><span class="spandix-l">Covered Period :</span></td>
			<td>
				<input type="text" id="sgw_dtf" class="gridInput" style="width: 80%;" value="<?php echo date('m/01/Y'); ?>" />
			</td>
		</tr>
		<tr><td height=4></td></tr>
		<tr>
			<td width=35%><span class="spandix-l"></span></td>
			<td>
				<input type="text" id="sgw_dt2" class="gridInput" style="width: 80%;" value="<?php echo date('m/d/Y'); ?>" />
			</td>
		</tr>
		<tr><td height=4></td></tr>
		<tr><td colspan=2><hr></hr></td></tr>
		<tr>
			<td align=center colspan=2>
				<button onClick="generateSGW();" class="buttonding" style="font-size: 11px;"><img src="images/icons/pdf.png" width=18 height=18 align=absmiddle />&nbsp;&nbsp;Generate Report</button>
			</td>
		</tr>
	</table>
</div>

<div id="inventorybook" style="display:none;">	
	<table border="0" cellpadding="0" cellspacing="0" width=100% class="td_content" style="padding: 10px;">
		<tr>
			<td width=35%><span class="spandix-l">Type :</span></td>
			<td>
				<select id="ibook_group" style="width: 90%; font-size: 11px;" class="gridInput">
				<option value="">- All Inventory Items -</option>
				<?php
					$iut = $o->dbquery("select `mid`,mgroup from options_mgroup where `mid` in (3,4);");
					while(list($t,$tt) = $iut->fetch_array()) {
						echo "<option value='$t'>$tt</option>";
					}
				?>
				</select>
			</td>
		</tr>
		<tr><td height=4></td></tr>
		<tr>
			<td width=35%><span class="spandix-l">Covered Period :</span></td>
			<td>
				<input type="text" id="ibook_dtf" class="gridInput" style="width: 90%;" value="<?php echo date('m/01/Y'); ?>" />
			</td>
		</tr>
		<tr><td height=4></td></tr>
		<tr>
			<td width=35%><span class="spandix-l"></span></td>
			<td>
				<input type="text" id="ibook_dt2" class="gridInput" style="width: 90%;" value="<?php echo date('m/d/Y'); ?>" />
			</td>
		</tr>
		<tr><td height=4></td></tr>
		<tr><td colspan=2><hr></hr></td></tr>
		<tr>
			<td align=center colspan=2>
				<button onClick="processInventory();" class="buttonding" style="font-size: 11px;"><img src="images/icons/processraw.png" width=18 height=18 align=absmiddle />&nbsp;&nbsp;View Inventory</button>
				<button onClick="exportInventoryNow();" class="buttonding" style="font-size: 11px;"><img src="images/icons/excel.png" width=18 height=18 align=absmiddle />&nbsp;&nbsp;Export to Excel</button>
			</td>
		</tr>
	</table>
</div>

<div id="censusReport" name="censusReport" style="display: none;">
    <table width=100% cellpadding=2 cellspacing=1>
         <tr>
			<td width=35%><span class="spandix-l">Report Type :</span></td>
			<td>
				<select name="census_type" id="census_type" class="gridInput" style="width: 90%; font-size: 11px;">
                    <option value='1'>Summary</option>
                    <option value='2'>Detailed</option>
                </select>
			</td>
		</tr>
        <tr>
			<td width=35%><span class="spandix-l">Category :</span></td>
			<td>
				<select name="census_category" id="census_category" class="gridInput" style="width: 90%; font-size: 11px;">
                    <option value=''>- All -</option>
                    <?php
                        $ccatQuery = $o->dbquery("SELECT DISTINCT a.subcategory AS subcatid, b.subcategory AS subcatname FROM services_master a LEFT JOIN options_servicesubcat b ON a.subcategory = b.id WHERE a.subcategory != 0 order by b.subcategory;");
                        while($ccatRow = $ccatQuery->fetch_array()) {
                            echo "<option value='$ccatRow[0]'>$ccatRow[1]</option>";
                        }
                    ?>
                </select>
			</td>
		</tr>
		<tr>
			<td width=35%><span class="spandix-l">Covered Period</span></td>
			<td>
				<input type="text" name="census_dtf" id="census_dtf" class="gridInput" style="width: 90%;" value="<?php echo date('m/01/Y'); ?>" />
			</td>
		</tr>          
        <tr>
        <td width=35%></td>
			<td>
				<input type="text" name="census_dt2" id="census_dt2" class="gridInput" style="width: 90%;" value="<?php echo date('m/d/Y'); ?>" />
			</td>       
        </tr>
    </table>
</div>

<div id="validationList" name="validationList" style="display: none;"></div>
<div id="descResult" name="descResult" style="display: none;"></div>
<div id="cbcResult" name="cbcResult" style="display: none;"></div>
<div id="bloodChemResult" name="bloodChemResult" style="display: none;"></div>
<div id="specialChemistryResult" name="specialChemistryResult" style="display: none;"></div>
<div id="coagulationResult" name="coagulationResult" style="display: none;"></div>
<div id="uaResult" name="uaResult" style="display: none;"></div>
<div id="stoolResult" name="stoolResult" style="display: none;"></div>
<div id="semAnalReport" name="semAnalReport" style="display: none;"></div>
<div id="barcode" name="barcode" style="display: none;"></div>
<div id="crossMatching" name="crossMatching" style="display: none;"></div>
<div id="abgResult" name="abgResult" style="display: none;"></div>
<div id="ogttResult" name="ogttResult" style="display: none;"></div>

