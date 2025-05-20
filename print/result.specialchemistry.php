<?php
	session_start();
	include("../lib/mpdf6/mpdf.php");
	include("../handlers/_generics.php");

	$con = new _init;

/* MYSQL QUERIES SECTION */
    // $datePrinted = date("m/d/Y");
    // $timePrinted = date('h:i:s a');

    $order = $con->getArray("select *, date_format(extractdate,'%m/%d/%Y') as exdate, date_format(release_date,'%m/%d/%Y') as rdate, dotime from lab_samples where enccode = '$_REQUEST[enccode]' and serialno = '$_REQUEST[serialno]' limit 1;");
    $a = $con->getArray("SELECT docointkey, a.enccode, SUBSTR(enccode,8,15) AS hmrno, DATE_FORMAT(dodate,'%m/%d/%Y %h:%i %p') AS orderdate, DATE_FORMAT(dodate,'%Y-%m-%d') AS xorderdate, a.hpercode, URLENCODE(concat(c.patlast,', ', c.patfirst,' ', c.patmiddle)) as pname,  DATE_FORMAT(c.patbdate,'%m/%d/%Y') AS bday, DATE_FORMAT(c.patbdate,'%Y-%m-%d') AS xbday, IF(c.patsex='F','FEMALE','MALE') AS sex, c.patsex as gender, a.proccode, b.procdesc, a.donotes AS remarks, a.licno, a.estatus, entby FROM hospital_dbo.hdocord a LEFT JOIN hospital_dbo.hprocm b ON a.proccode = b.proccode LEFT JOIN hospital_dbo.hperson c ON a.hpercode = c.hpercode WHERE a.enccode = '$order[enccode]' and a.dotime = '$order[dotime]';");
    $b = $con->getArray("select * from lab_spchem where enccode = '$a[enccode]' and serialno = '$order[serialno]';");

    $con->calculateAge($a['xorderdate'],$a['xbday']);
	list($pateintAddress) = $con->getArray("SELECT URLENCODE(CONCAT(a.patstr,', ',b.bgyname,', ',c.ctyname,', ',d.provname)) AS addr FROM hospital_dbo.haddr a LEFT JOIN hospital_dbo.hbrgy b ON a.brg = b.bgycode LEFT JOIN hospital_dbo.hcity c ON a.ctycode = c.ctycode LEFT JOIN hospital_dbo.hprov d ON a.provcode = d.provcode WHERE a.hpercode = '$a[hmrno]';");
   

    list($datePrinted, $timePrinted) = $con->getArray("select date_format(printed_on, '%m/%d/%Y') as d8print, date_format(printed_on, '%h:%i:%s %p') as timeprint from lab_samples where enccode = '$_REQUEST[enccode]' and serialno = '$_REQUEST[serialno]';");

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
        <td style="border-bottom: 1px solid black; font-weight: bold;">' . $con->ageDisplay . "/" . $a['sex']. '</td>
        <td><b>Date Collected :</b></td>
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
			<span style="font-weight: bold; font-size: 12pt; color: #000000; font-family: TimesNewRoman, Times New Roman;">CLINICAL CHEMISTRY</span>
		</td>
	</tr>
    <tr>
        <td width=100% colspan=4 style="background-color: #8db4e2; border-top: 1px solid black; padding: 5px; font-size: 12pt; font-weight: bold;" align=center>SPECIAL CHEMISTRY</td>
	</tr>    
</table>
</htmlpageheader>

<htmlpagefooter name="myfooter">
    <table width=100% cellpadding=5 style="margin-bottom: 5px; font-size: 9pt;">
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

<table width=90% cellpadding=0 cellspacing=0 align=center>';
if($b['bhcg'] > 0) {
    $html .='<tr>
        <td align="left" width=25% style="border-bottom: 1px solid black;"><b>TEST</b></td>
        <td align=center width=25% style="border-bottom: 1px solid black;"><b>RESULT</b></td>
        <td align=center width=25% style="border-bottom: 1px solid black;"><b>FLAG</b></td>
        <td align="center" width=25% style="padding-left: 15px; border-bottom: 1px solid black;"><b>REFERENCE VALUES</b></td>	
        <tr><td colspan=4 height=5>&nbsp;</td></tr>';	
    $htnl .='</tr>';
}else {
    $html .='<tr>
        <td align="left" width=25% style="border-bottom: 1px solid black;"><b>TEST</b></td>
        <td align=center width=25% style="border-bottom: 1px solid black;"><b>RESULT</b></td>
        <td align=center width=25% style="border-bottom: 1px solid black;"><b>FLAG</b></td>
        <td align="center" width=25% style="padding-left: 15px; border-bottom: 1px solid black;"><b>REFERENCE VALUES</b></td>
        <tr><td colspan=4 height=5>&nbsp;</td></tr>';	
    $htnl .='</tr>';
}
'<tr><td colspan=4 height=5>&nbsp;</td></tr>';


if($b['afp'] > 0) {
    $html .= '<tr>
        <td align="left" valign=top>AFP</td>
        <td align=center style="border-bottom: 1px solid black;vertical-align: top;">'. $b['afp'] . '</td>
        <td align=center>'.$con->checkChemValues($con->age,$a['xgender'],'L072',$b['afp']).'</td>
        <td align="center" valign=top>'.$con->getAttribute('L072',$con->age,$a['xgender']).'</td>	
    </tr>
    <tr><td colspan=4 height=5>&nbsp;</td></tr>
    ';
}

if($b['bhcg'] != '') {
    $html .= '<tr>
        <td align="left">ß-HCG PREGNANCY TEST (QUANTI)</td>
        <td align=center style="border-bottom: 1px solid black;">'. $b['bhcg'] . '</td>
        <td align=center>'.$con->checkChemValues($con->age,$a['xgender'],'L073',$b['bhcg']).'</td>
        <td align="center">'.$con->getAttribute('L073',$con->age,$a['xgender']).'</td>	
    </tr>
    <tr><td colspan=4 height=5>&nbsp;</td></tr>';
}

if($b['bhcgt'] > 0) {
    $html .= '<tr>
        <td align="left">B-HCGT w/ Titer</td>
        <td align=center style="border-bottom: 1px solid black;">'. $b['bhcgt'] . '</td>
        <td align=center>'.$con->checkChemValues($con->age,$a['xgender'],'L074',$b['bhcgt']).'</td>
        <td align="center">'.$con->getAttribute('L074',$con->age,$a['xgender']).'</td>	
    </tr>
    <tr><td colspan=4 height=5>&nbsp;</td></tr>';
}

if($b['cea'] > 0) {
    $html .= '<tr>
        <td align="left">CARCINO-EMBRYONIC ANTIGEN (CEA)</td>
        <td align=center style="border-bottom: 1px solid black;">'. $b['cea'] . '</td>
        <td align=center>'.$con->checkChemValues($con->age,$a['xgender'],'L075',$b['cea']).'</td>
        <td align="center">'.$con->getAttribute('L075',$con->age,$a['xgender']).'</td>	
    </tr>
    <tr><td colspan=4 height=5>&nbsp;</td></tr>';
}

if($b['crp'] != '') {
    $html .= '<tr>
        <td align="left">CRP</td>
        <td align=center style="border-bottom: 1px solid black;">'. $b['crp'] . '</td>
        <td align=center>'.$con->checkChemValues2($con->age,$a['xgender'],'L078', trim($b['crp'],'>')).'</td>
        <td align="center">'.$con->getAttribute2('L078',$con->age,$a['xgender']).'</td>	
    </tr>
    <tr><td colspan=4 height=5>&nbsp;</td></tr>
    <tr>
        <td align="left">Hs-CRP</td>
        <td align=center style="border-bottom: 1px solid black;">'. $b['hscrp'] . '</td>
        <td align=center>'.$con->checkChemValues2($con->age,$a['xgender'],'HSCRP', trim($b['hscrp'],'>')).'</td>
        <td align="center">'.$con->getAttribute2('HSCRP',$con->age,$a['xgender']).'</td>	
    </tr>
    <tr><td colspan=4 height=5>&nbsp;</td></tr>
    ';
}

if($b['dimer'] > 0) {
    $html .= '<tr>
        <td align="left">D-Dimer&nbsp;:</td>
        <td align=center style="border-bottom: 1px solid black;">'. $b['dimer'] . '</td>
        <td align=center>'.$con->checkChemValues($con->age,$a['xgender'],'L138',$b['dimer']).'</td>
        <td align="center">'.$con->getAttribute('L138',$con->age,$a['xgender']).'</td>	
    </tr>
    <tr><td colspan=4 height=5>&nbsp;</td></tr>';
}

if($b['ft3'] > 0) {
    $html .= '<tr>
        <td align="left">FT3&nbsp;:</td>
        <td align=center style="border-bottom: 1px solid black;">'. $b['ft3'] . '</td>
        <td align=center>'.$con->checkChemValues($con->age,$a['xgender'],'L044',$b['ft3']).'</td>
        <td align="center">'.$con->getAttribute('L044',$con->age,$a['xgender']).'</td>	
    </tr>
    <tr><td colspan=4 height=5>&nbsp;</td></tr>';
}

if($b['ft4']) {
    $html .= '<tr>
        <td align="left">FT4&nbsp;:</td>
        <td align=center style="border-bottom: 1px solid black;">'. $b['ft4'] . '</td>
        <td align=center>'.$con->checkChemValues($con->age,$a['xgender'],'L045',$b['ft4']).'</td>
        <td align="center"style="padding-left: 15px;">'.$con->getAttribute('L045',$con->age,$a['xgender']).'</td>	
    </tr>
    <tr><td colspan=4 height=5>&nbsp;</td></tr>';
}

if($b['ggt'] > 0) {
    $html .= '<tr>
        <td align="left">GGT</td>
        <td align=center style="border-bottom: 1px solid black;">'. $b['ggt'] . '</td>
        <td align=center>'.$con->checkChemValues($con->age,$a['xgender'],'L081',$b['ggt']).'</td>
        <td align="center" colspan=2>'.$con->getAttribute('L081',$con->age,$a['xgender']).'</td>	
    </tr>
    <tr><td colspan=4 height=5>&nbsp;</td></tr>';
}

if($b['psa'] > 0) {
    $html .= '<tr>
        <td align="left">PSA&nbsp;:</td>
        <td align=center style="border-bottom: 1px solid black;">'. $b['psa'] . '</td>
        <td align=center>'.$con->checkChemValues($con->age,$a['xgender'],'L076',$b['psa']).'</td>
        <td align="center">'.$con->getAttribute('L076',$con->age,$a['xgender']).'</td>	
    </tr>
    <tr><td colspan=4 height=5>&nbsp;</td></tr>';
}

if($b['tsh'] != '') {

    /* trim leading > value */
    $tshVal = trim($b['tsh'],'<');


    $html .= '<tr>
        <td align="left">TSH&nbsp;:</td>
        <td align=center style="border-bottom: 1px solid black;">'. $b['tsh'] . '</td>
        <td align=center>'.$con->checkChemValues($con->age,$a['xgender'],'L049',$tshVal).'</td>
        <td align="center">'.$con->getAttribute('L049',$con->age,$a['xgender']).'</td>	
    </tr>
    <tr><td colspan=4 height=5>&nbsp;</td></tr>';
}

if($b['t3']) {
    $html .= '<tr>
        <td align="left">T3&nbsp;:</td>
        <td align=center style="border-bottom: 1px solid black;">'. $b['t3'] . '</td>
        <td align=center>'.$con->checkChemValues($con->age,$a['xgender'],'L047',$b['t3']).'</td>
        <td align="center">'.$con->getAttribute('L047',$con->age,$a['xgender']).'</td>	
    </tr>
    <tr><td colspan=4 height=5>&nbsp;</td></tr>';
}

if($b['t4']) {
    $html .= '<tr>
        <td align="left">T4&nbsp;:</td>
        <td align=center style="border-bottom: 1px solid black;">'. $b['t4'] . '</td>
        <td align=center>'.$con->checkChemValues($con->age,$a['xgender'],'L048',trim($b['t4'],'>')).'</td>
        <td align="center">'.$con->getAttribute('L048',$con->age,$a['xgender']).'</td>	
    </tr>
    <tr><td colspan=4 height=5>&nbsp;</td></tr>';
}

if($b['hba1c']) {
    $html .= '<tr>
        <td align="left">HbA1c&nbsp;:</td>
        <td align=center style="border-bottom: 1px solid black;">'. $b['hba1c'] . '</td>
        <td align=center>'.$con->checkChemValues($con->age,$a['xgender'],'L022',trim($b['hba1c'],'>')).'</td>
        <td align="center">'.$con->getAttribute('L022',$con->age,$a['xgender']).'</td>	
    </tr>
    <tr><td colspan=4 height=5>&nbsp;</td></tr>';
}

if($b['tropi_qn']) {
    $html .= '<tr>
        <td align="left">TROP-I (Quantitative)&nbsp;:</td>
        <td align=center style="border-bottom: 1px solid black;">'. $b['tropi_qn'] . '</td>
        <td align=center>'.$con->checkChemValues($con->age,$a['xgender'],'L131',trim($b['tropi_qn'],'>')).'</td>
        <td align="center">'.$con->getAttribute('L131',$con->age,$a['xgender']).'</td>	
    </tr>
    <tr><td colspan=4 height=5>&nbsp;</td></tr>';
}

if($b['tropi_ql'] != '') {
    $html .= '<tr>
        <td align="left">TROP-I (Qualitative)&nbsp;:</td>
        <td align=center style="border-bottom: 1px solid black;">'. $b['tropi_ql'] . '</td>
        <td align=center></td>
        <td align="center"></td>	
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

?>