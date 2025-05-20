<?php
	session_start();
	include("../lib/mpdf6/mpdf.php");
	include("../handlers/_generics.php");

	$con = new _init;

/* MYSQL QUERIES SECTION */
	// $datePrinted = date("m/d/Y");
	// $timePrinted = date('h:i:s a');
	
	$order = $con->getArray("select *, date_format(extractdate,'%m/%d/%Y') as exdate, date_format(release_date,'%m/%d/%Y') as rdate from lab_samples where enccode = '$_REQUEST[enccode]' and serialno = '$_REQUEST[serialno]';");
    $a = $con->getArray("SELECT docointkey, a.enccode, SUBSTR(enccode,8,15) AS hmrno, DATE_FORMAT(dodate,'%m/%d/%Y %h:%i %p') AS orderdate, DATE_FORMAT(dodate,'%Y-%m-%d') AS xorderdate, a.hpercode, concat(URLENCODE(c.patlast),', ', URLENCODE(c.patfirst),', ', URLENCODE(c.patmiddle)) as pname,  DATE_FORMAT(c.patbdate,'%m/%d/%Y') AS bday, DATE_FORMAT(c.patbdate,'%Y-%m-%d') AS xbday, IF(c.patsex='F','FEMALE','MALE') AS sex, c.patsex as gender, a.proccode, b.procdesc, a.donotes AS remarks, a.licno, a.estatus, entby FROM hospital_dbo.hdocord a LEFT JOIN hospital_dbo.hprocm b ON a.proccode = b.proccode LEFT JOIN hospital_dbo.hperson c ON a.hpercode = c.hpercode WHERE a.enccode = '$order[enccode]';");
    $b = $con->getArray("select * from lab_ogtt where enccode = '$order[enccode]' and `code` = '$order[code]' and serialno = '$order[serialno]';");

	list($datePrinted, $timePrinted) = $con->getArray("select date_format(printed_on, '%m/%d/%Y') as d8print, date_format(printed_on, '%h:%i:%s %p') as timeprint from lab_samples where enccode = '$_REQUEST[enccode]' and serialno = '$_REQUEST[serialno]';");

	$con->calculateAge($a['xorderdate'],$a['xbday']);
	list($encSignature,$encBy,$encByLicense,$encByRole) = $con->getArray("SELECT if(signature_file != '',concat('<img src=\"../images/signatures/',signature_file,'\" align=absmiddle />'),'<img src=\"../images/signatures/blank.png\" align=absmiddle />') as signature, fullname, license_no, role from user_info where emp_id = '$b[created_by]';");
    if($b['verified_by'] != '') {
        list($cbySignature,$cby,$cbyLicense,$cbyRole) = $con->getArray("SELECT if(signature_file != '',concat('<img src=\"../images/signatures/',signature_file,'\" align=absmiddle />'),'<img src=\"../images/signatures/blank.png\" align=absmiddle />') as signature, fullname, license_no, role from user_info where emp_id = '$b[verified_by]';");
    }

	list($pateintAddress) = $con->getArray("SELECT URLENCODE(CONCAT(a.patstr,', ',b.bgyname,', ',c.ctyname,', ',d.provname)) AS addr FROM hospital_dbo.haddr a LEFT JOIN hospital_dbo.hbrgy b ON a.brg = b.bgycode LEFT JOIN hospital_dbo.hcity c ON a.ctycode = c.ctycode LEFT JOIN hospital_dbo.hprov d ON a.provcode = d.provcode WHERE a.hpercode = '$a[hmrno]';");    
	
	//list($room) = $con->getArray("SELECT concat(b.wardcode,'-',rmname,'-',bdname) FROM ppp_danao.lab_samples a LEFT JOIN hospital_dbo.hpatroom b ON a.enccode = b.enccode LEFT JOIN hospital_dbo.hroom c ON b.rmintkey = c.rmintkey LEFT JOIN hospital_dbo.hbed d ON b.bdintkey = d.bdintkey WHERE a.enccode = '$a[enccode]' limit 1;");
	$room = $con->identRoom($a['enccode']);
	if($room == '') { 
        list($room) = $con->getArray("select toecode from hospital_dbo.henctr where enccode = '$order[enccode]';");
    }

	// if($b['result'] > 100) { $flag1 = 'H'; }
	// if($b['result'] < 60) { $flag1 = 'L'; }

	// if($b['result2'] > 200) { $flag2 = 'H'; }
	// if($b['result3'] > 140) { $flag2 = 'H'; }



/* END OF SQL QUERIES */

$mpdf=new mPDF('win-1252','LETTER','','',10,10,95,30,5,10);
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
		body {font-family: sans-serif; font-size: 10px; }
        .itemHeader {
            padding:5px;border-top:1px solid black; border-bottom:1px solid black; text-align: center; font-weight: bold;
        }

        .itemResult {
            padding:5px; text-align: center;
        }

        /* #items td { border: 1px solid; text-align: center; } */
	</style>
</head>
<body>

<!--mpdf
<htmlpageheader name="myheader">
<table width="100%" cellpadding=0 cellspaing=0>
	<tr><td align=center><img src="../images/doc-header.jpg" /></td></tr>
</table>
<table width=100% cellpadding=2 cellspacing=0 style="font-size: 10pt;margin-top:20px;">
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
	<td width=100% colspan=4 style="background-color: #8db4e2; border-top: 1px solid black; padding: 5px; font-weight: bold;" align=center>2-HOUR 75gms. ORAL GLUCOSE TOLERANCE TEST</td>
	</tr>  
</table>

</htmlpageheader>

<htmlpagefooter name="myfooter">
<table width=100% cellpadding=5 style="margin-bottom: 5px; font-size: 9pt;">
    <tr>
		<td width=33% align=center>'.$encSignature.'<br/><b>'.$encBy.'<br/>_______________________________<br/><span style="font-size: 8pt;">PRC LICENSE NO. '.$encByLicense.'</span><br/><b>REPORTED BY</b></td>
        <td width=33% align=center>'.$cbySignature.'<br/><b>'.$cby.'<br/>_______________________________<br/><span style="font-size: 8pt;">PRC LICENSE NO. '.$cbyLicense.'</span><br/><b>VALIDATED BY</b></td>
        <td align=center valign=top><img src="../images/signatures/psa-signature.png" align=absmidddle /><br/><b>PETER S. AZNAR, M.D, F.P.S.P<br/>_______________________________<br><span style="font-size: 8pt;">PRC LICENSE NO. 72410</span><br/><b>PATHOLOGIST</b></td>
    </tr>
</table>
</htmlpagefooter>

<sethtmlpageheader name="myheader" value="on" show-this-page="1" />
<sethtmlpagefooter name="myfooter" value="on" />
mpdf-->
<div id="main">
    <table width=100% cellpadding=0 cellspacing=0 align=center style="border-collapse: collapse; font-size: 9pt;">
		<tr>
			<td width=20% class="itemHeader" align=left>TEST</td>
			<td width=20% class="itemHeader">RESULT</td>
			<td width=20% class="itemHeader">URINE</td>
			<td width=20% class="itemHeader">FLAG</td>
			<td width=20% class="itemHeader">NORMAL REFERENCE</td>
		</tr>
		<tr>
			<td class="itemResult" align=left>FBS</td>
			<td class="itemResult">'.$b['result'].'</td>
			<td class="itemResult">'.$b['fbs_urine'].'</td>
			<td align=center>'.$con->checkChemValues($con->age,$a['xgender'],'OGTT_FBS',$b['result']).'</td>
			<td align=center valign=top>'.$con->getAttribute('OGTT_FBS',$con->age,$a['xgender']).'</td>	
		</tr>
		<tr>
			<td class="itemResult" align=left>After 1 Hour</td>
			<td class="itemResult">'.$b['result2'].'</td>
			<td class="itemResult">'.$b['fhours_urine'].'</td>
			<td align=center>'.$con->checkChemValues($con->age,$a['xgender'],'OGTT_1HR',$b['result2']).'</td>
			<td align=center valign=top>'.$con->getAttribute('OGTT_1HR',$con->age,$a['xgender']).'</td>	
			</tr>
		<tr>
			<td class="itemResult" align=left>After 2 Hours</td>
			<td class="itemResult">'.$b['result3'].'</td>
			<td class="itemResult">'.$b['shours_urine'].'</td>
			<td align=center>'.$con->checkChemValues($con->age,$a['xgender'],'OGTT_2HR',$b['result3']).'</td>
			<td align=center valign=top>'.$con->getAttribute('OGTT_2HR',$con->age,$a['xgender']).'</td>	
		</tr>
    </table>
	<table width=100% align=center style="margin-top: 5px; font-size: 9pt; font-style: italic;">
        <tr>
            <td align="center" style="padding-top: 10px;"><b>END OF RESULT. NOTHING FOLLOWS</b></td>
        </tr> 
        <tr>
            <td align="left" style="border-top: 1px solid black; margin-top: 20px;"><b>Remarks:</b> '.$b['remarks'].'</td>
        </tr>
    </table>
</div>
</body>
</html>
';

$html = html_entity_decode($html);
$mpdf->WriteHTML($html);
$mpdf->Output(); exit;
exit;

mysql_close($con);
?>