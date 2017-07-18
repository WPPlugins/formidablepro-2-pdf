<?php
/**
 * Plugin Name: Formidable PRO2PDF
 * Version: 2.65
 * Description: This plugin allows to export data from Formidable Pro forms to PDF
 * Author: Alexandre S.
 * Plugin URI: http://www.formidablepro2pdf.com/
 * Author URI: http://www.formidablepro2pdf.com/
 */

if ( !defined('ABSPATH') )
  exit;


require_once( __DIR__ . '/classes/class-fpropdf-global.php');
global $fpropdf_global;
$fpropdf_global = new Fpropdf_Global();

define('FPROPDF_VERSION', '262');

function fpropdf_enable_security()
{
  return ( get_option('fpropdf_enable_security') and !defined('FPROPDF_IS_SENDING_EMAIL') and !defined('FPROPDF_IS_DATA_SUBMITTING') );
}

function fpropdf_check_user_role( $roles )
{
  if ( !$roles )
    return true;
  $result = true;
  
  $hierarchy = array(
    'contributor'          => array( 'subscriber' ),
    'author'               => array( 'subscriber', 'contributor' ),
    'editor'               => array( 'subscriber', 'contributor', 'author' ),
    'administrator'        => array( 'subscriber', 'contributor', 'author', 'editor' ),
    'super_admin'          => array( 'subscriber', 'contributor', 'author', 'editor' ),
    'superadmin'           => array( 'subscriber', 'contributor', 'author', 'editor' ),
    'super_admininstrator' => array( 'subscriber', 'contributor', 'author', 'editor' ),
  );
  
  foreach ( explode(',', $roles ) as $v )
  {
    $v = trim( $v );
    if ( ! $v )
      continue;
    if ( $v == 'all' )
      return true;
    if ( ! is_user_logged_in() )
      return false;
    $result = false;
  	$current_user = wp_get_current_user();
  	$current_user_roles = $current_user->roles;
  	foreach ( $current_user_roles as $role )
  	{
      if ( $v == 'any' )
        return true;
  	  if ( strtolower( $role ) == strtolower( $v ) )
  	    return true;
  	  if ( isset( $hierarchy[ $role ] ) )
  	    foreach ( $hierarchy[ $role ] as $hierarchy_role )
      	  if ( strtolower( $hierarchy_role ) == strtolower( $v ) )
      	    return true;
  	}
  }
  return $result;
}

function fpropdf_check_user_id( $ids )
{
  if ( !$ids )
    return true;
  $result = true;
  foreach ( explode(',', $ids ) as $v )
  {
    $v = trim( $v );
    if ( ! $v )
      continue;
    if ( $v == 'all' )
      return true;
    if ( ! is_user_logged_in() )
      return false;
    if ( $v == 'any' )
      return true;
    $result = false;
  	$current_user = wp_get_current_user();
	  if ( intval( $current_user->ID ) == intval( $v ) )
	    return true;
  }
  return $result;
}

function fpropdf_field_id_to_key( $id )
{
  // if ( ! fpropdf_use_field_keys() ) return $id;
  if ( preg_match('/^FPROPDF_/', $id ) )
    return $id;
  if ( ! is_numeric( $id ) )
    return $id;
  global $wpdb;
  $got_id = $wpdb->get_var( 'SELECT field_key FROM ' . $wpdb->prefix . 'frm_fields WHERE id = ' . intval( $id ) );
  if ( $got_id )
    return $got_id;
  return $id;
}

function fpropdf_field_key_to_id( $id )
{
  // if ( ! fpropdf_use_field_keys() ) return $id;
  if ( is_numeric( $id ) )
    return $id;
  global $wpdb;
  $query = $wpdb->prepare( 'SELECT id FROM ' . $wpdb->prefix . 'frm_fields WHERE field_key = %s', $id );
  $got_id = $wpdb->get_var( $query );
  if ( $got_id )
    return $got_id;
  return $id;
}

function fpropdf_dataset_key( $dataset, $form, $layout, $user='', $role='', $condition='' ) 
{
  global $wpdb;
  if ( $user || $role || $condition )
  {
     $layout .= $user . ',' . $role . ',' . $condition;
  }
  return md5( implode(',', array( NONCE_SALT, $wpdb->prefix, $dataset, $form, $layout ) ) );
}

function fpropdf_admin_head()
{
  $additional = apply_filters( 'fpropdf_additional_formatting', array() );
  if ( !count($additional) )
    return;
  echo '<script type="text/javascript">';
    echo 'window.fpropdfAdditionalFormatting = ' . json_encode( array_keys( $additional ) ) . ";\n";
  echo '</script>';
}
add_action('admin_head', 'fpropdf_admin_head');

$temp_dir = sys_get_temp_dir();
if ( !is_writable($temp_dir) || !is_readable($temp_dir) )
  $temp_dir = ABSPATH . '/tmp/';
if ( !defined('PROPDF_TEMP_DIR') )
  define('PROPDF_TEMP_DIR', $temp_dir);

// fpropdfTmpFile
$dir = PROPDF_TEMP_DIR;
@shell_exec(sprintf('find %s -name "*fpropdfTmpFile*" -type f -mmin +60 -delete 2>&1', $dir));


@include __DIR__ . '/settings.php';

@include __DIR__ . '/backups.php';
@include __DIR__ . '/debug.php';
@include __DIR__ . '/templates.php';
@ini_set('display_errors', 'off');

function fpropdf_set_charset() {
  
  global $wpdb;
  $exists = $wpdb->get_var(
    $wpdb->prepare(
      'SELECT COUNT(*)
        FROM information_schema.TABLES
        WHERE (TABLE_SCHEMA = %s) AND (TABLE_NAME = %s)',
      DB_NAME,
      FPROPDF_WPFXLAYOUTS
    )
  ) > 0;
  if ($exists === false) {
    $wpdb->query('RENAME TABLE wp_fxlayouts TO ' . FPROPDF_WPFXLAYOUTS);
  }
  
  if ( ! file_exists( FPROPDF_BACKUPS_DIR ) )
  {
    // Create forms folder in wp-content/uploads
    @mkdir(FPROPDF_BACKUPS_DIR, 0755);
    
    $rows = $wpdb->get_results('SELECT * FROM ' . FPROPDF_WPFXLAYOUTS, ARRAY_A );
    $num = count( $rows );
    $ids = array();
    for ( $i = 0; $i < $num; $i++ )
    {
      $row = $rows[ $i ];
      $ids[] = $row['ID'];
    }
    foreach ( $ids as $id )
      wpfx_backup_layout($id);
    
  }
  
}
add_action('init', 'fpropdf_set_charset');

$upload_dir = wp_upload_dir();
define('FPROPDF_FORMS_DIR', $upload_dir['basedir'] . '/fpropdf-forms/');
define('FPROPDF_BACKUPS_DIR', $upload_dir['basedir'] . '/fpropdf-backups/');

global $wpdb;
define('FPROPDF_WPFXLAYOUTS', $wpdb->prefix . 'fpropdf_layouts');
define('FPROPDF_WPFXFIELDS', $wpdb->prefix . 'fpropdf_fields');
define('FPROPDF_WPFXTMP', $wpdb->prefix . 'fpropdf_tmp');

if ( ! file_exists( FPROPDF_FORMS_DIR ) )
{
  // Create forms folder in wp-content/uploads
  @mkdir(FPROPDF_FORMS_DIR, 0755);

  // Move old forms to new folder
  $old_forms = __DIR__ . '/forms/';

  if ( file_exists( $old_forms ) )
    if ($handle = opendir( $old_forms )) 
    {
      while (false !== ($entry = readdir($handle)))
      {
        if ( $entry == '.' ) continue;
        if ( $entry == '..' ) continue;
        @rename( $old_forms . $entry, FPROPDF_FORMS_DIR . $entry );
      }
    }
}

// Plugin settings link in Plugins list
add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'fpropdf_add_action_links' );


function fpropdf_add_action_links ( $links ) {
  $mylinks = array(
    '<a href="' . admin_url( 'admin.php?page=fpdf' ) . '">Settings</a>',
  );
  return array_merge( $links, $mylinks );
}
 
function fpropdf_use_field_keys()
{
  if ( defined('FPROPDF_USE_KEYS') )
    return FPROPDF_USE_KEYS;
  return get_option( 'fpropdf_use_field_keys' );
}
 
function fpropdf_myplugin_activate() {
  global $wpdb;
  
  if ( ! get_option('fpropdf_licence') )
  {
    update_option( 'fpropdf_use_field_keys', '1' );
    update_option( 'fpropdf_limit_dropdowns', '1' );
   
    if ( ! get_option('fpropdf_installed_version') )
    {
      update_option( 'fpropdf_installed_version', '20000' );
      
    }
    
  }
  
  update_option( 'fpropdf_enable_security', '1' );

  $exists = $wpdb->get_var(
    $wpdb->prepare(
      'SELECT COUNT(*)
        FROM `INFORMATION_SCHEMA`.`TABLES`
        WHERE `TABLE_SCHEMA` = %s AND `TABLE_NAME` = %s',
      DB_NAME,
      wp_fxlayouts
    )
  ) > 0;
  if ($exists === true) {
    $wpdb->query('RENAME TABLE wp_fxlayouts TO ' . FPROPDF_WPFXLAYOUTS);
  }
  
  
  $wpdb->query('CREATE TABLE IF NOT EXISTS `' . FPROPDF_WPFXLAYOUTS . '` (
    `ID` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(255) CHARACTER SET utf8 NOT NULL,
    `file` varchar(255) CHARACTER SET utf8 NOT NULL,
    `data` LONGTEXT CHARACTER SET utf8 NOT NULL,
    `visible` tinyint(1) NOT NULL,
    `form` int(11) NOT NULL,
    `dname` int(11) NOT NULL,
    `created_at` datetime NOT NULL,
    `formats` LONGTEXT CHARACTER SET utf8,
    PRIMARY KEY (`ID`)
  ) CHARACTER SET utf8');
  

    $wpdb->query('CREATE TABLE IF NOT EXISTS `' . FPROPDF_WPFXFIELDS . '` (
    `ID` int(11) NOT NULL AUTO_INCREMENT,
    `field_key` varchar(255) CHARACTER SET utf8 NOT NULL,
    `field_id` int(11) NOT NULL,
    `form_id`  int(11) NOT NULL,
    PRIMARY KEY (`ID`)
  ) CHARACTER SET utf8');
    
     $wpdb->query('CREATE TABLE IF NOT EXISTS `' . FPROPDF_WPFXTMP . '` (
    `ID` int(11) NOT NULL AUTO_INCREMENT,
    `form_id` int(11) NOT NULL,
    `layout_id` int(11) NOT NULL,
    `entry_id` int(11) NOT NULL,
    `path` varchar(255) NOT NULL,
    PRIMARY KEY (`ID`)
    ) CHARACTER SET utf8');


  $columns = $wpdb->get_col(
    $wpdb->prepare('SELECT `COLUMN_NAME`
        FROM `INFORMATION_SCHEMA`.`COLUMNS`
        WHERE `TABLE_SCHEMA` = %s AND `TABLE_NAME` = %s',
      DB_NAME,
      FPROPDF_WPFXLAYOUTS
    )
  );
  
  if (!in_array('formats', $columns)) {
    $wpdb->query("ALTER TABLE " . FPROPDF_WPFXLAYOUTS . " ADD COLUMN formats LONGTEXT CHARACTER SET utf8");
  }
  if (!in_array('passwd', $columns)) {
    $wpdb->query("ALTER TABLE " . FPROPDF_WPFXLAYOUTS . " ADD COLUMN passwd VARCHAR(255)");
  }
  if (!in_array('lang', $columns)) {
    $wpdb->query("ALTER TABLE " . FPROPDF_WPFXLAYOUTS . " ADD COLUMN lang INT(3) UNSIGNED NOT NULL DEFAULT '0'");
  }
  if (!in_array('name_email', $columns)) {
    $wpdb->query("ALTER TABLE " . FPROPDF_WPFXLAYOUTS . " ADD COLUMN name_email VARCHAR(255)");
  }
  if (!in_array('restrict_user', $columns)) {
    $wpdb->query("ALTER TABLE " . FPROPDF_WPFXLAYOUTS . " ADD COLUMN restrict_user TEXT CHARACTER SET utf8");
  }
  if (!in_array('restrict_role', $columns)) {
    $wpdb->query("ALTER TABLE " . FPROPDF_WPFXLAYOUTS . " ADD COLUMN restrict_role TEXT CHARACTER SET utf8");
  }
  if (!in_array('default_format', $columns)) {
    $wpdb->query("ALTER TABLE " . FPROPDF_WPFXLAYOUTS . " ADD COLUMN default_format VARCHAR(255) NOT NULL DEFAULT 'pdf'");
  }
  if (!in_array('add_att', $columns)) {
    $wpdb->query("ALTER TABLE " . FPROPDF_WPFXLAYOUTS . " ADD COLUMN add_att INT(3) UNSIGNED NOT NULL DEFAULT '0'");
  }
  if (!in_array('add_att_ids', $columns)) {
    $wpdb->query("ALTER TABLE " . FPROPDF_WPFXLAYOUTS . " ADD COLUMN add_att_ids VARCHAR(255) NOT NULL DEFAULT 'all'");
  }

  $wpdb->query("ALTER TABLE " . FPROPDF_WPFXLAYOUTS . " MODIFY COLUMN `formats` LONGTEXT");
  $wpdb->query("ALTER TABLE " . FPROPDF_WPFXLAYOUTS . " MODIFY COLUMN `data` LONGTEXT");
  
  update_option( 'fpropdf_version', FPROPDF_VERSION);

  if ( ! get_option('fpropdf_licence') )
    update_option( 'fpropdf_licence', 'TRIAL' . strtoupper(FPROPDF_SALT) );

}
register_activation_hook( __FILE__, 'fpropdf_myplugin_activate' );


if (!get_option('fpropdf_version') || get_option('fpropdf_version') < FPROPDF_VERSION) {
    $wpdb->query('CREATE TABLE IF NOT EXISTS `' . FPROPDF_WPFXFIELDS . '` (
    `ID` int(11) NOT NULL AUTO_INCREMENT,
    `field_key` varchar(255) CHARACTER SET utf8 NOT NULL,
    `field_id` int(11) NOT NULL,
    `form_id`  int(11) NOT NULL,
    PRIMARY KEY (`ID`)
    ) CHARACTER SET utf8');
    
    
    $wpdb->query('CREATE TABLE IF NOT EXISTS `' . FPROPDF_WPFXTMP . '` (
    `ID` int(11) NOT NULL AUTO_INCREMENT,
    `form_id` int(11) NOT NULL,
    `layout_id` int(11) NOT NULL,
    `entry_id` int(11) NOT NULL,
    `path`varchar(255) NOT NULL,
    PRIMARY KEY (`ID`)
    ) CHARACTER SET utf8');
    
    
    
  update_option( 'fpropdf_version', FPROPDF_VERSION);
}

include 'class.php';

// Define some consts
$wpfx_idd = 'fpdf';
$wpfx_dsc = 'Formidable PRO2PDF';

// Plugin base url
$wpfx_url = trailingslashit( WP_PLUGIN_URL. '/' .dirname( plugin_basename(__FILE__) ) );

// Generate file
function wpfx_output($form, $content)
{
  $form = FPROPDF_FORMS_DIR . $form;
  $temp = tempnam(PROPDF_TEMP_DIR, 'fpropdfTmpFile');
  $file = fopen  ($temp, 'w');

  if($file)
  {
    $output = tempnam(PROPDF_TEMP_DIR, 'fpropdfTmpFile');

    fwrite($file, $content);
    fclose($file);

    return $temp;
  } 
  else 
    die("Can not open a temporary file for writing, verify the permissions.");
}

function wpfx_download($content)
{
  $temp = tempnam(PROPDF_TEMP_DIR, 'fpropdfTmpFile');
  $file = fopen  ($temp, 'w');

  if ( $file )
  {
    fwrite($file, $content);
    fclose($file);

    return $temp;
  } 
  else 
    die("Can not open a temporary file for writing, verify the permissions.");
}

// Field mapping is performed here
function wpfx_extract($layout, $id, $custom = false)
{
  global $wpdb;

  $layoutId = $layout;
  $id = intval( $id ); // Filter IDs

  $data   = array();
  $array  = array();
  $query  = "SELECT `field_id` as id, `meta_value` as value FROM `".$wpdb->prefix."frm_item_metas` WHERE `item_id` IN( $id ";

  // handle rental quotes form which is preceding inflatable
  if($layout == 1)
    $query .= ", ".($id - 1).")";
  else $query .= " )";

  $rows = $wpdb->get_results( $query, ARRAY_A );

  $entry = FrmEntry::getOne($id, true);
  $entryId = $id;
  $formId = $entry->form_id;
  $fields = FrmField::get_all_for_form( $entry->form_id, '', 'include' );


  foreach ( $rows as $index => $row )
  {
    $query  = "SELECT `type` FROM `".$wpdb->prefix."frm_fields` WHERE `id` = " . intval( $row['id'] );
    $data = $wpdb->get_row( $query, ARRAY_A );
    if ( !$data ) continue;
    if ( $data['type'] == 'image' ) 
    {
      $url = $row['value'];
      $response = wp_remote_get( $url );
      // print_r( $row ); print_r( $response ); exit; 
      if ( is_array($response) )
      {
        $rows[ $index ]['value'] = implode(':', array(
          'FPROPDF_IMAGE_FIELD',
          basename( $url ),
          base64_encode( $response['body'] )
        ));
      }
    }
    if ( $data['type'] == 'file' )
    {
      $files = @unserialize( $row['value'] );
      if ( $files and is_array( $files ) )
      {
        
      }
      else
      {
        $files = array( $row['value'] );
      }
      if ( $files and is_array($files) and count($files) )
      {
        $image = false;
        foreach ( $files as $filesIndex => $file )
        {
          $path = get_attached_file( $file );
          if ( preg_match('/\.(jpe?g|png|gif)$/i', $path ) )
          {
            $image = $path;
            $rows[ $index ]['value'] = 'FPROPDF_IMAGE:' . str_replace(ABSPATH, '/', $path);
          }
        }
        if ( ! $image )
        {
          $urls = array();
          foreach ( $files as $file )
            $urls[] = wp_get_attachment_url( $file );
          $rows[ $index ]['value'] = implode(' ', $urls);
        }
      }
    }
    if ( ( $data['type'] == 'data' ) or ( $data['type'] == 'checkbox' ) )
    {
      foreach ( $fields as $field )
      {
        if ( $field->id != $row['id'] ) continue;
        $embedded_field_id = ( $entry->form_id != $field->form_id ) ? 'form' . $field->form_id : 0;
        $atts = array(
          'type' => $field->type, 'post_id' => $entry->post_id,
          'show_filename' => true, 'show_icon' => true, 'entry_id' => $entry->id,
          'embedded_field_id' => $embedded_field_id,
        );

        //if ( isset( $_GET['testing'] ) ) { print_r($entry); print_r($field); exit; }
        if ( $data['type'] == 'data' )
          $rows[ $index ]['value'] = FrmEntriesHelper::prepare_display_value($entry, $field, $atts);
        else
          $rows[ $index ]['value'] = $entry->metas[ $field->id ];
      }

      //$query  = "SELECT `meta_value` FROM `".$wpdb->prefix."frm_item_metas` WHERE `item_id` = ".intval( $row['value'] ) . ' ORDER BY id DESC LIMIT 1';
      //if ( !$data2 ) continue;
      //$rows[ $index ]['value'] = $data2['meta_value'];
    }
  }
  
  $query  = "SELECT `id`, `description` AS `value` FROM `".$wpdb->prefix."frm_fields` WHERE `type` = 'html' AND `form_id` = " . intval( $entry->form_id );
  $results = $wpdb->get_results($query, ARRAY_A );
  foreach ( $results as $buf )
  {
    $s = $buf['value'];
    $s = strip_tags($s);
    $s = html_entity_decode($s, ENT_COMPAT | ENT_HTML401, 'UTF-8');
    $buf['value'] = $s;
    $rows[] = $buf;
  }
  

  $query  = "SELECT * FROM `".$wpdb->prefix."frm_items` WHERE id = $id";
  $row = $wpdb->get_row( $query, ARRAY_A );
  if ( ! $row )
    $row = array();
  
  $description = @unserialize( $row['description'] );
  if ( ! $description )
    $description = array();

  $referrer = '';
  if( @preg_match('/Referer +\d+\:[ \t]+([^\n\t]+)/', $description['referrer'], $m) )
    $referrer = $m[1];
  else
    $referrer = $description['referrer'];

  $rows[] = array(
    'id'    => 'FPROPDF_ITEM_KEY',
    'value' => $row['item_key'],
  );
  $rows[] = array(
    'id'    => 'FPROPDF_BROWSER',
    'value' => $description['browser'],
  );
  $rows[] = array(
    'id'    => 'FPROPDF_IP',
    'value' => $row['ip'],
  );
  $rows[] = array(
    'id'    => 'FPROPDF_CREATED_AT',
    'value' => get_date_from_gmt( $row['created_at'], 'Y-m-d H:i:s' ),
  );
  $rows[] = array(
    'id'    => 'FPROPDF_UPDATED_AT',
    'value' => get_date_from_gmt( $row['updated_at'], 'Y-m-d H:i:s' ),
  );
  $rows[] = array(
    'id'    => 'FPROPDF_REFERRER',
    'value' => $referrer,
  );
  $rows[] = array(
    'id'    => 'FPROPDF_USER_ID',
    'value' => $row['user_id'],
  );
  $rows[] = array(
    'id'    => 'FPROPDF_DATASET_ID',
    'value' => $entryId,
  );
  
  global $wpdb;
  $counter1 = $wpdb->get_var('SELECT COUNT(*) FROM ' . $wpdb->prefix . 'frm_items WHERE form_id = ' . intval($formId) . ' AND id <= ' . intval($entryId) . ' ORDER BY id ASC');
  
  $counter2Key = 'fpropdf_layout_' . $layoutId . '_counter2_for_form_' . $formId;
  $counter2 = get_option($counter2Key);
  if ( !$counter2 )
    $counter2 = 1;
  $counter2KeyItem = 'fpropdf_layout_' . $layoutId . '_counter2_for_form_' . $formId . '_entry_' . $entryId;
  if ( !get_option($counter2KeyItem) )
  {
    update_option($counter2Key, $counter2 + 1);
    update_option($counter2KeyItem, $counter2);
  }
  else
  {
    $counter2 = get_option($counter2KeyItem);
  }
  
  $rows[] = array(
    'id'    => 'FPROPDF_COUNTER1',
    'value' => $counter1,
  );
  $rows[] = array(
    'id'    => 'FPROPDF_COUNTER2',
    'value' => $counter2,
  );

  $data = array();

  // get data
  foreach ( $rows as $row )
  {
    $key = $row['id'];
	
	if(is_array($row['value'])){
		 $new_array = array();
		foreach ($row['value'] as $kk => $arr_val){	
			$new_array[$kk] = stripslashes($arr_val);
		}
		$val = $new_array;
	}else{
		$val = stripslashes($row['value']);
	}

    $found = false;
    foreach ( $data as $dataKey => $values )
      if ( $values[ 0 ] == $key )
      {
        $found = true;
        $data[ $dataKey ][ 1 ] = $val;
      }
    if ( !$found )
      $data[] = array( $key, $val );
  }

  //print_r($data); exit;

  switch($layout)
  {
  case 1: // inflatable app
    $array = array(1135 => 50, 1139 => 73, 1131 => 60, 1140 => 72, 1163 => 74, 1150 => 53, 1125 => 78, 1125 => 79,
      1124 => 82, 1130 => 56, 1127 => 57, 1128 => 59, 1363 => 'List', 1168 => 393, 1147 => 125,
      1148 => 216, 1462 => 151, 1462 => 31); // last one is date filling
    break;

  case 2: // business quote
    $array = array(845 => 71, 848 => 349, 826 => 378, 923 => 389, 876 => 491, 828 => 492, 847 => 489, 830 => 50,
      837 => 102, 928 => 60, 1052 => 346, 844 => 73, 927 => 53, 925 => 74, 932 => 72, 853 => 75,
      854 => 78, 840 => 79, 856 => 80, 855 => 82, 881 => 56, 882 => 57, 883 => 58, 884 => 59,
      857 => 91, 859 => 92, 858 => 93, 860 => 95);
    break;

  case 3: // use custom layout
    $array = $custom;
    break;
  }

  //print_r($data);
  //print_r($array);
  //exit;

  // Prepare list for fdf forming in case of missing fields
  $awesome = array();
  if(is_array($array)) 
    foreach($array as $datakey => $fdfKey)
    {
      $found = false;
      foreach ( $data as $values )
        if ( ( fpropdf_field_id_to_key($values[0]) == fpropdf_field_id_to_key($fdfKey[0]) ) or ( ($values[0]) == ($fdfKey[0]) ) )
        {
          $awesome[] = array( $fdfKey[ 1 ], $values[ 1 ] );
          $found = true;
        }
      if ( ! $found )
        $awesome[] = array( $fdfKey[ 1 ], '');
    }

  //print_r($awesome);
  
  return $awesome;
}

function fpropdf_bash_replace($string)
{
  return $string;
}

function fpropdf_custom_command_exist($cmd) {
  $returnVal = @shell_exec("which $cmd");
  return ( empty($returnVal) ? false : true );
}

function fpropdf_is_activated()
{
  if ( defined('FPROPDF_IS_MASTER') )
    return true;
  $code = get_option('fpropdf_licence');
  return $code;
}

function fpropdf_is_trial()
{
  $code = get_option('fpropdf_licence');
  return ( $code and preg_match( '/^TRIAL/', $code) );
}

function fpropdf_check_code($code, $update=0)
{
  if ( ! function_exists('curl_init') )
    throw new Exception('Curl extension is not enabled on this server.');
  $curl = curl_init();
  curl_setopt_array($curl, array(
    CURLOPT_RETURNTRANSFER => 1,
    CURLOPT_URL => FPROPDF_SERVER . 'licence/check.php',
    CURLOPT_POST => 1,
    CURLOPT_HTTPHEADER => array('Expect:'),
    CURLOPT_POSTFIELDS => array(
      'salt'   => FPROPDF_SALT,
      'code'   => $code,
      'update' => $update,
      'site_url' => site_url('/')
    )
  ));
  $result = curl_exec($curl);
  if ( ! $result )
    throw new Exception('Server did not return any results. Please try again later.');
  $result = json_decode($result);
  if ( $result->activated )
  {
    if ( $update )
      update_option('fpropdf_licence', $code);
    return true;
  }
  // update_option( 'fpropdf_licence', 'TRIAL' . strtoupper(FPROPDF_SALT) );
  throw new Exception('This licence code is not valid.');
  return false;
}

function wpfx_stripslashes_array($array)
{
  if ( function_exists('get_magic_quotes_gpc') and get_magic_quotes_gpc() )
    return is_array($array) ? array_map('wpfx_stripslashes_array', $array) : stripslashes($array);
  else
    return $array;
}

function wpfx_addslashes_array($array)
{
  if ( function_exists('get_magic_quotes_gpc') and get_magic_quotes_gpc() )
    return $array;
  else
    return is_array($array) ? array_map('wpfx_addslashes_array', $array) : addslashes($array);
}

define('FPROPDF_SERVER', 'http://www.idealchoiceinsurance.com/wp-content/plugins/fpropdf/');
global $wpdb;
define('FPROPDF_SALT', md5( NONCE_SALT . $wpdb->prefix) );

// Admin Options page
function wpfx_admin()
{
  
  global $wpfx_url, $wpfx_idd, $wpfx_dsc;
  
  if ( class_exists( 'FrmXMLHelper' ) )
  {
    if ( get_option( 'fpropdf_installed_version' ) >= 20000 )
    {
      if ( ! get_option( 'fpropdf_demo_imported' ) )
      {
        fpropdf_restore_backup( dirname( __FILE__ ) . '/demo.json', 990 );
        update_option( 'fpropdf_demo_imported', 1 );
      }
    }
  }
  
  if ( isset($_FILES['postdata']) )
  {
    //$_POST['wpfx_postdata'] = implode('', $_POST['wpfx_postdata']);
    //parse_str( $_POST['wpfx_postdata'], $output);
    $_POST['wpfx_savecl'] = 1;
    $data = file_get_contents( $_FILES['postdata']['tmp_name'] );
    //echo $data; exit;
    @unlink( $_FILES['postdata']['tmp_name'] );
    $output = json_decode( $data, true );
    if ( $output )
      foreach ( $output as $k => $v )
      {
        //if ( !is_array($v) ) continue;
        $_POST[ $k ] = $v;
        $_REQUEST[ $k ] = $v;
      }
    // print_r($_POST); exit;
  }

  $wpfx_fdf = new FDFMaker();

  echo "<div class = 'parent formidable-pro-fpdf formidable-pro-fpdf-tab-".@preg_replace('/[^a-z]+/', '', $_GET['tab'])."'>";

  echo "<div class = '_first _left'>";
  echo "<h1>$wpfx_dsc</h1>";

  if ( version_compare(PHP_VERSION, '5.3.0', '<') )
  {
    echo '<div class="error"><p>This plugin requires PHP version 5.3 or higher. Your version is '.PHP_VERSION.'. Please upgrade your PHP installation.</p></div>';
    exit;
  }

  if ( isset($_GET['action']) and ( $_GET['action'] == 'deactivatekey' ) )
  {
    update_option( 'fpropdf_licence', 'TRIAL' . strtoupper(FPROPDF_SALT) );
    echo "<div class='updated'><p>The licence key has been deactivated.</p></div>";
  }

  // Start activating

  if ( isset( $_POST['action'] ) and ( $_POST['action'] == 'activate-fpropdf' ) )
  {
    try
    {
      $code = trim( $_POST['activation-code'] );
      if ( ! $code )
        throw new Exception('Please paste the activation code into the text field.');
      fpropdf_check_code($code, 2);
      echo '<div class="updated" style="margin-left: 0;"><p>Thanks for activating Formidable PRO2PDF! You are now using the full version of the plugin.</p></div>';
    }
    catch ( Exception $e )
    {
      echo '<div class="error" style="margin-left: 0;"><p>'.$e->getMessage().' <a href="#" class="fpropdf-activate">Click here</a> to retry.</p></div>';
    }
  }

  // start checking for errors

  $errors = array();

  try
  {
    if ( ! file_exists( $tmp = FPROPDF_FORMS_DIR ) )
      throw new Exception("Folder $tmp could not be created. Please create it using FTP, and set its permissions to 777.");
    if ( ! is_writable( $tmp = FPROPDF_FORMS_DIR ) )
      throw new Exception("Folder $tmp should be writable. Please change its permissions to 777.");
  }
  catch ( Exception $e ) 
  { 
    $errors[] = $e->getMessage(); 
  }
  
  try
  {
    if ( ! file_exists( $tmp = FPROPDF_BACKUPS_DIR ) )
      throw new Exception("Folder $tmp could not be created. Please create it using FTP, and set its permissions to 777. <br /> It is required to have automatic backups of your field maps.");
    if ( ! is_writable( $tmp = FPROPDF_BACKUPS_DIR ) )
      throw new Exception("Folder $tmp should be writable. Please change its permissions to 777. <br /> It is required to have automatic backups of your field maps.");
  }
  catch ( Exception $e ) 
  { 
    $errors[] = $e->getMessage(); 
  }
  
  

  try
  {
    if ( ! is_writable( $tmp = __DIR__ . '/fields' ) )
      throw new Exception("Folder $tmp should be writable. Please change its permissions to 777.");
    if ( ! is_writable( $tmp = PROPDF_TEMP_DIR ) )
      throw new Exception("Folder $tmp should be writable. Please change its permissions to 777.");
  }
  catch ( Exception $e ) 
  { 
    $errors[] = $e->getMessage(); 
  }

  try
  {
    
    // This produced errors somewhy
    // http://www.formidablepro2pdf.com/support/subject/installation-problem-with-formidable-form-pro-version/
    if ( false )
      if ( ! file_exists( __DIR__ . '/../formidable/formidable.php') )
        throw new Exception("Formidable PRO2PDF requires Formidable Forms plugin installed and activated. Please <a target='_blank' href='plugin-install.php?tab=search&s=formidable'>install it</a>.");
    if ( ! class_exists('FrmAppHelper') ) // Check if Formidable class exists
      throw new Exception("Formidable PRO2PDF requires Formidable Forms plugin installed and activated. Please <a href='plugins.php'>activate it</a>.");
    $tmp = $version = FrmAppHelper::$plug_version;
    $version = explode('.', $version);
    if ( intval($version[0]) < 2 )
      throw new Exception("Formidable PRO2PDF requires the latest version of Formidable Forms plugin (or at least 2.0.9). Your version is $tmp. Please <a href='update-core.php'>update it</a>.");
    elseif ( intval($version[0]) == 2 )
    {
      if ( intval($version[1]) == 0 && intval($version[2]) < 9 )
        throw new Exception("Formidable PRO2PDF requires the latest version of Formidable Forms plugin (or at least 2.0.9). Your version is $tmp. Please <a href='update-core.php'>update it</a>.");
    }
  }
  catch ( Exception $e ) 
  { 
    $errors[] = $e->getMessage(); 
  }

  try
  {
    $msg = "You can generate only 1 PDF form, because ";
    if ( ini_get('safe_mode') )
      throw new Exception("PHP safe mode is turned on. Unless you <a href='#' class='fpropdf-activate'>activate this plugin</a>, it won't work with PHP safe mode.");
    $functions = explode(' ', 'exec passthru system shell_exec');
    foreach ( $functions as $function )
    {
      $d = ini_get('disable_functions');
      $s = ini_get('suhosin.executor.func.blacklist');
      if ("$d$s") {
        $array = preg_split('/,\s*/', "$d,$s");
        if (in_array($function, $array) and !fpropdf_is_activated()) {
          throw new Exception("your server has to have PHP <code>".$function."()</code> command enabled for PDF generation.");
        }
      }
    }
    if ( ! function_exists('curl_init') )
      throw new Exception("your server has to have <code>Curl</code> extension installed and enabled for PDF generation.");
    if ( ! function_exists('mb_convert_encoding') and ! function_exists('iconv') )
      throw new Exception("your server has to have PHP <code>MB</code> or <code>iconv</code> extension installed and enabled for PDF generation.");
    if ( ! fpropdf_is_activated() or fpropdf_is_trial() )
    {
      if ( ! fpropdf_custom_command_exist('ls') )
        throw new Exception("your server has to have PHP <code>shell_exec()</code> command enabled for PDF generation.");
      if ( ! fpropdf_custom_command_exist('pdftk') )
        throw new Exception("your server has to have <code>pdftk</code> installed for PDF generation. Please <a href='https://www.pdflabs.com/docs/install-pdftk-on-redhat-or-centos/' target='_blank'>install it</a>.");
    }
  }
  catch ( Exception $e ) 
  { 
    $errors[] = $msg . $e->getMessage() . " Alternatively, you can <a href='#' class='fpropdf-activate'>activate Formidable PRO2PDF</a> if you want to use more forms."; 
  }

  try
  {
    if ( ! fpropdf_is_activated() )
      throw new Exception("You are using a free version of the plugin. To unlock additional functions (no need of installing pdftk, pretty field selection and many others), please <a href='#' class='fpropdf-activate'>activate Formidable PRO2PDF</a>.");
  }
  catch ( Exception $e ) { $errors[] = $e->getMessage(); }

  foreach ( $errors as $error )
    echo '<div class="error" style="margin-left: 0;"><p>'.$error.'</p></div>';

  // end checking for errors

  if ( isset( $_SESSION['new_layout'] ) and $_SESSION['new_layout'] )
  {
    unset( $_SESSION['new_layout'] );
    echo '<div class="updated" style="margin-left: 0;"><p>Layout has been added. You can now use it.</p></div>';
    
  }

  if ( isset($_POST['action']) and ( $_POST['action'] == 'upload-pdf-file' ) )
  {
    try
    {
      if ( !isset( $_FILES['upload-pdf'] ) or !$_FILES['upload-pdf'] )
        throw new Exception('Please select a PDF file');
      $file = $_FILES['upload-pdf'];
      $fname = $file['name'];
      $tmp = $file['tmp_name'];
      if ( ! preg_match('/\.pdf$/i', $fname) )
        throw new Exception('The file should be a PDF file and have .pdf file extension. Please <a href="#" class="upl-new-pdf">upload another file</a>.');
      $fname = preg_replace('/\.pdf$/i', '.pdf', $fname);
      @move_uploaded_file( $file['tmp_name'], FPROPDF_FORMS_DIR . $fname );
      echo '<div class="updated" style="margin-left: 0;"><p><b>'.$fname.'</b> has been uploaded. You can now use it in your layouts.</p></div>';
    }
    catch (Exception $e)
    {
      echo '<div class="error" style="margin-left: 0;"><p>'.$e->getMessage().'</p></div>';
    }
  }

  // Handle user input
  if ( isset($_POST["wpfx_submit"]) and $_POST["wpfx_submit"] )
  {
    echo "<div align = 'center'>";
    echo "<form method = 'POST' action = '$wpfx_url"."generate.php' target='_blank' id = 'dform' >";

    $filename = '';
    $filledfm = '';

    $layout   = wpfx_readlayout(intval($_POST['wpfx_layout']) - 9);
    global $currentLayout;
    $currentLayout = $layout;

    // Generate pdf
    switch($_POST['wpfx_layout'])
    {
    case 1:
      $filename = wpfx_download($wpfx_fdf->makeInflatablesApp(wpfx_extract(1, $_POST['wpfx_dataset']), FPROPDF_FORMS_DIR.'InflatableApp.pdf') );
      $filledfm = 'InflatableApp.pdf';
      break;

    case 2:
      $filename = wpfx_download($wpfx_fdf->makeBusinessQuote(wpfx_extract(2, $_POST['wpfx_dataset']), FPROPDF_FORMS_DIR.'BusinessQuote.pdf') );
      $filledfm = 'BusinessQuote.pdf';
      break;

    default:
      $unicode = isset($layout['lang']) && $layout['lang'] == '1' ? true : false;
      $pdf      = FPROPDF_FORMS_DIR.$layout['file'];
      $layout_id = intval($_POST['wpfx_layout']) - 9;
      $entry_id = intval($_POST['wpfx_dataset']);
      global $wpdb;
      $tmpFDF = $wpdb->get_row('SELECT * FROM ' . FPROPDF_WPFXTMP . ' WHERE layout_id = \'' . $layout_id . '\' AND `entry_id` = \'' . $entry_id . '\'', ARRAY_A);

      if ($tmpFDF && file_exists($tmpFDF['path'])) {
        $filename = $tmpFDF['path'];
      } else {
        $filename = wpfx_download($wpfx_fdf->makeFDF(wpfx_extract(3, $_POST['wpfx_dataset'], $layout['data']), $pdf, $unicode));
      }
      $filledfm = $layout['file'];
      break;
    }

    $filledfm = FPROPDF_FORMS_DIR.fpropdf_bash_replace($filledfm);

    echo "<input type = 'hidden' name = 'desired' value = \"". htmlspecialchars($filledfm) ."\" />";
    echo "<input type = 'hidden' name = 'actual'  value = '$filename' />";
    echo "<input type = 'hidden' name = 'lock' value = '".$layout['visible']."' />";
    echo "<input type = 'hidden' name = 'passwd' value = \"".htmlspecialchars($layout['passwd'])."\" />";
    echo "<input type = 'hidden' name = 'lang' value = '".esc_attr($layout['lang'])."' />";
    echo "<input type = 'hidden' name = 'filename' value = '".esc_attr($layout['name'])."' />";
    echo "<input type = 'hidden' name = 'default_format' value = '".esc_attr($layout['default_format'])."' />";
    echo "<input type = 'hidden' name = 'name_email' value = '".esc_attr($layout['name_email'])."' />";
    echo "<input type = 'hidden' name = 'restrict_user' value = '".esc_attr( $layout['restrict_user'] ? $layout['restrict_user'] : get_option('fpropdf_restrict_user') )."' />";
    echo "<input type = 'hidden' name = 'restrict_role' value = '".esc_attr( $layout['restrict_role'] ? $layout['restrict_role'] : get_option('fpropdf_restrict_role') )."' />";
    echo "<input type = 'submit' value = 'Download' name = 'download' id = 'hideme' />";
    echo "</form>";
    echo "</div>";
    //exit;

    unset ($_POST);

    if ( defined('FPROPDF_IS_GENERATING') )
      return;        

  } 
  else if( isset($_POST['wpfx_savecl']) and $_POST['wpfx_savecl'] ) // Save a custom layout here
  {
    $layout = array();

    $formats = array();

    foreach($_POST['clfrom'] as $index => $value)
    {
      $to = $_POST['clto'][$index];

      $_f = fpropdf_stripslashes( $_POST['format'][ $index ] );
      if ( in_array( $_f, array('curDate','date','number_f')))
      {
        $_f = $_POST['select_for_' . $_f][ $index ];
      }

      $formats[] = array(
                      $to, 
                      $_f, 
                      fpropdf_stripslashes( $_POST['repeatable_field'][ $index ] ), 
                      fpropdf_stripslashes( $_POST['checkbox_field'][ $index ] ), 
                      fpropdf_stripslashes( $_POST['image_field'][ $index ] ),
                      fpropdf_stripslashes( $_POST['address_field'][ $index ] ),
                      fpropdf_stripslashes( $_POST['credit_card_field'][ $index ] )
      );


      if( strlen(trim($value)) && strlen(trim($to)) )
        $layout[] = array( $value, $to );
    }

    // Get desired dataset name
    // "clname" can be anything and does not need to be filtered
    list(, $index) = explode("_", $_POST['clname']);

    $index = intval($index);

    $add_att = esc_sql( $_POST['wpfx_add_att'] );
    $default_format = esc_sql( $_POST['wpfx_default_format'] );
    $name_email = esc_sql( stripslashes( $_POST['wpfx_name_email'] ) );
    $restrict_user = esc_sql( $_POST['wpfx_restrict_user'] );
    $restrict_role = esc_sql( $_POST['wpfx_restrict_role'] );
    $add_att_ids = $_POST['wpfx_add_att_ids'];
    if ( is_array($add_att_ids) and count($add_att_ids) )
      $add_att_ids = implode(',', $add_att_ids);
    else
      $add_att_ids = '';
    $passwd = esc_sql( $_POST['wpfx_password'] );
    $lang = esc_sql( $_POST['wpfx_lang'] );

    if(isset ($_POST['update']) && ($_POST['update'] == 'update'))
      $r = wpfx_updatelayout(intval($_POST['wpfx_layout']) - 9, esc_sql( stripslashes( $_POST['wpfx_clname'] ) ), esc_sql(base64_decode(urldecode($_POST['wpfx_clfile']))), intval($_POST['wpfx_layout_visibility']), esc_sql( $_POST['wpfx_clform'] ), $index, $layout, $formats, $add_att, $passwd, $lang, $add_att_ids, $default_format, $name_email, $restrict_role, $restrict_user);
    else 
      $r = wpfx_writelayout( esc_sql( stripslashes( $_POST['wpfx_clname'] ) ), esc_sql(base64_decode(urldecode($_POST['wpfx_clfile']))), intval($_POST['wpfx_layout_visibility']), esc_sql( $_POST['wpfx_clform'] ), $index, $layout, $formats, $add_att, $passwd, $lang, $add_att_ids, $default_format, $name_email, $restrict_role, $restrict_user);

    global $wpdb;

    if ( $r )
      echo '<div class="updated" style="margin-left: 0;"><p>Layout has been saved!</p></div>';
    else 
      echo '<div class="error" style="margin-left: 0;"><p>Failed to save this custom layout :( <br />' . $wpdb->last_error . '</p></div>';

    echo '<script>window.location.href="?page=fpdf&wpfx_form=' . urlencode( $_POST['wpfx_clform'] ) . '&wpfx_layout=' . ( $r + 9 ) . '";</script>'; 
    exit;

  }

  if ( !is_plugin_active('formidable/formidable.php') && !is_plugin_active('formidable-2/formidable.php'))
  {
    echo '<div class="error" style="margin-left: 0;"><p>Formidable plugin not found</p></div>';
    echo '</div></div>';
    return;
  }

  $forms  = wpfx_getforms();

  $has_forms = false;
  foreach($forms as $key => $data)
  {
    if(($key == '9wfy4z') or ($key == '218da3') or (strtotime($data[1]) > strtotime("01 March 2013")))
    {
      $has_forms = true;
    }
  }
  if ( ! $has_forms )
  {
    echo '<div class="error" style="margin-left: 0;"><p>You have no Formidable Forms. Please <a href="admin.php?page=formidable&frm_action=new" class="button-primary">Add a Formidable Form</a></p></div>';
    echo '</div></div>';
    return;
  }


  $currentTab = $_GET['tab'];
  if ( ! in_array( $currentTab, array( 'templates', 'debug', 'general', 'forms', 'settings', 'backups') ) )
    $currentTab = "general";

  //if ( fpropdf_is_activated() and !defined('FPROPDF_IS_MASTER') )
  //{
    $tabs = array( 'general' => 'Export', 'forms' => 'Activated Forms', 'settings' => 'Settings', 'templates' => 'Templates', 'backups' => 'Backups', 'debug' => 'Under the Hood' );
    if (!fpropdf_check_user_role('administrator')) {
        unset($tabs['settings']);
    }
    echo '<div id="icon-themes" class="icon32"><br></div>';
    echo '<h2 class="nav-tab-wrapper">';
    foreach( $tabs as $tab => $name ){
      if ( ! ( fpropdf_is_activated() and !defined('FPROPDF_IS_MASTER') ) && ( $tab == 'forms' ) )
        continue;
      if ( $tab == 'forms' )
        if ( get_option('fpropdf_licence') == 'OFFLINE_SITE' ) 
          continue;
        
      $class = ( $tab == $currentTab ) ? ' nav-tab-active' : '';
      echo "<a class='nav-tab$class' href='?page=fpdf&tab=$tab'>$name</a>";

    }
    echo '</h2>';
  //}

  if ( $currentTab == 'debug' )
  {
    fpropdf_debug_page();
    return;
  }

  if ( $currentTab == 'settings' )
  {
    fpropdf_settings_page();
    return;
  }
  
  if ( $currentTab == 'backups' )
  {
    fpropdf_backups_page();
    return;
  }
  
  
  if ( $currentTab == 'templates' )
  {
    fpropdf_templates_page();
    return;
  }

  if ( $currentTab == 'forms' )
  {

    $code = get_option('fpropdf_licence');
    if ( $code and !fpropdf_is_trial() )
    {
      try
      {
        fpropdf_check_code( $code, 1 );
      }
      catch (Exception $e)
      {

      }
    }

    $this_site = new stdClass();
    $this_site->url = site_url('/');
    $this_site->site_salt = FPROPDF_SALT;
    $this_site->title = get_bloginfo('name');
    $this_site->not_active = true;
    $this_site->ip = $_SERVER['SERVER_ADDR'];

    if ( $_GET['action'] == 'site_activate' )
    {
      $curl = curl_init();
      curl_setopt_array($curl, array(
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_URL => FPROPDF_SERVER . 'licence/licence-change.php',
        CURLOPT_POST => 1,
        CURLOPT_HTTPHEADER => array('Expect:'),
        CURLOPT_POSTFIELDS => array(
          'salt'   => FPROPDF_SALT,
          'code'   => $code,
          'action' => 'activate_site',
          'title'  => $this_site->title,
          'url'    => $this_site->url,
          'site_url' => site_url('/')
        )
      ));
      $result = curl_exec($curl);
      $result = json_decode($result);
      if ( $result->success )
        echo "<div class='updated'><p>The site has been activated.</p></div>";
      elseif ( $result->error )
        echo "<div class='error'><p>".$result->error."</p></div>";
      else
        echo "<div class='error'><p>Unknown error. Please try again later.</p></div>";
    }

    if ( $_GET['action'] == 'form_activate' )
    {
      $curl = curl_init();
      curl_setopt_array($curl, array(
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_URL => FPROPDF_SERVER . 'licence/licence-change.php',
        CURLOPT_POST => 1,
        CURLOPT_HTTPHEADER => array('Expect:'),
        CURLOPT_POSTFIELDS => array(
          'salt'   => FPROPDF_SALT,
          'code'   => $code,
          'action' => 'activate_form',
          'site_id'    => $_GET['site'], // No need to filter this
          'form_id'    => $_GET['form'], // No need to filter this
          'title'      => $_GET['title'], // No need to filter this
          'site_url' => site_url('/')
        )
      ));
      $result = curl_exec($curl);
      $result = json_decode($result);
      //print_r($result);
      if ( $result->success )
        echo "<div class='updated'><p>The form has been activated.</p></div>";
      elseif ( $result->error )
        echo "<div class='error'><p>".$result->error."</p></div>";
      else
        echo "<div class='error'><p>Unknown error. Please try again later.</p></div>";
    }

    if ( $_GET['action'] == 'form_deactivate' )
    {
      $curl = curl_init();
      curl_setopt_array($curl, array(
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_URL => FPROPDF_SERVER . 'licence/licence-change.php',
        CURLOPT_POST => 1,
        CURLOPT_HTTPHEADER => array('Expect:'),
        CURLOPT_POSTFIELDS => array(
          'salt'   => FPROPDF_SALT,
          'code'   => $code,
          'action' => 'deactivate_form',
          'site_id'    => $_GET['site'], // No need to filter this
          'form_id'    => $_GET['form'], // No need to filter this
          'site_url' => site_url('/')
        )
      ));
      $result = curl_exec($curl);
      //print_r($result);
      $result = json_decode($result);
      if ( $result->success )
        echo "<div class='updated'><p>The form has been deactivated.</p></div>";
      elseif ( $result->error )
        echo "<div class='error'><p>".$result->error."</p></div>";
      else
        echo "<div class='error'><p>Unknown error. Please try again later.</p></div>";
    }

    if ( $_GET['action'] == 'site_deactivate' )
    {
      $curl = curl_init();
      curl_setopt_array($curl, array(
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_URL => FPROPDF_SERVER . 'licence/licence-change.php',
        CURLOPT_POST => 1,
        CURLOPT_HTTPHEADER => array('Expect:'),
        CURLOPT_POSTFIELDS => array(
          'salt'   => FPROPDF_SALT,
          'code'   => $code,
          'action' => 'deactivate_site',
          'site_id'    => $_GET['site'], // No need to filter this
          'site_url' => site_url('/')
        )
      ));
      $result = curl_exec($curl);
      //print_r($result);
      $result = json_decode($result);
      if ( $result->success )
        echo "<div class='updated'><p>The site has been deactivated.</p></div>";
      elseif ( $result->error )
        echo "<div class='error'><p>".$result->error."</p></div>";
      else
        echo "<div class='error'><p>Unknown error. Please try again later.</p></div>";
    }


    $curl = curl_init();
    curl_setopt_array($curl, array(
      CURLOPT_RETURNTRANSFER => 1,
      CURLOPT_URL => FPROPDF_SERVER . 'licence/info.php',
      CURLOPT_POST => 1,
      CURLOPT_HTTPHEADER => array('Expect:'),
      CURLOPT_POSTFIELDS => array(
        'salt'   => FPROPDF_SALT,
        'code'   => $code,
      )
    ));
    $result = curl_exec($curl);
    $result = json_decode($result);

    // Output activated forms

    $found = false;
    foreach ( $result->sites as $site )
      if ( $site->site_salt == $this_site->site_salt  && $this_site->url==$site->url)
        $found = true;
    if ( ! $found )
      array_unshift( $result->sites, $this_site );

    $this_forms = array();
    foreach ( $forms as $key => $form )
    {
      if(($key == '9wfy4z') or ($key == '218da3') or (strtotime($form[1]) > strtotime("01 March 2013")))
      {
        $this_form = new stdClass();
        $this_form->form_id = $key;
        $this_form->not_active = 1;
        $this_form->title = $form[0];
        $this_forms[] = $this_form;
      }
    }

    foreach ( $result->sites as $site )
      if ( $site->site_salt == $this_site->site_salt  && $this_site->url==$site->url)
      {
        foreach ( $this_forms as $form )
        {
          $found = false;
          foreach ( $site->forms as $site_form )
            if ( $site_form->form_id == $form->form_id )
            {
              $found = true;
              $site_form->title = $form->title;
            }
          if ( ! $found )
            $site->forms[] = $form;
        }
      }

	$number_of_sites = count($result->sites);
	$manage_activation='';
	if($number_of_sites>1)
	$manage_activation = "<a href='http://www.formidablepro2pdf.com/my-account/' target='_blank'>Click to manage your activation key</a>";

    if ( fpropdf_is_trial() )
      echo "<div class='updated'><p>You can activate only 1 form on this website. Please <a href='#' class='button-primary fpropdf-activate'>upgrade</a> if you want to use more forms.</p></div>";
    else
      echo "<div class='updated'><p>Your licence key is <strong>".$code."</strong>. <br /> It is valid until ".date('m/d/Y', strtotime( $result->licence->expires_on ))." <br />With this activation code, you can register up to <strong>".$result->licence->sites."</strong> site".($result->licence->sites == 1 ? '' : 's')." and up to <strong>".$result->licence->forms."</strong> form".($result->licence->forms == 1 ? '' : 's').". <a href='?page=fpdf&action=deactivatekey'>Click here to deactivate this key.</a> </p><p>You have <strong>".$result->sites_left."</strong> site".($result->licence->sites_left == 1 ? '' : 's')." and <strong>".$result->forms_left."</strong> form".($result->licence->forms_left == 1 ? '' : 's')." left.$manage_activation</p></div>";

    echo '<ol class="fpropdf-sites">';
    if ( ! count($result->sites) )
    {
      echo '<li><i>you do not have any active sites</i></li>';
    }
    else
    {
      foreach ( $result->sites as $site )
      {
		if( $this_site->url==$site->url){
        echo '<li class="opt-'.($site->not_active ? 'inactive' : 'active').'">';
        echo $site->url.' ('.$site->title.')';

        if ( $site->not_active )
        {

          echo ' - not active. <a class="" href="?page=fpdf&tab=forms&action=site_activate" style="opacity: 1;">Activate this website</a>';
          echo '</li>';
          continue;
        }

        echo ' - active. <a class="" href="?page=fpdf&action=site_deactivate&tab=forms&site='.$site->site_id.'">Deactivate this website</a>';
        if ( !count( $site->forms ) )
          echo '<ul><li><i>no activated forms</i></li></ul>';
        else
        {
          echo '<ul>';
          foreach ( $site->forms as $form )
          {
            echo '<li class="opt-'.($form->not_active ? 'inactive' : 'active').'">';
            echo $form->title;
            if ( $form->not_active )
              echo ' - not active. <a class="" href="?page=fpdf&action=form_activate&tab=forms&site='.urlencode($site->site_id).'&form='.urlencode($form->form_id).'&title='.urlencode($form->title).'">Activate</a>';
            else
              echo ' - active. <a class="" href="?page=fpdf&action=form_deactivate&tab=forms&site='.urlencode($site->site_id).'&form='.urlencode($form->form_id).'">Deactivate</a>';
            echo '</li>';
          }
          echo '</ul>';
        }
        echo '</li>';
		
		
	  }
      }
    }

    echo '</ol>';

    echo '</div>';
    return;
  }

  if ( function_exists('add_thickbox') )
    add_thickbox();

  echo "<form method = 'POST' id='frm-bg' data-limitdropdowns='" . intval( get_option('fpropdf_limit_dropdowns') ) . "' data-automap='" . intval( get_option('fpropdf_automap') ) . "' data-security='".intval(fpropdf_enable_security())."' data-activated='".intval(!fpropdf_is_trial())."' data-pdfaid='".( get_option('fpropdf_pdfaid_api_key') ? '1' : '0' )."'>";
  echo "<table>";
  echo "<tr>";
  echo "<td width='300'>Select the form to export data from:</td>";
  echo "<td colspan = '2'><select id = 'wpfx_form' name = 'wpfx_form'>";

  $actual = array();

  // hardcode inflatable apps, business quote and new forms
  foreach($forms as $key => $data)
  {
    if(($key == '9wfy4z') or ($key == '218da3') or (strtotime($data[1]) > strtotime("01 March 2013")))
    {
      $allowed = array();
      global $wpdb;
      $rows = $wpdb->get_results('SELECT * FROM ' . $wpdb->prefix . 'fpropdf_layouts WHERE `form` = ' . intval( $data[2] ) );
      foreach ( $rows as $row )
        $allowed[] = $row->ID;
      echo "<option value = '$key' " . ( $_GET['wpfx_form'] == $key ? ' selected="selected"' : '') . " data-allowedlayouts='" . esc_attr(json_encode($allowed)) . "'>".$data[0]."</option>";
      $actual[ $key ] = $data;
    }
  }

  echo "</select> &nbsp; ";
  echo "<a class='button' target = 'blank' href = 'admin-ajax.php?action=frm_forms_preview' id = 'wpfx_preview'>Preview</a></td></tr>";

  echo "<tr><td>Select the dataset to export:</td>";
  echo "<td><select id = 'wpfx_dataset' name = 'wpfx_dataset'>";

  // Datasets will be filled by AJAX

  echo "</select></td>";
  echo "<td></td></tr>";

  // Manage layouts
  echo "<tr><td>Field Map to use:</td>";
  echo "<td colspan = '2'><select id = 'wpfx_layout' name = 'wpfx_layout'>";
  echo "<option value = '3'>New Field Map</option>";

  // Populate with custom saved layouts
  foreach(wpfx_getlayouts() as $key => $name)
    echo "<option value = '$key'>$name</option>";

  echo "</select></td></tr>";

  if ( $_GET['wpfx_layout'] )
    echo '<script>window.currentSelectedLayout = "' . $_GET['wpfx_layout'] . '";</script>';

  if ( fpropdf_is_activated() and !fpropdf_is_trial() )
  {
    echo "<tr><td></td><td> <label> <input type='checkbox' id='use-second-layout' /> Add a second dataset</label> </td></tr>";


    echo "<tr class='hidden-use-second'>";
    echo "<td>Select the second form to export data from:</td>";
    echo "<td colspan = '2'><select id = 'wpfx_form2' name = 'wpfx_form2'>";

    $actual = array();

    foreach($forms as $key => $data)
    {
      if(($key == '9wfy4z') or ($key == '218da3') or (strtotime($data[1]) > strtotime("01 March 2013")))
      {
        $allowed = array();
        global $wpdb;
        $rows = $wpdb->get_results('SELECT * FROM ' . $wpdb->prefix . 'fpropdf_layouts WHERE `form` = ' . intval( $data[2] ) );
        foreach ( $rows as $row )
          $allowed[] = $row->ID;
        echo "<option value = '$key' data-allowedlayouts='" . esc_attr(json_encode($allowed)) . "'>".$data[0]."</option>";
        $actual[ $key ] = $data;
      }
    }

    echo "</select> &nbsp; ";
    echo "<a class='button' target = 'blank' href = 'admin-ajax.php?action=frm_forms_preview' id = 'wpfx_preview2'>Preview</a></td></tr>";

    echo "<tr class='hidden-use-second'><td>Select the second dataset to export:</td>";
    echo "<td><select id = 'wpfx_dataset2' name = 'wpfx_dataset2'>";

    // Will be filled by AJAX

    echo "</select></td>";
    echo "<td></td></tr>";

    // Manage layouts
    echo "<tr class='hidden-use-second'><td>Second Field Map to use:</td>";
    echo "<td colspan = '2'><select id = 'wpfx_layout2' name = 'wpfx_layout2'>";

    // Populate with custom saved layouts
    foreach(wpfx_getlayouts() as $key => $name)
      echo "<option value = '$key'>$name</option>";

    echo "</select></td></tr>";


  }

  echo "<tr id='tr-export' style='display: none;'><td colspan = '3' align = 'center'><hr /><a href='#' target='_blank' id='main-export-btn' class='button-primary'>Export to PDF</a>";
  if ( get_option('fpropdf_pdfaid_api_key') )
    echo "&nbsp; <a href='#' target='_blank' id='main-export-btn-docx' class='button-primary'>Export to DOCX</a>";
  echo "</td></tr>";
  echo "</table>";
  echo "</form>";

  echo "</div>";
  echo "<div class = '_second _left'><div id = 'loader'><img src = '".$wpfx_url."res/loader.gif' /> Loading layout... Please wait...</div><div class = 'layout_builder' style='width: auto;'><h2>Field Map Designer</h2>";
  echo "<form method = 'POST' id = 'wpfx_layout_form'  enctype='multipart/form-data'>";

  echo "<table>";
  echo "<tr><td>Name of Field Map (will be used as default filename):</td><td><input required='required' name = 'wpfx_clname' id = 'wpfx_clname' /></td></tr>";
  echo "<tr><td>Select PDF file to work with:</td><td><select name = 'wpfx_clfile' id = 'wpfx_clfile'>";

  // Print existing PDF files
  if ($handle = opendir( FPROPDF_FORMS_DIR ))
  {
    $files = array();
    while (false !== ($file = readdir($handle)))
    {
      if ($file != "." && $file != ".." && strtolower(substr($file, strrpos($file, '.') + 1)) == 'pdf')
      {
        $files[] = $file;
      }
    }
    natcasesort($files);
    foreach ($files as $file) {
      echo "<option value = '".base64_encode($file)."' >$file</option>";
    }
    closedir($handle);
  } 
  else 
    echo "<option>Error: can not list directory</option>";

  echo "</select></td></tr>
    <tr><td></td><td>
    <a href='#' class='upl-new-pdf button-primary' style='margin: 1px;'>Upload a PDF file</a>
    <input type='button' class='remove-pdf button' style='margin: 1px;' value='Remove this PDF file' />

    </td></tr>";

  echo "<tr><td>Select Form to work with:</td><td><select name = 'wpfx_clform' id = 'wpfx_clform'>";

  $forms  = wpfx_getforms();
  $actual = array();

  foreach($forms as $key => $data)
  {
    if(($key == '9wfy4z') or ($key == '218da3') or (strtotime($data[1]) > strtotime("01 March 2013")))
    {
      echo "<option value = '$key'>".$data[0]."</option>";
      $actual[ $key ] = $data;
    }
  }

  echo "<tr><td>Flatten PDF form</td>";
  if ( fpropdf_is_activated() and !fpropdf_is_trial() )
    echo "<td><select id = 'wpfx_layoutvis'><option value = '1'>Yes</option><option value='2'>Yes, and transform text into images</option><option value = '0'>No</option></select><div id='wpfx_layoutvis_options' style='display: none;'>&nbsp;<b>Warning:</b> PDF file size will be about 1 MB per page.</div></td></tr>";
  else
    echo "<td><select id = 'wpfx_layoutvis' disabled='disabled'><option value = '0'>No</option></select></td></tr>";

  echo "<tr><td valign='top' style='padding-top: 6px;'>Attach file to Email notifications</td>";
  if ( fpropdf_is_activated() and !fpropdf_is_trial() )
  {
    echo "<td>
      <select id = 'wpfx_add_att' name='wpfx_add_att'><option value = '1'>Yes</option><option value = '0'>No</option></select>

<div id='wpfx_frm_actions'><input type='hidden' name='wpfx_add_att_ids[]' value='all' /></div>
<div id='wpfx_frm_actions2' style='display: none;'>
<br/>PDF file name in e-mails:<br />
<input name='wpfx_name_email' id='wpfx_name_email' />
</div>
";
    echo "</td></tr>";
    echo "<tr><td valign='top' style='padding-top: 6px;'>Language support:</td>";
    echo "<td><select id = 'wpfx_lang' name='wpfx_lang'><option value = '0'>Default</option><option value='1'>Unicode</option></select><div id='wpfx_lang' style='display: none;'>&nbsp;<b>Warning:</b> PDF font in forms will be replaced.</div>";
    echo "</td></tr>";

  }
  else {
    echo "<td><select id = 'wpfx_add_att' name='wpfx_add_att' disabled='disabled'><option value = '0'>No</option></select></td></tr>";
    echo "<tr><td valign='top' style='padding-top: 6px;'>Language support:</td>";
    echo "<td><select id = 'wpfx_lang' name='wpfx_lang' disabled='disabled'><option value = '0'>Default</option></select><div id='wpfx_lang' style='display: none;'>&nbsp;<b>Warning:</b> PDF font in forms will be replaced.</div>";
    echo "</td></tr>";
  }

  
  echo "<tr><td>PDF password <i>(leave empty if password shouldn't be set)</i>:</td><td><input name = 'wpfx_password' id = 'wpfx_password' /></td></tr>";
  

  echo "<tr><td>Allow downloads only for roles:</td><td><input name = 'wpfx_restrict_role' id = 'wpfx_restrict_role' placeholder='all' /></td></tr>";
  echo "<tr><td>Allow downloads only for user IDs:</td><td><input name = 'wpfx_restrict_user' id = 'wpfx_restrict_user' placeholder='all' /></td></tr>";
  
  if ( get_option('fpropdf_pdfaid_api_key') )
  {
   echo "<tr><td>Default file format</td><td><select id = 'wpfx_default_format' name='wpfx_default_format'><option value = 'pdf'>PDF</option><option value = 'docx'>DOCX</option></select></td></tr>";
  }
  else
    echo "<input type='hidden' name='wpfx_default_format' id='wpfx_default_format' value='' />";

  // now create dynamic list
  echo "<tr><td colspan = '2'><table class = 'cltable'>";
  echo "<thead><tr>";
  echo "<th>Use as <br />Dataset<br />Name?</th><th>Webform Data Field ID</th><th>Maps<br />to...</th><th>PDF Form Field Name</th>";
  if ( fpropdf_is_activated() and !fpropdf_is_trial() )
    echo "<th>Format</th>";
  echo "<th>&nbsp;</th>";
  echo "</thead></tr><tbody id='clbody' data-activated='".intval(!fpropdf_is_trial())."'>";

  // table body will be populated by AJAX

  echo "</tbody></table>";
  echo "<br />";
  echo "</td></tr><tr><td colspan = '2'><table  width = '100%'><tr>";

  // Control buttons here
  echo "<td align = 'left'><input type = 'button' id = 'clnewmap' value = 'Map Another Field' class='button' />";
  echo "<input type = 'reset' value = 'Reset' class='button' /></td>";
  echo "<td align = 'center'><input type = 'submit' value = 'Save Field Map' class='button-primary' name = 'wpfx_savecl' id = 'savecl'/></td>";
  echo "<td align = 'right'>
    <input type = 'button' value = 'Duplicate this Field Map' class='button' id = 'dupcl'/>
    <input type = 'button' value = 'Delete Entire Field Map' class='button' id = 'remvcl'/>
  </td></tr></table>";
  echo "</td></tr></table></form>";
  echo "</div></div>";
  echo "</div>";
}

// Get all Formidable forms available
function wpfx_getforms($show_id = false)
{
  global $wpdb;

  $query = "SELECT `id`, `form_key`, `name`, `created_at` FROM `".$wpdb->prefix."frm_forms` WHERE `status` = 'published'  AND ( `parent_form_id` = 0 OR `parent_form_id` IS NULL ) ORDER BY UNIX_TIMESTAMP(`created_at`) DESC";
  $array = array();

  $result = $wpdb->get_results( $query, ARRAY_A );

  foreach ( $result as $row )
  {
    $array[ ($show_id ? $row['id'] : $row['form_key'] ) ] = array( stripslashes($row['name']), $row['created_at'], $row['id'] );
  }

  return $array;
}

// Get all custom created layouts
function wpfx_getlayouts()
{
  global $wpdb;

  $array  = array();
  $query  = "SELECT `ID`, `name` FROM `" . FPROPDF_WPFXLAYOUTS . "` WHERE 1 ORDER BY `created_at` DESC";
  $result = $wpdb->get_results( $query, ARRAY_A );

  foreach ( $result as $row )
    $array[ $row['ID'] + 9 ] = stripslashes($row['name']); // adding 9 not to mess up with our hardcoded layouts

  return $array;
}

function wpfx_readlayout($id)
{
  global $wpdb;

  $query  = "SELECT w.*, f.`form_key` as `form` FROM `" . FPROPDF_WPFXLAYOUTS . "` w, `".$wpdb->prefix."frm_forms` f WHERE w.`ID` = $id AND f.`id` = w.`form`";
  
  $result = $wpdb->get_row( $query, ARRAY_A );
  if ( ! $result )
    $result = array();

  $formats = @unserialize($result['formats']);
  if ( ! is_array($formats) )
    $formats = array();

  $data = isset($result['data']) ? unserialize($result['data']) : array();

  $vals = array_values( $data );
  if ( count($vals) )
  if ( !is_array($vals[0]) )
  {
    $_data = array();
    foreach ( $data as $k => $v )
    {
      $_data[] = array( $k, $v );
    }
      
    $data = $_data;
  }
  
  
  if ( is_array( $data ) )
    foreach ( $data as $index => $array )
      if ( is_array( $array ) and $array[0] )
      {
        if ( fpropdf_use_field_keys() )
          $data[ $index ][ 0 ] = fpropdf_field_id_to_key( $data[ $index ][ 0 ] );
        else
          $data[ $index ][ 0 ] = fpropdf_field_key_to_id( $data[ $index ][ 0 ] );
      }

  $vals = array_values( $formats );
  if ( count($vals) )
  if ( !is_array($vals[0]) )
  {
    $_data = array();
    foreach ( $formats as $k => $v )
      $_data[] = array( $k, $v );
    $formats = $_data;
  }
  
  if ( !isset($result['default_format']) || ! $result['default_format'] )
    $result['default_format'] = 'pdf';

  return array(
    'name' => isset($result['name']) ? $result['name'] : '', 
    'passwd' => isset($result['passwd']) ? stripslashes($result['passwd']) : '',
     'lang' => isset($result['lang']) ? $result['lang'] : '', 
    'file' => isset($result['file']) ? $result['file'] : '', 
    'visible' => isset($result['visible']) ? $result['visible'] : '', 
    'form' => isset($result['form']) ? $result['form'] : '', 
    'index' => isset($result['dname']) ? $result['dname'] : '', 
    'add_att' => isset($result['add_att']) ? $result['add_att'] : '', 
    'add_att_ids' => isset($result['add_att_ids']) ? $result['add_att_ids'] : '', 
    'default_format' => isset($result['default_format']) ? $result['default_format'] : '',
    'name_email' => isset($result['name_email']) ? $result['name_email'] : '',
    'restrict_role' => isset($result['restrict_role']) ? $result['restrict_role'] : '',
    'restrict_user' => isset($result['restrict_user']) ? $result['restrict_user'] : '',
    'data' => $data, 
    'formats' => $formats
  );
}


function wpfx_writelayout($name, $file, $visible, $form, $index, $data, $formats, $add_att, $passwd, $lang, $add_att_ids, $default_format, $name_email, $restrict_role, $restrict_user)
{
  global $wpdb;

  $data = $wpdb->prepare( '%s', serialize($data) );
  $formats = $wpdb->prepare( '%s', serialize($formats) );

  $query = "SELECT `id` FROM `".$wpdb->prefix."frm_forms` WHERE `form_key` = '$form'";

  $form = $wpdb->get_row( $query, ARRAY_A );

  if ($form && isset($form['id'])) {
  $form = $form['id'];
  } else {
      $form = 0;
  }

  $wpdb->query("ALTER TABLE " . FPROPDF_WPFXLAYOUTS . " ADD COLUMN formats LONGTEXT CHARACTER SET utf8");
  $wpdb->query("ALTER TABLE " . FPROPDF_WPFXLAYOUTS . " MODIFY COLUMN formats LONGTEXT CHARACTER SET utf8");
  $wpdb->query("ALTER TABLE " . FPROPDF_WPFXLAYOUTS . " MODIFY COLUMN data LONGTEXT");
  $wpdb->query("ALTER TABLE " . FPROPDF_WPFXLAYOUTS . " ADD COLUMN passwd VARCHAR(255)");
  $wpdb->query("ALTER TABLE " . FPROPDF_WPFXLAYOUTS . " ADD COLUMN lang INT(3) UNSIGNED NOT NULL DEFAULT '0'");
  $wpdb->query("ALTER TABLE " . FPROPDF_WPFXLAYOUTS . " ADD COLUMN default_format VARCHAR(255) NOT NULL DEFAULT 'pdf'");
  $wpdb->query("ALTER TABLE " . FPROPDF_WPFXLAYOUTS . " ADD COLUMN add_att_ids VARCHAR(255) NOT NULL DEFAULT 'all'");
  $wpdb->query("ALTER TABLE " . FPROPDF_WPFXLAYOUTS . " ADD COLUMN add_att INT(3) UNSIGNED NOT NULL DEFAULT '0'");
  $wpdb->query("ALTER TABLE " . FPROPDF_WPFXLAYOUTS . " ADD COLUMN name_email VARCHAR(255)");
  $wpdb->query("ALTER TABLE " . FPROPDF_WPFXLAYOUTS . " ADD COLUMN restrict_user TEXT CHARACTER SET utf8");
  $wpdb->query("ALTER TABLE " . FPROPDF_WPFXLAYOUTS . " ADD COLUMN restrict_role TEXT CHARACTER SET utf8");

  $query = "INSERT INTO `" . FPROPDF_WPFXLAYOUTS . "` (`name`, `file`, `visible`, `form`, `data`, `dname`, `created_at`, `formats`, `add_att`, `passwd`, `lang`, `add_att_ids`, `default_format`, `name_email`, `restrict_role`, `restrict_user`)
    VALUES ('$name', '$file', $visible, $form, $data, $index, NOW(), $formats, '$add_att', '$passwd', '$lang', '$add_att_ids', '$default_format', '$name_email', '$restrict_role', '$restrict_user')";

  $_SESSION['new_layout'] = 1;

  $res = $wpdb->query($query);
  
  if ( $error = $wpdb->last_error )
  {
    die("<div class='error' style='margin-left: 0;'><p>Error while saving layout: $error</p><p>Query was: $query</p></div>");
  }
  
  $id = $wpdb->insert_id;
  wpfx_backup_layout($id);
  
  if ( $id )
    return $id;
  
  return $res;
}

@session_start();

function wpfx_backup_layout($id, $with_pdf=false)
{
  
  global $wpdb;
  
  $folder = FPROPDF_BACKUPS_DIR;
  $query = "SELECT * FROM " . FPROPDF_WPFXLAYOUTS . " WHERE id = $id";
    
  $data = $wpdb->get_row( $query, ARRAY_A );
  
  if ( ! $data )
    return;
  
  $assocData = @unserialize( $data['data'] );
  if ( $assocData )
  {
    foreach ( $assocData as $index => $v )
      $assocData[ $index ][ 0 ] = fpropdf_field_id_to_key( $v[ 0 ] );
    $data['data'] = serialize( $assocData );
  }
    
  $formid = $data['form'];
  $query2 = ("SELECT * FROM `".$wpdb->prefix."frm_forms` WHERE `id` = '$formid'");
  
  $formdata = $wpdb->get_row( $query2, ARRAY_A );
  if ( ! $formdata )
    $formdata = array();
  
  $filedata = array(
    'ts' => time(),
    'data' => $data,
    'salt' => FPROPDF_SALT,
    'form' => $formdata,
  );
  
  if ( $with_pdf )
  {
    
    // error_reporting(E_ALL);
    // ini_set('display_errors', 'on');
    
    $pdf = base64_encode( file_get_contents( FPROPDF_FORMS_DIR . $data['file'] ) );
    $filedata['pdf'] = $pdf;
    
    ob_start();
    FrmXMLController::generate_xml( 
      array( 'forms' ),
      array( 'ids' => $data['form'] )
    );
    $xml = ob_get_clean();
    $filedata['xml'] = base64_encode($xml); //
    
    header_remove( 'Content-Description' );
    header_remove( 'Content-Disposition' );
    header_remove( 'Content-Type' );
    header_remove( 'Content-Encoding' );
    header_remove( 'Content-Length' );
    header_remove( 'Content-Type' );
    header_remove();
    
    header( 'Content-Type: text/html; charset=' . get_bloginfo('charset') );
    
    // print_r( $filedata ); exit;
  }
  
  $name = preg_replace('/[^a-zA-Z0-9]+/', '_', $data['name']);
  $name = preg_replace('/\_+/', '_', $name);
  $filename = $folder . time() . "_" . $name . '_' . $id . ".json";
  
  @file_put_contents( $filename, json_encode( $filedata ) );
  
  return $filename;
  
}

function wpfx_updatelayout($id, $name, $file, $visible, $form, $index, $data, $formats, $add_att, $passwd, $lang, $add_att_ids, $default_format, $name_email, $restrict_role, $restrict_user)
{
  global $wpdb;

  $data = $wpdb->prepare( '%s', serialize($data ) );
  $formats = $wpdb->prepare( '%s', serialize($formats) );

  $wpdb->query("ALTER TABLE " . FPROPDF_WPFXLAYOUTS . " ADD COLUMN formats LONGTEXT CHARACTER SET utf8");
  $wpdb->query("ALTER TABLE " . FPROPDF_WPFXLAYOUTS . " MODIFY COLUMN formats LONGTEXT CHARACTER SET utf8");
  $wpdb->query("ALTER TABLE " . FPROPDF_WPFXLAYOUTS . " MODIFY COLUMN data LONGTEXT");
  $wpdb->query("ALTER TABLE " . FPROPDF_WPFXLAYOUTS . " ADD COLUMN passwd VARCHAR(255)");
    $wpdb->query("ALTER TABLE " . FPROPDF_WPFXLAYOUTS . " ADD COLUMN lang INT(3) UNSIGNED NOT NULL DEFAULT '0'");
  $wpdb->query("ALTER TABLE " . FPROPDF_WPFXLAYOUTS . " ADD COLUMN default_format VARCHAR(255) NOT NULL DEFAULT 'pdf'");
  $wpdb->query("ALTER TABLE " . FPROPDF_WPFXLAYOUTS . " ADD COLUMN add_att INT(3) UNSIGNED NOT NULL DEFAULT '0'");
  $wpdb->query("ALTER TABLE " . FPROPDF_WPFXLAYOUTS . " ADD COLUMN add_att_ids VARCHAR(255) NOT NULL DEFAULT 'all'");
  $wpdb->query("ALTER TABLE " . FPROPDF_WPFXLAYOUTS . " ADD COLUMN name_email VARCHAR(255)");
  $wpdb->query("ALTER TABLE " . FPROPDF_WPFXLAYOUTS . " ADD COLUMN restrict_user TEXT CHARACTER SET utf8");
  $wpdb->query("ALTER TABLE " . FPROPDF_WPFXLAYOUTS . " ADD COLUMN restrict_role TEXT CHARACTER SET utf8");

  $query = "UPDATE `" . FPROPDF_WPFXLAYOUTS . "` SET `name` = '$name',
    `file` = '$file', `data` = $data, `visible` = $visible,
    `form` = (SELECT `id` FROM `".$wpdb->prefix."frm_forms` WHERE `form_key` = '$form'),
             `dname` = $index,
             `formats` = $formats,
             `add_att` = '$add_att',
             `add_att_ids` = '$add_att_ids',
             `passwd` = '$passwd',
             `lang` = '$lang',
             `name_email` = '$name_email',
             `restrict_role` = '$restrict_role',
             `restrict_user` = '$restrict_user',
             `default_format` = '$default_format',
             `created_at` = NOW() WHERE `ID` = $id";
  

  wpfx_backup_layout($id);

  $res = $wpdb->query($query);
  
  if ( $error = $wpdb->last_error )
  {
    die("<div class='error' style='margin-left: 0;'><p>Error while saving layout: $error</p><p>Query was: $query</p></div>");
  }
  
  if ( $res )
    return $id;
  
  return $res;
}


// Get all datasets for specified form
function wpfx_getdataset()
{
  global $wpdb;

  // Form key can be any string
  $key   = esc_sql( $_POST['wpfx_form_key'] );

  $array = array();

  $query = "SELECT  `id` FROM  `".$wpdb->prefix."frm_forms` WHERE  `form_key` =  '$key'";
  $fid   = $wpdb->get_row( $query, ARRAY_A );
  if ( ! $fid ) $fid = array();
  $fid   = $fid['id'];


  $query = "SELECT `id`, `name`, `item_key`, `created_at`, `updated_at`, `user_id` FROM  `".$wpdb->prefix."frm_items`
    WHERE  `form_id` = $fid ORDER BY UNIX_TIMESTAMP(`created_at`) DESC";

  $results = $wpdb->get_results($query, ARRAY_A);

  $fields = FrmField::get_all_for_form( $fid, '', 'include' );

  if ( !$results || ( count($results) == 0 ) ) {
    $array = array(
      array(
        'id' => -3,
        'date' => 'You MUST enter form data before creating merge!'
      )
    );
    echo json_encode($array);
    die();
  }

  $query  = "SELECT `data`, `dname` FROM `" . FPROPDF_WPFXLAYOUTS . "` WHERE `form` = $fid";
  $layouts = $wpdb->get_results( $query, ARRAY_A );
  if ( ! $layouts )
    $layouts = array();

  foreach ($layouts as &$layout) {
    $layout['count'] = 0;
    $layout['found'] = false;
    
    foreach ( unserialize($layout['data']) as $values )
    {
      if ( $layout['count'] == $layout['dname'] )
      {
        $layout['count'] = fpropdf_field_key_to_id( $values[0] );
        $layout['found'] = true;
        break;
      }
      $layout['count']++;
    }
  }

  foreach ( $results as $row )
  {
    $name = '';
    if ( !count( $layouts ) )
    {
      $array[] = array('id' => $row['id'],
        'date' => "Add matching layout first — ".date("m-d-Y", strtotime($row['created_at'])));
      continue;
    }
      
    $entry = FrmEntry::getOne($row['id'], true);

    foreach ( $layouts as $layout )
    {
      if ( !$layout['found'] )
      {
        $name = "[empty]";
        continue;
      }
      
      $description = @unserialize( $entry->description );
      if ( !$description )
        $description = array();
      
      $referrer = '';
      if( @preg_match('/Referer +\d+\:[ \t]+([^\n\t]+)/', $description['referrer'], $m) )
        $referrer = $m[1];
      else
        $referrer = $description['referrer'];
      
      if ( $layout['count'] == 'FPROPDF_ITEM_KEY' )
      {
        $name = $row['item_key'];
        continue;
      }
      if ( $layout['count'] == 'FPROPDF_BROWSER' )
      {
        $name = $description['browser'];
        continue;
      }
      if ( $layout['count'] == 'FPROPDF_IP' )
      {
        $name = $entry->ip;
        continue;
      }
      if ( $layout['count'] == 'FPROPDF_CREATED_AT' )
      {
        $name = get_date_from_gmt( $row['created_at'], 'Y-m-d H:i:s' );
        continue;
      }
      if ( $layout['count'] == 'FPROPDF_UPDATED_AT' )
      {
        $name = get_date_from_gmt( $row['updated_at'], 'Y-m-d H:i:s' );
        continue;
      }
      if ( $layout['count'] == 'FPROPDF_REFERRER' )
      {
        $name = $referrer;
        continue;
      }
      if ( $layout['count'] == 'FPROPDF_USER_ID' )
      {
        $name = $row['user_id'];
        continue;
      }
      if ( $layout['count'] == 'FPROPDF_DATASET_ID' )
      {
        $name = $row['id'];
        continue;
      }
      
      $found2 = false;
      foreach ( $fields as $field )
      {
        if ( $field->id != $layout['count'] ) continue;
        $embedded_field_id = ( $entry->form_id != $field->form_id ) ? 'form' . $field->form_id : 0;
        $atts = array(
        'type' => $field->type, 'post_id' => $entry->post_id,
        'show_filename' => true, 'show_icon' => true, 'entry_id' => $entry->id,
        'embedded_field_id' => $embedded_field_id,
        );
        $name = FrmEntriesHelper::prepare_display_value($entry, $field, $atts);
      
        if ( $name )
        $found2 = true;
        break;
      }
      
      if ( $found2 ) continue;
      
      
      $query = "SELECT `meta_value` as value FROM `".$wpdb->prefix."frm_item_metas` WHERE `item_id` = ".$row['id']." AND `field_id` = ".$layout['count'];
      $_name  = $wpdb->get_row( $query, ARRAY_A);
      if ( $_name )
      {
        $name  = stripslashes($_name['value']);
        break;
      }

      if ( ! $name )
        $name = "[empty]";
    } 

    if ( ! $name )
      $name = "Add matching field first";

    $array[] = array('id' => $row['id'],
      'date' => $name." — ".date("m-d-Y", strtotime($row['created_at'])));
  }

  echo json_encode($array);

  die();
}

function wpfx_peeklayout()
{
  global $wpdb, $currentFile;

  // Convert into integer for security reasons
  $id = intval($_POST['wpfx_layout']) - 9;

  $layout = wpfx_readlayout($id);

  $file = FPROPDF_FORMS_DIR . $layout['file'];
  if ( defined('FPROPDF_IS_DATA_SUBMITTING') )
    $file = $currentFile;

  $form_key = $layout['form'];

  $layout['file'] = base64_encode($layout['file']);

  ob_start();

  $layout['imagesBase'] = plugins_url( '', __FILE__ );
  $layout['images'] = array();
  $layout['checkboxes'] = array();

  try
  {
    if ( !file_exists($file) )
      throw new Exception('PDF file not found');
    $fields_data = shell_exec("pdftk '$file' dump_data_fields_utf8 2> /dev/null");
    //if ( $_SERVER['REMOTE_ADDR'] == '97.74.144.138' ) { echo md5( file_get_contents($file) ) . "\n\n"; copy($file, ABSPATH . '/test.pdf');  var_dump($fields_data); exit; }
    $fields = array();
    if ( !preg_match_all('/FieldName: (.*)/', $fields_data, $m) )
      throw new Exception('PDFTK returned no fields.');
    $fields = $m[1];
    $layout['fields2'] = $fields;

    $data2 = explode('---', $fields_data);
    foreach ($data2 as $_row)
    {
      $id = false;
      $options = array();
      $_row = explode("\n", $_row);
      foreach ( $_row as $_line )
      {
        //if ( isset( $_REQUEST['testing'] ) ) { echo $_line; }
        if ( preg_match('/FieldName: (.*)$/', $_line, $m) )
          $id = $m[ 1 ];
        if ( $id )
          if ( preg_match('/FieldStateOption: (.*)$/', $_line, $m) )
            $options[] = $m[ 1 ];
      }
      if ( $id and count( $options ) )
        $layout['checkboxes'][ $id ] = json_encode( $options );
    }
    //if ( isset( $_GET['testing'] ) ) { print_r( $layout['checkboxes'] ); exit; }
  }
  catch (Exception $e)
  {
    $layout['error2'] = $e->getMessage();
  }

  try
  {
    if ( !file_exists($file) )
      throw new Exception('PDF file not found');

    $layout['imageFields'] = array();

    global $wpdb;

    $layout['actions'] = array();
    $results = $wpdb->get_row("SELECT forms.id FROM {$wpdb->prefix}frm_forms forms WHERE forms.form_key = '$form_key'");
    if ( $results and $results->id )
    {
      $form_actions = FrmFormAction::get_action_for_form( intval( $results->id ) );
      foreach ( $form_actions as $a )
        if ( $a->post_excerpt == 'email' )
          $layout['actions'][ $a->ID ] = $a->post_title;
    }




    $fields = array();


    $results = $wpdb->get_results($q = "SELECT fields.* FROM {$wpdb->prefix}frm_fields fields INNER JOIN {$wpdb->prefix}frm_forms forms ON ( forms.id = fields.form_id AND forms.form_key = '$form_key') ORDER BY fields.field_order ASC ");

    foreach ( $results as $row )
    {
      if ( ( $row->type == 'file' ) or ( $row->type == 'signature' ) or ( $row->type == 'image' ) )
      {
        $_row_id = $row->id;
        if ( fpropdf_use_field_keys() )
          $_row_id = fpropdf_field_id_to_key( $_row_id );
        $layout['imageFields'][] = $_row_id;
      }
      $name = $row->name;
      $name = str_replace('&nbsp;', ' ', $name);
      $name = trim($name);
      // if ( $name == 'Section' ) continue;
      if ( $name == 'End Section' ) continue;
      if ( $row->type == 'checkbox' )
      {
        $checkboxes = array();
        $_opts = @unserialize( $row->options );
        if ( $_opts and is_array( $_opts ) )
          foreach ( $_opts as $_opt )
          {
            //print_r($_opt); exit;
            if ( is_array( $_opt ) )
              $_opt = $_opt['value'];
            $checkboxes[] = $_opt;
          }
        //$layout['checkboxes'][ $row->id ] = $checkboxes;
      }
      if ( $row->type == 'divider' )
      {
        $data = $row->field_options;
        $data = @unserialize( $data );
        if ( ! $data['repeat'] )
          continue;
      }
      // if ( $row->type == 'html' ) continue;
      //$fields[ $row->id ] = "[" . $row->id . "] " . $name;
      $_row_id = $row->id;
      if ( fpropdf_use_field_keys() )
        $_row_id = fpropdf_field_id_to_key( $_row_id );
      $fields[] = array( $_row_id, "[" . $row->id . "] " . $name, fpropdf_field_id_to_key( $row->id ) );
    }

    if ( !count($fields) )
      throw new Exception('Could not get web form IDs');
    $layout['fields1'] = $fields;
  }
  catch (Exception $e)
  {
    $layout['error1'] = $e->getMessage();
  }

  $layout['activated'] = ( fpropdf_is_activated() && !fpropdf_is_trial() );
#<master>
  $layout['activated'] = true;
#</master>

  $layout['previews_activated'] = ( $layout['activated'] && get_option('fpropdf_enable_previews') );

  // Try to get data from master server
  // Master server processes the submitted PDF file and returns user-friendly field names and IDs using Java program

  // if ( fpropdf_is_activated() and !defined('FPROPDF_IS_MASTER') )
  if ( !defined('FPROPDF_IS_MASTER') )
  {

    try
    {

      if ( !file_exists($file) or !is_file($file) )
        throw new Exception('PDF file not found');

      $post = array(
        'salt'   => FPROPDF_SALT,
        'code'   => get_option('fpropdf_licence'),
        'form'   => $layout['form'],
      );
      $post['pdf_file'] = '@' . realpath( $file );
      $post['pdf_file_string'] = base64_encode( @file_get_contents( realpath( $file ) ) );

      $curl = curl_init();
      curl_setopt_array($curl, array(
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_URL => FPROPDF_SERVER . 'licence/data.php?' . time(),
        CURLOPT_POST => 1,
        CURLOPT_HTTPHEADER => array('Expect:'),
        CURLOPT_POSTFIELDS => $post,
        CURLOPT_TIMEOUT => 120,
      ));
      $data = curl_exec($curl);
      if ( !$data )
        throw new Exception('Server returned no data: ' . curl_error($curl));
      $tmp = $data;
      $data = json_decode($data);
      if ( !$data )
        throw new Exception('Server unknown data: ' . $tmp);
      $keys = explode(' ', 'fields fields2 checkboxes');
      foreach ($keys as $key) 
      {
        if ( isset( $data->{$key} ) and $data->{$key} )
          $layout[ $key ] = $data->{$key};
      }

    } 
    catch ( Exception $e )
    {
      $layout['error_server'] = $e->getMessage();
    }
  }

  if ( defined('FPROPDF_IS_DATA_SUBMITTING') )
    @unlink( $currentFile );

  // End try to get data

  ob_get_clean();

  echo json_encode($layout);

  die();
}

function wpfx_killlayout()
{
  global $wpdb;

  // Convert to integers for security reasons
  $id = intval($_POST['wpfx_layout']) - 9;
  
  wpfx_backup_layout($id);

  $query = "DELETE FROM `" . FPROPDF_WPFXLAYOUTS . "` WHERE `ID` = $id";
  $wpdb->query($query);
  die();
}

function wpfx_duplayout()
{
  global $wpdb;

  // Convert to integers for security reasons
  $id = intval($_POST['wpfx_layout']) - 9;

  $layout = wpfx_readlayout( $id );
  extract($layout);
  $name .= ' (copy)';
  wpfx_writelayout($name, $file, $visible, $form, $index, $data, $formats, $add_att, $passwd, $lang, $add_att_ids, $default_format, $name_email, $restrict_role, $restrict_user);

  die();
}

// Enqueue admin styles and scripts
function wpfx_init()
{

  if (!isset($_GET['page']) || $_GET['page'] != 'fpdf' )
    return;

  wp_register_script( 'wpfx-script', plugins_url('/res/script.js', __FILE__), array(), @filemtime( __DIR__ . '/res/script.js' ) );
  wp_register_style ( 'wpfx-style',  plugins_url('/res/style.css', __FILE__) );

  wp_enqueue_style ( 'wpfx-style'  );
  wp_enqueue_script( 'wpfx-script' );
}


// Add menu button
function wpfx_menu()
{
  global $wpfx_idd, $wpfx_url, $wpfx_dsc;
  if (get_option('fpropdf_field_map_allowed') == 'Yes') {
      $role = 'edit_pages';
  } else {
      $role = 'administrator';
  }
  add_menu_page($wpfx_dsc, 'Formidable PRO2PDF', $role, $wpfx_idd, 'wpfx_admin', $wpfx_url.'/res/icon.png');
}


// Get layout visibility
function wpfx_getlayoutvisibility()
{
  global $wpdb;

  // Convert to integers for security reasons
  $id = intval( $_POST['wpfx_layout'] ) - 9;

  $query = "SELECT `visible`, `form` FROM `" . FPROPDF_WPFXLAYOUTS . "` WHERE `ID` = '$id'";
  $result = $wpdb->get_row( $query, ARRAY_A );
  if ( ! $result )
    $result = array();

  die(json_encode(array('visible' => $result['visible'], 'form' => $result['form'])));
}

// Change layout visibility
function wpfx_setlayoutvisibility()
{
  global $wpdb;

  // Convert to integers for security reasons
  $id = intval( $_POST['wpfx_layout'] ) - 9;
  $vs = intval( $_POST['wpfx_layout_visibility'] );

  $query = "UPDATE `" . FPROPDF_WPFXLAYOUTS . "` SET `visible` = $vs WHERE `ID` = '$id'";
  die($wpdb->query($query));
}

// Formidable forms are required for this
function wpfx_validate_formidable()
{
  global $frm_entry, $frm_form, $frm_field, $frmpro_is_installed;

  $errors = $frm_form->validate($_POST);
  $id = (int)FrmAppHelper::get_param('id');

  if( count($errors) > 0 )
  {
    $hide_preview = true;
    $frm_field_selection = FrmFieldsHelper::field_selection();
    $record = $frm_form->getOne( $id );
    $fields = $frm_field->getAll(array('fi.form_id' => $id), 'field_order');
    $values = FrmAppHelper::setup_edit_vars($record, 'forms', $fields, true);
    die($errors);
  }
}

function wpfx_fpropdf_remove_pdf()
{
  $file = $_POST['file'];
  $file = base64_decode( $file );
  // Check if filename does not contain slashes
  if ( preg_match('/\//', $file) )
    die('Wrong filename');
  @unlink( FPROPDF_FORMS_DIR . $file );
  die();
}

// Add admin init action
add_action( 'admin_init', 'wpfx_init');

// Register menu
add_action( 'admin_menu', 'wpfx_menu');

// Register AJAX requests
add_action('wp_ajax_wpfx_get_dataset', 'wpfx_getdataset');
add_action('wp_ajax_wpfx_get_layout',  'wpfx_peeklayout');
add_action('wp_ajax_wpfx_del_layout',  'wpfx_killlayout');
add_action('wp_ajax_wpfx_dup_layout',  'wpfx_duplayout');
add_action('wp_ajax_wpfx_validate_fd', 'wpfx_validate_formidable');
add_action('wp_ajax_fpropdf_remove_pdf', 'wpfx_fpropdf_remove_pdf');

// Form Visibility
add_action('wp_ajax_wpfx_getlayoutvis',  'wpfx_getlayoutvisibility');
add_action('wp_ajax_wpfx_setlayoutvis',  'wpfx_setlayoutvisibility');

// Generate PDF
add_action('wp_ajax_wpfx_generate',  'wpfx_generate_pdf');
add_action('wp_ajax_nopriv_wpfx_generate',  'wpfx_generate_pdf');

function wpfx_generate_pdf()
{
  if ( $_GET['redirect_to_secure'] )
  {
    if ( current_user_can('manage_options') )
    {
      $params = $_GET;
      unset( $params['redirect_to_secure'] );
      $params[ 'key' ] = fpropdf_dataset_key( $params['dataset'], $params['form'], $params['layout'] );
      if ( $params['form2'] )
        $params[ 'key2' ] = fpropdf_dataset_key( $params['dataset2'], $params['form2'], $params['layout2'] );
      wp_redirect( admin_url( 'admin-ajax.php') . '?' . http_build_query( $params ) );
      exit;
    }
  }
  include __DIR__ . '/download.php';
  exit;
}

// Generate Previews
add_action('wp_ajax_wpfx_preview_pdf',  'wpfx_preview_pdf');
add_action('wp_ajax_nopriv_wpfx_preview_pdf',  'wpfx_preview_pdf');

function wpfx_preview_pdf()
{
  if ( isset( $_GET['TB_iframe'] ) and $_GET['TB_iframe'] )
  {
    unset( $_GET['TB_iframe'] );
    $src = '?' . http_build_query( $_GET );
    echo '<img src="'.$src.'" />';
    exit;
  }
  include __DIR__ . '/preview.php';
  exit;
}



add_action('frm_after_create_entry', 'cache_entry', 20, 2);

function cache_entry($entry_id, $form_id) {
    global $wpdb;

    $form = FrmForm::getOne($form_id);
    if (!$form) {
        return;
    }
    

    if (isset($form->options['no_save']) && $form->options['no_save'] == '1') {
        $layout = $wpdb->get_row('SELECT * FROM ' . FPROPDF_WPFXLAYOUTS . ' WHERE form = \'' . $form_id . '\'', ARRAY_A);
        if (!$layout) {
            return false;
        }

        $layout_id = $layout['ID'];
        $layout = wpfx_readlayout($layout_id);

        global $currentLayout;
        $currentLayout = $layout;

        $unicode = isset($layout['lang']) && $layout['lang'] == '1' ? true : false;
        $pdf = FPROPDF_FORMS_DIR . $layout['file'];


        $wpfx_fdf = new FDFMaker();
        $filename = wpfx_download($wpfx_fdf->makeFDF(wpfx_extract(3, $entry_id, $layout['data']), $pdf, $unicode));


        $query = "INSERT INTO `" . FPROPDF_WPFXTMP . "` (`form_id`, `layout_id`,`entry_id`, `path`) VALUES ('$form_id', '$layout_id', '$entry_id', '$filename')";
        $wpdb->query($query);
    }
}


// Email Notifications

add_filter('frm_notification_attachment', 'add_my_attachment', 10, 3);
function add_my_attachment($attachments, $form, $args)
{
  
  global $wpdb, $fpropdf_global;

  if ( !defined('FPROPDF_IS_SENDING_EMAIL') )
    define('FPROPDF_IS_SENDING_EMAIL', true);
  
  $form_id = $form->id;
  $form_key = $form->form_key;
  $layouts = $wpdb->get_results('SELECT * FROM ' . FPROPDF_WPFXLAYOUTS . ' WHERE form = \''.$form_id.'\' AND add_att = 1', ARRAY_A);
  if ( ! $layouts or ! count($layouts) ) return $attachments;
  
  if ( ! $layouts )
    $layouts = array();

  foreach ( $layouts as $layout )
  {

    if ( isset( $layout['add_att_ids'] ) )
    {
      $ids = explode(',', $layout['add_att_ids']);
      $found = false;
      foreach ( $ids as $id )
        if ( ( $id == 'all' ) or ( $id == $args['email_key'] ) )
          $found = true;
      if ( !$found )
        continue;
    }
  
    $layout = $layout['ID'];
    $dataset = $args['entry']->id;
  
    global $FPROPDF_NO_HEADERS;
    $FPROPDF_NO_HEADERS = true;
  
    $__POST = $_POST;
    $__GET = $_GET;
    $__REQUEST = $_REQUEST;
  
    $_GET['form'] = $_REQUEST['form'] = $form_key;
    $_GET['layout'] = $_REQUEST['layout'] = $layout + 9;
    $_GET['dataset'] = $_REQUEST['dataset'] = $dataset;



    
    global $FPROPDF_CONTENT;
    if ( ! $FPROPDF_CONTENT )
    {
      ob_start();
      include __DIR__ . '/download.php';
      ob_get_clean();
    }
  
    global $FPROPDF_CONTENT;
    $data = $FPROPDF_CONTENT;
    $FPROPDF_CONTENT = false;
    //var_dump($data); exit;
  
    $_POST = $__POST;
    $_GET = $__GET;
    $_REQUEST = $__REQUEST;
  
    global $FPROPDF_FILENAME;
    $filename = $FPROPDF_FILENAME;
  
    global $FPROPDF_GEN_ERROR;
    if ( $FPROPDF_GEN_ERROR )
    {
      $filename = "error.txt";
      if ( $FPROPDF_GEN_ERROR )
        $data = $FPROPDF_GEN_ERROR;
    }
  
    $tmp = __DIR__ . '/fields/' . $filename;
    file_put_contents( $tmp, $data );
  
    //echo $tmp; exit;
  
    $attachments[] = $tmp;
    
    $fpropdf_global->addAttachmentToRemove($tmp);
    
  }
  
  return $attachments;
}

add_filter('frm_importing_xml', 'importing_fields_meta_fix', 5, 2);

function importing_fields_meta_fix($imported, $xml) {
    global $wpdb;

    if (!isset($xml->view) && !isset($xml->item)) {
        return $imported;
    }

    if (isset($xml->item)) {
        foreach ($xml->item as $item) {
            $item_key = (string) $item->item_key;
            $form_id = (int) $item->form_id;


            $item_id = $wpdb->get_var($wpdb->prepare(
                            "SELECT id FROM " . $wpdb->prefix . 'frm_items' . " WHERE item_key = %s", $item_key
            ));
            if ($item_id) {
                $item->id = $item_id;
            }
            foreach ($item->item_meta as $meta) {
                $field_id = (int) $meta->field_id;
                $field_key = $wpdb->get_var($wpdb->prepare(
                                "SELECT field_key FROM " . FPROPDF_WPFXFIELDS . " WHERE field_id = %d AND form_id = %d", array($field_id, $form_id)
                ));
                
                if ($field_key) {
                     $field = FrmField::getOne($field_key);
                        if ($field && isset($field->id)) {
                            $meta->field_id = $field->id;
                     }
                }
                
               
            }
        }
    }
    return $imported;
}


add_action( 'frm_notification', 'remove_my_attachment', 10, 3);
function remove_my_attachment() {
    global $fpropdf_global;

    $attachments = $fpropdf_global->getAttachmentsToRemove();

    if (!empty($attachments)) {
        foreach ($attachments as $attachment) {
            if (file_exists($attachment)) {
                unlink($attachment);
            }
        }
    }
    
    $fpropdf_global->flush();
    
}

// Shortcode
include_once __DIR__ . '/formidable-shortcode.php';

function fpropdf_stripslashes($str)
{
        return stripslashes($str);
        return get_magic_quotes_gpc() ? stripslashes($str) : $str;
}