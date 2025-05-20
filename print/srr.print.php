<?php
	session_start();
	//ini_set('display_errors','On');
	require_once '../lib/mpdf6/mpdf.php';
	require_once '../handlers/_generics.php';

	$con = new _init;

/* MYSQL QUERIES SECTION */
	$now = date("m/d/Y h:i a");
	$co = $con->getArray("select * from companies where company_id = '1';");
	$_ihead = $con->getArray("select trace_no, srr_no, lpad(srr_no,6,0) as srr, date_format(srr_date,'%m/%d/%Y') as d8, received_from, received_by, ref_type, ref_no, if(ref_date!='0000-00-00',date_format(ref_date,'%m/%d/%Y'),'') as rd8, amount, remarks from srr_header where srr_no = '$_REQUEST[srr_no]' and branch = '$_SESSION[branchid]';");
	$_idetails = $con->dbquery("select item_code, description, qty, unit, cost, amount, lot_no, if(expiry!='0000-00-00',date_format(expiry,'%m/%d/%Y'),'') as expiry from srr_details where srr_no = '$_REQUEST[srr_no]' and branch = '$_SESSION[branchid]';");
	$bcode = $_ihead['trace_no'];
	
/* END OF SQL QUERIES */

$mpdf=new mPDF('win-1252','FOLIO-H','','',15,15,55,20,10,10);
$mpdf->use_embeddedfonts_1252 = true;    // false is default
$mpdf->SetProtection(array('print'));
$mpdf->SetAuthor("PORT80 Solutions");

if($_REQUEST['rePrint'] == 'Y') {
	$mpdf->SetWatermarkText('Reprinted Copy');
	$mpdf->showWatermarkText = true;
}

$mpdf->SetDisplayMode(60);

$html = '
<html>
<head>
<style>
body {font-family: sans-serif; font-size: 9pt; }
td { vertical-align: top; }

table thead td { 
	border-top: 0.1mm solid #000000;
	border-bottom: 0.1mm solid #000000;
	background-color: #EEEEEE;
    text-align: center;
}

.td-l { border-left: 0.1mm solid #000000; }
.td-r { border-right: 0.1mm solid #000000; }
.empty { border-left: 0.1mm solid #000000; border-right: 0.1mm solid #000000; }

.items td.blanktotal {
    background-color: #FFFFFF;
    border: 0.1mm solid #000000;
}
.items td.totals-l-top {
    text-align: right; font-weight: bold;
    border-left: 0.1mm solid #000000;
	border-top: 0.1mm solid #000000;
}
.items td.totals-r-top {
    text-align: right; font-weight: bold;
    border-right: 0.1mm solid #000000;
	border-top: 0.1mm solid #000000;
}
.items td.totals-l {
    text-align: right; font-weight: bold;
    border-left: 0.1mm solid #000000;
}
.items td.totals-r {
    text-align: right; font-weight: bold;
    border-right: 0.1mm solid #000000;
}

.items td.tdTotals-l {
    text-align: left; font-weight: bold;
    border-left: 0.1mm solid #000000; border-top: 0.1mm solid #000000; border-bottom: 0.1mm solid #000000;  background-color: #EEEEEE;
}
.items td.tdTotals-r {
    text-align: right; font-weight: bold;
    border-right: 0.1mm solid #000000; border-top: 0.1mm solid #000000; border-bottom: 0.1mm solid #000000; background-color: #EEEEEE;
}

.items td.tdTotals-l-1 {
    text-align: left;
    border-top: 0.1mm solid #000000; border-bottom: 0.1mm solid #000000;
}
.items td.tdTotals-r-1 {
    text-align: right;
    border-top: 0.1mm solid #000000; border-bottom: 0.1mm solid #000000;
}

.td-l-top { 	
		background-color: #EEEEEE; padding: 3px;
		text-align: left; font-weight: bold;
		border-left: 0.1mm solid #000000; border-right: 0.1mm solid #000000;
		border-top: 0.1mm solid #000000;
	}
.td-r-top { 
	text-align: right; font-weight: bold; padding: 3px;
    border-right: 0.1mm solid #000000;
	border-top: 0.1mm solid #000000;
}

.td-l-head {
	text-align: left; font-weight: bold; padding: 3px;
    border-left: 0.1mm solid #000000; border-right: 0.1mm solid #000000; border-top: 0.1mm solid #000000; background-color: #EEEEEE;
}

.td-r-head {
	text-align: right; font-weight: bold; padding: 3px;
    border-right: 0.1mm solid #000000; border-top: 0.1mm solid #000000;
}
.td-l-head-bottom {
	text-align: left; font-weight: bold; padding: 3px;
    border-left: 0.1mm solid #000000; border-right: 0.1mm solid #000000; border-top: 0.1mm solid #000000; background-color: #EEEEEE; border-bottom: 0.1mm solid #000000;
}

.td-r-head-bottom {
	text-align: right; font-weight: bold; padding: 3px;
    border-right: 0.1mm solid #000000; border-top: 0.1mm solid #000000; border-bottom: 0.1mm solid #000000;
}

.billto {
	font-size: 12px; vertical-align: top; padding: 3px;
}
</style>
</head>
<body>

<!--mpdf
<htmlpageheader name="myheader">
<table width="100%" cellpadding=0 cellspaing=0>
<tr>
	<td width=75>
		<img src="../images/logosmall.jpg" width=72 height=72 />
	</td>
	<td style="color:#000000; padding-top: 15px;" align=left>
		<span style="font-size: 8pt; font-weight: bold;">'.$co['company_name'].'</span><br/><span style="font-size: 7pt;">'.$co['company_address'].'<br/>Tel # '.$co['tel_no'].'</span>
	</td>
	<td width="40%" align=right>
		<span style="font-weight: bold; font-size: 10pt; color: #000000;">STOCKS RECEIVING RECEIPT&nbsp;&nbsp;</span><br />
		<barcode size=0.8 code="'.$bcode.'" type="C128A">
	</td>
</tr>
</table>

<table width="100%" cellspacing=0 cellpadding=0>
<tr>
<td class="billto" width=60% rowspan="4"></td>
<td class="td-l-top"><b>Doc No.</b></td>
<td class="td-r-top"><b>' .$_ihead['srr']. '</b></td>
</tr>
<tr>
<td class="td-l-head"><b>Doc Date</b></td>
<td class="td-r-head"><b>' . $_ihead['d8'] . '</b></td>
</tr>
<tr>
<td class="td-l-head"><b>Reference #</b></td>
<td class="td-r-head"><b>' . $_ihead['ref_no'] . '</b></td>
</tr>
<tr>
<td class="td-l-head-bottom"><b>Reference Date</b></td>
<td class="td-r-head-bottom"><b>' . $_ihead['rd8'] . '</b></td>
</tr>
</table>
</htmlpageheader>
<htmlpagefooter name="myfooter">
<table width=100% cellpadding=5 style="font-size: 8pt;">
	<tr><td width=12%><b>Remarks :</b></td><td align=left>'.$_ihead['remarks'].'</td></tr>
</table>
<table width=100% cellpadding=5 style="font-size: 8pt; border: 1px solid #000000;">
<tr>
	
	<td width=33% align=center><b>RECEIVED & CHECKED BY:</b><br><br>'.$_ihead['received_by'].'<br/>_________________________________<br><font size=3>Signature over Printed Name</font></td>
	<td width=33% align=center></td>
	<td width=34% align=center><b>APPROVED BY:</b><br><br><br/>_________________________________<br><font size=3>Printed Name over Signature</font></td>
</tr>
</table>
<table width=100%>
	<tr><td align=left>Page {PAGENO} of {nb}</td><td align=right>Run Date: '.date('m/d/Y h:i:s a').'</td></tr>
</table>
</htmlpagefooter>

<sethtmlpageheader name="myheader" value="on" show-this-page="1" />
<sethtmlpagefooter name="myfooter" value="on" />
mpdf-->

<table class="items" width="100%" style="font-size: 8pt; border-collapse: collapse;" cellpadding="1">
<thead>
	<tr>
		<td width="12%" align=left>CODE</td>
		<td align=left>DESCRIPTION</td>
		<td width="10%" align=center><b>UNIT</b></td>
		<td width="10%" align=center><b>LOT NO</b></td>
		<td width="10%" align=center><b>EXPIRY</b></td>
		<td width="10%" align=right><b>QTY</b></td>
	</tr>
</thead>
<tbody>';
$i = 0;
while($row = $_idetails->fetch_array()) {
	
	$html = $html . '<tr>
	<td align=left>' .$row['item_code']. '</td>
	<td align=left>' . $row['description'] . '</td>
	<td align="center">' . $con->identUnit($row['unit']) . '</td>
	<td align="center">' . $row['lot_no'] . '</td>
	<td align="center">' . $row['expiry'] . '</td>
	<td align="right">' . number_format($row['qty'],2) . '</td>
	</tr>'; $i++; $amtGT+=$row['amount'];
}

$html = $html .  '
</tbody>
</table>

</body>
</html>
';

$mpdf->WriteHTML($html);
$mpdf->Output(); exit;
exit;
?>