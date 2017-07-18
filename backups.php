<?php

function fpropdf_backups_sort($a, $b) {
  if ( $a['ts'] > $b['ts'] )
    return -1;
  return 1;
}

function fpropdf_restore_backup( $filename, $force_id = false )
{
  if ( ! file_exists( $filename ) )
    throw new Exception('File wasn\'t uploaded or couldn\'t be found.');
  
  $currentFileData = json_decode( file_get_contents( $filename ), true );
  
  if ( ! $currentFileData )
    throw new Exception('File contains some invalid data. The plugin wasn\'t able to read it.');
  
  if ( $currentFileData['xml'] )
  {
    $tmp = tempnam( PROPDF_TEMP_DIR, 'fproPdfXml' );
    
     try {
      if (!file_exists($tmp)) {
                throw new Exception("Tmp folder " . PROPDF_TEMP_DIR . " not exists or not writable");
      }
    } catch (Exception $e) {
            echo '<div class="error" style="margin-left: 0;"><p>' . $e->getMessage() . '</p></div>';
            die();
    }
    
    file_put_contents( $tmp, base64_decode( $currentFileData['xml'] ) );
		$result = FrmXMLHelper::import_xml( $tmp );
                
                
        global $wpdb;
        $dom = new DOMDocument;
        $success = $dom->loadXML(file_get_contents($tmp));

        try {
            if (!$success) {
                throw new Exception("There was an error when reading this XML file");
            } elseif (!function_exists('simplexml_import_dom')) {
                throw new Exception("Your server is missing the simplexml_import_dom function");
            }
        } catch (Exception $e) {
            echo '<div class="error" style="margin-left: 0;"><p>' . $e->getMessage() . '</p></div>';
            die();
        }
        
        
        $xml = simplexml_import_dom($dom);

        $form_id = (string) $xml->form->id;

        foreach ($xml->form->field as $item) {

            $field = array(
                'field_id' => (int) $item->id,
                'field_key' => (string) $item->field_key,
                'form_id' => $form_id
            );


            $result = $wpdb->get_var($wpdb->prepare(
                            "SELECT field_key FROM " . FPROPDF_WPFXFIELDS . " WHERE field_id = %s AND form_id = %d", array($field['field_id'], $field['form_id'])
            ));

            if (!$result) {
                $wpdb->insert(FPROPDF_WPFXFIELDS, $field, array('%d', '%s', '%s'));
            }
        }
                
		FrmXMLHelper::parse_message( $result, $message, $errors );
		if ( $errors )
		  throw new Exception('There were some errors when importing Formidable Form. ' . print_r( $errors, true ) );
  }
  
  // unset($currentFileData['xml']);
  // unset($currentFileData['pdf']);
  
  $map = $currentFileData['data'];
  
  extract($map);
  global $wpdb;
  $form = $currentFileData['form']['form_key'];
  $exists = $wpdb->get_var( "SELECT COUNT(*) AS c FROM " . FPROPDF_WPFXLAYOUTS . " WHERE ID = $ID" );

  $index = $dname;
  $data = @unserialize( $data );
  $formats = @unserialize( $formats );
  
  if ( $currentFileData['salt'] != FPROPDF_SALT )
  {
    $row = $wpdb->get_row( $wpdb->prepare("SELECT * FROM " . FPROPDF_WPFXLAYOUTS . " WHERE name = %s", $name ) , ARRAY_A );
    if ( $row )
    {
      $exists = true;
      $ID = $row['ID'];
    }
  }
  
  $lang = isset($lang) ? $lang : 0; 
  
  if ( $exists )
    wpfx_updatelayout($ID, esc_sql($name), esc_sql($file), $visible, esc_sql($form), $index, $data, $formats, $add_att, $passwd, $lang, $add_att_ids, $default_format, $name_email, $restrict_role, $restrict_user);
  else
    wpfx_writelayout(esc_sql($name), esc_sql($file), $visible, esc_sql($form), $index, $data, $formats, $add_att, $passwd, $lang, $add_att_ids, $default_format, $name_email, $restrict_role, $restrict_user);
    
  if ( $currentFileData['pdf'] )
  {
    file_put_contents( FPROPDF_FORMS_DIR . '/' . $file, base64_decode( $currentFileData['pdf'] ) );
  }
    
  if ( $force_id )
  {
    $wpdb->query( $wpdb->prepare( "UPDATE " . FPROPDF_WPFXLAYOUTS . " SET ID = $force_id WHERE name = %s", $name ) );
  }
    
  $exists = $wpdb->get_var( "SELECT COUNT(*) AS c FROM " . FPROPDF_WPFXLAYOUTS . " WHERE ID = $ID" );
}

function fpropdf_backups_page()
{
  $files = array();
  
  if ($handle = opendir( FPROPDF_BACKUPS_DIR )) {
    
    while (false !== ($entry = readdir($handle))) {
      
      if ( !preg_match('/\.json$/', $entry) )
        continue;
        
      $data = json_decode( file_get_contents( FPROPDF_BACKUPS_DIR . $entry), true );
        
      $files[] = array(
        'name' => $entry,
        'ts' => $data['ts'],
        'data' => $data,
      );
      
    }
    

    closedir($handle);
  }
  
  usort($files, 'fpropdf_backups_sort');
  
  if ( $_GET['restore'] )
  {
    foreach ( $files as $currentFile )
    {
      if ( $_GET['restore'] == $currentFile['name'] )
      {
        try
        {
          fpropdf_restore_backup( FPROPDF_BACKUPS_DIR . $currentFile['name'] );
        }
        catch ( Exception $e )
        {
          die( $e->getMessage() );
        }
        $_SESSION['fpropdf_restored'] = true;
        echo '<script>window.location.href = "?page=fpdf&tab=backups";</script>';
        exit;
      }
    }
  }
  
  if ( $_GET['delete'] )
  {
    foreach ( $files as $currentFile )
    {
      if ( $_GET['delete'] == $currentFile['name'] )
      {
        @unlink( FPROPDF_BACKUPS_DIR . $currentFile['name'] );
        $_SESSION['fpropdf_deleted'] = true;
        echo '<script>window.location.href = "?page=fpdf&tab=backups";</script>';
        exit;
      }
    }
  }
  
  if ( $_SESSION['fpropdf_restored'] )
  {
    echo '<div class="updated" style="margin-left: 0;"><p>Field map has been restored. You can now edit it in <a href="?page=fpdf">field map designer</a>.</p></div>';
    unset($_SESSION['fpropdf_restored']);
  }
  
  if ( $_SESSION['fpropdf_deleted'] )
  {
    echo '<div class="updated" style="margin-left: 0;"><p>Backup has been deleted.</p></div>';
    unset($_SESSION['fpropdf_deleted']);
  }
  
  if ( !count($files) )
  {
    echo '<div class="error" style="margin-left: 0;"><p>You don\'t have any backups yet. <br /> Backups will be automatically generated after you save or create a field map.</p></div>';
    return;
  }
  
  ?>
  
    <table class="wp-list-table widefat fixed striped">
      <thead>
        <tr>
          <th>Date</th>
          <th>Time</th>
          <th>Form</th>
          <th>Field Map</th>
          <th>Filename</th>
          <th>Number of fields</th>
          <th>&nbsp;</th>
        </tr>
      </thead>
      <tbody>

        <?php
        
          foreach ( $files as $file ):
          
          ?>
          
            <tr>
              <td><?php echo date(get_option('date_format'), $file['data']['ts']); ?></td>
              <td><?php echo date('H:i:s', $file['data']['ts']); ?></td>
              <td><?php echo $file['data']['form']['name']; ?></td>
              <td><?php echo $file['data']['data']['name']; ?></td>
              <td><?php echo $file['data']['data']['file']; ?></td>
              <td><?php echo @count( @unserialize( $file['data']['data']['data'] ) ); ?></td>
              <td>
                <p>
                  <a href="?page=fpdf&tab=backups&restore=<?php echo $file['name']; ?>" class="button button-primary" onclick="return confirm('Are you sure you want to restore this backup (<?php echo date(get_option('date_format') . ' H:i:s', $file['data']['ts']); ?>)?');">Restore</a>
                  <a href="?page=fpdf&tab=backups&delete=<?php echo $file['name']; ?>" class="button" onclick="return confirm('Are you sure you want to delete this backup (<?php echo date(get_option('date_format') . ' H:i:s', $file['data']['ts']); ?>)?');">Delete</a>
                </p>
                <p>
                  
                  <a href="../wp-content/uploads/fpropdf-backups/<?php echo $file['name']; ?>" class="button" download="<?php echo esc_attr($file['data']['form']['name'] . " - " . $file['data']['data']['name'] . " - " . date("Y-m-d H-i-s", $file['data']['ts']) . ".json"); ?>">Download</a>
                </p>
              </td>
            </tr>
          
          <?php
          
          endforeach;
        
        ?>
        
      </tbody>
    </table>
  
  <?php
    
}