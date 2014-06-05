<?
/*
******************************************************************************
* Administrador de Contenidos                                                *
* -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-= *
*                                                                            *
* (C) 2002, Fabián Chesta                                                    *
*                                                                            *
* Comentarios:                                                               *
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

$nTiempo = fGetMicroTime();

// Determina si es "Info Normal" o "Info Relacionada"
$cnfModulo = ((isset($_GET["Desde"]) and $_GET["Desde"]=="Relacion")?$_GET["ModuloRel"]:$_SESSION["gbl".$conf["VariablesSESSION"]."Modulo"]) ;


// Determina los permisos necesarios para las diferentes acciones
$cSql = "SELECT ModTexto, ModInfoAdic, ModInfoRela, PerVer, PerEditar, PerAgregar, PerBorrar, PerAcciones, PerExportar, VerCntLineas FROM sysModulos LEFT JOIN sysModUsu ON sysModulos.ModNombre=sysModUsu.ModNombre WHERE sysModulos.ModNombre='" . $cnfModulo . "' AND sysModUsu.UsuAlias='" . $_SESSION["gbl".$conf["VariablesSESSION"]."Alias"] . "'";
$nResultado = mysql_query ($cSql) or fErrorSQL($conf["EstadoSitio"], "<br /><br /><b>Error en la consulta:</b><br />" . $cSql . "<br /><br /><b>Tipo de error:</b><br />" . mysql_error() . "<br />");
$aRegistro  = mysql_fetch_array($nResultado);

$cnfModNombre   = $aRegistro["ModTexto"];
$cnfModInfoAdic = $aRegistro["ModInfoAdic"];
$cnfModInfoRela = $aRegistro["ModInfoRela"];

$cnfPerVer      = $aRegistro["PerVer"];
$cnfPerAgregar  = $aRegistro["PerAgregar"];
$cnfPerEditar   = $aRegistro["PerEditar"];
$cnfPerBorrar   = $aRegistro["PerBorrar"];
$cnfPerAcciones = $aRegistro["PerAcciones"];
$cnfPerExportar = $aRegistro["PerExportar"];

$cnfCntLineas   = $aRegistro["VerCntLineas"];

mysql_free_result ($nResultado);


// Control de Permisos
if ($cnfPerVer!='S') {
  header ("Location: Index.php");
  exit(0);
}


// Nombre de la página actual
$cEsta = substr ( str_replace (".php", "", $_SERVER["PHP_SELF"]), strrpos (str_replace (".php", "", $_SERVER["PHP_SELF"]), "/")+1 ) ;


// Hay multiples idiomas..?
$nCntIdiomas = 0;
$cSql = "SELECT LanName, LanParticle, LanFlag FROM sysLenguajes ORDER BY LanOrder";
$nResultado = mysql_query ($cSql) or fErrorSQL($conf["EstadoSitio"], "<br /><br /><b>Error en la consulta:</b><br />" . $cSql . "<br /><br /><b>Tipo de error:</b><br />" . mysql_error() . "<br />");
while ($aRegistro = mysql_fetch_array($nResultado)) {
  $aIdiomas["Name"][$nCntIdiomas] = $aRegistro["LanName"];
  $aIdiomas["Part"][$nCntIdiomas] = $aRegistro["LanParticle"];
  $aIdiomas["Flag"][$nCntIdiomas] = $aRegistro["LanFlag"];
  $nCntIdiomas++;
}


// Determina la existencia de Acciones personalizadas (y sus permisos)
if ($cnfPerAcciones) {
  $cSql = "SELECT * FROM sysAcciones WHERE ModNombre='" . $cnfModulo . "' ORDER BY AccOrden";
  $nResultado = mysql_query ($cSql) or fErrorSQL($conf["EstadoSitio"], "<br /><br /><b>Error en la consulta:</b><br />" . $cSql . "<br /><br /><b>Tipo de error:</b><br />" . mysql_error() . "<br />");

  $aFilas["R"] = 0 ;  // Cantidad de Acciones a nivel de Registro
  $aFilas["G"] = 0 ;  // Cantidad de Acciones a nivel General
  while ($aRegistro  = mysql_fetch_array($nResultado)) {
    $aAcciones[$aFilas["R"]+$aFilas["G"]]["Nivel"]     = $aRegistro["AccNivel"] ;
    $aAcciones[$aFilas["R"]+$aFilas["G"]]["Titulo"]    = $aRegistro["AccTitulo"] ;
    $aAcciones[$aFilas["R"]+$aFilas["G"]]["Link"]      = $aRegistro["AccLink"] ;
    $aAcciones[$aFilas["R"]+$aFilas["G"]]["EjecutarSi"] = $aRegistro["AccEjecutarSi"] ;
    $aAcciones[$aFilas["R"]+$aFilas["G"]]["VentAlto"]  = $aRegistro["AccVentAlto"] ;
    $aAcciones[$aFilas["R"]+$aFilas["G"]]["VentAncho"] = $aRegistro["AccVentAncho"] ;

    $aFilas[$aRegistro["AccNivel"]]++ ;
  }
  mysql_free_result ($nResultado);
}


$nCntCposNoCons = 0 ;

// Armado de la SELECT, los posibles Ordenes y los posibles Filtros
$cSql = "SELECT * FROM sysInfo WHERE ModNombre='" . $cnfModulo . "' ORDER BY QryPosicion";
$nResultado = mysql_query ($cSql) or fErrorSQL($conf["EstadoSitio"], "<br /><br /><b>Error en la consulta:</b><br />" . $cSql . "<br /><br /><b>Tipo de error:</b><br />" . mysql_error() . "<br />");
$cnfConsulta = "SELECT ";
$cnfConsAcPe = "SELECT * ";    // Armado de la SELECT por si es necesaria en condiciones para Acciones Personalizada a nivel de Registro
$nIndiceOrd  = 0;
$nIndiceFil  = 0;
$nIndiceCps  = 0;

while ($aRegistro = mysql_fetch_array($nResultado)) {

   // Armado de la SELECT
   if ($aRegistro["QryCampoAlias"]=='') {
      $cnfConsulta .= $aRegistro["QryCampo"] . ", ";
   } else {
      $cnfConsulta .= $aRegistro["QryCampo"] . " AS " . $aRegistro["QryCampoAlias"] . ", ";
   }

   if ($aRegistro["QryPosicion"]!=999) {

     // Array con los distintos Campos
     $aCampo[$nIndiceCps]["Camp"] = $aRegistro["QryCampoNombre"];

     // Array con la alineacion de los Campos
     $aCampo[$nIndiceCps]["Alin"] = $aRegistro["QryAlineacion"];

     // Array con los tipos de Campos
     $aCampo[$nIndiceCps]["Imag"] = $aRegistro["QryCampoImagen"];

     // Si es una imagen tipo U busca el posible subdirectorio
     if ($aCampo[$nIndiceCps]["Imag"]=="U" && strstr($aCampo[$nIndiceCps]["Camp"],"[")) {
       $aCampo[$nIndiceCps]["SubD"] = trim(str_replace("]", "", str_replace("[", "", strstr($aCampo[$nIndiceCps]["Camp"], "[")))) ;
       $aCampo[$nIndiceCps]["Camp"] = trim(str_replace(strstr($aCampo[$nIndiceCps]["Camp"], "["), "", $aCampo[$nIndiceCps]["Camp"])) ;
     } else {
       $aCampo[$nIndiceCps]["SubD"] = "" ;
     }

     // Array con los distintos Ordenes posibles
     if ($aRegistro["QryOrden"]!='N' and $aRegistro["QryPosicion"]!=0) {
       $aCampo[$nIndiceCps]["Ordn"] = 'S';
       $aCampo[$nIndiceCps]["Expr"] = ($aRegistro["QryOrdenExpr"]==''?($aRegistro["QryCampoAlias"]==''?$aRegistro["QryCampo"]:$aRegistro["QryCampoAlias"]):$aRegistro["QryOrdenExpr"]) ;

       if ($aRegistro["QryOrden"]!='S') {
         $cnfOrdExpr = $aCampo[$nIndiceCps]["Expr"];
         $cnfOrdTipo = $aRegistro["QryOrden"]=="D"?"DESC":"ASC" ;
       }

       $nIndiceOrd++;
     } else {
       $aCampo[$nIndiceCps]["Ordn"] = 'N';
     }
     $nIndiceCps++;

     // Array con los distintos Filtros posibles
     if ($aRegistro["QryFiltro"]!='N' and $aRegistro["QryPosicion"]!=0) {
       $aFiltro[$nIndiceFil]["Expr"] = $aRegistro["QryFiltroExpr"]==''?$aRegistro["QryCampo"]:$aRegistro["QryFiltroExpr"];
       $aFiltro[$nIndiceFil]["Nomb"] = $aRegistro["QryCampoNombre"];

       $nIndiceFil++;
     }

   } else {

     $nCntCposNoCons++ ;

   }
}
mysql_free_result ($nResultado);

$cnfConsulta = substr_replace($cnfConsulta, '', -2, 1);


// Cláusula FROM dentro de la SELECT
$cSql = "SELECT * FROM sysFrom WHERE ModNombre='" . $cnfModulo . "'";
$nResultado = mysql_query ($cSql) or fErrorSQL($conf["EstadoSitio"], "<br /><br /><b>Error en la consulta:</b><br />" . $cSql . "<br /><br /><b>Tipo de error:</b><br />" . mysql_error() . "<br />");
$aRegistro = mysql_fetch_array($nResultado);
$cnfConsulta .= "FROM ";
$cnfConsAcPe .= "FROM ";
if ($aRegistro["QryFromAlias"]=='') {
   $cnfConsulta .= $aRegistro["QryFrom"] . " ";
   $cnfConsAcPe .= $aRegistro["QryFrom"] . " ";
} else {
   $cnfConsulta .= $aRegistro["QryFrom"] . " AS " . $aRegistro["QryFromAlias"] . " ";
   $cnfConsAcPe .= $aRegistro["QryFrom"] . " AS " . $aRegistro["QryFromAlias"] . " ";
}
$cTblFrom = $aRegistro["QryFrom"];
mysql_free_result ($nResultado);


// Si hay muchos idiomas, me fijo si esta tabla los necesita
$cMostrarFlag = "No" ;
if ($nCntIdiomas>1) {
  // Si hay una tabla para multiples idiomas debe llamarse Tabla_Lng
  $cSql = "SHOW TABLES LIKE '".$cTblFrom."_Lng'";
  $nResultAux1 = mysql_query ($cSql) or fErrorSQL($conf["EstadoSitio"], "<br /><br /><b>Error en la consulta:</b><br />" . $cSql . "<br /><br /><b>Tipo de error:</b><br />" . mysql_error() . "<br />");
  if ($aRegistAux1 = mysql_fetch_array($nResultAux1)) {
    $cMostrarFlag = "Si" ;
  }
  mysql_free_result ($nResultAux1);
}


// Cláusula JOIN dentro de la SELECT
$cSql = "SELECT * FROM sysJoin WHERE ModNombre='" . $cnfModulo . "' AND QryJoinUso IN ('I','A')";
$nResultado = mysql_query ($cSql) or fErrorSQL($conf["EstadoSitio"], "<br /><br /><b>Error en la consulta:</b><br />" . $cSql . "<br /><br /><b>Tipo de error:</b><br />" . mysql_error() . "<br />");
while ($aRegistro = mysql_fetch_array($nResultado)) {
  if ($aRegistro["QryJoinTipo"]=="L") {
     $cnfConsulta .= "LEFT ";
     $cnfConsAcPe .= "LEFT ";
  } elseif ($aRegistro["QryJoinTipo"]=="R") {
     $cnfConsulta .= "RIGHT ";
     $cnfConsAcPe .= "RIGHT ";
  } elseif ($aRegistro["QryJoinTipo"]=="I") {
     $cnfConsulta .= "INNER ";
     $cnfConsAcPe .= "INNER ";
  }
  $cnfConsulta .= "JOIN ";
  $cnfConsAcPe .= "JOIN ";

  if ($aRegistro["QryJoinAlias"]=='') {
     $cnfConsulta .= $aRegistro["QryJoin"] . " ";
     $cnfConsAcPe .= $aRegistro["QryJoin"] . " ";
  } else {
     $cnfConsulta .= $aRegistro["QryJoin"] . " AS " . $aRegistro["QryJoinAlias"] . " ";
     $cnfConsAcPe .= $aRegistro["QryJoin"] . " AS " . $aRegistro["QryJoinAlias"] . " ";
  }
  $cnfConsulta .= "ON " . $aRegistro["QryJoinExpr"] . " " ;
  $cnfConsAcPe .= "ON " . $aRegistro["QryJoinExpr"] . " " ;
}
mysql_free_result ($nResultado);


if (isset($_GET["Desde"]) and $_GET["Desde"]=="Relacion") {
  // Cláusula JOIN dentro de la SELECT para info Relacionada
  $cSql = "SELECT * FROM sysJoin WHERE ModNombre='" . $cnfModulo . "' AND RelModulo='" . $_GET["ModuloAct"] . "' AND QryJoinUso = 'R'";
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
}


// Cláusula WHERE dentro de la SELECT  (Filtro por defecto y permanente)
$cSql = "SELECT * FROM sysWhere WHERE ModNombre='" . $cnfModulo . "'";
$nResultado = mysql_query ($cSql) or fErrorSQL($conf["EstadoSitio"], "<br /><br /><b>Error en la consulta:</b><br />" . $cSql . "<br /><br /><b>Tipo de error:</b><br />" . mysql_error() . "<br />");
$aRegistro = mysql_fetch_array($nResultado);
$cnfFiltInic = $aRegistro["QryWhereExpr"];
mysql_free_result ($nResultado);

$cFiltroIni = "";
$nPosIni    = strpos($cnfFiltInic,"{");
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

// Arma el Filtro 
$cFiltro = ($cFiltroIni==""?"":" WHERE " . $cFiltroIni) ;


// Variables para control de los datos (Orden y Filtros Adicionales)
if (isset($_GET["Desde"]) and $_GET["Desde"]=="Relacion") {
  // Info Relacionada
  $Orden      = ((isset($_GET["Orden"]) and $_GET["Orden"]!="")?$_GET["Orden"]:$cnfOrdExpr) ;
  $Forma      = ((isset($_GET["Forma"]) and $_GET["Forma"]!="")?$_GET["Forma"]:$cnfOrdTipo) ;
  $cFiltro   .= ($cFiltro==""?" WHERE ( ":" AND ( ") . (strstr($_GET["CampoRel"],"XXCodigoXX")?str_replace("XXCodigoXX",$_GET["Codigo"],$_GET["CampoRel"]):$_GET["CampoRel"]."=".$_GET["Codigo"]) . ")" ;
  $Inicio     = 0 ;
  $nCantidad  = 1000000 ;
} else {
  // Info Normal
  $Orden      = ((isset($_GET["Orden"]) and $_GET["Orden"]!="")?$_GET["Orden"]:$cnfOrdExpr) ;
  $Forma      = ((isset($_GET["Forma"]) and $_GET["Forma"]!="")?$_GET["Forma"]:$cnfOrdTipo) ;
  $CpoFiltro1 = (isset($_GET["CpoFiltro1"])?fSacarBarras($_GET["CpoFiltro1"]):"") ;
  $TipFiltro1 = (isset($_GET["TipFiltro1"])?$_GET["TipFiltro1"]:"") ;
  $TxtFiltro1 = (isset($_GET["TxtFiltro1"])?$_GET["TxtFiltro1"]:"") ;     // ¿Será necesario usar urldecode en algunos hostings...?
  $NexFiltro  = (isset($_GET["NexFiltro"])?$_GET["NexFiltro"]:"") ;
  $CpoFiltro2 = (isset($_GET["CpoFiltro2"])?fSacarBarras($_GET["CpoFiltro2"]):"") ;
  $TipFiltro2 = (isset($_GET["TipFiltro2"])?$_GET["TipFiltro2"]:"") ;
  $TxtFiltro2 = (isset($_GET["TxtFiltro2"])?$_GET["TxtFiltro2"]:"") ;     // ¿Será necesario usar urldecode en algunos hostings...?
  $Inicio     = ((isset($_GET["Inicio"]) and $_GET["Inicio"]!="")?$_GET["Inicio"]:0) ;
  $nCantidad  = ((isset($_GET["Cantidad"]) and $_GET["Cantidad"]!="")?$_GET["Cantidad"]:$cnfCntLineas) ;
}


// Arma el Filtro con adicionales 
if ( strlen($CpoFiltro1)!=0 and strlen($TxtFiltro1)!=0 ) {
  $cFiltro .= ($cFiltro==""?" WHERE ( ":" AND ( ") ;

  if ( $TipFiltro1=="IN" or $TipFiltro1=="NOT IN" ) {
    $cFiltro .= "(" . $CpoFiltro1 . " " . $TipFiltro1 . " ('" . str_replace(",","','",fPonerBarras($TxtFiltro1)) . "')) ";
  } elseif ( $TipFiltro1=="BETWEEN" or $TipFiltro1=="NOT BETWEEN" ) {
    $cFiltro .= "(" . $CpoFiltro1 . " " . $TipFiltro1 . " '" . substr_replace (fPonerBarras($TxtFiltro1),"' AND '",strpos(fPonerBarras($TxtFiltro1),","),1) . "') ";
  } elseif ( strstr($TipFiltro1,"LIKE") ) {
    $cFiltro .= "(" . $CpoFiltro1 . " " . substr($TipFiltro1,0,strpos($TipFiltro1,"-")) . " '" . (strstr($TipFiltro1,"-E")?"":"%") . fPonerBarras($TxtFiltro1) . (strstr($TipFiltro1,"-T")?"":"%") . "') ";
  } else {
    $cFiltro .= "(" . $CpoFiltro1 . " " . $TipFiltro1 . " '" . fPonerBarras($TxtFiltro1) . "') ";
  }

  if ( strlen($CpoFiltro2)!=0 and strlen($TxtFiltro2)!=0 ) {
    $cFiltro .= $NexFiltro . " ";
    if ( $TipFiltro2=="IN" or $TipFiltro2=="NOT IN" ) {
      $cFiltro .= "(" . $CpoFiltro2 . " " . $TipFiltro2 . " ('" . str_replace(",","','",fPonerBarras($TxtFiltro2)) . "')) ";
    } elseif ( $TipFiltro2=="BETWEEN" or $TipFiltro2=="NOT BETWEEN" ) {
      $cFiltro .= "(" . $CpoFiltro2 . " " . $TipFiltro2 . " '" . substr_replace (fPonerBarras($TxtFiltro2),"' AND '",strpos(fPonerBarras($TxtFiltro2),","),1) . "') ";
    } elseif ( strstr($TipFiltro2,"LIKE") ) {
      $cFiltro .= "(" . $CpoFiltro2 . " " . substr($TipFiltro2,0,strpos($TipFiltro2,"-")) . " '" . (strstr($TipFiltro2,"-E")?"":"%") . fPonerBarras($TxtFiltro2) . (strstr($TipFiltro2,"-T")?"":"%") . "') ";
    } else {
      $cFiltro .= "(" . $CpoFiltro2 . " " . $TipFiltro2 . " '" . fPonerBarras($TxtFiltro2) . "') ";
    }
  } else {
    $NexFiltro  = "AND" ;
    $CpoFiltro2 = "" ;
    $TipFiltro2 = "" ;
    $TxtFiltro2 = "" ;
  }
  $cFiltro .= ") ";

} else {
  $CpoFiltro1 = "" ;
  $TipFiltro1 = "" ;
  $TxtFiltro1 = "" ;
  $NexFiltro  = "AND" ;
  $CpoFiltro2 = "" ;
  $TipFiltro2 = "" ;
  $TxtFiltro2 = "" ;
}


// Arma la instrucción SQL y luego la ejecuta
$cSql = $cnfConsulta . $cFiltro . ($Orden==""?"":" ORDER BY " . $Orden . " " . $Forma) ;

$nResultado = mysql_query ($cSql) or fErrorSQL($conf["EstadoSitio"], "<br /><br /><b>Error en la consulta:</b><br />" . $cSql . "<br /><br /><b>Tipo de error:</b><br />" . mysql_error() . "<br />");


if ( mysql_num_rows($nResultado)==0 ) {
  $nParar = -1;
  $nFilas = -2;

  $cPaginas = $txt['SinRegistros'];

} else {
  $nFilas    = (mysql_num_rows($nResultado)-1);
  $nColumnas = (mysql_num_fields($nResultado)-1);

  if ( $nFilas < $Inicio ) {
    $Inicio -= $nCantidad;
  }

  if ( $nFilas >= ($nCantidad + $Inicio) ) {
    $nParar = $nCantidad + $Inicio - 1;
  } else {
    $nParar = $nFilas;
  }

  $cPaginas = $txt['PaginaX'] . " " . (floor($nFilas/$nCantidad)+1);

  mysql_data_seek ($nResultado, $Inicio);
}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
    "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
  <title></title>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">

  <link rel="stylesheet" href="Estilos/hde.css" type="text/css">

  <script language="JavaScript" type="text/javascript" src="Funciones/Funciones.js"></script>
  <script language="JavaScript" type="text/javascript" src=" Funciones/tigra_tables.js"></script>

  <script language="JavaScript" type="text/javascript">
    //<![CDATA[
    /**
    * Pide la confirmación del usuario para eliminar un registro.
    **/
    function Borrar(Donde,Codigo,Inicio,Orden,Forma,CpoFiltro1,TipFiltro1,TxtFiltro1,NexFiltro,CpoFiltro2,TipFiltro2,TxtFiltro2,vInicio,vCantidad) {
      if (confirm('<?= $txt["ConfBorr"]?>')) {
        location.href = 'ABM.php?Accion=Borrar&Codigo='+Codigo+'&Inicio='+Inicio+'&Orden='+Orden+'&Forma='+Forma+'&CpoFiltro1='+CpoFiltro1+'&TipFiltro1='+TipFiltro1+'&TxtFiltro1='+TxtFiltro1+'&NexFiltro='+NexFiltro+'&CpoFiltro2='+CpoFiltro2+'&TipFiltro2='+TipFiltro2+'&TxtFiltro2='+TxtFiltro2+'&Inicio='+vInicio+'&Cantidad='+vCantidad ;
      }
    }

    /**
     * Pide la confirmación del usuario para ejecutar una acción personalizada.
     **/
    function Ejecutar(Donde,Que,Mensaje,Codigo,Inicio,Orden,Forma,CpoFiltro1,TipFiltro1,TxtFiltro1,NexFiltro,CpoFiltro2,TipFiltro2,TxtFiltro2,vInicio,vCantidad) {
      if (confirm(Mensaje)) {
        location.href = Que+'?Codigo='+Codigo+'&Inicio='+Inicio+'&Orden='+Orden+'&Forma='+Forma+'&CpoFiltro1='+CpoFiltro1+'&TipFiltro1='+TipFiltro1+'&TxtFiltro1='+TxtFiltro1+'&NexFiltro='+NexFiltro+'&CpoFiltro2='+CpoFiltro2+'&TipFiltro2='+TipFiltro2+'&TxtFiltro2='+TxtFiltro2+'&Inicio='+vInicio+'&Cantidad='+vCantidad ;
      }
    }
    //]]>
  </script>
</head>

<body bgcolor="#FFFFFF" text="#000000" style="margin:0;">

<center><span class="gralNormal" style="font-weight: bold;"><?= $cnfModNombre?></span></center>

<table id="infoModulo" width="98%" cellspacing="1" cellpadding="1" align="center" border="0" class="gralTabla">
  <tr bgcolor="#929292" align="center">
    <td><b>N&ordm;</b></td><? 
    for ($nElem=1; $nElem<count($aCampo); $nElem++) {
      $cURLData = "&amp;Orden=" . $aCampo[$nElem]["Expr"] . "&amp;Modulo=" . $cnfModulo . ((isset($_GET["Desde"]) and $_GET["Desde"]=="Relacion")?"&amp;Desde=Relacion&amp;ModuloRel=".$cnfModulo."&amp;ModuloAct=".$_GET["ModuloAct"]."&amp;CampoRel=".urlencode($_GET["CampoRel"])."&amp;Codigo=".$_GET["Codigo"]:"") . "&amp;CpoFiltro1=" . fPonerBarras($CpoFiltro1) . "&amp;TipFiltro1=" . $TipFiltro1 . "&amp;TxtFiltro1=" . urlencode($TxtFiltro1) . "&amp;NexFiltro=" . $NexFiltro . "&amp;CpoFiltro2=" . fPonerBarras($CpoFiltro2) . "&amp;TipFiltro2=" . $TipFiltro2 . "&amp;TxtFiltro2=" . urlencode($TxtFiltro2) . "&amp;Inicio=" . $Inicio  . "&amp;Cantidad=" . $nCantidad ; 
      $cAscOn = "";    $cDesOn = "";
      if ($aCampo[$nElem]["Expr"] == $Orden) {
        if ($Forma == "ASC") 
          $cAscOn = "On";
        else
          $cDesOn = "On";
      } ?>
      <td><? 
        if ($conf['ModOrden'] == "2d") { ?>
          <table width="100%" height="100%" class="gralTabla">
            <tr>
              <td width="1%"><? 
                if ($aCampo[$nElem]["Ordn"] == 'S') { ?>
                  <a href="<?= $cEsta?>.php?Forma=ASC<?= $cURLData?>"><img src="Imagenes/imgFlechaAsc<?= $cAscOn?>.gif" width="10" height="9" border="0" valign="middle" title="<?= $txt['Ascendente']?>" alt=""></a><? 
                } ?>
              </td>
              <td width="99%" align="center" rowspan="2"><b><?= $aCampo[$nElem]["Camp"]?></b></td>
            </tr>
            <tr>
              <td width="1%"><? 
                if ($aCampo[$nElem]["Ordn"] == 'S') { ?>
                  <a href="<?= $cEsta?>.php?Forma=DESC<?= $cURLData?>"><img src="Imagenes/imgFlechaDes<?= $cDesOn?>.gif" width="10" height="8" border="0" valign="middle" title="<?= $txt['Descendente']?>" alt=""></a><? 
                } ?>
              </td>
            </tr>
          </table><? 
        } elseif ($conf['ModOrden'] == "2i") { ?>
          <table width="100%" height="100%" class="gralTabla">
            <tr>
              <td width="99%" align="center" rowspan="2"><b><?= $aCampo[$nElem]["Camp"]?></b></td>
              <td width="1%"><? 
                if ($aCampo[$nElem]["Ordn"] == 'S') { ?>
                  <a href="<?= $cEsta?>.php?Forma=ASC<?= $cURLData?>"><img src="Imagenes/imgFlechaAsc<?= $cAscOn?>.gif" width="10" height="9" border="0" valign="middle" title="<?= $txt['Ascendente']?>" alt=""></a><? 
                } ?>
              </td>
            </tr>
            <tr>
              <td width="1%"><? 
                if ($aCampo[$nElem]["Ordn"] == 'S') { ?>
                  <a href="<?= $cEsta?>.php?Forma=DESC<?= $cURLData?>"><img src="Imagenes/imgFlechaDes<?= $cDesOn?>.gif" width="10" height="8" border="0" valign="middle" title="<?= $txt['Descendente']?>" alt=""></a><? 
                } ?>
              </td>
            </tr>
          </table><? 
        } elseif ($conf['ModOrden'] == "3") { ?>
          <b><?= $aCampo[$nElem]["Camp"]?></b><? 
          if ($aCampo[$nElem]["Ordn"] == 'S') { ?>
            <a href="<?= $cEsta?>.php?Forma=ASC<?= $cURLData?>"><img src="Imagenes/imgFlechaAsc<?= $cAscOn?>.gif" width="10" height="9" border="0" valign="middle" title="<?= $txt['Ascendente']?>" alt=""></a>
            <a href="<?= $cEsta?>.php?Forma=DESC<?= $cURLData?>"><img src="Imagenes/imgFlechaDes<?= $cDesOn?>.gif" width="10" height="8" border="0" valign="middle" title="<?= $txt['Descendente']?>" alt=""></a><? 
          } 
        } else { 
          if ($aCampo[$nElem]["Ordn"] == 'S') { 
            if ($aCampo[$nElem]["Expr"] == $Orden) {
              if ($Forma == "ASC") { ?>
                <a class="columna" href="<?= $cEsta?>.php?Forma=DESC<?= $cURLData?>"><b><?= $aCampo[$nElem]["Camp"]?></b></a><img src="Imagenes/imgFlechaAsc.gif" width="10" height="9" border="0" valign="middle" title="<?= $txt['Ascendente']?>" alt=""><? 
              } else { ?>
                <a class="columna" href="<?= $cEsta?>.php?Forma=ASC<?= $cURLData?>"><b><?= $aCampo[$nElem]["Camp"]?></b></a><img src="Imagenes/imgFlechaDes.gif" width="10" height="9" border="0" valign="middle" title="<?= $txt['Descendente']?>" alt=""><? 
              }
            } else { ?>
              <a class="columna" href="<?= $cEsta?>.php?Forma=ASC<?= $cURLData?>"><b><?= $aCampo[$nElem]["Camp"]?></b></a><? 
            }
          } else { ?>
            <b><?= $aCampo[$nElem]["Camp"]?></b><? 
          } 
        } ?>
      </td><? 
    } 
    if ( $_SESSION["gbl".$conf["VariablesSESSION"]."Tipo"]=="I" ) { ?>
      <td><b><?= $txt['InfoArchivo']?></b></td><? 
    } 
    if ($conf["MostrarBotonABMDesactivado"]=="Si" or (strstr($cnfModInfoAdic . $cnfModInfoRela, "S") or ($_GET["Desde"]!="Relacion" and strstr($cnfPerEditar . $cnfPerBorrar . $cnfPerAcciones, "S")))) { ?>
      <td><b><?= $txt['Acciones']?></b></td><? 
    } ?>
  </tr><?
  for ($nFilaActual=$Inicio; $nFilaActual<=$nParar; $nFilaActual++ ) {
    $aRegistro  = mysql_fetch_row($nResultado) ; ?>
    <tr>
      <td align="center"><?= $nFilaActual+1?></td><?
    for ($nColumnaActual=1; $nColumnaActual<=($nColumnas-$nCntCposNoCons); $nColumnaActual++ ) {
      if ($aCampo[$nColumnaActual]["Alin"]=="D") {
        $cAlineacion = "right";
      } elseif ($aCampo[$nColumnaActual]["Alin"]=="C") {
        $cAlineacion = "center";
      } else {
        $cAlineacion = "left";
      } ?>
      <td align="<?= $cAlineacion?>">
        <?
        if ($aRegistro[$nColumnaActual]!="") {
          if ($_SESSION["gbl".$conf["VariablesSESSION"]."Tipo"]=="I" and $aCampo[$nColumnaActual]["Imag"]!="N") {
            $aCampo[$nColumnaActual]["Imag"] = ($aRegistro[1]=="Documentos"?"A":"S") ;
          }
          if ($aCampo[$nColumnaActual]["Imag"]=="S") {
            echo(substr_count($aRegistro[$nColumnaActual],"/")==1?$aRegistro[$nColumnaActual]:substr(strstr($aRegistro[$nColumnaActual],"/"),1)); 
            unset($aPropImg);  unset($cInfoArc);
            if (is_file("../Upload/" . $aRegistro[$nColumnaActual])) {
              $aPropImg = getimagesize("../Upload/" . $aRegistro[$nColumnaActual]) ;
              $cInfoArc = $aPropImg[0]."x".$aPropImg[1] . " - " . filesize($conf['DirUpload'] . $aRegistro[$nColumnaActual]) . " bytes" ;
              ?>&nbsp;&nbsp;<a href="javascript:Abrir('VerImagen.php?Imagen=<?= $aRegistro[$nColumnaActual]?>&amp;Info=<?= $cInfoArc?>&amp;Ancho=<?= $aPropImg[0]?>&amp;Alto=<?= $aPropImg[1]?>','Imagen',<?= $aPropImg[0]?>,<?= $aPropImg[1]?>)"><img src="Imagenes/imgIconoDatos.gif" width="10" height="11" border="0" valign="middle" title="<?= $txt['VerImagen']?>" alt=""></a><?
            } else {
              //echo("Imagen no disponible") ;
            }
          } elseif ($aCampo[$nColumnaActual]["Imag"]=="U") {
            echo ($aRegistro[$nColumnaActual]); 
            unset($aPropImg);  unset($cInfoArc);
            $cFileName = "../Upload/Directos/" . ($aCampo[$nColumnaActual]["SubD"]==""?"":$aCampo[$nColumnaActual]["SubD"]."/") . $aRegistro[$nColumnaActual];
            $cFileExte = strtolower(end(explode(".", $aRegistro[$nColumnaActual])));
            if (is_file($cFileName)) {
              $aImageExtAllowed = array('jpg', 'jpeg', 'png', 'gif','bmp');
              if (in_array($cFileExte, $aImageExtAllowed)) {
                $aPropImg = getimagesize($cFileName) ;
                $cInfoArc = $aPropImg[0]."x".$aPropImg[1] . " - " . filesize($conf['DirUpload'] . "Directos/" . ($aCampo[$nColumnaActual]["SubD"]==""?"":$aCampo[$nColumnaActual]["SubD"]."/") . $aRegistro[$nColumnaActual]) . " bytes" ;
                ?>&nbsp;&nbsp;<a href="javascript:Abrir('VerImagen.php?Imagen=Directos/<?=  ($aCampo[$nColumnaActual]["SubD"]==""?"":$aCampo[$nColumnaActual]["SubD"]."/") . $aRegistro[$nColumnaActual]?>&Info=<?= $cInfoArc?>&Ancho=<?= $aPropImg[0]?>&Alto=<?= $aPropImg[1]?>','Imagen',<?= $aPropImg[0]?>,<?= $aPropImg[1]?>)"><img src="Imagenes/imgIconoDatos.gif" width="10" height="11" border="0" align="absmiddle" title="<?= $txt['VerImagen']?>"></a><?
              } else {
                ?>&nbsp;&nbsp;<a href="Download.php?file=<?= $cFileName?>"><img src="Imagenes/imgIconoDatos.gif" width="10" height="11" border="0" align="absmiddle" title="<?= $txt['VerArchivo']?>"></a><?
              }
            } else {
              //echo("Imagen no disponible") ;
            }
          } elseif ($aCampo[$nColumnaActual]["Imag"]=="A") {
            echo(substr_count($aRegistro[$nColumnaActual],"/")==1?$aRegistro[$nColumnaActual]:substr(strstr($aRegistro[$nColumnaActual],"/"),1)); 
            if (is_file("../Upload/" . $aRegistro[$nColumnaActual])) {
              $cInfoArc = filesize($conf['DirUpload'] . $aRegistro[$nColumnaActual]) . " bytes" ;
              ?>&nbsp;&nbsp;<a href="javascript:Abrir('../Upload/<?= $aRegistro[$nColumnaActual]?>','Documento')"><img src="Imagenes/imgIconoDatos.gif" width="10" height="11" border="0" valign="middle" title="<?= $txt['VerDocumento']?>" alt=""></a><?
            } else {
              //echo("Archivo no disponible") ;
            }
          } else {
            if (strstr("#int#real#",mysql_field_type($nResultado,$nColumnaActual))) 
              $cSalida = number_format($aRegistro[$nColumnaActual]) ;
//          elseif (mysql_field_type($nResultado,$nColumnaActual)=="date")
//            $cSalida = fFormatoFecha($aRegistro[$nColumnaActual], str_replace(array("D", "M", "Y"), array("dd", "mm", "aaaa"), substr_replace(substr_replace($conf["Fecha"], '-', 1, 0), '-', 3, 0))) ;
            else
              $cSalida = htmlspecialchars($aRegistro[$nColumnaActual]) ;

            echo ($cSalida==""?"&nbsp;":$cSalida) ;
          }
        } else {
          echo("&nbsp;");
        }?>
      </td><?
    }
    ?>
    <? if ( $_SESSION["gbl".$conf["VariablesSESSION"]."Tipo"]=="I" ) { ?>
      <td align="right">
        <?= (isset($cInfoArc)?$cInfoArc:$txt['NoArchivo']) ?>
      </td>
    <? } ?>
    <? if ($conf["MostrarBotonABMDesactivado"]=="Si" or (strstr($cnfModInfoAdic . $cnfModInfoRela, "S") or ($_GET["Desde"]!="Relacion" and strstr($cnfPerEditar . $cnfPerBorrar . $cnfPerAcciones, "S")))) { ?>
      <td align="center" valign="middle"><? 
      if ( $cnfModInfoAdic=="S" ) { ?>
        <a href="javascript:Abrir('Divide.php?Opcion=2&amp;Modulo=<?= $cnfModulo?>&amp;Codigo=<?= $aRegistro[0]?>','MasInfo')"><img src="Imagenes/imgIconoDatos.gif" width="10" height="11" border="0" valign="middle" title="<?= $txt['MasInfo']?>" alt=""></a>&nbsp;<? 
      } 
      if ( $cnfModInfoRela=="S" ) {

        $cSql = "SELECT sysRelacion.RelModulo, ModTexto, RelCampo, RelExtraJoin FROM sysRelacion LEFT JOIN sysModulos ON sysRelacion.RelModulo=sysModulos.ModNombre WHERE sysRelacion.ModNombre='" . $cnfModulo . "'" ;
        $nResulInfRel = mysql_query ($cSql) or fErrorSQL($conf["EstadoSitio"], "<br /><br /><b>Error en la consulta:</b><br />" . $cSql . "<br /><br /><b>Tipo de error:</b><br />" . mysql_error() . "<br />");

        while ($aRegisInfRel = mysql_fetch_array($nResulInfRel)) { 
          // Determino la cantidad de registros en las tablas relacionadas  
          $cSql = "SELECT COUNT(*) AS ccCantidad FROM " . $aRegisInfRel["RelModulo"] . " " . $aRegisInfRel["RelExtraJoin"] .  " WHERE " . (strstr($aRegisInfRel["RelCampo"],"XXCodigoXX")?str_replace("XXCodigoXX",$aRegistro[0],$aRegisInfRel["RelCampo"]):$aRegisInfRel["RelCampo"]."=".$aRegistro[0]) ; 
          $nResulCntRel = mysql_query ($cSql) or fErrorSQL($conf["EstadoSitio"], "<br /><br /><b>Error en la consulta:</b><br />" . $cSql . "<br /><br /><b>Tipo de error:</b><br />" . mysql_error() . "<br />");

          $aRegisCntRel = mysql_fetch_array($nResulCntRel) ;

          if ($conf["MostrarInfoRelacVacia"]=="Si" or $aRegisCntRel["ccCantidad"]>0) { ?>
            <a href="javascript:Abrir('Divide.php?Opcion=5&amp;ModuloRel=<?= $aRegisInfRel["RelModulo"]?>&amp;ModuloAct=<?= $cnfModulo?>&amp;CampoRel=<?= urlencode($aRegisInfRel["RelCampo"])?>&amp;Codigo=<?= $aRegistro[0]?>','InfoRel')"><img src="Imagenes/imgIconoFlecha.gif" width="10" height="11" border="0" valign="middle" title="<?= $aRegisInfRel["ModTexto"] . " > " . $aRegisCntRel["ccCantidad"] . " " . ($aRegisCntRel["ccCantidad"]==1?$txt['Registro']:$txt['Registros'])?>" alt=""></a>&nbsp; <?
          } else { ?>
            <img src="Imagenes/imgGralTrasp.gif" width="10" height="11" border="0" valign="middle" alt="">&nbsp; <? 
          } 
          mysql_free_result ($nResulCntRel) ;
        }
        mysql_free_result ($nResulInfRel) ;

      }
      // Más idiomas
      if ($cMostrarFlag=="Si") {
        for ($nIdioma=1; $nIdioma<$nCntIdiomas; $nIdioma++) { ?>
          <a href="javascript:Abrir('InfoMngr.php?Codigo=<?= $aRegistro[0]?>&Lang=Si&Flag=<?= $aIdiomas["Flag"][$nIdioma]?>&Part=<?= $aIdiomas["Part"][$nIdioma]?>','Registro')"><img src="Lenguajes/flags/<?= $aIdiomas["Flag"][$nIdioma]?>.gif" width="20" height="12" border="0" valign="middle" alt=""></a><?
        }
      }
      if (!isset($_GET["Desde"]) or $_GET["Desde"]!="Relacion") {
        if ( $cnfPerEditar=='S' ) { ?>
          <input class="blanco" type="button" name="Editar" value="<?= $txt['Editar']?>" onClick="javascript:Abrir('InfoMngr.php?Codigo=<?= $aRegistro[0]?>','Registro')">&nbsp; <? 
        } elseif ($conf["MostrarBotonABMDesactivado"]=="Si") { ?>
          <input class="blanco" type="button" name="Editar" value="<?= $txt['Editar']?>">&nbsp; <? 
        } 
        if ( $cnfPerBorrar=='S' ) { ?>
          <input class="blanco" type="button" name="Borrar" value="<?= $txt['Borrar']?>" onClick="javascript:Borrar('<?= $cnfModulo?>',<?= $aRegistro[0]?>,<?= $Inicio?>,'<?= $Orden?>','<?= $Forma?>','<?= fPonerBarras($CpoFiltro1)?>','<?= $TipFiltro1?>','<?= urlencode($TxtFiltro1)?>','<?= urlencode($NexFiltro)?>','<?= fPonerBarras($CpoFiltro2)?>','<?= $TipFiltro2?>','<?= urlencode($TxtFiltro2)?>',<?= $Inicio?>,<?= $nCantidad?>)">&nbsp; <? 
        } elseif ($conf["MostrarBotonABMDesactivado"]=="Si") { ?>
          <input class="blanco" type="button" name="Borrar" value="<?= $txt['Borrar']?>">&nbsp; <? 
        } 
        if ($aFilas["R"]>0) { // Acciones personalizadas a nivel de Registro 
          for ($i=0; $i<count($aAcciones); $i++) {
            if ( $aAcciones[$i]["Nivel"]=="R" ) { 
              if ( $cnfPerAcciones=='S' ) {
                // El User tiene permitido Acciones Personalizada en este módulo

                // Ahora verifico si cumple la condición para ejecutarse (en caso de existir dicha condición)
                $cEjecutarAccPer = "Si" ;
                if ( $aAcciones[$i]["EjecutarSi"]!="" ) { 
                  // Existen Condiciones
                  $cSql = $cnfConsAcPe . "WHERE " . mysql_field_name($nResultado, 0) . "=" . $aRegistro[0] . " AND " . $aAcciones[$i]["EjecutarSi"] ;
                  $nResulAccPer = mysql_query ($cSql) or fErrorSQL($conf["EstadoSitio"], "<br /><br /><b>Error en la consulta:</b><br />" . $cSql . "<br /><br /><b>Tipo de error:</b><br />" . mysql_error() . "<br />");

                  if (!($aRegisAccPer = mysql_fetch_array($nResulAccPer))) {
                    $cEjecutarAccPer = "No" ;
                  }
                  mysql_free_result ($nResulAccPer) ;
                }

                if ($cEjecutarAccPer=="Si") {
                  if ($aAcciones[$i]["VentAncho"]!=0 and $aAcciones[$i]["VentAlto"]!=0) { ?>
                    <input class="blanco" type="button" name="AccPer<?= $i?>" value="<?= $aAcciones[$i]["Titulo"]?>" onClick="javascript:Abrir('Divide.php?Opcion=6&Pagina=<?= $aAcciones[$i]["Link"]?>&Codigo=<?= $aRegistro[0]?>','Acciones', <?= $aAcciones[$i]["VentAncho"]?>, <?= $aAcciones[$i]["VentAlto"]?>)">&nbsp;<? 
                  } else { ?>
                    <input class="blanco" type="button" name="AccPer<?= $i?>" value="<?= $aAcciones[$i]["Titulo"]?>" onClick="javascript:Ejecutar('<?= $cnfModulo?>','<?= $aAcciones[$i]["Link"]?>','<?= $txt['ConfAccP']?>',<?= $aRegistro[0]?>,<?= $Inicio?>,'<?= $Orden?>','<?= $Forma?>','<?= fPonerBarras($CpoFiltro1)?>','<?= $TipFiltro1?>','<?= urlencode($TxtFiltro1)?>','<?= urlencode($NexFiltro)?>','<?= fPonerBarras($CpoFiltro2)?>','<?= $TipFiltro2?>','<?= urlencode($TxtFiltro2)?>',<?= $Inicio?>,<?= $nCantidad?>)">&nbsp;<? 
                  }
                } elseif ($conf["MostrarBotonABMDesactivado"]=="Si") { 
                  // El registro no cumple la condición para ejecutar la Accion Personalizada ?>
                  <input class="blanco" type="button" name="AccPer<?= $i?>" value="<?= $aAcciones[$i]["Titulo"]?>">&nbsp;<? 
                } 
              } elseif ($conf["MostrarBotonABMDesactivado"]=="Si") { 
                // El User no tiene permitido Acciones Personalizada en este módulo ?>
                <input class="blanco" type="button" name="AccPer<?= $i?>" value="<?= $aAcciones[$i]["Titulo"]?>">&nbsp;<? 
              } 
            } 
          } 
        }
      } ?>
      </td>
    <? } ?>
    </tr><?
  }

  mysql_free_result ($nResultado);?>

</table>

<script language="JavaScript" type="text/javascript">
<!--
  tigra_tables('infoModulo', 1, 0, '#DEDEDE', '#D2D2D2', '#CCFFCC', '#FFCC99');
// -->
</script>

<?
if (!isset($_GET["Desde"]) or $_GET["Desde"]!="Relacion") {

  // Variables para el Pie de Página
  // ===============================

  $_SESSION["pdpIrA"] = $cEsta;

  if ( $cnfPerAgregar=='S' ) {
    $_SESSION["pdpAgregar"] = "<input class=\"blanco\" type=\"button\" name=\"Agregar\" value=\"" . $txt['Agregar'] . "\" onClick=\"javascript:Abrir('InfoMngr.php?Codigo=0','Registro')\">";
  } elseif ($conf["MostrarBotonABMDesactivado"]=="Si") {
    $_SESSION["pdpAgregar"] = "<input class=\"blanco\" type=\"button\" name=\"Agregar\" value=\"" . $txt['Agregar'] . "\">";
  } else {
    $_SESSION["pdpAgregar"] = "";
  }

  $_SESSION["pdpOrden"] = $Orden;
  $_SESSION["pdpForma"] = $Forma;

  if ($cnfPerExportar=="S") {
    $_SESSION["pdpExportar"]  = "<a href=\"Exporta.php?Tipo=E&amp;Orden=" . $Orden . "&amp;Forma=" . $Forma . "&amp;CpoFiltro1=" . $CpoFiltro1 . "&amp;TipFiltro1=" . $TipFiltro1 . "&amp;TxtFiltro1=" . urlencode($TxtFiltro1) . "&amp;NexFiltro=" . $NexFiltro . "&amp;CpoFiltro2=" . $CpoFiltro2 . "&amp;TipFiltro2=" . $TipFiltro2 . "&amp;TxtFiltro2=" . urlencode($TxtFiltro2) . "\"><img src=\"Imagenes/icoExcel.gif\" width=\"20\" height=\"23\" border=\"0\" alt=\"\"></a>" ;
    $_SESSION["pdpExportar"] .= "&nbsp;" ;
    $_SESSION["pdpExportar"] .= "<a href=\"Exporta.php?Tipo=W&amp;Orden=" . $Orden . "&amp;Forma=" . $Forma . "&amp;CpoFiltro1=" . $CpoFiltro1 . "&amp;TipFiltro1=" . $TipFiltro1 . "&amp;TxtFiltro1=" . urlencode($TxtFiltro1) . "&amp;NexFiltro=" . $NexFiltro . "&amp;CpoFiltro2=" . $CpoFiltro2 . "&amp;TipFiltro2=" . $TipFiltro2 . "&amp;TxtFiltro2=" . urlencode($TxtFiltro2) . "\"><img src=\"Imagenes/icoWord.gif\" width=\"20\" height=\"23\" border=\"0\" alt=\"\"></a>" ;
  } else {
    $_SESSION["pdpExportar"] = "<img src=\"Imagenes/icoExcel.gif\" width=\"20\" height=\"23\" border=\"0\" alt=\"\">&nbsp;<img src=\"Imagenes/icoWord.gif\" width=\"20\" height=\"23\" border=\"0\" alt=\"\">" ;
  }

  $_SESSION["pdpCpoFiltro1"] = "<option value=\"\"></option>" ;
  for ( $nFilaActual=0; $nFilaActual<=(count($aFiltro)-1); $nFilaActual++ ) {
    $_SESSION["pdpCpoFiltro1"] .= "<option value=\"" . $aFiltro[$nFilaActual]["Expr"] . "\"" . ($aFiltro[$nFilaActual]["Expr"]==$CpoFiltro1?" selected":"") . ">" . $aFiltro[$nFilaActual]["Nomb"] . "</option>";
  }

  $_SESSION["pdpTipFiltro1"] = "";
  $_SESSION["pdpTipFiltro1"] .= "<option value=\"=\"" . ($TipFiltro1=="="?" selected":"") . ">=</option> \n";
  $_SESSION["pdpTipFiltro1"] .= "<option value=\"<>\"" . ($TipFiltro1=="<>"?" selected":"") . "><></option> \n";
  $_SESSION["pdpTipFiltro1"] .= "<option value=\"<\"" . ($TipFiltro1=="<"?" selected":"") . "><</option> \n";
  $_SESSION["pdpTipFiltro1"] .= "<option value=\"<=\"" . ($TipFiltro1=="<="?" selected":"") . "><=</option> \n";
  $_SESSION["pdpTipFiltro1"] .= "<option value=\">\"" . ($TipFiltro1==">"?" selected":"") . ">></option> \n";
  $_SESSION["pdpTipFiltro1"] .= "<option value=\">=\"" . ($TipFiltro1==">="?" selected":"") . ">>=</option> \n";
  $_SESSION["pdpTipFiltro1"] .= "<option value=\"IN\"" . ($TipFiltro1=="IN"?" selected":"") . ">" . $txt['En'] . "</option> \n";
  $_SESSION["pdpTipFiltro1"] .= "<option value=\"NOT IN\"" . ($TipFiltro1=="NOT IN"?" selected":"") . ">" . $txt['NoEn'] . "</option> \n";
  $_SESSION["pdpTipFiltro1"] .= "<option value=\"BETWEEN\"" . ($TipFiltro1=="BETWEEN"?" selected":"") . ">" . $txt['Entre'] . "</option> \n";
  $_SESSION["pdpTipFiltro1"] .= "<option value=\"NOT BETWEEN\"" . ($TipFiltro1=="NOT BETWEEN"?" selected":"") . ">" . $txt['NoEntre'] . "</option> \n";
  $_SESSION["pdpTipFiltro1"] .= "<option value=\"LIKE-E\"" . ($TipFiltro1=="LIKE-E"?" selected":"") . ">" . $txt['EmpiezaCon'] . "</option> \n";
  $_SESSION["pdpTipFiltro1"] .= "<option value=\"NOT LIKE-E\"" . ($TipFiltro1=="NOT LIKE-E"?" selected":"") . ">" . $txt['NoEmpiezaCon'] . "</option> \n";
  $_SESSION["pdpTipFiltro1"] .= "<option value=\"LIKE-C\"" . (($TipFiltro1=="LIKE-C" or $TipFiltro1=="")?" selected":"") . ">" . $txt['ContieneA'] . "</option> \n";
  $_SESSION["pdpTipFiltro1"] .= "<option value=\"NOT LIKE-C\"" . ($TipFiltro1=="NOT LIKE-C"?" selected":"") . ">" . $txt['NoContieneA'] . "</option> \n";
  $_SESSION["pdpTipFiltro1"] .= "<option value=\"LIKE-T\"" . ($TipFiltro1=="LIKE-T"?" selected":"") . ">" . $txt['TerminaCon'] . "</option> \n";
  $_SESSION["pdpTipFiltro1"] .= "<option value=\"NOT LIKE-T\"" . ($TipFiltro1=="NOT LIKE-T"?" selected":"") . ">" . $txt['NoTerminaCon'] . "</option> \n";

  $_SESSION["pdpTxtFiltro1"] = $TxtFiltro1;

  $_SESSION["pdpNexFiltro"]  = "<option value=\"AND\"" . ($NexFiltro=="AND"?" selected":"") . ">" . $txt['Y'] . "</option> \n" ;
  $_SESSION["pdpNexFiltro"] .= "<option value=\"OR\"" . ($NexFiltro=="OR"?" selected":"") . ">" . $txt['O'] . "</option> \n" ;

  $_SESSION["pdpCpoFiltro2"] = "<option value=\"\"></option>" ;
  for ( $nFilaActual=0; $nFilaActual<=(count($aFiltro)-1); $nFilaActual++ ) {
    $_SESSION["pdpCpoFiltro2"] .= "<option value=\"" . $aFiltro[$nFilaActual]["Expr"] . "\"" . ($aFiltro[$nFilaActual]["Expr"]==$CpoFiltro2?" selected":"") . ">" . $aFiltro[$nFilaActual]["Nomb"] . "</option>";
  }

  $_SESSION["pdpTipFiltro2"] = "";
  $_SESSION["pdpTipFiltro2"] .= "<option value=\"=\"" . ($TipFiltro2=="="?" selected":"") . ">=</option> \n";
  $_SESSION["pdpTipFiltro2"] .= "<option value=\"<>\"" . ($TipFiltro2=="<>"?" selected":"") . "><></option> \n";
  $_SESSION["pdpTipFiltro2"] .= "<option value=\"<\"" . ($TipFiltro2=="<"?" selected":"") . "><</option> \n";
  $_SESSION["pdpTipFiltro2"] .= "<option value=\"<=\"" . ($TipFiltro2=="<="?" selected":"") . "><=</option> \n";
  $_SESSION["pdpTipFiltro2"] .= "<option value=\">\"" . ($TipFiltro2==">"?" selected":"") . ">></option> \n";
  $_SESSION["pdpTipFiltro2"] .= "<option value=\">=\"" . ($TipFiltro2==">="?" selected":"") . ">>=</option> \n";
  $_SESSION["pdpTipFiltro2"] .= "<option value=\"IN\"" . ($TipFiltro2=="IN"?" selected":"") . ">" . $txt['En'] . "</option> \n";
  $_SESSION["pdpTipFiltro2"] .= "<option value=\"NOT IN\"" . ($TipFiltro2=="NOT IN"?" selected":"") . ">" . $txt['NoEn'] . "</option> \n";
  $_SESSION["pdpTipFiltro2"] .= "<option value=\"BETWEEN\"" . ($TipFiltro2=="BETWEEN"?" selected":"") . ">" . $txt['Entre'] . "</option> \n";
  $_SESSION["pdpTipFiltro2"] .= "<option value=\"NOT BETWEEN\"" . ($TipFiltro2=="NOT BETWEEN"?" selected":"") . ">" . $txt['NoEntre'] . "</option> \n";
  $_SESSION["pdpTipFiltro2"] .= "<option value=\"LIKE-E\"" . ($TipFiltro2=="LIKE-E"?" selected":"") . ">" . $txt['EmpiezaCon'] . "</option> \n";
  $_SESSION["pdpTipFiltro2"] .= "<option value=\"NOT LIKE-E\"" . ($TipFiltro2=="NOT LIKE-E"?" selected":"") . ">" . $txt['NoEmpiezaCon'] . "</option> \n";
  $_SESSION["pdpTipFiltro2"] .= "<option value=\"LIKE-C\"" . (($TipFiltro2=="LIKE-C" or $TipFiltro2=="")?" selected":"") . ">" . $txt['ContieneA'] . "</option> \n";
  $_SESSION["pdpTipFiltro2"] .= "<option value=\"NOT LIKE-C\"" . ($TipFiltro2=="NOT LIKE-C"?" selected":"") . ">" . $txt['NoContieneA'] . "</option> \n";
  $_SESSION["pdpTipFiltro2"] .= "<option value=\"LIKE-T\"" . ($TipFiltro2=="LIKE-T"?" selected":"") . ">" . $txt['TerminaCon'] . "</option> \n";
  $_SESSION["pdpTipFiltro2"] .= "<option value=\"NOT LIKE-T\"" . ($TipFiltro2=="NOT LIKE-T"?" selected":"") . ">" . $txt['NoTerminaCon'] . "</option> \n";

  $_SESSION["pdpTxtFiltro2"] = $TxtFiltro2;

  if ( $Inicio > 0 ) {
    $_SESSION["pdpAnterior"] = "<a href=\"javascript:CambiarPagina('" . $cnfModulo . "', '" . $Orden . "', '" . $Forma . "', '" . fPonerBarras($CpoFiltro1) . "', '" . $TipFiltro1 . "', '" . urlencode($TxtFiltro1) . "', '" . $NexFiltro . "', '" . fPonerBarras($CpoFiltro2) . "', '" . $TipFiltro2 . "', '" . urlencode($TxtFiltro2) . "', " . ($Inicio-$nCantidad) . ", " . $nCantidad . ")\">&lt;&nbsp;" . $txt['Anterior'] . "</a>";
    $_SESSION["pdpPrimera"]  = "<a href=\"javascript:CambiarPagina('" . $cnfModulo . "', '" . $Orden . "', '" . $Forma . "', '" . fPonerBarras($CpoFiltro1) . "', '" . $TipFiltro1 . "', '" . urlencode($TxtFiltro1) . "', '" . $NexFiltro . "', '" . fPonerBarras($CpoFiltro2) . "', '" . $TipFiltro2 . "', '" . urlencode($TxtFiltro2) . "', 0, " . $nCantidad . ")\">&lt;&lt;&nbsp;" . $txt['Primera'] . "</a>";
  } else {
    $_SESSION["pdpAnterior"] = "&lt;&nbsp;" . $txt['Anterior'] ;
    $_SESSION["pdpPrimera"]  = "&lt;&lt;&nbsp;" . $txt['Primera'] ;
  }

  if ( $nParar < $nFilas ) {
    $_SESSION["pdpSiguiente"] = "<a href=\"javascript:CambiarPagina('" . $cnfModulo . "', '" . $Orden . "', '" . $Forma . "', '" . fPonerBarras($CpoFiltro1) . "', '" . $TipFiltro1 . "', '" . urlencode($TxtFiltro1) . "', '" . $NexFiltro . "', '" . fPonerBarras($CpoFiltro2) . "', '" . $TipFiltro2 . "', '" . urlencode($TxtFiltro2) . "', " . ($Inicio+$nCantidad) . ", " . $nCantidad . ")\">" . $txt['Siguiente'] . "&nbsp;&gt;</a>";
    $_SESSION["pdpUltima"]    = "<a href=\"javascript:CambiarPagina('" . $cnfModulo . "', '" . $Orden . "', '" . $Forma . "', '" . fPonerBarras($CpoFiltro1) . "', '" . $TipFiltro1 . "', '" . urlencode($TxtFiltro1) . "', '" . $NexFiltro . "', '" . fPonerBarras($CpoFiltro2) . "', '" . $TipFiltro2 . "', '" . urlencode($TxtFiltro2) . "', " . (floor($nFilas/$nCantidad)*$nCantidad) . ", " . $nCantidad . ")\">" . $txt['Ultima'] . "&nbsp;&gt;&gt;</a>";
  } else {
    $_SESSION["pdpSiguiente"] = $txt['Siguiente'] . "&nbsp;&gt;";
    $_SESSION["pdpUltima"]    = $txt['Ultima'] . "&nbsp;&gt;&gt;";
  }

  $cNroPagina = "<select name=\"Pagina\" id=\"Pagina\" size=\"1\" class=\"Verdana11\" onchange=\"javascript:CambiarPagina('" . $cnfModulo . "', '" . $Orden . "', '" . $Forma . "', '" . fPonerBarras($CpoFiltro1) . "', '" . $TipFiltro1 . "', '" . urlencode($TxtFiltro1) . "', '" . $NexFiltro . "', '" . fPonerBarras($CpoFiltro2) . "', '" . $TipFiltro2 . "', '" . urlencode($TxtFiltro2) . "', -1, " . $nCantidad . ")\"> \n";
  for ( $nNroPagina=1; $nNroPagina<=(floor($nFilas/$nCantidad)+1); $nNroPagina++ ) {
    $cNroPagina .= "<option value=\"" . ($nNroPagina-1)*$nCantidad . "\"" . (floor($Inicio/$nCantidad)+1==$nNroPagina?" selected":"") . ">" . $nNroPagina . "</option> \n";
  }
  $cNroPagina .= "</select>";
  $_SESSION["pdpPagina"] = str_replace("#",$cNroPagina,$cPaginas);

  $cCntFilas  = "<select name=\"CntFilas\" id=\"CntFilas\" size=\"1\" class=\"Verdana11\" onchange=\"javascript:CambiarPagina('" . $cnfModulo . "', '" . $Orden . "', '" . $Forma . "', '" . fPonerBarras($CpoFiltro1) . "', '" . $TipFiltro1 . "', '" . urlencode($TxtFiltro1) . "', '" . $NexFiltro . "', '" . fPonerBarras($CpoFiltro2) . "', '" . $TipFiltro2 . "', '" . urlencode($TxtFiltro2) . "', -2, " . $nCantidad . ")\"> \n" ;
  $cCntFilas .= "  <option value=\"5\"".($nCantidad==5?" selected":"").">5</option> \n" ;
  $cCntFilas .= "  <option value=\"10\"".($nCantidad==10?" selected":"").">10</option> \n" ;
  $cCntFilas .= "  <option value=\"15\"".($nCantidad==15?" selected":"").">15</option> \n" ;
  $cCntFilas .= "  <option value=\"20\"".($nCantidad==20?" selected":"").">20</option> \n" ;
  $cCntFilas .= "  <option value=\"25\"".($nCantidad==25?" selected":"").">25</option> \n" ;
  $cCntFilas .= "  <option value=\"30\"".($nCantidad==30?" selected":"").">30</option> \n" ;
  $cCntFilas .= "  <option value=\"35\"".($nCantidad==35?" selected":"").">35</option> \n" ;
  $cCntFilas .= "  <option value=\"40\"".($nCantidad==40?" selected":"").">40</option> \n" ;
  $cCntFilas .= "  <option value=\"45\"".($nCantidad==45?" selected":"").">45</option> \n" ;
  $cCntFilas .= "  <option value=\"50\"".($nCantidad==50?" selected":"").">50</option> \n" ;
  $cCntFilas .= "</select>" ;
  $_SESSION["pdpCntFilas"] = str_replace("#",$cCntFilas,$txt['XFilas']);

  $_SESSION["pdpPredet"] = ($cnfCntLineas==$nCantidad?"":"<input class=\"blanco\" type=\"button\" name=\"Predeterminado\" value=\"".$txt['Predet']."\" onClick=\"javascript:location.href='Utilidades.php?Accion=Predeterminar&amp;Cantidad=".$nCantidad."';\">") ;

  $_SESSION["pdpTiempo"] = str_replace("#",number_format(max((fGetMicroTime()-$nTiempo),0.01),2),$txt['TiempoGener']) ;

  ?>
  <script language="JavaScript" type="text/javascript">
    parent.Pie.location.href="Pie.php";
  </script>
  <?

}
?>
</body>
</html>
