<?php
	//ini_set("display_errors","On");
	require_once "initDB.php";
	class _init extends myDB {
	
		public $pageNum;
		public $cpass;
		public $exception;
		public $age;
		public $ageDisplay;
		
		public function _toHrs($_x) {
			return ROUND($_x / 3600,2);
		}
		
		public function renew_timestamp($key,$time) {
			$v = parent::dbquery("update active_sessions set timestamp = '$time' where sessid = '$key';");
			if($v) { return true; }
		}
		
		function getUname($uid) {
			list($name) = parent::getArray("select fullname from user_info where emp_id = '$uid';");
			echo $name;
		}
		
		function validateKey() {
			$tcur = time();
			
			list($_sess) = parent::getArray("select count(*) from active_sessions where sessid = '$_SESSION[authkey]';");
			if($_sess > 0) {
				list($tstamp) = parent::getArray("select `timestamp` from active_sessions where sessid = '$_SESSION[authkey]';");
				$life = $tcur - $tstamp;
				if($life > 7200) {
					$this->exception = 2;
					unset($_SESSION['userid']);
					unset($_SESSION['authkey']);
					unset($_SESSION['branchid']);
					unset($_SESSION['company']);
					session_destroy();
					parent::dbquery("delete from active_sessions where sessid = '$_SESSION[authkey]';");
				} else {
					if($this->renew_timestamp($_SESSION['authkey'],$tcur) == true) { $this->exception = 0; } else { $this->exception = 3; }
					list($this->cpass) = parent::getArray("select require_change_pass from user_info where emp_id = '$_SESSION[userid]';");
				}
			} else {
				$this->exception = 4;
			}
		}
		
		function loginError($exception) {
			switch($exception) {
				case "3": echo "There was an error while trying to renew your session. Please contact technical team to resolve this issue."; break;
				case "4": echo "Invalid Session Detected!"; break;
			}
		}
		
		function trailer($module,$action) {
			parent::dbquery("insert ignore into traillog (user_id,`timestamp`,ipaddress,module,`action`) values ('$_SESSION[userid]',now(),'$_SERVER[REMOTE_ADDR]','$module','".mysql_real_escape_string($action)."');");
		}
		
		function initBackground($i) {
			if($i%2==0){ $bgC = "#ededed"; } else { $bgC = "#ffffff"; }
			return $bgC;
		}

		function generateRandomString($length = 10) {
			return substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/strlen($x)) )),1,$length);
		}

		function identUnit($abbrv) {
			list($unit) = parent::getArray("select UCASE(description) from options_units where unit = '$abbrv';");
			return $unit;
		}
				
		function _structInput($a,$b,$c,$d,$e,$f) {
			
			echo "<tr>
					<td width=$b><span class=\"spandix-l\">$a :</span></td>
					<td>
						<input type=\"text\" id=\"$c\" class=\"$d\" style=\"$e\" value = \"$f\" />
					</td>
				</tr>
				<tr><td height=4></td></tr>";
			
		}

		function timify($el,$w) {
			if($w=='') { $w = '80px;'; }

			$time = "<select name='$el"."_hr' class='gridInput' style='width: $w'>";
			
			for($i=1;$i<=23;$i++) {
				$h = str_pad($i,2,'0',STR_PAD_LEFT);
				$curT = date('H');

				if($h === $curT) { $selected = "selected"; } else { $selected = ''; }
				$time .= "<option value='$h' $selected>$h</option>";

			}

			$time .= "</select> : <select name='$el"."_min' class='gridInput' style='width: $w'>";
			
			for($i=1;$i<=59;$i++) {
				$min = str_pad($i,2,'0',STR_PAD_LEFT);
				$curT = date('i');

				if($min === $curT) { $selected = "selected"; } else { $selected = ''; }
				$time .= "<option value='$min' $selected>$min</option>";

			}
			
			$curT = date('a');

			$time .= "</select>";

			echo $time;
		}
		
		function _structMonths($a,$b,$c) {
			$string =  '<tr>
						<td width=35%><span class="spandix-l">Month :</span></td>
						<td>
							<select id="'.$a.'" name="'.$a.'"  class="'.$c.'" style="'.$b.'">
								<option value="01">January</option>
								<option value="02">February</option>
								<option value="03">March</option>
								<option value="04">April</option>
								<option value="05">May</option>
								<option value="06">June</option>
								<option value="07">July</option>
								<option value="08">August</option>
								<option value="09">September</option>
								<option value="10">October</option>
								<option value="11">November</option>
								<option value="12">December</option>
							</select>
						</td>
					</tr>
					<tr><td height=4></td></tr>
			    ';
				
			echo $string;
		}
		
		function _structYear($a,$b,$c,$d,$e) {
			echo '<tr>
					<td width='.$b.' valign=top><span class="spandix-l">'.$a.' :</span></td>
					<td>
						<select id="'.$c.'" class="'.$d.'" '.$e.'>';
							$cy = date('Y');
							for($x=$cy;$x>=2018;$x--){
								echo "<option value=$x>$x</option>";
							}							
					echo '</select>
					</td>
				</tr>
				<tr><td height=4></td></tr>';
		}
		
				function _month($dig) {
			switch($dig) {
				case "01": return "January"; break; case "02": return "February"; break; case "03": return "March"; break; case "04": return "April"; break;
				case "05": return "May"; break; case "06": return "June"; break; case "07": return "July"; break; case "08": return "August"; break;
				case "09": return "September"; break; case "10": return "October"; break; case "11": return "November"; break; case "12": return "December"; break;
			}
		}
		
		function formatDate($date) {
			$date = explode("/",$date);
			return $date[2]."-".$date[0]."-".$date[1];
		}
		
		function formatDigit($dig) {
			return preg_replace('/,/','',$dig);
		}

		function inWords($number) {
			$hyphen      = ' ';
			$conjunction = ' ';
			$separator   = ' ';
			$negative    = 'negative ';
			$decimal     = ' point ';
			$dictionary  = array(
				0                   => 'zero',
				1                   => 'one',
				2                   => 'two',
				3                   => 'three',
				4                   => 'four',
				5                   => 'five',
				6                   => 'six',
				7                   => 'seven',
				8                   => 'eight',
				9                   => 'nine',
				10                  => 'ten',
				11                  => 'eleven',
				12                  => 'twelve',
				13                  => 'thirteen',
				14                  => 'fourteen',
				15                  => 'fifteen',
				16                  => 'sixteen',
				17                  => 'seventeen',
				18                  => 'eighteen',
				19                  => 'nineteen',
				20                  => 'twenty',
				30                  => 'thirty',
				40                  => 'forty',
				50                  => 'fifty',
				60                  => 'sixty',
				70                  => 'seventy',
				80                  => 'eighty',
				90                  => 'ninety',
				100                 => 'hundred',
				1000                => 'thousand',
				1000000             => 'million',
				1000000000          => 'billion',
				1000000000000       => 'trillion',
				1000000000000000    => 'quadrillion',
				1000000000000000000 => 'quintillion'
			);
			
			if (!is_numeric($number)) {
				return false;
			}
			
			if (($number >= 0 && (int) $number < 0) || (int) $number < 0 - PHP_INT_MAX) {
				// overflow
				trigger_error(
					'inWords only accepts numbers between -' . PHP_INT_MAX . ' and ' . PHP_INT_MAX,
					E_USER_WARNING
				);
				return false;
			}

			if ($number < 0) {
				return $negative . self::inWords(abs($number));
			}
			
			$string = $fraction = null;
			
			if (strpos($number, '.') !== false) {
				list($number, $fraction) = explode('.', $number);
			}
			
			switch (true) {
				case $number < 21:
					$string = $dictionary[$number];
					break;
				case $number < 100:
					$tens   = ((int) ($number / 10)) * 10;
					$units  = $number % 10;
					$string = $dictionary[$tens];
					if ($units) {
						$string .= $hyphen . $dictionary[$units];
					}
					break;
				case $number < 1000:
					$hundreds  = $number / 100;
					$remainder = $number % 100;
					$string = $dictionary[$hundreds] . ' ' . $dictionary[100];
					if ($remainder) {
						$string .= $conjunction . self::inWords($remainder);
					}
					break;
				default:
					$baseUnit = pow(1000, floor(log($number, 1000)));
					$numBaseUnits = (int) ($number / $baseUnit);
					$remainder = $number % $baseUnit;
					$string = self::inWords($numBaseUnits) . ' ' . $dictionary[$baseUnit];
					if ($remainder) {
						$string .= $remainder < 100 ? $conjunction : $separator;
						$string .= self::inWords($remainder);
					}
					break;
			}
			
			if (null !== $fraction && is_numeric($fraction)) {
				$string .= $decimal;
				$words = array();
				foreach (str_split((string) $fraction) as $number) {
					$words[] = $dictionary[$number];
				}
				$string .= implode(' ', $words);
			}
			
			return strtoupper($string);
		}
		
		function formatNumber($num, $dec) {
			if($num=='') { $num = 0; }
			if($num < 0) {
				return '('.number_format(abs($num),$dec).')';
			} else {
				return number_format($num,$dec);
			}
		}
		
		 function convert2Short($n) {
			$n = (0+str_replace(",", "", $n));
			if (!is_numeric($n)) return false;
			
			if($n < 0) { $xn = $n * -1; } else { $xn = $n; }
			
			if ($xn > 1000000000000) $xn = round(($xn/1000000000000), 2).'T';
			elseif ($xn > 1000000000) $xn = round(($xn/1000000000), 2).'B';
			elseif ($xn > 1000000) $xn = round(($xn/1000000), 2).'M';
			elseif ($xn > 1000) $xn = round(($xn/1000), 2).'K';
			
			if($n < 0) {
				return '('.$xn.')';
			} else { return $xn; }

		}

		function identRoom($enccode) {
			list($room) = parent::getArray("SELECT CONCAT(e.wardname,' - ',rmname,' - ',bdname) FROM ppp_danao.lab_samples a LEFT JOIN hospital_dbo.hpatroom b ON a.enccode = b.enccode LEFT JOIN hospital_dbo.hroom c ON b.rmintkey = c.rmintkey LEFT JOIN hospital_dbo.hbed d ON b.bdintkey = d.bdintkey LEFT JOIN hospital_dbo.hward e ON b.wardcode = e.wardcode WHERE a.enccode = '$enccode' LIMIT 1;");
			return $room;
		}

		function calculateAge($orderdate,$dob) {
			
			$bday = new DateTime($dob); // Your date of birth
			$today = new Datetime($orderdate);

			$diff = $today->diff($bday);
			$ageInYears = $diff->y;
			
			if($ageInYears > 0) {
				$ageDisplay = $ageInYears . "Y";
			} else {
				if($diff->m < 1) {
					$ageDisplay  = $diff->d . "D";
				} else {
					$ageDisplay = $diff->m . "M";
				}
			}	

			$this->age = $ageInYears;
			$this->ageDisplay = $ageDisplay;

		}

		function getAttribute($code,$age,$gender) {
			
			$att = parent::getArray("SELECT unit,`min_value`,`max_value`,f_min_value,f_max_value,p_min_value,p_max_value,nb_min_value,nb_max_value FROM lab_testvalues WHERE `code` = '$code';");
			if($age <= 17) {
				if($att['p_min_value'] != '' || $att['p_max_value'] !='') {
					if($att['p_min_value'] == 0) {
						$testAttribute = "< " . $att['p_max_value'] . " " . $att['unit'];	
					} else {
						$testAttribute = $att['p_min_value']	. " - " . $att['p_max_value'] . " " . $att['unit'];	
					}
				} else {
					if($att['min_value'] == 0) {
						$testAttribute = "< " . $att['max_value'] . " " . $att['unit'];	
					} else {
						$testAttribute = $att['min_value']	. " - " . $att['max_value'] . " " . $att['unit'];
					}
				}
			} else {
				if($gender == 'M') {
					if($att['min_value'] == 0) {
						$testAttribute = "< " . $att['max_value'] . " " . $att['unit'];
					} else {
						$testAttribute = $att['min_value']	. " - " . $att['max_value'] . " " . $att['unit'];
					}
				} else {
					if($att['f_min_value'] == 0) {
						$testAttribute = "< " . $att['f_max_value'] . " " . $att['unit'];
					} else {
						$testAttribute = $att['f_min_value']	. " - " . $att['f_max_value'] . " " . $att['unit'];	
					}
				}
			}

			/* ABG */
			if($code == 'BE' || $code == 'HCTC' ) {
				if($att['min_value'] == 0) {
					$testAttribute = $att['unit'];	
				}
			}

			return $testAttribute;
		}

		function getAttribute2($code,$age,$gender,$machine) {


			switch($machine) {
				case "BIOBASE":
					$fstring = " and `mach` = 'BIOBASE'";
				break;
				case "MINDRAY":
					$fstring = " and `mach` = 'MINDRAY'";
				break;
				case "FUJI":
					$fstring = " and `mach` = 'FUJI'";
				break;
				default:
					$fstring = " and `mach` = 'MINDRAY'";
				break;

			}
			
			$att = parent::getArray("SELECT unit,`min_value`,`max_value`,f_min_value,f_max_value,p_min_value,p_max_value,nb_min_value,nb_max_value FROM lab_testvalues WHERE `code` = '$code' $fstring;");
			if($age <= 17) {
				if($att['p_min_value'] != '' || $att['p_max_value'] !='') {
					if($att['p_min_value'] == 0) {
						$testAttribute = "< " . $att['p_max_value'] . " " . $att['unit'];	
					} else {
						$testAttribute = $att['p_min_value']	. " - " . $att['p_max_value'] . " " . $att['unit'];	
					}
				} else {
					if($att['min_value'] == 0) {
						$testAttribute = "< " . $att['max_value'] . " " . $att['unit'];	
					} else {
						$testAttribute = $att['min_value']	. " - " . $att['max_value'] . " " . $att['unit'];
					}
				}
			} else {
				if($gender == 'M') {
					if($att['min_value'] == 0) {
						$testAttribute = "< " . $att['max_value'] . " " . $att['unit'];
					} else {
						$testAttribute = $att['min_value']	. " - " . $att['max_value'] . " " . $att['unit'];
					}
				} else {
					if($att['f_min_value'] == 0) {
						$testAttribute = "< " . $att['f_max_value'] . " " . $att['unit'];
					} else {
						$testAttribute = $att['f_min_value']	. " - " . $att['f_max_value'] . " " . $att['unit'];	
					}
				}

				if($code == 'L134') {
					if($att['min_value'] == 0) {
						$testAttribute = "> " . $att['max_value'] . " " . $att['unit'];
					} 
				}
			}

			return $testAttribute;
		}

		function checkChemValues($age,$gender,$code,$result) {
			
			if($result > 0) {
				$att = parent::getArray("SELECT * FROM lab_testvalues where `code` = '$code';");
				
				//if($age > 0) {

					if($age <= 17) {
						if($result < $att['p_min_value']) { return "<font color=red><b>L</b></font>"; }
						if($result > $att['p_max_value']) { return "<font color=red><b>H</b></font>"; }
					
					} else {
						if($gender == 'M') {
							if($result < $att['min_value']) { return "<font color=red><b>L</b></font>"; }
							if($result > $att['max_value']) { return "<font color=red><b>H</b></font>"; }
						} else {
							if($result < $att['f_min_value']) { return "<font color=red><b>L</b></font>"; }
							if($result > $att['f_max_value']) { return "<font color=red><b>H</b></font>"; }
						}
					}
				
					/* TROP - I (QUANTITATIVE) */
					if($code == 'L131') {

						if($result < $att['min_value']) { return "<font color=red><b>L</b></font>"; }
						if($result >= $att['max_value']) { return "<font color=red><b>H</b></font>"; }

					}

					/* CRP */
					if($code == 'L078' || $code == 'L070') {

						if($result > $att['min_value']) { return "<font color=red><b>H</b></font>"; }
						if($result < $att['max_value']) { return "<font color=red><b>L</b></font>"; }

					}

					/* HS-CRP */
					if($code == 'HSCRP') {

						if($result > $att['min_value']) { return "<font color=red><b>L</b></font>"; }
						if($result < $att['max_value']) { return "<font color=red><b>H</b></font>"; }

					}

					/* D-DIMER */
					if($code == 'L138') {

						if($result < $att['min_value']) { return "<font color=red><b>L</b></font>"; }
						if($result > $att['max_value']) { return "<font color=red><b>H</b></font>"; }

					}

					/* BUN E*/
					if($code == 'L005') {
						if($result < $att['min_value']) { return "<font color=red><b>L</b></font>"; }
						if($result > $att['max_value']) { return "<font color=red><b>H</b></font>"; }
					}

					/* AMYLASE*/
					if($code == 'L133') {
						if($result < $att['min_value']) { return "<font color=red><b>L</b></font>"; }
						if($result > $att['max_value']) { return "<font color=red><b>H</b></font>"; }
					}

					/* T4*/
					if($code == 'L048') {
						if($result < $att['min_value']) { return "<font color=red><b>L</b></font>"; }
						if($result > $att['max_value']) { return "<font color=red><b>H</b></font>"; }
					}

					 /* HBA1C */

					 if($code == 'L022') {

						if($result < $att['min_value']) { return "<font color=red><b>L</b></font>"; }
						if($result > $att['max_value']) { return "<font color=red><b>H</b></font>"; }

					}
					
				
				/* } else {

					if($result < $att['p_min_value']) { return "<font color=red><b>L</b></font>"; }
					if($result > $att['p_max_value']) { return "<font color=red><b>H</b></font>"; }

				} */

				//return $age;
			}
		}

		function checkChemValues2($age,$gender,$code,$result,$machine) {
			
			if($result > 0) {

				switch($machine) {
					case "BIOBASE":
						$fstring = " and `mach` = 'BIOBASE'";
					break;
					case "MINDRAY":
						$fstring = " and `mach` = 'MINDRAY'";
					break;
					case "FUJI":
						$fstring = " and `mach` = 'FUJI'";
					break;
					default:
						$fstring = " and `mach` = 'MINDRAY'";
					break;
	
				}


				$att = parent::getArray("SELECT * FROM lab_testvalues where `code` = '$code' $fstring;");
				
				//if($age > 0) {

					if($age <= 17) {
						if($result < $att['p_min_value']) { return "<font color=red><b>L</b></font>"; }
						if($result > $att['p_max_value']) { return "<font color=red><b>H</b></font>"; }
					
					} else {
						if($gender == 'M') {
							if($result < $att['min_value']) { return "<font color=red><b>L</b></font>"; }
							if($result > $att['max_value']) { return "<font color=red><b>H</b></font>"; }
						} else {
							if($result < $att['f_min_value']) { return "<font color=red><b>L</b></font>"; }
							if($result > $att['f_max_value']) { return "<font color=red><b>H</b></font>"; }
						}
					}
				
					/* TROP - I (QUANTITATIVE) */
					if($code == 'L131') {

						if($result < $att['min_value']) { return "<font color=red><b>L</b></font>"; }
						if($result >= $att['max_value']) { return "<font color=red><b>H</b></font>"; }

					}

					/* CRP */
					if($code == 'L078' || $code == 'L070') {

						if($result < $att['min_value']) { return "<font color=red><b>L</b></font>"; }
						if($result > $att['max_value']) { return "<font color=red><b>H</b></font>"; }

					}

					/* HS-CRP */
					if($code == 'HSCRP') {

						if($result < $att['min_value']) { return "<font color=red><b>L</b></font>"; }
						if($result >= $att['max_value']) { return "<font color=red><b>H</b></font>"; }

					}

					/* BUN E*/
					if($code == 'L005') {
						if($result < $att['min_value']) { return "<font color=red><b>L</b></font>"; }
						if($result > $att['max_value']) { return "<font color=red><b>H</b></font>"; }
					}

					if($code == 'L022') {

						if($result < $att['min_value']) { return "<font color=red><b>L</b></font>"; }
						if($result > $att['max_value']) { return "<font color=red><b>H</b></font>"; }

					}
					/* AMYLASE*/
					if($code == 'L133') {
						if($result < $att['min_value']) { return "<font color=red><b>L</b></font>"; }
						if($result > $att['max_value']) { return "<font color=red><b>H</b></font>"; }
					}
				
				/* } else {

					if($result < $att['p_min_value']) { return "<font color=red><b>L</b></font>"; }
					if($result > $att['p_max_value']) { return "<font color=red><b>H</b></font>"; }

				} */

				//return $age;
			}
		}

		function getCBCAttribute($age,$gender,$attr) {
			
			$att = parent::getArray("SELECT unit, if(multiplier!='',concat('x',multiplier),'') as multiplier, format(`min_value`,place_values) as `min_value`,format(`max_value`,place_values) as `max_value`,format(f_min_value,place_values) as f_min_value,format(f_max_value,place_values) as f_max_value,format(p_min_value,place_values) as p_min_value,format(p_max_value,place_values) as p_max_value,format(p_f_min_value,place_values) as p_f_min_value,format(p_f_max_value,place_values) as p_f_max_value, format(nb_min_value,place_values) as nb_min_value, format(nb_max_value,place_values) as nb_max_value FROM lab_cbc_defvalues where attribute = '$attr';");

			if($age > 0) {
				if($age <= 17) {

					if($gender == 'M') {
						if($att['p_min_value'] == 0) {
							$testAttribute =  "< " . $att['p_max_value'] . "" . $att['multiplier'] . " " . $att['unit'];	
						} else {
							$testAttribute = $att['p_min_value']	. " - " . $att['p_max_value'] . "" . $att['multiplier'] . " " . $att['unit'];	
						}
					} else {
						if($att['p_f_min_value'] == 0) {
							$testAttribute = "< " . $att['p_f_max_value'] . "" . $att['multiplier'] . " " . $att['unit'];
						} else {
							$testAttribute = $att['p_f_min_value']	. " - " . $att['p_f_max_value'] . "" . $att['multiplier'] . " " . $att['unit'];
						}	
					}
				} else {
					if($gender == 'M') {
						if($att['min_value'] == 0) {
							$testAttribute = "< " . $att['max_value'] . "" . $att['multiplier'] . " " . $att['unit'];	
						} else {
							$testAttribute = $att['min_value']	. " - " . $att['max_value'] . "" . $att['multiplier'] . " " . $att['unit'];	
						}
					} else {
						if($att['f_min_value'] == 0) {
							$testAttribute = "< " . $att['f_max_value'] . "" . $att['multiplier'] . " " . $att['unit'];	
						} else {
							$testAttribute = $att['f_min_value']	. " - " . $att['f_max_value'] . "" . $att['multiplier'] . " " . $att['unit'];	
						}
					}
				}
			}  else {
				if($att['nb_min_value'] == 0) {
					$testAttribute = "< " . $att['nb_max_value'] . "" . $att['multiplier'] . " " . $att['unit'];	
				} else {
					$testAttribute = $att['nb_min_value']	. " - " . $att['nb_max_value'] . "" . $att['multiplier'] . " " . $att['unit'];	
				}
			}

			return $testAttribute;
			
		}

		function getCBCAttribute2($age,$gender,$attr,$machine) {

			switch($machine) {
				case "GENRUI":
					$fstring = " and `mach` = 'GENRUI'";
				break;
				case "H500":
					$fstring = " and `mach` = 'H500'";
				break;
				default:
					$fstring = " and `mach` = 'GENRUI'";
				break;

			}
			
			$att = parent::getArray("SELECT unit, if(multiplier!='',concat('x',multiplier),'') as multiplier, format(`min_value`,place_values) as `min_value`,format(`max_value`,place_values) as `max_value`,format(f_min_value,place_values) as f_min_value,format(f_max_value,place_values) as f_max_value,format(p_min_value,place_values) as p_min_value,format(p_max_value,place_values) as p_max_value,format(p_f_min_value,place_values) as p_f_min_value,format(p_f_max_value,place_values) as p_f_max_value, format(nb_min_value,place_values) as nb_min_value, format(nb_max_value,place_values) as nb_max_value FROM lab_cbc_defvalues where attribute = '$attr' $fstring;");

			if($age > 0) {
				if($age <= 17) {

					if($gender == 'M') {
						if($att['p_min_value'] == 0) {
							$testAttribute =  "< " . $att['p_max_value'] . "" . $att['multiplier'] . " " . $att['unit'];	
						} else {
							$testAttribute = $att['p_min_value']	. " - " . $att['p_max_value'] . "" . $att['multiplier'] . " " . $att['unit'];	
						}
					} else {
						if($att['p_f_min_value'] == 0) {
							$testAttribute = "< " . $att['p_f_max_value'] . "" . $att['multiplier'] . " " . $att['unit'];
						} else {
							$testAttribute = $att['p_f_min_value']	. " - " . $att['p_f_max_value'] . "" . $att['multiplier'] . " " . $att['unit'];
						}	
					}
				} else {
					if($gender == 'M') {
						if($att['min_value'] == 0) {
							$testAttribute = "< " . $att['max_value'] . "" . $att['multiplier'] . " " . $att['unit'];	
						} else {
							$testAttribute = $att['min_value']	. " - " . $att['max_value'] . "" . $att['multiplier'] . " " . $att['unit'];	
						}
					} else {
						if($att['f_min_value'] == 0) {
							$testAttribute = "< " . $att['f_max_value'] . "" . $att['multiplier'] . " " . $att['unit'];	
						} else {
							$testAttribute = $att['f_min_value']	. " - " . $att['f_max_value'] . "" . $att['multiplier'] . " " . $att['unit'];	
						}
					}
				}
			}  else {
				if($att['nb_min_value'] == 0) {
					$testAttribute = "< " . $att['nb_max_value'] . "" . $att['multiplier'] . " " . $att['unit'];	
				} else {
					$testAttribute = $att['nb_min_value']	. " - " . $att['nb_max_value'] . "" . $att['multiplier'] . " " . $att['unit'];	
				}
			}

			return $testAttribute;
			
		}


		function checkCBCValues($age,$gender,$attr,$result) {
			//$att = array();
			$att = parent::getArray("SELECT * FROM lab_cbc_defvalues where attribute = '$attr';");
			if($result > 0 && count($att) > 0) {
				if($age > 0) {
					if($age <= 17) {

						if($result < $att['p_min_value']) { return "<font color=red><b>L</b></font>"; }
						if($result > $att['p_max_value']) { return "<font color=red><b>H</b></font>"; }
					
					} else {
						if($gender == 'M') {
							if($result < $att['min_value']) { return "<font color=red><b>L</b></font>"; }
							if($result > $att['max_value']) { return "<font color=red><b>H</b></font>"; }
						} else {
							if($result < $att['f_min_value']) { return "<font color=red><b>L</b></font>"; }
							if($result > $att['f_max_value']) { return "<font color=red><b>H</b></font>"; }
						}
					}
				} else {
					if($result < $att['nb_min_value']) { return "<font color=red><b>L</b></font>"; }
					if($result > $att['nb_max_value']) { return "<font color=red><b>H</b></font>"; }
				}
			} else { return ""; }
		}


		function validateResult($table,$enccode,$code,$serialno,$uid) {
			
			parent::dbquery("update ignore $table set verified = 'Y', verified_by = '$uid', verified_on = now() where enccode = '$enccode' and serialno = '$serialno';");

			/* UPDATE HDOCORD AS RECOMMENDED BY DoH/ICTO */
			$loopQuery = parent::dbquery("SELECT enccode, dotime, `code` from lab_samples where serialno = '$serialno';");
			while(list($enccode,$dotime,$proccode) = $loopQuery->fetch_array()) {

				/* Update as per recommendation by DoH/ICTO to update hdocord everytime a sample is taken */
				parent::dbquery("UPDATE IGNORE hospital_dbo.hdocord set estatus = 'S' where enccode = '$enccode' and dotime = '$dotime' and proccode = '$proccode';");

				/* Create Log (No data structure where to put audit trail when a record in hdocord is update) */
				parent::dbquery("INSERT IGNORE into hdocord_log (enccode,dotime,proccode,estatus_update,updated_by,updated_on) values ('$enccode','$dotime','$proccode','S','$_SESSION[userid]',now());");

			}
			
		}

		function updateLabSampleStatus($enccode,$code,$sn,$stat,$uid) {
			if($code != '') { $whereString = " and `code` = '$code' "; }
			parent::dbquery("update ignore lab_samples set status = '$stat', updated_by = '$uid', updated_on = now() where enccode = '$enccode' and serialno = '$sn' $whereString;");

			parent::dbquery("update ignore lab_samples set printed_on = now() where enccode = '$enccode' and serialno = '$sn' $whereString;");

		}
		
	}
?>