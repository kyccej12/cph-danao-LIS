<?php 
	
	session_start();
	require_once "handlers/_generics.php";
	
	$o = new _init;
	if(isset($_SESSION['authkey'])) { $exception = $o->validateKey(); if($o->exception != 0) {	$URL = $HTTP_REFERER . "login/index.php?exception=" . $o->exception; } } else { $URL = $HTTP_REFERER . "login"; }
	if($URL) { header("Location: $URL"); };

?>
<html>
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8"/> 
	<title>Prime Care WebLIS System Ver. 1.0b</title>
	<link rel="icon" type="image/x-icon" href="favicon.ico" />
	<link href="ui-assets/themes/redmond/jquery-ui.css" rel="stylesheet" type="text/css" />
	<link href="style/style.css" rel="stylesheet" type="text/css" />
	<link href="style/dropMenu.css" rel="stylesheet" type="text/css" />
	<script language="javascript" src="ui-assets/jquery/jquery-1.12.3.js"></script>
	<script language="javascript" src="ui-assets/themes/redmond/jquery-ui.js"></script>
	<script language="javascript" src="js/websocket-printer.js"></script>
	<script language="javascript" src="js/jquery.dialogextend.js"></script>
	<script language="javascript" src="js/main.js?sessid=<?php echo uniqid(); ?>"></script>
	<script language="javascript" src="js/dropMenu.js"></script>
</head>
<body bgcolor="#ffffff" leftmargin="0" bottommargin="0" rightmargin="0" topmargin="0" <?php if($o->cpass == 'Y') { echo "onLoad=\"showChangePass();\""; } ?> style="background: url(images/wallpaper-5.jpg); background-size: 100% 100%; background-repeat: no-repeat;">
 <table height="100%" width="100%" border="0" cellspacing="0" cellpadding="0" >
	<tr class="ui-dialog-title ui-widget-header">
		<td colspan=2 height=37 style="padding-left: 3px;">
			<a href="#" onclick="javascript: showMenu();"><img src="images/icons/button-menu.png" width=24 height=24 align=absmiddle></a>
		</td>
		<td align=right style="padding-right: 10px;"><img src="images/icons/user.png" align=absmiddle border=0 width=18 height=18 /><span style="font-size: 11px; font-weight: bold; color: #ffffff;">&nbsp;<?php $o->getUname($_SESSION['userid']); ?>&nbsp;&nbsp;&nbsp;|</span>&nbsp;<a href="logout.php" style="font-size: 12px; font-weight: bold; color: #ffffff; text-decoration: none;" title="Click to Logout"><img src="images/button-logout.png" align=absmiddle border=0 width=24 height=24 />Logout</a></td>
	</tr>
	<tr height=90%>
		<td colspan=3>
			<table width="100%" height="100%" align="center" valign=middle>
				<tr>
					<td align=center>
						<img src="images/doc-header3.png" />
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td colspan=3>
			<table width="100%" height="100%" cellpadding=0 cellspacing=0 align="center" valign=middle>
				<tr bgcolor="#0e5284">
					<td align=center style="font-family: arial, helvetica, sans-serif; color: #fefefe; font-size: 11px; height: 15px; font-weight: bold;">&copy; Developed Exclusively by Medgruppe Polyclinics & Diagnostic Center for Cebu Province</td>
				</tr>
			</table>
		</td>
	</tr>
 </table>
<?php
	include("includes/lab_menu.php");
	include("includes/labDialogs.php");
 ?>

<div id="userrights" style="display: none;"></div>
<div id="userdetails" style="display: none;"></div>
<div id="userlist" style="display: none;"></div>
<div id="samplelist" style="display: none;"></div>
<div id="itemlist" style="display: none;"></div>
<div id="itemdetails" style="display: none;"></div>
<div id="srrlist" style="display: none;"></div>
<div id="srrdetails" style="display: none;"></div>
<div id="srrprint" style="display: none;"></div>
<div id="swlist" style="display: none;"></div>
<div id="swdetails" style="display: none;"></div>
<div id="strlist" style="display: none;"></div>
<div id="strdetails" style="display: none;"></div>
<div id="strprint" style="display: none;"></div>
<div id="phylist" style="display: none;"></div>
<div id="phydetails" style="display: none;"></div>
<div id="phyprint" style="display: none;"></div>
<div id="ibook" style="display: none;"></div>
<div id="stockcard" style="display: none;"></div>
<div id="barcode" style="display: none;"></div>
<div id="changepass" style="display: none;"></div>
<div id="resultlist" style="display: none;"></div>
<div id="serviceslist" style="display: none;"></div>
<div id="servicesdetails" style="display: none;"></div>

<?php for($rpt = 1; $rpt <= 10; $rpt++) { echo "<div id=\"report$rpt\" style=\"display: none;\"></div>"; } ?>
<div id="printConsolidation" name="printConsolidation" style="display: none;">
	<p style="margin-left: 20px; text-align: justify;" id="message2">It appears that the selected result belongs to one consolidated result sheet. You may select from the given list w/c result you wish to print.</span></p><br/>
	<form name="otherTests" id="otherTests">

	</form>
</div>
<div id="loaderMessage" style="display: none;">
	<table width=100%>
		<tr>
			<td align=center style="color:grey; padding-top: 40px; font-size: 11px;"><img src="images/ajax-loader.gif" align=absmiddle>&nbsp;Please wait while the system is processing your request...</td>
		</tr>
	</table>
</div>
<div id="errorMessage" title="Error Message" style="display: none;">
	<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span><b>Unable to continue due to the following error(s):</b></p>
	<p style="margin-left: 20px; text-align: justify;" id="message"></span></p>
</div>
<div id="mainLoading" style="display:none; width:100%;height:100%;position:absolute;top:0;margin:auto;"> 
	<div style="background-color:white;width:10%;height:20%;;margin:auto;position:relative;top:100;">
		<img style="display:block;margin-left:auto;margin-right:auto;" src="images/ajax-loader.gif" width=128 height=128 align=absmiddle /> 
	</div>
	<div id="mainLoading2" style="background-color:white;width:100%;height:100%;position:absolute;top:0;margin:auto;opacity:0.5;"> </div>
</div>
<div class="suggestionsBox" id="suggestions" style="display: none;">
	<div class="suggestionList" id="autoSuggestionsList">&nbsp;</div>
</div>
<div id="userChangePass" style="display: none;">
	<form name="frmPass" id="frmPass">
		<input type="hidden" name="myUID" id="myUID" value="<?php echo $_SESSION['userid']; ?>">
		<table border="0" cellpadding="0" cellspacing="0" width=100%>
			<tr><td height=4></td></tr>
			<tr>
				<td width=35%><span class="spandix-l">New Password :</span></td>
				<td>
					<input type="password" id="pass1" class="nInput" style="width: 80%;"  />
				</td>
			</tr>
			<tr><td height=4></td></tr>
			<tr>
				<td width=35%><span class="spandix-l">Confirm New Password :</span></td>
				<td>
					<input type="password" id="pass2" class="nInput" style="width: 80%;" />
				</td>
			</tr>
			</table>
	</form>
</div>
</body>
</html>