<?php
// Staging-Login

// Falls Loginversuch, Login checken
if(isset($_POST['username'])) {
	require_once "./checkLogin.php";
}

?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title>Login - <?php echo(isset($_SERVER['SERVER_NAME']) ? htmlspecialchars($_SERVER['SERVER_NAME']) : "") ?></title>
    <meta content="text/html; charset=utf-8" http-equiv="content-type">
	<meta name="robots" content="noindex,follow">
    <style>
      body {
        font-family:verdana, arial, helvetica, sans-serif;
        font-size:0.75rem;
		color:#33499A;
        padding: 0;
        margin:0;
      }
	  .banner {
		width:100%;
		height:270px;
		padding:3px 0;
		background: rgb(90,164,200);
		background: -moz-linear-gradient(top, rgba(90,164,200,1) 0%, rgba(51,140,183,1) 100%);
		background: -webkit-linear-gradient(top, rgba(90,164,200,1) 0%,rgba(51,140,183,1) 100%);
		background: linear-gradient(to bottom, rgba(90,164,200,1) 0%,rgba(51,140,183,1) 100%);
		filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#5aa4c8', endColorstr='#338cb7',GradientType=0 );
	  }
	  .center {
		  padding:10px 20px;
		  background:#DFF2FF;
		  border-radius:3px;
	  }
	  h2 {
		  font-weight:normal;
	  }
	  legend {
		  display:none;
	  }
	  fieldset {
		  border:none;
	  }
      ul {
        padding:0;
		list-style:none;
		position:relative;
      }
      ul li {
        margin:5px 0;
      }
	  label {
		  display:block;
	  }
	  input[type="text"],
	  input[type="password"] {
		  display:block;
	  }
  }
    </style>
  </head>  
  <body>
	<div style="width:100%; height:100%; position:relative;">
		<div style="text-align:center; height:100%; height:calc(100% - 225px); min-height:450px; padding:27px 0 360px; background:#FFFFFF;">
			<p><a href="http://www.hermani-webrealisierung.de"><img src="http://www.hermani-web.de/media/files/cwms/hermani-webrealisierung-logo-small.png" /></a></p>
			<div class="banner"><img alt="banner-01" src="http://www.hermani-webrealisierung.de/media/files/cwms/bluehexagons.jpg" style="height:270px; margin:0px auto 0;"></div>
			<div id="logForm" class="logForm form" style="text-align:left; width:333px; margin:20px auto;">        
			<div class="top">
			</div>
			<div class="center">
			  <form action="?page=_login&amp;staginglog=1" method="post" class="form">            
				<fieldset>              
				  <legend>Benutzerformular</legend>              
				  <h2>
					<img alt="concise-wms" src="http://www.hermani-web.de/media/files/cwms/concise-wms_logo-symbol.svg" width="20" style="margin:0 10px 0 0; float:left;" />
					LOGIN
				  </h2>
<?php
	
	// Falls fehlerhafter Login
	if(isset($_GET['login']) && $_GET['login'] == 0) {
		if(isset($_GET['banned']))		
			echo '<p class="error" style="color:#FF9A20;">Maximale Anzahl an Fehlversuchen erreicht.</p>';
		else
			echo '<p class="error" style="color:#FF9A20;">Bitte Benutzernamen und Passwort überprüfen.</p>';
	}
?>
				  <ul>                
					<li>                  
					  <label for="username">
						Benutzername
					  </label>                  
					  <input type="text" name="username" id="username" maxlength="100" tabindex="1" value="<?php (isset($_POST['username']) ? htmlspecialchars($_POST['username']) : "") ?>">                  
					</li>                
					<li>                  
					  <label for="password">
						Passwort
					  </label>                  
					  <input type="password" name="password" id="password" maxlength="15" tabindex="2">                  
					</li>                
					<li>
					  <br />
					  <label class="rememberMe" style="float:left;">
						<input type="checkbox" name="rememberMe" class="checkbox" tabindex="3">
						eingelogged bleiben &nbsp;
					  </label>
					  <input type="submit" name="loginbutton" value="einloggen" class="loginbutton formbutton button btn btn-primary ok right"<?php (isset($_POST['rememberMe']) && $_POST['rememberMe'] == "on" ? ' checked="checked"' : "") ?> style="float:right;" tabindex="4">                  
					  <br style="clear:both;" />
					</li>                
				  </ul>              
				</fieldset>            
			  </form>          
			</div>
			<div class="bottom">
			</div>
		  </div>
			</div>
			<div style="position:relative;
						margin-top:-198px;
						background-color: #f5f4f0;
						background-image: url(http://server49.configcenter.info/bckg.gif);
						background-position: top right;
						background-repeat: no-repeat;
						width:100%;
						height:198px;">
				<table align="center" border="0" cellpadding="0" cellspacing="0" width="400">
					<tbody>
						<tr> 
							<td style="padding-top: 25px;" align="center" valign="top"><img src="http://server49.configcenter.info/loginscreen.gif" height="85" width="396"></td>
						</tr>
						<tr>
							<td style="padding-top: 50px;" align="center"><a href="http://www.swsoft.de/" title="Go to SWsoft homepage" target="_blank">
							<img src="http://server49.configcenter.info/swsoftpoweredby.gif" title="Go to SWsoft homepage" border="0" height="18" width="165"></a></td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
	</body>
</html>