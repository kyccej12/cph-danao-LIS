<?php

  function _returnError($code) {
    switch($code) {
      case "1": echo "Unable to log you in. You may have specified an invalid username or password!"; break;
      case "2": echo "You have been logged out as your session has already expired!"; break;
      case "3": echo "Unable to renew Session ID. Please contact system administrator to correct this problem."; break;
      case "4": echo "Unable to retrieve Session Data. Try to login into the system again."; break;
    }
  }

?>
<!DOCTYPE html>
<html >
  <head>
    <meta charset="UTF-8">
    <title>Prime Care Cebu WebLIS Ver. 1.0b</title>
    <link rel="stylesheet" href="css/reset.css"> 
    <link rel="stylesheet" href="css/login.css">
    <script src="js/prefixfree.min.js"></script>
  </head>
  <body <?php if(isset($_GET[exception])) { ?> onLoad = "alert('<?php _returnError($_GET[exception]); ?>');" <?php } ?>>
    <div class="login">
		  <h1 style="padding-top: 10px;padding-bottom: 10px;">Cebu Provincial Hospital - Danao City<br/><span style="font-size:10px">National Highway, Danao City, Cebu</span></h1>
          <form class="form" method="post" action="../authenticate.php">
          <p class="field">
            <input type="text" name="txtname" placeholder="Username" required/>
            <i><img src="images/personalinfo.png" size=18 height=18></i>
          </p>
          <p class="field">
            <input type="password" name="txtpass" placeholder="Password" required/>
            <i><img src="images/locked.png" size=18 height=18></i>
          </p>
          <p class="submit"><input type="submit" name="sent" value="Login"></p>
        </form>
            <p style='margin: 0 50px; text-align: justify; font-size: 11px; color: #4a4a4a;'><font style='font-weight: bold; font-size: 11px;'>NOTICE:</font> Use of this network, its equipment, and resources is monitored at all times and requires explicit permission from the network administrator.</p>
    </div>
</body>
</html>
