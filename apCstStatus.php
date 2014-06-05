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

// Determina los permisos necesarios para las diferentes acciones
$cSql = "SELECT ModTexto, PerAcciones FROM sysModulos LEFT JOIN sysModUsu ON sysModulos.ModNombre=sysModUsu.ModNombre WHERE sysModulos.ModNombre='" . $_SESSION["gbl".$conf["VariablesSESSION"]."Modulo"] . "' AND sysModUsu.UsuAlias='" . $_SESSION["gbl".$conf["VariablesSESSION"]."Alias"] . "'";
$nResultado = mysql_query ($cSql) or die("Error en la consulta: <b>" . $cSql . "</b> <br>Tipo de error: <b>" . mysql_error() . "</b>");
$aRegistro  = mysql_fetch_array($nResultado);

$cnfModNombre = $aRegistro["ModTexto"];

$cnfPerAcciones = $aRegistro["PerAcciones"];

mysql_free_result ($nResultado);


// Control de Permisos
if ($cnfPerAcciones!='S') {
  header ("Location: Index.php");
  exit(0);
}



    // Averiguo el estado actual y lo cambio
    $cSql = "SELECT CstStatus FROM Customers WHERE CstId=" . $_GET["Codigo"];
    $nResultado = mysql_query ($cSql) or fErrorSQL($conf["EstadoSitio"], "<br /><br /><b>Error en la consulta:</b><br />" . $cSql . "<br /><br /><b>Tipo de error:</b><br />" . mysql_error() . "<br />");
    $aRegistro  = mysql_fetch_array($nResultado);
    
    $cNuevoEstado = ($aRegistro["CstStatus"]=="Ok"?"Blocked":($aRegistro["CstStatus"]=="Blocked"?"Ok":$aRegistro["CstStatus"]));
    
    mysql_free_result ($nResultado);
    
    $cSql = "UPDATE Customers SET CstStatus='".$cNuevoEstado."' WHERE CstId=" . $_GET["Codigo"];
    mysql_query ($cSql) or fErrorSQL($conf["EstadoSitio"], "<br /><br /><b>Error en la consulta:</b><br />" . $cSql . "<br /><br /><b>Tipo de error:</b><br />" . mysql_error() . "<br />");



// Recargo la página de Info
echo("<script language=\"JavaScript\">") ;
echo("  location.href='Info.php?Inicio=" . $_GET["Inicio"] . "&Orden=" . $_GET["Orden"] . "&Forma=" . $_GET["Forma"] . "&CpoFiltro1=" . $_GET["CpoFiltro1"] . "&TipFiltro1=" . $_GET["TipFiltro1"] . "&TxtFiltro1=" . fSacarBarras($_GET["TxtFiltro1"]) . "&NexFiltro=" . $_GET["NexFiltro"] . "&CpoFiltro2=" . $_GET["CpoFiltro2"] . "&TipFiltro2=" . $_GET["TipFiltro2"] . "&TxtFiltro2=" . fSacarBarras($_GET["TxtFiltro2"]) . "&Inicio=" . $_GET["Inicio"] . "&Cantidad=" . $_GET["Cantidad"] . "';") ;
echo("</script>") ;

?>