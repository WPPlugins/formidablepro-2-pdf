<?php

if ( !defined('ABSPATH') )
  exit;

// Add backend styles
function formidable_shortcode_wp_admin_style( $hook )
{
  if ( basename( $_SERVER['PHP_SELF'] ) != 'admin.php' )
    return;
  if ( $_GET['page'] != 'fpdf' )
    return;
  wp_register_style( 'formidable_shortcode_css', plugin_dir_url( __FILE__ ) . 'css/admin.css', false, filemtime( __DIR__ . '/css/admin.css' ) );
  wp_enqueue_style( 'formidable_shortcode_css' );
  wp_enqueue_script( 'formidable_shortcode_js', plugin_dir_url( __FILE__ ) . 'js/admin.js', array( 'jquery' ), filemtime( __DIR__ . '/js/admin.js' ) );
}
add_action( 'admin_enqueue_scripts', 'formidable_shortcode_wp_admin_style' );

// Add frontend styles
function formidable_shortcode_name_scripts()
{
  wp_register_style( 'formidable_shortcode_css', plugin_dir_url( __FILE__ ) . 'css/style.css', false, filemtime( __DIR__ . '/css/style.css' ) );
  wp_enqueue_style( 'formidable_shortcode_css' );
}

add_action( 'wp_enqueue_scripts', 'formidable_shortcode_name_scripts' );

// Add download shortcode
function formidable_shortcode_download($atts = array(), $content = '')
{
  $text = $atts['title'];
  if ( isset( $atts['flatten'] ) )
  {
    $atts['flattenOverride'] = $atts['flatten'];
    unset($atts['flatten']);
  }
  if ( ! $text )
    $text = 'Download'; 
  $class = $atts['class'];
  $class .= ' readmore formidable-download';
  if ( isset($atts['download']) && $atts['download'] )
    $class .= ' formidable-download-auto';
  $iframe = 'iframe' . time() . rand(0,1000000);
  $args = $atts;
  unset($args['class']); 
  unset($args['title']);
  
  $layout_info = wpfx_readlayout( $args['layout'] );
  
  $restrict_condition = isset($args['condition']) ? strtolower( $args['condition'] ) : '';
  if ( ! $restrict_condition )
    $restrict_condition = get_option('fpropdf_restrict_condition');
  if ( ! $restrict_condition )
    $restrict_condition = 'and';
  
  $restrict_1 = 1;
  $restrict_2 = 1;
  
  $restrict_user = isset($atts['user']) ? $atts['user'] : null;
  if ( ! $restrict_user )
    $restrict_user = $layout_info[ 'restrict_user' ];
  if ( ! $restrict_user )
    $restrict_user = get_option('fpropdf_restrict_user');
  if ( ! fpropdf_check_user_id( $restrict_user ) )
    $restrict_1 = false;
    
  $restrict_role = isset($atts['role']) ? $atts['role'] : null;
  if ( ! $restrict_role )
    $restrict_role = $layout_info[ 'restrict_role' ];
  if ( ! $restrict_role )
    $restrict_role = get_option('fpropdf_restrict_role');
  if ( ! fpropdf_check_user_role( $restrict_role ) )
    $restrict_2 = false;
  
  // echo "condition = $restrict_condition " . intval( $restrict_1 ) . " "  . intval( $restrict_2 ) . '<br />';
  
  if ( $restrict_condition == 'or' )
    if ( !$atts['user'] or !$atts['role'] )
      $restrict_condition = 'and';

  if ( $restrict_condition == 'and' )
    if ( !$restrict_1 || !$restrict_2 )
      return '';
  if ( $restrict_condition == 'or' )
    if ( !$restrict_1 && !$restrict_2 )
      return '';

  if ( get_option('fpropdf_enable_security') )
  {
    $args['key'] = fpropdf_dataset_key( $args['dataset'], $args['form'], $args['layout'], isset($args['user']) ? $args['user'] : null, isset($args['role']) ? $args['role'] : null, isset($args['condition']) ? $args['condition'] : null );
    if ( isset($args['dataset2']) && $args['dataset2'] )
      $args['key2'] = fpropdf_dataset_key( $args['dataset2'], $args['form2'], $args['layout2'], $args['user'], $args['role'], $args['condition'] );
  }
  
  $href = admin_url('admin-ajax.php') . '?action=wpfx_generate&' . http_build_query( $args );
  if ( isset($atts['label']) && $atts['label'] . '' === '0' )
    return $href;
  $html = '<a href="' . $href . '" class="' . $class . '" target="_blank">' . $text . '</a>';
  if ( isset($atts['download']) && $atts['download'] )
    $html .= '<iframe class="formidable-download-iframe" id="' . $iframe . '" src="' . $href . '"></iframe>';
  return do_shortcode($html);
}
add_shortcode('formidable-download', 'formidable_shortcode_download');

// Add template download shortcode
function formidable_shortcode_download_in_list($atts = array())
{
  $atts['class'] = 'icon-button download-icon';
  $atts['dataset'] = $_GET['entry'];
  $atts['title'] .= ' <span class="et-icon"></span>';
  return formidable_shortcode_download($atts);
}
add_shortcode('formidable-download-in-list', 'formidable_shortcode_download_in_list');

// Add secret kye shortcode
function formidable_secret_key($atts = array(), $content = '')
{
  return fpropdf_dataset_key( $atts['dataset'], $atts['form'], $atts['layout'], $atts['user'], $atts['role'], $atts['condition'] );
}
add_shortcode('formidable-pdf-key', 'formidable_secret_key');