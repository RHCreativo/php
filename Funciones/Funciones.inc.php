<?php

$error_consulta = '
	Disculpe las molestias, se ha producido un error inesperado. Si el problema persiste por favor reportelo a través de nuestro formulario de contacto.<br>
	Muchas gracias!
';

//******************************************************************************
//* Función: fContarVisitas(tabla, prefijos, identificador)
//* -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-= *
//*                                                                            *
function fContarVisitas($tabla, $pref, $id){
	$update = mysql_query("UPDATE ".$tabla." SET ".$pref."Visitas = ".$pref."Visitas+1 WHERE ".$pref."Codigo='".$id."'");
}


//******************************************************************************
//* Función: fRedimensiona                                                     *
//* -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-= *
//*                                                                            *
function fRedimensiona($nAncho, $nAlto, $nMaxAncho, $nMaxAlto) { 
  if ($nAncho<=$nMaxAncho && $nAlto<=$nMaxAlto) {
    $cResultado = "width=\"".$nAncho."\" height=\"".$nAlto."\"" ;
  } elseif ($nAncho>$nMaxAncho && $nAlto>$nMaxAlto && $nAncho/$nAlto>=$nMaxAncho/$nMaxAlto) {
    $cResultado = "width=\"".$nMaxAncho."\" height=\"".round($nAlto*($nMaxAncho/$nAncho),0)."\"" ;
  } elseif ($nAncho>$nMaxAncho && $nAlto>$nMaxAlto) {
    $cResultado = "width=\"".round($nAncho*($nMaxAlto/$nAlto),0)."\" height=\"".$nMaxAlto."\"" ;
  } elseif ($nAncho>$nMaxAncho) {
    $cResultado = "width=\"".$nMaxAncho."\" height=\"".round($nAlto*($nMaxAncho/$nAncho),0)."\"" ;
  } elseif ($nAlto>$nMaxAlto) {
    $cResultado = "width=\"".round($nAncho*($nMaxAlto/$nAlto),0)."\" height=\"".$nMaxAlto."\"" ;
  }
  return $cResultado;
}



//******************************************************************************
//* Función: getIPInfo                                                         *
//* -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-= *
//*                                                                            *
function getIPInfo($addr) {
  // Database Conexion
  $dbIPInfo = @mysql_connect("server","user","pass");
  if (!$dbIPInfo) {
    $aIPInfo = array("cc" => "US", "cn" => "United States", "continente" => "NA", "directpayeu" => "No");
    return $aIPInfo;
  }
  mysql_select_db("chesta",$dbIPInfo) or die ("ERROR: mysql_select_db() failed: " . mysql_error()."\n");

  // this sprintf() wrapper is needed, because the PHP long is signed by default
  $ipnum = sprintf("%u", ip2long($addr));
  $query = "SELECT _geoCC.cc, cn, continente FROM _geoIP NATURAL JOIN _geoCC  NATURAL JOIN _geoContinente WHERE ${ipnum} BETWEEN start AND end";
  $result = mysql_query($query, $dbIPInfo);
  if((! $result) or mysql_numrows($result) < 1)
    $aIPInfo = array("cc" => "**", "cn" => "*****", "continente" => "**");
  else
    $aIPInfo = mysql_fetch_assoc($result);

  mysql_close($dbIPInfo);

  return $aIPInfo;
}



//******************************************************************************
//* Función: fNombreArchivo                                                    *
//* -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-= *
//*                                                                            *
function fNombreArchivo($cArchivo, $cQue="A") {
  $cSinExt = preg_replace('/(.+)\..*$/', '$1', $cArchivo);
  $cSolExt = str_replace($cSinExt.".","",$cArchivo);
  if ($cQue=="A")       // Ambos, en un array
    return array($cSinExt, $cSolExt);
  elseif ($cQue=="N")   // Solo el Nombre
    return $cSinExt;
  elseif ($cQue=="E")   // Solo la Extensión
    return $cSolExt;
}



//******************************************************************************
//* Función: fFullUpper                                                        *
//* -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-= *
//*                                                                            *
function fFullUpper($str){
  $subject = strtoupper(htmlentities($str, null, 'UTF-8'));
  $pattern = '/&([A-Z]+);/';
  return preg_replace_callback($pattern, "ucfirstHTMLentity", $subject);
}

function ucfirstHTMLentity($matches){
  if (strpos("#&IEXCL;#&IQUEST;#", $matches[0])===false) 
    return "&".ucfirst(strtolower($matches[1])).";";

  return "&".strtolower($matches[1]).";";
}

//******************************************************************************
//* Función: fUrlsAmigables                                                    *
//* -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-= *
//*   
function fUrlsAmigables($url) {
	$url = strtolower($url); // Tranformamos todo a minusculas
//Rememplazamos caracteres especiales latinos
	$find = array('á', 'é', 'í', 'ó', 'ú', 'ñ');
	$repl = array('a', 'e', 'i', 'o', 'u', 'n');
	$url = str_replace ($find, $repl, $url);
// Añaadimos los guiones
	$find = array(' ', '&', '\r\n', '\n', '+'); 
	$url = str_replace ($find, '-', $url);
// Eliminamos y Reemplazamos demás caracteres especiales
	$find = array('/[^a-z0-9\-<>]/', '/[\-]+/', '/<[^>]*>/');
	$repl = array('', '-', '');
	$url = preg_replace ($find, $repl, $url);
	return $url;
}

//******************************************************************************
//* Función: fValidarVar ($_XXX['var'], tipo, largo_maximo                     *
//* -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-= *
//*   
//******************************************************************************
//* Seguridad variables:                                                       *
//*   1. mysql_real_escape_string a todas                                      *
//*   2. Validación por tipo de dato esperado                                  *
//*          - tipos aceptados: $_POST, $_GET, $_SESSION, $_COOKIE             *
//* -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-= *
//*                                                                            *
function pasomagic($variable){
	if (get_magic_quotes_gpc()){
		$variable = stripslashes($variable);
	}
	if (!is_numeric($variable)){
		if (is_array($variable)) {
			return array_map("pasomagic", $variable);
		}else{
			return mysql_real_escape_string($variable);
		}
	}else{
		return $variable;
	}
}
//******************************************************************************
//* LLAMADA: fValidarVar($_XXX['variable'], tipo, largo_maximo ******************
function fValidarVar($variable, $tipo='int', $largo=''){
	switch($tipo){
	  //» solo texto como string (letras, espacio y punto) [Nombre, Apellido, Mes, Ciudad, Prov, País, etc.]
	  case 'abc' :
	  $va = pasomagic($variable);
	  $va = (string)preg_replace("/[^a-zA-ZñÑáÁéÉíÍóÓúÚçÇüÜ\.\s]/", "", $va);
	  break;
	  //» solo numeros (números, punto, -, + y eE) [Para Teléfono y DNI por ej.]
	  case 'num' :
	  $va = pasomagic($variable);
	  $va = (string)preg_replace("/[^0-9eE\+\-\.\s]/", "", str_replace(',','.',$va));
	  break;
	  //» solo texto como string (letras, punto, coma, punto y coma, guiones y espacio) SIN NUMEROS
	  case 'txt' :
	  $va = pasomagic($variable);
	  $va = (string)preg_replace("/[^a-zA-ZñÑáÁéÉíÍóÓúÚçÇüÜ,;\.\-_\s]/", "", $va);
	  break;
	  //» solo enteros positivos sin espacio ni nada (INTEGER) [Edad, Dia, Año]
	  case 'int' :
	  $va = pasomagic($variable);
	  $va = (int)preg_replace("/[^0-9]/", "", $va);
	  break;
	  //» Texto y números (letras, punto, coma, punto y coma, guiones, espacio y números)
	  case 'tyn' :
	  $va = pasomagic($variable);
	  $va = (string)preg_replace("/[^a-zA-Z0-9ñÑáÁéÉíÍóÓúÚçÇüÜ,;°\.\-_\s]/", "", $va);
	  break;
	  //» solo EMAIL
	  case 'mail' :
	  $va = pasomagic($variable);
	  $va = (string)(preg_match('|^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]{2,})+$|i', $va))?$va:'no es mail';
	  break;
	  //» solo texto con saltos de línea
	  case 'area' :
	  $va = html_entity_decode($variable);
	  $va = preg_replace ('/<[^>]*>/', '', $va);
	  $va = pasomagic($va);
	  $va = nl2br($va);
	  break;
	}
	$va = ($largo!='')?substr($va,0,$largo):$va;
	return $va;
}


//******************************************************************************
//* Función: fPonerBarras y fSacarBarras                                       *
//* -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-= *
//*                                                                            *
function fPonerBarras($cValor) {
  if (get_magic_quotes_gpc()==1) {
    return $cValor ;
  }
  if (is_array($cValor)) {
    return array_map("fPonerBarras", $cValor);
  } else {
    return addslashes($cValor);
  }
}

function fSacarBarras($cValor) {
  if (get_magic_quotes_gpc()==0) {
    return $cValor ;
  }
  if (is_array($cValor)) {
    return array_map("fSacarBarras", $cValor);
  } else {
    return stripslashes($cValor);
  }
}


//******************************************************************************
//* Función: fFormatoFecha                                                     *
//* -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-= *
//*                                                                            *
//*   Consideraciones sobre cFormato                                           *
//*   ------------------------------                                           *
//*     dd   -> Número del día (completado con ceros)                          *
//*     xd   -> Número del día (sin ceros)                                     *
//*     DD   -> Nombre del dia (abreviado)                                     *
//*     DDD  -> Nombre del día (completo)                                      *
//*     mm   -> Número del mes (completado con ceros)                          *
//*     xm   -> Número del mes (sin ceros)                                     *
//*     MM   -> Nombre del mes (abreviado)                                     *
//*     MMM  -> Nombre del mes (completo)                                      *
//*     aa   -> Número del año (2 dígitos)                                     *
//*     aaaa -> Número del año (2 dígitos)                                     *
//*                                                                            *
function fFormatoFecha($cFecha,$cFormato) {
  if ($cFecha=='0000-00-00') {
    return "" ;
  }

  $dFecha = strtotime($cFecha) ;

  $cDiaNroLF = date("d", $dFecha) ;
  $cDiaNroLV = date("j", $dFecha) ;
  $cMesNroLF = date("m", $dFecha) ;
  $cMesNroLV = date("n", $dFecha) ;
  $aAnoNroL4 = date("Y", $dFecha) ;
  $aAnoNroL2 = date("y", $dFecha) ;

  $cDiaNom   = date("D", $dFecha) ;
  $cMesNom   = date("M", $dFecha) ;

  if ($_SESSION["gbl".$conf["VariablesSESSION"]."Partic"]=="en") {
    $aNombreMes['Jan']['Corto'] = "Jan";           $aNombreMes['Jan']['Largo'] = "January" ;
    $aNombreMes['Feb']['Corto'] = "Feb";           $aNombreMes['Feb']['Largo'] = "February" ;
    $aNombreMes['Mar']['Corto'] = "Mar";           $aNombreMes['Mar']['Largo'] = "March" ;
    $aNombreMes['Apr']['Corto'] = "Apr";           $aNombreMes['Apr']['Largo'] = "April" ;
    $aNombreMes['May']['Corto'] = "May";           $aNombreMes['May']['Largo'] = "May" ;
    $aNombreMes['Jun']['Corto'] = "Jun";           $aNombreMes['Jun']['Largo'] = "June" ;
    $aNombreMes['Jul']['Corto'] = "Jul";           $aNombreMes['Jul']['Largo'] = "July" ;
    $aNombreMes['Aug']['Corto'] = "Aug";           $aNombreMes['Aug']['Largo'] = "August" ;
    $aNombreMes['Sep']['Corto'] = "Sep";           $aNombreMes['Sep']['Largo'] = "September" ;
    $aNombreMes['Oct']['Corto'] = "Oct";           $aNombreMes['Oct']['Largo'] = "October" ;
    $aNombreMes['Nov']['Corto'] = "Nov";           $aNombreMes['Nov']['Largo'] = "November" ;
    $aNombreMes['Dec']['Corto'] = "Dec";           $aNombreMes['Dec']['Largo'] = "December" ;

    $aNombreDia['Sun']['Corto'] = "Sun";           $aNombreDia['Sun']['Largo'] = "Sunday" ;
    $aNombreDia['Mon']['Corto'] = "Mon";           $aNombreDia['Mon']['Largo'] = "Monday" ;
    $aNombreDia['Tue']['Corto'] = "Tue";           $aNombreDia['Tue']['Largo'] = "Tuesday" ;
    $aNombreDia['Wed']['Corto'] = "Wed";           $aNombreDia['Wed']['Largo'] = "Wednesday" ;
    $aNombreDia['Thu']['Corto'] = "Thu";           $aNombreDia['Thu']['Largo'] = "Thursday" ;
    $aNombreDia['Fri']['Corto'] = "Fri";           $aNombreDia['Fri']['Largo'] = "Friday" ;
    $aNombreDia['Sat']['Corto'] = "Sat";           $aNombreDia['Sat']['Largo'] = "Saturday" ;
  } else {
    $aNombreMes['Jan']['Corto'] = "Ene";           $aNombreMes['Jan']['Largo'] = "Enero";
    $aNombreMes['Feb']['Corto'] = "Feb";           $aNombreMes['Feb']['Largo'] = "Febrero";
    $aNombreMes['Mar']['Corto'] = "Mar";           $aNombreMes['Mar']['Largo'] = "Marzo";
    $aNombreMes['Apr']['Corto'] = "Abr";           $aNombreMes['Apr']['Largo'] = "Abril";
    $aNombreMes['May']['Corto'] = "May";           $aNombreMes['May']['Largo'] = "Mayo";
    $aNombreMes['Jun']['Corto'] = "Jun";           $aNombreMes['Jun']['Largo'] = "Junio";
    $aNombreMes['Jul']['Corto'] = "Jul";           $aNombreMes['Jul']['Largo'] = "Julio";
    $aNombreMes['Aug']['Corto'] = "Ago";           $aNombreMes['Aug']['Largo'] = "Agosto";
    $aNombreMes['Sep']['Corto'] = "Sep";           $aNombreMes['Sep']['Largo'] = "Septiembre";
    $aNombreMes['Oct']['Corto'] = "Oct";           $aNombreMes['Oct']['Largo'] = "Octubre";
    $aNombreMes['Nov']['Corto'] = "Nov";           $aNombreMes['Nov']['Largo'] = "Noviembre";
    $aNombreMes['Dec']['Corto'] = "Dic";           $aNombreMes['Dec']['Largo'] = "Diciembre";

    $aNombreDia['Sun']['Corto'] = "Dom";           $aNombreDia['Sun']['Largo'] = "Domingo";
    $aNombreDia['Mon']['Corto'] = "Lun";           $aNombreDia['Mon']['Largo'] = "Lunes";
    $aNombreDia['Tue']['Corto'] = "Mar";           $aNombreDia['Tue']['Largo'] = "Martes";
    $aNombreDia['Wed']['Corto'] = "Mie";           $aNombreDia['Wed']['Largo'] = "Miércoles";
    $aNombreDia['Thu']['Corto'] = "Jue";           $aNombreDia['Thu']['Largo'] = "Jueves";
    $aNombreDia['Fri']['Corto'] = "Vie";           $aNombreDia['Fri']['Largo'] = "Viernes";
    $aNombreDia['Sat']['Corto'] = "Sab";           $aNombreDia['Sat']['Largo'] = "Sabado";
  }

  $cFormato = str_replace('aaaa',$aAnoNroL4,$cFormato);
  $cFormato = str_replace('aa',$aAnoNroL2,$cFormato);
  $cFormato = str_replace('MMM',$aNombreMes[$cMesNom]['Largo'],$cFormato);
  $cFormato = str_replace('MM',$aNombreMes[$cMesNom]['Corto'],$cFormato);
  $cFormato = str_replace('mm',$cMesNroLF,$cFormato);
  $cFormato = str_replace('xm',$cMesNroLV,$cFormato);
  $cFormato = str_replace('DDD',$aNombreDia[$cDiaNom]['Largo'],$cFormato);
  $cFormato = str_replace('DD',$aNombreDia[$cDiaNom]['Corto'],$cFormato);
  $cFormato = str_replace('dd',$cDiaNroLF,$cFormato);
  $cFormato = str_replace('xd',$cDiaNroLV,$cFormato);

  return ($cFormato);
}


//******************************************************************************
//* Función: fStrPosN                                                          *
//* -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-= *
//*                                                                            *
//*   Encuentra la ocurrencia número $nOcurrencia de $cAguja en $cPajar        *
//*                                                                            *
function fStrPosN($cPajar, $cAguja, $nOcurrencia) {
  $nPuntero    = 0 ;
  $nAgujaLargo = strlen($cAguja);
  for($i=0; $i<$nOcurrencia; ++$i) {
    $nPosicion = strpos($cPajar, $cAguja, $nPuntero);
    if($nPosicion===false) return false;
      $nPuntero = $nPosicion + $nAgujaLargo;
  }
  return $nPosicion;
}


//******************************************************************************
//* Función: fGetMicroTime                                                     *
//* -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-= *
//*                                                                            *
function fGetMicroTime(){
  list($useg, $seg) = explode(" ", microtime());
  return ((float)$useg + (float)$seg);
}


//******************************************************************************
//* Función: fCntPalabras                                                      *
//* -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-= *
//*                                                                            *
//*   Cuenta la cantidad de palabras de una cadena.                            *
//*   Una palabra está separada de la otra por al menos un espacio.            *
//*                                                                            *
function fCntPalabras($cCadena) {
  // la siguiente linea elimina espacios consecutivos
  $cCadena = eregi_replace(" +", " ", $cCadena);
  $cResult = count_chars($cCadena, 0);
  return($cResult[32]+1);
}


//******************************************************************************
//* Función: fDirectorios                                                      *
//* -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-= *
//*                                                                            *
function fDirectorios($cPath) {

  $cResultado = "";

  $handle = opendir($cPath);
  while ($file = readdir($handle)) {
    if ($file != "." && $file != "..") {
      $cResultado .= $file . ",";
    }
  }
  closedir($handle);

  return substr_replace($cResultado, '', -1);
}


//******************************************************************************
//* Función: iif                                                               *
//* -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-= *
//*                                                                            *
function iif($Condicion, $ValorSi, $ValorNo) {
  if ( $Condicion ) {
    return $ValorSi;
  } else {
    return $ValorNo;
  }
}


//******************************************************************************
//* Función: fModiData                                                         *
//* -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-= *
//*                                                                            *
function fModiData($cTabla, $cQue, $aCampos) {

  if ( $cQue=="Agregar" ) {
    $cInstrSql = "INSERT INTO " . $cTabla . " (" ;
    for ($nElem=1; $nElem<count($aCampos); $nElem++) {
       if ( $nElem!=1 ) {
          $cInstrSql .= ", " ;
       }
       $cInstrSql .= $aCampos[$nElem]["Campo"] ;
    }
    $cInstrSql .= ") VALUES ('" ;
    for ($nElem=1; $nElem<count($aCampos); $nElem++) {
      if ( $nElem!=1 ) {
        $cInstrSql .= "', '" ;
      }
      $cInstrSql .= $aCampos[$nElem]["Valor"] ;
    }
    $cInstrSql .= "')" ;

  } else if ( $cQue=="Modificar" ) {
    $cInstrSql = "UPDATE " . $cTabla . " SET " ;
    for ($nElem=1; $nElem<count($aCampos); $nElem++) {
      if ( $nElem!=1 ) {
        $cInstrSql .= ", " ;
      }
      $cInstrSql .= $aCampos[$nElem]["Campo"] . "='" . $aCampos[$nElem]["Valor"] . "'" ;
    }
    $cInstrSql .= " WHERE " . $aCampos[0]["Campo"] . "='" . $aCampos[0]["Valor"] . "'" ;

  } else if ( $cQue=="Borrar" ) {
    $cInstrSql = "DELETE FROM " . $cTabla . " WHERE " . $aCampos[0]["Campo"] . "='" . $aCampos[0]["Valor"] . "'" ;
  }

  return $cInstrSql ;

}



//******************************************************************************
//* Función: fErrorSQL                                                         *
//* -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-= *
//*                                                                            *
function fErrorSQL($cEstadoSitio, $cMensaje) { 
  $cExtra  = "" ;
  $cExtra .= "=================================== \n" ;
  $cExtra .= "Estado: ".$cEstadoSitio." \n" ;
  $cExtra .= "=================================== \n" ;
  $cExtra .= "IP: ".$_SERVER["REMOTE_ADDR"]." \n" ;
  $cExtra .= "Browser: ".$_SERVER["HTTP_USER_AGENT"]." \n" ;
  $cExtra .= "=================================== \n" ;
  $cExtra .= "Script: ".$_SERVER["PHP_SELF"]." \n" ;
  $cExtra .= "Query String: ".$_SERVER["QUERY_STRING"]." \n" ;
  $cExtra .= "=================================== \n" ;
  $cExtra .= "VARIABLES SESSION: \n" ;
  $cExtra .= "------------------ \n" ;
  $aExtra = $_SESSION;
  foreach ($aExtra as $cClave => $cValor){
    $cExtra .= "  - ".$cClave.": ".$cValor." \n" ;
  }
  unset ($aExtra);
  $cExtra .= "=================================== \n" ;

  /*
  =======================================================================================
   arreglar esto, que guarde en la DB 
  =======================================================================================
  $cSql = "INSERT INTO sysErrores "
        . "  (ErrTexto, ErrExtra)"
        . "  VALUES "
        . "  ('".mysql_real_escape_string(strip_tags(str_replace("<br />", "\n", $cMensaje)))."', '".mysql_real_escape_string($cExtra)."')";
  $nConexion->query ($cSql) or die("Error en la consulta: " . $cSql . " Tipo de error: " . mysqli_error($nConexion));
  */

  if ($cEstadoSitio=="Production") {
    die();
  } else {
    die($cMensaje);
  }
}



//******************************************************************************
//* Función: fTraerBanner(BnrTipo);                                            *
//* -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-= *
//*                                                                            *
function fTraerBanner($BnrTipo) {
	srand((double)microtime()*1000000);
	
	$cSql  = "SELECT BnrCodigo, BnrNombre, BnrTipo, BnrImg, BnrLink, BnrPaginaNueva, BnrCobertura FROM Banners 
	WHERE BnrVisible='Si' AND (BnrLmtHasta>=NOW() OR BnrLmtHasta='0000-00-00') 
	AND (BnrLmtVeces>BnrCntMostrado OR BnrLmtVeces='') 
	AND (BnrLmtClicks>BnrCntClicks OR BnrLmtClicks='') 
	AND (BnrArea='Todas' OR BnrArea='" . $_SESSION["gblUbicacion"] . "') 
	AND BnrTipo='".$BnrTipo."'" ;
	
	$nResultBnr = mysql_query ($cSql) or fErrorSQL($conf["EstadoSitio"], "<br /><br /><b>Error en la consulta:</b><br />" . $cSql . "<br /><br /><b>Tipo de error:</b><br />" . mysql_error() . "<br />");
	
	$nIndice = 0;
	$nCobertTotal = 0;
	while ($aRegistBnr = mysql_fetch_array($nResultBnr)) {
	  $aBanners[$nIndice]["Codigo"] = $aRegistBnr["BnrCodigo"];
	  $aBanners[$nIndice]["Imagen"] = $aRegistBnr["BnrImg"];
	  $aBanners[$nIndice]["Link"]   = $aRegistBnr["BnrLink"];
	  $aBanners[$nIndice]["Nueva"]  = $aRegistBnr["BnrPaginaNueva"];
	  $aBanners[$nIndice]["Cobert"] = $aRegistBnr["BnrCobertura"];
	  $aBanners[$nIndice]["Titulo"] = $aRegistBnr["BnrNombre"];
	
	  $nCobertTotal += $aBanners[$nIndice]["Cobert"] ;
	  $nIndice++;
	}
	mysql_free_result ($nResultBnr);	
	
	if($nIndice > 0){
		$nSumaTotal = 0;
		for ($nIndice=0; $nIndice<=count($aBanners)-1; $nIndice++) {
			if ($nIndice<count($aBanners)-1) {
				$aBanners[$nIndice]["Cobert"] = round($aBanners[$nIndice]["Cobert"]*100/$nCobertTotal,0) + $nSumaTotal;
				$nSumaTotal = $aBanners[$nIndice]["Cobert"];
			} else {
				$aBanners[$nIndice]["Cobert"] = 100;
			}
		}
		
		$nNroAleat = rand(1,100);
		for ($nIndice=0; $nIndice<=count($aBanners)-1; $nIndice++) {
			if ($nNroAleat<=$aBanners[$nIndice]["Cobert"]) {
				$nBnrIndice = $nIndice;
				break;
		}
	}

	$cBnrSuTitulo	= $aBanners[$nBnrIndice]["Titulo"];
	$cBnrSuImagen	= $aBanners[$nBnrIndice]["Imagen"];
	$cBnrSuLink		= $aBanners[$nBnrIndice]["Link"];
	$cBnrSuNueva	= $aBanners[$nBnrIndice]["Nueva"];
	$cBnrSuCodigo	= $aBanners[$nBnrIndice]["Codigo"];
	}
	
	if($aBanners[$nBnrIndice]["Codigo"]){
		$cSql = "UPDATE Banners SET BnrCntMostrado = BnrCntMostrado+1 WHERE BnrCodigo=" . $aBanners[$nBnrIndice]["Codigo"];
		mysql_query ($cSql) or fErrorSQL($conf["EstadoSitio"], "<br /><br /><b>Error en la consulta:</b><br />" . $cSql . "<br /><br /><b>Tipo de error:</b><br />" . mysql_error() . "<br />");
		//$nResultBnr = mysql_query ($cSql) or die("Error en la consulta: " . $cSql . " Tipo de error: " . mysql_error()) ;
	}
	unset($aBanners);
	
	
	// -> muestro el banner
	if(is_file('Upload/Directos/Banners/'.$cBnrSuImagen)){
		
		if (strpos($cBnrSuImagen,".swf")===false) { // NO FLASH
			if ($cBnrSuLink=="") { //SIN link
				echo '<img src="Upload/Directos/Banners/'.$cBnrSuImagen.'" alt="'.$cBnrSuTitulo.'" />';
				
			} else { // CON link
				echo '<a href="Click.php?Codigo='.$cBnrSuCodigo.'&amp;Pagina='.MD5($cBnrSuLink).'" target="'.($cBnrSuNueva=="Si"?"_blank":"_self").'"><img src="Upload/Directos/Banners/'.$cBnrSuImagen.'" alt="'.$cBnrSuTitulo.'" /></a>';
				
			}

		} else { // ES FLASH
			// obtener alto y ancho del flash
			$datosFlash = getimagesize("Upload/Directos/Banners/".$cBnrSuImagen);
			$anchoSwf = $datosFlash[0];
			$altoSwf = $datosFlash[1];
			
			if ($cBnrSuLink=="") { // SIN link
				echo '<div id="'.$cBnrSuCodigo.$cBnrSuImagen.'">
						Peli Flash
					  </div>
					  <script type="text/javascript">
					  	var flashvars = {};
						flashvars.ButtonV = "No";
						var params = {};
						params.menu = "false";
						params.scale = "noscale";
						params.wmode = "transparent";
						var attributes = {};
						
						swfobject.embedSWF("Upload/Directos/Banners/'.$cBnrSuImagen.'", "'.$cBnrSuCodigo.$cBnrSuImagen.'", "'.$anchoSwf.'", "'.$altoSwf.'", "9.0.0", "swf/expressInstall.swf", flashvars, params, attributes);
					  </script>';
			
			} else { // CON link
				echo '<div id="'.$cBnrSuCodigo.$cBnrSuImagen.'">
						Peli Flash
					  </div>
					  <script type="text/javascript">
					  	var flashvars = {};
						flashvars.ButtonV = "Si";
						flashvars.Codigo = "'.$cBnrSuCodigo.'";
						flashvars.URL = "'.MD5($cBnrSuLink).'";
						flashvars.Window = "'.($cBnrSuNueva=="Si"?"_blank":"_self").'";
						var params = {};
						params.menu = "false";
						params.scale = "noscale";
						params.wmode = "transparent";
						var attributes = {};
						
						swfobject.embedSWF("Upload/Directos/Banners/'.$cBnrSuImagen.'", "'.$cBnrSuCodigo.$cBnrSuImagen.'", "'.$anchoSwf.'", "'.$altoSwf.'", "9.0.0", "swf/expressInstall.swf", flashvars, params, attributes);
					  </script>';
			}
			unset($altoSwf);
			unset($anchoSwf);
		}
		
	} else { // NO SE ENCONTRARON BANNERS PARA ESTA POSICION
		echo 'no se encontraron banners para esta posición';
	}
};
?>