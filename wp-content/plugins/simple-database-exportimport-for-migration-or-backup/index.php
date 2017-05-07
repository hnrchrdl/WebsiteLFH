<?php
/*
Plugin Name: Simple Database Export+Import+Migration
Description: Easily Import and export  database. Good and quick way to migrate typical websites.
Author: TazoTodua
Author URI: http://www.protectpages.com/profile
Plugin URI: http://www.protectpages.com/
Donate link: http://paypal.me/tazotodua
Version: 1.2
*/
define('version__SDIEM', 		1.1);
define('pluginpage__SDIEM', 	'sdiem_opts_subpage');
define('plugin_settings_page__SDIEM', 	(is_multisite() ? network_admin_url('settings.php') : admin_url( 'options-general.php') ). '?page='.pluginpage__SDIEM  );


function is_admin__SDIEM(){require_once(ABSPATH.'wp-includes/pluggable.php');	return (current_user_can('activate_plugins')? true:false);}
function validate_Nonce__SDIEM($value, $name){if (!wp_verify_nonce($value, $name) ) { die("expired. refresh previous page.  error_41551");}  }	


// ==================================================  PLUGIN ACTIVATION  HOOK ==============================================
if (IS_ADMIN()) { register_activation_hook( __FILE__, 'refresh_options__SDIEM' );}
// from:    https://github.com/tazotodua/useful-php-scripts/blob/master/mysql-commands%20%28+Wordpress%29.php
Function refresh_options__SDIEM(){	

						// die if not network (when MULTISITE )
						if ( is_multisite() && ! strpos( $_SERVER['REQUEST_URI'], 'wp-admin/network/plugins.php' ) ) {
							die ( __( '<script>alert("Activate this plugin only from the NETWORK DASHBOARD.");</script>') );
						}
						
						
	$opts	=get_mainsite_options__SDIEM();
	$initial=$opts;
	
  // Defaults
	$InitialArray = array( 
		'version' => version__SDIEM
		);
	foreach($InitialArray as $name=>$value){if (!array_key_exists($name,$opts)) { $opts[$name]=$value; } }
	$opts['version']= version__MLSS;	//MUST-CHANGE key

	if($initial!=$opts) {	update_site_option('Opts__SDIEM',$opts);	}	
	return $opts;
}

function get_mainsite_options__SDIEM($keyname=false){
	$x = get_site_option('Opts__SDIEM', array()); 	if($keyname && is_array($x) && !empty($x)) { $x= array_key_exists($keyname,$x) ? $x[$keyname] : ''; }
	return $x;
}
// ==================================================  #### PLUGIN ACTIVATION  HOOK =========================================




// https://github.com/tazotodua/useful-php-scripts/
function DOMAIN_or_STRING_modifier_in_DB__SDIEM($old_string,  $new_string, $sql_content,     $download = false){
	set_time_limit(1000);
	if(empty($old_string) || empty($new_string) || empty($sql_content)) return;
	$length_difference= strlen($old_string)- strlen($new_string);
	$old_string_slashed=str_replace('/','\/',$old_string);
	// Replace every occurence of Serialized arrays, i.e. {s:32:"blablabla"}
	preg_match_all('/(\}|\{|\;)s\:(.*?){1,5}\:\"(.*?)\"/si', $sql_content, $n, PREG_SET_ORDER);
	foreach($n as $each){
		if(!is_numeric($each[2])) {continue;}  else { $found_char_length= $each[2];}
		if(stripos($each[3],$old_string)===false){ continue; }
		$before_s_SYMBOL = $each[1]; //i.e.  | or } or {
		$found_line	= $each[0];	 
		$found_line_changed	= str_replace(
			array($before_s_SYMBOL.'s:'.$found_char_length,							$old_string),
			array($before_s_SYMBOL.'s:'.($found_char_length - $length_difference),	$new_string),
			$found_line);
		$sql_content	= str_replace($found_line,	    str_replace($old_string,$new_string,$found_line_changed),    $sql_content);
	}
	// Now, we can freely replace  typical occurences
	$sql_content=str_replace($old_string,$new_string,$sql_content);
	if ($download) { header('Content-Type: application/octet-stream');	header("Content-Transfer-Encoding: Binary");     header('Content-Length: '. (function_exists('mb_strlen') ? mb_strlen($sql_content, '8bit') : strlen($sql_content)) );    header('Content-disposition: attachment; filename="'.$_SERVER['REQUEST_URI'].'_db_'.rand(1,99999).'.sql"');  echo $sql_content; exit; }   else {return $sql_content;}
}

 
 
// =====================================  database    EXPORT+IMPORT =================== //
	 
	 
	// https://github.com/tazotodua/useful-php-scripts
	// EXAMPLE:     EXPORT_TABLES("localhost","user","pass","db_name" );  
					//optional: 5th parameter(array) for specific tables: array("mytable1","mytable2","mytable3")   
					
	function EXPORT_TABLES__SDIEM($host,$user,$pass,$name,       $tables=false, $backup_name=false){ 
		set_time_limit(3000); $mysqli = new mysqli($host,$user,$pass,$name); $mysqli->select_db($name); $mysqli->query("SET NAMES 'utf8'");
		$queryTables = $mysqli->query('SHOW TABLES'); while($row = $queryTables->fetch_row()) { $target_tables[] = $row[0]; }	if($tables !== false) { $target_tables = array_intersect( $target_tables, $tables); } 
		$content = "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\r\nSET time_zone = \"+00:00\";\r\n\r\n\r\n/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;\r\n/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;\r\n/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;\r\n/*!40101 SET NAMES utf8 */;\r\n--\r\n-- Database: `".$name."`\r\n--\r\n\r\n\r\n";
		foreach($target_tables as $table){
			if (empty($table)){ continue; } 
			$result	= $mysqli->query('SELECT * FROM `'.$table.'`');  	$fields_amount=$result->field_count;  $rows_num=$mysqli->affected_rows; 	$res = $mysqli->query('SHOW CREATE TABLE '.$table);	 $TableMLine=$res->fetch_row();  $TableMLine[1]=str_ireplace('CREATE TABLE `','CREATE TABLE IF NOT EXISTS `',$TableMLine[1]);
			$content .= "\n\n".$TableMLine[1].";\n\n";
			for ($i = 0, $st_counter = 0; $i < $fields_amount;   $i++, $st_counter=0) {
				while($row = $result->fetch_row())	{ //when started (and every after 100 command cycle):
					if ($st_counter%100 == 0 || $st_counter == 0 )	{$content .= "\nINSERT INTO ".$table." VALUES";}
						$content .= "\n(";    for($j=0; $j<$fields_amount; $j++){ $row[$j] = str_replace("\n","\\n", addslashes($row[$j]) ); if (isset($row[$j])){$content .= '"'.$row[$j].'"' ;}  else{$content .= '""';}	   if ($j<($fields_amount-1)){$content.= ',';}   }        $content .=")";
					//every after 100 command cycle [or at last line] ....p.s. but should be inserted 1 cycle eariler
					if ( (($st_counter+1)%100==0 && $st_counter!=0) || $st_counter+1==$rows_num) {$content .= ";";} else {$content .= ",";}	$st_counter=$st_counter+1;
				}
			} $content .="\n\n\n";
		}
		$content .= "\r\n\r\n/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;\r\n/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;\r\n/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;";
		$backup_name = $backup_name ? $backup_name : $name.'___('.date('H-i-s').'_'.date('d-m-Y').').sql';
		ob_get_clean(); header('Content-Type: application/octet-stream');  header("Content-Transfer-Encoding: Binary");  header('Content-Length: '. (function_exists('mb_strlen') ? mb_strlen($content, '8bit'): strlen($content)) );  header("Content-disposition: attachment; filename=\"".$backup_name."\""); 
		echo $content; exit;
	}   //see import.php too
	
	
	
	// EXAMPLE:	IMPORT_TABLES("localhost","user","pass","db_name", "my_baseeee.sql"); //TABLES WILL BE OVERWRITTEN
	// P.S. IMPORTANT NOTE for people who try to change/replace some strings  in SQL FILE before importing, MUST READ:  https://goo.gl/2fZDQL
	// https://github.com/tazotodua/useful-php-scripts 
	function IMPORT_TABLES__SDIEM($host,$user,$pass,$dbname, $sql_file_OR_content){
		set_time_limit(3000); 
		$SQL_CONTENT = (strlen($sql_file_OR_content) > 300 ?  $sql_file_OR_content : file_get_contents($sql_file_OR_content)  );  
		$allLines = explode("\n",$SQL_CONTENT); 
		$mysqli = new mysqli($host, $user, $pass, $dbname); if (mysqli_connect_errno()){echo "Failed to connect to MySQL: " . mysqli_connect_error();} 
			$zzzzzz = $mysqli->query('SET foreign_key_checks = 0');	        preg_match_all("/\nCREATE TABLE(.*?)\`(.*?)\`/si", "\n". $SQL_CONTENT, $target_tables); foreach ($target_tables[2] as $table){$mysqli->query('DROP TABLE IF EXISTS '.$table);  }         $zzzzzz = $mysqli->query('SET foreign_key_checks = 1');    $mysqli->query("SET NAMES 'utf8'");	
		$templine = '';	// Temporary variable, used to store current query
		foreach ($allLines as $line)	{											// Loop through each line
			if (substr($line, 0, 2) != '--' && $line != '') {$templine .= $line; 	// (if it is not a comment..) Add this line to the current segment
				if (substr(trim($line), -1, 1) == ';') {		// If it has a semicolon at the end, it's the end of the query
					if(!$mysqli->query($templine)){ print('Error performing query \'<strong>' . $templine . '\': ' . $mysqli->error . '<br /><br />');  }  $templine = ''; // set variable to empty, to start picking up the lines after ";"
				}
			}
		}	return 'Importing finished. Now, Delete the import file.';
	}   //see also export.php 
	
	

// =====================================  ### database    EXPORT+IMPORT =================== //
add_action( (is_multisite() ? 'network_admin_menu' : 'admin_menu') , function() {add_submenu_page(   (is_multisite() ?  'settings.php' : 'options-general.php'), 'IMPORT_EXPORT_DB', 'IMPORT_EXPORT_DB',  'create_users', pluginpage__SDIEM, 'fnc3598__SDIEM' ); 	} );

function fnc3598__SDIEM() 	{	
	if(!is_admin__SDIEM()) return;
	$opts =get_mainsite_options__SDIEM();
	$noncee=wp_create_nonce('upd_sdieem');
	
	//if(!empty($_POST['enable_quick_links'])) {$opts['enable_quicklinks']=1; update_site_option('Opts__SDIEM',$opts); }
	?>
	<br/><br/>Export DATABASE : <a href="<?php  echo home_url().'/?export__SDIEM=y&submited_sdiem='.$noncee;?>" target="_blank" id="DBEXPORT_button" style="background:#e7e7e7;padding:4px;border:1px solid;" > Click Here</a>
	<script type="text/javascript"><?php if (isset($_GET['autoexp'])) { ?>document.getElementById("DBEXPORT_button").click();<?php  } ?> </script> 
	
	<form action="" method="POST" enctype="multipart/form-data" target="_blank" style="margin:50px 0 0 0;"> 
	<input type="hidden" name="import__SDIEM" value="y" />Import DATABASE: <input type="file" name="fileToUpload__SDIEM" multiple >  <input style="margin:0 0 0 20px;" type="submit" value="OK" /> <input type="checkbox" name="replace_domain__SDIEM" value="ok" checked="checked">(replace domain name too? <a href="javascript:alert('For example, when you import database from other domain/website, then mainsite url needs to be replaced too. This script will try its best to do this automatically (If you import the database exported from this site exactly, then no need to check this)');void(0);">READ MORE</a>) 
	<br/>  (Note! before importing, I suggest to backup current database)  
	<input type="hidden" name="submited_sdiem" value="<?php echo $noncee;?>" />
	</form>
	
	<!-- <form action="" method="POST" style="margin:250px 0 0 0; border:3px solid; padding:6px; float:right;"> 
	Enable quick-link: <input type="checkbox" name="enable_quick_links" value="ok" <?php //checked($opts['enable_quicklinks'],1);?> />
	<input type="hidden" name="submited_sdiem" value="<?php //echo $noncee;?>" /> <input type="submit" value="save" />
	</form> -->
	<?php 
}


//if submitted

if (isset($_REQUEST['submited_sdiem'])){  
	add_action('init', 'execute_myfunc88__SDIEM');  function execute_myfunc88__SDIEM(){ 
		if (is_admin__SDIEM()){
		  //IF FORM SUBMITED 
			if (!empty($_REQUEST['submited_sdiem'])) {	validate_Nonce__SDIEM($_REQUEST['submited_sdiem'],'upd_sdieem'); 
			  //IF EXPORT
				if (!empty($_GET['export__SDIEM'])){EXPORT_TABLES__SDIEM(DB_HOST, DB_USER, DB_PASSWORD ,DB_NAME   ,false, false, array()  ) ; }
			  //IF IMPORT
				elseif  (!empty($_POST['import__SDIEM'])){
					$fileName=	__DIR__ .'/temp_'.rand().'_file_'.rand().'.sql';
					move_uploaded_file($_FILES['fileToUpload__SDIEM']['tmp_name'],$fileName );
					$domain_change=array();
					if(!empty($_POST['replace_domain__SDIEM'])){
						$content=file_get_contents($fileName);
						//get database prefix
						preg_match('/\nCREATE TABLE(.*?)`(.*?)posts`/i',$content,$a);			$prefix=$a[2];		//i.e. wp_4
						$new_prefix= preg_replace('/_(\d{1,4})/si', '', $prefix);									//i.e. wp
						if(strpos($content, 'CREATE TABLE `'.$new_prefix.'posts`') !==false || strpos($content, 'CREATE TABLE IF NOT EXISTS `'.$new_prefix.'posts`') !==false)	{$prefix=$new_prefix;}
						
						//get&change old HOME URL
						preg_match('/\nINSERT INTO '.$prefix.'options VALUES(.*?)\("1","siteurl","(.*?)"/si',$content,$b); 	$site_name=$b[2];
						$content=	DOMAIN_or_STRING_modifier_in_DB__SDIEM($site_name, home_url(), $content);
						
						//get&change old DOMAIN URL in MULTI-SITE INSTALLATIONS(if exists)
						preg_match('/\nINSERT INTO wp_site VALUES(.*?)\("1","(.*?)","(.*?)"\)/si',$content,$c);	
						if(!empty($c[2])) { 
							$old_domain=$c[2];		 $old_path=$c[3];
							if(function_exists('get_sites')) {$sites=get_sites();}   elseif(function_exists('wp_get_sites')) {$sites=wp_get_sites();} 
							if(!empty($sites))	{ $new_domain=$sites[0]['domain'];		$new_path=$sites[0]['path'];}
							else				{ $new_domain=$_SERVER['HTTP_HOST'];	$new_path=home_url('/','relative'); }
						  //replace of path  (only several fixed occurences in wp_blogs and etc..)
							$content=str_replace('"'. $old_domain .'","'.$old_path  , '"'. $new_domain .'","'.$new_path, $content);
						  //replace of domain (many nested occurences)
							$content=DOMAIN_or_STRING_modifier_in_DB__SDIEM($old_domain,$new_domain,	$content);
						}
						file_put_contents($fileName,$content );
					}
					IMPORT_TABLES__SDIEM(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME,  $fileName ); 
					@unlink($fileName);
					
					//IF database prefixes are different, then change DB prefix too
					if( $new_prefix != $GLOBALS['wpdb']->base_prefix) {
						$wp_conf= ABSPATH .'wp-config.php'; 
						file_put_contents($wp_conf,   preg_replace('/\$table_prefix(.*?)\;/', '\$table_prefix = \''.$new_prefix.'\';', file_get_contents($wp_conf)) );
					}
				} 
			}
		} exit('<div style="top:100px; width:80%; margin:0 0 0 10%;background:#e7e7e7;" > Importing done. <br/><br/>Now, you can enter <a href="'.admin_url().'">Dashboard</a>;
		<br/> <br/> <br/> 
		(P.S. dont forget, if this site was STAND-ALONE Wordpress installation, and now you\'ve imported MULTI-SITE Installation database, then you have to update <b style="color:red;">wp-config.php</b> and <b style="color:red;">.htaccess</b> too, with Multi-site parameters (Examples: <a href="https://goo.gl/M5AWoU" target="_blank">wp-config</a> and <a href="https://goo.gl/HPJxgT" target="_blank">.htaccess</a>)!!! </div>'); 
	}
}
//if(requestURIfromHome ==  '/e') {header("location:". Export_short_urlll__SDIEM );exit;}






								
								//===========  links in Plugins list ==========//
								add_filter( "plugin_action_links_".plugin_basename( __FILE__ ), function ( $links ) {   $links[] = '<a href="'.plugin_settings_page__SDIEM.'">Settings</a>'; $links[] = '<a href="http://paypal.me/tazotodua">Donate</a>';  return $links; } );
								//REDIRECT SETTINGS PAGE (after activation)
								add_action( 'activated_plugin', function($plugin ) { if( $plugin == plugin_basename( __FILE__ ) ) { exit( wp_redirect( plugin_settings_page__SDIEM.'&isactivation'  ) ); } } );
?>