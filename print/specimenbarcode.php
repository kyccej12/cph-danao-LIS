<?php
    session_start();
	require_once("../lib/mpdf6/mpdf.php");
	require_once("../handlers/initDB.php");

    $con = new myDB;
    $serialno = $_REQUEST['id'];
    $a = $con->getArray("SELECT enccode, SUBSTR(enccode,8,15) AS hmrno, patientname as pname, `procedure`, CONCAT(DATE_FORMAT(extractdate,'%m/%d/%y'),' ',TIME_FORMAT(extractime,'%h:%i %p')) AS tstamp,`location` FROM lab_samples where serialno = '$serialno';");
    
    list($room) = $con->getArray("SELECT concat(b.wardcode,'-',rmname,'-',bdname) FROM ppp_danao.lab_samples a LEFT JOIN hospital_dbo.hpatroom b ON a.enccode = b.enccode LEFT JOIN hospital_dbo.hroom c ON b.rmintkey = c.rmintkey LEFT JOIN hospital_dbo.hbed d ON b.bdintkey = d.bdintkey WHERE a.enccode = '$a[enccode]' limit 1;");
    if($room == '') { 
        list($room) = $con->getArray("select toecode from hospital_dbo.henctr where enccode = '$order[enccode]';");
    }
    
    //list($room) = $con->getArray("SELECT concat(b.wardcode,'-',rmname,'-',bdname) FROM ppp_danao.lab_samples a LEFT JOIN hospital_dbo.hpatroom b ON a.enccode = b.enccode LEFT JOIN hospital_dbo.hroom c ON b.rmintkey = c.rmintkey LEFT JOIN hospital_dbo.hbed d ON b.bdintkey = d.bdintkey WHERE a.enccode = '$a[enccode]';");
    //if($room == '') { list($room) = $con->getArray("select `location` from lab_locations where id = '$a[location]';"); }
    
    list($dob) = $con->getArray("select date_format(patbdate,'%m/%d/%Y') as dob from hospital_dbo.hperson where hpercode = '$a[hmrno]';");
    
    list($testCount) = $con->getArray("select count(*) from lab_samples where serialno = '$serialno';");
    if($testCount > 1) {
        $procedure = '';
        $testQuery = $con->dbquery("select if(b.short_description = '',a.procedure,b.short_description) from lab_samples a left join services_master b on a.code = b.ihomis_code where serialno = '$serialno';");
        while($testRow = $testQuery->fetch_array()) {
            $procedure .= $testRow[0] . ",";
        }
        $procedure = substr($procedure,0,-1);
    } else { $procedure = $a['procedure']; }



    $mpdf=new mPDF('win-1252','BARCODE','','',0,0,0,0,0,0);
    $mpdf->use_embeddedfonts_1252 = true;    // false is default
    $mpdf->setAutoTopMargin='stretch';
    $mpdf->setAutoBottomMargin='stretch';
    $mpdf->use_kwt = true;
    $mpdf->SetProtection(array('print'));
    $mpdf->SetAuthor("Opon Medical Diagnostic Corporation");
    $mpdf->SetDisplayMode(100);

    $html = '<html>
                <head>
                    <title>Specimen Barcode</title>
                    <style>
                        body {
                            font-family: arial;
                            font-size: 5.5pt;
                        }    
                    </style>
                </head>
                <body>
                    <table width=100% cellpadding=0 cellspacing=0  style="font-weight: bold;">
                        <tr>
                            <td align=left colspan=2>&nbsp;&nbsp;&nbsp;'.substr($a['pname'],0,40).'</td>
                        </tr>
                        <tr>
                            <td align=left colspan=2>&nbsp;&nbsp;&nbsp;HMR NO.'.$a['hmrno'].'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;DOB: '.$dob.'</td>
                        </tr>   
                        <tr><td colspan=2 align=center><barcode code="'.$serialno.'" type="C128A" height="0.6" size="0.75"></td></tr>
                        <tr>
                            <td align=left>&nbsp;&nbsp;'.$a['tstamp'].'</td>
                            <td width=30% align=right>'. $serialno . '&nbsp;&nbsp;</td>
                        </tr>
                        <tr><td colspan=2>&nbsp;&nbsp;&nbsp;Room/Dept: '.$room.'</td></tr>
                        <tr>
                            <td align=left colspan=2>&nbsp;&nbsp;&nbsp;Test(s): '.$procedure.'</td>
                        </tr>     
                    </table>
                </body>
            </html>';


$filename = "../images/qrcodes/" . $serialno . ".pdf";

 $mpdf->WriteHTML($html);
 $mpdf->Output($filename,'F');
 exit;
?>