<?php

if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) 
  exit();

function fpropdf_delete_data( $blog_id = 0)
{

  global $wpdb;
  
  $prefix = $wpdb->prefix;
  if ( $blog_id )
    $prefix = $wpdb->get_blog_prefix( $blog_id );
  
  $wpdb->query( "DROP TABLE IF EXISTS {$prefix}fpropdf_layouts" );
  $wpdb->query( "DROP TABLE IF EXISTS {$prefix}fpropdf_fields" );
  
  /*
  $all_options = wp_load_alloptions();
  foreach ( $all_options as $key => $value )
    if ( preg_match( '/^(fpropdf|formidablepro2pdf)/', $key ) )
      delete_site_option( $key );  
  */
  
  $wpdb->query( "DELETE FROM {$prefix}options WHERE option_name LIKE 'fpropdf%'" );
  $wpdb->query( "DELETE FROM {$prefix}options WHERE option_name LIKE 'formidablepro2pdf%'" );
  
  $upload_dir = wp_upload_dir();
  $dirs = array();
  $dirs[] = $upload_dir['basedir'] . '/fpropdf-forms/';
  $dirs[] = $upload_dir['basedir'] . '/fpropdf-backups/';
  foreach ( $dirs as $dir )
  {
    $dh = @opendir($dir);
    if ( $dh )
    {
      while (false !== ($filename = readdir($dh))) 
      {
        if ( ! preg_match('/^\./', $filename) )  
          @unlink( $dir . '/' . $filename );
      }
      @closedir($dh);
    }
    @rmdir( $dir );
  }

}

if ( !is_multisite() ) 
{
  fpropdf_delete_data();
} 
else 
{
  global $wpdb;
  $blog_ids = $wpdb->get_col( "SELECT blog_id FROM {$wpdb->blogs}" );
  $original_blog_id = get_current_blog_id();

  foreach ( $blog_ids as $blog_id ) 
  {
    switch_to_blog( $blog_id );
    fpropdf_delete_data(  $blog_id ); 
  }

  switch_to_blog( $original_blog_id );
}