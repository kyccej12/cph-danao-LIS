<?php
	session_start();
	include("../lib/mpdf6/mpdf.php");
	include("../handlers/_generics.php");

	$con = new _init;

/* MYSQL QUERIES SECTION */
	$now = date("m/d/Y h:i a");
	$co = $con->getArray("select * from companies where company_id = '$_SESSION[company]';");

	$_ihead = $con->getArray("SELECT DATE_FORMAT(result_date,'%m/%d/%Y') AS rdate, b.patient_name, b.patient_address, IF(c.gender='M','Male','Female') AS gender, FLOOR(DATEDIFF(b.so_date,c.birthdate)/365.25) AS age, a.physician, d.fullname as consultant, d.prefix, d.license_no, d.specialization, d.signature_file, a.serialno, a.procedure, a.impression, a.created_by, b.trace_no, a.verified, e.role, e.signature_file as encodersignature FROM lab_descriptive a LEFT JOIN so_header b ON a.so_no = b.so_no AND a.branch = b.branch LEFT JOIN patient_info c ON b.patient_id = c.patient_id left join options_doctors d on a.consultant = d.id left join user_info e on a.created_by = e.emp_id WHERE a.so_no = '$_REQUEST[so_no]' AND `code` = '$_REQUEST[code]' AND serialno = '$_REQUEST[serialno]' AND a.branch = '$_SESSION[branchid]';");
	
	if($_ihead['signature_file'] != '') {
		$consultantSignature = "<img src='../images/signatures/$_ihead[signature_file]' align=absmiddle />";
	} else {
		$consultantSignature = "<img src='../images/signatures/blank.png' align=absmiddle />";	
	}

	if($_ihead['encodersignature'] != '') {
		$encoderSignature = "<img src='../images/signatures/$_ihead[encodersignature]' align=absmiddle />";
	} else {
		$encoderSignature = "<img src='../images/signatures/blank.png' align=absmiddle />";	
	}

/* END OF SQL QUERIES */

$mpdf=new mPDF('win-1252','LETTER-HP','','',10,10,80,30,10,10);
$mpdf->use_embeddedfonts_1252 = true;    // false is default
$mpdf->SetProtection(array('print'));
$mpdf->SetAuthor("PORT80 Solutions");

if($_ihead['verified'] != 'Y') {
	$mpdf->SetWatermarkText('FOR VALIDATION');
	$mpdf->showWatermarkText = true;
}

$mpdf->SetDisplayMode(50);

$html = '
<html>
<head>
	<style>
		body {font-family: arial; font-size: 10pt; }
	</style>
</head>
<body>

<!--mpdf
<htmlpageheader name="myheader">
<table width="100%" cellpadding=0 cellspaing=0>
	<tr><td align=center><img src="../images/doc-header.jpg" /></td></tr>
</table>
<table width=100% cellpadding=2 cellspacing=0 style="font-size:8pt;margin-top:20px;">
	<tr>
		<td width=100% colspan=4 style="background-color: #cdcdcd; border-top: 1px solid black; border-bottom: 1px solid black;" align=center><b>PATIENT INFORMATION</b></td>
	</tr>
	<tr>
		<td width=20%><b>CASE NO.</b></td>
		<td width=35%>:&nbsp;&nbsp;'.$_ihead['serialno'].'</td>
		<td width=20%><b>DATE</b></td>
		<td width=25%>:&nbsp;&nbsp;'.$_ihead['rdate'].'</td>
	</tr>
	<tr>
		<td><b>PATIENT NAME</b></td>
		<td>:&nbsp;&nbsp;'.$_ihead['patient_name'].'</td>
		<td><b>GENDER</b></td>
		<td>:&nbsp;&nbsp;'.$_ihead['gender'].'</td>
	</tr>
	<tr>
		<td><b>ADDRESS</b></td>
		<td>:&nbsp;&nbsp;' . $_ihead['patient_address'] . '</td>
		<td><b>AGE</b></td>
		<td>:&nbsp;&nbsp;'.$_ihead['age'].' Y.O</td>
	</tr>
	<tr>
		<td><b>PHYSICIAN</b></td>
		<td>:&nbsp;&nbsp;'.$_ihead['physician'].'</td>
		<td><b>EXAMINATION</b></td>
		<td>:&nbsp;&nbsp;'.$_ihead['procedure'].'</td>
	</tr>
	<tr>
		<td width="100%" colspan=4 style="padding-top: 30px;" align=center>
			<span style="font-weight: bold; font-size: 14pt; color: #000000;">X-RAY REPORT</span>
		</td>
	</tr>
</table>

</htmlpageheader>

<htmlpagefooter name="myfooter">
<table width=100% cellpadding=5 style="margin-bottom: 25px;">
	<tr>
		<td width=50% align=center valign=top>'.$encoderSignature.'<br/>'.$con->getUname($_ihead['created_by']).'<br>___________________________________________<br/><b>'.$_ihead['role'].'</b></td>
		<td align=center valign=top>'.$consultantSignature.'<br/>'.$_ihead['consultant'].', '. $_ihead['prefix'] .'<br/>___________________________________________<br><b>'.$_ihead['specialization'].'<br/>License No. '.$_ihead['license_no'].'</b></td>
	</tr>
</table>
<table width=100% style="font-size: 8pt;">
	<tr><td align=left><barcode size=0.6 code="'.$_ihead['trace_no'].'" type="C128A"></td><td align=right>Run Date: '.date('m/d/Y h:i:s a').'</td></tr>
</table>
</htmlpagefooter>

<sethtmlpageheader name="myheader" value="on" show-this-page="1" />
<sethtmlpagefooter name="myfooter" value="on" />
mpdf-->
<div id="main" style="text-align: justify;">'.$_ihead['impression'].'</div>
</body>
</html>
';

$html = html_entity_decode($html);
$mpdf->WriteHTML($html);
$mpdf->Output(); exit;
exit;

mysql_close($con);
?>