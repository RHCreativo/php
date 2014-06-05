<?
/*
******************************************************************************
* Administrador de Contenidos                                                *
* -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-= *
*                                                                            *
* (C) 2002, Fabián Chesta                                                    *
*                                                                            *
* Comentarios: Programa para visualizar una imagen                           *
*                                                                            *
******************************************************************************
*/

session_start();

// Archivos de Conexión y Configuración
include("Conexion.inc.php");
include("Lenguajes/" . $conf["Lenguaje"]);
include("Funciones/Funciones.inc.php");

$cSql = "UPDATE sysModUsu SET VerCntLineas='".$_GET["Cantidad"]."' WHERE ModNombre='" . $_SESSION["gbl".$conf["VariablesSESSION"]."Modulo"] . "' AND UsuAlias='" . $_SESSION["gbl".$conf["VariablesSESSION"]."Alias"] . "'";
mysql_query ($cSql) or fErrorSQL($conf["EstadoSitio"], "<br /><br /><b>Error en la consulta:</b><br />" . $cSql . "<br /><br /><b>Tipo de error:</b><br />" . mysql_error() . "<br />");

$_SESSION["pdpPredet"] = "" ;

header ("Location: Pie.php");
?>