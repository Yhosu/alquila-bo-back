<?php

function vardump() {
    $arg_list = func_get_args();
    foreach ( $arg_list as $variable ) {
      echo '<pre style="color: #000; background-color: #fff;">';
      echo htmlspecialchars( var_export( $variable, true ) );
      echo '</pre>';
    }
}

function decryptRsa( $crypted_text ) {
    $crypted_text = base64_decode($crypted_text);
    $key          = 'ZPNPigWvDuqvTjvqtQDb5CDUM7FTbPTj';
    $iv           = 'hP7TeIeXBZHSoYQi';
    $method       = "AES-256-CBC";
    $text         = openssl_decrypt($crypted_text, $method, $key, OPENSSL_RAW_DATA, $iv);
    return $text;
}

function encryptRsa( $text ) {
    $key        = 'ZPNPigWvDuqvTjvqtQDb5CDUM7FTbPTj';
    $iv         = 'hP7TeIeXBZHSoYQi';
    $method     = "AES-256-CBC";
    $ciphertext = openssl_encrypt($text, $method, $key, OPENSSL_RAW_DATA, $iv);
    $text       = openssl_decrypt($ciphertext, $method, $key, OPENSSL_RAW_DATA, $iv);
    return base64_encode($ciphertext);
}

function generateUuid() {
    $uuid = \Str::uuid();
    $uuidString = $uuid->toString();
    return $uuidString;
}

function split_name($name) {
    $name       = trim($name);
    $last_name  = (strpos($name, ' ') === false) ? '' : preg_replace('#.*\s([\w-]*)$#', '$1', $name);
    $first_name = trim( preg_replace('#'.preg_quote($last_name,'#').'#', '', $name ) );
    return [$first_name, $last_name];
}

function clean4search( $text, $cleanSpecialData = true ) {
    $a = 'áéíóúñüÁÉÍÓÚÑÜ';
    $b = 'aeiounuaeiounu';
    $text = utf8_decode( $text );
    $text = strtr( $text, utf8_decode( $a ), $b );
    $text = strtolower( $text );
    $text = strTolower( $text );
    if ( $cleanSpecialData ) {
        $text = preg_replace( '#[^a-z0-9@."]#', ' ', $text );
    }
    $text = trim( preg_replace( '#[[:space:]]+#', ' ', $text ) );
    $undoPos = 0;
    $undo = array();
    /* Se respetarán los textos entre comillas */
    while ( preg_match( '#"[^"]+"#', $text, $quoted ) ) {
        $undo[++$undoPos] = trim( $quoted );
        $text = str_replace( $quoted, "[:sac_undo_$undoPos:]", $text );
    }
    $text = str_replace( '"', '', $text );
    if ( $undoPos ) {
        foreach ( $undo as $undoGet => $undoText  ) {
            $text = str_replace( "[:sac_undo_$undoGet:]", $undoText, $text );
        }
    }
    return trim( $text );
}

function getTypeField( $table_name, $field ) {
    return \DB::getSchemaBuilder()->getColumnType($table_name, $field);
}

function getCardType( $card_number ) {
    return preg_match("/^5[1-5][0-9]{14}$/", $card_number) ? 'MASTERCARD' : 'VISA';
}

function getLiteralFromYearAndMonth( $date ) {
    $arrayDate = explode('/', $date);
    $month = $arrayDate[1];
    $literal = null;
    $arrayMonths = [
        '01' => 'Enero de',
        '02' => 'Febrero de',
        '03' => 'Marzo de',
        '04' => 'Abril de',
        '05' => 'Mayo de',
        '06' => 'Junio de',
        '07' => 'Julio de',
        '08' => 'Agosto de',
        '09' => 'Septiembre de',
        '10' => 'Octubre de',
        '11' => 'Noviembre de',
        '12' => 'Diciembre de'
    ];
    return $arrayMonths[$month] . ' ' . $arrayDate[0];
}

function currencyFormat(
    $number,
    $decimal = 2,
    $negativeFormat = '<span class="neg">(%s)</span>' ) {
    if ( !is_integer( $decimal ) ) {
        $decimal = 2;
    }
    if ( empty( $number ) ) {
        $number = 0;
    }
    return sprintf(
        ( $number < 0 ) ? $negativeFormat : '%s',
        number_format( abs( $number ), $decimal, '.', ',' ) );
}

function isConsecutive($numbers) {
    $numbers = array_filter(array_unique($numbers), function ($v) { return $v >= 0; });
    sort($numbers);
    for ($i = 1; $i < count($numbers); $i++) {
        if ($numbers[$i] != $numbers[$i-1] + 1) {
            return $numbers[$i-1] + 1;
        }
    }
    return false;
}

function numberToText( $number ) {
    $number = $number;
    $decimal = round($number - ($no = floor($number)), 2) * 100;
    $hundred = null;
    $digits_length = strlen($no);
    $i = 0;
    $str = array();
    $words = array(0 => '', 1 => 'uno', 2 => 'dos',
        3 => 'tres', 4 => 'cuatro', 5 => 'cinco', 6 => 'seis',
        7 => 'siete', 8 => 'ocho', 9 => 'nueve',
        10 => 'diez', 11 => 'once', 12 => 'doce',
        13 => 'trece', 14 => 'catorce', 15 => 'quince',
        16 => 'dieciseis', 17 => 'diecisiete', 18 => 'dieciocho',
        19 => 'diecinueve', 20 => 'veinte', 30 => 'treinta',
        40 => 'cuarenta', 50 => 'cincuenta', 60 => 'sesenta',
        70 => 'setenta', 80 => 'ochenta', 90 => 'noventa');
    $digits = array('', 'cientos','mil','lakh', 'crore');
    while( $i < $digits_length ) {
        $divider = ($i == 2) ? 10 : 100;
        $number = floor($no % $divider);
        $no = floor($no / $divider);
        $i += $divider == 10 ? 1 : 2;
        if ($number) {
            $plural = (($counter = count($str)) && $number > 9) ? 's' : null;
            $hundred = ($counter == 1 && $str[0]) ? ' y ' : null;
            $eval = ($number < 21) ? $words[$number].' '. $digits[$counter]. $plural.' '.$hundred:$words[floor($number / 10) * 10].' y '.$words[$number % 10]. ' '.$digits[$counter].$plural.' '.$hundred;
            if( substr(trim($eval), -1) == 'y' ) {
                $eval = substr_replace(trim($eval) ,"", -1);
            }
            if (strpos($eval, 'uno mil') !== false) {
                $eval = str_replace('uno mil', 'mil', $eval);
            } elseif(strpos($eval, 'uno cientos') !== false) {
                $eval = str_replace('uno cientos', 'ciento', $eval);
            } elseif(strpos($eval, 'cinco cientos') !== false) {
                $eval = str_replace('cinco cientos', 'quinientos', $eval);
            } elseif(strpos($eval, 'siete cientos') !== false) {
                $eval = str_replace('siete cientos', 'setecientos', $eval);
            } elseif(strpos($eval, 'nueve cientos') !== false) {
                $eval = str_replace('nueve cientos', 'novecientos', $eval);
            }
            $str [] = $eval;
        } else $str[] = null;
    }
    $Rupees = implode('', array_reverse($str));
    return ucfirst( preg_replace(['/\s+/','/^\s|\s$/'],[' ',''], ($Rupees ? $Rupees . '' : '')) );	
}

function getElemsOfArrayOfArray( $array, $key ) {
    $debts = [];
    $client_code = null;
    foreach( $array as $elem => $item ) {
        if( is_array( $item[$key] ) && !empty( $item[$key] ) ) {
            $client_code = $item['codigoCliente'] ?? null;
            $debts = array_merge($debts, $item[$key]);
        }
    }
    return [
      'client_code' => $client_code,
      'childrens'   => count( $array ),
      'debts'       => $debts
    ];
}

function likeProductsArray( $array, $field, $text ) {
    $text = clean4search( $text );
    return array_values( array_filter( $array, function ($value) use ( $field, $text ) {
        return 1 === preg_match(sprintf('/^%s$/i', preg_replace('/(^%)|(%$)/', '.*', '%'.$text.'%')), \Func::clean4search( $value[$field] ) );
    }));
}