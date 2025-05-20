<?php
	session_start();
	include("../lib/mpdf6/mpdf.php");
	include("../handlers/_generics.php");

	$con = new _init;

/* MYSQL QUERIES SECTION */
	$now = date("m/d/Y h:i a");
	$co = $con->getArray("select * from companies where company_id = '$_SESSION[company]';");

	$_ihead = $con->getArray("SELECT DATE_FORMAT(result_date,'%m/%d/%Y') AS rdate, URLENCODE(b.patient_name), URLENCODE(b.patient_address), IF(c.gender='M','Male','Female') AS gender, FLOOR(DATEDIFF(so_date,c.birthdate)/364.25) AS age, date_format(c.birthdate,'%m/%d/%Y') as dob, b.physician, a.serialno, a.created_by, a.performed_by, a.verified_by, b.trace_no, a.result, a.verified FROM lab_enumresult a LEFT JOIN so_header b ON a.so_no = b.so_no AND a.branch = b.branch LEFT JOIN patient_info c ON b.patient_id = c.patient_id WHERE a.so_no = '$_REQUEST[so_no]' AND `code` = '$_REQUEST[code]' AND serialno = '$_REQUEST[serialno]' AND a.branch = '$_SESSION[branchid]';");
    $b = $con->getArray("SELECT a.testkit, a.lotno, date_format(a.expiry,'%Y/%m/%d') as expiry, date_format(extractdate,'%m/%d/%Y') as extractdate from lab_samples a where so_no = '$_REQUEST[so_no]' and branch = '$_SESSION[branchid]' and code = '$_REQUEST[code]' and serialno = '$_REQUEST[serialno]';");

	list($pbySignature,$pby,$pbyLicense,$pbyRole) = $con->getArray("SELECT if(signature_file != '',concat('<img src=\"../images/signatures/',signature_file,'\" align=absmiddle />'),'<img src=\"../images/signatures/blank.png\" align=absmiddle />') as signature, fullname, license_no, role from user_info where emp_id = '$_ihead[performed_by]';");
	list($encSignature,$encBy) = $con->getArray("SELECT if(signature_file != '',concat('<img src=\"../images/signatures/',signature_file,'\" align=absmiddle />'),'<img src=\"../images/signatures/blank.png\" align=absmiddle />') as signature, fullname from user_info where emp_id = '$_ihead[created_by]';");
	if($_ihead['verified_by'] != '') {
        list($cbySignature,$cby,$cbyLicense,$cbyRole) = $con->getArray("SELECT if(signature_file != '',concat('<img src=\"../images/signatures/',signature_file,'\" align=absmiddle />'),'<img src=\"../images/signatures/blank.png\" align=absmiddle />') as signature, fullname, license_no, role from user_info where emp_id = '$_ihead[verified_by]';");
    }

/* END OF SQL QUERIES */

$mpdf=new mPDF('win-1252','LETTER','','',10,10,80,30,10,10);
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
		body {font-family: sans-serif; font-size: 11pt; }
       
	</style>
</head>
<body>

<!--mpdf
<htmlpageheader name="myheader">
<table width="100%" cellpadding=0 cellspaing=0>
	<tr><td align=center><img src="../images/doc-header.jpg" /></td></tr>

    <tr>
		<td width="100%" style="padding-top: 30px;" align=center>
			<span style="font-weight: bold; font-size: 12pt; color: #000000;">LABORATORY DEPARTMENT</span>
		</td>
	</tr>

</table>
<table width=100% cellpadding=1 cellspacing=0 style="font-size: 10pt; margin-top:20px;">
	<tr>
		<td width=100% colspan=4 style="background-color: #cdcdcd; border-top: 1px solid black; border-bottom: 1px solid black;" align=center><b>PATIENT INFORMATION</b></td>
	</tr>
	<tr>
		<td width=25%><b>NAME</b></td>
		<td width=40%>:&nbsp;&nbsp;'.urldecode($_ihead['patient_name']).'</td>
		<td width=20%><b>DATE RECEIVED</b></td>
		<td width=15%>:&nbsp;&nbsp;'.$b['extractdate'].'</td>
	</tr>
	<tr>
		<td><b>DATE OF BIRTH</b></td>
		<td>:&nbsp;&nbsp;'.$_ihead['dob'].'</td>
		<td><b>GENDER</b></td>
		<td>:&nbsp;&nbsp;'.$_ihead['gender'].'</td>
	</tr>
	<tr>
		<td><b>PATIENT STATUS</b></td>
		<td>:&nbsp;&nbsp;' . $_ihead['patient_status'] . '</td>
		<td><b>AGE</b></td>
		<td>:&nbsp;&nbsp;'.$_ihead['age'].'</td>
	</tr>
	<tr>
		<td><b>REQUESTING COMPANY</b></td>
		<td>:&nbsp;&nbsp;'.$_ihead['company'].'</td>
		<td><b>RESULT DATE</b></td>
		<td>:&nbsp;&nbsp;'.$_ihead['rdate'].'</td>
	</tr>
</table>

</htmlpageheader>

<htmlpagefooter name="myfooter">
<table width=100% cellpadding=5 style="margin-bottom: 25px;">
		<tr>
			<td width=33% align=center>'.$pbySignature.'<br/><b>'.$pby.'<br/>_______________________________<br/><span style="font-size: 8pt;">PRC LICENSE NO. '.$pbyLicense.'</span><br/><b>PERFORMED BY</b></td>
			<td width=33% align=center>'.$cbySignature.'<br/><b>'.$cby.'<br/>_______________________________<br/><span style="font-size: 8pt;">PRC LICENSE NO. '.$cbyLicense.'</span><br/><b>CONFIRMED BY</b></td>
			<td align=center valign=top><img src="../images/signatures/psa-signature.png" align=absmidddle /><br/><b>PETER S. AZNAR, M.D, F.P.S.P<br/>_______________________________<br><span style="font-size: 8pt;">PRC LICENSE NO. 72410</span><br/><b>PATHOLOGIST</b></td>
		</tr>
</table>
<table width=100%>
	<tr><td align=left><barcode size=0.8 code="'.$_ihead['trace_no'].'" type="C128A"></td><td align=right>Date & Time Printed : '.date('m/d/Y h:i:s a').'</td></tr>
</table>
</htmlpagefooter>

<sethtmlpageheader name="myheader" value="on" show-this-page="1" />
<sethtmlpagefooter name="myfooter" value="on" />
mpdf-->
<div id="main">
    <table width=100% cellpadding=0 cellspacing=0 align=center style="border-collapse: collapse;">

            <tr>
               <td align=center width=100% style="font-size: 13pt; font-weight: bold;">SARS-COV2 RAPID ANTIGEN TEST</td>
            </tr>
            <tr>
				<td align=center width=100% style="font-size: 12pt; font-weight: bold; padding-top: 10px;">RESULT: '.$_ihead['result'].'</td>
            </tr>
			<tr>
				<td align=left width=100% style="font-size: 11pt; font-weight: bold; padding-top: 10px;">INTERPRETATION:</td>
            </tr>
			<tr>
				<td align=center width=100% style="font-size: 11pt; font-weight: bold; font-style: italic; padding-top: 10px;">Patient is <u>&nbsp;'.$_ihead['result'] . '&nbsp;</u> for SARS-COV2 Antigen</td>
            </tr>
			<tr>';

			if($_ihead['result'] == 'POSITIVE') {
				$html .= '<td align=left width=100% style="font-size: 9pt; font-style: italic; padding-top: 20px;"><b>Remarks:</b> PLEASE CORRELATE ALL RESULTS CLINICALLY. SUGGESTIVE FOR RT-PCR CONFIRMATION.</td>';
			} else {
				$html .= '<td align=left width=100% style="font-size: 9pt; font-style: italic; padding-top: 20px;"><b>Remarks:</b> PLEASE CORRELATE ALL RESULTS CLINICALLY</td>';
			}
			
		$html .= '</tr>
    </table>
	<table width=100% cellpadding=0 cellspacing=0 style="font-style: italic; margin-top: 20px; font-size: 7pt;">
		<tr><td width=80><b>Test Kit :</b></td><td>'.$b['testkit'].'</td></tr>
		<tr><td width=80><b>Lot No :</b></td><td>'.$b['lotno'].'</td></tr>
		<tr><td width=80><b>Expiry Date :</b></td><td>'.$b['expiry'].'</td></tr>
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