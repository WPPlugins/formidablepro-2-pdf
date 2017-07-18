<?php

// Update embedded field

function fpropdf2_frm_after_create_entry($entry_id, $form_id) {
  $opts = get_option('fpropdf_embedded_data');
  if ( $opts and is_array($opts) )
    foreach ( $opts as $opt)
      if ( $form_id == $opt['form'] )
      {
        global $wpdb;
        $wpdb->query($q = "UPDATE `" . $wpdb->prefix . "frm_item_metas` SET `meta_value` = '" . $entry_id . "' WHERE `field_id` = '" . $opt['field'] . "' AND `item_id` = '" . $entry_id . "'");
      }
}
add_action('frm_after_create_entry', 'fpropdf2_frm_after_create_entry', 30, 2);

function fpropdf2_frm_pre_create_entry($values)
{
  $opts = get_option('fpropdf_embedded_data');
  if ( $opts and is_array($opts) )
    foreach ( $opts as $opt)
      if ( $values['form_id'] == $opt['form'] ) { 
        $values['item_meta'][ $opt['field'] ] = 'NOTSET';
      }
  return $values;
}
add_filter('frm_pre_create_entry', 'fpropdf2_frm_pre_create_entry');

// Settings

add_action( 'admin_init', 'register_fpropdf_settings' );

function register_fpropdf_settings() {
  //register our settings
  register_setting( 'fpropdf-settings-group', 'fpropdf_enable_security' );
  register_setting( 'fpropdf-settings-group', 'fpropdf_enable_previews' );
  register_setting( 'fpropdf-settings-group', 'fpropdf_pdfaid_api_key' );
  register_setting( 'fpropdf-settings-group', 'fpropdf_embedded_data' );
  register_setting( 'fpropdf-settings-group', 'fpropdf_disable_local' );
  register_setting( 'fpropdf-settings-group', 'fpropdf_enable_local' );
  register_setting( 'fpropdf-settings-group', 'fpropdf_faster_uploads' );
  register_setting( 'fpropdf-settings-group', 'fpropdf_use_field_keys' );
  register_setting( 'fpropdf-settings-group', 'fpropdf_limit_dropdowns' );
  register_setting( 'fpropdf-settings-group', 'fpropdf_automap' );
  register_setting( 'fpropdf-settings-group', 'fpropdf_restrict_user' );
  register_setting( 'fpropdf-settings-group', 'fpropdf_restrict_role' );
  register_setting( 'fpropdf-settings-group', 'fpropdf_restrict_condition' );
  register_setting( 'fpropdf-settings-group', 'fpropdf_field_map_allowed' );
  
}

function fpropdf_settings_page() {

  if ( isset($_GET['settings-updated']) )
  {
    echo '<div class="updated"><p>Settings saved.</p></div>';
  }
  
  $has_soap = class_exists('SoapClient');
  $has_soap = true;

?>
<form method="post" action="options.php">
    <?php settings_fields( 'fpropdf-settings-group' ); ?>
    <?php do_settings_sections( 'fpropdf-settings-group' ); ?>
    <table class="form-table" width="100%">

        <?php if ( isset($_GET['show_hidden_options'] ) ): ?>
        
          <tr valign="top">
            <td colspan="2">
              <label>
                <input type="checkbox" name="fpropdf_faster_uploads" value="1" <?php if ( get_option('fpropdf_faster_uploads') ) echo ' checked="checked"'; ?> />
                Enable faster PDF uploads
              </label>
            </td>
          </tr>
        
        <?php endif; ?>

        <tr valign="top">
          <td colspan="2">
            <label>
              <input type="checkbox" name="fpropdf_automap" value="1" <?php if ( get_option('fpropdf_automap') ) echo ' checked="checked"'; ?> />
              Enable automated layout creation for new field maps
            </label>
          </td>
        </tr>

        <tr valign="top">
          <td colspan="2">
            <label>
              <input type="checkbox" name="fpropdf_use_field_keys" value="1" <?php if ( get_option('fpropdf_use_field_keys') ) echo ' checked="checked"'; ?> />
              Use field keys instead of field IDs
            </label>
          </td>
        </tr>
        
        <tr valign="top">
          <td colspan="2">
            <label>
              <input type="checkbox" name="fpropdf_limit_dropdowns" value="1" <?php if ( get_option('fpropdf_limit_dropdowns') ) echo ' checked="checked"'; ?> />
              Limit Field Map to use Dropdown
            </label>
          </td>
        </tr>

        <tr valign="top">
          <td colspan="2">
            <label>
              <input type="checkbox" name="fpropdf_enable_security" value="1" <?php if ( get_option('fpropdf_enable_security') ) echo ' checked="checked"'; ?> />
              Enable secure links
            </label>
          </td>
        </tr>
      
        <tr valign="top">
          <td colspan="2">
            <label>
              <input type="checkbox" name="fpropdf_enable_previews" value="1" <?php if ( get_option('fpropdf_enable_previews') ) echo ' checked="checked"'; ?> />
              Enable field previews in the Field Map Designer
            </label>
          </td>
        </tr>
        
        <tr valign="top">
          <th>PDFaid API key<br />for generating DOCX files</th>
          <td>
            <label>
              <input style="display: block; width: 100%;" type="text" name="fpropdf_pdfaid_api_key" value="<?php echo esc_attr( get_option('fpropdf_pdfaid_api_key') ); ?>" <?php if ( !$has_soap ) echo ' disabled="disabled"'; ?> />
            </label>
            <i>You can get a free API key with 20 credits here: <a href='http://www.pdfaid.com/api-registration.aspx' target='_blank'>http://www.pdfaid.com/api-registration.aspx</a></i>
            <?php if ( !$has_soap ): ?>
            <i><b>PDFaid requires PHP SOAP extension. 
              <br />Until you install and activate this extension , generation of DOCX files won't be possible.
              <br />Please contact your hosting provider or server administrator to enable this extension.
            </b></i>
            <?php endif; ?>
          </td>
        </tr>
        
        <tr valign="top">
          <th>Embedded Forms</th>
          <td id="embedded_options">
            <div class="embedded_options_field_container">
              
              <div class="embedded_options_field embedded_options_field_template">
                For form
                <select data-name="fpropdf_embedded_data[NUMBER][form]">
                  <option value="">Please select a form...</option>
                  <?php 
                    $forms = wpfx_getforms(true);
                    $fields_data = array();
                    foreach ( $forms as $form_id => $form )
                    {
                      $fields = FrmField::get_all_for_form( $form_id, '', 'include' );
                      $fields_data[ $form_id ] = array();
                      foreach ( $fields as $field )
                      {
                        $fields_data[ $form_id ][] = array(
                          'id' => $field->id,
                          'title' => '[' . $field->id . '] ' . strip_tags($field->name),
                        );
                      }
                      echo '<option value="' . $form_id . '">'.$form[ 0 ].'</option>' . PHP_EOL;
                    }
                  ?>
                </select>
                <script>window.fpropdfEmbeddedFieldData = <?php 
                  echo json_encode( array( 
                    'fields' => $fields_data,
                    'currentData' => get_option('fpropdf_embedded_data'),
                  ) );
                ?>;</script>
                set value of field 
                <select data-name="fpropdf_embedded_data[NUMBER][field]"><option value="">Please select a field...</option></select>
                to entry ID. <a href="#" class="button button_del">Remove</a>
              </div>
            
            </div>
            
            <a href="#" class="button" id="fpropdf_add_embedded_form" >Map another field</a>
            
          </td>
        </tr>
         
        <?php if ( fpropdf_is_activated() /*and fpropdf_custom_command_exist('pdftk')*/ ): ?>
         
          <tr valign="top">
            <td colspan="2">
              <label>
                <input type="checkbox" name="fpropdf_enable_local" value="1" <?php if ( get_option('fpropdf_enable_local') ) echo ' checked="checked"'; ?> />
                Enable local PDFTK
              </label>
            </td>
          </tr>
        
        <?php endif; ?>
         
        <tr valign="top">
          <th>Allow downloads only for these user roles:</th>
          <td>
            <label>
              <input style="display: block; width: 100%;" type="text" name="fpropdf_restrict_role" value="<?php echo esc_attr( get_option('fpropdf_restrict_role') ); ?>" placeholder="all" />
            </label>
            <i>A comma separated list of role names. Accepted values: <?php
              global $wp_roles;
              $roles = $wp_roles->get_names();
              echo implode( ', ', array_keys( $roles ) );
            ?></i>
          </td>
        </tr>
        
        <tr valign="top">
          <th>Allow downloads for these user IDs:</th>
          <td>
            <label>
              <input style="display: block; width: 100%;" type="text" name="fpropdf_restrict_user" value="<?php echo esc_attr( get_option('fpropdf_restrict_user') ); ?>" placeholder="all" />
            </label>
            <i>A comma separated list of user IDs.</i>
          </td>
        </tr>
        
        <tr valign="top">
          <th>How to apply restrictions by user/role?</th>
          <td>
            <label>
              <select name="fpropdf_restrict_condition">
                <option value="and"<?php echo ( get_option('fpropdf_restrict_condition') == 'and' ? ' selected="selected"' : '' ); ?>>User role AND user ID should match</option>
                <option value="or"<?php echo ( get_option('fpropdf_restrict_condition') == 'or' ? ' selected="selected"' : '' ); ?>>User role OR user ID should match</option>
              </select>
            </label>
          </td>
        </tr>
        
        <tr valign="top">
          <th>Allow "Editor" user roles to access the plugin?</th>
          <td>
            <label>
              <input type="radio" name="fpropdf_field_map_allowed" value="Yes" <?php echo ( get_option('fpropdf_field_map_allowed') == 'Yes' ? ' checked="checked"' : '' ); ?> /> Yes
            </label>
            <label>
              <input type="radio" name="fpropdf_field_map_allowed" value="No" <?php echo ( get_option('fpropdf_field_map_allowed') != 'Yes' ? ' checked="checked"' : '' ); ?>/> No
            </label>
          </td>
        </tr>
         
    </table>
    
    <?php submit_button(); ?>

</form>
<?php } 



