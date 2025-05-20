<?php

    include("../handlers/initDB.php");

    $con = new myDB;

    function decodeDate($dateString) {
        return substr($dateString,0,4) . "-" . substr($dateString,4,2) . "-" . substr($dateString,6,2);
    }

    function generateRandomString($length = 32) {
        return substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/strlen($x)) )),1,$length);
    }


    $dir = 'LOG';
    $content = scandir($dir, 1);

    foreach($content as $hfile) {

        $tfile = explode(".",$hfile);

        if($tfile[1] == 'log') {

            $file = "LOG/$hfile";
            $handle = fopen($file, "r");
            $read = file_get_contents($file); 
            $lines = explode("\r", $read);
            $traceno = generateRandomString();
            $updateString = '';
            $i = 0;

            foreach($lines as $key => $value) {

                $value = preg_replace('/[\x00-\x1F\x7F-\xFF]/', '', $value);
                $cols[$i] = explode("|", $value);
            
                if($cols[$i][0] == '1H') { $date = decodeDate($cols[$i][13]); }
                if($cols[$i][0] == "3O") {
                            
                    $specimenID = $cols[$i][2];
                
                    if($specimenID != '') {
                        $con->dbquery("INSERT IGNORE INTO ppp_danao.lab_cbcresult_temp (serialno,result_date,`machine`,traceno) VALUES ('$specimenID','$date','H500','$traceno');");
                    }
                }
                
                if(substr($cols[$i][0],-1) == "R") {
                    $segmentValue = explode("^",$cols[$i][2]);
                
                    switch($segmentValue[3]) {
                        case "WBC":
                            $updateString .= ",wbc = '".$cols[$i][3]."'";
                        break;
                        case "RBC":
                            $updateString .= ",rbc = '".$cols[$i][3]."'";
                        break;
                        case "HGB":
                            $updateString .= ",hemoglobin = '".$cols[$i][3]."'";
                        break;
                        case "HCT":
                            $updateString .= ",hematocrit = '".$cols[$i][3]."'";
                        break;
                        case "NEU%":
                            $updateString .= ",neutrophils = '".$cols[$i][3]."'";
                        break;
                        case "LYM%":
                            $updateString .= ",lymphocytes = '".$cols[$i][3]."'";
                        break;
                        case "MON%":
                            $updateString .= ",monocytes = '".$cols[$i][3]."'";
                        break;
                        case "EOS%":
                            $updateString .= ",eosinophils = '".$cols[$i][3]."'";
                        break;
                        case "BAS%":
                            $updateString .= ",basophils = '".$cols[$i][3]."'";
                        break;
                        case "PLT":
                            $updateString .= ",platelate = '".$cols[$i][3]."'";
                        break;
                        case "MCV":
                            $updateString .= ",mcv = '".$cols[$i][3]."'";
                        break;
                        case "MCH":
                            $updateString .= ",mch = '".$cols[$i][3]."'";
                        break;
                        case "MCHC":
                            $updateString .= ",mchc = '".$cols[$i][3]."'";
                        break;
                        case "RDW-CV":
                            $updateString .= ",rdwcv = '".$cols[$i][3]."'";
                        break;
                        case "RDW-SD":
                            $updateString .= ",rdwsd = '".$cols[$i][3]."'";
                        break;
                        case "MPV":
                            $updateString .= ",mpv = '".$cols[$i][3]."'";
                        break;
                        case "PDW-CV":
                            $updateString .= ",pdwcv = '".$cols[$i][3]."'";
                        break;
                        case "PDW-SD":
                            $updateString .= ",pdwsd = '".$cols[$i][3]."'";
                        break;
                        case "PCT":
                            $updateString .= ",pct = '".$cols[$i][3]."'";
                        break;
                        case "P-LCC":
                            $updateString .= ",plcc = '".$cols[$i][3]."'";
                        break;
                        case "P-LCR":
                            $updateString .= ",plcr = '".$cols[$i][3]."'";
                        break;

                    }
                }
                $i++;
            }

            fclose($handle);
            $newFileName = "CBC" . strtoupper($traceno) . $specimenID . ".log";

            if($specimenID != '') {
                $updateQuery = "UPDATE IGNORE lab_cbcresult_temp set parsed_on = now(), parsed_file = '$newFileName' $updateString WHERE traceno = '$traceno';";
                $con->dbquery($updateQuery);
                rename($file,"out/$newFileName");
            } else {
                rename($file,"stray/$newFileName");
            }
        }
    }

?>