<?php
	session_start();
	include("../lib/mpdf6/mpdf.php");
	include("../handlers/_generics.php");

	$o = new _init;

/* MYSQL QUERIES SECTION */
	// $datePrinted = date("m/d/Y");
	// $timePrinted = date('h:i:s a');

	$order = $o->getArray("select *, date_format(extractdate,'%m/%d/%Y') as exdate, date_format(release_date,'%m/%d/%Y') as rdate, dotime from lab_samples where enccode = '$_REQUEST[enccode]' and serialno = '$_REQUEST[serialno]';");
    $a = $o->getArray("SELECT docointkey, a.enccode, SUBSTR(enccode,8,15) AS hmrno, DATE_FORMAT(dodate,'%m/%d/%Y %h:%i %p') AS orderdate, DATE_FORMAT(dodate,'%Y-%m-%d') AS xorderdate, a.hpercode, concat(URLENCODE(c.patlast),', ', URLENCODE(c.patfirst),' ', URLENCODE(c.patmiddle)) as pname,  DATE_FORMAT(c.patbdate,'%m/%d/%Y') AS bday, DATE_FORMAT(c.patbdate,'%Y-%m-%d') AS xbday, IF(c.patsex='F','FEMALE','MALE') AS sex, c.patsex as gender, a.proccode, b.procdesc, a.donotes AS remarks, a.licno, a.estatus, entby FROM hospital_dbo.hdocord a LEFT JOIN hospital_dbo.hprocm b ON a.proccode = b.proccode LEFT JOIN hospital_dbo.hperson c ON a.hpercode = c.hpercode WHERE a.enccode = '$order[enccode]' and a.dotime = '$order[dotime]';");
    $b = $o->getArray("select * from lab_uaresult where enccode = '$order[enccode]' and serialno= '$order[serialno]';");

	//$c = $o->getArray("select date_format(verified_on, '%m/%d/%Y') as vdate from lab_uaresult where enccode = '$order[enccode]' and serialno= '$order[serialno]';");

	$o->calculateAge($a['xorderdate'],$a['xbday']);


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

/* END OF SQL QUERIES */

$mpdf=new mPDF('win-1252','letter','','',10,10,95,30,5,10);
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
<table align=center width=90% cellpadding=2 cellspacing=0 style="font-size: 8pt;margin-top:20px;">
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
			<span style="font-weight: bold; font-size: 12pt; color: #000000;">CLINICAL MICROSCOPY</span>
		</td>
	</tr>
    <tr>
		<td width=100% colspan=4 style="background-color: #ffff00; border-top: 1px solid black; padding: 5px; font-size: 12pt; font-weight: bold;" align=center>URINALYSIS</td>
	</tr>  
</table>

</htmlpageheader>

<htmlpagefooter name="myfooter">
    <table width=100% cellpadding=5 style="margin-bottom: 5px;">
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

	<table width=80% cellpadding=0 cellspacing=5 align=center style="font-size: 8pt;">
		<tr>
			<td align="left" colspan=5><b>MACROSCOPIC EXAMINATION&nbsp;:</b></td>
		</tr>
		<tr>
			<td align="left" colspan=5 height=5>&nbsp;</td>
		</tr>
		<tr>
			<td width=20% align="left">Color :</td>
			<td width=20% align=center style="border-bottom: 1px solid black;vertical-align: top;font-weight: bold;">'. $b['color'] . '</td>
			<td width=20% align="left"></td>
			<td width=15% align="left">Transparency :</td>
			<td width=20% align=center style="border-bottom: 1px solid black;vertical-align: top;font-weight: bold;">'. $b['transparency'] . '</td>	
		</tr>
		<tr>
			<td align="left" colspan=5 height=5>&nbsp;</td>
		</tr>
		<tr>
			<td align="left" colspan=5><b>CHEMICAL EXAMINATION&nbsp;:</b></td>
		</tr>
		<tr>
			<td align="left" colspan=5 height=5>&nbsp;</td>
		</tr>
		<tr>
			<td width=20% align="left">Glucose :</td>
			<td width=20% align=center style="border-bottom: 1px solid black;vertical-align: top;font-weight: bold;">'. $b['glucose'] . '</td>
			<td width=20% align="left"></td>
			<td width=15% align="left">pH :</td>
			<td width=20% align=center style="border-bottom: 1px solid black;vertical-align: top;font-weight: bold;">'. $b['ph'] . '</td>	
		</tr>
		<tr>
			<td width=20% align="left">Bilirubin :</td>
			<td width=20% align=center style="border-bottom: 1px solid black;vertical-align: top;font-weight: bold;">'. $b['bilirubin'] . '</td>
			<td width=20% align="left"></td>
			<td width=15% align="left">Protein :</td>
			<td width=20% align=center style="border-bottom: 1px solid black;vertical-align: top;font-weight: bold;">'. $b['protein'] . '</td>	
		</tr>
		<tr>
			<td width=20% align="left">Ketone :</td>
			<td width=20% align=center style="border-bottom: 1px solid black;vertical-align: top;font-weight: bold;">'. $b['ketone'] . '</td>
			<td width=20% align="left"></td>
			<td width=15% align="left">Urobilinogen :</td>
			<td width=20% align=center style="border-bottom: 1px solid black;vertical-align: top;font-weight: bold;">'. $b['urobilinogen'] . '</td>	
		</tr>
		<tr>
			<td width=20% align="left">Specific Gravity :</td>
			<td width=20% align=center style="border-bottom: 1px solid black;vertical-align: top;font-weight: bold;">'. $b['gravity'] . '</td>
			<td width=20% align="left"></td>
			<td width=15% align="left">Nitrite :</td>
			<td width=20% align=center style="border-bottom: 1px solid black;vertical-align: top;font-weight: bold;">'. $b['nitrite'] . '</td>	
		</tr>
		<tr>
			<td width=20% align="left">Blood :</td>
			<td width=20% align=center style="border-bottom: 1px solid black;vertical-align: top;font-weight: bold;">'. $b['blood'] . '</td>
			<td width=20% align="left"></td>
			<td width=15% align="left">Leukocyte :</td>
			<td width=20% align=center style="border-bottom: 1px solid black;vertical-align: top;font-weight: bold;">'. $b['leukocyte'] . '</td>	
		</tr>
		<tr>
			<td align="left" colspan=5 height=5>&nbsp;</td>
		</tr>
		<tr>
			<td align="left" colspan=5><b>MICROSCOPIC EXAMINATION&nbsp;:</b></td>
		</tr>
		<tr>
			<td align="left" colspan=5 height=5>&nbsp;</td>
		</tr>
		<tr>
			<td width=20% align="left">PUS Cells :</td>
			<td width=25% align=center style="border-bottom: 1px solid black;vertical-align: top;font-weight: bold;">'. $b['pus'] . ' / HPF</td>
			<td width=20% align="left"></td>
			<td width=15% align="left">Casts :</td>
			<td width=25% align=left style="border-bottom: 1px solid black;vertical-align: top;font-weight: bold;">'. $b['casts'] . '</td>	
		</tr>
		<tr>
			<td width=20% align="left">Red Blood Cells :</td>
			<td width=25% align=center style="border-bottom: 1px solid black;vertical-align: top;font-weight: bold;">'. $b['rbc_hpf'] . ' / HPF</td>
			<td width=20% align="left"></td>
			<td width=15% align="left"></td>';
			if($b['casts1'] != '') {
		$html .=	'<td width=25% align=left style="border-bottom: 1px solid black;vertical-align: top;font-weight: bold;">'. $b['casts1'] . '</td>'; 
			}
		$html .= '</tr>
		<tr>
			<td width=20% align="left">Epithelial Cells:</td>
			<td width=25% align=center style="border-bottom: 1px solid black;vertical-align: top;font-weight: bold;">'. $b['epith'] . '</td>
			<td width=20% align="left"></td>
			<td width=15% align="left"></td>';
			if($b['casts2'] != '') {
		$html .=	'<td width=25% align=left style="border-bottom: 1px solid black;vertical-align: top;font-weight: bold;">'. $b['casts2'] . '</td>'; 
			}
		$html .= '</tr>
		<tr>
			<td width=20% align="left">Mucus Threads:</td>
			<td width=25% align=center style="border-bottom: 1px solid black;vertical-align: top;font-weight: bold;">'. $b['mucus_thread'] . '</td>
			<td width=20% align="left"></td>
			<td width=15% align="left"></td>';
			if($b['casts3'] != '') {
		$html .=	'<td width=25% align=left style="border-bottom: 1px solid black;vertical-align: top;font-weight: bold;">'. $b['casts3'] . '</td>'; 
			}
		$html .= '</tr>
		<tr>
			<td width=20% align="left">Bacteria :</td>
			<td width=25% align=center style="border-bottom: 1px solid black;vertical-align: top;font-weight: bold;">'. $b['bacteria'] . '</td>
			<td width=20% align="left"></td>
			<td width=15% align="left"></td>';
			if($b['casts4'] != '') {
		$html .=	'<td width=25% align=left style="border-bottom: 1px solid black;vertical-align: top;font-weight: bold;">'. $b['casts4'] . '</td>'; 
			}
		$html .= '</tr>
		<tr>
			<td width=20% align="left">Amorphous Urates :</td>
			<td width=25% align=center style="border-bottom: 1px solid black;vertical-align: top;font-weight: bold;">'. $b['amorphous_urates'] . '</td>
			<td width=20% align="left"></td>
			<td width=15% align="left"></td>';
			if($b['casts5'] != '') {
		$html .=	'<td width=25% align=left style="border-bottom: 1px solid black;vertical-align: top;font-weight: bold;">'. $b['casts5'] . '</td>'; 
			}
		$html .= '</tr>
		<tr>
			<td width=20% align="left">Amorphous Phosphate :</td>
			<td width=25% align=center style="border-bottom: 1px solid black;vertical-align: top;font-weight: bold;">'. $b['amorphous_po4'] . '</td>
			<td width=20% align="left"></td>
			<td width=15% align="left"></td>';
			if($b['casts6'] != '') {
		$html .=	'<td width=25% align=left style="border-bottom: 1px solid black;vertical-align: top;font-weight: bold;">'. $b['casts6'] . '</td>'; 
			}
		$html .= '</tr>
		<tr>
			<td width=20% align="left"><b>Others :</b></td>
			<td width=25% align=center style="border-bottom: 1px solid black;vertical-align: top;font-weight: bold;">'. $b['others'] . '</td>
			<td width=20% align="left"></td>
			<td width=15% align="left">Crystals :</td>
			<td width=25% align=left style="border-bottom: 1px solid black;vertical-align: top;font-weight: bold;">'. $b['crystals'] . '</td>	
		</tr>
		<tr>
			<td width=20% align="left"></td>
			<td width=25% align=center></td>
			<td width=20% align="left"></td>
			<td width=15% align="left"></td>';
			if($b['crystals1'] != '') {
		$html .=	'<td width=25% align=left style="border-bottom: 1px solid black;vertical-align: top;font-weight: bold;">'. $b['crystals1'] . '</td>'; 
			}
		$html .= '</tr>
		<tr>
			<td width=20% align="left"></td>
			<td width=25% align=center></td>
			<td width=20% align="left"></td>
			<td width=15% align="left"></td>';
			if($b['crystals2'] != '') {
		$html .=	'<td width=25% align=left style="border-bottom: 1px solid black;vertical-align: top;font-weight: bold;">'. $b['crystals2'] . '</td>'; 
			}
		$html .= '</tr>
		<tr>
			<td width=20% align="left"></td>
			<td width=25% align=center></td>
			<td width=20% align="left"></td>
			<td width=15% align="left"></td>';
			if($b['crystals3'] != '') {
		$html .=	'<td width=25% align=left style="border-bottom: 1px solid black;vertical-align: top;font-weight: bold;">'. $b['crystals3'] . '</td>'; 
			}
		$html .= '</tr>
		<tr>
			<td width=20% align="left"></td>
			<td width=25% align=center></td>
			<td width=20% align="left"></td>
			<td width=15% align="left"></td>';
			if($b['crystals4'] != '') {
		$html .=	'<td width=25% align=left style="border-bottom: 1px solid black;vertical-align: top;font-weight: bold;">'. $b['crystals4'] . '</td>'; 
			}
		$html .= '</tr>
		<tr>
			<td align="center" colspan=5 style="padding-top: 10px;"><b>END OF RESULT. NOTHING FOLLOWS</b></td>
		</tr>
		<tr>
			<td align="left" colspan=5 style="border-top: 1px solid black; margin-top: 20px;"><b>Remarks:</b> '.$b['remarks'].'</td>
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