<?php

    session_start();
    require_once("lib/mpdf6/mpdf.php");
    include("handlers/_generics.php");
    $con = new _init();

    ini_set("max_execution_time",0);
    ini_set("memory_limit",-1);

    $mpdf=new mPDF('win-1252','BARCODE','','',0,0,0,0,0,0);
    $mpdf->use_embeddedfonts_1252 = true;    // false is default
    $mpdf->setAutoTopMargin='stretch';
    $mpdf->setAutoBottomMargin='stretch';
    $mpdf->use_kwt = true;
    $mpdf->SetProtection(array('print'));
    $mpdf->SetAuthor("Cebu Provincial Hospital");

    $a = $con->dbquery("select distinct hpercode from tmp_barcoder where uid = '$_SESSION[userid]';");
    while($b = $a->fetch_array()) {
        
        /* Chemistry Consilidation */
        $c = $con->dbquery("SELECT enccode, orderdate, dotime, hpercode, URLENCODE(patname) as patname, patbday, patage, patgender, dopriority, physician FROM tmp_barcoder WHERE uid = '$_SESSION[userid]' and hpercode = '$b[hpercode]' AND subcategory = '1' GROUP BY subcategory, enccode, dotime");

        while($d = $c->fetch_array()) {
            list($series) = $con->getArray("SELECT CONCAT(DATE_FORMAT(NOW(),'%Y%m'),LPAD(IFNULL(MAX(series+1),1),6,0)) as series FROM (SELECT TRIM(LEADING '0' FROM SUBSTRING(`serialno`,7,7)) AS series FROM lab_samples where LEFT(serialno,6) = DATE_FORMAT(NOW(),'%Y%m')) a");
            $room = $con->identRoom($d['enccode']);
            if($room == '') { 
                list($room) = $con->getArray("select toecode from hospital_dbo.henctr where enccode = '$d[enccode]';");
            }    

      
            $procedure = '';
            $testQuery = $con->dbquery("select if(b.short_description = '',a.description,b.short_description) from tmp_barcoder a left join services_master b on a.proccode = b.ihomis_code where a.uid = '$_SESSION[userid]' and a.enccode = '$d[enccode]' and a.hpercode = '$d[hpercode]' and a.dotime = '$d[dotime]' and a.subcategory = '1';");
            while($testRow = $testQuery->fetch_array()) {
                $procedure .= $testRow[0] . ",";
            }

           /* REGISTER TO LAB SAMPLES */
           $con->dbquery("INSERT IGNORE INTO lab_samples (enccode,dotime,`code`,`procedure`,primecarecode,sampletype,samplecontainer,serialno,hpercode,hpatroom,patientname,physician,extracted,extractdate,extractime,extractby,location,created_on,created_by,is_consolidated,dopriority) SELECT enccode, dotime, proccode AS `code`, a.description AS `procedure`, a.code AS primecarecode, a.sampletype, a.containertype AS samplecontainer, '$series', hpercode, hpatroom, CONCAT(patname ,'/',patage) AS patient_name, physician, 'Y' AS extracted, DATE_FORMAT(NOW(),'%Y-%m-%d') AS extractdate, DATE_FORMAT(NOW(),'%H:%m:00') AS extractime, c.fullname AS extractby, '$room' AS location, NOW() AS created_on, '$_SESSION[uid]' AS created_by,'Y' AS is_consolidated, dopriority FROM tmp_barcoder a LEFT JOIN services_master b ON a.proccode = b.ihomis_code LEFT JOIN user_info c ON a.uid = c.emp_id WHERE a.uid = '$_SESSION[userid]' and a.enccode = '$d[enccode]' and a.hpercode = '$d[hpercode]' and a.dotime = '$d[dotime]' and a.subcategory = '1';");
           
           /* Update as per recommendation by DoH/ICTO to update hdocord everytime a sample is taken */
          $loopQuery_1 = $con->dbquery("SELECT enccode, dotime, proccode FROM tmp_barcoder a WHERE a.uid = '$_SESSION[userid]' AND a.enccode = '$d[enccode]' AND a.hpercode = '$d[hpercode]' AND a.dotime = '$d[dotime]' AND a.subcategory = '1';");
          while(list($enccode,$dotime,$proccode) = $loopQuery_1->fetch_array()) {
               $con->dbquery("UPDATE IGNORE hospital_dbo.hdocord set estatus = 'P' where enccode = '$enccode' and dotime = '$dotime' and proccode = '$proccode';");

                /* Create Log (No data structure where to put audit trail when a record in hdocord is update) */
		        $con->dbquery("INSERT IGNORE into hdocord_log (enccode,dotime,proccode,estatus_update,updated_by,updated_on) values ('$enccode','$dotime','$proccode','P','$_SESSION[userid]',now());");
          }
           
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
                            <td align=left colspan=2>&nbsp;&nbsp;&nbsp;' . substr(urldecode($d['patname']),0,40). ' ' . $d['patgender'] . '/' . $d['patage'] . '</td>
                        </tr>
                        <tr>
                            <td align=left colspan=2>&nbsp;&nbsp;&nbsp;HMR NO.'.$d['hpercode'].'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;DOB: '. $d['patbday'] .'</td>
                        </tr>   
                        <tr><td colspan=2 align=center><barcode code="'. $series . '" type="C128A" height="0.6" size="0.75"></td></tr>
                        <tr>
                            <td align=left>&nbsp;&nbsp;'. date('m/d/Y h:s') . '</td>
                            <td width=30% align=right>'. $series . '&nbsp;&nbsp;</td>
                        </tr>
                        <tr><td colspan=2>&nbsp;&nbsp;&nbsp;Room/Dept: '. $room . '</td></tr>
                        <tr>
                            <td align=left colspan=2>&nbsp;&nbsp;&nbsp;Test(s): ' . substr($procedure,0,-1) . '</td>
                        </tr>     
                    </table>
                </body>
            </html>';

			$endOfPage = $mpdf->page + 1;
		    $html = html_entity_decode($html);
			$mpdf->WriteHTML($html);
			$mpdf->AddPage();

        }

        /* Special Chemistry Consilidation */
        $e = $con->dbquery("SELECT enccode, orderdate, dotime, hpercode, URLENCODE(patname) as patname, patbday, patage, patgender, dopriority, physician FROM tmp_barcoder WHERE uid = '$_SESSION[userid]' and hpercode = '$b[hpercode]' AND subcategory = '8' GROUP BY subcategory, enccode, dotime");
        while($f = $e->fetch_array()) {
            list($series) = $con->getArray("SELECT CONCAT(DATE_FORMAT(NOW(),'%Y%m'),LPAD(IFNULL(MAX(series+1),1),6,0)) as series FROM (SELECT TRIM(LEADING '0' FROM SUBSTRING(`serialno`,7,7)) AS series FROM lab_samples where LEFT(serialno,6) = DATE_FORMAT(NOW(),'%Y%m')) a");
            $room = $con->identRoom($d['enccode']);
            if($room == '') { 
                list($room) = $con->getArray("select toecode from hospital_dbo.henctr where enccode = '$f[enccode]';");
            }    

      
            $procedure = '';
            $testQuery = $con->dbquery("select if(b.short_description = '',a.description,b.short_description) from tmp_barcoder a left join services_master b on a.proccode = b.ihomis_code where a.uid = '$_SESSION[userid]' and a.enccode = '$f[enccode]' and a.hpercode = '$f[hpercode]' and a.dotime = '$f[dotime]' and a.subcategory = '8';");
            while($testRow = $testQuery->fetch_array()) {
                $procedure .= $testRow[0] . ",";
            }

            /* REGISTER TO LAB SAMPLES */
            $con->dbquery("INSERT IGNORE INTO lab_samples (enccode,dotime,`code`,`procedure`,primecarecode,sampletype,samplecontainer,serialno,hpercode,hpatroom,patientname,physician,extracted,extractdate,extractime,extractby,location,created_on,created_by,is_consolidated,dopriority) SELECT enccode, dotime, proccode AS `code`, a.description AS `procedure`, a.code AS primecarecode, a.sampletype, a.containertype AS samplecontainer, '$series', hpercode, hpatroom, CONCAT(patname ,'/',patage) AS patient_name, physician, 'N' AS extracted, DATE_FORMAT(NOW(),'%Y-%m-%d') AS extractdate, DATE_FORMAT(NOW(),'%H:%m:00') AS extractime, c.fullname AS extractby, '$room' AS location, NOW() AS created_on, '$uid' AS created_by, 'N' AS is_consolidated, dopriority FROM tmp_barcoder a LEFT JOIN services_master b ON a.proccode = b.ihomis_code LEFT JOIN user_info c ON a.uid = c.emp_id WHERE a.uid = '$_SESSION[userid]' and a.enccode = '$f[enccode]' and a.hpercode = '$f[hpercode]' and a.dotime = '$f[dotime]' and a.subcategory = '8';");

            /* Update as per recommendation by DoH/ICTO to update hdocord everytime a sample is taken */
            $loopQuery_2 = $con->dbquery("SELECT enccode, dotime, proccode FROM tmp_barcoder a WHERE a.uid = '$_SESSION[userid]' and a.enccode = '$f[enccode]' and a.hpercode = '$f[hpercode]' and a.dotime = '$f[dotime]' and a.subcategory = '8';");
            while(list($enccode,$dotime,$proccode) = $loopQuery_2->fetch_array()) {
                $con->dbquery("UPDATE IGNORE hospital_dbo.hdocord set estatus = 'P' where enccode = '$enccode' and dotime = '$dotime' and proccode = '$proccode';");

                /* Create Log (No data structure where to put audit trail when a record in hdocord is update) */
			    $con->dbquery("INSERT IGNORE into hdocord_log (enccode,dotime,proccode,estatus_update,updated_by,updated_on) values ('$enccode','$dotime','$proccode','P','$_SESSION[userid]',now());");
            }

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
                            <td align=left colspan=2>&nbsp;&nbsp;&nbsp;' . substr(urldecode($f['patname']),0,40) . ' ' . $f['patgender'] . '/' . $f['patage'] . '</td>
                        </tr>
                        <tr>
                            <td align=left colspan=2>&nbsp;&nbsp;&nbsp;HMR NO.'.$f['hpercode'].'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;DOB: '. $f['patbday'] .'</td>
                        </tr>   
                        <tr><td colspan=2 align=center><barcode code="'. $series . '" type="C128A" height="0.6" size="0.75"></td></tr>
                        <tr>
                            <td align=left>&nbsp;&nbsp;'. date('m/d/Y h:s') . '</td>
                            <td width=30% align=right>'. $series . '&nbsp;&nbsp;</td>
                        </tr>
                        <tr><td colspan=2>&nbsp;&nbsp;&nbsp;Room/Dept: '. $room . '</td></tr>
                        <tr>
                            <td align=left colspan=2>&nbsp;&nbsp;&nbsp;Test(s): ' . substr($procedure,0,-1) . '</td>
                        </tr>     
                    </table>
                </body>
            </html>';

			$endOfPage = $mpdf->page + 1;
		    $html = html_entity_decode($html);
			$mpdf->WriteHTML($html);
			$mpdf->AddPage();

        }

        /* Non Chemistry */
        $g = $con->dbquery("select enccode, orderdate, dotime, hpercode, URLENCODE(patname) as patname, patbday, patage, patgender, dopriority, physician, description, proccode from tmp_barcoder where uid = '$_SESSION[userid]' and hpercode = '$b[hpercode]' and subcategory not in (1,8);");
        while($h = $g->fetch_array()) {

            list($series) = $con->getArray("SELECT CONCAT(DATE_FORMAT(NOW(),'%Y%m'),LPAD(IFNULL(MAX(series+1),1),6,0)) as series FROM (SELECT TRIM(LEADING '0' FROM SUBSTRING(`serialno`,7,7)) AS series FROM lab_samples where LEFT(serialno,6) = DATE_FORMAT(NOW(),'%Y%m')) a");
            $room = $con->identRoom($h['enccode']);
            if($room == '') { 
                list($room) = $con->getArray("select toecode from hospital_dbo.henctr where enccode = '$h[enccode]';");
            }

            /* REGISTER TO LAB SAMPLES */
            $con->dbquery("INSERT IGNORE INTO lab_samples (enccode,dotime,`code`,`procedure`,primecarecode,sampletype,samplecontainer,serialno,hpercode,hpatroom,patientname,physician,extracted,extractdate,extractime,extractby,location,created_on,created_by,is_consolidated,dopriority) SELECT enccode, dotime, proccode AS `code`, a.description AS `procedure`, a.code AS primecarecode, a.sampletype, a.containertype AS samplecontainer, '$series', hpercode, hpatroom, CONCAT(patname ,'/',patage) AS patient_name, physician, 'N' AS extracted, DATE_FORMAT(NOW(),'%Y-%m-%d') AS extractdate, DATE_FORMAT(NOW(),'%H:%m:00') AS extractime, c.fullname AS extractby, '$room' AS location, NOW() AS created_on, '$_SESSION[userid]' AS created_by,'N' AS is_consolidated, dopriority FROM tmp_barcoder a LEFT JOIN services_master b ON a.proccode = b.ihomis_code LEFT JOIN user_info c ON a.uid = c.emp_id WHERE a.uid = '$_SESSION[userid]' and a.enccode = '$h[enccode]' and a.hpercode = '$h[hpercode]' and a.dotime = '$h[dotime]' and a.proccode = '$h[proccode]';");
            
            /* Update as per recommendation by DoH/ICTO to update hdocord everytime a sample is taken */
            $loopQuery_3 = $con->dbquery("SELECT enccode, dotime, proccode FROM tmp_barcoder a WHERE a.uid = '$_SESSION[userid]' and a.enccode = '$h[enccode]' and a.hpercode = '$h[hpercode]' and a.dotime = '$h[dotime]' and a.proccode = '$h[proccode]'");
            while(list($enccode,$dotime,$proccode) = $loopQuery_3->fetch_array()) {
                $con->dbquery("UPDATE IGNORE hospital_dbo.hdocord set estatus = 'P' where enccode = '$enccode' and dotime = '$dotime' and proccode = '$proccode';");

                /* Create Log (No data structure where to put audit trail when a record in hdocord is update) */
				$con->dbquery("INSERT IGNORE into hdocord_log (enccode,dotime,proccode,estatus_update,updated_by,updated_on) values ('$enccode','$dotime','$proccode','P','$_SESSION[userid]',now());");
            }

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
                            <td align=left colspan=2>&nbsp;&nbsp;&nbsp;' . substr(urldecode($h['patname']),0,40) . ' ' . $h['patgender'] . '/' . $h['patage'] . '</td>
                        </tr>
                        <tr>
                            <td align=left colspan=2>&nbsp;&nbsp;&nbsp;HMR NO.'.$h['hpercode'].'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;DOB: '. $h['patbday'] .'</td>
                        </tr>   
                        <tr><td colspan=2 align=center><barcode code="'. $series . '" type="C128A" height="0.6" size="0.75"></td></tr>
                        <tr>
                            <td align=left>&nbsp;&nbsp;'. date('m/d/Y h:s') . '</td>
                            <td width=30% align=right>'. $series . '&nbsp;&nbsp;</td>
                        </tr>
                        <tr><td colspan=2>&nbsp;&nbsp;&nbsp;Room/Dept: '. $room . '</td></tr>
                        <tr>
                            <td align=left colspan=2>&nbsp;&nbsp;&nbsp;Test(s): ' . $h['description'] . '</td>
                        </tr>     
                    </table>
                </body>
            </html>';

			$endOfPage = $mpdf->page + 1;
		    $html = html_entity_decode($html);
			$mpdf->WriteHTML($html);
			$mpdf->AddPage();

        }
    }

    $mpdf->DeletePages($endOfPage);
    $filename = "images/labels/" . uniqid() . ".pdf";

    $mpdf->WriteHTML($html);
    $mpdf->Output($filename,'F');

    echo $filename;

    /* Keep Journal for Printed Label for Reprinting Purposes */
    $con->dbquery("insert ignore into lab_labels (printed_by,printed_on,filepath) values ('$_SESSION[userid]',now(),'$filename');");

?>