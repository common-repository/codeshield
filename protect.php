<?php
/*
Plugin Name: CodeShield
Description: With this you won't be worried about your blog content been stolen by automated web scraped. It will change 
<br/> your chosen text blocks into images that will look like exactly as text. They are cached to improve your blog speed.
Version: 5.0
License: GPL
Author: Arturo Emilio 
Author URI: http://arturoemilio.es

*/
/*  Copyright 2009  Arturo Emilio  (email : info@arturoemilio.es)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

if (!defined('ABSPATH')) {
	header('Status: 403 Forbidden');
	header('HTTP/1.1 403 Forbidden');
	die();
}
register_activation_hook(__FILE__ ,'install');
add_action('admin_menu', 'cs_menu');
function cs_menu() {
	add_options_page('CodeShield', 'CodeShield', 10, __FILE__, 'cs_opt');
}

add_action('admin_notices', 'ProtectNotice');
function ProtectNotice() { 
	global $table_prefix, $wpdb;   
	$table = $table_prefix."cs_fonts";    
	$tcl = get_option('cs_col');
	$tsz = get_option('cs_sz');
	$results = $wpdb->get_results("SELECT * FROM {$table}");
   if (!$results){
		echo '<div id="message" class="updated"><p>';
		$Errurl = get_option(siteurl)."/wp-admin/options-general.php?page=codeshield/protect.php";
		printf('<a href="'.$Errurl.'">Just one step more to be able to use Code Shield. Click 
		here to configure it so may enjoy it too!.<br/><b>ATENTION: THE FIRST TIME YOU ACCESS TO THE OPTIONS PAGE
		 IS GOING TO TRY TO FETCH THE DATA FROM GOOGLE SO 
		IT MAY BE A LONG WAIT. ONCE IT IS STARTED DO NOT LEAVE THE WINDOW AND LET IT FINISH. EVEN IF IT GETS TIMEOUT IS OK</b></a>');
		echo "</p></div>";
   }
   if (!wp_next_scheduled( 'up_font' ))
   		echo '<div id="message" class="error"><p>The cron event is not been set in Wordpress</p></div>'; 
	if   (get_option('cs_font_adv') == 'X'){ 
		echo '<div id="message" class="error"><form name="forma" method="post" action="'.get_option(siteurl).'/wp-admin/options-general.php?page=codeshield/protect.php">
			<input type="hidden" name="cs_font_adv" value="1">';
			echo 'It seems that google has change the main font url. The new one is been reported as <a href="'.get_option('cs_font_old').'"> <b>'. get_option('cs_font_old').'</b> </a>. 
			Please clik in the Update button to check the CodeShield Options and dimiss this message.
			</p><input type="submit" class="button-primary" value="Check Options and dimiss this message" /></div>';
		
		
	} 	
}
function install(){
	global $table_prefix, $wpdb;
	$table = $table_prefix."cs_fonts";
	$charset_collate = '';
	if( $wpdb->has_cap('collation'))
	{
		if(!empty($wpdb->charset))
			$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
		if(!empty($wpdb->collate))
			$charset_collate .= " COLLATE $wpdb->collate";
	}
   $sql = "create table {$table} ( nombre varchar(255) default '', url varchar(255) default '', primary key (nombre)){$charset_collate};" ;
   $ok = $wpdb->query($wpdb->prepare($sql)); 
	update_option('cs_last_font', current_time( 'timestamp' )); 
	update_option('cs_time', current_time( 'timestamp' )); 
	$check = 'http://arturoemilio.es/check_google.php';
		if ($ruta = url($check,'url')){
			update_option('cs_font_old',$ruta) ;    
			update_option('cs_font_adv',' ');						
		}

 }

function url($target_url, $fast = null){
	$userAgent = 'msnbot-Products/1.0 (+http://search.msn.com/msnbot.htm) ';
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
	curl_setopt($ch, CURLOPT_URL,$target_url);
	curl_setopt($ch, CURLOPT_FAILONERROR, true);
	curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
	//curl_setopt($ch,CURLOPT_FOLLOWLOCATION,true);
	curl_setopt($ch,CURLOPT_MAXREDIRS,5); 
	curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,0); 
	curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,0);
	if ($fast == 'url'){
		//curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_AUTOREFERER, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		$html= curl_exec($ch);
		curl_close ($ch); 
		if ($html)return $html;
		else return false;
	}elseif ($fast == 'fast'){
		curl_setopt($ch,CURLOPT_VERBOSE,false);
		curl_setopt($ch,CURLOPT_TIMEOUT,2);
		curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,2);
		curl_setopt($ch,CURLOPT_HEADER,true);
		curl_setopt($ch,CURLOPT_NOBODY,true);
		curl_exec($ch);
		$http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close ($ch); 
		if ($http != 200 && $http != 302 && $http != 304) { return false; } else return true;
	}	
}
function fonts(){ 
   global $table_prefix, $wpdb;   
   $html = url(get_option('cs_fn'),'url');	
	if(!$html)echo '<div id="message" class="error"><p>
									I can not fetch google fonts ¿Is it down? 
								(<a href="'.get_option('cs_fn').'"> - TEST LINK - </a>)</p></div>\n';   
	$fonts = array("nombre" => '', "url"=>'');
	$dom = new DOMDocument();
	$dom->loadHTML($html); 
	$xpath = new DOMXPath($dom);
	$hrefs = $xpath->evaluate("/html/body//a");  
	unset($fonts);
	for ($i = 0; $i < $hrefs->length; $i++) {
		$href = $hrefs->item($i);   
		$url = $href->getAttribute('href'); 
		if (strlen($url) <= 1)  continue;		
		$f = get_option('cs_fn').$url;  
		if ($ya[$f]) continue;  	
		$html2 = url($f,'url');
		if(!$html2) continue;
		$dom2 = new DOMDocument();
		$dom2->loadHTML($html2);
		$xpath2 = new DOMXPath($dom2);
		$hrefs2 = $xpath2->evaluate("/html/body//a");
		for ($a = 0; $a < $hrefs2->length; $a++) {  
			$href2 = $hrefs2->item($a);
			$url2 = $href2->getAttribute('href'); 
			if (strlen($url2) <= 4)  continue; 
			$ext = pathinfo($url2);
			$ext = $ext['extension'];  
			if ($ext == 'ttf')    
				$fonts[] = array( "nombre" => $url2, "url" => $f); 
		}               
	}  
	return $fonts;
}    
function hexrgb($hexstr, $rgb = null){ 
 $int = hexdec(str_replace("#", '', $hexstr));
 switch($rgb) {
		case "r":
		return 0xFF & $int >> 0x10;
			break;
		case "g":
		return 0xFF & ($int >> 0x8);
			break;
		case "b":
		return 0xFF & $int;
			break;
		default:
		return array(
			"r" => 0xFF & $int >> 0x10,
			"g" => 0xFF & ($int >> 0x8),
			"b" => 0xFF & $int
			);
			break;
	}    
}
function cs_opt(){
	global $table_prefix, $wpdb;  	 
   $pluginURL = get_option(siteurl)."/wp-content/plugins/codeshield/";
	$hd = 'submit1';
	$rl = 'reload';
	$tcl = get_option('cs_col');
	$tsz = get_option('cs_sz'); 
	$cs_fn = get_option('cs_fn');
	$table = $table_prefix."cs_fonts";    
		if(!$wpdb->get_var("show tables like '$table'") == $table)install();
	   $results = $wpdb->get_results("SELECT * FROM {$table}");
      if ($results){
		foreach ($results as $result) {
				$fuentes[] = array( "nombre" => $result->nombre, "url" => $result->url);}          
		 }else{
		  $fuentes = up_font('F');
		}
  	if($_POST['cs_font_adv'] == '1' ){ 
	echo '<hr>'.$_POST['cs_font_adv'];
	    	update_option('cs_font_adv',' '); 
			header("Location: ".get_option(siteurl)."/wp-admin/options-general.php?page=codeshield/protect.php");
	}  
	if ($_POST[ $rl ] == '1' && $_POST['reload'])  {
		            $fuentes = up_font('F');
		     if(count($fuentes) > 2){  
				 				echo  '<div id="message" class="updated fade">
											<p><strong>Saved. Loaded '.count($fuentes) .' fonts</strong></p>
										</div>';
						  } else{
				  echo  '<div id="message" class="error">
		   <p><strong>No fonts detected in url given. Please check the fonts directory and click on save to start feching. May take some time. </strong></p>
									</div>'; 
							   }	
	}
	if($_POST[ $hd ] == '1'  ) {
		$tcl = $_POST['cs_col'];
		$tsz = $_POST['cs_sz'];
		echo '<hr>'.$_POST['reload'];
		$cs_fn =   $_POST['cs_fn']; 
		update_option('cs_fn',$cs_fn) ;
		if (($cs_fn != $_POST['cs_fn'])) { 	
		     	$fuentes = up_font('F');
          				if((count($fuentes)) > 2){  
	 				echo  '<div id="message" class="updated fade">
								<p><strong>Saved. Loaded '.count($fuentes) .' fonts</strong></p>
							</div>';
			  } else{
	  echo  '<div id="message" class="error">
<p><strong>No fonts detected in url given. Please check the fonts directory and click on save to start feching. May take some time. </strong></p>
						</div>'; 
				   }
		}  
		if($_POST['cs_check'])cs_upt();
		if (!is_numeric($tsz)) $tsz = 12;
		if (($tsz < 8) OR ($tsz>22)) $tsz = 12;
		if (!$tcl) $tcl = '#ffffff';  
		
		update_option('cs_col', $tcl);
		update_option('cs_sz', $tsz);
		update_option('cs_root',$_POST['cs_root']);
		update_option('cs_url',$_POST['cs_url']);
		
	  if ($_POST['cs_fuente'])update_option('cs_fuente', $_POST['cs_fuente']);
		 
		  ?>

			<div id="message" class="updated fade">
				<p><strong>Saved.</strong></p>
			</div>
			<?php	} ?>   
			<style>

			.tabs { 
			margin: 40px auto;
			min-height: 200px;    
			height : 90%;
			position: absolute;
			width: 90%;
			}
			.tab { 
			float: left;
			}
			.tab label {   
			font-size: 20px;	
			background-color: #456;
			border-radius: 5px 5px 0 0;
			box-shadow: -3px 3px 2px #678 inset;
			color: #DDD;
			cursor: pointer;
			left: 0; 
			margin-top: 100px; 
			margin-right: 1px;
			padding: 5px 15px;
			position: relative;
			text-shadow: 1px 1px #000;
			}
			.tab [type=radio] { display: none; }

			
			.content {
				border-radius: 5px 5px 0 0;        
				border-style:ridge;
				border-width:5px;
				border-color:#456;
				
			background-color: #ffffff;
			bottom: 0;
			left: 0;
			overflow:scroll;
			padding: 20px;
			position: absolute;
			right: 0;
			top: 23px;
			}
			.content > * {
			opacity: 0;

			-moz-transform: translateX(-100%);
			-webkit-transform: translateX(-100%);
			-o-transform: translateX(-100%);

			-moz-transition: all 0.6s ease;
			-webkit-transition: all 0.6s ease;
			-o-transition: all 0.6s ease;
			}

			[type="radio"]:checked ~ label {
			background-color: #678;
			box-shadow: 0 3px 2px #89A inset;
			color: #FFF;
			z-index: 2;
			}
			[type=radio]:checked ~ label ~ .content { z-index: 1; }
			[type=radio]:checked ~ label ~ .content > * {
			opacity: 1;
			-moz-transform: translateX(0);
			-webkit-transform: translateX(0);
			-o-transform: translateX(0);
			-ms-transform: translateX(0);
			}
			</style>  
			<div style="margin-left: 500px; margin-top: 10px" id="colorpicker301" class="colorpicker301"></div>
			<div class="wrap">
				<h2>CodeShield by Arturo Emilio </h2><small><a href="http://arturoemilio.es">(click here to visit my website for support o questions)</a></small>
			<div class="tabs">
			   <div class="tab">
			       <input type="radio" id="tab-1" name="reload" value = "" checked>
			       <label for="tab-1">Settings</label>
			       <div class="content">			
			
			
			
			<form name="forma" method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
			<input type="hidden" name="<?php echo $hd; ?>" value="1">
			
			<table class="form-table" style="border-bottom: 1px solid #aaa">
				<tr valign="top">
				<th scope="row">Next fonts fetching sheduled:</th>
			<td><?php  echo date('l jS \of F Y h:i:s A', (get_option('cs_time') + 60*60*24*30)); ?> (Every 30 days)
				</td></tr>
				<tr valign="top">
				<th scope="row">Next check of avaliable fonts:</th>
			<td><?php  echo date('l jS \of F Y h:i:s A', (get_option('cs_last_font') + 60*60*24*7)); ?> (Every 7 days)
				</td></tr>
			<tr valign="top">
			
			<tr valign="top">
			<th scope="row">Si se usa un directorio como CDN poner aqui la ruta desde HOME</th>
			<td>
				<input type="text" name="cs_root" value="<?php echo get_option('cs_root'); ?>" size = 50 /> 
			</td>
			</tr>
			
			<tr valign="top">
			<th scope="row">Si se usa un directorio como CDN poner aqui el subdominio asociado</th>
			<td>
				<input type="text" name="cs_url" value="<?php echo get_option('cs_url'); ?>" size = 50 /> 
			</td>
			</tr>
			
			<th scope="row">HTTP to google fonts main directory</th>
			<td>  
			<input type="text" name="cs_fn" value="<?php echo $cs_fn; ?>"  size = 50/> 
			Reload the fonts using the address here.. In case google keep changing directories. Must be the root from where you can see all the fonts nodes. 
			</td>
			</tr> 
			<tr valign="top">
			<th scope="row">Force Reload</th>
			<td>  
			<input type="checkbox" name="cs_check" value="X"> 
			Hit the Save Options button the images already cached <span style="color:red"><b>will be deleted</b></span>.
			</td>
			</tr>
			<tr valign="top">
				<th scope="row">Color:</th>
			<td>
				<input id="txtcl" type="text" name="cs_col" value="<?php echo $tcl; ?>" /> 
			<img src="<?php echo $pluginURL; ?>select.jpg" onClick="showColorGrid3('txtcl','none');" title="Select color" style="vertical-align: bottom" />
			</td>
			</tr>
			<tr valign="top">
				<th scope="row">Size [8 - 22]:</th>
			<td>
				<input type="hidden" name="cs_sz" value="<?php echo $tsz; ?>" /> 
			</td>
			</tr>
			<tr valign="top">
				<th scope="row">Font:</th>
			<td>I you want to have a peek of the font <a href="http://www.google.com/webfonts/">click here</a>
			<p>
				<select name="cs_fuente">   
			<?php foreach($fuentes as $fuente){  
				   $font = $fuente['url'].$fuente['nombre']; 
					  if  ($font == get_option('cs_fuente')) $sel = 'selected'; 
							else $sel = '';
				?>  
				
				<option value="<?php echo $fuente['url'].$fuente['nombre'];  ?>" <?php echo $sel; ?>>
				<?php echo $fuente['nombre']; ?></option>
				<?php } 
					$par['color'] =get_option('cs_col');
       			$par['size'] = get_option('cs_sz'); 
       			$cont = 'Esta es una prueba.. áéíóúÇ';
				$par['font'] = get_option('cs_fuente');
				$file = pathinfo(get_option('cs_fuente'));
			 $file =    $file['filename'].'.'.$file['extension'];
				echo '</td></p></tr><tr valign="top"><th scope="row">Example</th>
			<td><p>'.$file.'</p><p>'.protect_code($par,$cont).'</p></td></tr>';
				?>	
									
				
				</table>
				<p class="submit">
					<input type="submit" class="button-primary" value="Save Options" />
				</p>
				</form>

				</div>
				 </div>    
				
					    <div class="tab">
					       <input type="radio" id="tab-2" name="reload" value ="X">
					       <label for="tab-2">Fonts Downloaded</label>
					       <div class="content"> 
								<form name="forma" method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
					        <input type="submit" class="button-primary" value="Reload Fonts" />
					<p> Clicking in the Reload Fonts button <span style="color:red"><b>will delete all cached fonts and refresh the database</b></span>. It will take some time.. </p>
								<input type="hidden" name="<?php echo $rl; ?>" value="1">
					
					         <table id="newspaper-b" summary="Cached Google Fonts">
								<thead>
								<tr>
								<th scope="col">Font Name</th>
								<th scope="col">Example</th>
								</thead>
								<tfoot>
								<tr>
								<td colspan="5"><em>Those are the fonts already cached</em></td>
								</tr>
								</tfoot>
								<tbody>
								  <?php  
								$par['color'] = '#000000';
			       			$par['size'] = 16; 
			       			$cont = 'Esta es una prueba.. áéíóúÇ';
								       foreach ($fuentes as $fuente){
											$file =    ABSPATH . 'wp-content/codeshield/'.$fuente['nombre']; 
											if (file_exists($file)){
												$par['font'] =   $fuente['nombre'];
												echo '<tr><td><p>'.$fuente['nombre'].'</p></td><td><p>'.protect_code($par,$cont).'</p></td></tr>' ;  			
												
											}
								}
								?>
								</tbody>
								</table> 
								</form>
								
					</div>  
					
				  </div>
			   </div> 
		</div> 
		<style>
#newspaper-b{font-family:"Lucida Sans Unicode", "Lucida Grande", Sans-Serif;font-size:12px;width:480px;text-align:center;border-collapse:collapse;border:1px solid #69c;margin:20px;}#newspaper-b th{font-weight:normal;font-size:14px;color:#039;padding:15px 10px 10px;}#newspaper-b tbody{background:#e8edff;}#newspaper-b td{color:#669;border-top:1px dashed #fff;padding:10px;}#newspaper-b tbody tr:hover td{color:#339;background:#d0dafd;}#newspaper-c{font-family:"Lucida Sans Unicode", "Lucida Grande", Sans-Serif;font-size:12px;width:480px;text-align:left;border-collapse:collapse;border:1px solid #6cf;margin:20px;}
		</style>  
				<script type="text/javascript">function getScrollY(){var scrOfX = 0,scrOfY=0;if(typeof(window.pageYOffset)=='number'){scrOfY=window.pageYOffset;scrOfX=window.pageXOffset;}else if(document.body&&(document.body.scrollLeft||document.body.scrollTop)){scrOfY=document.body.scrollTop;scrOfX=document.body.scrollLeft;}else if(document.documentElement&&(document.documentElement.scrollLeft||document.documentElement.scrollTop)){scrOfY=document.documentElement.scrollTop;scrOfX=document.documentElement.scrollLeft;}return scrOfY;}document.write("<style>.colorpicker301{text-align:center;visibility:hidden;display:none;position:absolute;background-color:#FFF;border:solid 1px #CCC;padding:4px;z-index:999;filter:progid:DXImageTransform.Microsoft.Shadow(color=#D0D0D0,direction=135);}.o5582brd{border-bott6om:solid 1px #DFDFDF;border-right:solid 1px #DFDFDF;padding:0;width:12px;height:14px;}a.o5582n66,.o5582n66,.o5582n66a{font-family:arial,tahoma,sans-serif;text-decoration:underline;font-size:9px;color:#666;border:none;}.o5582n66,.o5582n66a{text-align:center;text-decoration:none;}a:hover.o5582n66{text-decoration:none;color:#FFA500;cursor:pointer;}.a01p3{padding:1px 4px 1px 2px;background:whitesmoke;border:solid 1px #DFDFDF;}</style>");function gett6op6(){csBrHt=0;if(typeof(window.innerWidth)=='number'){csBrHt=window.innerHeight;}else if(document.documentElement&&(document.documentElement.clientWidth||document.documentElement.clientHeight)){csBrHt=document.documentElement.clientHeight;}else if(document.body&&(document.body.clientWidth||document.body.clientHeight)){csBrHt=document.body.clientHeight;}ctop=((csBrHt/2)-132)+getScrollY();return ctop;}function getLeft6(){var csBrWt=0;if(typeof(window.innerWidth)=='number'){csBrWt=window.innerWidth;}else if(document.documentElement&&(document.documentElement.clientWidth||document.documentElement.clientHeight)){csBrWt=document.documentElement.clientWidth;}else if(document.body&&(document.body.clientWidth||document.body.clientHeight)){csBrWt=document.body.clientWidth;}cleft=(csBrWt/2)-125;return cleft;}var nocol1="&#78;&#79;&#32;&#67;&#79;&#76;&#79;&#82;",clos1="&#67;&#76;&#79;&#83;&#69;",tt6="&#70;&#82;&#69;&#69;&#45;&#67;&#79;&#76;&#79;&#82;&#45;&#80;&#73;&#67;&#75;&#69;&#82;&#46;&#67;&#79;&#77;",hm6="&#104;&#116;&#116;&#112;&#58;&#47;&#47;&#119;&#119;&#119;&#46;";hm6+=tt6;tt6="&#80;&#79;&#87;&#69;&#82;&#69;&#68;&#32;&#98;&#121;&#32;&#70;&#67;&#80;";function setCCbldID6(objID,val){document.getElementById(objID).value=val;}function setCCbldSty6(objID,prop,val){switch(prop){case "bc":if(objID!='none'){document.getElementById(objID).style.backgroundColor=val;}break;case "vs":document.getElementById(objID).style.visibility=val;break;case "ds":document.getElementById(objID).style.display=val;break;case "tp":document.getElementById(objID).style.top=val;break;case "lf":document.getElementById(objID).style.left=val;break;}}function putOBJxColor6(OBjElem,Samp,pigMent){if(pigMent!='x'){setCCbldID6(OBjElem,pigMent);setCCbldSty6(Samp,'bc',pigMent);}setCCbldSty6('colorpicker301','vs','hidden');setCCbldSty6('colorpicker301','ds','none');}function showColorGrid3(OBjElem,Sam){var objX=new Array('00','33','66','99','CC','FF');var c=0;var z='"'+OBjElem+'","'+Sam+'",""';var xl='"'+OBjElem+'","'+Sam+'","x"';var mid='';mid+='<center><table bgcolor="#FFFFFF" border="0" cellpadding="0" cellspacing="0" style="border:solid 1px #F0F0F0;padding:2px;"><tr>';mid+="<td colspan='18' align='left' style='font-size:10px;background:#6666CC;color:#FFF;font-family:arial;'>&nbsp;Combo-Chromatic Selection Palette</td></tr><tr><td colspan='18' align='center' style='margin:0;padding:2px;height:14px;' ><input class='o5582n66' type='text' size='10' id='o5582n66' value='#FFFFFF'><input class='o5582n66a' type='text' size='2' style='width:14px;' id='o5582n66a' onclick='javascript:alert(\"click on selected swatch below...\");' value='' style='border:solid 1px #666;'>&nbsp;|&nbsp;<a class='o5582n66' href='javascript:onclick=putOBJxColor6("+z+")'><span class='a01p3'>"+nocol1+"</span></a>&nbsp;&nbsp;&nbsp;&nbsp;<a class='o5582n66' href='javascript:onclick=putOBJxColor6("+xl+")'><span class='a01p3'>"+clos1+"</span></a></td></tr><tr>";var br=1;for(o=0;o<6;o++){mid+='</tr><tr>';for(y=0;y<6;y++){if(y==3){mid+='</tr><tr>';}for(x=0;x<6;x++){var grid='';grid=objX[o]+objX[y]+objX[x];var b="'"+OBjElem+"', '"+Sam+"','#"+grid+"'";mid+='<td class="o5582brd" style="background-color:#'+grid+'"><a class="o5582n66"  href="javascript:onclick=putOBJxColor6('+b+');" onmouseover=javascript:document.getElementById("o5582n66").value="#'+grid+'";javascript:document.getElementById("o5582n66a").style.backgroundColor="#'+grid+'";  title="#'+grid+'"><div style="width:12px;height:14px;"></div></a></td>';c++;}}}mid+='</tr></table>';var objX=new Array('0','3','6','9','C','F');var c=0;var z='"'+OBjElem+'","'+Sam+'",""';var xl='"'+OBjElem+'","'+Sam+'","x"';mid+='<table bgcolor="#FFFFFF" border="0" cellpadding="0" cellspacing="0" style="border:solid 1px #F0F0F0;padding:1px;"><tr>';var br=0;for(y=0;y<6;y++){for(x=0;x<6;x++){if(br==18){br=0;mid+='</tr><tr>';}br++;var grid='';grid=objX[y]+objX[x]+objX[y]+objX[x]+objX[y]+objX[x];var b="'"+OBjElem+"', '"+Sam+"','#"+grid+"'";mid+='<td class="o5582brd" style="background-color:#'+grid+'"><a class="o5582n66"  href="javascript:onclick=putOBJxColor6('+b+');" onmouseover=javascript:document.getElementById("o5582n66").value="#'+grid+'";javascript:document.getElementById("o5582n66a").style.backgroundColor="#'+grid+'";  title="#'+grid+'"><div style="width:12px;height:14px;"></div></a></td>';c++;}}mid+="</tr><tr><td colspan='18' align='right' style='padding:2px;border:solid 1px #FFF;background:#FFF;'><a href='"+hm6+"' style='color:#666;font-size:8px;font-family:arial;text-decoration:none;lett6er-spacing:1px;'>"+tt6+"</a></td>";mid+='</tr></table></center>';setCCbldSty6('colorpicker301','tp','100px');document.getElementById('colorpicker301').style.top=gett6op6();document.getElementById('colorpicker301').style.left=getLeft6();setCCbldSty6('colorpicker301','vs','visible');setCCbldSty6('colorpicker301','ds','block');document.getElementById('colorpicker301').innerHTML=mid;}</script>		
			    			  
				
				
				
				
				
				
				
				<?php      		    
		}
    
    add_action('up_font', 'up_font');
		
		function upd_font() {
			if ( !wp_next_scheduled( 'up_font' ) ) {
						wp_schedule_event( current_time( 'timestamp' ), 'daily', 'up_font');
			}
		}
		
		add_action('init', 'upd_font');

		function up_font($ret = NULL) {
			/*
					 global $table_prefix, $wpdb;
					$table = $table_prefix."cs_fonts"; 
					$sql = array();
					 if ((!get_option('cs_time')) || (get_option('cs_time') <= (current_time( 'timestamp' )- 60*60*24*30)) || $ret == 'F'){
													cs_upt('X') ;
													$fuentes = fonts(); } 
						if (sizeof($fuentes) > 0){       
							      $sql_post = 'TRUNCATE TABLE '.$table.'';
									$wpdb->query($wpdb->prepare($sql_post));
								foreach( $fuentes as $fuente ) {  
									$sql = '("'.$fuente['nombre'].'", "'.$fuente['url'].'")';  
									$sql_post = 'INSERT INTO '.$table.' (nombre, url) VALUES '. $sql;
									//$ok = $wpdb->query($wpdb->prepare($sql_post));
									//if ($ok === FALSE) echo '<div id="message" class="error"><p>Error found when appending data to font table.SQL: '.$sql_post.'</p></div>';
								}  
								
								update_option('cs_time', current_time( 'timestamp' ));
							}  
						if (get_option('cs_last_font') <= (current_time( 'timestamp' ) - 60*60*24*7)){ 
					   	$results = $wpdb->get_results("SELECT * FROM {$table}");
							foreach ($results as $result){
								$file =    ABSPATH . 'wp-content/codeshield/'.$result->nombre;
									if(!file_exists($file)) {
										$url = $result->url.$result->nombre;  
			       				if(!url($url,'fast') ){
											$sql_post = 'DELETE FROM '.$table.' WHERE nombre = %s';
											//$wpdb->query($wpdb->prepare($sql_post, $result->nombre));
										}
									update_option('cs_last_font', current_time( 'timestamp' )); 
									$check = 'http://arturoemilio.es/check_google.php';
									$ruta = url($check,'url');
									if ($ruta != get_option('cs_font_old') && $ruta != get_option('cs_fn') && $ruta){
										update_option('cs_font_old',$ruta) ;    
										update_option('cs_font_adv','X');						
									}
									}
								}	
						}
				  	if($ret) return $fuentes;  
			 * */

		}
		
		
add_action('wp_enqueue_scripts','script_codeshield');
function script_codeshield(){
	wp_enqueue_script( 'script_codeshield', plugins_url( 'jquery.lazyload.min.js' , __FILE__ ), array('jquery'), '1.0', true );
	
}


			function protect_code($par,$cont) { 
					global $table_prefix, $wpdb; 
					$table = $table_prefix."cs_fonts"; 
					if($par['font'])
						$fuente = $wpdb->get_var("SELECT url FROM ".$table." WHERE nombre = '".trim($par['font'])."'").trim($par['font']);								
					elseif (($fuente = get_option('cs_fuente')) ==  '') return '(* No hay fuente definida)<br/>'.$cont;
					
					$upload_dir = wp_upload_dir();	
					if(!file_exists($upload_dir['basedir'] ."/codeshield"))
									if( (mkdir($upload_dir['basedir'] ."/codeshield")) == FALSE)
														return  'No se puede crear directorio' ;
							
							$in = "<?php header('Location: http://arturoemilio.es/'); ?>";	
							if(!file_exists($upload_dir['basedir']  ."/codeshield/index.php"))
												file_put_contents($upload_dir['basedir']  ."/codeshield/index.php", $in);		
					
					
					$font_file = pathinfo($fuente);
					$font_file = $upload_dir['basedir']."/codeshield/".$font_file['filename'].'.'.$font_file['extension'];
					if(!file_exists($font_file))
									if (url($fuente,'fast'))
											file_put_contents($font_file, url($fuente,'url'));
									else 	return '(* No se puede descargar la fuente '.$fuente.')<br/>'.$cont;	
					
					if(!file_exists($font_file))return '(* Error de fuente)<br/>'.$cont;
					
					
					if ($par['size'] && is_numeric($par['size']))$size = $par['size'];
					else $size = get_option('cs_sz');
					if (($size < 8) or ($size > 22)){ $size = 12;}
					
					if(!($color = $par['color'])) $color =  get_option('cs_col');
					//else return '(* Error de color<br/>)'.$cont;
					
					$color == '#000001' ?  $fondo='#000002' : $fondo='#000001';
					$fondo = hexrgb($fondo);
					$color = hexrgb($color);
					
					$root = get_option('cs_root');
					$url = get_option('cs_url');

					$return = '';	
					$cont = str_replace('>', '> ', $cont);
					 $tags = preg_split('/(<[^>]*[^\/]>|\s)/i', $cont, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
					 foreach($tags as $tag){
							 if($tag == ' ' || preg_match('/<[^>]*[^\/]>/', $tag)) $return .= $tag;
							 else $return .= imaginator($tag,$font_file,$color,$fondo,$size,$root,$url);
					 }
						return $return;
					}
					
					
					function cs_hex($hexstr, $rgb = null){ 
						$int = hexdec(str_replace("#", '', $hexstr));
						switch($rgb) {
							case "r":
							return 0xFF & $int >> 0x10;
							break;
							case "g":
							return 0xFF & ($int >> 0x8);
							break;
							case "b":
							return 0xFF & $int;
							break;
							default:
							return array(
								"r" => 0xFF & $int >> 0x10,
								"g" => 0xFF & ($int >> 0x8),
								"b" => 0xFF & $int
								);
								break;
							}    
						}


						function imaginator($palabra,$fuente,$color,$fondo,$size,$cs_root,$cs_url){
							$size = $size * 2;	
							$palabra = trim($palabra);
							$palabra = htmlspecialchars_decode($palabra);
							$name = md5($palabra.$font_url.var_export($color,true).$size);
							
							$upload_dir = wp_upload_dir();	
							$file = $upload_dir['basedir']."/codeshield/".$name.".png";
							$url = 	$upload_dir['baseurl']."/codeshield/".$name.".png";
							if(file_exists($file)){
									list($texto_x, $texto_y, $tipo, $atributos) = getimagesize($file);
									//($alto / 2) <= 5 ?  $height = $alto : $height = $alto / 2;
									//($ancho / 2) <= 5 ?  $width = $ancho : $width = $ancho / 2;
							}else{
							
										if (!($pos_texto = imagettfbbox($size, 0, $fuente, $palabra))) return 'ERROR:'.error_get_last();
										$pos_texto = imagettfbbox($size, 0, $fuente, $palabra);
										$texto_x = 	abs($pos_texto[2]) + 20;
										$texto_y = abs($pos_texto[5]) + 8;
										$new_image  = imagecreatetruecolor($texto_x,$texto_y);
										if(($f_color = imagecolorallocate($new_image, $color['r'],$color['g'],$color['b'])) === false)  return '(*Error 01)'.$palabra;
										if(($b_color = imagecolorallocate($new_image, $fondo['r'],$fondo['g'],$fondo['b'])) === false) return '(*Error 02)'.$palabra;
										if(!imagefilledrectangle($new_image, 0, 0, ($texto_x - 1 ), ($texto_y - 1), $b_color)) return '(*Error 03)'.$palabra;
										if(!imagecolortransparent($new_image,$b_color)) return '(*Error 04)'.$palabra;
										if(!imagettftext($new_image, $size, 0, 1, abs($pos_texto[5]), $f_color, $fuente, $palabra)) return '(*Error 05)'.$palabra;
										imagepng($new_image,$file,9);
										imagedestroy($new_image);
							}
							
							if(!empty($cs_root)){
								$cdn_path = $cs_root.'/CodeShield/'.$name.'.png';
										if(!file_exists($cdn_path)){
												mkdir(dirname($cdn_path), 0755, true);
												copy($file,$cdn_path);
										}		
								
							}
							if (!empty($cs_url)){
								$url = str_ireplace(site_url(), $cs_url, $upload_dir['baseurl']).'/codeshield/'.$name.'.png';	
							}
							return '<img  class="cshield" data-original="'.$url.'" width="'.($texto_x / 2).'" height="'.($texto_y / 2).'" >';






	
							} 
							
							function cs_upt($force = FALSE) {
								$sDir = ABSPATH . 'wp-content/codeshield';
								if (is_dir($sDir)) {
									$sDir = rtrim($sDir, '/');
									$oDir = dir($sDir);
									while (($sFile = $oDir->read()) !== false) {
										if ($sFile != '.' && $sFile != '..' && !stristr($sFile,'ttf')  && !$force)
													(!is_link("$sDir/$sFile") && is_dir("$sDir/$sFile")) ? upt() : unlink("$sDir/$sFile"); 
										elseif  ($sFile != '.' && $sFile != '..'  && $force)
										        	(!is_link("$sDir/$sFile") && is_dir("$sDir/$sFile")) ? upt() : unlink("$sDir/$sFile"); 

									}
									$oDir->close();
									$in = "<?php header('Location: http://arturoemilio.es/'); ?>";
									file_put_contents(ABSPATH."wp-content//codeshield/index.php", $in);
										return true;
								}
								return false;
							}
							add_shortcode('PCODE','protect_code');
							add_shortcode('pcode','protect_code');
							add_shortcode('Pcode','protect_code');
							function add_codeshield_button() {
								if ( ! current_user_can('edit_posts') && ! current_user_can('edit_pages') ){
									return;
								}
								if ( get_user_option('rich_editing') == 'true') {
									add_filter('mce_external_plugins', 'add_codeshield_tinymce_plugin');
									add_filter('mce_buttons', 'register_codeshield_button');
								}
							}
							define( "codeshield_PLUGIN_DIR", "codeshield" );
							define( "codeshield_PLUGIN_URL", get_bloginfo('wpurl')."/wp-content/plugins/" . codeshield_PLUGIN_DIR );
							function register_codeshield_button($buttons) {
								array_push($buttons, "|", "CodeShield");
								return $buttons;
							}
							function add_codeshield_tinymce_plugin($plugin_array) {
								$plugin_array['CodeShield'] = codeshield_PLUGIN_URL . '/cs.js';
								return $plugin_array;
							}

							function codeshield_my_refresh_mce($ver) {
								$ver += 6;
								return $ver;
							}
							add_action('init', 'add_codeshield_button');
							add_filter( 'tiny_mce_version', 'codeshield_my_refresh_mce');

?>