<?php
	session_start();
	include("../lib/mpdf6/mpdf.php");
	include("../handlers/_generics.php");

	$con = new _init;

/* MYSQL QUERIES SECTION */

	$now = date("m/d/Y h:i a");
	$co = $con->getArray("select * from companies where company_id = '1';");
	$_ihead = $con->getArray("select trace_no, mrs_no, lpad(mrs_no,2,0) as rr, date_format(mrs_date,'%m/%d/%Y') as d8, requested_by, if(needed_on!='0000-00-00',date_format(needed_on,'%m/%d/%Y'),'') as nd8, remarks, created_by from mrs_header where mrs_no = '$_REQUEST[mrs_no]' and branch = '$_SESSION[branchid]';");
	$_idetails = $con->dbquery("select item_code, description, qty, unit from mrs_details where mrs_no = '$_REQUEST[mrs_no]' and branch = '$_SESSION[branchid]';");
	$bcode = $_ihead['trace_no'];
	
	list($dCount) = $con->getArray("select count(*) from mrs_details where mrs_no = '$_REQUEST[mrs_no]' and branch = '$_SESSION[branchid]';");
	if($dCount > 10) { $paper = 'Letter'; } else { $paper = 'FOLIO-H'; }


/* END OF SQL QUERIES */

$mpdf=new mPDF('win-1252',$paper,'','',15,15,60,30,10,10);
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
body {font-family: sans-serif; font-size: 8pt; }
td { vertical-align: top; }

table thead td { 
	border-top: 0.1mm solid #000000;
	border-bottom: 0.1mm solid #000000;
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
    border-left: 0.1mm solid #000000; border-top: 0.1mm solid #000000; border-bottom: 0.1mm solid #000000;
}
.items td.tdTotals-r {
    text-align: right; font-weight: bold;
    border-right: 0.1mm solid #000000; border-top: 0.1mm solid #000000; border-bottom: 0.1mm solid #000000;
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
		padding: 3px;
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
    border-left: 0.1mm solid #000000; border-right: 0.1mm solid #000000; border-top: 0.1mm solid #000000;
}

.td-r-head {
	text-align: right; font-weight: bold; padding: 3px;
    border-right: 0.1mm solid #000000; border-top: 0.1mm solid #000000;
}
.td-l-head-bottom {
	text-align: left; font-weight: bold; padding: 3px;
    border-left: 0.1mm solid #000000; border-right: 0.1mm solid #000000; border-top: 0.1mm solid #000000; border-bottom: 0.1mm solid #000000;
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
			<span style="font-weight: bold; font-size: 11pt; color: #000000;">MATERIALS REQUEST SLIP&nbsp;&nbsp;</span><br />
			<barcode size=0.8 code="'.$bcode.'" type="C128A">
		</td>
	</tr>
</table>
<table width="100%" cellspacing=0 cellpadding=0>
<tr>
<td class="billto" width=60% rowspan="6">
	<span style="font-size: 9pt; font-weight: bold;">Requested By:<br/><br/><i>'.$_ihead['requested_by'].'</i></span>
</td>
<td class="td-l-top"><b>PAGE</b></td>
<td class="td-r-top"><b>{PAGENO} of {nb}</b></td>
</tr>
<tr>
<td class="td-l-head"><b>Doc No</b></td>
<td class="td-r-head"><b>' . $_REQUEST['mrs_no'] . '</b></td>
</tr>
<tr>
<td class="td-l-head"><b>Doc Date</b></td>
<td class="td-r-head"><b>' . $_ihead['d8'] . '</b></td>
</tr>
<tr>
<tr>
<td class="td-l-head-bottom"><b>Date Needed</b></td>
<td class="td-r-head-bottom"><b>' . $_ihead['nd8'] . '</b></td>
</tr>
</table>
</htmlpageheader>
<htmlpagefooter name="myfooter">
<table width=100% cellspacing=0 cellpadding=0 style="font-size: 10pt;">
	<tr><td width=150><b>MEMO :</b></td><td style="padding-left: 5px;" align=left>'.$_ihead['remarks'].'</td></tr>
</table>
<table width=100% cellpadding=5 style="font-size: 8pt; border: 1px solid #000000; margin-top: 10px;">
	<tr>
		<td width=33% align=center><b>PREPARED BY:</b><br><br>'.$con->getUname($_ihead['created_by']).'<br></td>
		<td width=33% align=center></td>
		<td width=34% align=center><b>APPROVED BY:</b><br><br>_________________________________<br><font size=3>Printed Name over Signature</font></td>
	</tr>
</table>
<table width=100% style="font-size: 8pt;">
	<tr><td align=left>Page {PAGENO} of {nb}</td><td align=right>Run Date: '.date('m/d/Y h:i:s a').'</td></tr>
</table>
</htmlpagefooter>
<sethtmlpageheader name="myheader" value="on" show-this-page="1" />
<sethtmlpagefooter name="myfooter" value="on" />
mpdf-->
<table class="items" width="100%" style="font-size: 8pt; border-collapse: collapse;" cellpadding="2">
<thead>
	<tr>
		<td width="12%" align=left><b>CODE</b></td>
		<td align=left><b>DESCRIPTION</b></td>
		<td width="10%" align=center><b>UNIT</b></td>
		<td width="10%" align=right><b>QTY</b></td>
	</tr>
</thead>
<tbody>';
	$i = 0;
	while($row =$_idetails->fetch_array()) {
		
		$html = $html . '<tr>
		<td align=left>' .$row['item_code']. '</td>
		<td align=left>' . html_entity_decode($row['description']) . '</td>
		<td align="center">' . $con->identUnit($row['unit']) . '</td>
		<td align="right">' . number_format($row['qty'],2) . '</td>
		</tr>'; $i++; 
	}


	$html = $html .  '<tr><td colspan=4 align=center>* * * * * * * * * * * * * * * * * * * * * * * * * * * * NOTHING FOLLOWS * * * * * * * * * * * * * * * * * * * * * * * * * * * *</td></tr>
					  
	</tbody>
</table>
</body>
</html>
';

$mpdf->WriteHTML($html);
$mpdf->Output(); exit;
exit;

?>