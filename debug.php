<?php

function fpropdf_debug_page()
{
  
  function fpropdf_print($v)
  {
    if ( $v === false )
      $v = 'No';
    elseif ( $v === true )
      $v = 'Yes';
    elseif ( is_array($v) )
    {
      if ( count($v) )
      {
        $v = array_map('fpropdf_print', $v);
        $v = implode(', ', $v);
      }
      else
        $v = 'Empty';
    }
    elseif ( @strlen($v) )
      $v = str_replace("\n", ' ', $v);
    else
      $v = 'No';
    return $v;
  }
  
  //phpinfo(); exit;
  
  $debug = array();
  
  //error_reporting(E_ALL);
  //ini_set('display_errors', 'on');
  
  $debug[] = "Site URL: " . site_url('/');
  $debug[] = "Plugin folder: " . basename( dirname( __FILE__ ));
  $debug[] = "PHP version: " . phpversion();
  $debug[] = 'WP version: ' . fpropdf_print( get_bloginfo('version') );
  $debug[] = "FrmAppHelper: " . fpropdf_print( class_exists('FrmAppHelper') );
  $debug[] = 'Trial: ' . fpropdf_print( fpropdf_is_trial() );
  $debug[] = 'PDFTK: ' . fpropdf_print( @shell_exec('which pdftk') );
  $debug[] = 'System: ' . fpropdf_print( @shell_exec('uname -a') );
  
  if ( function_exists('curl_version') )
    $version = curl_version();
  else
    $version = array( 'version' => false );
  $debug[] = 'CURL: ' . fpropdf_print( $version['version'] );
  
  $curl = false;
  if ( function_exists('curl_init') )
  {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://www.idealchoiceinsurance.com/');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $curl = ( strlen(curl_exec($ch)) > 0 );
    $debug[] = 'CURL error: ' . fpropdf_print( curl_error( $ch ) );
  }
  $debug[] = 'CURL test: ' . fpropdf_print( $curl );
  
  $debug[] = 'PHP Extensions: ' . fpropdf_print( get_loaded_extensions() );
  $debug[] = 'Plugins: ' . fpropdf_print( get_option('active_plugins') );
  
  $debug[] = '';
  $folders = array( __DIR__ . '/fields/', sys_get_temp_dir(), FPROPDF_FORMS_DIR );
  foreach ( $folders as $folder )
  {
    $folder = realpath( $folder );
    $tmp = $folder . '/' . md5(time()) . '.tmp';
    @file_put_contents($tmp, 'test');
    //$folderss = str_replace( realpath( ABSPATH), '', $folder);
    //$debug[] = '';
    $debug[] = $folder . ' is writable: ' . fpropdf_print( is_writable( $folder ) );
    $debug[] = $folder . ' can have files: ' . fpropdf_print( file_exists( $tmp ) );
    $debug[] = $folder . ' write successful: ' . fpropdf_print( file_get_contents( $tmp ) === 'test' );
    @unlink($tmp);
    $debug[] = $folder . ' delete successful: ' . fpropdf_print( file_exists( $tmp ) === false );
  }
  
  $debug = implode("\n", $debug);
  
  ?>
  
  <p>Please copy-paste the information below for your customer support requests:</p>
  <textarea style="display: block; width: 100%; height: 600px;"><?php echo $debug; ?></textarea>
  
  <?php
}