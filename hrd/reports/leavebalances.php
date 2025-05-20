<?php
session_start();

require_once '../../handlers/initDB.php';
require_once '../../lib/mpdf6/mpdf.php';
ini_set("memory_limit","1024M");
ini_set("max_execution_time",0);

$pay = new myDB;

$mpdf=new mPDF('win-1252','letter','','',8,8,25,25,10,10);
$mpdf->use_embeddedfonts_1252 = true;    // false is default
$mpdf->SetProtection(array('print'));
$mpdf->SetTitle("Payroll Summary");
$mpdf->SetAuthor("PORT80 Solutions");
$mpdf->SetDisplayMode(75);

	/* MYSQL QUERIES SECTION */
		$cutoff = $_GET['cutoff'];
		
		if($_GET[emp_type] != "") { $fs = " and emp_type = '$_GET[emp_type]' "; }
		
		$now = date("m/d/Y h:i a");
		$co = $pay->getArray("select * from companies where company_id = '$_SESSION[company]';");
		$fy = $pay->getArray("select * from fiscal_year where id = '$_GET[fyid]';");
		$_ih = $pay->dbquery("SELECT emp_id, CONCAT(LNAME,', ',FNAME,' ',LEFT(MNAME,1),'.') AS emp_name, sl_credit, vl_credit FROM emp_masterfile WHERE FILE_STATUS != 'DELETED' AND EMPLOYMENT_STATUS NOT IN (7,8,9,10) and dept = '$_GET[dept]';");
		
		
	/* END OF SQL QUERIES */

$html = '
<html>
<head>
<style>
body {font-family: sans-serif;
    font-size: 9pt;
}
p {    margin: 0pt;
}
td { vertical-align: top; }

table thead td {
    text-align: center;
    border-top: 0.1mm solid #000000;
	border-bottom: 0.1mm solid #000000;
}
.items td.blanktotal {
    background-color: #FFFFFF;
    border: 0mm none #000000;
    border-top: 0.1mm solid #000000;
    border-right: 0.1mm solid #000000;
}
.items td.totals {
    text-align: right;
    border: 0.1mm solid #000000;
}

.items td {
	border: 0.1em solid black;
}
</style>
</head>
<body>

<!--mpdf
<htmlpageheader name="myheader">
<table width="100%">
	<tr>
		<td width="50%" style="color:#000000;" align=left><span style="font-size: 10pt;"><b>Superior Gas & Equipment Co. of Cebu, Inc.</b><br><span style="font-size: 10px;">Highway Labogon, Mandaue City, Cebu</span></td>
		<td align=right><span style="font-size: 9pt;"><b>EMPLOYEE LEAVE BALANCES</b><br />FISCAL YEAR: '. $fy['fy'] . '</span></td>
</tr></table>
</htmlpageheader>

<htmlpagefooter name="myfooter">
<table style="border-top: 1px solid #000000; font-size: 7pt; width: 100%">
<tr>
<td width="50%" align="left">Page {PAGENO} of {nb}</td>
<td width="50%" align="right" style="font-size:7pt; font-color: #cdcdcd;">Run Date: ' . $now . '</td>
</tr>
</table>
</htmlpagefooter>

<sethtmlpageheader name="myheader" value="on" show-this-page="1" />
<sethtmlpagefooter name="myfooter" value="on" />
mpdf-->
<table class="items" width="100%" style="font-size: 7pt; border-collapse: collapse;" cellpadding="3">
<thead>
	<tr>
		<td width="25%" align=left><b>EMPLOYEE</b></td>
		<td align=center><b>VL CREDITS</b></td>
		<td align=center><b>SL CREDITS</b></td>
		<td align=center><b>VL CONSUMED</b></td>
		<td align=center><b>SL CONSUMED</b></td>
		<td align=center><b>VL BALANCE</b></td>
		<td align=center><b>SL BALANCE</b></td>
	</tr>
</thead>
<tbody>';

	while($row = $_ih->fetch_array(MYSQLI_BOTH)) {
		list($vl_consumed) = $pay->getArray("SELECT ifnull(SUM(`length`),0) AS sil FROM pay_loa WHERE date_from >= '" . $fy['dtf'] . "' and date_to <= '". $fy['dt2'] ."' and w_pay = 'Y' and leave_type = '1' and emp_id = '$row[emp_id]' and file_status != 'Deleted';");
		list($sl_consumed) = $pay->getArray("SELECT ifnull(SUM(`length`),0) AS sil FROM pay_loa WHERE date_from >= '" . $fy['dtf'] . "' and date_to <= '". $fy['dt2'] ."' and w_pay = 'Y' and leave_type = '2' and emp_id = '$row[emp_id]' and file_status != 'Deleted';");

		$html = $html . '<tr>
			<td align="left">('.$row['emp_id'].') ' . $row['emp_name'] . '</td>
			<td align="center">'. $row['vl_credit'] . '</td>
			<td align="center">' . $row['sl_credit'] . '</td>
			<td align="center">'. $vl_consumed . '</td>
			<td align="center">' . $sl_consumed . '</td>
			<td align="center">'. ($row['vl_credit'] - $vl_consumed) . '</td>
			<td align="center">' . ($row['sl_credit'] - $sl_consumed) . '</td>
		</tr>';
			
	}
$html = $html . '
</tbody>
</table>
</body>
</html>
';

$mpdf->WriteHTML($html);
$mpdf->Output(); exit;
exit;
?>