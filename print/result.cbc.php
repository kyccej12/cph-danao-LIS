<?php
	session_start();
    //ini_set("display_errors","On");
	include("../lib/mpdf6/mpdf.php");
	include("../handlers/_generics.php");

	$con = new _init;

/* MYSQL QUERIES SECTION */
    // $datePrinted = date("m/d/Y");
    // $timePrinted = date('h:i:s a');

    $order = $con->getArray("select *, date_format(extractdate,'%m/%d/%Y') as exdate, date_format(release_date,'%m/%d/%Y') as rdate, dotime from lab_samples where enccode = '$_REQUEST[enccode]' and `code` = 'LABOR00021' and serialno = '$_REQUEST[serialno]';");
   // $a = $con->getArray("SELECT docointkey, a.enccode, SUBSTR(enccode,8,15) AS hmrno, DATE_FORMAT(dodate,'%m/%d/%Y %h:%i %p') AS orderdate, DATE_FORMAT(dodate,'%Y-%m-%d') AS xorderdate, a.hpercode, URLENCODE(concat(c.patlast,', ', c.patfirst,' ', c.patmiddle)) as pname,  DATE_FORMAT(c.patbdate,'%m/%d/%Y') AS bday, DATE_FORMAT(c.patbdate,'%Y-%m-%d') AS xbday, IF(c.patsex='F','FEMALE','MALE') AS sex, c.patsex as gender, a.proccode, b.procdesc, a.donotes AS remarks, a.licno, a.estatus, entby FROM hospital_dbo.hdocord a LEFT JOIN hospital_dbo.hprocm b ON a.proccode = b.proccode LEFT JOIN hospital_dbo.hperson c ON a.hpercode = c.hpercode WHERE a.enccode = '$order[enccode]' and a.dotime = '$order[dotime]' limit 1;");
    $a = $con->getArray("SELECT a.enccode, SUBSTR(enccode,8,15) AS hmrno, DATE_FORMAT(dotime,'%m/%d/%Y %h:%i %p') AS orderdate, DATE_FORMAT(dotime,'%Y-%m-%d') AS xorderdate, a.hpercode, URLENCODE(CONCAT(c.patlast,', ', c.patfirst,' ', c.patmiddle)) AS pname,  DATE_FORMAT(c.patbdate,'%m/%d/%Y') AS bday, DATE_FORMAT(c.patbdate,'%Y-%m-%d') AS xbday, IF(c.patsex='F','FEMALE','MALE') AS sex, c.patsex AS gender, a.code AS proccode, b.procdesc FROM lab_samples a LEFT JOIN hospital_dbo.hprocm b ON a.code = b.proccode LEFT JOIN hospital_dbo.hperson c ON a.hpercode = c.hpercode WHERE a.enccode = '$order[enccode]' AND a.dotime = '$order[dotime]' LIMIT 1;");
    $b = $con->getArray("select *, machine from lab_cbcresult where enccode = '$a[enccode]' and serialno = '$order[serialno]';");

    list($datePrinted, $timePrinted) = $con->getArray("select date_format(printed_on, '%m/%d/%Y') as d8print, date_format(printed_on, '%h:%i:%s %p') as timeprint from lab_samples where enccode = '$_REQUEST[enccode]' and `code` = 'LABOR00021' and serialno = '$_REQUEST[serialno]';");

    $con->calculateAge($a['xorderdate'],$a['xbday']);
	list($pateintAddress) = $con->getArray("SELECT URLENCODE(CONCAT(a.patstr,', ',b.bgyname,', ',c.ctyname,', ',d.provname)) AS addr FROM hospital_dbo.haddr a LEFT JOIN hospital_dbo.hbrgy b ON a.brg = b.bgycode LEFT JOIN hospital_dbo.hcity c ON a.ctycode = c.ctycode LEFT JOIN hospital_dbo.hprov d ON a.provcode = d.provcode WHERE a.hpercode = '$a[hmrno]';");
   
    //list($room) = $con->getArray("SELECT concat(b.wardcode,'-',rmname,'-',bdname) FROM ppp_danao.lab_samples a LEFT JOIN hospital_dbo.hpatroom b ON a.enccode = b.enccode LEFT JOIN hospital_dbo.hroom c ON b.rmintkey = c.rmintkey LEFT JOIN hospital_dbo.hbed d ON b.bdintkey = d.bdintkey WHERE a.enccode = '$a[enccode]';");
    $room = $con->identRoom($a['enccode']);
    if($room == '') { 
        list($room) = $con->getArray("select toecode from hospital_dbo.henctr where enccode = '$order[enccode]';");
    }

    list($encSignature,$encBy,$encByLicense,$encByRole) = $con->getArray("SELECT if(signature_file != '',concat('<img src=\"../images/signatures/',signature_file,'\" align=absmiddle />'),'<img src=\"../images/signatures/blank.png\" align=absmiddle />') as signature, fullname, license_no, role from user_info where emp_id = '$b[created_by]';");

    if($b['verified_by'] != '') {
        list($cbySignature,$cby,$cbyLicense,$cbyRole) = $con->getArray("SELECT if(signature_file != '',concat('<img src=\"../images/signatures/',signature_file,'\" align=absmiddle />'),'<img src=\"../images/signatures/blank.png\" align=absmiddle />') as signature, fullname, license_no, role from user_info where emp_id = '$b[verified_by]';");
    }

    if($b['machine'] == 'GENRUI') {
        $machine = 'GENRUI';
    }else {
        $machine = 'YUMIZEN H500';
    }

/* END OF SQL QUERIES */

$mpdf=new mPDF('win-1252','letter','','',10,10,80,25,5,10);
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
		body {font-family: sans-serif; font-size: 10pt; }
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
        <td><b>Machine :</b></td>
        <td style="border-bottom: 1px solid black; font-weight: bold;">'.$machine.'</td>
        <td><b>&nbsp;</b></td>
        <td>&nbsp;</td>
    </tr>
    <tr>
		<td colspan=4 width="100%" style="padding-top: 20px;" align=center>
			<span style="font-weight: bold; font-size: 12pt; color: #000000;">HEMATOLOGY</span>
		</td>
	</tr>
    <tr>
        <td width=100% colspan=4 style="background-color: #da9694; border-top: 1px solid black; padding: 5px; font-size: 12pt; font-weight: bold;" align=center>COMPLETE BLOOD COUNT</td>
    </tr>    
</table>
</htmlpageheader>

<htmlpagefooter name="myfooter">
    <table width=100% cellpadding=5 style="margin-bottom: 5px; font-size: 11px;">
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

<table width=80% cellpadding=0 cellspacing=5 align=center>
    <tr>
        <td align="left" width=20% style="padding-left: 15px;"><b>PARAMETERS</b></td>	
        <td align=center width=20%><b>RESULT</b></td>
        <td align=center width=20%></td>
        <td align="left" width=40% style="padding-left: 15px;"><b>NORMAL VALUES</b></td>	
    </tr>
    <tr>
        <td align="left" style="padding-left: 15px;">WBC </td>
        <td align=center style="border-bottom: 1px solid black;vertical-align: top;">'. number_format($b['wbc'],2) . '</td>
        <td align=center>'.$con->checkCBCValues($con->age,$a['gender'],"WBC",$b['wbc']).'</td>
        <td align="left" style="padding-left: 15px;">'.$con->getCBCAttribute2($con->age,$a['gender'],"WBC",$order['machine']).'</td>	
    </tr>

    <tr>
        <td align="left" style="padding-left: 15px;" valign=top>RBC </td>
        <td align=center style="border-bottom: 1px solid black;" valign=top>'. $b['rbc'] . '</td>
        <td align=center>'.$con->checkCBCValues($con->age,$a['gender'],"RBC",$b['rbc']).'</td>
        <td align="left" style="padding-left: 15px;" valign=top>'.$con->getCBCAttribute2($con->age,$a['gender'],"RBC",$order['machine']).'</td>	
    </tr>

    <tr>
        <td align="left" style="padding-left: 15px;">Hemoglobin </td>
        <td align=center style="border-bottom: 1px solid black;">'. $b['hemoglobin'] . '</td>
        <td align=center>'.$con->checkCBCValues($con->age,$a['gender'],"HEMOGLOBIN",$b['hemoglobin']).'</td>
        <td align="left" style="padding-left: 15px;">'. $con->getCBCAttribute2($con->age,$a['gender'],"HEMOGLOBIN",$order['machine']) . '</td>	
    </tr>
    <tr>
        <td align="left" style="padding-left: 15px;">Hematocrit</td>
        <td align=center style="border-bottom: 1px solid black;">'. $b['hematocrit'] . '</td>
        <td align=center>'.$con->checkCBCValues($con->age,$a['gender'],"HEMATOCRIT",$b['hematocrit']).'</td>
        <td align="left" style="padding-left: 15px;">'. $con->getCBCAttribute2($con->age,$a['gender'],"HEMATOCRIT",$order['machine']).'</td>	
    </tr>
    <tr><td height=5>&nbsp;</td></tr>
    <tr>
        <td align="left" colspan=3  style="padding-left: 15px;"><b>Differential Count&nbsp;:</b></td>
    </tr>
    <tr>
        <td align="left" style="padding-left: 35px;">Neutrophils </td>
        <td align=center style="border-bottom: 1px solid black;">'. $b['neutrophils'] . '</td>
        <td align=center>'.$con->checkCBCValues($con->age,$a['gender'],"NEUTROPHILS",$b['neutrophils']).'</td>
        <td align="left" style="padding-left: 15px;">'.$con->getCBCAttribute2($con->age,$a['gender'],"NEUTROPHILS",$order['machine']).'</td>	
    </tr>
    <tr>
        <td align="left" style="padding-left: 35px;">Lymphocytes </td>
        <td align=center style="border-bottom: 1px solid black;">'. $b['lymphocytes'] . '</td>
        <td align=center>'.$con->checkCBCValues($con->age,$a['gender'],"LYMPHOCYTES",$b['lymphocytes']).'</td>
        <td align="left" style="padding-left: 15px;">'.$con->getCBCAttribute2($con->age,$a['gender'],"LYMPHOCYTES",$order['machine']).'</td>	
    </tr>
    <tr>
        <td align="left" style="padding-left: 35px;">Monocytes </td>
        <td align=center style="border-bottom: 1px solid black;">'. $b['monocytes'] . '</td>
        <td align=center>'.$con->checkCBCValues($con->age,$a['gender'],"MONOCYTES",$b['monocytes']).'</td>
        <td align="left" style="padding-left: 15px;">'.$con->getCBCAttribute2($con->age,$a['gender'],"MONOCYTES",$order['machine']).'</td>	
    </tr>
    <tr>
        <td align="left" style="padding-left: 35px;">Eosinophils </td>
        <td align=center style="border-bottom: 1px solid black;">'. $b['eosinophils'] . '</td>
        <td align=center>'.$con->checkCBCValues($con->age,$a['gender'],"EOSINOPHILS",$b['eosinophils']).'</td>
        <td align="left" style="padding-left: 15px;">'.$con->getCBCAttribute2($con->age,$a['gender'],"EOSINOPHILS",$order['machine']).'</td>	
    </tr>
    <tr>
        <td align="left" style="padding-left: 35px;">Basophils </td>
        <td align=center style="border-bottom: 1px solid black;">'. $b['basophils'] . '</td>
        <td align=center>'.$con->checkCBCValues($con->age,$a['gender'],"BASOPHILS",$b['basophils']).'</td>
        <td align="left" style="padding-left: 15px;">'.$con->getCBCAttribute2($con->age,$a['gender'],"BASOPHILS",$order['machine']).'</td>	
    </tr>
    <tr>
        <td align="left" style="padding-left: 15px;">MCV </td>
        <td align=center style="border-bottom: 1px solid black;">'. $b['mcv'] . '</td>
        <td align=center>'.$con->checkCBCValues($con->age,$a['gender'],"MCV",$b['mcv']).'</td>
        <td align="left" style="padding-left: 15px;">'.$con->getCBCAttribute2($con->age,$a['gender'],"MCV",$order['machine']).'</td>	
    </tr>
    <tr>
        <td align="left" style="padding-left: 15px;">MCH </td>
        <td align=center style="border-bottom: 1px solid black;">'. $b['mch'] . '</td>
        <td align=center>'.$con->checkCBCValues($con->age,$a['gender'],"MCH",$b['mch']).'</td>
        <td align="left" style="padding-left: 15px;">'.$con->getCBCAttribute2($con->age,$a['gender'],"MCH",$order['machine']).'</td>	
    </tr>
    <tr>
        <td align="left" style="padding-left: 15px;">MCHC </td>
        <td align=center style="border-bottom: 1px solid black;">'. $b['mchc'] . '</td>
        <td align=center>'.$con->checkCBCValues($con->age,$a['gender'],"MCHC",$b['mchc']).'</td>
        <td align="left" style="padding-left: 15px;">'.$con->getCBCAttribute2($con->age,$a['gender'],"MCHC",$order['machine']).'</td>	
    </tr>
    <tr>
        <td align="left" style="padding-left: 15px;">RDW-CV </td>
        <td align=center style="border-bottom: 1px solid black;">'. $b['rdwcv'] . '</td>
        <td align=center>'.$con->checkCBCValues($con->age,$a['gender'],"RDW-CV",$b['rdwcv']).'</td>
        <td align="left" style="padding-left: 15px;">'.$con->getCBCAttribute2($con->age,$a['gender'],"RDW-CV",$order['machine']).'</td>	
    </tr>
    <tr>
        <td align="left" style="padding-left: 15px;">RDW-SD </td>
        <td align=center style="border-bottom: 1px solid black;">'. $b['rdwsd'] . '</td>
        <td align=center>'.$con->checkCBCValues($con->age,$a['gender'],"RDW-SD",$b['rdwsd']).'</td>
        <td align="left" style="padding-left: 15px;">'.$con->getCBCAttribute2($con->age,$a['gender'],"RDW-SD",$order['machine']).'</td>	
    </tr>
    <tr>
        <td align="left" style="padding-left: 15px;">Platelet Count </td>
        <td align=center style="border-bottom: 1px solid black;">'. $b['platelate'] . '</td>
        <td align=center>'.$con->checkCBCValues($con->age,$a['gender'],"PLATELATE",$b['platelate']).'</td>
        <td align="left" style="padding-left: 15px;">'.$con->getCBCAttribute2($con->age,$a['gender'],"PLATELATE",$order['machine']).'</td>	
    </tr>
    <tr>
        <td align="left" style="padding-left: 15px;">MPV </td>
        <td align=center style="border-bottom: 1px solid black;">'. $b['mpv'] . '</td>
        <td align=center>'.$con->checkCBCValues($con->age,$a['gender'],"MPV",$b['mpv']).'</td>
        <td align="left" style="padding-left: 15px;">'.$con->getCBCAttribute2($con->age,$a['gender'],"MPV",$order['machine']).'</td>	
    </tr>
    <tr>
        <td align="left" style="padding-left: 15px;">PDW-CV </td>
        <td align=center style="border-bottom: 1px solid black;">'. $b['pdwcv'] . '</td>
        <td align=center>'.$con->checkCBCValues($con->age,$a['gender'],"PDW-CV",$b['pdwcv']).'</td>
        <td align="left" style="padding-left: 15px;">'.$con->getCBCAttribute2($con->age,$a['gender'],"PDW-CV",$order['machine']).'</td>	
    </tr>
    <tr>
        <td align="left" style="padding-left: 15px;">PDW-SD </td>
        <td align=center style="border-bottom: 1px solid black;">'. $b['pdwsd'] . '</td>
        <td align=center>'.$con->checkCBCValues($con->age,$a['gender'],"PDW-SD",$b['pdwsd']).'</td>
        <td align="left" style="padding-left: 15px;">'.$con->getCBCAttribute2($con->age,$a['gender'],"PDW-SD",$order['machine']).'</td>	
    </tr>
    <tr>
        <td align="left" style="padding-left: 15px;">PCT </td>
        <td align=center style="border-bottom: 1px solid black;">'. $b['pct'] . '</td>
        <td align=center>'.$con->checkCBCValues($con->age,$a['gender'],"PCT",$b['pct']).'</td>
        <td align="left" style="padding-left: 15px;">'.$con->getCBCAttribute2($con->age,$a['gender'],"PCT",$order['machine']).'</td>	
    </tr>
    <tr>
        <td align="left" style="padding-left: 15px;">P-LCC </td>
        <td align=center style="border-bottom: 1px solid black;">'. $b['plcc'] . '</td>
        <td align=center>'.$con->checkCBCValues($con->age,$a['gender'],"P-LCC",$b['plcc']).'</td>
        <td align="left" style="padding-left: 15px;">'.$con->getCBCAttribute2($con->age,$a['gender'],"P-LCC",$order['machine']).'</td>	
    </tr>
    <tr>
        <td align="left" style="padding-left: 15px;">P-LCR </td>
        <td align=center style="border-bottom: 1px solid black;">'. $b['plcr'] . '</td>
        <td align=center>'.$con->checkCBCValues($con->age,$a['gender'],"P-LCR",$b['plcr']).'</td>
        <td align="left" style="padding-left: 15px;">'.$con->getCBCAttribute2($con->age,$a['gender'],"P-LCR",$order['machine']).'</td>	
    </tr>
    <tr>
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
$mpdf->Output();
exit;

?>