<?php

if ( !defined('ABSPATH') )
  exit;

if ( ! function_exists('fpropdf_header') )
{
function fpropdf_transliterateString($txt) {
    $transliterationTable = array('á' => 'a', 'Á' => 'A', 'à' => 'a', 'À' => 'A', 'ă' => 'a', 'Ă' => 'A', 'â' => 'a', 'Â' => 'A', 'å' => 'a', 'Å' => 'A', 'ã' => 'a', 'Ã' => 'A', 'ą' => 'a', 'Ą' => 'A', 'ā' => 'a', 'Ā' => 'A', 'ä' => 'ae', 'Ä' => 'AE', 'æ' => 'ae', 'Æ' => 'AE', 'ḃ' => 'b', 'Ḃ' => 'B', 'ć' => 'c', 'Ć' => 'C', 'ĉ' => 'c', 'Ĉ' => 'C', 'č' => 'c', 'Č' => 'C', 'ċ' => 'c', 'Ċ' => 'C', 'ç' => 'c', 'Ç' => 'C', 'ď' => 'd', 'Ď' => 'D', 'ḋ' => 'd', 'Ḋ' => 'D', 'đ' => 'd', 'Đ' => 'D', 'ð' => 'dh', 'Ð' => 'Dh', 'é' => 'e', 'É' => 'E', 'è' => 'e', 'È' => 'E', 'ĕ' => 'e', 'Ĕ' => 'E', 'ê' => 'e', 'Ê' => 'E', 'ě' => 'e', 'Ě' => 'E', 'ë' => 'e', 'Ë' => 'E', 'ė' => 'e', 'Ė' => 'E', 'ę' => 'e', 'Ę' => 'E', 'ē' => 'e', 'Ē' => 'E', 'ḟ' => 'f', 'Ḟ' => 'F', 'ƒ' => 'f', 'Ƒ' => 'F', 'ğ' => 'g', 'Ğ' => 'G', 'ĝ' => 'g', 'Ĝ' => 'G', 'ġ' => 'g', 'Ġ' => 'G', 'ģ' => 'g', 'Ģ' => 'G', 'ĥ' => 'h', 'Ĥ' => 'H', 'ħ' => 'h', 'Ħ' => 'H', 'í' => 'i', 'Í' => 'I', 'ì' => 'i', 'Ì' => 'I', 'î' => 'i', 'Î' => 'I', 'ï' => 'i', 'Ï' => 'I', 'ĩ' => 'i', 'Ĩ' => 'I', 'į' => 'i', 'Į' => 'I', 'ī' => 'i', 'Ī' => 'I', 'ĵ' => 'j', 'Ĵ' => 'J', 'ķ' => 'k', 'Ķ' => 'K', 'ĺ' => 'l', 'Ĺ' => 'L', 'ľ' => 'l', 'Ľ' => 'L', 'ļ' => 'l', 'Ļ' => 'L', 'ł' => 'l', 'Ł' => 'L', 'ṁ' => 'm', 'Ṁ' => 'M', 'ń' => 'n', 'Ń' => 'N', 'ň' => 'n', 'Ň' => 'N', 'ñ' => 'n', 'Ñ' => 'N', 'ņ' => 'n', 'Ņ' => 'N', 'ó' => 'o', 'Ó' => 'O', 'ò' => 'o', 'Ò' => 'O', 'ô' => 'o', 'Ô' => 'O', 'ő' => 'o', 'Ő' => 'O', 'õ' => 'o', 'Õ' => 'O', 'ø' => 'oe', 'Ø' => 'OE', 'ō' => 'o', 'Ō' => 'O', 'ơ' => 'o', 'Ơ' => 'O', 'ö' => 'oe', 'Ö' => 'OE', 'ṗ' => 'p', 'Ṗ' => 'P', 'ŕ' => 'r', 'Ŕ' => 'R', 'ř' => 'r', 'Ř' => 'R', 'ŗ' => 'r', 'Ŗ' => 'R', 'ś' => 's', 'Ś' => 'S', 'ŝ' => 's', 'Ŝ' => 'S', 'š' => 's', 'Š' => 'S', 'ṡ' => 's', 'Ṡ' => 'S', 'ş' => 's', 'Ş' => 'S', 'ș' => 's', 'Ș' => 'S', 'ß' => 'SS', 'ť' => 't', 'Ť' => 'T', 'ṫ' => 't', 'Ṫ' => 'T', 'ţ' => 't', 'Ţ' => 'T', 'ț' => 't', 'Ț' => 'T', 'ŧ' => 't', 'Ŧ' => 'T', 'ú' => 'u', 'Ú' => 'U', 'ù' => 'u', 'Ù' => 'U', 'ŭ' => 'u', 'Ŭ' => 'U', 'û' => 'u', 'Û' => 'U', 'ů' => 'u', 'Ů' => 'U', 'ű' => 'u', 'Ű' => 'U', 'ũ' => 'u', 'Ũ' => 'U', 'ų' => 'u', 'Ų' => 'U', 'ū' => 'u', 'Ū' => 'U', 'ư' => 'u', 'Ư' => 'U', 'ü' => 'ue', 'Ü' => 'UE', 'ẃ' => 'w', 'Ẃ' => 'W', 'ẁ' => 'w', 'Ẁ' => 'W', 'ŵ' => 'w', 'Ŵ' => 'W', 'ẅ' => 'w', 'Ẅ' => 'W', 'ý' => 'y', 'Ý' => 'Y', 'ỳ' => 'y', 'Ỳ' => 'Y', 'ŷ' => 'y', 'Ŷ' => 'Y', 'ÿ' => 'y', 'Ÿ' => 'Y', 'ź' => 'z', 'Ź' => 'Z', 'ž' => 'z', 'Ž' => 'Z', 'ż' => 'z', 'Ż' => 'Z', 'þ' => 'th', 'Þ' => 'Th', 'µ' => 'u', 'а' => 'a', 'А' => 'a', 'б' => 'b', 'Б' => 'b', 'в' => 'v', 'В' => 'v', 'г' => 'g', 'Г' => 'g', 'д' => 'd', 'Д' => 'd', 'е' => 'e', 'Е' => 'E', 'ё' => 'e', 'Ё' => 'E', 'ж' => 'zh', 'Ж' => 'zh', 'з' => 'z', 'З' => 'z', 'и' => 'i', 'И' => 'i', 'й' => 'j', 'Й' => 'j', 'к' => 'k', 'К' => 'k', 'л' => 'l', 'Л' => 'l', 'м' => 'm', 'М' => 'm', 'н' => 'n', 'Н' => 'n', 'о' => 'o', 'О' => 'o', 'п' => 'p', 'П' => 'p', 'р' => 'r', 'Р' => 'r', 'с' => 's', 'С' => 's', 'т' => 't', 'Т' => 't', 'у' => 'u', 'У' => 'u', 'ф' => 'f', 'Ф' => 'f', 'х' => 'h', 'Х' => 'h', 'ц' => 'c', 'Ц' => 'c', 'ч' => 'ch', 'Ч' => 'ch', 'ш' => 'sh', 'Ш' => 'sh', 'щ' => 'sch', 'Щ' => 'sch', 'ъ' => '', 'Ъ' => '', 'ы' => 'y', 'Ы' => 'y', 'ь' => '', 'Ь' => '', 'э' => 'e', 'Э' => 'e', 'ю' => 'ju', 'Ю' => 'ju', 'я' => 'ja', 'Я' => 'ja');
    return str_replace(array_keys($transliterationTable), array_values($transliterationTable), $txt);
}
}

if ( ! function_exists('fpropdf_header') )
{
  function fpropdf_header( $h )
  {
    global $FPROPDF_NO_HEADERS;
    if ( ! $FPROPDF_NO_HEADERS )
      @header( $h );
  }
}

global $fpropdfSignatures;



$error = 0;

if ( isset($_POST['desired']) and isset($_POST['actual']) )
{

  if ( !file_exists($_POST['desired']) or !is_file($_POST['desired']) )
  {
    $_POST['actual'] = __DIR__ . '/blank.pdf';
    $is_blank = true;
  }

  if ( !file_exists($_POST['actual']) or !is_file($_POST['actual']) )
  {
    $_POST['actual'] = __DIR__ . '/blank.pdf';
    $is_blank = true;
  }

  $desired = escapeshellarg( $_POST['desired'] );
  $actual  = escapeshellarg( $_POST['actual'] );
  $actual2  = escapeshellarg( $_POST['actual2'] );
  $flatten = intval($_POST['lock']) ? 'flatten' : '';
  $real_flatten = ( intval($_POST['lock']) == 2 );
  
  $format = $_REQUEST['format'];
  if ( ! $format )
    $format = $_REQUEST['default_format'];
  $format = @strtolower( $format );
  if ( !in_array($format, explode(' ', 'pdf docx')) )
    $format = 'pdf';
    
  if ( isset($_REQUEST['flattenOverride']) )
  {
    if ( $_REQUEST['flattenOverride'] == 'yes' )
      $flatten = 'flatten';
    if ( $_REQUEST['flattenOverride'] == 'no' )
      $flatten = '';
    if ( $_REQUEST['flattenOverride'] == 'image' )
      $real_flatten = true;
  }

  $encrypt = false;
  if ( isset($_POST['passwd']) and $_POST['passwd'] )
  {
    $pass = escapeshellarg( stripslashes($_POST['passwd']) );
    $encrypt = ' encrypt_40bit user_pw ' . $pass;
    //$encrypt = ' input_pw ' . $pass;
  }
    

  if ( $format == 'docx' )
  {
    $encrypt = false;
    $flatten = '';
  }

  $generated_filename = $_POST['filename'];

  if ( isset($_GET['filename']) and $_GET['filename'] )
    $generated_filename = $_GET['filename'];
    
  if ( defined('FPROPDF_IS_SENDING_EMAIL') )
    if ( trim($_POST['name_email']) )
      $generated_filename = $_POST['name_email'];

  global $currentFieldsData;
  if ( ! $currentFieldsData ) 
    $currentFieldsData = array();
  
  preg_match_all('/\[(\d+)\]/', $generated_filename, $matches);
    if (isset($matches['1']) && !empty($matches['1'])) {
        $fields_to_replace = $matches[1];
        foreach ($fields_to_replace as $key => $value) {
            $field = FrmField::getOne($value);
            if ($field && array_key_exists($value, $currentFieldsData)) {
                $generated_filename = str_replace("[{$value}]", "{$currentFieldsData[$value]}", $generated_filename);
            } elseif ($field && array_key_exists($field->field_key, $currentFieldsData)) {
                $generated_filename = str_replace("[{$value}]", "{$currentFieldsData[$field->field_key]}", $generated_filename);
            } else {
                $generated_filename = str_replace("[{$value}]", "", $generated_filename);
            }
        }
    }
    
  foreach($currentFieldsData as $id => $val)
  {
    $generated_filename = str_replace('['.$id.']', $val, $generated_filename);
  }

  $generated_filename = fpropdf_transliterateString( $generated_filename );

  $generated_filename = preg_replace('/[^a-zA-Z0-9\_\.\- ]+/', ' ', $generated_filename);
  $generated_filename = preg_replace('/ +/', ' ', $generated_filename);
  $generated_filename = trim($generated_filename);
  $generated_filename = trim($generated_filename, '_');
  if ( ! $generated_filename )
    $generated_filename = "Form";
  if ( !preg_match('/\.'.$format.'$/i', $generated_filename) )
    $generated_filename .= '.'.$format;

  $old_post = $_POST;
  unset($_POST);
  $cont = true;

  if ( $_REQUEST['testing'] )
  {
    //echo shell_exec("pdftk $desired fill_form $actual output - $flatten 2>&1"); exit;
  }

  if($cont)
  {
    
    if ( $format == 'pdf' )
      fpropdf_header('Content-type: application/pdf');
    elseif ( $format == 'docx' )
      fpropdf_header('Content-type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
    fpropdf_header("Content-Disposition: attachment; filename=\"".$generated_filename."\"");

    if ( isset($_GET['inline']) )
      fpropdf_header("Content-Disposition: inline; filename=\"".$generated_filename."\"");

    global $FPROPDF_FILENAME;
    $FPROPDF_FILENAME = $generated_filename;

    ob_start();

    $tmp = false;
    $command = false;
    $tmpPdf = '-';

    if ( $_REQUEST['testing'] and false )
    {
      header('Content-Type: text/plain');
      echo shell_exec($cmd = "java -jar " . escapeshellarg( ABSPATH . 'wp-content/plugins/fpropdf/mcpdf/mcpdf.jar'  ) . " $desired fill_form - output - < $actual 2>&1");
      echo $cmd;
      exit;
    }

    if ( $is_blank )
    {
      readfile( __DIR__ . '/blank.pdf' );
    }
    elseif ( $actual2 and ( $actual2 != "''" ) )
    {
      $tmp = escapeshellarg(tempnam( PROPDF_TEMP_DIR, 'fpropdfTmpFile' ) . '.pdf');
      $command = "pdftk $desired fill_form $actual output $tmp 2>&1";
      shell_exec($command);
      $command = "pdftk $tmp fill_form $actual2 output $tmpPdf $flatten";
    }
    else
    {
      $command = "pdftk $desired fill_form $actual output $tmpPdf $flatten";
    }

    if ( $command )
    {
      //shell_exec($command);
      
      ob_start();
      passthru($command);
      $buffer = ob_get_clean();
      
      // if ( $_REQUEST['testing'] == 1 ) { readfile( $old_post['desired'] ); exit; }

      if ( $real_flatten )
      {
        $tmpPdf = tempnam( PROPDF_TEMP_DIR, 'fpropdfTmpFile' ) . '.pdf';
        $tmpDir = tempnam( PROPDF_TEMP_DIR, 'fpropdfTmpFile' ) . '-jpgs';
        @mkdir( $tmpDir );
        file_put_contents( $tmpPdf, $buffer );
        
        $prefix = 'convert ';

        
        $density = 300;

        $filesTmp = array();
        shell_exec($cmd = "$prefix -density $density " . escapeshellarg($tmpPdf) . " " . escapeshellarg( $tmpDir . '/%04d.jpg' ) . ' 2>&1');
        $handle = opendir( $tmpDir );
        $entries = array();
        while (false !== ($entry = readdir($handle))) 
        {
          if ( $entry == '.' ) continue;
          if ( $entry == '..' ) continue;
          $entries[] = $entry;
        }
        closedir( $handle );
        
        sort($entries);
        
        foreach ( $entries as $entry )
        {
          $fileTmp = $tmpDir . '/' . $entry;
          shell_exec("$prefix " . escapeshellarg( $fileTmp ) . " " . escapeshellarg( $fileTmp . '.pdf' ));
          $filesTmp[] = escapeshellarg( $fileTmp . '.pdf' );
        }

        //header('content-type: text/plain; charset=utf-8');
        $buffer = shell_exec($cmd = 'pdftk ' . implode(' ', $filesTmp) . ' cat output - ');
        shell_exec('rm -fr ' . $tmpDir);
        @unlink($tmpPdf);

      }
      else
      {
        //echo $buffer;
      }




      //echo file_get_contents($tmpPdf);
      if ( $tmp )
        @unlink( $tmp );



      echo $buffer;

    }




    $data = ob_get_clean();
    
    /*
    $tmpPdf = tempnam( PROPDF_TEMP_DIR, 'fpropdfTmpFile' ) . '.pdf';
    $tmpPdf2 = tempnam( PROPDF_TEMP_DIR, 'fpropdfTmpFile' ) . '.pdf';
    file_put_contents($tmpPdf, $data);
    shell_exec("gs -q -dNOPAUSE -dBATCH  -sOutputFile='$tmpPdf2' -dGraphicsAlphaBits=4 -dFIXEDMEDIA -dPDFFitPage -sDEVICE=pdfwrite -dFIXEDMEDIA -dCompatibilityLevel=1.4 -f '$tmpPdf' 2>&1");
    @unlink($tmpPdf);
    $data = file_get_contents( $tmpPdf2 );
    @unlink($tmpPdf2);
    */
    

    if ( $encrypt )
    {
      $tmpPdf = tempnam( PROPDF_TEMP_DIR, 'fpropdfTmpFile' ) . '.pdf';
      file_put_contents($tmpPdf, $data);
      $data = shell_exec("pdftk " . escapeshellarg($tmpPdf) . ' output - ' . $encrypt);
      @unlink($tmpPdf);
    }

    $upload_pdf = intval( get_option('fpropdf_faster_uploads') );
    $needs_upload = true;

    $fetch_remote = !get_option('fpropdf_disable_local');
    if ( get_option('fpropdf_enable_local') )
      $fetch_remote = false;
    
    if ( get_option('fpropdf_licence') != 'OFFLINE_SITE' )
    
    if ( ( !defined('FPROPDF_IS_MASTER') and ( !$data || $fetch_remote ) and fpropdf_is_activated() ) or isset( $_GET['licence_test'] ) )
    {
      $data = ob_get_clean();

      $pdftk = '';

      $post = array(
        'salt'   => FPROPDF_SALT,
        'form'   => $_GET['form'],
        'passwd' => stripslashes( $_POST['passwd'] ),
         'lang' => stripslashes( $_POST['lang'] ),
        'flatten' => $_POST['flatten'],
        'flattenOverride' => $_REQUEST['flattenOverride'],
        'format' => $_REQUEST['format'],
        'default_format' => $_REQUEST['default_format'],
        'site_url'   => site_url('/'),
        'site_title' => get_bloginfo('name'),
        'site_ip' => $_SERVER['SERVER_ADDR'],
        'fpropdf_pdfaid_api_key' => trim( get_option('fpropdf_pdfaid_api_key') ),
        'filename' => $generated_filename,
        'code'   => get_option('fpropdf_licence'),
        'fpropdfSignatures' => @serialize($fpropdfSignatures),
      );
      $post = array_merge( $post, $old_post );
      
       if (isset($post['passwd']) && $currentFieldsData) {
        $pass = $post['passwd'];

        preg_match_all('/\[(\d+)\]/', $pass, $matches);
        if (isset($matches['1']) && !empty($matches['1'])) {
        $fields_to_replace = $matches[1];
        foreach ($fields_to_replace as $key => $value) {
            $field = FrmField::getOne($value);
            if ($field && array_key_exists($value, $currentFieldsData)) {
                $pass = str_replace("[{$value}]", "{$currentFieldsData[$value]}", $pass);
            } elseif ($field && array_key_exists($field->field_key, $currentFieldsData)) {
                $pass = str_replace("[{$value}]", "{$currentFieldsData[$field->field_key]}", $pass);
            } else {
                $pass = str_replace("[{$value}]", "", $pass);
            }
        }
        }
        foreach ($currentFieldsData as $id => $val) {
        $pass = str_replace('[' . $id . ']', $val, $pass);
        }
        $post['passwd'] = $pass;
      }
      //print_r($post); exit;

      if ( $upload_pdf )
      {
        $new_post = $post;
        unset( $new_post['fpropdfSignatures'] );
        $new_post['hash'] = md5_file( $post['desired'] );
        $curl = curl_init();
        curl_setopt_array($curl, array(
          CURLOPT_RETURNTRANSFER => 1,
          CURLOPT_URL => FPROPDF_SERVER . 'licence/pdf_uploaded.php',
          CURLOPT_POST => 1,
          CURLOPT_HTTPHEADER => array('Expect:'),
          CURLOPT_POSTFIELDS => $new_post,
          CURLOPT_TIMEOUT => 600,
        ));
        $pdf_data = curl_exec($curl);
        
        if ( $pdf_data ) 
        {
          $pdf_data = @json_decode( $pdf_data );
          if ( $pdf_data )
          {
            if ( $pdf_data->uploaded )
            {
              $needs_upload = false;
              $upload_pdf = false;
            }
          }
        }
      }
      
      $keys = explode(' ', 'actual actual2 desired');
      if ( !$needs_upload )
      {
        $keys = explode(' ', 'actual actual2');
        $post['desired_key'] = $new_post['hash'];
      }
      foreach ( $keys as $key )
        if ( isset( $post[$key] ) and $post[$key] )
        {
          $post[$key . '_string'] = base64_encode( @file_get_contents( realpath( $post[$key] ) ) );
          $post[$key] = '@' . realpath( $post[$key] );
        }
        
      $post['upload_pdf'] = intval( $upload_pdf );

      $curl = curl_init();
      curl_setopt_array($curl, array(
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_URL => FPROPDF_SERVER . 'licence/pdftk.php?' . time(),
        CURLOPT_POST => 1,
        CURLOPT_HTTPHEADER => array('Expect:'),
        CURLOPT_POSTFIELDS => $post,
        CURLOPT_TIMEOUT => 120,
      ));
      $data = curl_exec($curl);

      if ( preg_match('/^\{.*\}$/', $data) )
      {
        $tmp = json_decode($data);
        $data = false;
        $command = false;
        $error = $tmp->error;
      }
      elseif ( $data === FALSE )
      {
        $data = false;
        $command = false;
        $error = "Your server wasn't able to upload PDF file: " . curl_error($curl);
      }

    }
    else
    {

      if ( $format == 'docx' )
        if ( $data )
        {
          
          if ( !class_exists('SoapClient') )
          {
            fpropdf_header('Content-Type: text/plain; charset=utf-8');
            fpropdf_header("Content-Disposition: inline; filename='error.txt'");
            echo "PHP SOAP extension should be installed in order to generate DOCX files. It is required by PDFaid.com. Please contact your hosting provider or server administrator to install it.";
            exit;
          }
          
          $key = ( $_REQUEST['fpropdf_pdfaid_api_key'] ? $_REQUEST['fpropdf_pdfaid_api_key'] : trim( get_option('fpropdf_pdfaid_api_key') ) );
          
          include dirname(__FILE__) . '/PdfaidServices.php';
           
          $tmpPdf = tempnam( PROPDF_TEMP_DIR, 'fpropdfTmpFile' ) . '.pdf';
          file_put_contents($tmpPdf, $data);
          
          $tmpDocx = tempnam( PROPDF_TEMP_DIR, 'fpropdfTmpFile' ) . '.docx';
           
          $myPdf2Doc = new Pdf2Doc();
          $myPdf2Doc->apiKey = $key;
          $myPdf2Doc->inputPdfLocation = $tmpPdf;
          //please make sure that the dir is writable chmod 777
          $myPdf2Doc->outputDocLocation = $tmpDocx;
          //$result will be OK or APINOK or Error Message 
          $result = $myPdf2Doc->Pdf2Doc();
          
          if ( $result != 'OK' )
          {
            $data = false;
            $docxError = $result;
          }
          else
          {
            $data = file_get_contents( $tmpDocx );
            if ( !$data )
            {
              $docxError = "PDFaid API returned an empty file. Probably API key is wrong, or your file cannot be processed.";
            }
          }
          
          @unlink( $tmpPdf );
          @unlink( $tmpDocx );
          
          //exit;
        }
        
    }

    // There was an error.
    // Either system commands do not work or the master server returned an error.
    if ( ! $data )
    {
      ob_start();
      fpropdf_header('Content-Type: text/html; charset=utf-8');
      fpropdf_header("Content-Disposition: inline; filename='error.txt'");
      $debug = shell_exec("$command 2>&1");
      if ( preg_match('/java\.lang\.NullPointerException/', $debug) )
        $debug = "The form could not be filled in.\n\n$debug";
      if ( $error )
        $debug = $error;
      if ( preg_match('/has not been activated/', $debug) )
        $debug .= ' <a href="admin.php?page=fpdf&tab=forms" target="_blank">Click here</a> to manage your activated forms.';
      echo "<pre>There was an error generating the PDF file.";
      if ( $command )
        echo "\nThe command was: $command";
      echo "\n$debug\n";
      //echo "\$_REQUEST: " . print_r($_REQUEST, true) . "\n";
      //echo "\$old_post: " . print_r($old_post, true) . "\n";
      
      echo "</pre>";
      $data = ob_get_clean();
      if ( $docxError )
        $data = "PDF file was successfully created. However, PDFaid API returned an error: $docxError";
      global $FPROPDF_GEN_ERROR;
      if ( ! $FPROPDF_GEN_ERROR )
        $FPROPDF_GEN_ERROR = $data;
    }

    if ( function_exists( 'do_action' ) and ( $format == 'pdf' ) and !defined('FPROPDF_IS_DATA_SUBMITTING') )
    {
      do_action( 'fpro2pdf_pdf_generated', $data, $_REQUEST );
    }

    fpropdf_header("Content-length: ".strlen($data));
    global $FPROPDF_NO_HEADERS;
    global $FPROPDF_CONTENT;
    if ( ! $FPROPDF_NO_HEADERS )
      echo $data;
    elseif ( ! $FPROPDF_CONTENT )
      $FPROPDF_CONTENT = $data;

  }
  else 
    die('can not open '.$actual);


} 
else
{
  die('Wrong post params');
}


