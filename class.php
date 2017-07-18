<?php

@include_once dirname( __FILE__ ) . '/format.php';

class FDFMaker
{
  function makeInflatablesApp($data = false, $remote = false)
  {
    // let's fill default fields
    $defaults = array(
      109 => '/No', 105 => '/No', 104 => '/No', 103 => '/No', 3660 => '/', 3650 => '/Yes', 98 => '/',
      97 => '/', 96 => '/', 399 => '1', 392 => 'S', 391 => 'Rentals', 390 => '1', 88 => '100%', 87 => '0',
      85 => '/', 84 => '/Tenant', 83 => '/Inside', 383 => '5,000', 382 => '300,000', 381 => '1,000,000',
      380 => '1,000,000', 77 => '1', 76 => '1', 379 => '2,000,000', 378 => '2,000,000', 377 => '/Yes',
      376 => '/', 373 => '0', 372 => '/Yes', 371 => '0', 370 => '/Yes', 67 => '/', 66 => '/', 65 => '/',
      64 => '/', 63 => '/', 62 => '/', 61 => '/', 567 => '/', 566 => 'None', 565 => '/No', 564 => '/No',
      401 => 'U', 563 => '/No', 400 => 'Inflatables', 562 => '/No', 561 => '/No', 367 => 'None',
      560 => '/No', 366 => '/', 365 => '/', 559 => '/No', 558 => '/No', 557 => '/No', 556 => '/No',
      555 => '/No', 554 => '/No', 553 => '/No', 552 => '/No', 358 => '/', 551 => '/No', 357 => '/',
      550 => '/No', 350 => '/', 48 => 'Y', 45 => '/AgencyBill', 549 => '/No', 548 => '/No', 547 => '/No',
      546 => '/No', 349 => '/Yes', 34 => '/Quote', 33 => '/', 32 => '/', 31 => '/', 136 => '/Yes',
      'busops' => 'Inflatable Rentals with \(\) Inflatables', 292 => '/', 291 => '/', 25 => '/Yes',
      3690 => '/Yes', 22 => '/', 'Waiver' => 'Yes', 9 => '619-423-7172', 4 => 'Imperial Beach, CA 91932',
      3 => '1233 Palm Avenue', 2 => 'Ideal Choice Insurance Agency, Inc.', 3680 => '/', 10 => '619-374-2317',
      115 => 'None', 114 => '/No', 113 => '/No', 112 => '/No', 111 => '/No', 110 => '/No', 3670 => '/');

    if( !is_array($data) )
      $data = array();

    foreach ( $defaults as $k => $v )
    {
      $found = false;
      foreach ( $data as $key => $values )
        if ( ( $values[ 0 ] == $k ) and $values[1] )
          $found = true;
      if ( ! $found )
        $data[] = array( $k, $v );

    }

    // format filename
    $file = $remote ? $remote : 'InflatableApp.pdf';

    // create FDF
    return ($this->makeFDF($data, $file));
  }

  function makeBusinessQuote($data = false, $remote = false)
  {
    // defaults for 125&126
    $defaults = array(
      109 => '/No', 108 => '/No', 107 => '/No', 106 => '/No', 105 => '/No', 104 => '/No', 103 => '/No',
      3660 => '/', 3650 => '/Yes', 98 => '/', 97 => '/', 96 => '/', 399 => '1', 392 => 'S', 391 => 'Rentals',
      390 => '1', 88 => '100%', 87 => '0', 85 => '/', 84 => '/Tenant', 83 => '/Inside', 383 => '5,000',
      382 => '300,000', 381 => '1,000,000', 380 => '1,000,000', 77 => '1', 76 => '1', 379 => '2,000,000',
      378 => '2,000,000', 377 => '/Yes', 376 => '/', 373 => '0', 372 => '/Yes', 371 => '0', 370 => '/Yes',
      67 => '/', 66 => '/', 65 => '/', 64 => '/', 63 => '/', 62 => '/', 61 => '/', 567 => '/', 566 => 'None',
      565 => '/No', 564 => '/No', 401 => 'U', 563 => '/No', 400 => 'Inflatables', 562 => '/No', 561 => '/No',
      367 => 'None', 560 => '/No', 366 => '/', 365 => '/', 559 => '/No', 558 => '/No', 557 => '/No', 556 => '/No',
      555 => '/No', 554 => '/No', 553 => '/No', 552 => '/No', 358 => '/', 551 => '/No', 357 => '/', 550 => '/No',
      350 => '/', 48 => 'Y', 45 => '/AgencyBill', 549 => '/No', 548 => '/No', 547 => '/No', 546 => '/No', 349 => '/Yes',
      34 => '/Quote', 33 => '/', 32 => '/', 31 => '/', 136 => '/Yes', 292 => '/', 291 => '/', 25 => '/Yes',
      3690 => '/Yes', 22 => '/', 9 => '619-423-7172', 4 => 'Imperial Beach, CA 91932', 3 => '1233 Palm Avenue',
      2 => 'Ideal Choice Insurance Agency, Inc.', 3680 => '/', 10 => '619-374-2317', 115 => 'None', 114 => '/No',
      113 => '/No', 112 => '/No', 111 => '/No', 110 => '/No', 3670 => '/');


    if( !is_array($data) )
      $data = array();

    foreach ( $defaults as $k => $v )
    {
      $found = false;
      foreach ( $data as $key => $values )
        if ( ( $values[ 0 ] == $k ) and $values[1] )
          $found = true;
      if ( ! $found )
        $data[] = array( $k, $v );

    }

    // format filename
    $file = $remote ? $remote : 'BusinessQuote.pdf';

    // create FDF
    return ($this->makeFDF($defaults, $file));
  }

  // create FDF from array
  function makeFDF($data, $file, $unicode = false)
  {
      
    if ($unicode) {
            $str_replace = array(
                'č'
            );
            $str_unicode = array(
                '\u010D'
            );
     } 
      
    $cr   = chr(hexdec('0a')); // use carriage return explicitly

    // make header
    $fdf  = '%FDF-1.2'.$cr.'%'.chr(hexdec('e2')).chr(hexdec('e3')).chr(hexdec('cf')).chr(hexdec('d3')).$cr;
    $fdf .= '1 0 obj '.$cr.'<<'.$cr.'/FDF '.$cr.'<<'.$cr.'/Fields [';

    //if ( isset( $_GET['testing'] ) ) { print_r($data); exit; }

    global $currentLayout;
    if ( $currentLayout )
    {

      $formats = $currentLayout['formats'];
      //print_r($formats); exit;
      foreach ( $formats as $_format )
      {

        $key = $_format[ 0 ];
        $format = $_format[ 1 ];
        
        //print_r($formats); exit;

        //$foundFormat = false;

        foreach ( $data as $dataKey => $values )
        {

          if ( ($values[ 0 ]) != ($key) )
            continue;

          $v = $values[ 1 ];
		 if(is_array($v) && $_format[1]=='returnToCarriage'){
			 $v = implode(', ',$v);
			}
          global $currentKey;
          $currentKey = preg_replace( '/1$/', '', $key );

          $v = fpropdf_format_field( $v, $_format );

          if ( is_array( $_format ) )
            if ( $_format[1] == 'repeatable2' )
              continue;

          $data[ $dataKey ][ 1 ] = $v;
          
          global $currentFieldsData;
          if ( ! $currentFieldsData ) 
            $currentFieldsData = array();
          
          foreach ( $currentLayout['data'] as $__v )
            if ( ($__v[1]) == ($key) )
              $currentFieldsData[ $__v[0] ] = $v;

        }

        //if ( ! $formatFound )
          //if ( $format == 'curDate' )


      }

      $currentLayout = false;
    }

    global $separateRepeatable;
    if ( $separateRepeatable )
    {
      foreach ( $separateRepeatable as $key => $vals )
        foreach ( $vals as $index => $val )
        {
          $data[] = array( $key . ( $index + 1 ), $val );
        }
    }
    // print_r( $data ); exit;

    // generate fields
    foreach($data as $values)
    {
      $index = $values[ 0 ];
      $value = $values[ 1 ];

      if ( !is_array($value) )
        $_values = array( $value );
      else
      {
        if ( $values[ 2 ] )
          $_values = $value;
        else
          $_values = array( implode(', ', $value) );
      }

      foreach ( $_values as $value )
      {
        
        if ($unicode) {
                    $value = str_replace($str_replace, $str_unicode, $value);
        }
          
        if ( function_exists('htmlspecialchars_decode') )
          $value = htmlspecialchars_decode( $value );
        if ( function_exists('mb_convert_encoding') )
          $value = mb_convert_encoding( $value, 'UTF-16BE' );
        elseif ( function_exists('iconv') )
          $value = iconv( 'UTF-8', 'UTF-16BE', $value );
        $value = chr(0xfe) . chr(0xff) . str_replace(array('\\', '(', ')'), array('\\\\', '\(', '\)'), $value);
        
        if ( function_exists('mb_convert_encoding') )
          $index = chr(0xfe) . chr(0xff) . mb_convert_encoding( $index, 'UTF-16BE' );
        elseif ( function_exists('iconv') )
          $index = chr(0xfe) . chr(0xff) . iconv( 'UTF-8', 'UTF-16BE', $index );
        $index = str_replace(array('\\', '(', ')'), array('\\\\', '\(', '\)'), $index); 

        $fdf .= $cr.'<<';
        $fdf .= $cr.'/V ';

        if($value[0] == '/')
          $fdf .= $value;
        else $fdf .= '('.$value.')';

        $fdf .= $cr.'/T ('.$index.')';
        $fdf .= $cr.'>> ';

      }

    }

    // make footer
    $fdf .= ']'.$cr.'/ID [ <'.md5(time()).'>'.$cr.'] >> '.$cr.'>> '.$cr.' endobj '.$cr.'trailer'.$cr.$cr.'<<'.$cr.'/Root 1 0 R'.$cr.'>>'.$cr.'%%EOF'.$cr;
    //echo $fdf; exit;
    
    /*
    if ( $_GET['testing'] )
    {
      echo $fdf; exit;
    }
    */

    return $fdf;
  }
}


