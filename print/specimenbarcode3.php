<?php
    session_start();
	require_once("../lib/mpdf6/mpdf.php");
	require_once("../handlers/_generics.php");
    require_once "../lib/phpqrcode/qrlib.php";   

    $PNG_WEB_DIR = '../images/qrcodes/';

    $con = new _init();
    $serialno = $_GET['id'];
    $a = $con->getArray("SELECT enccode, SUBSTR(enccode,8,15) AS hmrno, patientname as pname, `procedure`, CONCAT(DATE_FORMAT(extractdate,'%m/%d/%y'),' ',TIME_FORMAT(extractime,'%h:%i %p')) AS tstamp,`location`, DATE_FORMAT(extractdate,'%Y-%m-%d') as xorderdate FROM lab_samples where serialno = '$serialno';");
    list($room) = $con->getArray("SELECT concat(b.wardcode,'-',rmname,'-',bdname) FROM ppp_danao.lab_samples a LEFT JOIN hospital_dbo.hpatroom b ON a.enccode = b.enccode LEFT JOIN hospital_dbo.hroom c ON b.rmintkey = c.rmintkey LEFT JOIN hospital_dbo.hbed d ON b.bdintkey = d.bdintkey WHERE a.enccode = '$a[enccode]';");
    if($room == '') { list($room) = $con->getArray("select `location` from lab_locations where id = '$a[location]';"); }
    
    list($dob,$patname,$patsex,$patdob,$qrdob) = $con->getArray("select date_format(patbdate,'%m/%d/%Y') as dob, concat(patlast,', ',patfirst,', ',patmiddle) as patname, patsex, DATE_FORMAT(patbdate,'%Y-%m-%d') AS xdob, DATE_FORMAT(patbdate,'%m-%d-%Y') AS qrdob from hospital_dbo.hperson where hpercode = '$a[hmrno]';");
    $con->calculateAge($a['xorderdate'],$patdob);

    list($testCount) = $con->getArray("select count(*) from lab_samples where serialno = '$serialno';");
    if($testCount > 1) {
        $procedure = '';
        $testQuery = $con->dbquery("select if(b.short_description = '',a.procedure,b.short_description) from lab_samples a left join services_master b on a.code = b.ihomis_code where serialno = '$serialno';");
        while($testRow = $testQuery->fetch_array()) {
            $procedure .= $testRow[0] . ",";
        }
        $procedure = substr($procedure,0,-1);
    } else { $procedure = $a['procedure']; }


    $filename = $PNG_WEB_DIR.$serialno.'.png';
	$errorCorrectionLevel = 'L';
	$matrixPointSize = 2;

	$bday = explode("-",$qrdob);

    QRcode::png(chr(9).$serialno.chr(9).ltrim($a['hmrno'],'0').chr(9).$patname.chr(9).$patsex.chr(9).$bday[0].chr(9).$bday[1].chr(9).$bday[2], $filename, $errorCorrectionLevel, $matrixPointSize, 2);


    $mpdf=new mPDF('win-1252','BARCODE','','',0,0,0,0,0,0);
    $mpdf->use_embeddedfonts_1252 = true;    // false is default
    $mpdf->setAutoTopMargin='stretch';
    $mpdf->setAutoBottomMargin='stretch';
    $mpdf->use_kwt = true;
    $mpdf->SetProtection(array('print'));
    $mpdf->SetAuthor("CPH Danao");
    $mpdf->SetDisplayMode(100);

    $html = '<html>
                <head>
                    <title>Specimen Barcode</title>
                    <style>
                        body {
                            font-family: "Arial Narrow", Arial, Sans-Serif;
                            font-size: 6pt;
                        }    
                    </style>
                </head>
                <body>
                    <table width=100% cellpadding=0 cellspacing=0  style="margin-left: 5px;">
                        <tr>
                            <td align=left colspan=5><b>'.$patname .'</b></td>
                        </tr>
                        <tr>
                            <td align=left>HMR #</td>
                            <td>:&nbsp;&nbsp;'.ltrim($a['hmrno'],'0').'</td>
                            <td align=left>SEX</td>
                            <td>:&nbsp;&nbsp;'.$patsex.'</td>
                            <td rowspan=4 align=center valign=top><b>'.substr($serialno,0,6).'</b><br/><img src="'.$filename.'" width=48 height=48 align=absmiddle><br/><b>'.substr($serialno,6,6).'</b></td>
                        </tr>   
                        <tr>
                            <td align=left>DOB</td>
                            <td>:&nbsp;&nbsp;'.$patdob.'</td>
                            <td align=left>AGE</td>
                            <td>:&nbsp;&nbsp;'.$con->ageDisplay.'</td>
                        </tr>
                        <tr>
                            <td align=left>WARD</td>
                            <td colspan=3>:&nbsp;&nbsp;'.$room.'</td>
                            
                        </tr>
                        <tr>
                            <td align=left>TESTS</td>
                            <td colspan=3>:&nbsp;&nbsp;'.$procedure.'</td>
                        </tr>     
                    </table>
                </body>
            </html>';

 $mpdf->WriteHTML($html);
 $mpdf->Output();
 exit;
?>