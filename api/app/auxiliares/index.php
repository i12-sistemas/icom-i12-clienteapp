<?php

if (! function_exists('barcodeEtiqueta')) {
    function barcodeEtiqueta($ean13){
        // code, $type, $w = 2, $h = 30, $color = array(0, 0, 0), $showCode = false)
        return  \DNS1D::getBarcodePNG($ean13, 'EAN13');
        // return  $barcode = \DNS1D::getBarcodeHTML($this->ean13, 'EAN13', 1, 40, 'black', true);
    }
}


// arquivo de configuração
if (! function_exists('utf8_decode2')) {
  function utf8_decode2($value){
    $isUTF = mb_detect_encoding($value, 'UTF-8', true);
    $r = $value;
    if ($isUTF) {
        if ($isUTF !== 'UTF-8') $r = utf8_decode($value);
    } else {
        $r = utf8_decode($value);
    }

    return $r;
  }
}

if (! function_exists('getIp')) {
  function getIp(){
      return \Request::ip();
      // foreach (array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR') as $key){
      //     if (array_key_exists($key, $_SERVER) === true){
      //         foreach (explode(',', $_SERVER[$key]) as $ip){
      //             $ip = trim($ip); // just to be safe
      //             if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false){
      //                 return $ip;
      //             }
      //         }
      //     }
      // }
  }
}

if (! function_exists('validEmail')) {
    function validEmail($pEmail) {
        // Remove all illegal characters from email
        $email = filter_var($pEmail, FILTER_SANITIZE_EMAIL);
        // Validate e-mail
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $email;
        } else {
            return false;
        }
    }
}

if (! function_exists('toBool')) {
  function toBool($s) {
    $r = mb_strtolower($s);
    return ($r === 1) || ($r === true) || ($r === "1") || ($r === "true");
  }
}

if (! function_exists('special_ucwords')) {
    function special_ucwords($string) {
        $words = explode(' ', strtolower(trim(preg_replace("/\s+/", ' ', $string))));
        $return[] = ucfirst($words[0]);

        unset($words[0]);

        foreach ($words as $word)
        {
        if (!preg_match("/^([dn]?[aeiou][s]?|em)$/i", $word))
        {
        $word = ucfirst($word);
        }
        $return[] = $word;
        }

        return implode(' ', $return);
    }
}

if (! function_exists('strtoTime_HH_MM')) {
  function strtoTime_HH_MM($value) {
    try {
      $t = null;
      $s = isset($value) ? trim($value) : '';
      $s = str_replace(' ', '', $s);
      if($s==':') $s='';

      if(!($s=='')){
        $t = date('H:i', strtotime($s));
      }
    } catch (\Throwable $th) {
      $t = null;
      throw new Exception($th->getMessage());
    }
    return $t;
  }
}

if (! function_exists('isJson')) {
function isJson($string) {
  json_decode($string);
  return (json_last_error() == JSON_ERROR_NONE);
 }
}


if (! function_exists('mask')) {
function mask($str, $first, $last) {
    $len = strlen($str);
    $toShow = $first + $last;
    return substr($str, 0, $len <= $toShow ? 0 : $first).str_repeat("*", $len - ($len <= $toShow ? 0 : $toShow)).substr($str, $len - $last, $len <= $toShow ? 0 : $last);
}
}

if (! function_exists('mask_email')) {
function mask_email($email) {
    if(filter_var($email, FILTER_VALIDATE_EMAIL))
    {
        list($first, $last) = explode('@', $email);
        $first = str_replace(substr($first, '2'), str_repeat('*', strlen($first)-2), $first);
        $last = explode('.', $last);
        $last_domain = str_replace(substr($last['0'], '1'), str_repeat('*', strlen($last['0'])-1), $last['0']);
        $hide_email = $first.'@'.$last_domain.'.'.$last['1'];
        return $hide_email;
    }
}
}

if (! function_exists('formatFloat')) {
    function formatFloat($mask, $str){
        if ($str !== '') {
            $str = str_replace(" ","",$str);

            for($i=0;$i<strlen($str);$i++){
                $mask[strpos($mask,"#")] = $str[$i];
            }
        } else {
            $mask = str_replace("#", "_", $mask);
        }

        return $mask;

    }
}

if (! function_exists('ucutf8dec')) {
    function ucutf8dec($string) {
        $return = utf8_decode(mb_strtoupper($string, 'UTF-8') );
        return $return;
    }
}

if ( ! function_exists('valida_cpf') ) {
    function valida_cpf( $cpf = false ) {
        // Exemplo de CPF: 025.462.884-23

        /**
         * Multiplica dígitos vezes posições
         *
         * @param string $digitos Os digitos desejados
         * @param int $posicoes A posição que vai iniciar a regressão
         * @param int $soma_digitos A soma das multiplicações entre posições e dígitos
         * @return int Os dígitos enviados concatenados com o último dígito
         *
         */
        if ( ! function_exists('calc_digitos_posicoes') ) {
            function calc_digitos_posicoes( $digitos, $posicoes = 10, $soma_digitos = 0 ) {
                // Faz a soma dos dígitos com a posição
                // Ex. para 10 posições:
                //   0    2    5    4    6    2    8    8   4
                // x10   x9   x8   x7   x6   x5   x4   x3  x2
                //   0 + 18 + 40 + 28 + 36 + 10 + 32 + 24 + 8 = 196
                for ( $i = 0; $i < strlen( $digitos ); $i++  ) {
                    $soma_digitos = $soma_digitos + ( $digitos[$i] * $posicoes );
                    $posicoes--;
                }

                // Captura o resto da divisão entre $soma_digitos dividido por 11
                // Ex.: 196 % 11 = 9
                $soma_digitos = $soma_digitos % 11;

                // Verifica se $soma_digitos é menor que 2
                if ( $soma_digitos < 2 ) {
                    // $soma_digitos agora será zero
                    $soma_digitos = 0;
                } else {
                    // Se for maior que 2, o resultado é 11 menos $soma_digitos
                    // Ex.: 11 - 9 = 2
                    // Nosso dígito procurado é 2
                    $soma_digitos = 11 - $soma_digitos;
                }

                // Concatena mais um dígito aos primeiro nove dígitos
                // Ex.: 025462884 + 2 = 0254628842
                $cpf = $digitos . $soma_digitos;

                // Retorna
                return $cpf;
            }
        }

        // Verifica se o CPF foi enviado
        if ( ! $cpf ) {
            return false;
        }

        // Remove tudo que não é número do CPF
        // Ex.: 025.462.884-23 = 02546288423
        $cpf = preg_replace( '/[^0-9]/is', '', $cpf );

        // Verifica se o CPF tem 11 caracteres
        // Ex.: 02546288423 = 11 números
        if ( strlen( $cpf ) != 11 ) {
            return false;
        }

        // Captura os 9 primeiros dígitos do CPF
        // Ex.: 02546288423 = 025462884
        $digitos = substr($cpf, 0, 9);

        // Faz o cálculo dos 9 primeiros dígitos do CPF para obter o primeiro dígito
        $novo_cpf = calc_digitos_posicoes( $digitos );

        // Faz o cálculo dos 10 dígitos do CPF para obter o último dígito
        $novo_cpf = calc_digitos_posicoes( $novo_cpf, 11 );

        // Verifica se o novo CPF gerado é idêntico ao CPF enviado
        if ( $novo_cpf === $cpf ) {
            // CPF válido
            return true;
        } else {
            // CPF inválido
            return false;
        }
    }

}

if (! function_exists('ellipsis')) {
    function ellipsis($str, $maxchar = 255) {
        return mb_strimwidth($str, 0, $maxchar, "...");
    }
}

if (! function_exists('formatRS')) {
    function formatRS($float, $decimals = 2, $prefixo = 'R$') {
        $r = '';
        if($float==0){
            $r = '-';
        }else{
            $r = number_format($float , $decimals, ',' , '.');
        }

        $r = ($prefixo=='' ? '' : $prefixo . ' ') . $r;
        return $r;
    }
}

if (! function_exists('generateEANdigit')) {
    function generateEANdigit($code) {
        $weightflag = true;
        $sum = 0;
        for ($i = strlen($code) - 1; $i >= 0; $i--) {
            $sum += (int)$code[$i] * ($weightflag?3:1);
            $weightflag = !$weightflag;
        }
        return (10 - ($sum % 10)) % 10;
    }
}

if (! function_exists('utf8dec')) {
    function utf8dec($string, $forceucase = false) {
        $return = ($forceucase ? mb_strtoupper($string, 'UTF-8') : $string);
        $return = utf8_decode($return);

        return $return;
    }
}

if (! function_exists('min2hr')) {
    function min2hr($minutes, $CharHr = 'h', $MinChar = 'm') {
        $return = date('H', mktime(0,$minutes)) . $CharHr . date('i', mktime(0,$minutes)) . $MinChar;
        return $return;
    }
}

if (! function_exists('getPerc')) {
    function getPerc($vrlatual, $vlrtotal, $multiplicoCem = true, $QtdeCasaDecimal = 2) {
        if (($vrlatual==0) or ($vlrtotal==0)) {
            return 0;
            exit;
        }

        $perc = ($vrlatual / $vlrtotal);
        if ($multiplicoCem){
            $perc = $perc * 100;
        }
        $perc = number_format($perc, $QtdeCasaDecimal);

        return $perc;
    }
}


if (! function_exists('transformDate')) {
    function transformDate($value, $format = 'Y-m-d')
    {
        if ($value == '') return null;
        if ($value == '-') return null;
        try {
            return \Carbon\Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value));
        } catch (\ErrorException $e) {
            return \Carbon\Carbon::createFromFormat($format, $value);
        }
    }
}

if (! function_exists('strtoFloat')) {
    function strtoFloat($str) {
        $s = strtoupper(trim($str));
        $s = str_replace('R', '', $s);
        $s = str_replace('$', '', $s);
        $s = str_replace('.', '', $s);
        $s = str_replace(',', '.', $s);
        $s = str_replace(' ', '', $s);
        if($s == ''){
            return 0;
        }else{
            return floatval($s);
        }
    }
}

if (! function_exists('mysqlPassword')) {
    function mysqlPassword($str) {
        $pass = strtoupper(
                sha1(
                        sha1($str, true)
                )
        );
        $pass = '*' . $pass;
        return $pass;
    }
}

if (! function_exists('Mask')) {
function Mask($mask,$str){

    $str = str_replace(" ","",$str);

    for($i=0;$i<strlen($str);$i++){
        $mask[strpos($mask,"#")] = $str[$i];
    }

    return $mask;
}
}

if (! function_exists('isDate')) {
    function isDate($value)
    {
        if (!$value) {
            return false;
        }

        try {
            new \DateTime($value);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}

if (! function_exists('createRandomVal')) {
  function createRandomVal($val) {
    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789,-";
    srand((double)microtime() * 1000000);
    $i = 0;
    $pass = '';
    while ($i < $val) {
        $num = rand() % 64;
        $tmp = substr($chars, $num, 1);
        $pass = $pass . $tmp;
        $i++;
    }
    return $pass;
  }
}

if (! function_exists('getBytesFromHexString')) {
    function getBytesFromHexString($hexdata)
    {
    for($count = 0; $count < strlen($hexdata); $count+=2)
        $bytes[] = chr(hexdec(substr($hexdata, $count, 2)));

    return implode($bytes);
    }
}

if (! function_exists('getImageMimeType')) {
    function getImageMimeType($imagedata)
    {
    $imagemimetypes = array(
        "jpeg" => "FFD8",
        "png" => "89504E470D0A1A0A",
        "gif" => "474946",
        "bmp" => "424D",
        "tiff" => "4949",
        "tiff" => "4D4D"
    );

    foreach ($imagemimetypes as $mime => $hexbytes)
    {
        $bytes = getBytesFromHexString($hexbytes);
        if (substr($imagedata, 0, strlen($bytes)) == $bytes)
        return $mime;
    }

    return NULL;
    }
}

if (! function_exists('humanReadBytes')) {
    function humanReadBytes($bytes, $decimals = 2){
        $size = array('B','kB','MB','GB','TB','PB','EB','ZB','YB');
        $factor = floor((strlen($bytes) - 1) / 3);
        return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$size[$factor];
    }
}

if (! function_exists('fullNameToFirstName')) {
    function fullNameToFirstName($fullName, $checkFirstNameLength=TRUE)
    {
        // Split out name so we can quickly grab the first name part
        $nameParts = explode(' ', $fullName);
        $firstName = $nameParts[0];
        // If the first part of the name is a prefix, then find the name differently
        if(in_array(strtolower($firstName), array('mr', 'ms', 'mrs', 'miss', 'dr'))) {
            if($nameParts[2]!='') {
                // E.g. Mr James Smith -> James
                $firstName = $nameParts[1];
            } else {
                // e.g. Mr Smith (no first name given)
                $firstName = $fullName;
            }
        }
        // make sure the first name is not just "J", e.g. "J Smith" or "Mr J Smith" or even "Mr J. Smith"
        if($checkFirstNameLength && strlen($firstName)<3) {
            $firstName = $fullName;
        }
        return $firstName;
    }
}

if (! function_exists('mime2ext')) {
    function mime2ext($mime){
      $all_mimes = '{"png":["image\/png","image\/x-png"],"bmp":["image\/bmp","image\/x-bmp","image\/x-bitmap","image\/x-xbitmap","image\/x-win-bitmap","image\/x-windows-bmp","image\/ms-bmp","image\/x-ms-bmp","application\/bmp","application\/x-bmp","application\/x-win-bitmap"],"gif":["image\/gif"],"jpeg":["image\/jpeg","image\/pjpeg"],"xspf":["application\/xspf+xml"],"vlc":["application\/videolan"],"wmv":["video\/x-ms-wmv","video\/x-ms-asf"],"au":["audio\/x-au"],"ac3":["audio\/ac3"],"flac":["audio\/x-flac"],"ogg":["audio\/ogg","video\/ogg","application\/ogg"],"kmz":["application\/vnd.google-earth.kmz"],"kml":["application\/vnd.google-earth.kml+xml"],"rtx":["text\/richtext"],"rtf":["text\/rtf"],"jar":["application\/java-archive","application\/x-java-application","application\/x-jar"],"zip":["application\/x-zip","application\/zip","application\/x-zip-compressed","application\/s-compressed","multipart\/x-zip"],"7zip":["application\/x-compressed"],"xml":["application\/xml","text\/xml"],"svg":["image\/svg+xml"],"3g2":["video\/3gpp2"],"3gp":["video\/3gp","video\/3gpp"],"mp4":["video\/mp4"],"m4a":["audio\/x-m4a"],"f4v":["video\/x-f4v"],"flv":["video\/x-flv"],"webm":["video\/webm"],"aac":["audio\/x-acc"],"m4u":["application\/vnd.mpegurl"],"pdf":["application\/pdf","application\/octet-stream"],"pptx":["application\/vnd.openxmlformats-officedocument.presentationml.presentation"],"ppt":["application\/powerpoint","application\/vnd.ms-powerpoint","application\/vnd.ms-office","application\/msword"],"docx":["application\/vnd.openxmlformats-officedocument.wordprocessingml.document"],"xlsx":["application\/vnd.openxmlformats-officedocument.spreadsheetml.sheet","application\/vnd.ms-excel"],"xl":["application\/excel"],"xls":["application\/msexcel","application\/x-msexcel","application\/x-ms-excel","application\/x-excel","application\/x-dos_ms_excel","application\/xls","application\/x-xls"],"xsl":["text\/xsl"],"mpeg":["video\/mpeg"],"mov":["video\/quicktime"],"avi":["video\/x-msvideo","video\/msvideo","video\/avi","application\/x-troff-msvideo"],"movie":["video\/x-sgi-movie"],"log":["text\/x-log"],"txt":["text\/plain"],"css":["text\/css"],"html":["text\/html"],"wav":["audio\/x-wav","audio\/wave","audio\/wav"],"xhtml":["application\/xhtml+xml"],"tar":["application\/x-tar"],"tgz":["application\/x-gzip-compressed"],"psd":["application\/x-photoshop","image\/vnd.adobe.photoshop"],"exe":["application\/x-msdownload"],"js":["application\/x-javascript"],"mp3":["audio\/mpeg","audio\/mpg","audio\/mpeg3","audio\/mp3"],"rar":["application\/x-rar","application\/rar","application\/x-rar-compressed"],"gzip":["application\/x-gzip"],"hqx":["application\/mac-binhex40","application\/mac-binhex","application\/x-binhex40","application\/x-mac-binhex40"],"cpt":["application\/mac-compactpro"],"bin":["application\/macbinary","application\/mac-binary","application\/x-binary","application\/x-macbinary"],"oda":["application\/oda"],"ai":["application\/postscript"],"smil":["application\/smil"],"mif":["application\/vnd.mif"],"wbxml":["application\/wbxml"],"wmlc":["application\/wmlc"],"dcr":["application\/x-director"],"dvi":["application\/x-dvi"],"gtar":["application\/x-gtar"],"php":["application\/x-httpd-php","application\/php","application\/x-php","text\/php","text\/x-php","application\/x-httpd-php-source"],"swf":["application\/x-shockwave-flash"],"sit":["application\/x-stuffit"],"z":["application\/x-compress"],"mid":["audio\/midi"],"aif":["audio\/x-aiff","audio\/aiff"],"ram":["audio\/x-pn-realaudio"],"rpm":["audio\/x-pn-realaudio-plugin"],"ra":["audio\/x-realaudio"],"rv":["video\/vnd.rn-realvideo"],"jp2":["image\/jp2","video\/mj2","image\/jpx","image\/jpm"],"tiff":["image\/tiff"],"eml":["message\/rfc822"],"pem":["application\/x-x509-user-cert","application\/x-pem-file"],"p10":["application\/x-pkcs10","application\/pkcs10"],"p12":["application\/x-pkcs12"],"p7a":["application\/x-pkcs7-signature"],"p7c":["application\/pkcs7-mime","application\/x-pkcs7-mime"],"p7r":["application\/x-pkcs7-certreqresp"],"p7s":["application\/pkcs7-signature"],"crt":["application\/x-x509-ca-cert","application\/pkix-cert"],"crl":["application\/pkix-crl","application\/pkcs-crl"],"pgp":["application\/pgp"],"gpg":["application\/gpg-keys"],"rsa":["application\/x-pkcs7"],"ics":["text\/calendar"],"zsh":["text\/x-scriptzsh"],"cdr":["application\/cdr","application\/coreldraw","application\/x-cdr","application\/x-coreldraw","image\/cdr","image\/x-cdr","zz-application\/zz-winassoc-cdr"],"wma":["audio\/x-ms-wma"],"vcf":["text\/x-vcard"],"srt":["text\/srt"],"vtt":["text\/vtt"],"ico":["image\/x-icon","image\/x-ico","image\/vnd.microsoft.icon"],"csv":["text\/x-comma-separated-values","text\/comma-separated-values","application\/vnd.msexcel"],"json":["application\/json","text\/json"]}';
      $all_mimes = json_decode($all_mimes,true);
      foreach ($all_mimes as $key => $value) {
        if(array_search($mime,$value) !== false) return $key;
      }
      return false;
    }
  }

if (! function_exists('formatCnpjCpf')) {
    function formatCnpjCpf($value)
    {
      $CPF_LENGTH = 11;
      $cnpj_cpf = preg_replace("/\D/", '', $value);

      if (strlen($cnpj_cpf) === $CPF_LENGTH) {
        return preg_replace("/(\d{3})(\d{3})(\d{3})(\d{2})/", "\$1.\$2.\$3-\$4", $cnpj_cpf);
      }

      return preg_replace("/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/", "\$1.\$2.\$3/\$4-\$5", $cnpj_cpf);
    }
}

if (! function_exists('removeAcentos')) {
    function removeAcentos($string) {

        // matriz de entrada
        $what = array( 'ä','ã','à','á','â','ê','ë','è','é','ï','ì','í','ö','õ','ò','ó','ô','ü','ù','ú','û','ñ','ç','Ä','Ã','À','Á','Â','Ê','Ë','È','É','Ï','Ì','Í','Ö','Õ','Ò','Ó','Ô','Ü','Ù','Ú','Û','Ñ','Ç',' ','-','(',')',',',';',':','|','!','"','#','$','%','&','/','=','?','~','^','>','<','ª','º' );

        // matriz de saída
        $by   = array( 'a','a','a','a','a','e','e','e','e','i','i','i','o','o','o','o','o','u','u','u','u','n','c','A','A','A','A','A','E','E','E','E','I','I','I','O','O','O','O','O','U','U','U','U','N','C','_','_','_','_','_','_','_','_','_','_','_','_','_','_','_','_','_','_','_','_','_','_','_' );

        // devolver a string
        return str_replace($what, $by, $string);
    }
}

if (! function_exists('formatarPlaca')) {
    function formatarPlaca($placa) {
        $placa = str_replace('-', '', strtoupper($placa));
        return preg_filter('/^([a-zA-Z]{3})(\d{3,7})$/', '$1-$2', $placa);
    }
}

if (! function_exists('cleanDocMask')) {
  function cleanDocMask($string) {

      // matriz de entrada
      $what = array( '.','-','/','\\','*','+','_',';',':','<','>',',',' ');


      // devolver a string
      return str_replace($what, '', $string);
  }
  }


  if (! function_exists('decodeChaveNFe')) {
    function decodeChaveNFe ($pChave) {
        // cUF - Código da UF do emitente do Documento Fisca 0, 2
        // AAMM - Ano e Mês de emissão da NF-e;
        // CNPJ - CNPJ do emitente;
        // mod - Modelo do Documento Fiscal;
        // serie - Série do Documento Fiscal;
        // nNF - Número do Documento Fiscal;
        // tpEmis – forma de emissão da NF-e;
        // cNF - Código Numérico que compõe a Chave de Acesso;
        // cDV - Dígito Verificador da Chave de Acesso.
        $elementos = [
          'cUF' => [ 'value' => null, 'size' => 2 ],
          'AAMM' => [ 'value' => null, 'size' => 4 ],
          'CNPJ' => [ 'value' => null, 'size' => 14 ],
          'mod' => [ 'value' => null, 'size' => 2 ],
          'serie' => [ 'value' => null, 'size' => 3 ],
          'nNF' => [ 'value' => null, 'size' => 9 ],
          'tpEmis' => [ 'value' => null, 'size' => 1 ],
          'cNF' => [ 'value' => null, 'size' => 8 ],
          'cDV' => [ 'value' => null, 'size' => 1 ]
        ];

        $ret = [];
        // 35190808957311000155550000003068981672367898
        // 35 . 1908 . 08957311000155 . 55 . 000 . 000306898 . 1 . 67236789 . 8
        // var l = ch.length

        $p = 0;
        foreach ($elementos as $key => $elem) {
            $value = substr($pChave, $p, intval($elem['size']));  // abcd
            $ret[$key] = $value;
            $p = $p + intval($elem['size']);
        };
        return $ret;
      }

  }
  if (! function_exists('formatMoney')) {
    function formatMoney($value, $decimals = 2) {
        return number_format($value, $decimals, ",",".");
    }
}


if (! function_exists('testaChaveNFe')) {
    function testaChaveNFe($chave = '') {
        if (strlen($chave) != 44) {
            return false;
        }
        $cDV = substr($chave, -1);
        $calcDV = calculaDV(substr($chave, 0, 43));
        if ($cDV === $calcDV) {
            return true;
        }
        return false;
    }
}

if (! function_exists('calculaDV')) {
    function calculaDV($chave43) {
        $multiplicadores = array(2, 3, 4, 5, 6, 7, 8, 9);
        $iCount = 42;
        $somaPonderada = 0;
        while ($iCount >= 0) {
            for ($mCount = 0; $mCount < count($multiplicadores) && $iCount >= 0; $mCount++) {
                $num = (int) substr($chave43, $iCount, 1);
                $peso = (int) $multiplicadores[$mCount];
                $somaPonderada += $num * $peso;
                $iCount--;
            }
        }
        $resto = $somaPonderada % 11;
        if ($resto == '0' || $resto == '1') {
            $cDV = 0;
        } else {
            $cDV = 11 - $resto;
        }
        return (string) $cDV;
    }
}
