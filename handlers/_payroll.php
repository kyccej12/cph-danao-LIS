<?php
	//ini_set("display_errors","On");
	require_once 'initDB.php';
	class payroll extends myDB {
	
		public $cutoff;
		public $ptype;
		public $ndays;
		public $restDays;
		public $baseDays;
		public $dtf;
		public $dt2; 
		public $foy;
		public $bleep;
		public $wom;
		public $dtrCount;
		public $reportingMonth;
		public $reportingYear;
		public $semiRate;
		public $dailyRate;
		public $hrate;
		
		public $def_ins;
		public $def_ins_min;
		public $def_ins_max;
		public $def_oas;
		public $def_oas_min;
		public $def_oas_max;
		public $def_ips;
		public $def_ips_min;
		public $def_ips_max;
		public $def_ops;
		public $def_ops_min;
		public $def_ahrs;
		public $def_phrs;
		
		public $ins;
		public $oas;
		public $ips;
		public $ops;
		
		public $late;
		public $ut;
		public $twork;
		public $overtime;
		public $restday;
		public $premium;
		public $htype;
		
		public $vl;
		public $sl;
		public $sil;
		public $absences;
		public $sss_premium;
		public $sss_premium_er;
		public $pg_premium;
		public $pg_premium_er;
		public $ph_premium;
		public $ph_premium_er;
		public $previousTaxable;
		public $onRestDayHoliday;
		
		public function __construct($pid) {
			$a = parent::getArray("select period_start, period_end, date_format(period_end,'%d') as bleep, weekOfMonth, reportingMonth, reportingYear from pay_periods where period_id = '$pid';");
			$this->cutoff = $pid;
			$this->dtf = $a['period_start'];
			$this->dt2 = $a['period_end']; 
			$this->bleep = $a['bleep'];
			$this->wom = $a['weekOfMonth'];
			$this->reportingMonth = $a['reportingMonth'];
			$this->reportingYear = $a['reportingYear'];
			
			list($this->foy) = parent::getArray("select date_format('".$this->dt2."','%Y-01-01');");
			list($this->ndays) = parent::getArray("select datediff('".$this->dt2."','".$this->dtf."');");
		
			for($x = 0; $x <=$this->ndays; $x++) {
				list($xday) = parent::getArray("select date_format(date_add('". $this->dtf ."',INTERVAL $x DAY),'%a');");
				if($this->checkRestDay($xday)) { $this->restDays += 1; }
			}
		
			$this->baseDays = ($this->ndays + 1) - $this->restDays;
		}
			
		public function _toHrs($_x) {
			return ROUND($_x / 3600,2);
		}
		
		public function countHolidays($area) {
			list($this->sholiday) = parent::getArray("select count(*) FROM (SELECT DISTINCT `date`,DATE_FORMAT(`date`,'%a') FROM pay_holiday_nat WHERE `date` BETWEEN '". $this->dtf ."' AND '" . $this->dt2 . "' AND `type` = '2' and date_format(`date`,'%a') != 'Sun' UNION SELECT DISTINCT `date`,DATE_FORMAT(`date`,'%a') FROM pay_holiday_local WHERE `date` BETWEEN '". $this->dtf ."' AND '" . $this->dt2 . "' AND `area` = '$area' and date_format(`date`,'%a') != 'Sun') a;");
			list($this->lholiday) = parent::getArray("select count(*) from pay_holiday_nat where `date` between '" . $this->dtf . "' and '". $this->dt2 . "' and `type` = '1' and date_format(`date`,'%a') != 'Sun';");
			
			/* Holiday on Sundays */
			list($nat) = parent::getArray("select count(*) from pay_holiday_nat where `date` between '" . $this->dtf . "' and '". $this->dt2 . "' and DATE_FORMAT(`date`,'%a') = 'Sun';");
			list($local) = parent::getArray("select count(*) from pay_holiday_local where `date` between '" . $this->dtf . "' and '". $this->dt2 . "' and DATE_FORMAT(`date`,'%a') = 'Sun' and `area` = '$area';");
			$this->onRestDayHoliday = $nat + $local;
			
			$this->holidayCount = $this->sholiday + $this->lholiday + $this->onRestDayHoliday;
		}
		
		public function checkHoliday($date,$area) {
			list($myDay) = parent::getArray("select date_format('$date','%a');");
			if($myDay == 'Sun') {
				$this->htype = 'RD'; return true;
			} else {
				list($type) = parent::getArray("SELECT DISTINCT `type` FROM (SELECT `date`,IF(`type`=1,'LH','SH') AS `type` FROM pay_holiday_nat WHERE `date` = '$date' UNION SELECT `date`,'SH' AS `type` FROM pay_holiday_local WHERE `date` = '$date' and `area` = '$area') a limit 1;");
				if($type != '') {
					$this->htype = $type; return true;
				} else { $this->htype = 'NA'; return false; }
			}
		}
		
		public function checkRestDay($day) {
			if($day == "Sun") {	$this->htype = "RD"; return true; } else { $this->htype = "NA"; return false; }
		}
		
		public function getRates($ptype,$rate) {
			if($ptype == 2) {
				$this->dailyRate = $rate;
				$this->hrate = ROUND($this->dailyRate/8,2);
			} else {
				$this->semiRate = ROUND($rate/2,2);
				$this->dailyRate = ROUND(($rate * 12) / 314,2);
				$this->hrate = ROUND((($rate * 12) / 314)/8,2);
			}
		}
		
		public function getTimeDefaults($shift,$day) {
			if($day == 'Sun') {
				$sqlTxt = "SELECT TIME_TO_SEC('08:00:00') as def_in_am, TIME_TO_SEC('12:00:00') as def_out_am, TIME_TO_SEC('13:00:00') as def_in_pm, TIME_TO_SEC('17:00:00') as def_out_pm, '4' as hrs_am, '4' as hrs_pm;";
			} else {
				$sqlTxt = "SELECT TIME_TO_SEC(in_am) as def_in_am, TIME_TO_SEC(out_am) as def_out_am, TIME_TO_SEC(in_pm) as def_in_pm, TIME_TO_SEC(out_pm) as def_out_pm, hrs_am, hrs_pm FROM emp_shifts WHERE `day` = '$day' AND shift_id = '$shift';";
			}
			
			$a = parent::getArray($sqlTxt);
			$this->def_ins = $a['def_in_am'];
			$this->def_ins_min = $a['def_in_am'] - 7200;
			$this->def_ins_max = $a['def_in_am'] + 7200;
			
			$this->def_oas = $a['def_out_am']; 
			$this->def_oas_min = $a['def_out_am'] - 7200;
			$this->def_oas_max = $a['def_out_am'] + 3600;
			
			$this->def_ips = $a['def_in_pm'];
			$this->def_ips_max = $a['def_in_pm'] + 7200;
		
			$this->def_ops = $a['def_out_pm'];
			$this->def_ops_min = $a['def_out_pm'] - 7200;
			
			$this->def_ahrs = $a['hrs_am']; 
			$this->def_phrs = $a['hrs_pm'];
		}
		
		function computeTimeSheets($eid,$date,$day,$shift,$inAM,$outAM,$inPM,$outPM,$autoNoon) {
		
			list($dept) = parent::getArray("SELECT DEPT FROM emp_masterfile where emp_id = '$eid';");
		
			$lateAM = 0;
			$utAM = 0;
			$amWork = 0;
			$latePM = 0;
			$utPM = 0;
			$pmWork = 0;
			$this->twork = 0; 
			$this->overtime = 0; 
			$this->premium = 0; 
			$this->late = 0; 
			$this->ut = 0;
			
			
			$this->getTimeDefaults($shift,$day);
			
			/* Set Noon time Defaults if Employee is set to Auto Noon Swipe */
			if($autoNoon == 'Y' && $inAM != 0 && $outPM != 0) {
				$outAM = $this->def_oas; $inPM = $this->def_ips;
			}
			
			/* AM Working Hours */
			if($inAM > 0 && $outAM > 0) {
				if($inAM < $this->def_ins) { $inAM = $this->def_ins; }
				if($outAM >= $this->def_oas && $outAM < $this->def_ips) { $outAM = $this->def_oas; }
			
				if($inAM > $this->def_ins) { 
					$amOver = $inAM - $this->def_ins; }
					if($amOver <= 300) { $lateAM = 0; } else { $lateAM = $this->_toHrs($amOver - 300); } 
				
				if($outAM < $this->def_oas) { $utAM = $this->_toHrs($this->def_oas - $outAM); }
				$amWork = $this->def_ahrs - $lateAM - $utAM;
			}
			
			if($inPM > 0 && $outPM > 0) {
				if($inPM < $this->def_ips) { $inPM = $this->def_ips; }
				if($inPM > $this->def_ips) { $latePM = $this->_toHrs($inPM - $this->def_ips); }
				if($outPM > $this->def_ips && $outPM < $this->def_ops) { $utPM = $this->_toHrs($this->def_ops - $outPM); }
			
				/* Compute Overtime */
				if($outPM > $this->def_ops && $outPM <= 86400) {
					$this->overtime = $this->_toHrs($outPM - $this->def_ops);
					
					/* Only Hydrogen & Oxygen Departments are entitled to Night Premium as per Nelsa Lagrosas (HRD Head) */
					if($dept == '12' or $dept == '35') { if($outPM > 64800) { $this->premium = $this->_toHrs($outPM - 64800); } } else { $this->premium = 0; }
				}
				
				/* Compute if Time out is past midnight */
				if($outPM >= 60 && $outPM <= 25200) {
					
					/* Only Hydrogen & Oxygen Departments are entitled to Night Premium as per Nelsa Lagrosas (HRD Head) */
					if($dept == '12' || $dept == '35') {	$this->premium = $this->_toHrs(($outPM+86400) - 64800); } else { $this->premium = 0; }
					if($shift != 2) { $this->overtime = $this->_toHrs(($outPM+86400) - $this->def_ops); } else { $this->overtime = $this->_toHrs($outPM - $this->def_ops); }
			
				}					
				
				$pmWork = $this->def_phrs - $latePM - $utPM;
		
			}
			
			$this->twork = $amWork + $pmWork; 
			if($day != 'Sun') {
				$this->late = $lateAM + $latePM; $this->ut = $utAM + $utPM;
			}
		}
		
		function getAbsences($eid,$area) {	
			
			$this->countHolidays($area);
			list($wholeDay) = parent::getArray("SELECT COUNT(*) FROM emp_dtrfinal WHERE TIME_TO_SEC(IN_AM) > 0 AND TIME_TO_SEC(OUT_PM) > 0 AND EMP_ID = '$eid' AND `DATE` BETWEEN '". $this->dtf ."' AND '". $this->dt2 ."' AND DATE_FORMAT(`DATE`,'%a') NOT IN ('Sun');");
			list($halfAM) = parent::getArray("SELECT COUNT(*) FROM emp_dtrfinal WHERE TIME_TO_SEC(IN_AM) > 0 AND TIME_TO_SEC(OUT_AM) > 0 AND TIME_TO_SEC(IN_PM) = 0 AND TIME_TO_SEC(OUT_PM) = 0 AND EMP_ID = '$eid' AND `DATE` BETWEEN '". $this->dtf ."' AND '". $this->dt2 ."' AND DATE_FORMAT(`DATE`,'%a') NOT IN ('Sun');");
			list($halfPM) = parent::getArray("SELECT COUNT(*) FROM emp_dtrfinal WHERE TIME_TO_SEC(IN_AM) = 0 AND TIME_TO_SEC(OUT_AM) = 0 AND TIME_TO_SEC(IN_PM) > 0 AND TIME_TO_SEC(OUT_PM) > 0 AND EMP_ID = '$eid' AND `DATE` BETWEEN '". $this->dtf ."' AND '". $this->dt2 ."' AND DATE_FORMAT(`DATE`,'%a') NOT IN ('Sun');");
			
			$this->dtrCount = $wholeDay + ($halfAM/2) + ($halfPM/2);
			list($sil) = parent::getArray("SELECT ifnull(SUM(`length`),0) FROM pay_loa WHERE date_from >= '" . $this->dtf . "' AND date_to <= '" . $this->dt2 . "' AND w_pay = 'Y' AND emp_id = '$eid' and file_status != 'Deleted';");
			$myabsences = $this->baseDays+$this->onRestDayHoliday - $sil - $this->dtrCount - $this->holidayCount;
			if($myabsences > 0) { $this->absences = $myabsences; } else { return $this->absences = 0; }	
			
		}
		
		function checkSL($eid,$credits) {
			list($prev_sl) = parent::getArray("SELECT ifnull(SUM(`length`),0) AS sil FROM pay_loa WHERE date_to < '" . $this->dtf . "' and date_to >= '". date('2021-10-01') ."' and w_pay = 'Y' and leave_type = '2' and emp_id = '$eid' and file_status != 'Deleted';");
			if($prev_sl < $credits) {
				$slBalance = $credits - $prev_sl;	
				list($cur_sl) = parent::getArray("SELECT ifnull(SUM(`length`),0) AS sl FROM pay_loa WHERE emp_id = '$eid' and date_to >= '". $this->dtf. "' AND date_to <= '" . $this->dt2 . "' and w_pay = 'Y' and leave_type = '2' and file_status != 'Deleted';");
				if($slBalance > $cur_sl) { $this->sl = $cur_sl; } else { $this->sl = $slBalance; }
			} else { $this->sl = 0; }
		}
		
		function checkVL($eid,$credits) {
			list($prev_vl) = parent::getArray("SELECT ifnull(SUM(`length`),0) AS sil FROM pay_loa WHERE emp_id = '$eid' and date_to < '" . $this->dtf . "' and date_to >= '". date('2021-10-01') ."' and w_pay = 'Y' and leave_type = '1' and file_status != 'Deleted';");
			if($prev_vl < $credits) {
				$vlBalance = $credits - $prev_vl;	
				list($cur_vl) = parent::getArray("SELECT ifnull(SUM(`length`),0) AS sl FROM pay_loa WHERE emp_id = '$eid' and date_to >= '". $this->dtf. "' AND date_to <= '" . $this->dt2 . "' and w_pay = 'Y' and leave_type = '1' and file_status != 'Deleted';");
				if($vlBalance > $cur_vl) { $this->vl = $cur_vl; } else { $this->vl = $vlBalance; }
			} else { $this->vl = 0; }
		}
		
		function checkSIL($eid) {
			list($sil) = parent::getArray("SELECT ifnull(SUM(`length`),0) AS sil FROM pay_loa WHERE emp_id = '$eid' and date_to >= '". $this->dtf. "' AND date_to <= '" . $this->dt2 . "' and w_pay = 'Y' and leave_type not in (1,2) and file_status != 'Deleted';");
			$this->sil = $sil;
		}
		
		function myPremiums($rate,$eid,$etype,$hdmf,$wSSS,$wPH,$wHDMF) {	
			$this->pg_premium = 0; $this->pg_premium_er = 0; $this->ph_premium = 0; $this->ph_premium_er = 0; $this->sss_premium = 0; $this->sss_premium_er = 0;
			
			if($rate > 1000) {
				if($this->wom == 1) { 
					if($wSSS == 'Y') {
						list($this->sss_premium,$this->sss_premium_er) = parent::getArray("select (ee-mpf_ee) as ee, ((er+ec)-mpf_er) as er from sss_table where $rate >= ms_range1 and $rate <= ms_range2;");
					}
					if($wPH == 'Y') { 
						if($rate <= 10000) { $this->ph_premium = '150.00'; } else { if($rate >= 60000) { $this->ph_premium = "900.00"; } else { $this->ph_premium = ROUND((($rate * 0.03)/2),2); }}
						$this->ph_premium_er = $this->ph_premium;
					}
				} else {
					if($wHDMF == 'Y') {	$this->pg_premium_er = 100; if($hdmf == 0 ) { $this->pg_premium = 100;	} else { $this->pg_premium = $hdmf; }}
					
				}
			}
		}
		
		function getPreviousPays($eid) {
			list($lastPID) = parent::getArray("select period_id from pay_periods where reportingMonth='".$this->reportingMonth."' and reportingYear = '".$this->reportingYear."' and weekOfMonth = '1';");
			list($a) = parent::getArray("select gross_pay from emp_payslip where emp_id = '$eid' and period_id = '$lastPID';");
			$this->previousTaxable = $a;
		}
		
		function loadLoans($eid,$etype,$area,$dept) {
			$r = parent::dbquery("select record_id as loan_id,loan_type, if(dedu_type=3,semi_amrtz,monthly_amrtz) as amrtz, date_loan from emp_loanmasterfile where emp_id = '$eid' and '". $this->dtf ."' <= date_add(effective_date,INTERVAL loan_terms MONTH) and '". $this->dt2 ."' >= effective_date and file_status != 'Deleted' and `active` = 'Y' and dedu_type in ('". $this->wom . "','3');");
			if($r) {
				while(list($lid,$ltype,$samt,$d8) = $r->fetch_array(MYSQLI_BOTH)) {
					parent::dbquery("insert ignore into emp_deductionmaster (period_id,emp_type,emp_id,type,area,dept,ref_id,ref_date,ref_type,amount,posted_by,posted_on) values ('". $this->cutoff ."','$etype','$eid','L','$area','$dept','$lid','$d8','$ltype','$samt','$_SESSION[userid]',now());");
			
					list($tLoanApplied) = parent::getArray("select sum(amount) from emp_deductionmaster where ref_id = '$lid' and emp_id = '$eid' and `type` = 'L';");
					parent::dbquery("update ignore emp_loanmasterfile set amt_paid = 0$tLoanApplied, balance = loan_amt - 0$tLoanApplied where record_id = '$lid' and emp_id = '$eid]';");
				}
			}
		}

		function getLoans($eid,$pid,$type) {
			$amt = 0;
			list($amt) = parent::getArray("select sum(amount) from emp_deductionmaster where period_id = '$pid' and emp_id='$eid' and type = 'L' and ref_type = '$type';");
			return $amt;
		}
		
		function getOtherLoans($eid,$pid) {
			$amt = 0;
			list($amt) = parent::getArray("select sum(amount) from emp_deductionmaster where period_id = '$pid' and emp_id='$eid' and type = 'L' and ref_type not in (1,2,3,4,8,10);");
			return $amt;
		}
		
	}
	
?>