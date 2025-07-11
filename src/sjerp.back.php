<?php
	session_start();
	//ini_set("display_errors","On");
	require_once "../handlers/_generics.php";
	
	$con = new _init;
	$date = date('Y-m-d');
	$bid = $_SESSION['branchid'];
	$uid = $_SESSION['userid'];

	switch($_POST['mod']) {
		
		/* Items */
		case "getIcode":
			switch($_POST['mid']) { case "1": $main="RMO"; break; case "2": $main="FA"; break; case "3": $main="LAB"; break; case "4": $main="NRS"; break; case "5": $main="DTL"; break; case "6": $main="XRY"; break;}
			
			if($_POST['sgroup'] > 0) {
				list($scode) = $con->getArray("select `code` from options_sgroup where sid = '$_POST[sgroup]';");
			} else { $scode = '000'; }
			
			list($series) = $con->getArray("SELECT LPAD(IFNULL(MAX(series+1),1),4,0) FROM (SELECT TRIM(LEADING '0' FROM(SUBSTRING_INDEX(SUBSTRING_INDEX(item_code, '-', 3), '-', -1))) AS series FROM products_master WHERE SUBSTRING_INDEX(SUBSTRING_INDEX(item_code, '-', 1), '-', -1) = '$main' AND subgroup='$_POST[sgroup]') a;");		
			echo $main.'-'.$scode.'-'.$series;
		break;
		
		case "getSgroup":
			$a = $con->dbquery("select sid, sgroup from options_sgroup where mid = '$_POST[mgroup]';");
			echo "<option value='0'>- Not Applicable -</option>";
			while(list($y,$z) = $a->fetch_array()) {
				echo "<option value = '$y'>$z</option>";
			}
		break;
		
		case "getCgroup":
			$a = $con->dbquery("select id, cgrp_name from options_cgroup where sgrp_id = '$_POST[sgrp_id]';");
			echo "<option value='0'>- Not Applicable -</option>";
			while(list($y,$z) = $a->fetch_array()) {
				echo "<option value = '$y'>$z</option>";
			}
		break;

		case "copyInfo":
			$a = $con->dbquery("select * from products_master where indcode = '$_POST[stockCode]' limit 1;");
			if($a) {
				echo json_encode($a->fetch_array());
			} else {
				echo json_encode(array("noerror"=>true));
			}
		break;
		
		case "checkDupCode":
			if($_POST['rid'] != "") {
				list($isExist) = $con->getArray("select count(*) from products_master where item_code = '$_POST[item_code]' and company = '1' and record_id != '$_POST[rid]';");
			} else {
				list($isExist) = $con->getArray("select count(*) from products_master where item_code = '$_POST[item_code]' and company = '1';");
			}
			
			if($isExist == 0) { echo "NODUPLICATE"; }
		break;

		case "savePInfo":
			if(!$_POST['status']) { $stat = "Y"; } else { $stat = $_POST['status']; }
			if(isset($_POST['rid']) && $_POST['rid'] != "") {
				$con->dbquery("update ignore products_master set category = '$_POST[item_category]', subgroup='$_POST[item_sgroup]', cgroup = '$_POST[item_cgroup]', item_code='".$con->escapeString($_POST['item_code'])."', brand = '".strtoupper($_POST['item_brand'])."', description='".$con->escapeString(htmlentities($_POST['item_description']))."',full_description='".$con->escapeString(htmlentities($_POST['item_fdescription']))."',beg_qty='".$con->formatDigit($_POST['item_beginning'])."',minimum_level='".$con->formatDigit($_POST['item_mininv'])."',reorder_pt='$_POST[item_reorder]',unit = '$_POST[item_unit]',unit_cost='".$con->formatDigit($_POST['item_unitcost'])."',srp='".$con->formatDigit($_POST['srp'])."',vat_exempt='$_POST[vat_exempt]',rev_acct='$_POST[rev_acct]',cogs_acct='$_POST[cogs_acct]',exp_acct='$_POST[exp_acct]',asset_acct='$_POST[asset_acct]',supplier='$_POST[supplier]',`active`='$stat',updated_by='$uid', updated_on=now(), with_expiration = '$_POST[with_expiration]' where record_id = '$_POST[rid]';");
				echo "update ignore products_master set category = '$_POST[item_category]', subgroup='$_POST[item_sgroup]', item_code='".$con->escapeString($_POST['item_code'])."', brand = '".strtoupper($_POST['item_brand'])."', description='".$con->escapeString(htmlentities($_POST['item_description']))."',full_description='".$con->escapeString(htmlentities($_POST['item_fdescription']))."',beg_qty='".$con->formatDigit($_POST['item_beginning'])."',minimum_level='".$con->formatDigit($_POST['item_mininv'])."',reorder_pt='$_POST[item_reorder]',unit = '$_POST[item_unit]',unit_cost='".$con->formatDigit($_POST['item_unitcost'])."',srp='".$con->formatDigit($_POST['srp'])."',vat_exempt='$_POST[vat_exempt]',rev_acct='$_POST[rev_acct]',cogs_acct='$_POST[cogs_acct]',exp_acct='$_POST[exp_acct]',asset_acct='$_POST[asset_acct]',supplier='$_POST[supplier]',`active`='$stat',updated_by='$uid', updated_on=now() where record_id = '$_POST[rid]';";
			} else {
				$con->dbquery("insert ignore into products_master (company,category,subgroup,cgroup,item_code,brand,description,full_description,unit,unit_cost,srp,beg_qty,minimum_level,reorder_pt,vat_exempt,supplier,rev_acct,cogs_acct,exp_acct,asset_acct,`active`,with_expiration,encoded_by,encoded_on) values ('$_SESSION[company]','$_POST[item_category]','$_POST[item_sgroup]','$_POST[item_cgroup]','".$con->escapeString(htmlentities($_POST['item_code']))."','".$con->escapeString(htmlentities($_POST['item_brand']))."','".$con->escapeString(htmlentities($_POST['item_description']))."','".$con->escapeString(htmlentities($_POST['item_fdescription']))."','$_POST[item_unit]','".$con->formatDigit($_POST['item_unitcost'])."','".$con->formatDigit($_POST['srp'])."','".$con->formatDigit($_POST['item_beginning'])."','".$con->formatDigit($_POST['item_mininv'])."','".$con->formatDigit($_POST['item_reorder'])."','$_POST[vat_exempt]','$_POST[supplier]','$_POST[rev_acct]','$_POST[cogs_acct]','$_POST[exp_acct]','$_POST[asset_acct]','$stat','$_POST[with_expiration]','$uid',now());");
			}
		break;
		
		case "deletePro":
			$con->dbquery("update products_master set `active` = 'N', file_status = 'Deleted' where record_id = '$_POST[rid]';");
		break;
		
		case "restorePro":
			$con->dbquery("update products_master set `active` = 'Y', file_status = 'Active' where record_id = '$_POST[rid]';");
		break;
		
		case "getItemTitle":
			$_h = $con->getArray("select `group`,concat(item_code, ' :: ',description) from products_master where record_id = '$_POST[fid]';");
			echo json_encode($_h);
		break;
		
		case "getGroups":
			$o = $con->dbquery("select `group`,`group_description` from options_igroup where mid = '$_POST[type]' order by `group_description` asc;");
			echo "<option value=''>- Not Applicable -</option>\n";
			while($oo = $o->fetch_array()) {
				echo "<option value='$oo[0]'>$oo[1]</option>\n";
			}
		break;
		
		case "newSGroup":
			list($isE) = $con->getArray("select count(*) from options_sgroup where code = '$_POST[code]';");
			if($isE > 0) {
				echo "DUPLICATE";
			} else {
				$con->dbquery("INSERT INTO options_sgroup (`mid`,sgroup,`code`) VALUES ('$_POST[maingrp]','".$con->escapeString(htmlentities($_POST['description']))."','$_POST[code]');");
			}
		break;
		
		case "retrieveSGroup":
			echo json_encode($con->getArray("select * from options_sgroup where sid = '$_POST[id]';"));
		break;
		
		case "updateSGroup":
			$con->dbquery("update options_sgroup set `mid` = '$_POST[maingrp]', sgroup = '" . $con->escapeString(htmlentities($_POST['description'])) . "' where sid = '$_POST[id]';");
		break;
		
		case "deleteSGroup":
			$con->dbquery("update options_sgroup set file_status = 'Deleted', deleted_by = '$uid', deleted_on = now() where sid = '$_POST[id]';");
		break;
		
		/* Services */
		case "checkServiceDupCode":
			if($_POST['rid'] != "") { $f = " and id != '$_POST[rid]'"; }
			list($isExist) = $con->getArray("select count(*) from services_master where code = '$_POST[item_code]' $f;");
			if($isExist == 0) { echo "NODUPLICATE"; }
		break;
		
		case "getServiceCode":
			list($scode) = $con->getArray("select `code` from options_servicecat where id = '$_POST[mid]';");
			list($series) = $con->getArray("SELECT LPAD(IFNULL(MAX(series+1),1),3,0) FROM (SELECT TRIM(LEADING '0' FROM(SUBSTRING(`code`,2,3))) AS series FROM services_master WHERE category = '$_POST[mid]') a;");		
			echo $scode.$series;
		break;

		case "getServiceSubgroup":
			$a = $con->dbquery("select id, subcategory from options_servicesubcat where parent_id = '$_POST[parent]';");
			echo "<option value='0'>- Not Applicable -</option>";
			while(list($y,$z) = $a->fetch_array()) {
				echo "<option value = '$y'>$z</option>";
			}
		break;

		case "saveSInfo":
			if($_POST['rid'] != '') {
				$con->dbquery("update ignore services_master set `code` = '$_POST[item_code]', ihomis_code = '$_POST[item_barcode]', `description` = '".$con->escapeString($_POST['item_description'])."', short_description = '$_POST[item_shortdesc]', fulldescription = '".$con->escapeString($_POST['item_fdescription'])."', report_title = '".$con->escapeString(htmlentities($_POST['report_title']))."', category = '$_POST[item_category]',subcategory = '$_POST[item_sgroup]', rev_acct = '$_POST[rev_acct]', unit = '$_POST[item_unit]', unit_cost = '".$con->formatDigit($_POST['item_unitcost'])."', unit_price = '".$con->formatDigit($_POST['item_unitprice'])."', with_specimen = '$_POST[with_specimen]', sample_type = '$_POST[sample_type]', with_subtests = '$_POST[with_subtests]', with_bom = '$_POST[with_bom]', lab_tat = '$_POST[lab_tat]', collection_tat = '".$con->formatDigit($_POST['collection_tat'])."', accession_tat = '".$con->formatDigit($_POST['accession_tat'])."', processing_tat = '".$con->formatDigit($_POST['processing_tat'])."', result_tat = '$_POST[result_tat]', container_type = '$_POST[container_type]', result_type = '$_POST[result_type]', updated_by = '$uid', updated_on = now() where id = '$_POST[rid]';");
			} else {
				$con->dbquery("INSERT IGNORE INTO services_master (`code`,ihomis_code,`description`,short_description,fulldescription,report_title,category,subcategory,rev_acct,unit,unit_cost,unit_price,with_specimen,sample_type,with_subtests,with_bom,lab_tat,collection_tat,accession_tat,processing_tat,result_tat,container_type,result_type,created_by,created_on) VALUES ('$_POST[item_code]','$_POST[item_barcode]','".$con->escapeString($_POST['item_description'])."','$_POST[item_shortdesc]','".$con->escapeString($_POST['item_fdescription'])."','".$con->escapeString(htmlentities($_POST['report_title']))."','$_POST[item_category]','$_POST[item_sgroup]','$_POST[rev_acct]','$_POST[item_unit]','".$con->formatDigit($_POST['item_unitcost'])."','".$con->formatDigit($_POST['item_unitprice'])."','$_POST[with_specimen]','$_POST[sample_type]','$_POST[with_subtests]','$_POST[with_bom]','$_POST[lab_tat]','".$con->formatDigit($_POST['collection_tat'])."','".$con->formatDigit($_POST['accession_tat'])."','".$con->formatDigit($_POST['processing_tat'])."','$_POST[result_tat]','$_POST[container_type]','$_POST[result_type]','$uid',now());");
			}
		break;

		case "checkifBoM":
			$e = $con->getArray("select count(*) from services_bom where `code` = '$_POST[scode]' and item_code = '$_POST[icode]';");
			if($e[0] == 0) { echo "ok"; }
		break;

		case "newBOM":
			$con->dbquery("insert ignore into services_bom (`code`,item_code,description,unit,qty,unit_cost,amount,remarks,created_by,created_on) values ('$_POST[scode]','$_POST[icode]','".$con->escapeString($_POST['description'])."','$_POST[unit]','".$con->formatDigit($_POST['qty'])."','".$con->formatDigit($_POST['cost'])."','".$con->formatDigit($_POST['amount'])."','".$con->escapeString($_POST['remarks'])."','$uid',now());");
		break;

		case "retrieveBoM":
			echo json_encode($con->getArray("select *, format(unit_cost,2) as ucost, format(amount,2) as amt from services_bom where record_id = '$_POST[rid]';"));
		break;

		case "updateBoM":
			$con->dbquery("update ignore services_bom set qty = '".$con->formatDigit($_POST['qty'])."', amount = '".$con->formatDigit($_POST['amount'])."', remarks = '".$con->escapeString($_POST['remarks'])."', updated_by = '$uid', updated_on = now() where record_id = '$_POST[rid]';");
		break;

		case "removeBoM":
			$con->dbquery("delete from services_bom where record_id = '$_POST[rid]';");
		break;

		case "addSublist":
			$con->dbquery("INSERT IGNORE INTO services_subtests (`parent`,`code`,`description`) values ('$_POST[parent]','$_POST[code]','".$con->escapeString($_POST['description'])."');");
		break;

		case "removeSublist":
			$con->dbquery("DELETE FROM services_subtests where record_id = '$_POST[lid]';");
		break;

		case "addAttribute":
			$con->dbquery("INSERT IGNORE INTO lab_testvalues (`code`,`attribute_type`,`attribute`,`unit`,`min_value`,`max_value`,`critical_low_value`,`critical_high_value`,`descriptive_value`) values ('$_POST[parent]','$_POST[attr_type]','" . $con->escapeString($_POST['attr']) . "','$_POST[unit]','$_POST[min]','$_POST[max]','$_POST[low]','$_POST[high]','". $con->escapeString($_POST['desc']) . "');");
		break;

		case "retrieveTestValues":
			echo json_encode($con->getArray("select * from lab_testvalues where record_id = '$_POST[lid]';"));
		break;

		case "updateAttribute":
			$con->dbquery("UPDATE IGNORE lab_testvalues set attribute_type = '$_POST[attr_type]', attribute = '" . $con->escapeString($_POST['attr']) . "', unit = '$_POST[unit]', min_value = '$_POST[min]', max_value = '$_POST[max]', critical_low_value = '$_POST[low]', critical_high_value = '$_POST[high]', descriptive_value = '". $con->escapeString($_POST['desc']) . "', updated_by = '$uid', updated_on = now() where record_id = '$_POST[lid]';");
		break;

		case "removeAttribute":
			$con->dbquery("DELETE FROM lab_testvalues where record_id = '$_POST[lid]';");
		break;

		case "saveXrayTemplate":
			if($_POST['tempid'] == '') {
				$con->dbquery("INSERT IGNORE INTO xray_templates (title,template,xray_type,template_owner,created_on) VALUES ('".htmlentities($_POST['template_title'])."','".htmlentities($_POST['template_details'])."','$_POST[template_type]','".htmlentities($_POST['template_owner'])."',now());");
			} else {
				$con->dbquery("UPDATE IGNORE xray_templates set title = '".htmlentities($_POST['template_title'])."', template = '".htmlentities($_POST['template_details'])."', xray_type = '$_POST[template_type]', template_owner = '".htmlentities($_POST['template_owner'])."', updated_by = '$_SESSION[userid]', updated_on = now() where id = '$_POST[tempid]';");
			}
		break;

		/* Laboratory Information System */
		case "grabPatient":
			$con->dbquery("INSERT IGNORE INTO queueing (priority_no,so_no,patient_name,gender,calling_station,date_queued,time_queued,queued_by) values ('$_POST[pri_no]','$_POST[so_no]','".$con->escapeString($_POST['patient'])."','$_POST[gender]','$_POST[callStation]','".date('Y-m-d')."',now(),'$_SESSION[userid]');");
		break;

		case "retrievePatientForPeme":
			echo json_encode($con->getArray("SELECT a.*, b.lname, b.fname, b.mname, d.patient_address, DATE_FORMAT(b.birthdate,'%m/%d/%Y') AS bday, b.gender, b.occupation, b.employer, b.mobile_no, c.civil_status, (YEAR(a.so_date) - YEAR(b.birthdate)) AS age FROM peme a LEFT JOIN patient_info b ON a.pid = b.patient_id left join options_civilstatus c on b.cstat = c.csid left join so_header d on a.so_no = d.so_no and a.branch = d.branch WHERE a.so_no = '$_POST[so_no]' AND a.branch = '$bid';"));
		break;

		case "retrieveOrderForSample":

			//echo "SELECT a.enccode, DATE_FORMAT(dodate,'%m/%d/%Y %h:%i %p') AS orderdate, DATE_FORMAT(dodate,'%Y-%m-%d') AS xorderdate, a.hpercode, URLENCODE(c.patlast) as patlast, c.patfirst, c.patmiddle, DATE_FORMAT(c.patbdate,'%m/%d/%Y') AS bday,  DATE_FORMAT(c.patbdate,'%Y-%m-%d') AS xbday, DATE_FORMAT(c.patbdate,'%Y-%m-%d') AS bday, IF(c.patsex='F','Female','Male') AS sex, a.proccode, b.procdesc, a.donotes AS remarks, a.licno, a.estatus, entby FROM hospital_dbo.hdocord a LEFT JOIN hospital_dbo.hprocm b ON a.proccode = b.proccode LEFT JOIN hospital_dbo.hperson c ON a.hpercode = c.hpercode WHERE a.enccode = '$_POST[enccode]' and a.proccode = '$_POST[proccode]';";

			//if($_SESSION['userid'] == 1) {
				//echo "select count(*) as samplecount from (SELECT a.proccode, b.procdesc FROM hospital_dbo.hdocord a LEFT JOIN hospital_dbo.hprocm b ON a.proccode = b.proccode LEFT JOIN ppp_danao.services_master c ON a.proccode = c.ihomis_code WHERE a.enccode = '$a[enccode]' AND c.container_type = '$d[container_type]' and c.sample_type = '$d[sample_type]') a;";
				//echo "SELECT a.enccode, DATE_FORMAT(dodate,'%m/%d/%Y %h:%i %p') AS orderdate, DATE_FORMAT(dodate,'%Y-%m-%d') AS xorderdate, a.hpercode, URLENCODE(c.patlast) as patlast, URLENCODE(c.patfirst) as patfirst, URLENCODE(c.patmiddle) as patmiddle, DATE_FORMAT(c.patbdate,'%m/%d/%Y') AS bday,  DATE_FORMAT(c.patbdate,'%Y-%m-%d') AS xbday, DATE_FORMAT(c.patbdate,'%Y-%m-%d') AS bday, IF(c.patsex='F','F','M') AS sex, a.proccode, b.procdesc, a.donotes AS remarks, a.licno, a.estatus, entby FROM hospital_dbo.hdocord a LEFT JOIN hospital_dbo.hprocm b ON a.proccode = b.proccode LEFT JOIN hospital_dbo.hperson c ON a.hpercode = c.hpercode WHERE a.enccode = '$_POST[enccode]' and a.proccode = '$_POST[proccode]';";
			//}

			$a = $con->getArray("SELECT a.enccode, DATE_FORMAT(dodate,'%m/%d/%Y %h:%i %p') AS orderdate, DATE_FORMAT(dodate,'%Y-%m-%d') AS xorderdate, a.hpercode, URLENCODE(c.patlast) as patlast, URLENCODE(c.patfirst) as patfirst, URLENCODE(c.patmiddle) as patmiddle, DATE_FORMAT(c.patbdate,'%m/%d/%Y') AS bday,  DATE_FORMAT(c.patbdate,'%Y-%m-%d') AS xbday, DATE_FORMAT(c.patbdate,'%Y-%m-%d') AS bday, IF(c.patsex='F','F','M') AS sex, a.proccode, b.procdesc, a.donotes AS remarks, a.licno, a.estatus, entby FROM hospital_dbo.hdocord a LEFT JOIN hospital_dbo.hprocm b ON a.proccode = b.proccode LEFT JOIN hospital_dbo.hperson c ON a.hpercode = c.hpercode WHERE a.enccode = '$_POST[enccode]' and a.proccode = '$_POST[proccode]' and a.dodate = '$_POST[dotime]';");
			$b = $con->getArray("SELECT CONCAT(empprefix,'. ',firstname, ' ',middlename, ' ', lastname) as physician FROM hospital_dbo.hprovider a LEFT JOIN hospital_dbo.hpersonal b ON a.employeeid = b.employeeid WHERE a.licno = '$a[licno]';");
			$c = $con->getArray("SELECT `code` as primecode from services_master where ihomis_code = '$a[proccode]';");		
	
			$d = $con->getArray("select sample_type, container_type from services_master where ihomis_code = '$_POST[proccode]';");
			$e = $con->getArray("select sample_type as xsample from options_sampletype where id = '$d[sample_type]';");
			
			/* Generate Auatomatic Serial No. for Specimen */
			$series = $con->getArray("SELECT CONCAT(DATE_FORMAT(NOW(),'%Y%m'),LPAD(IFNULL(MAX(series+1),1),6,0)) as series FROM (SELECT TRIM(LEADING '0' FROM SUBSTRING(`serialno`,7,7)) AS series FROM lab_samples where LEFT(serialno,6) = DATE_FORMAT(NOW(),'%Y%m')) a");

			/* Patient Age */
			$con->calculateAge($a['xorderdate'],$a['xbday']);
			$f = array("age"=>$con->ageDisplay);

			/* Count for tests that uses similar container & sample type */
			$scount = $con->getArray("select count(*) as samplecount from (SELECT a.proccode, b.procdesc FROM hospital_dbo.hdocord a LEFT JOIN hospital_dbo.hprocm b ON a.proccode = b.proccode LEFT JOIN ppp_danao.services_master c ON a.proccode = c.ihomis_code WHERE a.enccode = '$a[enccode]' AND c.container_type = '$d[container_type]' and c.sample_type = '$d[sample_type]') a;");
			$result = array_merge($a,$b,$c,$d,$e,$f,$series,$scount);

			echo json_encode($result);
		break;

		case "retrieveSameSample":

			$cat = $con->getArray("select category,subcategory,sample_type,container_type from services_master where ihomis_code = '$_POST[proccode]';");
			$sQuery = $con->dbquery("SELECT a.proccode, b.procdesc, a.dotime FROM hospital_dbo.hdocord a LEFT JOIN hospital_dbo.hprocm b ON a.proccode = b.proccode LEFT JOIN ppp_danao.services_master c ON a.proccode = c.ihomis_code WHERE a.enccode = '$_POST[enccode]' and a.proccode != '$_POST[proccode]' and c.category = '$cat[category]' and c.subcategory = '$cat[subcategory]' and a.dotime = '$_POST[dotime]' group by a.proccode;");
			
			if($cat['subcategory'] == 1) {

				$html  = '<fieldset name="sameTests" id="sameTest" style="padding:5px;">
							<legend class="bareBold" style="font-size: 9px;">Use sample for the following request: </legend>
							';
				while($sRow = $sQuery->fetch_array()) {

					list($isCollected) = $con->getArray("select count(*) from lab_samples where enccode = '$_POST[enccode]' and `code` = '$sRow[proccode]' and dotime = '$sRow[dotime]';");

					if($isCollected == 0) {
						$html .= '<input type="checkbox" id="othercodes[]" name="othercodes[]" value="' . $sRow['proccode'] . '" checked>&nbsp;<span class="bareBold">'.$sRow['procdesc'].'</span><br/>';
					}
				}
				$html .= '</fieldset>';
				echo $html;

			}
		break;

		case "retrieveSameSampleForPrint":

			$cat = $con->getArray("select category,subcategory,sample_type,container_type from services_master where ihomis_code = '$_POST[proccode]';");
			$sQuery = $con->dbquery("SELECT a.proccode, b.procdesc, c.code FROM hospital_dbo.hdocord a LEFT JOIN hospital_dbo.hprocm b ON a.proccode = b.proccode LEFT JOIN ppp_danao.services_master c ON a.proccode = c.ihomis_code WHERE a.enccode = '$_POST[enccode]' AND c.container_type = '$cat[container_type]' and c.sample_type = '$cat[sample_type]' and c.category = '$cat[category]' and c.subcategory = '$cat[subcategory]' and a.dotime = '$_POST[dotime]' group by a.proccode;");
		
			$html  = '<fieldset name="sameTests" id="sameTest" style="padding:5px; margin-left: 20px; margin-right: 20px;">
						<legend class="bareBold" style="font-size: 9px;">Use sample for the following request: </legend>
						';
			while($sRow = $sQuery->fetch_array()) {
				$html .= '<input type="checkbox" id="othercodes[]" name="othercodes[]" value="' . $sRow['code'] . '" checked>&nbsp;<span class="bareBold">'.$sRow['procdesc'].'</span><br/>';
			}
			$html .= '</fieldset>';
			echo $html;
		break;
		
		case "saveSample":
		
			$tmpdate = explode(" ",$_POST['phleb_date']);
			$extractDate = $con->formatDate(trim($tmpdate[0]));
			$extractTime = $tmpdate[1];

			
			list($isE) = $con->getArray("select count(*) from lab_samples where serialno = '$_POST[phleb_serialno]' and `code` = '$_POST[phleb_code]';");
			if($isE > 0) {
				$con->dbquery("UPDATE IGNORE lab_samples physician = '".htmlentities($_POST['phleb_physician'])."', testkit = '$_POST[phleb_testkit]', lotno = '$_POST[phleb_testkit_lotno]', expiry = '".$con->formatDate($_POST['phleb_testkit_expiry'])."', extractdate = '$extractDate', extractime = '$extractTime', extractby = '".$con->escapeString(htmlentities($_POST['phleb_by']))."', `location` = '$_POST[phleb_location]', remarks = '".$con->escapeString(htmlentities($_POST['phleb_remarks']))."', updated_by = '$uid', updated_on = now() WHERE `code` = '$_POST[phleb_code]' and serialno = '$_POST[phleb_serialno]' and enccode = '$_POST[phleb_sodetails]';");
			} else {
				$con->dbquery("INSERT IGNORE INTO lab_samples (enccode,dotime,`code`,`procedure`,`primecarecode`,`sampletype`,samplecontainer,serialno,physician,patientname,testkit,lotno,expiry,extractdate,extractime,extractby,location,remarks) VALUES ('$_POST[phleb_sodetails]','$_POST[phleb_dotime]','$_POST[phleb_code]','$_POST[phleb_description]','$_POST[phleb_primecode]','$_POST[phleb_spectype]','$_POST[phleb_containertype]','$_POST[phleb_serialno]','".htmlentities($_POST['phleb_physician'])."','".htmlentities($_POST['phleb_pname'])."','$_POST[phleb_testkit]','$_POST[phleb_testkit_lotno]','".$con->formatDate($_POST['phleb_testkit_expiry'])."','$extractDate','$extractTime','".$con->escapeString(htmlentities($_POST['phleb_by']))."','$_POST[phleb_location]','".$con->escapeString(htmlentities($_POST['phleb_remarks']))."');");
			}

			/* Check if other samples */
			if(count($_POST['othercodes']) > 0) {
				foreach($_POST['othercodes'] as $scode) {
					
					list($isE) = $con->getArray("select count(*) from lab_samples where serialno = '$_POST[phleb_serialno]' and `code` = '$scode';");
					if($isE > 0) {
						$con->dbquery("UPDATE IGNORE lab_samples physician = '".htmlentities($_POST['phleb_physician'])."', testkit = '$_POST[phleb_testkit]', lotno = '$_POST[phleb_testkit_lotno]', expiry = '".$con->formatDate($_POST['phleb_testkit_expiry'])."', extractdate = '$extractDate', extractime = '$extractTime', extractby = '".$con->escapeString(htmlentities($_POST['phleb_by']))."', `location` = '$_POST[phleb_location]', remarks = '".$con->escapeString(htmlentities($_POST['phleb_remarks']))."', updated_by = '$uid', updated_on = now() WHERE `code` = '$_POST[phleb_code]' and serialno = '$_POST[phleb_serialno]' and enccode = '$_POST[phleb_sodetails]';");
					} else {

						list($description) = $con->getArray("SELECT procdesc FROM hospital_dbo.hprocm WHERE proccode = '$scode';");
						list($primecode,$stype,$ctype) = $con->getArray("select `code`,sample_type,container_type from services_master where ihomis_code = '$scode';");
						$con->dbquery("INSERT IGNORE INTO lab_samples (enccode,dotime,`code`,`procedure`,`primecarecode`,`sampletype`,samplecontainer,serialno,physician,patientname,testkit,lotno,expiry,extractdate,extractime,extractby,location,remarks) VALUES ('$_POST[phleb_sodetails]','$_POST[phleb_dotime]','$scode','$description','$primecode','$stype','$ctype','$_POST[phleb_serialno]','".htmlentities($_POST['phleb_physician'])."','".htmlentities($_POST['phleb_pname'])."','$_POST[phleb_testkit]','$_POST[phleb_testkit_lotno]','".$con->formatDate($_POST['phleb_testkit_expiry'])."','$extractDate','$extractTime','".$con->escapeString(htmlentities($_POST['phleb_by']))."','$_POST[phleb_location]','".$con->escapeString(htmlentities($_POST['phleb_remarks']))."');");
					}
				}
			}
	
		break;

		case "checkSerialStatus":
			echo json_encode($con->getArray("select count(*) as mycount from lab_samples where serialno = '$_POST[serialno]';"));
		break;

		case "checkMultipleChem":
			list($cat,$scat) = $con->getArray("SELECT category, subcategory FROM services_master WHERE `code` = '$_POST[code]';");
			if($cat == 1 && ($scat == 1 || $scat == 5)) {
				echo json_encode($con->getArray("select count(*) as mycount from lab_samples where serialno = '$_POST[serialno]' and `primecarecode` in (select `code` from services_master where category = '1' and subcategory in (1,5)) and `status` != '4';"));
			} else {
				echo json_encode(array("mycount"=>1));

			}		
		break;

		case "retrieveSample":
			echo json_encode($con->getArray("SELECT b.patient_name AS pname,`code`,`procedure`,sampletype,serialno, location, DATE_FORMAT(extractdate,'%m/%d/%Y') AS exdate,  SUBSTR(extractime,1,2) AS hr, SUBSTR(extractime,4,2) AS MIN, extractby FROM lab_samples a LEFT JOIN so_header b ON a.so_no = b.so_no AND a.branch = b.branch WHERE a.record_id = '$_POST[lid]';"));
		break;

		case "rejectSample":
			$con->dbquery("update IGNORE lab_samples set status = '2', rejection_remarks = '".$con->escapeString(htmlentities())."', updated_by = '$uid', updated_on = now() where record_id = '$_POST[lid]';");
		break;

		case "resultSingle":

		
			$order = $con->getArray("select enccode, `code`, primecarecode, `procedure`, serialno, sampletype, samplecontainer, physician, location, date_format(extractdate,'%m/%d/%Y') as exdate, extractby, extractime from lab_samples where record_id = '$_POST[lid]';");
			$a = $con->getArray("SELECT a.enccode,  SUBSTR(enccode,8,15) AS hmrno, DATE_FORMAT(dodate,'%m/%d/%Y %h:%i %p') AS orderdate, DATE_FORMAT(dodate,'%Y-%m-%d') AS xorderdate, a.hpercode, URLENCODE(concat(c.patlast,', ', c.patfirst,', ', c.patmiddle)) as pname, DATE_FORMAT(c.patbdate,'%m/%d/%Y') AS bday,  DATE_FORMAT(c.patbdate,'%Y-%m-%d') AS xbday, DATE_FORMAT(c.patbdate,'%m/%d/%Y') AS bday, IF(c.patsex='F','Female','Male') AS sex, a.proccode, b.procdesc, a.donotes AS remarks, a.licno, a.estatus, entby FROM hospital_dbo.hdocord a LEFT JOIN hospital_dbo.hprocm b ON a.proccode = b.proccode LEFT JOIN hospital_dbo.hperson c ON a.hpercode = c.hpercode WHERE a.enccode = '$order[enccode]' and a.proccode = '$order[code]';");
			
			$con->calculateAge($a['xorderdate'],$a['xbday']);
			$c = array("age"=>$con->age);

			switch($_POST['submod']) {
				case "labSingle":
					list($isCount) = $con->getArray("select count(*) from lab_singleResult where enccode = '$order[enccode]' and code = '$order[code]' and serialno = '$order[serialno]';");
					if($isCount > 0) {
						$b = $con->getArray("SELECT attribute,unit,lower_value as `min_value`,upper_value as `max_value`,`value`,remarks FROM lab_singleResult WHERE enccode = '$order[enccode]' and code = '$order[code]' and serialno = '$order[serialno]';");	
					} else {
						$b = $con->getArray("SELECT attribute,unit,`min_value`,`max_value`,'' as `value`,'' as remarks FROM lab_testvalues WHERE `code` = '$order[primecarecode]';");
					}
				break;
				case "enumResult":
					$b = $con->getArray("select patient_stat, result, performed_by, remarks from lab_enumresult where enccode = '$order[enccode]' and serialno = '$order[serialno]' and code = '$order[code]';");
					//echo "select patient_stat, result, performed_by, remarks from lab_enumresult where enccode = '$order[enccode]' and serialno = '$order[serialno]' and code = '$order[code]';";
				break;
				case "bloodType":
					$b = $con->getArray("select patient_stat, result, rh, performed_by, remarks from lab_bloodtyping where enccode = '$order[enccode]' and serialno = '$order[serialno]' and code = '$order[code]';");
					if(!$b) { $b = array("rh"=>'Positive',"result"=>'A+'); }
				break;
				case "dengueDuo":
					$b = $con->getArray("select result, result2, result3, remarks from lab_dengueduo where enccode = '$order[enccode]' and serialno = '$order[serialno]';");
				break;
				case "ogtt":
					$b = $con->getArray("select result, result2, result3, remarks from lab_ogtt where enccode = '$order[enccode]' and serialno = '$order[serialno]';");
				break;
				case "prothrombinRes":
					$b = $con->getArray("select seconds, percent, inr, remarks from lab_prothrombin where enccode = '$order[enccode]' and serialno = '$order[serialno]';");
				break;
				case "cbs":
					$b = $con->getArray("select time, result, remarks from lab_cbs where enccode = '$order[enccode]' and serialno = '$order[serialno]';");
				break;
		
			}

			if(count($b) > 0) {
				echo json_encode(array_merge($a,$b,$c,$order));
			} else { echo json_encode(array_merge($a,$c,$order)); }
		break;

		case "saveEnumResult":
			list($cnt) = $con->getArray("select count(*) from lab_enumresult where enccode = '$_POST[enum_enccode]' and code = '$_POST[enum_code]' and serialno = '$_POST[enum_serialno]';");
			if($cnt > 0) {
				$con->dbquery("UPDATE IGNORE lab_enumresult set result_date = '".$con->formatDate($_POST['enum_date'])."', patient_stat = '$_POST[enum_patientstat]', result = '$_POST[enum_result]', performd_by = '$_POST[enum_result_by]', remarks = '".$con->escapeString($_POST['enum_remarks']) . "' where enccode = '$_POST[enum_enccode]' code = '$_POST[enum_code]' and serialno = '$_POST[enum_serialno]';");
			} else {
				$con->dbquery("INSERT IGNORE INTO lab_enumresult (enccode,result_date,patient_stat,sampletype,serialno,code,result,performed_by,remarks,created_by,created_on) VALUES ('$_POST[enum_enccode]','".$con->formatDate($_POST['enum_date'])."','$_POST[enum_patientstat]','$_POST[enum_spectype]','$_POST[enum_serialno]','$_POST[enum_code]','$_POST[enum_result]','$_POST[enum_result_by]','".$con->escapeString($_POST['enum_remarks'])."','$uid',now());");
			}
			$con->updateLabSampleStatus($_POST['enum_sono'],$_POST['enum_code'],$_POST['enum_serialno'],'3',$bid,$uid);
		break;

		case "validateEnumResult":
			$con->dbquery("UPDATE IGNORE lab_enumresult set result_date = '".$con->formatDate($_POST['enum_date'])."', patient_stat = '$_POST[enum_patientstat]', result = '$_POST[enum_result]', performd_by = '$_POST[enum_result_by]', remarks = '".$con->escapeString($_POST['enum_remarks']) . "' where enccode = '$_POST[enum_enccode]' and code = '$_POST[enum_code]' and serialno = '$_POST[enum_serialno]';");
			$con->validateResult("lab_enumresult",$_POST['enum_sono'],$_POST['enum_code'],$_POST['enum_serialno'],$bid,$uid);
			$con->updateLabSampleStatus($_POST['enum_sono'],$_POST['enum_code'],$_POST['enum_serialno'],'4',$uid);	
		break;

		case "saveDengueDuo":
			list($cnt) = $con->getArray("select count(*) from lab_dengueduo where enccode = '$_POST[dengue_enccode]' and serialno = '$_POST[dengue_serialno]';");
			if($cnt > 0) {
				$con->dbquery("UPDATE IGNORE lab_dengueduo set result_date = '".$con->formatDate($_POST['dengue_sodate'])."', result = '$_POST[dengue_result]', result2 = '$_POST[dengue_result2]', result3 = '$_POST[dengue_result3]', performed_by = '$_POST[dengue_result_by]', remarks = '".$con->escapeString($_POST['dengue_remarks']) . "' where enccode = '$_POST[dengue_enccode]' and code = '$_POST[dengue_code]' and serialno = '$_POST[dengue_serialno]';");
			} else {
				$con->dbquery("INSERT IGNORE INTO lab_dengueduo (enccode,result_date,sampletype,serialno,code,result,result2,result3,performed_by,remarks,created_by,created_on) VALUES ('$_POST[dengue_enccode]','".$con->formatDate($_POST['dengue_sodate'])."','$_POST[dengue_spectype]','$_POST[dengue_serialno]','$_POST[dengue_code]','$_POST[dengue_result]','$_POST[dengue_result2]','$_POST[dengue_result3]','$_POST[dengue_result_by]','".$con->escapeString($_POST['dengue_remarks'])."','$uid',now());");
			}
			$con->updateLabSampleStatus($_POST['dengue_enccode'],$_POST['dengue_code'],$_POST['dengue_serialno'],'3',$uid);
		break;

		case "validateDengueDuo":
			$con->dbquery("UPDATE lab_dengueduo set result_date = '".$con->formatDate($_POST['dengue_sodate'])."', result = '$_POST[dengue_result]', result2 = '$_POST[dengue_result2]', result3 = '$_POST[dengue_result2]', remarks = '".$con->escapeString($_POST['dengue_remarks']) . "' where enccode = '$_POST[dengue_enccode]' and code = '$_POST[dengue_code]' and serialno = '$_POST[dengue_serialno]';");
			$con->validateResult("lab_dengueduo",$_POST['dengue_enccode'],$_POST['dengue_code'],$_POST['dengue_serialno'],$uid);
			$con->updateLabSampleStatus($_POST['dengue_enccode'],$_POST['dengue_code'],$_POST['dengue_serialno'],'4',$uid);	
		break;

		case "saveOGTT":
			list($cnt) = $con->getArray("select count(*) from lab_ogtt where enccode = '$_POST[ogtt_enccode]' and serialno = '$_POST[ogtt_serialno]';");
			if($cnt > 0) {
				$con->dbquery("UPDATE IGNORE lab_ogtt set result_date = '".$con->formatDate($_POST['ogtt_sodate'])."', result = '$_POST[ogtt_fbs]', result2 = '$_POST[ogtt_fhours]', result3 = '$_POST[ogtt_2hours]', performed_by = '$_POST[ogtt_result_by]', remarks = '".$con->escapeString($_POST['ogtt_remarks']) . "' where enccode = '$_POST[ogtt_enccode]' and code = '$_POST[ogtt_code]' and serialno = '$_POST[ogtt_serialno]';");
			} else {
				$con->dbquery("INSERT IGNORE INTO lab_ogtt (enccode,result_date,sampletype,serialno,code,result,result2,result3,performed_by,remarks,created_by,created_on) VALUES ('$_POST[ogtt_enccode]','".$con->formatDate($_POST['ogtt_date'])."','$_POST[ogtt_spectype]','$_POST[ogtt_serialno]','$_POST[ogtt_code]','$_POST[ogtt_fbs]','$_POST[ogtt_fhours]','$_POST[ogtt_2hours]','$_POST[ogtt_result_by]','".$con->escapeString($_POST['ogtt_remarks'])."','$uid',now());");
			}
			$con->updateLabSampleStatus($_POST['ogtt_enccode'],$_POST['ogtt_code'],$_POST['ogtt_serialno'],'3',$uid);
		break;

		case "validateOGTT":
			$con->dbquery("UPDATE IGNORE lab_ogtt set result_date = '".$con->formatDate($_POST['ogtt_sodate'])."', result = '$_POST[ogtt_fbs]', result2 = '$_POST[ogtt_fhours]', result3 = '$_POST[ogtt_2hours]', remarks = '".$con->escapeString($_POST['ogtt_remarks']) . "' where enccode = '$_POST[ogtt_enccode]' and code = '$_POST[ogtt_code]' and serialno = '$_POST[ogtt_serialno]';");
			$con->validateResult("lab_ogtt",$_POST['ogtt_enccode'],$_POST['ogtt_code'],$_POST['ogtt_serialno'],$uid);
			$con->updateLabSampleStatus($_POST['ogtt_enccode'],$_POST['ogtt_code'],$_POST['ogtt_serialno'],'4',$uid);	
		break;

		case "saveCBSResult":
			list($cnt) = $con->getArray("select count(*) from lab_cbs where enccode = '$_POST[cbs_enccode]' and serialno = '$_POST[cbs_serialno]';");
			if($cnt > 0) {
				$con->dbquery("UPDATE lab_cbs set result_date = '".$con->formatDate($_POST['cbs_date'])."', time = '$_POST[cbs_time]', result = '$_POST[cbs_result]', created_by = '$uid', remarks = '".$con->escapeString($_POST['cbs_remarks']) . "' where enccode = '$_POST[cbs_enccode]' and code = '$_POST[cbs_code]' and serialno = '$_POST[cbs_serialno]';");
			} else {
				$con->dbquery("INSERT INTO lab_cbs (enccode,result_date,sampletype,serialno,code,time,result,remarks,created_by,created_on) VALUES ('$_POST[cbs_enccode]','".$con->formatDate($_POST['cbs_sodate'])."','$_POST[cbs_spectype]','$_POST[cbs_serialno]','$_POST[cbs_code]','$_POST[cbs_time]','$_POST[cbs_result]','".$con->escapeString($_POST['cbs_remarks'])."','$uid',now());");
			}
			$con->updateLabSampleStatus($_POST['cbs_enccode'],$_POST['cbs_code'],$_POST['cbs_serialno'],'3',$uid);
		break;

		case "validateCBSResult":
			$con->dbquery("UPDATE lab_cbs set result_date = '".$con->formatDate($_POST['cbs_date'])."', time = '$_POST[cbs_time]', result = '$_POST[cbs_result]', created_by = '$uid', remarks = '".$con->escapeString($_POST['cbs_remarks']) . "',updated_by = '$uid', updated_on = now() where enccode = '$_POST[cbs_enccode]' and code = '$_POST[cbs_code]' and serialno = '$_POST[cbs_serialno]';");
			$con->validateResult("lab_cbs",$_POST['cbs_enccode'],$_POST['cbs_code'],$_POST['cbs_serialno'],$uid);
			$con->updateLabSampleStatus($_POST['cbs_enccode'],$_POST['cbs_code'],$_POST['cbs_serialno'],'4',$uid);	
		break;

		case "saveCoagulationResult":
			list($cnt) = $con->getArray("select count(*) from lab_coagulation where enccode = '$_POST[coagulation_enccode]' and serialno = '$_POST[coagulation_serialno]';");
			if($cnt > 0) {
				$con->dbquery("UPDATE lab_coagulation set result_date = '".$con->formatDate($_POST['coagulation_date'])."', proth_seconds = '$_POST[proth_seconds]', proth_percent = '$_POST[proth_percent]', proth_inr = '$_POST[proth_inr]', aptt_seconds = '$_POST[aptt_seconds]', created_by = '$uid', remarks = '".$con->escapeString($_POST['remarks']) . "' where enccode = '$_POST[coagulation_enccode]' and code = '$_POST[coagulation_procedure]' and serialno = '$_POST[coagulation_serialno]';");
			} else {
				$con->dbquery("INSERT INTO lab_coagulation (enccode,result_date,sampletype,serialno,proth_seconds,proth_percent,proth_inr,aptt_seconds,remarks,created_by,created_on) VALUES ('$_POST[coagulation_enccode]','".$con->formatDate($_POST['coagulation_date'])."','$_POST[coagulation_spectype]','$_POST[coagulation_serialno]','$_POST[proth_seconds]','$_POST[proth_percent]','$_POST[proth_inr]','$_POST[aptt_seconds]','".$con->escapeString($_POST['remarks'])."','$uid',now());");
			}
			$con->updateLabSampleStatus($_POST['coagulation_enccode'],'LABOR00269',$_POST['coagulation_serialno'],'3',$uid);
			$con->updateLabSampleStatus($_POST['coagulation_enccode'],'LABOR00106',$_POST['coagulation_serialno'],'3',$uid);
		break;

		case "validateCoagulationResult":
			$con->dbquery("UPDATE lab_coagulation set result_date = '".$con->formatDate($_POST['coagulation_date'])."', proth_seconds = '$_POST[proth_seconds]', proth_percent = '$_POST[proth_percent]', proth_inr = '$_POST[proth_inr]', aptt_seconds = '$_POST[aptt_seconds]', created_by = '$uid', remarks = '".$con->escapeString($_POST['remarks']) . "' where enccode = '$_POST[coagulation_enccode]' and code = '$_POST[coagulation_procedure]' and serialno = '$_POST[coagulation_serialno]';");
			$con->validateResult("lab_coagulation",$_POST['coagulation_enccode'],$_POST['coagulation_proccode'],$_POST['coagulation_serialno'],$uid);
			$con->updateLabSampleStatus($_POST['coagulation_enccode'],'LABOR00269',$_POST['coagulation_serialno'],'4',$uid);	
			$con->updateLabSampleStatus($_POST['coagulation_enccode'],'LABOR00106',$_POST['coagulation_serialno'],'4',$uid);	
		break;

		case "saveBloodType":
			list($cnt) = $con->getArray("select count(*) from lab_bloodtyping where enccode = '$_POST[btype_enccode]' and code = '$_POST[btype_code]' and serialno = '$_POST[btype_serialno]';");
			if($cnt > 0) {
				$con->dbquery("UPDATE lab_bloodtyping set patient_stat = '$_POST[btype_patientstat]', result = '$_POST[btype_result]', rh = '$_POST[btype_rh]', performed_by = '$uid', result_date = '".$con->formatDate($_POST['btype_date'])."', remarks = '".$con->escapeString($_POST['btype_remarks']) . "' where enccode = '$_POST[btype_enccode]' and code = '$_POST[btype_code]' and serialno = '$_POST[btype_serialno]';");
			} else {
				$con->dbquery("INSERT IGNORE INTO lab_bloodtyping (enccode,result_date,patient_stat,sampletype,serialno,code,result,rh,performed_by,remarks,created_by,created_on) VALUES ('$_POST[btype_enccode]','".$con->formatDate($_POST['btype_date'])."','$_POST[btype_patientstat]','$_POST[btype_spectype]','$_POST[btype_serialno]','$_POST[btype_code]','$_POST[btype_result]','$_POST[btype_rh]','$_POST[btype_result_by]','".$con->escapeString($_POST['btype_remarks'])."','$uid',now());");
			}
			$con->updateLabSampleStatus($_POST['btype_enccode'],$_POST['btype_code'],$_POST['btype_serialno'],'3',$uid);
		break;

		case "validateBloodType":
			$con->dbquery("UPDATE lab_bloodtyping set patient_stat = '$_POST[btype_patientstat]', result = '$_POST[btype_result]', rh = '$_POST[btype_rh]', performed_by = '$uid', result_date = '".$con->formatDate($_POST['btype_date'])."', remarks = '".$con->escapeString($_POST['btype_remarks']) . "' where enccode = '$_POST[btype_enccode]' and code = '$_POST[btype_code]' and serialno = '$_POST[btype_serialno]';");
			$con->validateResult("lab_bloodtyping",$_POST['btype_enccode'],$_POST['btype_code'],$_POST['btype_serialno'],$uid);
			$con->updateLabSampleStatus($_POST['btype_enccode'],$_POST['btype_code'],$_POST['btype_serialno'],'4',$uid);	
		
		break;

		case "savePregnancyResult":
			list($cnt) = $con->getArray("select count(*) from lab_enumresult where enccode = '$_POST[pt_enccode]' and code = '$_POST[pt_code]' and serialno = '$_POST[pt_serialno]';");
			if($cnt > 0) {
				$con->dbquery("UPDATE IGNORE lab_enumresult set result = '$_POST[pt_result]', result_date = '".$con->formatDate($_POST['pt_date'])."', remarks = '".$con->escapeString($_POST['pt_remarks']) . "' where enccode = '$_POST[pt_enccode] and code = '$_POST[pt_code]' and serialno = '$_POST[pt_serialno]';");
			} else {
				$con->dbquery("INSERT IGNORE INTO lab_enumresult (enccode,result_date,sampletype,serialno,code,result,remarks,created_by,created_on) VALUES ('$_POST[pt_enccode]','".$con->formatDate($_POST['pt_date'])."','$_POST[pt_spectype]','$_POST[pt_serialno]','$_POST[pt_code]','$_POST[pt_result]','".$con->escapeString($_POST['pt_remarks'])."','$uid',now());");
				//echo "INSERT INTO lab_enumresult (enccode,result_date,sampletype,serialno,code,result,remarks,created_by,created_on) VALUES ('$_POST[pt_enccode]','".$con->formatDate($_POST['pt_date'])."','$_POST[pt_spectype]','$_POST[pt_serialno]','$_POST[pt_code]','$_POST[pt_result]','".$con->escapeString($_POST['pt_remarks'])."','$uid',now());";
			}
			$con->updateLabSampleStatus($_POST['pt_enccode'],$_POST['pt_code'],$_POST['pt_serialno'],'3',$uid);
		break;

		case "validatePregnancyResult":
			$con->dbquery("UPDATE IGNORE lab_enumresult set result = '$_POST[pt_result]', result_date = '".$con->formatDate($_POST['pt_date'])."', remarks = '".$con->escapeString($_POST['pt_remarks']) . "' where enccode = '$_POST[pt_enccode]' and code = '$_POST[pt_code]' and serialno = '$_POST[pt_serialno]';");
			
			
			$con->validateResult("lab_enumresult",$_POST['pt_enccode'],$_POST['pt_code'],$_POST['pt_serialno'],$uid);
			$con->updateLabSampleStatus($_POST['pt_enccode'],$_POST['pt_code'],$_POST['pt_serialno'],'4',$uid);	
			//$con->dbquery("update so_details set result_available = 'Y' where so_no = '$_POST[pt_sono]' and branch = '$bid' and code = '$_POST[pt_code]' and sample_serialno = '$_POST[pt_serialno]';");
		
		break;

		case "saveSingleValueResult":
			list($cnt) = $con->getArray("select count(*) from lab_singleresult where enccode = '$_POST[sresult_enccode]' and code = '$_POST[sresult_code]' and serialno = '$_POST[sresult_serialno]';");
			if($cnt>0) {
				$con->dbquery("UPDATE IGNORE lab_singleresult SET result_date = '".$con->formatDate($_POST['sresult_date'])."', `attribute`='$_POST[sresult_attribute]',`value`='$_POST[sresult_value]',remarks='".$con->escapeString(htmlentities($_POST['sresult_remarks']))."', updated_by='$uid',updated_on = now() where enccode = '$_POST[sresult_enccode]' and code = '$_POST[sresult_code]' and serialno = '$_POST[sresult_serialno]';");
			} else {
				$con->dbquery("INSERT IGNORE INTO lab_singleresult (enccode,result_date,sampletype,serialno,`code`,`procedure`,primecarecode,`attribute`,unit,`value`,remarks,created_by,created_on) values ('$_POST[sresult_enccode]','".$con->formatDate($_POST['sresult_date'])."','$_POST[sresult_spectype]','$_POST[sresult_serialno]','$_POST[sresult_code]','".$con->escapeString(htmlentities($_POST['sresult_procedure']))."','$_POST[sresult_primecarecode]','$_POST[sresult_attribute]','$_POST[sresult_unit]','$_POST[sresult_value]','".$con->escapeString(htmlentities($_POST['sresult_remarks']))."','$uid',now());");
				//echo "INSERT IGNORE IGNORE INTO lab_singleresult (enccode,result_date,sampletype,serialno,`code`,`procedure`,primecarecode,`attribute`,unit,`value`,remarks,created_by,created_on) values ('$_POST[sresult_enccode]','".$con->formatDate($_POST['sresult_date'])."','$_POST[sresult_spectype]','$_POST[sresult_serialno]','$_POST[sresult_code]','".$con->escapeString(htmlentities($_POST['sresult_procedure']))."','$_POST[sresult_primecarecode]','$_POST[sresult_attribute]','$_POST[sresult_unit]','$_POST[sresult_value]','".$con->escapeString(htmlentities($_POST['sresult_remarks']))."','$uid',now());";
			}
			$con->updateLabSampleStatus($_POST['sresult_enccode'],$_POST['sresult_code'],$_POST['sresult_serialno'],'3',$uid);
		break;

		case "validateSingleValueResult":
			$con->dbquery("UPDATE IGNORE IGNORE lab_singleresult SET result_date = '".$con->formatDate($_POST['sresult_date'])."', `attribute`='$_POST[sresult_attribute]',`value`='$_POST[sresult_value]',lower_value='$_POST[sresult_lowerlimit]',upper_value='$_POST[sresult_upperlimit]',remarks='".$con->escapeString(htmlentities($_POST['sresult_remarks']))."', updated_by='$uid',updated_on = now() where so_no = '$_POST[sresult_sono]' and branch = '$bid' and code = '$_POST[sresult_code]' and serialno = '$_POST[sresult_serialno]';");
			$con->validateResult("lab_singleresult",$_POST['sresult_enccode'],$_POST['sresult_code'],$_POST['sresult_serialno'],$uid);
			$con->updateLabSampleStatus($_POST['sresult_enccode'],$_POST['sresult_code'],$_POST['sresult_serialno'],'4',$uid);	
		
		break;

		case "saveDescResult":
			list($cnt) = $con->getArray("select count(*) from lab_descriptive where so_no = '$_POST[desc_sono]' and branch = '$bid' and code = '$_POST[desc_code]' and serialno = '$_POST[desc_serialno]';");
			if($cnt>0) {
				$con->dbquery("UPDATE IGNORE lab_descriptive SET impression = '".$con->escapeString(htmlentities($_POST['desc_impression']))."', physician = '".htmlentities($_POST['desc_physician'])."', consultant = '".htmlentities($_POST['desc_consultant'])."', result_type = '$_POST[desc_resultstat]', result_date = '".$con->formatDate($_POST['desc_date'])."', updated_by='$uid',updated_on = now() where so_no = '$_POST[desc_sono]' and branch = '$bid' and serialno = '$_POST[desc_serialno]';");
			} else {
				$con->dbquery("INSERT IGNORE INTO lab_descriptive (branch,so_no,result_date,sampletype,serialno,`code`,`procedure`,impression,physician,consultant,result_type,created_by,created_on) values ('$bid','$_POST[desc_sono]','".$con->formatDate($_POST['desc_date'])."','$_POST[desc_spectype]','$_POST[desc_serialno]','$_POST[desc_code]','".$con->escapeString(htmlentities($_POST['desc_procedure']))."','".$con->escapeString(htmlentities($_POST['desc_impression']))."','".htmlentities($_POST['desc_physician'])."','".htmlentities($_POST['desc_consultant'])."','$_POST[desc_resultstat]','$uid',now());");
				$con->updateLabSampleStatus($_POST['desc_sono'],$_POST['desc_code'],$_POST['desc_serialno'],'3',$bid,$uid);	
			}
		break;

		case "validateDescResult":
			
			$con->validateResult("lab_descriptive",$_POST['desc_sono'],$_POST['desc_code'],$_POST['desc_serialno'],$bid,$uid);
			$con->updateLabSampleStatus($_POST['desc_sono'],$_POST['desc_code'],$_POST['desc_serialno'],'4',$bid,$uid);	
			$con->dbquery("update ignore so_details set result_available = 'Y' where so_no = '$_POST[desc_sono]' and branch = '$bid' and code = '$_POST[desc_code]' and sample_serialno = '$_POST[desc_serialno]';");
		
		break;

		case "invalidateDescResult":
			$con->dbquery("update ignore lab_descriptive set verified = 'N', verified_by = '', verified_on = '' where so_no = '$_POST[desc_sono]' and branch = '$bid' and code = '$_POST[desc_code]' and serialno = '$_POST[desc_serialno]';");
			$con->dbquery("update ignore so_details set result_available = 'N' where so_no = '$_POST[desc_sono]' and branch = '$bid' and code = '$_POST[desc_code]' and sample_serialno = '$_POST[desc_serialno]';");
			$con->dbquery("update ignore lab_samples set status = '3', updated_by = '$uid', updated_on = now() where so_no = '$_POST[desc_sono]' and branch = '$bid' and code = '$_POST[desc_code]' and serialno = '$_POST[desc_serialno]';");
		break;

		case "saveCBCResult":
			list($cnt) = $con->getArray("select count(*) from lab_cbcresult where enccode = '$_POST[cbc_enccode]' and serialno = '$_POST[cbc_serialno]';");
			if($cnt > 0) {
				$con->dbquery("update ignore lab_cbcresult set result_date = '".$con->formatDate($_POST['cbc_date']) ."', wbc = '".$con->formatDigit($_POST['wbc'])."',rbc = '".$con->formatDigit($_POST['rbc'])."',hemoglobin = '".$con->formatDigit($_POST['hemoglobin'])."', hematocrit = '".$con->formatDigit($_POST['hematocrit'])."', neutrophils = '".$con->formatDigit($_POST['neutrophils'])."', lymphocytes = '".$con->formatDigit($_POST['lymphocytes'])."', monocytes = '".$con->formatDigit($_POST['monocytes'])."',eosinophils = '".$con->formatDigit($_POST['eosinophils'])."', basophils = '".$con->formatDigit($_POST['basophils'])."', platelate = '".$con->formatDigit($_POST['platelate'])."', mcv = '".$con->formatDigit($_POST['mcv'])."', mch = '".$con->formatDigit($_POST['mch'])."', mchc = '".$con->formatDigit($_POST['mchc'])."', rdwcv = '".$con->formatDigit($_POST['rdwcv'])."', rdwsd = '".$con->formatDigit($_POST['rdwsd'])."', mpv = '".$con->formatDigit($_POST['mpv'])."', pdwcv = '".$con->formatDigit($_POST['pdwcv'])."', pdwsd = '".$con->formatDigit($_POST['pdwsd'])."', pct = '".$con->formatDigit($_POST['pct'])."', plcc = '".$con->formatDigit($_POST['plcc'])."', plcr = '".$con->formatDigit($_POST['plcr'])."', remarks = '".$con->escapeString(htmlentities($_POST['remarks']))."', updated_by = '$uid', updated_on = now() where enccode = '$_POST[cbc_enccode]' and serialno = '$_POST[cbc_serialno]';");
			} else {
				$con->dbquery("insert ignore into lab_cbcresult (enccode,result_date,sampletype,serialno,wbc,rbc,hemoglobin,hematocrit,neutrophils,lymphocytes,monocytes,eosinophils,basophils,platelate,mcv,mch,mchc,rdwcv,rdwsd,mpv,pdwcv,pdwsd,pct,plcc,plcr,remarks,created_by,created_on) values ('$_POST[cbc_enccode]','".$con->formatDate($_POST['cbc_date'])."','$_POST[cbc_spectype]','$_POST[cbc_serialno]','".$con->formatDigit($_POST['wbc'])."','".$con->formatDigit($_POST['rbc'])."','".$con->formatDigit($_POST['hemoglobin'])."','".$con->formatDigit($_POST['hematocrit'])."','".$con->formatDigit($_POST['neutrophils'])."','".$con->formatDigit($_POST['lymphocytes'])."','".$con->formatDigit($_POST['monocytes'])."','".$con->formatDigit($_POST['eosinophils'])."','".$con->formatDigit($_POST['basophils'])."','".$con->formatDigit($_POST['platelate'])."','".$con->formatDigit($_POST['mcv'])."','".$con->formatDigit($_POST['mch'])."','".$con->formatDigit($_POST['mchc'])."','".$con->formatDigit($_POST['rdwcv'])."','".$con->formatDigit($_POST['rdwsd'])."','".$con->formatDigit($_POST['mpv'])."','".$con->formatDigit($_POST['pdwcv'])."','".$con->formatDigit($_POST['pdwsd'])."','".$con->formatDigit($_POST['pct'])."','".$con->formatDigit($_POST['plcc'])."','".$con->formatDigit($_POST['plcr'])."','".$con->escapeString(htmlentities($_POST['remarks']))."','$uid',now());");
			}

			$con->updateLabSampleStatus($_POST['cbc_enccode'],$_POST['cbc_ihomis_code'],$_POST['cbc_serialno'],'3',$uid);	
	
		break;

		case "validateCBCResult":
			$con->dbquery("update ignore lab_cbcresult set result_date = '".$con->formatDate($_POST['cbc_date']) ."', wbc = '".$con->formatDigit($_POST['wbc'])."',rbc = '".$con->formatDigit($_POST['rbc'])."',hemoglobin = '".$con->formatDigit($_POST['hemoglobin'])."', hematocrit = '".$con->formatDigit($_POST['hematocrit'])."', neutrophils = '".$con->formatDigit($_POST['neutrophils'])."', lymphocytes = '".$con->formatDigit($_POST['lymphocytes'])."', monocytes = '".$con->formatDigit($_POST['monocytes'])."',eosinophils = '".$con->formatDigit($_POST['eosinophils'])."', basophils = '".$con->formatDigit($_POST['basophils'])."', platelate = '".$con->formatDigit($_POST['platelate'])."', mcv = '".$con->formatDigit($_POST['mcv'])."', mch = '".$con->formatDigit($_POST['mch'])."', mchc = '".$con->formatDigit($_POST['mchc'])."', remarks = '".$con->escapeString(htmlentities($_POST['remarks']))."', updated_by = '$uid', updated_on = now() where enccode = '$_POST[cbc_enccode]' and serialno = '$_POST[cbc_serialno]';");
			$con->validateResult("lab_cbcresult",$_POST['cbc_enccode'],$_POST['cbc_ihomis_code'],$_POST['cbc_serialno'],$uid);
			$con->updateLabSampleStatus($_POST['cbc_enccode'],$_POST['cbc_ihomis_code'],$_POST['cbc_serialno'],'4',$uid);	
		break;

		case "saveBloodChem":
			list($cnt) = $con->getArray("select count(*) from lab_bloodchem where enccode = '$_POST[bloodchem_enccode]' and serialno = '$_POST[bloodchem_serialno]';");
			if($cnt > 0) {
				$con->dbquery("UPDATE IGNORE lab_bloodchem SET result_date = '".$con->formatDate($_POST['bloodchem_date'])."',glucose='".$con->formatDigit($_POST['glucose'])."',uric = '".$con->formatDigit($_POST['uric'])."',bun = '".$con->formatDigit($_POST['bun'])."',creatinine = '".$con->formatDigit($_POST['creatinine'])."', ldh = '".$con->formatDigit($_POST['ldh'])."', cholesterol = '".$con->formatDigit($_POST['cholesterol'])."',triglycerides = '".$con->formatDigit($_POST['triglycerides'])."',hdl = '".$con->formatDigit($_POST['hdl'])."',ldl = '".$con->formatDigit($_POST['ldl'])."',vldl = '".$con->formatDigit($_POST['vldl'])."',sgot = '".$con->formatDigit($_POST['sgot'])."',sgpt = '".$con->formatDigit($_POST['sgpt'])."',alkaline = '".$con->formatDigit($_POST['alkaline'])."',bilirubin = '".$con->formatDigit($_POST['bilirubin'])."',bilirubin_direct = '".$con->formatDigit($_POST['bilirubin_direct'])."',bilirubin_indirect = '".$con->formatDigit($_POST['bilirubin_indirect'])."',protein = '".$con->formatDigit($_POST['protein'])."',albumin = '".$con->formatDigit($_POST['albumin'])."',globulin = '".$con->formatDigit($_POST['globulin'])."',agratio = '".$con->formatDigit($_POST['agratio'])."',electrolytes_na = '".$con->formatDigit($_POST['electrolytes_na'])."',electrolytes_k = '".$con->formatDigit($_POST['electrolytes_k'])."',electrolytes_ci = '".$con->formatDigit($_POST['electrolytes_ci'])."',calcium = '".$con->formatDigit($_POST['calcium'])."',total_calcium = '".$con->formatDigit($_POST['total_calcium'])."',phosphorus = '".$con->formatDigit($_POST['phosphorus'])."',ggt = '".$con->formatDigit($_POST['ggt'])."', ion_calcium = '".$con->formatDigit($_POST['ion_calcium'])."', troponin = '".$con->formatDigit($_POST['troponin'])."', amylase = '".$con->formatDigit($_POST['amylase'])."', lipase = '".$con->formatDigit($_POST['lipase'])."', hba1c = '".$con->formatDigit($_POST['hba1c'])."', procalcitonin = '".$con->formatDigit($_POST['procalcitonin'])."', cbg = '".$con->formatDigit($_POST['cbg'])."', rbs = '$_POST[rbs]',  remarks = '".$con->escapeString(htmlentities($_POST['remarks']))."',updated_by = '$uid',updated_on = NOW() where enccode = '$_POST[bloodchem_enccode]' and serialno = '$_POST[bloodchem_serialno]';");
			} else {
				$con->dbquery("INSERT IGNORE INTO lab_bloodchem (enccode,result_date,sampletype,serialno,glucose,uric,bun,creatinine,ldh,cholesterol,triglycerides,hdl,ldl,vldl,sgot,sgpt,alkaline,bilirubin,bilirubin_direct,bilirubin_indirect,protein,albumin,globulin,agratio,electrolytes_na,electrolytes_k,electrolytes_ci,calcium,total_calcium,phosphorus,ggt,amylase,lipase,hba1c,procalcitonin,cbg,rbs,remarks,created_by,created_on) VALUES ('$_POST[bloodchem_enccode]','".$con->formatDate($_POST['bloodchem_date'])."','$_POST[bloodchem_spectype]','$_POST[bloodchem_serialno]','".$con->formatDigit($_POST['glucose'])."','".$con->formatDigit($_POST['uric'])."','".$con->formatDigit($_POST['bun'])."','".$con->formatDigit($_POST['creatinine'])."','".$con->formatDigit($_POST['ldh'])."','".$con->formatDigit($_POST['cholesterol'])."','".$con->formatDigit($_POST['triglycerides'])."','".$con->formatDigit($_POST['hdl'])."','".$con->formatDigit($_POST['ldl'])."','".$con->formatDigit($_POST['vldl'])."','".$con->formatDigit($_POST['sgot'])."','".$con->formatDigit($_POST['sgpt'])."','".$con->formatDigit($_POST['alkaline'])."','".$con->formatDigit($_POST['bilirubin'])."','".$con->formatDigit($_POST['bilirubin_direct'])."','".$con->formatDigit($_POST['bilirubin_indirect'])."','".$con->formatDigit($_POST['protein'])."','".$con->formatDigit($_POST['albumin'])."','".$con->formatDigit($_POST['globulin'])."','".$con->formatDigit($_POST['agratio'])."','".$con->formatDigit($_POST['electrolytes_na'])."','".$con->formatDigit($_POST['electrolytes_k'])."','".$con->formatDigit($_POST['electrolytes_ci'])."','".$con->formatDigit($_POST['calcium'])."','".$con->formatDigit($_POST['total_calcium'])."','".$con->formatDigit($_POST['phosphorus'])."','".$con->formatDigit($_POST['ggt'])."','".$con->formatDigit($_POST['amylase'])."','".$con->formatDigit($_POST['lipase'])."','".$con->formatDigit($_POST['hba1c'])."','".$con->formatDigit($_POST['procalcitonin'])."','".$con->formatDigit($_POST['cbg'])."','$_POST[rbs]','".$con->escapeString(htmlentities($_POST['remarks']))."','$uid',NOW());");
				//echo "INSERT IGNORE INTO lab_bloodchem (enccode,result_date,sampletype,serialno,glucose,uric,bun,creatinine,cholesterol,triglycerides,hdl,ldl,vldl,sgot,sgpt,alkaline,bilirubin,bilirubin_direct,bilirubin_indirect,protein,albumin,globulin,agratio,electrolytes_na,electrolytes_k,electrolytes_ci,calcium,phosphorus,ggt,remarks,created_by,created_on) VALUES ('$_POST[bloodchem_enccode]','".$con->formatDate($_POST['bloodchem_date'])."','$_POST[bloodchem_spectype]','$_POST[bloodchem_serialno]','".$con->formatDigit($_POST['glucose'])."','".$con->formatDigit($_POST['uric'])."','".$con->formatDigit($_POST['bun'])."','".$con->formatDigit($_POST['creatinine'])."','".$con->formatDigit($_POST['cholesterol'])."','".$con->formatDigit($_POST['triglycerides'])."','".$con->formatDigit($_POST['hdl'])."','".$con->formatDigit($_POST['ldl'])."','".$con->formatDigit($_POST['vldl'])."','".$con->formatDigit($_POST['sgot'])."','".$con->formatDigit($_POST['sgpt'])."','".$con->formatDigit($_POST['alkaline'])."','".$con->formatDigit($_POST['bilirubin'])."','".$con->formatDigit($_POST['bilirubin_direct'])."','".$con->formatDigit($_POST['bilirubin_indirect'])."','".$con->formatDigit($_POST['protein'])."','".$con->formatDigit($_POST['albumin'])."','".$con->formatDigit($_POST['globulin'])."','".$con->formatDigit($_POST['agratio'])."','".$con->formatDigit($_POST['electrolytes_na'])."','".$con->formatDigit($_POST['electrolytes_k'])."','".$con->formatDigit($_POST['electrolytes_ci'])."','".$con->formatDigit($_POST['calcium'])."','".$con->formatDigit($_POST['phosphorus'])."','".$con->formatDigit($_POST['ggt'])."','".$con->escapeString(htmlentities($_POST['remarks']))."','$uid',NOW());";
			}
			
			$con->dbquery("update lab_samples set `status` = '3', is_consolidated = 'Y', updated_by = '$uid', updated_on = now() where enccode = '$_POST[bloodchem_enccode]' and serialno = '$_POST[bloodchem_serialno]';");

		break;

		case "checkConosolidatedChemResult":
			list($isCount) = $con->getArray("select count(*) from lab_samples where enccode = '$_POST[enccode]' and serialno = '$_POST[serialno]';;");
			echo $isCount;
		break;

		case "validateBloodChem":
			/* Update Status of Lab Sample */
			$con->dbquery("UPDATE IGNORE lab_bloodchem SET result_date = '".$con->formatDate($_POST['bloodchem_date'])."',glucose='".$con->formatDigit($_POST['glucose'])."',uric = '".$con->formatDigit($_POST['uric'])."',bun = '".$con->formatDigit($_POST['bun'])."',creatinine = '".$con->formatDigit($_POST['creatinine'])."', ldh = '".$con->formatDigit($_POST['ldh'])."', cholesterol = '".$con->formatDigit($_POST['cholesterol'])."',triglycerides = '".$con->formatDigit($_POST['triglycerides'])."',hdl = '".$con->formatDigit($_POST['hdl'])."',ldl = '".$con->formatDigit($_POST['ldl'])."',vldl = '".$con->formatDigit($_POST['vldl'])."',sgot = '".$con->formatDigit($_POST['sgot'])."',sgpt = '".$con->formatDigit($_POST['sgpt'])."',alkaline = '".$con->formatDigit($_POST['alkaline'])."',bilirubin = '".$con->formatDigit($_POST['bilirubin'])."',bilirubin_direct = '".$con->formatDigit($_POST['bilirubin_direct'])."',bilirubin_indirect = '".$con->formatDigit($_POST['bilirubin_indirect'])."',protein = '".$con->formatDigit($_POST['protein'])."',albumin = '".$con->formatDigit($_POST['albumin'])."',globulin = '".$con->formatDigit($_POST['globulin'])."',agratio = '".$con->formatDigit($_POST['agratio'])."',electrolytes_na = '".$con->formatDigit($_POST['electrolytes_na'])."',electrolytes_k = '".$con->formatDigit($_POST['electrolytes_k'])."',electrolytes_ci = '".$con->formatDigit($_POST['electrolytes_ci'])."',calcium = '".$con->formatDigit($_POST['calcium'])."',total_calcium = '".$con->formatDigit($_POST['total_calcium'])."',phosphorus = '".$con->formatDigit($_POST['phosphorus'])."',ggt = '".$con->formatDigit($_POST['ggt'])."', ion_calcium = '".$con->formatDigit($_POST['ion_calcium'])."', troponin = '".$con->formatDigit($_POST['troponin'])."', cbg = '".$con->formatDigit($_POST['cbg'])."', rbs = '$_POST[rbs]', remarks = '".$con->escapeString(htmlentities($_POST['remarks']))."',updated_by = '$uid',updated_on = NOW() where enccode = '$_POST[bloodchem_enccode]' and serialno = '$_POST[bloodchem_serialno]';");
			$con->validateResult("lab_bloodchem",$_POST['bloodchem_enccode'],$_POST['bloodchem_code'],$_POST['bloodchem_serialno'],$uid);
			$con->updateLabSampleStatus($_POST['bloodchem_enccode'],$_POST['bloodchem_code'],$_POST['bloodchem_serialno'],'4',$uid);	
		break;

		case "saveSPChem":
			list($cnt) = $con->getArray("select count(*) from lab_spchem where enccode = '$_POST[spchem_enccode]' and serialno = '$_POST[spchem_serialno]';");	
			if($cnt > 0) {
				$con->dbquery("UPDATE IGNORE lab_spchem SET result_date='".$con->formatDate($_POST['spchem_date'])."',afp='$_POST[afp]',bhcg='$_POST[bhcg]',bhcgt='$_POST[bhcgt]',crp='$_POST[crp]',hscrp='$_POST[hscrp]',dimer='$_POST[dimer]',ft3='$_POST[ft3]',ft4='$_POST[ft4]',ggt='$_POST[ggt]',psa='$_POST[psa]',tsh='$_POST[tsh]',t3='$_POST[t3]',t4='$_POST[t4]',hba1c='$_POST[hba1c]',tropi_qn='$_POST[tropi_qn]',tropi_ql='$_POST[tropi_ql]',remarks='".$con->escapeString($_POST['remarks'])."',updated_by='$_SESSION[userid]',updated_on=now() WHERE enccode = '$_POST[spchem_enccode]' and serialno = '$_POST[spchem_serialno]';");
			} else {
				$con->dbquery("INSERT IGNORE INTO lab_spchem (enccode,serialno,result_date,afp,bhcg,bhcgt,crp,hscrp,dimer,ft3,ft4,ggt,psa,tsh,t3,t4,hba1c,tropi_qn,tropi_ql,remarks,created_by,created_on) VALUES ('$_POST[spchem_enccode]','$_POST[spchem_serialno]','".$con->formatDate($_POST['spchem_date'])."','$_POST[afp]','$_POST[bhcg]','$_POST[bhcgt]','$_POST[crp]','$_POST[hscrp]','$_POST[dimer]','$_POST[t3]','$_POST[t4]','$_POST[ggt]','$_POST[psa]','$_POST[tsh]','$_POST[t3]','$_POST[t4]','$_POST[hba1c]','$_POST[tropi_qn]','$_POST[tropi_ql]','".$con->escapeString($_POST['remarks'])."','$_SESSION[userid]',NOW());");
			}

			$con->dbquery("update lab_samples set `status` = '3', updated_by = '$uid', updated_on = now() where enccode = '$_POST[spchem_enccode]' and serialno = '$_POST[spchem_serialno]';");

		break;

		case "validateSPChem":
			$con->dbquery("UPDATE IGNORE lab_spchem SET result_date='".$con->formatDate($_POST['spchem_date'])."',afp='$_POST[afp]',bhcg='$_POST[bhcg]',bhcgt='$_POST[bhcgt]',crp='$_POST[crp]',hscrp='$_POST[hscrp]',dimer='$_POST[dimer]',ft3='$_POST[ft3]',ft4='$_POST[ft4]',ggt='$_POST[ggt]',psa='$_POST[psa]',tsh='$_POST[tsh]',t3='$_POST[t3]',t4='$_POST[t4]',hba1c='$_POST[hba1c]',tropi_qn='$_POST[tropi_qn]',tropi_ql='$_POST[tropi_ql]',remarks='".$con->escapeString($_POST['remarks'])."',updated_by='$_SESSION[userid]',updated_on=now() WHERE enccode = '$_POST[spchem_enccode]' and serialno = '$_POST[spchem_serialno]';");
			$con->validateResult("lab_spchem",$_POST['spchem_enccode'],$_POST['spchem_code'],$_POST['spchem_serialno'],$uid);
			$con->updateLabSampleStatus($_POST['spchem_enccode'],$_POST['spchem_code'],$_POST['spchem_serialno'],'4',$uid);	
		break;

		case "saveUAReport":
			list($cnt) = $con->getArray("select count(*) from lab_uaresult where enccode = '$_POST[ua_enccode]' and serialno = '$_POST[ua_serialno]';");
			if($cnt > 0) {
				$con->dbquery("UPDATE IGNORE lab_uaresult SET result_date = '".$con->formatDate($_POST['ua_date'])."', color = '$_POST[color]',transparency = '$_POST[transparency]',glucose = '$_POST[glucose]',bilirubin = '$_POST[bilirubin]',ketone = '$_POST[ketone]',gravity = '$_POST[gravity]',blood = '$_POST[blood]',ph = '$_POST[ph]',protein = '$_POST[protein]',urobilinogen = '$_POST[urobilinogen]',nitrite = '$_POST[nitrite]',leukocyte = '$_POST[leukocyte]',pus = '$_POST[pus]',rbc_hpf = '$_POST[rbc_hpf]',epith = '$_POST[epith]',mucus_thread = '$_POST[mucus_thread]',bacteria = '$_POST[bacteria]',casts = '$_POST[casts]', casts1 = '$_POST[casts1]', casts2 = '$_POST[casts2]', casts3 = '$_POST[casts3]', casts4 = '$_POST[casts4]', casts5 = '$_POST[casts5]', casts6 = '$_POST[casts6]', crystals = '$_POST[crystals]', crystals1 = '$_POST[crystals1]', crystals2 = '$_POST[crystals2]', crystals3 = '$_POST[crystals3]', crystals4 = '$_POST[crystals4]', crystals5 = '$_POST[crystals5]', crystals6 = '$_POST[crystals6]', crystals7 = '$_POST[crystals7]',amorphous_urates = '$_POST[amorphous_urates]', amorphous_po4 = '$_POST[amorphous_po4]', others = '".$con->escapeString($_POST['others'])."',remarks = '".$con->escapeString($_POST['remarks'])."',updated_by = '$uid',updated_on = now() where enccode = '$_POST[ua_enccode]' and serialno = '$_POST[ua_serialno]';");
			} else {
				$con->dbquery("insert ignore into lab_uaresult (enccode,result_date,sampletype,serialno,color,transparency,glucose,bilirubin,ketone,gravity,blood,ph,protein,urobilinogen,nitrite,leukocyte,pus,rbc_hpf,epith,mucus_thread,bacteria,casts,casts1,casts2,casts3,casts4,casts5,casts6,crystals,crystals1,crystals2,crystals3,crystals4,crystals5,crystals6,crystals7,amorphous_urates,amorphous_po4,others,remarks,created_by,created_on) values ('$_POST[ua_enccode]','".$con->formatDate($_POST['ua_date'])."','$_POST[ua_spectype]','$_POST[ua_serialno]','$_POST[color]','$_POST[transparency]','$_POST[glucose]','$_POST[bilirubin]','$_POST[ketone]','$_POST[gravity]','$_POST[blood]','$_POST[ph]','$_POST[protein]','$_POST[urobilinogen]','$_POST[nitrite]','$_POST[leukocyte]','$_POST[pus]','$_POST[rbc_hpf]','$_POST[epith]','$_POST[mucus_thread]','$_POST[bacteria]','$_POST[casts]','$_POST[casts1]','$_POST[casts2]','$_POST[casts3]','$_POST[casts4]','$_POST[casts5]','$_POST[casts6]','$_POST[crystals]','$_POST[crystals1]','$_POST[crystals2]','$_POST[crystals3]','$_POST[crystals4]','$_POST[crystals5]','$_POST[crystals6]','$_POST[crystals7]','$_POST[amorphous_urates]','$_POST[amorphous_po4]','".$con->escapeString($_POST['others'])."','".$con->escapeString($_POST['remarks'])."','$uid',now());");
			}

			/* Update Status of Lab Sample */
			$con->updateLabSampleStatus($_POST['ua_enccode'],$_POST['ua_code'],$_POST['ua_serialno'],'3',$uid);	
		break;

		case "validateUAReport":
			/* Update Status of Lab Sample */
			$con->dbquery("UPDATE IGNORE lab_uaresult SET result_date = '".$con->formatDate($_POST['ua_date'])."', color = '$_POST[color]',transparency = '$_POST[transparency]',glucose = '$_POST[glucose]',bilirubin = '$_POST[bilirubin]',ketone = '$_POST[ketone]',gravity = '$_POST[gravity]',blood = '$_POST[blood]',ph = '$_POST[ph]',protein = '$_POST[protein]',urobilinogen = '$_POST[urobilinogen]',nitrite = '$_POST[nitrite]',leukocyte = '$_POST[leukocyte]',pus = '$_POST[pus]',rbc_hpf = '$_POST[rbc_hpf]',epith = '$_POST[epith]',mucus_thread = '$_POST[mucus_thread]',bacteria = '$_POST[bacteria]',casts = '$_POST[casts]', casts1 = '$_POST[casts1]', casts2 = '$_POST[casts2]', casts3 = '$_POST[casts3]', casts4 = '$_POST[casts4]', casts5 = '$_POST[casts5]', casts6 = '$_POST[casts6]', crystals = '$_POST[crystals]', crystals1 = '$_POST[crystals1]', crystals2 = '$_POST[crystals2]', crystals3 = '$_POST[crystals3]', crystals4 = '$_POST[crystals4]', crystals5 = '$_POST[crystals5]', crystals6 = '$_POST[crystals6]', crystals7 = '$_POST[crystals7]',amorphous_urates = '$_POST[amorphous_urates]', amorphous_po4 = '$_POST[amorphous_po4]', others = '".$con->escapeString($_POST['others'])."',remarks = '".$con->escapeString($_POST['remarks'])."',updated_by = '$uid',updated_on = now() where enccode = '$_POST[ua_enccode]' and serialno = '$_POST[ua_serialno]';");
			$con->validateResult("lab_uaresult",$_POST['ua_enccode'],$_POST['ua_code'],$_POST['ua_serialno'],$uid);
			$con->updateLabSampleStatus($_POST['ua_enccode'],$_POST['ua_code'],$_POST['ua_serialno'],'4',$uid);	
		break;

		case "saveStoolExam":
			list($cnt) = $con->getArray("select count(*) from lab_stoolexam where enccode = '$_POST[stool_enccode]' and serialno = '$_POST[stool_serialno]';");
			if($cnt > 0) {
				$con->dbquery("UPDATE IGNORE lab_stoolexam SET result_date = '".$con->formatDate($_POST['stool_date'])."', color='$_POST[color]',consistency='$_POST[consistency]',pus_cells='$_POST[pus_cells]',rbc='$_POST[rbc_hpf]',yeast_cells='$_POST[yeast_cells]',starch='$_POST[starch]',globules='$_POST[globules]',muscle_fibers='$_POST[muscle_fibers]',bacteria='$_POST[bacteria]',ascaris='$_POST[ascaris]',trichiuris='$_POST[trichiuris]',hookworm='$_POST[hookworm]',trichomonas='$_POST[trichomonas]',strongyloides='$_POST[strongyloides]',histolytica_c='$_POST[histolytica_c]',histolytica_t='$_POST[histolytica_t]',occult_blood='$_POST[occult_blood]',others='$_POST[others]',remarks='".$con->escapeString($_POST[remarks])."', updated_by='$_SESSION[userid]', updated_on=NOW() WHERE enccode = '$_POST[stool_enccode]' AND serialno = '$_POST[stool_serialno]';");
				echo "UPDATE IGNORE lab_stoolexam SET result_date = '".$con->formatDate($_POST['stool_date'])."', color='$_POST[color]',consistency='$_POST[consistency]',pus_cells='$_POST[pus_cells]',rbc='$_POST[rbc_hpf]',yeast_cells='$_POST[yeast_cells]',starch='$_POST[starch]',globules='$_POST[globules]',muscle_fibers='$_POST[muscle_fibers]',bacteria='$_POST[bacteria]',ascaris='$_POST[ascaris]',trichiuris='$_POST[trichiuris]',hookworm='$_POST[hookworm]',trichomonas='$_POST[trichomonas]',strongyloides='$_POST[strongyloides]',histolytica_c='$_POST[histolytica_c]',histolytica_t='$_POST[histolytica_t]',occult_blood='$_POST[occult_blood]',others='$_POST[others]',remarks='".$con->escapeString($_POST[remarks])."', updated_by='$_SESSION[userid]', updated_on=NOW() WHERE enccode = '$_POST[stool_enccode]' AND serialno = '$_POST[stool_serialno]';";
			} else {
				$con->dbquery("INSERT IGNORE INTO lab_stoolexam (enccode,result_date,sampletype,serialno,color,consistency,pus_cells,rbc,yeast_cells,starch,globules,muscle_fibers,bacteria,ascaris,trichiuris,hookworm,trichomonas,strongyloides,histolytica_c,histolytica_t,occult_blood,others,remarks,created_by,created_on) VALUES ('$_POST[stool_enccode]','".$con->formatDate($_POST['stool_date'])."','$_POST[stool_spectype]','$_POST[stool_serialno]','$_POST[color]','$_POST[consistency]','$_POST[pus_cells]','$_POST[rbc_hpf]','$_POST[yeast_cells]','$_POST[starch]','$_POST[globules]','$_POST[muscle_fibers]','$_POST[bacteria]','$_POST[ascaris]','$_POST[trichiuris]','$_POST[hookworm]','$_POST[trichomonas]','$_POST[strongyloides]','$_POST[histolytica_c]','$_POST[histolytica_t]','$_POST[occult_blood]','$_POST[others]','".$con->escapeString($_POST['remarks'])."','$_SESSION[userid]',NOW());");
				//echo "INSERT IGNORE INTO lab_stoolexam (enccode,result_date,sampletype,serialno,color,consistency,pus_cells,rbc,yeast_cells,starch,globules,muscle_fibers,bacteria,ascaris,trichiuris,hookworm,trichomonas,strongyloides,histolytica_c,histolytica_t,occult_blood,others,remarks,created_by,created_on) VALUES ('$_POST[stool_enccode]','".$con->formatDate($_POST['stool_date'])."','$_POST[stool_spectype]','$_POST[stool_serialno]','$_POST[color]','$_POST[consistency]','$_POST[pus_cells]','$_POST[rbc]','$_POST[yeast_cells]','$_POST[starch]','$_POST[globules]','$_POST[muscle_fibers]','$_POST[bacteria]','$_POST[ascaris]','$_POST[trichiuris]','$_POST[hookworm]','$_POST[trichomonas]','$_POST[strongyloides]','$_POST[histolytica_c]','$_POST[histolytica_t]','$_POST[occult_blood]','$_POST[others]','".$con->escapeString($_POST['remarks'])."','$_SESSION[userid]',NOW());";
			}
			
			/* Update Status of Lab Sample */
			$con->updateLabSampleStatus($_POST['stool_enccode'],$_POST['stool_code'],$_POST['stool_serialno'],'3',$uid);
			
		break;

		case "validateStoolExam":
			$con->dbquery("UPDATE IGNORE lab_stoolexam SET result_date = '".$con->formatDate($_POST['stool_date'])."', color='$_POST[color]',consistency='$_POST[consistency]',pus_cells='$_POST[pus_cells]',rbc='$_POST[rbc_hpf]',yeast_cells='$_POST[yeast_cells]',starch='$_POST[starch]',globules='$_POST[globules]',muscle_fibers='$_POST[muscle_fibers]',bacteria='$_POST[bacteria]',ascaris='$_POST[ascaris]',trichiuris='$_POST[trichiuris]',hookworm='$_POST[hookworm]',trichomonas='$_POST[trichomonas]',strongyloides='$_POST[strongyloides]',histolytica_c='$_POST[histolytica_c]',histolytica_t='$_POST[histolytica_t]',occult_blood='$_POST[occult_blood]',others='$_POST[others]',remarks='".$con->escapeString($_POST[remarks])."', updated_by='$_SESSION[userid]', updated_on=NOW() WHERE enccode = '$_POST[enccode]' AND serialno = '$_POST[stool_serialno]';");
			$con->validateResult("lab_stoolexam",$_POST['stool_enccode'],'$_POST[stool_code]',$_POST['stool_serialno'],$uid);
			$con->updateLabSampleStatus($_POST['stool_enccode'],$_POST['stool_code'],$_POST['stool_serialno'],'4',$uid);	
		break;


		case "releaseResult":
			$con->dbquery("update lab_samples set released = 'Y', released_by = '$uid', release_date = '" . $con->formatDate($_POST['date']) . "', release_mode = '$_POST[mode]', release_remarks = '" . $con->escapeString($_POST['remarks']) . "', released_to = '" . $con->escapeString(htmlentities($_POST['remarks'])) . "' where record_id = '$_POST[id]';");
		break;

		case "resultUnpublishForm":
			$con->dbquery("update lab_samples set status = '3' where enccode = '$_POST[enccode]' and serialno = '$_POST[serialno]';");
		break;

		/* Patient Archive */
		case "savePatientInfo":
			if($_POST['pid'] != '') {
				$queryString = "UPDATE IGNORE patient_info SET lname = '".$con->escapeString(htmlentities($_POST['p_lname']))."',fname = '".$con->escapeString(htmlentities($_POST['p_fname']))."',mname = '".$con->escapeString(htmlentities($_POST['p_mname']))."',suffix = '$_POST[p_suffix]',gender = '$_POST[p_gender]', pwd = '$_POST[p_pwd]', birthdate = '".$con->formatDate($_POST['p_bday'])."', birthplace = '".$con->escapeString(htmlentities($_POST['p_birthplace']))."', nationality = '$_POST[nation]',cstat = '$_POST[p_cstat]',spouse_lname = '".$con->escapeString(htmlentities($_POST['s_lname']))."',spouse_fname = '".$con->escapeString(htmlentities($_POST['s_fname']))."',spouse_mname = '".$con->escapeString(htmlentities($_POST['s_mname']))."',spouse_suffix = '$_POST[s_suffix]',spouse_birthdate = '".$con->formatDate($_POST['s_bday'])."',mobile_no = '$_POST[p_mobileno]',tel_no = '$_POST[p_telephone]',email_add = '$_POST[p_email]',guardian = '".$con->escapeString(htmlentities($_POST['p_guardian']))."',street = '".$con->escapeString(htmlentities($_POST['p_street']))."',brgy = '$_POST[p_brgy]',city = '$_POST[p_city]',province = '$_POST[p_province]',phic_no = '$_POST[p_phic]',occupation = '$_POST[p_occupation]',employer = '".$con->escapeString(htmlentities($_POST['p_employer']))."',emp_street = '".$con->escapeString(htmlentities($_POST['e_street']))."',emp_brgy = '$_POST[e_brgy]',emp_city = '$_POST[e_city]',emp_province = '$_POST[e_province]', emp_telno = '$_POST[e_telno]', updated_by = '$uid',updated_on = now() where patient_id = '$_POST[pid]';";
			} else {
				$queryString = "INSERT IGNORE patient_info (lname,fname,mname,suffix,gender,birthdate,birthplace,pwd,nationality,cstat,spouse_lname,spouse_fname,spouse_mname,spouse_suffix,spouse_birthdate,mobile_no,tel_no,email_add,guardian,street,brgy,city,province,phic_no,occupation,employer,emp_street,emp_brgy,emp_city,emp_province,emp_telno,created_by,created_on) VALUES ('".$con->escapeString(htmlentities($_POST['p_lname']))."','".$con->escapeString(htmlentities($_POST['p_fname']))."','".$con->escapeString(htmlentities($_POST['p_mname']))."','$_POST[p_suffix]','$_POST[p_gender]','".$con->formatDate($_POST['p_bday'])."','".$con->escapeString(htmlentities($_POST['p_birthplace']))."','$_POST[p_pwd]','$_POST[p_naation]','$_POST[p_cstat]','".$con->escapeString(htmlentities($_POST['s_lname']))."','".$con->escapeString(htmlentities($_POST['s_fname']))."','".$con->escapeString(htmlentities($_POST['s_mname']))."','$_POST[s_suffix]','".$con->formatDate($_POST['s_bday'])."','$_POST[p_mobileno]','$_POST[p_telephone]','$_POST[p_email]','".$con->escapeString(htmlentities($_POST['p_guardian']))."','".$con->escapeString(htmlentities($_POST['p_street']))."','$_POST[p_brgy]','$_POST[p_city]','$_POST[p_province]','$_POST[p_phic]','$_POST[p_occupation]','".$con->escapeString(htmlentities($_POST['p_employer']))."','".$con->escapeString(htmlentities($_POST['e_street']))."','$_POST[e_brgy]','$_POST[e_city]','$_POST[e_province]','$_POST[e_telno]','$uid',now());";
			}	
			//echo $queryString;
			$con->dbquery($queryString);
		break;
		
		/* Asset Management */
		case "saveAsset":
			if($_POST['fid'] != "") {
				$con->dbquery("update fa_master set asset_no='$_POST[asset_no]', asset_description='".$con->escapeString(htmlentities($_POST['asset_description']))."', category='$_POST[category]', serial_no='$_POST[serial_no]', vendor='$_POST[vendor]', po_no='$_POST[po_no]', po_date='".$con->formatDate($_POST['po_date'])."', inv_no='$_POST[inv_no]', cv_no='$_POST[cv_no]', cv_date='".$con->formatDate($_POST['check_date'])."', check_no='$_POST[check_no]', warranty_exp='".$con->formatDate($_POST['warranty_exp'])."', life_span='$_POST[lifespan]', asset_acct='$_POST[asset_acct]', adeprn_acct='$_POST[adepn_acct]', deprn_acct='$_POST[depn_acct]', cost='".$con->formatDigit($_POST['cost'])."', assigned_to='".$con->escapeString(htmlentities($_POST['assigned_to']))."', date_assigned='".$con->formatDate($_POST['date_assigned'])."', dept_code='$_POST[dept_code]', `status`='$_POST[status]', remarks='".$con->escapeString(htmlentities($_POST['remarks']))."', updated_by='$uid', updated_on = now() where fid = '$_POST[fid]';");
			} else {
				$con->dbquery("insert ignore into fa_master (company,branch,asset_no,asset_description,category,serial_no,vendor,po_no,po_date,inv_no,cv_no,cv_date,check_no,warranty_exp,life_span,asset_acct,adeprn_acct,deprn_acct,cost,assigned_to,date_assigned,dept_code,`status`,remarks,created_by,created_on) values ('1','$bid','$_POST[asset_no]','".$con->escapeString(htmlentities($_POST['asset_description']))."','$_POST[category]','$_POST[serial_no]','".$con->escapeString(htmlentities($_POST['vendor']))."','$_POST[po_no]','".$con->formatDate($_POST['po_date'])."','$_POST[inv_no]','$_POST[cv_no]','".$con->formatDate($_POST['check_date'])."','$_POST[check_no]','".$con->formatDate($_POST['warranty_exp'])."','$_POST[lifespan]','$_POST[asset_acct]','$_POST[adepn_acct]','$_POST[depn_acct]','".$con->formatDigit($_POST['cost'])."','".$con->escapeString(htmlentities($_POST['assigned_to']))."','".$con->formatDate($_POST['date_assigned'])."','$_POST[dept_code]','$_POST[status]','".$con->escapeString(htmlentities($_POST['remarks']))."','$uid',now());");
			}
		break;
		
		case "checkDupAssetNo":
			if($_POST['fid'] != '') { $f1 = " and fid!='$_POST[fid]' "; }
			list($isE) = $con->getArray("select count(*) from fa_master where asset_no = '$_POST[asset_no]' and company='1' and branch = '$bid' $f1;");
			if($isE > 0) { echo "DUPLICATE"; } else { echo "NODUPLICATE"; }
		break;
		
		/* Customers & Suppliers */
		case "saveCInfo":
			if($_POST['type'] == 'SUPPLIER') { $company = '0'; } else { $company = $_SESSION['company']; }
			if(isset($_POST['fid']) && $_POST['fid'] != "") {
				$con->dbquery("update ignore contact_info set company='$company', type='$_POST[type]',tradename='".$con->escapeString(htmlentities($_POST['tradename']))."',address='".$con->escapeString(htmlentities($_POST['address']))."',billing_address='".$con->escapeString(htmlentities($_POST['billing_address']))."',shipping_address='".$con->escapeString(htmlentities($_POST['shipping_address']))."',bizstyle='".$con->escapeString($_POST['bizstyle'])."',brgy='$_POST[brgy]', city='$_POST[city]',province='$_POST[province]',country='$_POST[country]',tel_no='".$con->escapeString($_POST['telno'])."',cperson='".$con->escapeString($_POST['cperson'])."',price_level='$_POST[price_level]',terms='$_POST[terms]',credit_limit='".$con->formatDigit($_POST['climit'])."',email='".$con->escapeString($_POST['email'])."',srep='$_POST[srep]',tin_no='$_POST[tin_no]',bank_acct='$_POST[bank_acct]',vatable='$_POST[vatable]', acct_validity = '".$con->formatDate($_POST['acctValid'])."', updated_by='$uid', updated_on=now() where file_id='$_POST[fid]';");
			} else {
				$con->dbquery("insert ignore into contact_info (company,`type`,tradename,address,brgy,city,province,country,bizstyle,billing_address,shipping_address,tel_no,email,cperson,srep,price_level,terms,credit_limit,vatable,tin_no,bank_acct,acct_validity,created_by,created_on) values ('$company','$_POST[type]','".$con->escapeString(htmlentities($_POST['tradename']))."','".$con->escapeString(htmlentities($_POST['address']))."','$_POST[brgy]','$_POST[city]','$_POST[province]','$_POST[country]','".$con->escapeString($_POST['bizstyle'])."','".$con->escapeString(htmlentities($_POST['billing_address']))."','".$con->escapeString(htmlentities($_POST['shipping_address']))."','".$con->escapeString($_POST['telno'])."','".$con->escapeString($_POST['email'])."','$_POST[cperson]','$_POST[srep]','$_POST[price_level]','$_POST[terms]','".$con->formatDigit($_POST['climit'])."','$_POST[vatable]','$_POST[tin_no]','$_POST[bank_acct]','".$con->formatDate($_POST['acctValid'])."','$uid',now());");
			}
		break;
		case "deleteCust":
			$con->dbquery("update contact_info set record_status = 'Deleted', deleted_by='$uid', deleted_on=now() where file_id='$_POST[fid]';");
		break;
		case "verifyCID":
			list($iCount) = $con->getArray("select count(*) from contact_info where file_id = '$_POST[cid]';");
			if($iCount > 0) { echo "Ok"; } else { echo "notOk"; }
		break;

		case "newSpecialPrice":
			$con->dbquery("insert IGNORE into contact_sprice (contact_id,`code`,description,unit,unit_price,special_price,with_validity,valid_until,remarks,created_by,created_on) values ('$_POST[cid]','$_POST[code]','".$con->escapeString($_POST['description'])."','$_POST[unit]','".$con->formatDigit($_POST['walkinprice'])."','".$con->formatDigit($_POST['spprice'])."','$_POST[isValid]','".$con->formatDate($_POST['validUntil'])."','".$con->escapeString($_POST['remarks'])."','$uid',now());");
		break;

		case "retrieveSpecialPrice":
			$sp = $con->getArray("select *, format(unit_price,2) as uprice, format(special_price,2) as sprice from contact_sprice where record_id = '$_POST[rid]';");
			echo json_encode($sp);
		break;

		case "checkifSP":
			$e = $con->getArray("select count(*) from contact_sprice where `code` = '$_POST[code]' and contact_id = '$_POST[cid]';");
			if($e[0] == 0) { echo "ok"; }
		break;

		case "updateSpecialPrice":
			list($currentPrice) = $con->getArray("select unit_price from services_master where `code` = '$_POST[code]';");
			list($presentSpecialPrice) = $con->getArray("select special_price from contact_sprice where record_id = '$_POST[rid]';");

			if($con->formatDigit($_POST['spprice']) != $presentSpecialPrice) { $previousSpecialPrice = $presentSpecialPrice; }

			$con->dbquery("update ignore contact_sprice set unit_price = '$currentPrice', special_price = '".$con->formatDigit($_POST['spprice'])."', previous_price = '$previousSpecialPrice', with_validity = '$_POST[isValid]', valid_until = '".$con->formatDate($_POST['validUntil'])."', remarks = '".$con->escapeString($_POST['remarks'])."', updated_by = '$uid', updated_on = now() where record_id = '$_POST[rid]';");
		break;

		case "removeSpecialPrice":
			$con->dbquery("delete from contact_sprice where record_id = '$_POST[rid]';");
		break;

		/* USERS DATA */
		case "getUinfo":
			list($uname) = $con->getArray("select fullname from user_info where emp_id = '$_POST[uid]';");
			echo $uname;
		break;
		case "checkUname":
			list($count) = $con->getArray("select count(*) from user_info where username = '$_POST[uname]';"); echo $count;
		break;
		
		case "checkUnameUID":
			list($count) = $con->getArray("select count(*) from user_info where username = '$_POST[uname]' and emp_id!='$_POST[uid]';"); echo $count;
		break;
		
		case "getUserDetails":
			$u1 = $con->getArray("select *,if(signature_file!='',concat('<img src=\"images/signatures/',signature_file,'\" align=absmiddle />'),'<img src=\"images/signatures/blank.png\" align=absmiddle />') as signaturefile from user_info where emp_id = '$_POST[uid]';");
			$u2 = array("xfullname"=>html_entity_decode($u1['fullname']));
			$u3 = array_merge($u1,$u2);
			echo json_encode($u3);
		break;	

		case "updateUser":
			$uploadDir = "../images/signatures/";

			$fileName = $_FILES['signatureFile']['name'];
			$tmpName = $_FILES['signatureFile']['tmp_name'];
			
			
			if($fileName!='') {

				/* CHANGE UNIQUE FILENAME TO PREVENT DUPLICATION */
				$ext = substr(strrchr($fileName, "."), 1);
				$randName = md5(rand() * time());
				$newFileName = $randName . "." . $ext;
				$filePath = $uploadDir . $newFileName;
				$result = move_uploaded_file($tmpName, $filePath);
			
				$signatureFile = ",signature_file = '$newFileName' ";

			}

			$con->dbquery("UPDATE IGNORE user_info SET username = '$_POST[uname]', fullname = '".htmlentities($_POST['fname'])."', user_type = '$_POST[utype]', r_type = '$_POST[rtype]', role = '".$con->escapeString($_POST['urole'])."', license_no = '$_POST[license_no]', email = '$_POST[uemail]' $signatureFile WHERE emp_id = '$_POST[uid]';");
			
		break;

		case "newUser":
			$uploadDir = "../images/signatures/";

			$fileName = $_FILES['new_signatureFile']['name'];
			$tmpName = $_FILES['new_signatureFile']['tmp_name'];
			
			
			if($fileName!='') {

				/* CHANGE UNIQUE FILENAME TO PREVENT DUPLICATION */
				$ext = substr(strrchr($fileName, "."), 1);
				$randName = md5(rand() * time());
				$newFileName = $randName . "." . $ext;
				$filePath = $uploadDir . $newFileName;
				$result = move_uploaded_file($tmpName, $filePath);
			}

			$con->dbquery("INSERT IGNORE INTO user_info (username,`password`,fullname,user_type,r_type,email,`role`,license_no,signature_file) value ('$_POST[new_uname]',md5('$_POST[new_pass1]'),'".$con->escapeString(htmlentities($_POST['new_fname']))."','$_POST[new_utype]','$_POST[new_rtype]','$_POST[new_uemail]','".$con->escapeString($_POST['new_urole'])."','$_POST[new_license_no]','$newFileName');");

		break;

		case "deleteUser":
			$h = $con->getArray("select username, fullname from user_info where emp_id = '$_POST[uid]';");
			$con->trailer("USER MANAGEMENT","USER INFO DELETED, User ID: $_POST[uid], Username: $h[username], Full Name: $h[fullname]");
			$con->dbquery("delete from user_info where emp_id = '$_POST[uid]';");
			$con->dbquery("delete from user_rights where UID = '$_POST[uid]';");
		break;


		case "deleteUser":
			$h = $con->getArray("select username, fullname from user_info where emp_id = '$_POST[uid]';");
			$con->trailer("USER MANAGEMENT","USER INFO DELETED, User ID: $_POST[uid], Username: $h[username], Full Name: $h[fullname]");
			$con->dbquery("delete from user_info where emp_id = '$_POST[uid]';");
			$con->dbquery("delete from user_rights where UID = '$_POST[uid]';");
		break;
		case "checkOldPass":
			list($count) = $con->getArray("select count(*) from user_info where emp_id='$_POST[uid]' and password=md5('$_POST[old_pass]');");	
			if($count>0) { echo "Ok"; } else { echo "noOk"; }
		break;
		case "changePassword":
			$con->dbquery("update ignore user_info set password=md5('$_POST[pass]'), require_change_pass='N' where emp_id='$_POST[uid]';");
			$con->trailer("USER MANAGEMENT","PASSWORD FOR UID $_POST[uid] was updated");
		break;
		case "resetPassword":
			$con->dbquery("update ignore user_info set password=md5('123456'), require_change_pass='Y' where emp_id='$_POST[uid]';");
			$con->trailer("USER MANAGEMENT","PASSWORD FOR UID $_POST[uid] was reset");
		break;
		case "insertRights":
			list($module,$id) = explode("|",$_REQUEST['val']);
			if($_REQUEST['push'] == "N") { 
				$xfind = $con->getArray("select count(*) from user_rights where UID='$_POST[uid]' and MENU_MODULE='$module' and MENU_ID='$id';");
				if($xfind[0] > 0) { 
					$con->dbquery("delete from user_rights where UID='$_POST[uid]' and MENU_MODULE='$module' and MENU_ID='$id';"); 
					$con->trailer("USER MANAGEMENT","RIGHTS REMOVED FOR UID $_POST[uid] -> SUBMENU ID # $id");
				}
			} else {
				$xfind = $con->getArray("select count(*) from user_rights where UID='$_POST[uid]' and MENU_MODULE='$module' and MENU_ID='$id';");
				if($xfind[0] == 0) { 
					$con->dbquery("insert ignore into user_rights (UID,MENU_MODULE,MENU_ID) values ('$_REQUEST[uid]','$module','$id');"); 
					$con->trailer("USER MANAGEMENT","RIGHTS ADDED TO UID $_POST[uid] -> SUBMENU ID # $id");
				}
			}
		break;
		case "tagCompany":
			$con->dbquery("update user_info set `$_POST[val]` = '$_POST[push]' where emp_id = '$_POST[uid]';");
			echo "update user_info set `$_POST[val]` = '$_POST[push]' where emp_id = '$_POST[uid]';";
		break;

		case "checkSPass":
			if($_POST['pass'] == 'e10adc3949ba59abbe56e057f20f883e') { echo "ok"; }
		break;

		/* Miscellaneous */
		case "getCities":
			$cq = $con->dbquery("select citymunCode, citymunDesc from options_cities where provCode = '$_POST[pid]';");
			while(list($cid,$ctname) = $cq->fetch_array()) {
				echo "<option value='$cid'>$ctname</option>\n";
			}
		break;
		case "getBrgy":
			$cq = $con->dbquery("select brgyCode, brgyDesc from options_brgy where citymunCode = '$_POST[city]';");
			echo "<option value='0'>- Not Applicable -</option>\n";
			while(list($cid,$ctname) = $cq->fetch_array()) {
				echo "<option value='$cid'>$ctname</option>\n";
			}
		break;
		case "getSections":
			$vg = $con->dbquery("select section_code, section_name from options_sections where parent_dept = '$_POST[dept]';");
			echo "<option value=''>-N/A-</option>\n";
			while(list($scode,$sname) = $vg->fetch_array()) {
				echo "<option value='$scode'>$sname</option>\n";
			}
		break;
		
		/* Apply to Other Documents */
		case "checkForDoc":
			switch($_POST['doctype']) {
				case "SI":
					if($_POST['acct'] == '10106') {
						$sql = $con->dbquery("SELECT '' as lid, doc_no, DATE_FORMAT(invoice_date,'%m/%d/%Y') AS doc_date, format(amount,2) as amount, balance, 'CR' as side FROM invoice_header WHERE customer = trim(leading '0' from '$_POST[cid]') AND `status` = 'Finalized' AND balance > 0;");
					} else {
						$sql = $con->dbquery("SELECT record_id as lid, doc_no, DATE_FORMAT(doc_date,'%m/%d/%Y') AS doc_date, FORMAT(ABS(debit-credit),2) AS amount, ABS(debit-credit) - applied_amount AS balance, IF((debit-credit) > 0,'CR','DB') AS side FROM acctg_gl WHERE contact_id = trim(leading '0' from '$_POST[cid]') AND acct = '$_POST[acct]' and doc_type = 'SI' and (ABS(debit-credit) - applied_amount) > 0;");
					}
				break;
				case "APV": case "AP":
					if($_POST['acct'] == '20201') {
						$sql = $con->dbquery("SELECT '' as lid, apv_no AS doc_no, DATE_FORMAT(apv_date,'%m/%d/%Y') AS doc_date, FORMAT(amount,2) AS amount, balance, 'DB' AS side FROM apv_header WHERE supplier = TRIM(LEADING '0' FROM '$_POST[cid]') AND `status` = 'Posted' AND balance > 0");
					} else {
						$sql = $con->dbquery("SELECT a.record_id as lid, b.apv_no AS doc_no, DATE_FORMAT(b.apv_date,'%m/%d/%Y') AS doc_date, FORMAT(ABS(debit-credit),2) AS amount, a.balance, IF((debit-credit) > 0,'CR','DB') AS side FROM apv_details a INNER JOIN apv_header b ON a.apv_no = b.apv_no AND a.branch = b.branch WHERE a.acct = '$_POST[acct]' AND a.balance > 0 AND b.branch = '$bid' AND b.supplier = TRIM(LEADING '0' FROM '$_POST[cid]') AND b.status = 'Posted';");
					}
				break;
				default:
					$sql = $con->dbquery("SELECT record_id as lid, doc_no, DATE_FORMAT(doc_date,'%m/%d/%Y') AS doc_date, FORMAT(ABS(debit-credit),2) AS amount, ABS(debit-credit) - applied_amount AS balance, IF((debit-credit) > 0,'CR','DB') AS side FROM acctg_gl WHERE contact_id = TRIM(LEADING '0' FROM '$_POST[cid]') AND acct = '$_POST[acct]' AND doc_type = '$_POST[doctype]' AND (ABS(debit-credit) - applied_amount) > 0;");
				break;
			}

			if($sql) {
				echo "<table width=100% cellpadding=0 cellspacing=0>";
				while($b = $sql->fetch_array(MYSQLI_BOTH)) {
					echo "<tr bgcolor=".$con->initBackground($i++)." style='cursor: pointer;' title='Click to Apply this Document' onclick=\"javascript: selectDocument('$b[doc_no]','$b[doc_date]','$b[amount]','$b[balance]','$b[side]','$b[lid]');\">
						<td class = grid width = '20%'>$_POST[doctype]-$b[doc_no]</td>
						<td class = grid width = '20%' align=center>$b[doc_date]</td>
						<td class = grid width = '34%' align=right style='padding-right: 15px;'>".$b['amount']."</td>
						<td class = grid align=right style='padding-right: 10px;'>".number_format($b['balance'],2)."</td>
					</tr>
				";
				}
				if($i < 10) { for($i; $i <= 10; $i++) { echo "<tr bgcolor=".$con->initBackground($i)."><td class=grid colspan=4>&nbsp;</td>"; }}
				echo "</table>";
			}
		break;
		
		/* End to Apply Documents */
		
		case "verifyACCT":
			list($i) = $con->getArray("select count(*) from acctg_accounts where acct_code = '$_POST[acct]';");
			if($i > 0) { echo "NoError"; } else { echo "NotFound"; }
		break;
		
		
		case "checkLockStatus":
			//list($isOk) = $con->getArray("select count(*) from closingtime where `month` = '$_POST[month]' and `year` = '$_POST[year]';");
			//if($isOk == 0) { echo "Ok"; } else { echo "NotOK"; }
			
			echo "Ok";
		break;
		
		case "lockStatusOk":
			$con->dbquery("insert into $dbase.closingtime (`month`,`year`,`closing_memo`,closed_by,closed_on) values ('$_POST[month]','$_POST[year]','".$con->escapeString($_POST['memo'])."','$uid',now());");
		
			list($dtf,$dt2) = $con->getArray("select '$_POST[year]-$_POST[month]-01', last_day('$_POST[year]-$_POST[month]-01');");
			$con->dbquery("UPDATE ignore $dbase.apv_header SET locked='Y',locked_on=now(),locked_by=$uid WHERE apv_date BETWEEN '$dtf' AND '$dt2';");
			$con->dbquery("UPDATE ignore $dbase.cr_header SET locked='Y',locked_on=now(),locked_by=$uid WHERE cr_date BETWEEN '$dtf' AND '$dt2';");
			$con->dbquery("UPDATE ignore $dbase.cv_header SET locked='Y',locked_on=now(),locked_by=$uid WHERE cv_date BETWEEN '$dtf' AND '$dt2';");
			$con->dbquery("UPDATE ignore $dbase.dr_header SET locked='Y',locked_on=now(),locked_by=$uid WHERE dr_date BETWEEN '$dtf' AND '$dt2';");
			$con->dbquery("UPDATE ignore $dbase.invoice_header SET locked='Y',locked_on=now(),locked_by=$uid WHERE invoice_date BETWEEN '$dtf' AND '$dt2';");
			$con->dbquery("UPDATE ignore $dbase.journal_header SET locked='Y',locked_on=now(),locked_by=$uid WHERE j_date BETWEEN '$dtf' AND '$dt2';");
			$con->dbquery("UPDATE ignore $dbase.phy_header SET locked='Y',locked_on=now(),locked_by=$uid WHERE doc_date BETWEEN '$dtf' AND '$dt2';");
			$con->dbquery("UPDATE ignore $dbase.po_header SET locked='Y',locked_on=now(),locked_by=$uid WHERE po_date BETWEEN '$dtf' AND '$dt2';");
			$con->dbquery("UPDATE ignore $dbase.rr_header SET locked='Y',locked_on=now(),locked_by=$uid WHERE rr_date BETWEEN '$dtf' AND '$dt2';");
			$con->dbquery("UPDATE ignore $dbase.so_header SET locked='Y',locked_on=now(),locked_by=$uid WHERE so_date BETWEEN '$dtf' AND '$dt2';");
			$con->dbquery("UPDATE ignore $dbase.str_header SET locked='Y',locked_on=now(),locked_by=$uid WHERE str_date BETWEEN '$dtf' AND '$dt2';");
			$con->dbquery("UPDATE ignore $dbase.srr_header SET locked='Y',locked_on=now(),locked_by=$uid WHERE srr_date BETWEEN '$dtf' AND '$dt2';");
			$con->dbquery("UPDATE ignore $dbase.sw_header SET locked='Y',locked_on=now(),locked_by=$uid WHERE sw_date BETWEEN '$dtf' AND '$dt2';");
			
			/* CREATING SUMMARY OF ACCOUNTS FOR PERIODIC TRIAL BALANCE */
			$itapok = $con->dbquery("SELECT branch, acct, ROUND(SUM(debit-credit),2) AS amt, cost_center FROM $dbase.acctg_gl WHERE doc_date BETWEEN '$dtf' AND '$dt2' GROUP BY branch, acct, cost_center order by branch, acct, cost_center;");
			while($gitapok = $itapok->fetch_array(MYSQLI_BOTH)) {
				if($gitapok[amt] > 0) { $db = $gitapok[amt]; $cr = 0; } else { $db = 0; $cr = abs($gitapok[amt]); }
				$con->dbquery("insert ignore into $dbase.acctg_mo_tbalance (branch,`month`,`year`,`acct`,`debit`,`credit`,cost_center,monthend) values ('$gitapok[branch]','$_POST[month]','$_POST[year]','$gitapok[acct]','$db','$cr','$gitapok[cost_center]','$dt2');");
				$db = 0; $cr = 0;
			}
			
			/* CREATE INVENTORY JOURNAL */
			$iihap = $con->dbquery("SELECT a.branch, 'SI' AS `type`, item_code AS `code`, ROUND(SUM(qty),2) AS sold, 0 AS `in`, 0 AS `out`, ROUND(SUM(qty*cost),2) AS amount FROM $dbase.invoice_header a INNER JOIN $dbase.invoice_details b ON a.invoice_no = b.invoice_no AND a.branch = b.branch AND a.company = b.company WHERE a.status = 'Finalized' AND a.invoice_date BETWEEN '$dtf' AND '$dt2' GROUP BY a.branch, b.item_code UNION ALL SELECT a.branch, 'RR' AS `type`, item_code AS `code`, 0 AS sold, ROUND(SUM(qty),2) AS `in`, 0 AS `out`, ROUND(SUM(qty*cost),2) AS amount FROM $dbase.rr_header a INNER JOIN $dbase.rr_details b ON a.rr_no = b.rr_no AND a.branch = b.branch AND a.company = b.company WHERE a.status = 'Finalized' AND a.rr_date BETWEEN '$dtf' AND '$dt2' GROUP BY a.branch, b.item_code UNION ALL SELECT a.branch, 'STR' AS `type`, item_code AS `code`, 0 AS sold, 0 AS `in`, ROUND(SUM(qty),2) AS `out`, ROUND(SUM(qty*cost),2) AS amount FROM $dbase.str_header a INNER JOIN $dbase.str_details b ON a.str_no = b.str_no AND a.branch = b.branch AND a.company = b.company WHERE a.status = 'Finalized' AND a.str_date BETWEEN '$dtf' AND '$dt2' GROUP BY a.branch, b.item_code UNION ALL SELECT a.branch, 'SRR' AS `type`, item_code AS `code`, 0 AS sold, ROUND(SUM(qty),2) AS `in`, 0 AS `out`, ROUND(SUM(qty*cost),2) AS amount FROM $dbase.srr_header a INNER JOIN $dbase.srr_details b ON a.srr_no = b.srr_no AND a.branch = b.branch AND a.company = b.company WHERE a.status = 'Finalized' AND a.srr_date BETWEEN '$dtf' AND '$dt2' GROUP BY a.branch, b.item_code UNION ALL SELECT a.branch, 'SW' AS `type`, item_code AS `code`, 0 AS sold, 0 AS `in`, ROUND(SUM(qty),2) AS `out`, ROUND(SUM(qty*cost),2) AS amount FROM $dbase.sw_header a INNER JOIN $dbase.sw_details b ON a.sw_no = b.sw_no AND a.branch = b.branch AND a.company = b.company WHERE a.status = 'Finalized' AND a.sw_date BETWEEN '$dtf' AND '$dt2' GROUP BY a.branch, b.item_code UNION ALL SELECT a.branch, 'POS' AS `type`, item_code AS `code`, ROUND(SUM(qty),2) AS sold, 0 AS `in`, 0 AS `out`, ROUND(SUM(qty*price),2) AS amount FROM $dbase.pos_header a INNER JOIN $dbase.pos_details b ON a.tmpfileid = b.tmpfileid WHERE a.status = 'Finalized' AND a.trans_date BETWEEN '$dtf' AND '$dt2' GROUP BY a.branch, b.item_code");
			while($giihap = $iihap->fetch_array(MYSQLI_BOTH)) {
				$con->dbquery("insert ignore into $dbase.ijournal (branch,`month`,`year`,`code`,`type`,`sold`,`inbound`,`outbound`,`amount`,`monthend`) values ('$giihap[branch]','$_POST[month]','$_POST[year]','$giihap[code]','$giihap[type]','$giihap[sold]','$giihap[in]','$giihap[out]','$giihap[amount]','$dt2');");
			}
			
			/* Audit Trail */
			$con->dbquery("insert into traillog (company,branch,user_id,`timestamp`,ipaddress,module,`action`) values ('1','$bid','$uid',now(),'$_SERVER[REMOTE_ADDR]','DOC LOCKING','PERIOD $_POST[month]-$_POST[year] was marked as LOCKED :: Posted Remarks >> ".$con->escapeString($_POST['memo'])."');");
			
		break;
		case "unLock":
			$con->dbquery("delete from $dbase.closingtime where `month` = '$_POST[month]' and `year` = '$_POST[year]';");
			list($dtf,$dt2) = $con->getArray("select '$_POST[year]-$_POST[month]-01', last_day('$_POST[year]-$_POST[month]-01');");
			$con->dbquery("UPDATE ignore $dbase.apv_header SET locked='N',locked_on='',locked_by='' WHERE apv_date BETWEEN '$dtf' AND '$dt2';");
			$con->dbquery("UPDATE ignore $dbase.cr_header SET locked='N',locked_on='',locked_by='' WHERE cr_date BETWEEN '$dtf' AND '$dt2';");
			$con->dbquery("UPDATE ignore $dbase.cv_header SET locked='N',locked_on='',locked_by='' WHERE cv_date BETWEEN '$dtf' AND '$dt2';");
			$con->dbquery("UPDATE ignore $dbase.dr_header SET locked='N',locked_on='',locked_by='' WHERE dr_date BETWEEN '$dtf' AND '$dt2';");
			$con->dbquery("UPDATE ignore $dbase.invoice_header SET locked='N',locked_on='',locked_by='' WHERE invoice_date BETWEEN '$dtf' AND '$dt2';");
			$con->dbquery("UPDATE ignore $dbase.journal_header SET locked='N',locked_on='',locked_by='' WHERE j_date BETWEEN '$dtf' AND '$dt2';");
			$con->dbquery("UPDATE ignore $dbase.phy_header SET locked='N',locked_on='',locked_by='' WHERE doc_date BETWEEN '$dtf' AND '$dt2';");
			$con->dbquery("UPDATE ignore $dbase.po_header SET locked='N',locked_on='',locked_by='' WHERE po_date BETWEEN '$dtf' AND '$dt2';");
			$con->dbquery("UPDATE ignore $dbase.rr_header SET locked='N',locked_on='',locked_by='' WHERE rr_date BETWEEN '$dtf' AND '$dt2';");
			$con->dbquery("UPDATE ignore $dbase.so_header SET locked='N',locked_on='',locked_by='' WHERE so_date BETWEEN '$dtf' AND '$dt2';");
			$con->dbquery("UPDATE ignore $dbase.str_header SET locked='N',locked_on='',locked_by='' WHERE str_date BETWEEN '$dtf' AND '$dt2';");
			$con->dbquery("UPDATE ignore $dbase.srr_header SET locked='N',locked_on='',locked_by='' WHERE srr_date BETWEEN '$dtf' AND '$dt2';");
			$con->dbquery("UPDATE ignore $dbase.sw_header SET locked='N',locked_on='',locked_by='' WHERE sw_date BETWEEN '$dtf' AND '$dt2';");
			
			/* Delete from Monthly Trial Balance */
			$con->dbquery("delete from $dbase.acctg_mo_tbalance where `month` = '$_POST[month]' and `year` = '$_POST[year]';");
			
			/* Delete from Inventory Journal */
			$con->dbquery("delete from $dbase.ijournal where `month` = '$_POST[month]' and `year` = '$_POST[year]`;");
			
			/* Audit Trail */
			$con->dbquery("insert into traillog (company,branch,user_id,`timestamp`,ipaddress,module,`action`) values ('1','$bid','$uid',now(),'$_SERVER[REMOTE_ADDR]','DOC LOCKING','PERIOD $_POST[month]-$_POST[year] was removed from the list of locked periods.');");
		break;
		case "checkDateLock":
			list($belowOct) = $con->getArray("select '".$con->formatDate($_POST['myDate'])."' <= '2017-10-31';");
			if($belowOct != 1) {
				$i = explode("/",$_POST['myDate']);
				list($isOk) = $con->getArray("select count(*) from $dbase.closingtime where `month` = '$i[0]' and `year` = '$i[2]';");
				if($isOk == 0) { echo "Ok"; }
			}
		break;
		case "getSuppliers":
			echo "<option value=''>- All Suppliers -</option>";
			$yt = $con->dbquery("SELECT DISTINCT payee AS supplier, payee_name AS supplier_name FROM cv_header UNION SELECT DISTINCT supplier, supplier_name FROM apv_header ORDER BY supplier_name");
			while($ytt = $yt->fetch_array(MYSQLI_BOTH)) {
				echo "<option value='$ytt[0]'>$ytt[1]</option>";
			}
		break;
		case "modifyBudget":
			list($isE) = $con->getArray("select count(*) from budgets where `acct` = '$_POST[acct]' and `year` = '$_POST[year]';");
			if($isE > 0) {
				$con->dbquery("update budgets set `budget` = '".$con->formatDigit($_POST['budget'])."',updatedBy='$uid',updatedOn=now() where acct = '$_POST[acct]' and `year` = '$_POST[year]';");
			} else { $con->dbquery("INSERT INTO budgets (`year`,`acct`,`budget`,createdBy,createdOn) VALUES ('$_POST[year]','$_POST[acct]','".$con->formatDigit($_POST['budget'])."','$uid',now());"); }
		break;
		case "checkIdentProjCode":
			if($_POST['proj_id'] != '') {
				echo "ok";
			} else {
				list($isE) = $con->getArray("select count(*) from options_project where proj_code = '$_POST[proj_code]';");
				if($isE > 0) { echo "notOk"; } else { echo "ok"; } 
			}
		break;
		case "saveProj":
			if($_POST['proj_id'] != ""){
				$con->dbquery("UPDATE ignore options_project a SET a.proj_code = '$_POST[proj_code]' , a.proj_name = '".$con->escapeString(htmlentities($_POST['proj_name']))."',proj_description='".$con->escapeString(htmlentities($_POST['proj_description']))."', proj_address='".$con->escapeString(htmlentities($_POST['proj_address']))."', proj_type='$_POST[proj_type]', proj_cost='".$con->formatDigit($_POST['proj_cost'])."', proj_duration='$_POST[proj_scale]',proj_date='".$con->formatDate($_POST['proj_date'])."', proj_client='".$con->escapeString(htmlentities($_POST['client']))."', client_address='".$con->escapeString(htmlentities($_POST['client_address']))."', archived = '$_POST[archived_val]', parent='$_POST[is_Parent]',parent_id='$_POST[parent_id]', updated_by = '$uid', updated_on = now() where a.proj_id = '$_POST[proj_id]';");
			}else{
				$con->dbquery("INSERT ignore INTO options_project (proj_code,proj_name,proj_type,proj_description,proj_address,proj_cost,proj_date,proj_client,client_address,proj_duration,parent,parent_id) VALUES ('".$con->escapeString($_POST['proj_code'])."','".$con->escapeString(htmlentities($_POST['proj_name']))."','$_POST[proj_type]','".$con->escapeString(htmlentities($_POST['proj_description']))."','".$con->escapeString(htmlentities($_POST['proj_address']))."','".$con->formatDigit($_POST['proj_cost'])."','".$con->formatDate($_POST['proj_date'])."','".$con->escapeString(htmlentities($_POST['client']))."','".$con->escapeString(htmlentities($_POST['client_address']))."','$_POST[proj_scale]','$_POST[is_Parent]','$_POST[parent_id]');");
			}
		break;
		case "deleteCodes":

			$qr = "../images/qrcodes/" . $_REQUEST['serialno'] . ".png";
			$label = "../images/qrcodes/" . $_REQUEST['serialno'] . ".pdf";

			unlink($qr);
			unlink($label);

		break;
	}
?>