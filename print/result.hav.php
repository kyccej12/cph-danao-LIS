<?php
	session_start();
	include("../lib/mpdf6/mpdf.php");
	include("../handlers/_generics.php");

	$o = new _init;

/* MYSQL QUERIES SECTION */
	
    // $datePrinted = date("m/d/Y");
    // $timePrinted = date('h:i:s a');

	$order = $o->getArray("select *, date_format(extractdate,'%m/%d/%Y') as exdate, date_format(release_date,'%m/%d/%Y') as rdate, dotime from lab_samples where enccode = '$_REQUEST[enccode]' and serialno = '$_REQUEST[serialno]';");
    $a = $o->getArray("SELECT docointkey, a.enccode, SUBSTR(enccode,8,15) AS hmrno, DATE_FORMAT(dodate,'%m/%d/%Y %h:%i %p') AS orderdate, DATE_FORMAT(dodate,'%Y-%m-%d') AS xorderdate, a.hpercode, URLENCODE(concat(c.patlast,', ', c.patfirst,' ', c.patmiddle)) as pname,  DATE_FORMAT(c.patbdate,'%m/%d/%Y') AS bday, DATE_FORMAT(c.patbdate,'%Y-%m-%d') AS xbday, IF(c.patsex='F','FEMALE','MALE') AS sex, c.patsex as gender, a.proccode, b.procdesc, a.donotes AS remarks, a.licno, a.estatus, entby FROM hospital_dbo.hdocord a LEFT JOIN hospital_dbo.hprocm b ON a.proccode = b.proccode LEFT JOIN hospital_dbo.hperson c ON a.hpercode = c.hpercode WHERE a.enccode = '$order[enccode]' and a.dotime = '$order[dotime]';");
    $b = $o->getArray("select * from lab_havresult where enccode = '$order[enccode]' and serialno = '$order[serialno]' and `code` = '$order[code]';");
    $o->calculateAge($a['xorderdate'],$a['xbday']);
    list($scat,$code,$title) = $o->getArray("SELECT b.subcategory, a.code, if(a.report_title = '',a.description,a.report_title) as title FROM services_master a LEFT JOIN options_servicesubcat b ON a.subcategory = b.id WHERE a.ihomis_code = '$order[code]';");
    list($pateintAddress) = $o->getArray("SELECT URLENCODE(CONCAT(a.patstr,', ',b.bgyname,', ',c.ctyname,', ',d.provname)) AS addr FROM hospital_dbo.haddr a LEFT JOIN hospital_dbo.hbrgy b ON a.brg = b.bgycode LEFT JOIN hospital_dbo.hcity c ON a.ctycode = c.ctycode LEFT JOIN hospital_dbo.hprov d ON a.provcode = d.provcode WHERE a.hpercode = '$a[hmrno]';");    
    
    list($datePrinted, $timePrinted) = $o->getArray("select date_format(printed_on, '%m/%d/%Y') as d8print, date_format(printed_on, '%h:%i:%s %p') as timeprint from lab_samples where enccode = '$_REQUEST[enccode]' and serialno = '$_REQUEST[serialno]';");

    //list($room) = $o->getArray("SELECT concat(b.wardcode,'-',rmname,'-',bdname) FROM ppp_danao.lab_samples a LEFT JOIN hospital_dbo.hpatroom b ON a.enccode = b.enccode LEFT JOIN hospital_dbo.hroom c ON b.rmintkey = c.rmintkey LEFT JOIN hospital_dbo.hbed d ON b.bdintkey = d.bdintkey WHERE a.enccode = '$a[enccode]' limit 1;");
    $room = $o->identRoom($a['enccode']);
    if($room == '') {  
        list($room) = $o->getArray("select toecode from hospital_dbo.henctr where enccode = '$order[enccode]';");
    }
    
    list($encSignature,$encBy,$encByLicense,$encByRole) = $o->getArray("SELECT if(signature_file != '',concat('<img src=\"../images/signatures/',signature_file,'\" align=absmiddle />'),'<img src=\"../images/signatures/blank.png\" align=absmiddle />') as signature, fullname, license_no, role from user_info where emp_id = '$b[created_by]';");
    if($b['verified_by'] != '') {
        list($cbySignature,$cby,$cbyLicense,$cbyRole) = $o->getArray("SELECT if(signature_file != '',concat('<img src=\"../images/signatures/',signature_file,'\" align=absmiddle />'),'<img src=\"../images/signatures/blank.png\" align=absmiddle />') as signature, fullname, license_no, role from user_info where emp_id = '$b[verified_by]';");
    }


    $patterns = array(
        'POSITIVE',
        'REACTIVE',
    );

    if($code == 'L037' || $code == 'L034') {
        $title_header = "ANTI HEPATITIS A VIRUS IgG/IgM QUALITATIVE SCREENING TEST";
    }
    

/* END OF SQL QUERIES */

$mpdf=new mPDF('win-1252','LETTER','','',5,5,80,5,5,10);
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
            padding:10px;border:1px solid black;text-align: center;
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
<table width=100% cellpadding=2 cellspacing=0 style="font-size: 8pt;margin-top:10px;">
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
        <td style="border-bottom: 1px solid black; font-weight: bold;">' . $o->ageDisplay . "/" . $a['sex']. '</td>
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
			<span style="font-weight: bold; font-size: 12pt; color: #000000;">'.strtoupper($scat).'</span>
		</td>
	</tr>
	<tr>
		<td width=100% colspan=4 style="background-color: #b1a0c7; border-top: 1px solid black; padding: 5px; font-size:11pt;" align=center><b>'. $title_header . '&nbsp;</b></td>
	</tr>    
</table>

</htmlpageheader>

<htmlpagefooter name="myfooter">
<table width=90% align=center style="margin-top: 5px; font-size: 9pt; font-style: italic;">
<tr><td height=50>&nbsp;</td></tr>
    <tr>
        <td align="center" style="padding-top: 10px;"><b>END OF RESULT. NOTHING FOLLOWS</b></td>
    </tr> 
    <tr>
        <td align="left" style="border-top: 1px solid black; margin-top: 20px;"><b>Remarks:</b> '.$b['remarks'].'</td>
    </tr>
</table>
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
    <table width=90% cellpadding=0 cellspacing=0 align=center style="margin: 5px;">
        <tr><td></td><td align=center><span style="font-size: 12pt; font-weight: bold;">'.$procedure.'</span></td></tr>
    </table>
    <table width=90% cellpadding=0 cellspacing=0 align=center>
        <tr>
            <td width=20% style="border-top: 1px solid black; border-bottom: 1px solid black; font-weight: bold; padding: 3px;"></td>
            <td width=30% style="border-top: 1px solid black; border-bottom: 1px solid black; font-weight: bold; padding: 3px;">TEST</td>
            <td width=30% style="border-top: 1px solid black; border-bottom: 1px solid black; font-weight: bold; padding: 3px;">RESULT</td>
            <td width=20% style="border-top: 1px solid black; border-bottom: 1px solid black; font-weight: bold; padding: 3px;"></td>
        </tr>
        <tr><td height=5>&nbsp;</td></tr>
        <tr>
            <td width=20%></td>
            <td width=30%>ANTI-HAV IgM</td>
            <td width=30%><b>'.$b['hav_result_igm'].'</b></td>
            <td width=20%></td>
        </tr>
        <tr><td height=5></td></tr>
        <tr>
            <td width=20%></td>
            <td width=30%>ANTI-HAV IgG</td>
            <td width=30%><b>'.$b['hav_result_igg'].'</b></td>
            <td width=20%></td>
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