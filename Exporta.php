<?
/*
******************************************************************************
* Administrador de Contenidos                                                *
* -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-= *
*                                                                            *
* (C) 2003, Fabián Chesta                                                    *
*                                                                            *
* Comentarios: Programa basado en php-doc-xls-gen.php                        *
*                                                                            *
*      Nota sobre el uso de este programa con SSL                            *
*      ==========================================                            *
*      Para lograr que este programa funcione bajo SSL haga lo siguiente:    *
*                                                                            *
*      //borre este header                                                   *
*      header("Pragma: no-cache");                                           *
*                                                                            *
*      //y agregue estos headers luego de "Expires: 0"                       *
*      header("Keep-Alive: timeout=15, max=100");                            *
*      header("Connection: Keep-Alive");                                     *
*      header("Transfer-Encoding: chunked");                                 *
*                                                                            *
******************************************************************************
*/
session_start();

// Archivos de Conexión y Configuración
include("Conexion.inc.php");
include("Lenguajes/" . $conf["Lenguaje"]);
include("Funciones/Funciones.inc.php");

// Control de Acceso
if ($_SESSION["gbl".$conf["VariablesSESSION"]."Alias"]=="") {
  header ("Location: Index.php");
  exit(0);
}

// Determina los permisos necesarios para las diferentes acciones
$cSql = "SELECT ModTexto, ModInfoAdic, ModInfoRela, PerExportar FROM sysModulos LEFT JOIN sysModUsu ON sysModulos.ModNombre=sysModUsu.ModNombre WHERE sysModulos.ModNombre='" . $_SESSION["gbl".$conf["VariablesSESSION"]."Modulo"] . "' AND sysModUsu.UsuAlias='" . $_SESSION["gbl".$conf["VariablesSESSION"]."Alias"] . "'";
$nResultado = mysql_query ($cSql) or fErrorSQL($conf["EstadoSitio"], "<br /><br /><b>Error en la consulta:</b><br />" . $cSql . "<br /><br /><b>Tipo de error:</b><br />" . mysql_error() . "<br />");
$aRegistro  = mysql_fetch_array($nResultado);

$cnfModNombre   = $aRegistro["ModTexto"];
$cnfModInfoRela = $aRegistro["ModInfoRela"];

$cnfPerExportar = $aRegistro["PerExportar"];

mysql_free_result ($nResultado);


// Control de Permisos
if ($cnfPerExportar!='S') {
  header ("Location: Index.php");
  exit(0);
}


// Variables para control de los datos
$Orden      = $_GET["Orden"];
$Forma      = $_GET["Forma"];
$CpoFiltro1 = fSacarBarras($_GET["CpoFiltro1"]);
$TipFiltro1 = $_GET["TipFiltro1"];
$TxtFiltro1 = $_GET["TxtFiltro1"];     // ¿Será necesario usar urldecde en algunos hostings...?
$NexFiltro  = $_GET["NexFiltro"];
$CpoFiltro2 = fSacarBarras($_GET["CpoFiltro2"]);
$TipFiltro2 = $_GET["TipFiltro2"];
$TxtFiltro2 = $_GET["TxtFiltro2"];     // ¿Será necesario usar urldecde en algunos hostings...?


// Armado de la SELECT [Datos de Pantalla "Principal"]
$nIndiceCps  = 0;
$cnfConsulta = "SELECT ";
$cSql = "SELECT * FROM sysInfo WHERE ModNombre='" . $_SESSION["gbl".$conf["VariablesSESSION"]."Modulo"] . "' ORDER BY QryPosicion";
$nResultado = mysql_query ($cSql) or fErrorSQL($conf["EstadoSitio"], "<br /><br /><b>Error en la consulta:</b><br />" . $cSql . "<br /><br /><b>Tipo de error:</b><br />" . mysql_error() . "<br />");
while ($aRegistro = mysql_fetch_array($nResultado)) {
   if ($aRegistro["QryCampoAlias"]=='') {
      $cnfConsulta .= $aRegistro["QryCampo"] . ", ";
   } else {
      $cnfConsulta .= $aRegistro["QryCampo"] . " AS " . $aRegistro["QryCampoAlias"] . ", ";
   }
   $aCampo[$nIndiceCps]["Camp"] = $aRegistro["QryCampoNombre"];

   // Descripción del Orden
   if (($aRegistro["QryOrdenExpr"]==''?($aRegistro["QryCampoAlias"]==''?$aRegistro["QryCampo"]:$aRegistro["QryCampoAlias"]):$aRegistro["QryOrdenExpr"])==$Orden) {
     $cDesOrden  = $aRegistro["QryCampoNombre"] . " " ;
     $cDesOrden .= $Forma=="ASC"?$txt['Ascendente']:$txt['Descendente'] ;
   }

   // Descripción del Filtro 1
   if (($aRegistro["QryFiltroExpr"]==''?$aRegistro["QryCampo"]:$aRegistro["QryFiltroExpr"])==$CpoFiltro1) {
     $cDesFiltro1 = $aRegistro["QryCampoNombre"] . " " ;

     if ($TipFiltro1=="IN") {
       $cDesFiltro1 .= $txt['En'] . " (" . $TxtFiltro1 . ") " ;
     } elseif ($TipFiltro1=="NOT IN") {
       $cDesFiltro1 .= $txt['NoEn'] . " (" . $TxtFiltro1 . ") " ;
     } elseif ($TipFiltro1=="BETWEEN") {
       $cDesFiltro1 .= $txt['Entre'] . " (" . $TxtFiltro1 . ") " ;
     } elseif ($TipFiltro1=="NOT BETWEEN") {
       $cDesFiltro1 .= $txt['NoEntre'] . " (" . $TxtFiltro1 . ") " ;
     } elseif ($TipFiltro1=="LIKE-E") {
       $cDesFiltro1 .= $txt['EmpiezaCon'] . " " . $TxtFiltro1 . " " ;
     } elseif ($TipFiltro1=="NOT LIKE-E") {
       $cDesFiltro1 .= $txt['NoEmpiezaCon'] . " " . $TxtFiltro1 . " " ;
     } elseif ($TipFiltro1=="LIKE-C") {
       $cDesFiltro1 .= $txt['ContieneA'] . " " . $TxtFiltro1 . " " ;
     } elseif ($TipFiltro1=="NOT LIKE-C") {
       $cDesFiltro1 .= $txt['NoContieneA'] . " " . $TxtFiltro1 . " " ;
     } elseif ($TipFiltro1=="LIKE-T") {
       $cDesFiltro1 .= $txt['TerminaCon'] . " " . $TxtFiltro1 . " " ;
     } elseif ($TipFiltro1=="NOT LIKE-T") {
       $cDesFiltro1 .= $txt['NoTerminaCon'] . " " . $TxtFiltro1 . " " ;
     } else {
       $cDesFiltro1 .= $TipFiltro1 . " " . $TxtFiltro1 . " " ;
     }
   }

   // Descripción del Filtro 2
   if (($aRegistro["QryFiltroExpr"]==''?$aRegistro["QryCampo"]:$aRegistro["QryFiltroExpr"])==$CpoFiltro2) {
     $cDesFiltro2 = $aRegistro["QryCampoNombre"] . " " ;

     if ($TipFiltro2=="IN") {
       $cDesFiltro2 .= $txt['En'] . " (" . $TxtFiltro2 . ") " ;
     } elseif ($TipFiltro2=="NOT IN") {
       $cDesFiltro2 .= $txt['NoEn'] . " (" . $TxtFiltro2 . ") " ;
     } elseif ($TipFiltro2=="BETWEEN") {
       $cDesFiltro2 .= $txt['Entre'] . " (" . $TxtFiltro2 . ") " ;
     } elseif ($TipFiltro2=="NOT BETWEEN") {
       $cDesFiltro2 .= $txt['NoEntre'] . " (" . $TxtFiltro2 . ") " ;
     } elseif ($TipFiltro2=="LIKE-E") {
       $cDesFiltro2 .= $txt['EmpiezaCon'] . " " . $TxtFiltro2 . " " ;
     } elseif ($TipFiltro2=="NOT LIKE-E") {
       $cDesFiltro2 .= $txt['NoEmpiezaCon'] . " " . $TxtFiltro2 . " " ;
     } elseif ($TipFiltro2=="LIKE-C") {
       $cDesFiltro2 .= $txt['ContieneA'] . " " . $TxtFiltro2 . " " ;
     } elseif ($TipFiltro2=="NOT LIKE-C") {
       $cDesFiltro2 .= $txt['NoContieneA'] . " " . $TxtFiltro2 . " " ;
     } elseif ($TipFiltro2=="LIKE-T") {
       $cDesFiltro2 .= $txt['TerminaCon'] . " " . $TxtFiltro2 . " " ;
     } elseif ($TipFiltro2=="NOT LIKE-T") {
       $cDesFiltro2 .= $txt['NoTerminaCon'] . " " . $TxtFiltro2 . " " ;
     } else {
       $cDesFiltro2 .= $TipFiltro2 . " " . $TxtFiltro2 . " " ;
     }
   }

   $nIndiceCps++;
}
mysql_free_result ($nResultado);

// Armado de la SELECT [Datos de Pantalla "Más Información"]
$cSql = "SELECT * FROM sysMasInfo WHERE ModNombre='" . $_SESSION["gbl".$conf["VariablesSESSION"]."Modulo"] . "' AND MInPosicion>1 ORDER BY MInPosicion";
$nResultado = mysql_query ($cSql) or fErrorSQL($conf["EstadoSitio"], "<br /><br /><b>Error en la consulta:</b><br />" . $cSql . "<br /><br /><b>Tipo de error:</b><br />" . mysql_error() . "<br />");
while ($aRegistro = mysql_fetch_array($nResultado)) {
   if ($aRegistro["MInCampoAlias"]=='') {
      $cnfConsulta .= $aRegistro["MInCampo"] . ", ";
   } else {
      $cnfConsulta .= $aRegistro["MInCampo"] . " AS " . $aRegistro["MInCampoAlias"] . ", ";
   }
   $aCampo[$nIndiceCps]["Camp"] = $aRegistro["MInCampoNombre"];
   $nIndiceCps++;
}
mysql_free_result ($nResultado);

$cnfConsulta = substr_replace($cnfConsulta, '', -2, 1);


// Cláusula FROM dentro de la SELECT
$cSql = "SELECT * FROM sysFrom WHERE ModNombre='" . $_SESSION["gbl".$conf["VariablesSESSION"]."Modulo"] . "'";
$nResultado = mysql_query ($cSql) or fErrorSQL($conf["EstadoSitio"], "<br /><br /><b>Error en la consulta:</b><br />" . $cSql . "<br /><br /><b>Tipo de error:</b><br />" . mysql_error() . "<br />");
$aRegistro = mysql_fetch_array($nResultado);
$cnfConsulta .= "FROM ";
if ($aRegistro["QryFromAlias"]=='') {
   $cnfConsulta .= $aRegistro["QryFrom"] . " ";
} else {
   $cnfConsulta .= $aRegistro["QryFrom"] . " AS " . $aRegistro["QryFromAlias"] . " ";
}
mysql_free_result ($nResultado);


// Cláusula JOIN dentro de la SELECT
$cSql = "SELECT * FROM sysJoin WHERE ModNombre='" . $_SESSION["gbl".$conf["VariablesSESSION"]."Modulo"] . "' AND QryJoinUso<>'R'";
$nResultado = mysql_query ($cSql) or fErrorSQL($conf["EstadoSitio"], "<br /><br /><b>Error en la consulta:</b><br />" . $cSql . "<br /><br /><b>Tipo de error:</b><br />" . mysql_error() . "<br />");
while ($aRegistro = mysql_fetch_array($nResultado)) {
   if ($aRegistro["QryJoinTipo"]=="L") {
      $cnfConsulta .= "LEFT ";
   } elseif ($aRegistro["QryJoinTipo"]=="R") {
      $cnfConsulta .= "RIGHT ";
   } elseif ($aRegistro["QryJoinTipo"]=="I") {
      $cnfConsulta .= "INNER ";
   }
   $cnfConsulta .= "JOIN ";

   if ($aRegistro["QryJoinAlias"]=='') {
      $cnfConsulta .= $aRegistro["QryJoin"] . " ";
   } else {
      $cnfConsulta .= $aRegistro["QryJoin"] . " AS " . $aRegistro["QryJoinAlias"] . " ";
   }
   $cnfConsulta .= "ON " . $aRegistro["QryJoinExpr"] . " " ;
}
mysql_free_result ($nResultado);


// Cláusula WHERE dentro de la SELECT
$cSql = "SELECT * FROM sysWhere WHERE ModNombre='" . $_SESSION["gbl".$conf["VariablesSESSION"]."Modulo"] . "'";
$nResultado = mysql_query ($cSql) or fErrorSQL($conf["EstadoSitio"], "<br /><br /><b>Error en la consulta:</b><br />" . $cSql . "<br /><br /><b>Tipo de error:</b><br />" . mysql_error() . "<br />");
$aRegistro = mysql_fetch_array($nResultado);
$cnfFiltInic = $aRegistro["QryWhereExpr"];
mysql_free_result ($nResultado);


// Filtro por defecto y permanente
$cFiltroIni = "";

$nPosIni = strpos($cnfFiltInic,"{");
while ( !($nPosIni === false) ) {
  $nPosFin = strpos($cnfFiltInic,"}");
  $nPosSep = strpos($cnfFiltInic,"_");

  $cFiltroIni .= substr($cnfFiltInic, 0, $nPosIni);

  $cQueEs    = "_" . substr($cnfFiltInic, $nPosIni+1, $nPosSep-$nPosIni-1);
  $cVariable = substr($cnfFiltInic, $nPosSep+1, $nPosFin-$nPosSep-1);

  $cFiltroIni .= ${$cQueEs}[$cVariable];

  $cnfFiltInic = substr($cnfFiltInic, $nPosFin+1);

  $nPosIni = strpos($cnfFiltInic,"{");
}

$cFiltroIni .= $cnfFiltInic;


// Captura las variables para Filtros Adicionales
$cFiltro = ($cFiltroIni==""?"":" WHERE " . $cFiltroIni) ;

if ( strlen($CpoFiltro1)!=0 && strlen($TxtFiltro1)!=0 ) {
  $cFiltro .= ($cFiltro==""?" WHERE ( ":" AND ( ") ;

  if ( $TipFiltro1=="IN" || $TipFiltro1=="NOT IN" ) {
    $cFiltro .= "(" . $CpoFiltro1 . " " . $TipFiltro1 . " ('" . str_replace(",","','",$TxtFiltro1) . "')) ";
  } elseif ( $TipFiltro1=="BETWEEN" || $TipFiltro1=="NOT BETWEEN" ) {
    $cFiltro .= "(" . $CpoFiltro1 . " " . $TipFiltro1 . " '" . substr_replace ($TxtFiltro1,"' AND '",strpos($TxtFiltro1,","),1) . "') ";
  } elseif ( strstr($TipFiltro1,"LIKE") ) {
    $cFiltro .= "(" . $CpoFiltro1 . " " . substr($TipFiltro1,0,strpos($TipFiltro1,"-")) . " '" . (strstr($TipFiltro1,"-E")?"":"%") . $TxtFiltro1 . (strstr($TipFiltro1,"-T")?"":"%") . "') ";
  } else {
    $cFiltro .= "(" . $CpoFiltro1 . " " . $TipFiltro1 . " '" . $TxtFiltro1 . "') ";
  }

  if ( strlen($CpoFiltro2)!=0 && strlen($TxtFiltro2)!=0 ) {
    $cFiltro .= $NexFiltro . " ";
    if ( $TipFiltro2=="IN" || $TipFiltro2=="NOT IN" ) {
      $cFiltro .= "(" . $CpoFiltro2 . " " . $TipFiltro2 . " ('" . str_replace(",","','",$TxtFiltro2) . "')) ";
    } elseif ( $TipFiltro2=="BETWEEN" || $TipFiltro2=="NOT BETWEEN" ) {
      $cFiltro .= "(" . $CpoFiltro2 . " " . $TipFiltro2 . " '" . substr_replace ($TxtFiltro2,"' AND '",strpos($TxtFiltro2,","),1) . "') ";
    } elseif ( strstr($TipFiltro2,"LIKE") ) {
      $cFiltro .= "(" . $CpoFiltro2 . " " . substr($TipFiltro2,0,strpos($TipFiltro2,"-")) . " '" . (strstr($TipFiltro2,"-E")?"":"%") . $TxtFiltro2 . (strstr($TipFiltro2,"-T")?"":"%") . "') ";
    } else {
      $cFiltro .= "(" . $CpoFiltro2 . " " . $TipFiltro2 . " '" . $TxtFiltro2 . "') ";
    }
  }
  $cFiltro .= ") ";

}


// Arma la instrucción SQL y luego la ejecuta
$cSql = $cnfConsulta . $cFiltro . ($Orden==""?"":" ORDER BY " . $Orden . " " . $Forma) ;

$nResultExp = mysql_query ($cSql) or fErrorSQL($conf["EstadoSitio"], "<br /><br /><b>Error en la consulta:</b><br />" . $cSql . "<br /><br /><b>Tipo de error:</b><br />" . mysql_error() . "<br />");


// Armado de la "Cabecera" del archivo
if ($conf['Fecha']=="DMY") {
  $cFmtoFecha = 'd-m-Y H:i:s' ;
} elseif ($conf['Fecha']=="MDY") {
  $cFmtoFecha = 'm-d-Y H:i:s' ;
} else {
  $cFmtoFecha = 'Y-m-d H:i:s' ;
}

$cDesFiltro = $cDesFiltro1 ;
if ($cDesFiltro2!="") {
  $cDesFiltro .= ($NexFiltro=="AND"?$txt['Y']:$txt['O']) . " " . $cDesFiltro2 ;
}

$cTitulo  = $txt['VolcadoDe'] . ": " . $cnfModNombre . "\n" ;
$cTitulo .= $txt['FechaHora'] . ": " . date($cFmtoFecha) . "\n" ;
$cTitulo .= $txt['Orden'] . ": " . $cDesOrden . "\n" ;
$cTitulo .= $txt['Filtro'] . ": " . $cDesFiltro . "\n" ;


// Generar archivo Word o Excel
if ($_GET["Tipo"]=="W") {
  $cTipArchivo = "msword" ;
  $cExtArchivo = "doc" ;
} else {
  $cTipArchivo = "vnd.ms-excel" ;
  $cExtArchivo = "xls" ;
}

// Información del header para el browser: determina el tipo de archivo ('.doc' o '.xls')
header("Content-Type: application/" . $cTipArchivo);
header("Content-Disposition: attachment; filename=" . $cnfModNombre . "." . $cExtArchivo);
header("Pragma: no-cache");
header("Expires: 0");


if ($_GET["Tipo"]=="W") {     /* Formato para documentos Word ('.doc') */

  // Título
  echo($cTitulo . "\n");

  while ($aFila = mysql_fetch_row($nResultExp)) {
    $cRegistro = "";
    for ($nColumna=0; $nColumna<mysql_num_fields($nResultExp);$nColumna++) {
      // Nombre del campo
      $cRegistro .= $aCampo[$nColumna]["Camp"] . ":\t";
      // Valor del campo
      $cRegistro .= (isset($aFila[$nColumna])?strip_tags($aFila[$nColumna]):"NULL") . "\n" ;
    }
    echo("----------------------------------------------------\n");
    echo($cRegistro);
  }
  echo("----------------------------------------------------\n");

} else {        /* Formato para documentos Excel ('.xls') */

  // Título
  echo($cTitulo . "\n");

  // Nombres de los campos
  for ($nColumna=1; $nColumna<$nIndiceCps; $nColumna++) {
    echo($aCampo[$nColumna]["Camp"] . "\t");
  }
  echo("\n");

  // Valores de los campos
  while ($aFila = mysql_fetch_row($nResultExp)) {
    $cRegistro = "";
    for ($nColumna=1; $nColumna<mysql_num_fields($nResultExp);$nColumna++) {
      $cRegistro .= (isset($aFila[$nColumna])?strip_tags($aFila[$nColumna]):"NULL") . "\t" ;
    }
    // Se reemplazan \n y \r por un espacio
    $cRegistro = preg_replace("/\r\n|\n\r|\n|\r/", " ", $cRegistro);
    echo($cRegistro);
    echo("\n");
  }


}
mysql_free_result ($nResultExp);
?>
