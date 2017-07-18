<?php

if ( !function_exists('fpropdf_custom_capitalize') )
{
  function fpropdf_custom_capitalize( $m )
  {
    $s = $m[1];
    $s = function_exists('mb_strtoupper') ? mb_strtoupper($s) : strtoupper($s);
    return $s;
  }
}

if ( !function_exists('fpropdf_format_field' ) )
{
  
  function fpropdf_format_field( $v, $_format, $level = 0 )
  {
    
    if ( $level > 1 )
      return $v;
    
    global $currentLayout;
    
    $key = $_format[ 0 ];
    $format = $_format[ 1 ];
    
    if ( $format == 'html' )
    {
      $v = strip_tags($v . '');
      $format = 'removeEmptyLines';
    }

    $additional = apply_filters( 'fpropdf_additional_formatting', array() );
    foreach ( $additional as $title => $callback )
    {
      if ( ! is_callable( $callback ) and ! function_exists( $callback ) )
        continue;
      if ( $format != $title )
        continue;
      $v = call_user_func( $callback, $v );
    }

    switch ( $format )
    {

      /*
        'number_f1' => 'Number 1,000.00',
        'number_f2' => 'Number 1.000,00',
        'number_f3' => 'Number 1 000.00',
        'number_f4' => 'Number 1 000,00',
        'number_f5' => 'Number 1,000',
        'number_f6' => 'Number 1.000',
        'number_f7' => 'Number 1 000',
        'number_f8' => 'Number 1000,00',
        'number_f9' => 'Number 1. 000, 00',
        'number_f10' => 'Number 1, 000. 00',
      */
      
      case 'number_f1': $v = number_format( $v, 2, ".", "," ); break;
      case 'number_f2': $v = number_format( $v, 2, ",", "." ); break;
      case 'number_f3': $v = number_format( $v, 2, ".", " " ); break;
      case 'number_f4': $v = number_format( $v, 2, ",", " " ); break;
      case 'number_f5': $v = number_format( $v, 0, ".", "," ); break;
      case 'number_f6': $v = number_format( $v, 0, ".", "." ); break;
      case 'number_f7': $v = number_format( $v, 0, ".", " " ); break;
      case 'number_f8': $v = number_format( $v, 2, ",", "" ); break;
      case 'number_f9': $v = number_format( $v, 2, ", ", ". " ); break;
      case 'number_f10': $v = number_format( $v, 2, ". ", ", " ); break;

      case 'removeEmptyLines':
        $v = $v . '';
        $v = explode("\n", $v);
        foreach ($v as $_k => $_v)
        {
          $_v = str_replace("\r", "", $_v);
          $_v = preg_replace('/(^\s+)|(\s+$)/us', '', $_v);
          if ( strlen($_v) )
            $v[ $_k ] = $_v;
          else
            unset( $v[ $_k ] );
        }
        $v = array_values($v);
        $v = implode("\n", $v);
        break;

      case 'label':
        $fieldId = false;
        foreach ( $currentLayout['data'] as $__v )
        {
          if ( $__v[1] == $key )
          {
            
            $fieldId = $__v[0];
            global $wpdb;
            $query2  = "SELECT * FROM `".$wpdb->prefix."frm_fields` WHERE `id` = " . intval( fpropdf_field_key_to_id( $fieldId ) );
            $row2 = $wpdb->get_row( $query2, ARRAY_A );
            
            //print_r($row2);
            if ( $row2 )
              if ( in_array($row2['type'], explode(' ', 'select checkbox radio') ) )
              {
                $opts = @unserialize( $row2['options'] );
                //print_r($opts);
                foreach ( $opts as $_v )
                {
                  if ( ! is_array( $v ) )
                  {
                    if ( $_v['value'] == $v )
                    {
                      $v = $_v['label'];
                      break;
                    }
                  }
                  else
                  {
                    foreach ( $v as $v_k => $v_v )
                    {
                      if ( $_v['value'] == $v_v )
                      {
                        $v[ $v_k ] = $_v['label'];
                        
                      }
                    }
                  }
                }
                
              }
          }
        }
        
        if ( is_array($v) )
        {
          $_opts = @json_decode( $_format[ 3 ] );
          if ( $_opts and is_array( $_opts ) and count($_opts) )
          {
            foreach( $v as $_k => $_v )
              if ( !in_array( $_v, $_opts ) )
                unset( $v[ $_k ] );
          }
        }
        else
        {
          $v = str_replace("\r", "", $v);
        }
        
        break;

      case 'signature';
        global $fpropdfSignatures;
        if ( !$fpropdfSignatures )
          $fpropdfSignatures = array();
        
        $v = @unserialize($v);
        if (isset($v['typed']) && !isset($v['output']) && is_serialized($v['typed'])) {
            $v['output'] = $v['typed'];
        }
        $v = @serialize($v);
          
        $fpropdfSignatures[] = array(
          'data' => $v,
          'alignment' => $_format[ 4 ],
          'field' => $key,
        );
        
        $v = '';
        break;

      case 'curDate':
        $v = date('m/d/y');
        break;

      case 'curDate2':
        $v = date('d/m/Y');
        break;

      case 'curDate3':
        $v = date('m/d/Y');
        break;

      case 'curDate4':
        $v = date('Y/m/d');
        break;

      case 'curDate5':
        $v = date('d-m-Y');
        break;
        
      case 'curDate6':
        $v = date('d.m.Y');
        break;
        
      case 'curDate7':
        $v = date_i18n( 'j. F Y', time() );
        break;

      case 'repeatable':
      case 'repeatable2':
        $v = @unserialize($v);
        $vals = array();
        try
        {
          if ( !$v or !is_array($v) )
            throw new Exception('Not an array');

          foreach ( $v as $id )
          {

            $string = $_format[ 2 ];

            global $wpdb;
            $query  = "SELECT * FROM `".$wpdb->prefix."frm_item_metas` WHERE `item_id` = " . intval( $id );
            $rows = $wpdb->get_results( $query, ARRAY_A );
            if ( ! $rows )
              $rows = array();
            foreach ($rows as $row)
            {
              //$data [ $row['id'] ] = $row['value'];
              $key = $row['field_id'];
              $this_field_key = "unknown_field_key";
              $val = $row['meta_value'];
              $val_label = $val;
              
              if ( true )
              {
                $_tmp = @unserialize( $val_label );
                if ( $_tmp and is_array($_tmp) and count($_tmp) )
                  $val_label = implode(', ', $_tmp);
              }
                
              $query2  = "SELECT * FROM `".$wpdb->prefix."frm_fields` WHERE `id` = " . intval( $key );
              $row2 = $wpdb->get_row( $query2, ARRAY_A );
              if ( $row2 )
              {
                $this_field_key = $row2['field_key'];
                if ( in_array($row2['type'], explode(' ', 'select checkbox radio') ) )
                {
                  $opts = @unserialize( $row2['options'] );
                  foreach ( $opts as $v )
                  {
                    if ( $v['value'] == $val)
                    {
                      $val_label = $v['label'];
                      $_tmp = @unserialize( $val_label );
                      if ( $_tmp and is_array($_tmp) and count($_tmp) )
                        $val_label = $_tmp[0];
                    }
                  }
                }
              }
                
              $string = str_replace(':Show label for checkbox/select/radio]', ':label]', $string);
                
              $string = str_replace('['.$key.']', $val, $string);
              $string = str_replace('['.$key.':label]', $val_label, $string);
              $string = str_replace('['.$this_field_key.']', $val, $string);
              $string = str_replace('['.$this_field_key.':label]', $val_label, $string);
              
              $_formats = array( 
                'dd' => 'd', 
                'mm' => 'm', 
                'yyyy' => 'Y', 
                'yy' => 'y', 
              );
              if ( preg_match('/^\d{4}-\d{2}-\d{2}$/', $val) )
                foreach ( $_formats as $format1 => $date1 )
                  foreach ( $_formats as $format2 => $date2 )
                    foreach ( $_formats as $format3 => $date3 )
                    {
                      $f = $format1 . '/' . $format2 . '/' . $format3;
                      $datef = $date1 . '/' . $date2 . '/' . $date3;
                      $string = str_ireplace('['.$key.':' .$f . ']', date($datef, strtotime($val)), $string);
                      $string = str_ireplace('['.$this_field_key.':' .$f . ']', date($datef, strtotime($val)), $string);
                    }
                    
                    
              $_formats = array(
                'none' => '(no formatting)',
                'tel' => 'Telephone',
                'address' => 'Address',
                'credit_card' => 'Credit Card',
                'date' => 'Date MM/DD/YY',
                'date2' => 'Date DD/MM/YYYY',
                'date3' => 'Date MM/DD/YYYY',
                'date4' => 'Date YYYY/MM/DD',
                'date5' => 'Date DD-MM-YYYY',
                'date6' => 'Date DD.MM.YYYY',
                'date7' => 'Date DD. month year',
                'capitalize' => 'Capitalize',
                'capitalizeAll' => 'CAPITALIZE ALL',
                'returnToComma' => 'Carriage return to comma',
				 'returnToCarriage' => 'Comma to carriage return',
                //'label' => 'Show label for checkbox/select/radio',
                'removeEmptyLines' => 'Remove empty lines in text',
                'html' => 'Remove HTML tags',
                'number_f1' => 'Number 1,000.00',
                'number_f2' => 'Number 1.000,00',
                'number_f3' => 'Number 1 000.00',
                'number_f4' => 'Number 1 000,00',
                'number_f5' => 'Number 1,000',
                'number_f6' => 'Number 1.000',
                'number_f7' => 'Number 1 000',
                'number_f8' => 'Number 1000,00',
                'number_f9' => 'Number 1. 000, 00',
                'number_f10' => 'Number 1, 000. 00',
                
              );
              
              foreach ( $_formats as $format_key => $format_string )
              {
                $val_formatted = fpropdf_format_field( $val, array( $key, $format_key ), $level + 1 );
                $string = str_ireplace('['.$key.':' . $format_string . ']', $val_formatted, $string);
                $string = str_ireplace('['.$this_field_key.':' . $format_string . ']', $val_formatted, $string);
              }
              
              $_formats = array(
                'curDate' => 'Current Date MM/DD/YY',
                'curDate2' => 'Current Date DD/MM/YYYY',
                'curDate3' => 'Current Date MM/DD/YYYY',
                'curDate4' => 'Current Date YYYY/MM/DD',
                'curDate5' => 'Current Date DD-MM-YYYY',
                'curDate6' => 'Current Date DD.MM.YYYY',
                'curDate7' => 'Current Date DD. month year',
              );
              
              foreach ( $_formats as $format_key => $format_string )
              {
                $val_formatted = fpropdf_format_field( '', array( $key, $format_key ), $level + 1 );
                $string = str_ireplace('[' . $format_string . ']', $val_formatted, $string);
              }
              
            }

            $vals[] = $string;
          }
        }
        catch (Exception $e)                
        {
          
        }
        
        foreach ( $vals as $_k => $_v )
        {
          $_v = preg_replace('/\[[^\]]+?\]/', '', $_v);
          $_v = preg_replace('/\[[^\]\:]+\:[^\]]+\]/', '', $_v);
          $_v = str_replace("\r", "", $_v);
          $vals[ $_k ] = $_v;
        }
        
        if ( $format == 'repeatable2' )
        {
          
          $_opts = @json_decode( $_format[ 3 ] );
          if ( $_opts and is_array( $_opts ) and count($_opts) )
          {
            foreach ( $vals as $val_key => $vvals )
            {
              if ( in_array( $vvals, $_opts ) )
                continue;
              $vals[ $val_key ] = '';
              $vvals = explode(', ', $vvals);
              foreach ( $vvals as $__k => $__v )
              {
                if ( in_array( $__v, $_opts ) )
                {
                  $vals[ $val_key ] = $__v;
                }
              }
            }
          }
          
          global $separateRepeatable;
          global $currentKey;
          if ( ! $separateRepeatable )
            $separateRepeatable = array();
          $separateRepeatable[ $currentKey ] = $vals;
          $v = '';
          
        }
        else
        {
          $v = implode('', $vals);
        }
        //$v = preg_replace('/\[(\d+?)\]/', '[Field \1 not found]', $v);
        //$v = preg_replace('/\[(\d+?)\:label\]/', '[Label of field \1 not found]', $v);
        break;

      case 'tel':
        $v2 = preg_replace('/[^0-9]+/', '', $v);
        $v2 = intval($v2);
        $v2 = sprintf("%010d", $v2);
        if ( preg_match( '/(\d{3})(\d{3})(\d{4})$/', $v2,  $matches ) )
          $v = $matches[1] . '-' .$matches[2] . '-' . $matches[3];
        break;

      case 'address':
        $v = @unserialize($v);
        $string = $_format[ 5 ];
        
        foreach ($v as $key => $val) {
          $string = str_replace('['.$key.']', $val, $string);
        }
		/* for remove empty [] shorcode and line feed issue */
		$string_array = preg_split("/[\n ]+/", $string);
		$text_inside=array();
		foreach($string_array as $key =>$val){
			for($i=0;$i<strlen($val);$i++)
			 {
			  if($val[$i]=='[')
			  {
			   $t1="";
			   $i++;
			   while($val[$i]!=']')
			   {
				$t1.=$val[$i];
				$i++;
			   }
			   if($val!=''){
				   unset($string_array[$key]);
			   }
			  }
			 }
		} 
		$string = implode(' ',$string_array);
        $v = $string;
        break;

      case 'credit_card':
        $v = @unserialize($v);
        $string = $_format[ 6 ];
        
        foreach ($v as $key => $val) {
          $string = str_replace('['.$key.']', $val, $string);
        }
        
        $v = $string;
        break;

      case 'date':
        if ( preg_match('/^(\d{4})\-(\d{2})\-(\d{2})/', $v, $m) )
          $v = $m[2] . '/' . $m[3] . '/' . substr($m[1], 2, 4);
        break;

      case 'date2':
        if ( preg_match('/^(\d{4})\-(\d{2})\-(\d{2})/', $v, $m) )
          $v = $m[3] . '/' . $m[2] . '/' . $m[1];
        break;

      case 'date3':
        if ( preg_match('/^(\d{4})\-(\d{2})\-(\d{2})/', $v, $m) )
          $v = $m[2] . '/' . $m[3] . '/' . $m[1];
        break;


      case 'date4':
        if ( preg_match('/^(\d{4})\-(\d{2})\-(\d{2})/', $v, $m) )
          $v = $m[1] . '/' . $m[2] . '/' . $m[3];
        break;

      case 'date5':
        if ( preg_match('/^(\d{4})\-(\d{2})\-(\d{2})/', $v, $m) )
          $v = $m[3] . '-' . $m[2] . '-' . $m[1];
        break;
        
      case 'date6':
        if ( preg_match('/^(\d{4})\-(\d{2})\-(\d{2})/', $v, $m) )
          $v = $m[3] . '.' . $m[2] . '.' . $m[1];
        break;
        
      case 'date7':
        if ( preg_match('/^(\d{4})\-(\d{2})\-(\d{2})/', $v, $m) )
          $v = date_i18n( 'j. F Y', strtotime( $m[1] . '-' . $m[2] . '-' . $m[3] ) );
        break;

      case 'returnToComma':
        $v = str_replace("\r", "", $v);
        $v = str_replace("\n", ", ", $v);
        $v = preg_replace('/ +/', ' ', $v);
        $v = preg_replace('/\, +$/', '', $v);
        break;
		
	case 'returnToCarriage':
      $v = str_replace("", "\r", $v);
      $v = str_replace(", ", "\n", $v);
      $v = preg_replace('/ +/', ' ', $v);
      $v = preg_replace('/\, +$/', '', $v);
      break;	

      case 'capitalize':
        $v = preg_replace_callback('/(^[a-z]| [a-z])/u', 'fpropdf_custom_capitalize', strtolower( $v ));
        break;
        
      case 'capitalizeAll':
        $v = function_exists('mb_strtoupper') ? mb_strtoupper( $v ) : strtoupper( $v );
        break;

      default:

        if ( is_array($v) )
        {
          $_opts = @json_decode( $_format[ 3 ] );
          if ( $_opts and is_array( $_opts ) and count($_opts) )
          {
            $data[ $dataKey ][ 1 ] = true;
            foreach( $v as $_k => $_v )
              if ( !in_array( $_v, $_opts ) )
                unset( $v[ $_k ] );
          }
        }
        else
        {
          $v = str_replace("\r", "", $v);
        }
        break;

    }
    
    if ( preg_match('/^FPROPDF\_IMAGE\:(.*)$/', $v, $m ) )
    {
      global $fpropdfSignatures;
      if ( !$fpropdfSignatures )
        $fpropdfSignatures = array();
        
      $fpropdfSignatures[] = array(
        'data' => serialize( base64_encode(file_get_contents( ABSPATH . $m[1] )) ),
        'field' => $key,
        'alignment' => $_format[ 4 ],
        'is_file' => basename( $m[1] ),
      );
      
      $v = '';
    }
    
    if ( preg_match('/^FPROPDF\_IMAGE\_FIELD\:([^\:]+)\:([^\:]+)$/', $v, $m ) )
    {
      global $fpropdfSignatures;
      if ( !$fpropdfSignatures )
        $fpropdfSignatures = array();
        
      $fpropdfSignatures[] = array(
        'data' => serialize( $m[2] ),
        'field' => $key,
        'alignment' => $_format[ 4 ],
        'is_file' => $m[1],
      );
      
      $v = '';
    }
    
    return $v;
    
  }
  
}