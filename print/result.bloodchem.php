<?php
	session_start();
	include("../lib/mpdf6/mpdf.php");
	include("../handlers/_generics.php");
    //ini_set("display_errors","On");

	$con = new _init;

/* MYSQL QUERIES SECTION */
    // $datePrinted = date("m/d/Y");
    // $timePrinted = date('h:i:s a');

    $order = $con->getArray("select *, date_format(extractdate,'%m/%d/%Y') as exdate, date_format(release_date,'%m/%d/%Y') as rdate, dotime from lab_samples where enccode = '$_REQUEST[enccode]' and serialno = '$_REQUEST[serialno]' limit 1;");
    $a = $con->getArray("SELECT docointkey, a.enccode, SUBSTR(enccode,8,15) AS hmrno, DATE_FORMAT(dodate,'%m/%d/%Y %h:%i %p') AS orderdate, DATE_FORMAT(dodate,'%Y-%m-%d') AS xorderdate, a.hpercode, URLENCODE(concat(c.patlast,', ', c.patfirst,' ', c.patmiddle)) as pname,  DATE_FORMAT(c.patbdate,'%m/%d/%Y') AS bday, DATE_FORMAT(c.patbdate,'%Y-%m-%d') AS xbday, IF(c.patsex='F','FEMALE','MALE') AS sex, c.patsex as xgender, a.proccode, b.procdesc, a.donotes AS remarks, a.licno, a.estatus, entby FROM hospital_dbo.hdocord a LEFT JOIN hospital_dbo.hprocm b ON a.proccode = b.proccode LEFT JOIN hospital_dbo.hperson c ON a.hpercode = c.hpercode WHERE a.enccode = '$order[enccode]' and a.dotime = '$order[dotime]';");
    $b = $con->getArray("select *, verified_by from lab_bloodchem where enccode = '$a[enccode]' and serialno = '$order[serialno]';");

    list($datePrinted, $timePrinted) = $con->getArray("select date_format(printed_on, '%m/%d/%Y') as d8print, date_format(printed_on, '%h:%i:%s %p') as timeprint from lab_samples where enccode = '$_REQUEST[enccode]' and serialno = '$_REQUEST[serialno]';");


    $con->calculateAge($a['xorderdate'],$a['xbday']);
	list($pateintAddress) = $con->getArray("SELECT URLENCODE(CONCAT(a.patstr,', ',b.bgyname,', ',c.ctyname,', ',d.provname)) AS addr FROM hospital_dbo.haddr a LEFT JOIN hospital_dbo.hbrgy b ON a.brg = b.bgycode LEFT JOIN hospital_dbo.hcity c ON a.ctycode = c.ctycode LEFT JOIN hospital_dbo.hprov d ON a.provcode = d.provcode WHERE a.hpercode = '$a[hmrno]';");
   
    //list($room) = $con->getArray("SELECT concat(b.wardcode,'-',rmname,'-',bdname) FROM ppp_danao.lab_samples a LEFT JOIN hospital_dbo.hpatroom b ON a.enccode = b.enccode LEFT JOIN hospital_dbo.hroom c ON b.rmintkey = c.rmintkey LEFT JOIN hospital_dbo.hbed d ON b.bdintkey = d.bdintkey WHERE a.enccode = '$a[enccode]' limit 1;");
    
    $room = $con->identRoom($a['enccode']);
    if($room == '') { 
        list($room) = $con->getArray("select toecode from hospital_dbo.henctr where enccode = '$order[enccode]';");
    }

    list($encSignature,$encBy,$encByLicense,$encByRole) = $con->getArray("SELECT if(signature_file != '',concat('<img src=\"../images/signatures/',signature_file,'\" align=absmiddle />'),'<img src=\"../images/signatures/blank.png\" align=absmiddle />') as signature, fullname, license_no, role from user_info where emp_id = '$b[created_by]';");

    if($b['verified_by'] != '') {
        list($cbySignature,$cby,$cbyLicense,$cbyRole) = $con->getArray("SELECT if(signature_file != '',concat('<img src=\"../images/signatures/',signature_file,'\" align=absmiddle />'),'<img src=\"../images/signatures/blank.png\" align=absmiddle />') as signature, fullname, license_no, role from user_info where emp_id = '$b[verified_by]';");
    }

/* END OF SQL QUERIES */
function checkTest($code,$serialno) {
    if(in_array($code,$_REQUEST['othercodes'])) { return true; } else { return false; }
}


$mpdf=new mPDF('win-1252','letter','','',10,10,90,30,10,10);
$mpdf->use_embeddedfonts_1252 = true;    // false is default
$mpdf->SetProtection(array('print'));
$mpdf->SetAuthor("PORT80 Solutions");

if($b['verified'] != 'Y') {
	$mpdf->SetWatermarkText('FOR VALIDATION');
	$mpdf->showWatermarkText = true;
} else {
	$mpdf->SetWatermarkImage ('../images/logosmall.png',0.2,'F','P');
	$mpdf->showWatermarkImage = true;
}

$mpdf->SetDisplayMode(50);

$html = '
<html>
<head>
	<style>
		body {font-family: sans-serif; font-size: 9pt; }
        .itemHeader {
            padding:5px;border:1px solid black; text-align: center; font-weight: bold;
        }

        .itemResult {
            padding:20px;border:1px solid black;text-align: center;
        }

        #items td { border: 1px solid; text-align: center; }
	</style>
</head>
<body>

<!--mpdf
<htmlpageheader name="myheader">
<table width="100%" cellpadding=0 cellspaing=0>
	<tr><td align=center><img src="../images/doc-header.jpg" /></td></tr>
</table>
<table width=100% cellpadding=2 cellspacing=0 style="font-size: 8pt;margin-top:1px;">
    <tr>
        <td width=22%><b>Patient\'s Name :</b></td>
        <td width=40% style="border-bottom: 1px solid black; font-weight: bold;">'.urldecode($a['pname']).'</td>
        <td width=17%><b>Hospital No.:</b></td>
        <td style="border-bottom: 1px solid black; font-weight: bold;">'.$a['hmrno'].'</td>
    </tr>
    <tr>
        <td><b>Address :</b></td>
        <td style="border-bottom: 1px solid black; font-weight: bold;">'.urldecode($pateintAddress).'</td>
        <td><b>Specimen ID :</b></td>
        <td style="border-bottom: 1px solid black; font-weight: bold;">'.$order['serialno'].'</td>
    </tr>
    <tr>
        <td><b>Age/Sex :</b></td>
        <td style="border-bottom: 1px solid black; font-weight: bold;">' . $con->ageDisplay . "/" . $a['sex'] . '</td>
        <td><b>Date Requested :</b></td>
        <td style="border-bottom: 1px solid black; font-weight: bold;">'.$order['exdate'].'</td>
    </tr>
    <tr>
        <td><b>Ward/Room No. :</b></td>
        <td style="border-bottom: 1px solid black; font-weight: bold;">'.$room.'</td>
        <td><b>Date Printed :</b></td>
        <td style="border-bottom: 1px solid black; font-weight: bold;">'.$datePrinted.'</td>
    </tr>
    <tr>
        <td><b>Requesting Physician :</b></td>
        <td style="border-bottom: 1px solid black; font-weight: bold;">'.$order['physician'].'</td>
        <td><b>Time Printed :</b></td>
        <td style="border-bottom: 1px solid black; font-weight: bold;">'.$timePrinted.'</td>
    </tr>
    <tr>
		<td colspan=4 width="100%" style="padding-top: 30px;" align=center>
			<span style="font-weight: bold; font-size: 12pt; color: #000000;">CLINICAL CHEMISTRY</span>
		</td>
	</tr>
    <tr>
		<td width=100% colspan=4 style="background-color: #8db4e2; border-top: 1px solid black; padding: 5px; font-size: 11pt;" align=center><b>CLINICAL CHEMISTRY</b>&nbsp;</td>
	</tr>    
</table>
</htmlpageheader>

<htmlpagefooter name="myfooter">
    <table width=100% cellpadding=5 style="margin-bottom: 5px; font-size: 8pt;">
        <tr>
            <td width=33% align=center>'.$encSignature.'<br/><b>'.$encBy.'<br/>_______________________________________<br/><span style="font-size: 8pt;">PRC LICENSE NO. '.$encByLicense.'</span><br/><b>REPORTED BY</b></td>
            <td width=33% align=center>'.$cbySignature.'<br/><b>'.$cby.'<br/>_______________________________________<br/><span style="font-size: 8pt;">PRC LICENSE NO. '.$cbyLicense.'</span><br/><b>VALIDATED BY</b></td>
            <td align=center valign=top><img src="../images/signatures/psa-signature.png" align=absmidddle /><br/><b>PETER S. AZNAR, M.D, F.P.S.P<br/>_______________________________________<br><span style="font-size: 8pt;">PRC LICENSE NO. 72410</span><br/><b>PATHOLOGIST</b></td>
        </tr>
    </table>
</htmlpagefooter>

<sethtmlpageheader name="myheader" value="on" show-this-page="1" />
<sethtmlpagefooter name="myfooter" value="on" />
mpdf-->

<table width=90% cellpadding=0 cellspacing=0 align=center>
<tr>
    <td align="left" width=25% style="border-bottom: 1px solid black;"><b>TEST</b></td>
    <td align=center width=25% style="border-bottom: 1px solid black;"><b>RESULT</b></td>
    <td align=center width=25% style="border-bottom: 1px solid black;"><b>FLAG</b></td>
    <td align=center width=25% style="padding-left: 15px; border-bottom: 1px solid black;"><b>REFERENCE VALUES</b></td>	
</tr>
<tr><td colspan=4 height=5>&nbsp;</td></tr>';

if(checkTest('L113',$order['serialno']) && $b['glucose'] > 0) {

   /*  if($b['glucose'] <= 99) { $attr = "NORMAL"; }
    if($b['glucose'] > 100 && $b['glucose'] < 126) { $attr = "PREDIABETIC"; }
    if($b['glucose']> 125) { $attr = "DIABETIC"; } */

    $html .= '<tr>
        <td align="left">Glucose/FBS</td>
        <td align=center style="border-bottom: 1px solid black;vertical-align: top;">'. $b['glucose'] . '</td>
        <td align=center>'.$con->checkChemValues2($con->age,$a['xgender'],'L113',$b['glucose'],$order['machine']).'</td>
        <td align=center valign=top>'.$con->getAttribute2('L113',$con->age,$a['xgender'],$order['machine']).'</td>	
    </tr>
    <tr><td colspan=4 height=5>&nbsp;</td></tr>';

}

if(checkTest('L132',$order['serialno']) && $b['rbs'] > 0 || checkTest('L110',$order['serialno'])) {

    /*  if($b['glucose'] <= 99) { $attr = "NORMAL"; }
     if($b['glucose'] > 100 && $b['glucose'] < 126) { $attr = "PREDIABETIC"; }
     if($b['glucose']> 125) { $attr = "DIABETIC"; } */
 
     $html .= '<tr>
         <td align="left">Glucose Random (RBS)</td>
         <td align=center style="border-bottom: 1px solid black;vertical-align: top;">'. $b['rbs'] . '</td>
         <td align=center>'.$con->checkChemValues2($con->age,$a['xgender'],'L132',$b['rbs'],$order['machine']).'</td>
         <td align=center valign=top>'.$con->getAttribute2('L132',$con->age,$a['xgender'],$order['machine']).'</td>	
     </tr>
     <tr><td colspan=4 height=5>&nbsp;</td></tr>';
 
 }

if(checkTest('L004',$order['serialno']) && $b['uric'] > 0) {
    $html .= '<tr>
        <td align="left" valign=top>Blood Uric Acid</td>
        <td align=center style="border-bottom: 1px solid black;vertical-align: top;">'. $b['uric'] . '</td>
        <td align=center>'.$con->checkChemValues2($con->age,$a['xgender'],'L004',$b['uric'],$order['machine']).'</td>
        <td align=center valign=top>'.$con->getAttribute2('L004',$con->age,$a['xgender'],$order['machine']).'</td>	
    </tr>
    <tr><td colspan=4 height=5>&nbsp;</td></tr>
    ';
}

if(checkTest('L021',$order['serialno']) && $b['cbg'] > 0) {
    $html .= '<tr>
        <td align="left" valign=top>CBG</td>
        <td align=center style="border-bottom: 1px solid black;vertical-align: top;">'. $b['cbg'] . '</td>
        <td align=center>'.$con->checkChemValues2($con->age,$a['xgender'],'L021',$b['cbg'],$order['machine']).'</td>
        <td align=center valign=top>'.$con->getAttribute2('L021',$con->age,$a['xgender'],$order['machine']).'</td>	
    </tr>
    <tr><td colspan=4 height=5>&nbsp;</td></tr>
    ';
}

if(checkTest('L005',$order['serialno']) && $b['bun'] != '0') {
    $html .= '<tr>
        <td align="left">Blood Urea Nitrogen (BUN)</td>
        <td align=center style="border-bottom: 1px solid black;">'. $b['bun'] . '</td>
        <td align=center>'.$con->checkChemValues2($con->age,$a['xgender'],'L005',trim($b['bun'],'<'),$order['machine']).'</td>
        <td align=center>'.$con->getAttribute2('L005',$con->age,$a['xgender'],$order['machine']).'</td>	
    </tr>
    <tr><td colspan=4 height=5>&nbsp;</td></tr>';
}

if(checkTest('L020',$order['serialno']) && $b['creatinine'] > 0) {
    $html .= '<tr>
        <td align="left">Creatinine</td>
        <td align=center style="border-bottom: 1px solid black;">'. $b['creatinine'] . '</td>
        <td align=center>'.$con->checkChemValues2($con->age,$a['xgender'],'L020',$b['creatinine'],$order['machine']).'</td>
        <td align=center>'.$con->getAttribute2('L020',$con->age,$a['xgender'],$order['machine']).'</td>	
    </tr>
    <tr><td colspan=4 height=5>&nbsp;</td></tr>';
}

if(checkTest('L022',$order['serialno']) && $b['hba1c'] > 0) {
    $html .= '<tr>
        <td align="left">HbA1c</td>
        <td align=center style="border-bottom: 1px solid black;">'. $b['hba1c'] . '</td>
        <td align=center>'.$con->checkChemValues2($con->age,$a['xgender'],'L022',trim($b['hba1c'],'>'),$order['machine']).'</td>
        <td align=center>'.$con->getAttribute2('L022',$con->age,$a['xgender'],$order['machine']).'</td>	
    </tr>
    <tr><td colspan=4 height=5>&nbsp;</td></tr>';
}

if(checkTest('L071',$order['serialno']) && (($b['cholesterol'] + $b['tiglycerides'] + $b['hdl'] + $b['ldl'] + $b['vldl']) > 0) || checkTest('L139',$order['serialno'])) {
    $html .= '
    <tr><td colspan="5"><b>LIPID PROFILE :</b></td></tr>';

    if($b['cholesterol'] > 0) {

        $html .= '<tr><td colspan=4 height=5>&nbsp;</td></tr>
        <tr>
            <td align="left" style="padding-left: 25px;">Total Cholesterol</td>
            <td align=center style="border-bottom: 1px solid black;">'. $b['cholesterol'] . '</td>
            <td align=center>'.$con->checkChemValues2($con->age,$a['xgender'],'L019',$b['cholesterol'],$order['machine']).'</td>
            <td align=center>'.$con->getAttribute2('L019',$con->age,$a['xgender'],$order['machine']).'</td>	
        </tr>';
    }

    if($b['triglycerides'] > 0) {

        $html .= '<tr><td colspan=4 height=5>&nbsp;</td></tr>
        <tr>
            <td align="left" style="padding-left: 25px;">Triglycerides</td>
            <td align=center style="border-bottom: 1px solid black;">'. $b['triglycerides'] . '</td>
            <td align=center>'.$con->checkChemValues2($con->age,$a['xgender'],'L032',$b['triglycerides'],$order['machine']).'</td>
            <td align=center>'.$con->getAttribute2('L032',$con->age,$a['xgender'],$order['machine']).'</td>	
        </tr>';
    }

    if($b['hdl'] > 0) {
        $html .= '<tr><td colspan=4 height=5>&nbsp;</td></tr>
        <tr>
            <td align="left" style="padding-left: 25px;">HDL</td>
            <td align=center style="border-bottom: 1px solid black;">'. $b['hdl'] . '</td>
            <td align=center>'.$con->checkChemValues2($con->age,$a['xgender'],'L134',$b['hdl'],$order['machine']).'</td>
            <td align=center>'.$con->getAttribute2('L134',$con->age,$a['xgender'],$order['machine']).'</td>	
        </tr>';
    }

    if($b['ldl'] > 0) {
        $html .= '<tr><td colspan=4 height=5>&nbsp;</td></tr>
        <tr>
            <td align="left" style="padding-left: 25px;">LDL</td>
            <td align=center style="border-bottom: 1px solid black;">'. $b['ldl'] . '</td>
            <td align=center>'.$con->checkChemValues2($con->age,$a['xgender'],'L018',$b['ldl'],$order['machine']).'</td>
            <td align=center>'.$con->getAttribute2('L018',$con->age,$a['xgender'],$order['machine']).'</td>	
        </tr>';
    }

    if($b['vldl'] > 0) {

        $html .= '<tr><td colspan=4 height=5>&nbsp;</td></tr>
        <tr>
            <td align="left" style="padding-left: 25px;">VLDL</td>
            <td align=center style="border-bottom: 1px solid black;">'. $b['vldl'] . '</td>
            <td align=center>'.$con->checkChemValues2($con->age,$a['xgender'],'VLDL',$b['vldl'],$order['machine']).'</td>
            <td align=center>'.$con->getAttribute2('VLDL',$con->age,$a['xgender'],$order['machine']).'</td>	
        </tr>';
    }
}

if(checkTest('L028',$order['serialno'])) {
    $html .= '<tr>
        <td align="left">SGOT/AST&nbsp;</td>
        <td align=center style="border-bottom: 1px solid black;">'. $b['sgot'] . '</td>
        <td align=center>'.$con->checkChemValues2($con->age,$a['xgender'],'L028',$b['sgot'],$order['machine']).'</td>
        <td align=center>'.$con->getAttribute2('L028',$con->age,$a['xgender'],$order['machine']).'</td>	
    </tr>
    <tr><td colspan=4 height=5>&nbsp;</td></tr>';
}

if(checkTest('L023',$order['serialno'])) {
    $html .= '<tr>
        <td align="left">LDH&nbsp;</td>
        <td align=center style="border-bottom: 1px solid black;">'. $b['ldh'] . '</td>
        <td align=center>'.$con->checkChemValues2($con->age,$a['xgender'],'L023',$b['ldh'],$order['machine']).'</td>
        <td align=center>'.$con->getAttribute2('L023',$con->age,$a['xgender'],$order['machine']).'</td>	
    </tr>
    <tr><td colspan=4 height=5>&nbsp;</td></tr>';
}

if(checkTest('L029',$order['serialno'])) {
    $html .= '<tr>
        <td align="left">SGPT/ALT&nbsp;:</td>
        <td align=center style="border-bottom: 1px solid black;">'. $b['sgpt'] . '</td>
        <td align=center>'.$con->checkChemValues2($con->age,$a['xgender'],'L029',$b['sgpt'],$order['machine']).'</td>
        <td align=center>'.$con->getAttribute2('L029',$con->age,$a['xgender'],$order['machine']).'</td>	
    </tr>
    <tr><td colspan=4 height=5>&nbsp;</td></tr>';
}

if(checkTest('L016',$order['serialno'])) {
    $html .= '<tr>
        <td align="left">Alkaline Phosphate&nbsp;:</td>
        <td align=center style="border-bottom: 1px solid black;">'. $b['alkaline'] . '</td>
        <td align=center>'.$con->checkChemValues2($con->age,$a['xgender'],'L016',$b['alkaline'],$order['machine']).'</td>
        <td align=center style="padding-left: 15px;">'.$con->getAttribute2('L016',$con->age,$a['xgender'],$order['machine']).'</td>	
    </tr>
    <tr><td colspan=4 height=5>&nbsp;</td></tr>';
}

if(checkTest('L003',$order['serialno']) || checkTest('L109',$order['serialno'])) {
    $html .= '<tr>
    <td align="left">Total Bilirubin</td>
    <td align=center>'. $b['bilirubin'] . '</td>
    <td align=center>'.$con->checkChemValues2($con->age,$a['xgender'],'L003',$b['bilirubin'],$order['machine']).'</td>
    <td align=center colspan=2>'.$con->getAttribute2('L003',$con->age,$a['xgender'],$order['machine']).'</td>	
    </tr>
    <tr><td colspan=4 height=5>&nbsp;</td></tr>';

    $html .= '<tr>
    <td align="left">Direct Bilirubin&nbsp;:</td>
    <td align=center>'. $b['bilirubin_direct'] . '</td>
    <td align=center>'.$con->checkChemValues2($con->age,$a['xgender'],'L109',$b['bilirubin_direct'],$order['machine']).'</td>
    <td align=center>'.$con->getAttribute2('L109',$con->age,$a['xgender'],$order['machine']).'</td>	
    </tr>
    <tr><td colspan=4 height=5>&nbsp;</td></tr>';

    $html .= '<tr>
        <td align="left">Indirect Bilirubin</td>
        <td align=center>'. ($b['bilirubin']-$b['bilirubin_direct']) . '</td>
        <td align=center>'.$con->checkChemValues2($con->age,$a['xgender'],'L149',$b['bilirubin_indirect'],$order['machine']).'</td>
        <td align=center>'.$con->getAttribute2('L149',$con->age,$a['xgender'],$order['machine']).'</td>	
    </tr>
    <tr><td colspan=4 height=5>&nbsp;</td></tr>';

}

if(checkTest('L027',$order['serialno'])) {
    $html .= '<tr>
        <td align="left">Total Protein</td>
        <td align=center style="border-bottom: 1px solid black;">'. $b['protein'] . '</td>
        <td align=center>'.$con->checkChemValues2($con->age,$a['xgender'],'L027',$b['protein'],$order['machine']).'</td>
        <td align=center>'.$con->getAttribute2('L027',$con->age,$a['xgender'],$order['machine']).'</td>	
    </tr>
    <tr><td colspan=4 height=5>&nbsp;</td></tr>';
}

if(checkTest('L001',$order['serialno'])) {
    $html .= '<tr>
        <td align="left">Albumin&nbsp;:</td>
        <td align=center style="border-bottom: 1px solid black;">'. $b['albumin'] . '</td>
        <td align=center>'.$con->checkChemValues2($con->age,$a['xgender'],'L001',$b['albumin'],$order['machine']).'</td>
        <td align=center>'.$con->getAttribute2('L001',$con->age,$a['xgender'],$order['machine']).'</td>	
    </tr>
    <tr><td colspan=4 height=5>&nbsp;</td></tr>';
}

if(checkTest('L001',$order['serialno']) && checkTest('L027',$order['serialno'])) {
    $html .= '<tr>
        <td align="left">A/G Ratio&nbsp;:</td>
        <td align=center style="border-bottom: 1px solid black;">'. $b['agratio'] . '</td>
        <td align=center>'.$con->checkChemValues2($con->age,$a['xgender'],'AGRATIO',$b['agratio'],$order['machine']).'</td>
        <td align=center>'.$con->getAttribute2('AGRATIO',$con->age,$a['xgender'],$order['machine']).'</td>		
    </tr>
    <tr><td colspan=4 height=5>&nbsp;</td></tr>';
}
if(checkTest('L080',$order['serialno'])) {
    $html .= '<tr>
        <td align="left" colspan=3 ><b>Electrolytes&nbsp;:</b></td>
    </tr>
    <tr><td colspan=4 height=5>&nbsp;</td></tr>';

    if($b['electrolytes_na'] > 0) {
        $html .= '
                <tr>
                    <td align="left" style="padding-left: 35px;">Sodium (Na)&nbsp;:</td>
                    <td align=center style="border-bottom: 1px solid black;">'. $b['electrolytes_na'] . '</td>
                    <td align=center >'.$con->checkChemValues2($con->age,$a['xgender'],'L030',$b['electrolytes_na'],$order['machine']).'</td>
                    <td align=center>'.$con->getAttribute2('L030',$con->age,$a['xgender'],$order['machine']).'</td>	
                </tr>
                <tr><td colspan=4 height=5>&nbsp;</td></tr>
        ';
    }
   
    if($b['electrolytes_k'] > 0) {
        $html .= '
                <tr>
                    <td align="left" style="padding-left: 35px;">Potassium (K)&nbsp;:</td>
                    <td align=center style="border-bottom: 1px solid black;">'. $b['electrolytes_k'] . '</td>
                    <td align=center>'.$con->checkChemValues2($con->age,$a['xgender'],'L026',$b['electrolytes_k'],$order['machine']).'</td>
                    <td align=center>'.$con->getAttribute2('L026',$con->age,$a['xgender'],$order['machine']).'</td>	
                </tr>
                <tr><td colspan=4 height=5>&nbsp;</td></tr>
        ';
    }
    
    if($b['electrolytes_ci'] > 0) {
        $html .= '
            <tr>
                <td align="left" style="padding-left: 35px;">Chloride (CI)&nbsp;:</td>
                <td align=center width=20% style="border-bottom: 1px solid black;">'. $b['electrolytes_ci'] . '</td>
                <td align=center>'.$con->checkChemValues2($con->age,$a['xgender'],'L017',$b['electrolytes_ci'],$order['machine']).'</td>
                <td align=center>'.$con->getAttribute2('L017',$con->age,$a['xgender'],$order['machine']).'</td>	
            </tr>
            <tr><td colspan=4 height=5>&nbsp;</td></tr>

        ';
    }

    if($b['ion_calcium'] > 0) {   
        $html .= '
                <tr>
                    <td align="left" style="padding-left: 35px;">Ionized Calcium&nbsp;:</td>
                    <td align=center style="border-bottom: 1px solid black;">'. $b['ion_calcium'] . '</td>
                    <td align=center>'.$con->checkChemValues2($con->age,$a['xgender'],'L130',$b['ion_calcium'],$order['machine']).'</td>
                    <td align=center>'.$con->getAttribute2('L130',$con->age,$a['xgender'],$order['machine']).'</td>	
                </tr>
                <tr><td colspan=4 height=5>&nbsp;</td></tr>
        ';
    }
        
}

if(checkTest('L030',$order['serialno'])) {
    $html .= '<tr>
        <td align="left">Sodium (Na)&nbsp;:</td>
        <td align=center style="border-bottom: 1px solid black;">'. $b['electrolytes_na'] . '</td>
        <td align=center>'.$con->checkChemValues2($con->age,$a['xgender'],'L030',$b['electrolytes_na'],$order['machine']).'</td>
        <td align=center>'.$con->getAttribute2('L030',$con->age,$a['xgender'],$order['machine']).'</td>	
    </tr>
    <tr><td colspan=4 height=5>&nbsp;</td></tr>';
}

if(checkTest('L026',$order['serialno'])) {
    $html .= '<tr>
        <td align="left">Potassium (K)&nbsp;:</td>
        <td align=center style="border-bottom: 1px solid black;">'. $b['electrolytes_k'] . '</td>
        <td align=center>'.$con->checkChemValues2($con->age,$a['xgender'],'L026',$b['electrolytes_k'],$order['machine']).'</td>
        <td align=center>'.$con->getAttribute2('L026',$con->age,$a['xgender'],$order['machine']).'</td>	
    </tr>
    <tr><td colspan=4 height=5>&nbsp;</td></tr>';
}

if(checkTest('L017',$order['serialno'])) {
    $html .= '<tr>
        <td align="left">Chemical Ionization (CI)&nbsp;:</td>
        <td align=center style="border-bottom: 1px solid black;">'. $b['electrolytes_ci'] . '</td>
        <td align=center>'.$con->checkChemValues2($con->age,$a['xgender'],'L017',$b['electrolytes_ci'],$order['machine']).'</td>
        <td align=center>'.$con->getAttribute2('L017',$con->age,$a['xgender'],$order['machine']).'</td>	
    </tr>
    <tr><td colspan=4 height=5>&nbsp;</td></tr>';
}

if(checkTest('L006',$order['serialno'])) {
    $html .= '<tr>
        <td align="left">Total Calcium&nbsp;:</td>
        <td align=center style="border-bottom: 1px solid black;">'. $b['total_calcium'] . '</td>
        <td align=center>'.$con->checkChemValues2($con->age,$a['xgender'],'L006',$b['total_calcium'],$order['machine']).'</td>
        <td align=center>'.$con->getAttribute2('L006',$con->age,$a['xgender'],$order['machine']).'</td>	
    </tr>
    <tr><td colspan=4 height=5>&nbsp;</td></tr>';
}

if(checkTest('L130',$order['serialno'])) {
    $html .= '<tr>
        <td align="left">Ionized Calcium&nbsp;:</td>
        <td align=center style="border-bottom: 1px solid black;">'. $b['ion_calcium'] . '</td>
        <td align=center></td>
        <td align=center>'.$con->getAttribute2('L130',$con->age,$a['xgender'],$order['machine']).'</td>	
    </tr>
    <tr><td colspan=4 height=5>&nbsp;</td></tr>';
}

if(checkTest('L025',$order['serialno'])) {
    $html .= '<tr>
        <td align="left">Inorganic&nbsp;Phosphorus&nbsp;:</td>
        <td align=center style="border-bottom: 1px solid black;">'. $b['phosphorus'] . '</td>
        <td align=center>'.$con->checkChemValues($con->age,$a['xgender'],'L025',$b['phosphorus']).'</td>
        <td align=center>'.$con->getAttribute('L025',$con->age,$a['xgender']).'</td>	
    </tr>
    <tr><td colspan=4 height=5>&nbsp;</td></tr>';
}


if(checkTest('L131',$order['serialno'])) {

    if($b['troponin'] > 0.04) { $flag = 'H'; }

    $html .= '<tr>
        <td align="left">Troponin I&nbsp;:</td>
        <td align=center style="border-bottom: 1px solid black;">'. $b['troponin'] . '</td>
        <td align=center>'.$flag.'</td>
        <td align=center>> 0.04 ng/mL</td>	
    </tr>
    <tr><td colspan=4 height=5>&nbsp;</td></tr>';
}

if(checkTest('L133',$order['serialno'])) {

    $html .= '<tr>
        <td align="left">Amylase&nbsp;:</td>
        <td align=center style="border-bottom: 1px solid black;">'. $b['amylase'] . '</td>
        <td align=center>'.$con->checkChemValues2($con->age,$a['xgender'],'L133',$b['amylase']).'</td>
        <td align=center>'.$con->getAttribute2('L133',$con->age,$a['xgender'],$order['machine']).'</td>	
    </tr>
    <tr><td colspan=4 height=5>&nbsp;</td></tr>';
}

if(checkTest('L135',$order['serialno'])) {

    $html .= '<tr>
        <td align="left">Lipase&nbsp;:</td>
        <td align=center style="border-bottom: 1px solid black;">'. $b['lipase'] . '</td>
        <td align=center>'.$con->checkChemValues2($con->age,$a['xgender'],'L135',$b['lipase']).'</td>
        <td align=center>'.$con->getAttribute2('L135',$con->age,$a['xgender'],$order['machine']).'</td>	
    </tr>
    <tr><td colspan=4 height=5>&nbsp;</td></tr>';
}

if(checkTest('L158',$order['serialno'])) {

    $html .= '<tr>
        <td align="left">Magnesium&nbsp;:</td>
        <td align=center style="border-bottom: 1px solid black;">'. $b['magnesium'] . '</td>
        <td align=center>'.$con->checkChemValues2($con->age,$a['xgender'],'L158',$b['magnesium'],$order['machine']).'</td>
        <td align=center>'.$con->getAttribute2('L158',$con->age,$a['xgender'],$order['machine']).'</td>	
    </tr>
    <tr><td colspan=4 height=5>&nbsp;</td></tr>';
}

if(checkTest('L159',$order['serialno'])) {

    $html .= '<tr>
        <td align="left">Inorganic Phospharous&nbsp;:</td>
        <td align=center style="border-bottom: 1px solid black;">'. $b['inorganic_phos'] . '</td>
        <td align=center>'.$con->checkChemValues2($con->age,$a['xgender'],'L159',$b['inorganic_phos'],$order['machine']).'</td>
        <td align=center>'.$con->getAttribute2('L159',$con->age,$a['xgender'],$order['machine']).'</td>	
    </tr>
    <tr><td colspan=4 height=5>&nbsp;</td></tr>';
}

if(checkTest('L161',$order['serialno'])) {

    $html .= '<tr>
        <td align="left">GLUCOSE 2hrs Post Prandial&nbsp;:</td>
        <td align=center style="border-bottom: 1px solid black;">'. $b['prandial'] . '</td>
        <td align=center>'.$con->checkChemValues2($con->age,$a['xgender'],'L161',$b['prandial'],$order['machine']).'</td>
        <td align=center>'.$con->getAttribute2('L161',$con->age,$a['xgender'],$order['machine']).'</td>	
    </tr>
    <tr><td colspan=4 height=5>&nbsp;</td></tr>';
}

$html .= ' <tr>
        <td align="center" colspan=4 style="padding-top: 10px;"><b>END OF RESULT. NOTHING FOLLOWS</b></td>
    </tr>
    <tr>
        <td align="left" colspan=4 style="border-top: 1px solid black; margin-top: 20px;"><b>Remarks:</b> '.$b['remarks'].'</td>
    </tr>
</table>

</body>
</html>
';

$html = html_entity_decode($html);
$mpdf->WriteHTML($html);
$mpdf->Output(); exit;
exit;

mysql_close($con);
?>