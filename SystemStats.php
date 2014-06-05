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
} ?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
    "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
  <title></title>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">

  <link rel="stylesheet" href="Estilos/hde.css" type="text/css">

  <style type="text/css">
    html, body { margin:0; }
  </style>
</head>
<body>
<table class="gralTabla" align="center" border="0" cellpadding="1" cellspacing="1" width="90%">
  <tr>
    <td colspan="3" align="center" class="Verdana11"><?= $txt['SystemStats'])?></td>
  </tr>
  <tr>
    <td colspan="3" class="Verdana11">&nbsp;</td>
  </tr>
  <tr>
    <td align="right" class="Verdana11" style="background-color: #d2d2d2;">Client version:</td>
    <td align="left" class="Verdana11" style="background-color: #dedede;"><?= mysql_get_client_info()?></td>
    <td align="right" class="Verdana11"></td>
  </tr>
  <tr>
    <td align="right" class="Verdana11" style="background-color: #d2d2d2;">Server version:</td>
    <td align="left" class="Verdana11" style="background-color: #dedede;"><?= mysql_get_server_info()?></td>
    <td align="right" class="Verdana11"></td>
  </tr>
  <tr>
    <td align="right" class="Verdana11" style="background-color: #d2d2d2;">Protocol version:</td>
    <td align="left" class="Verdana11" style="background-color: #dedede;"><?= mysql_get_proto_info()?></td>
    <td align="right" class="Verdana11"></td>
  </tr>
  <tr>
    <td align="right" class="Verdana11" style="background-color: #d2d2d2;">Host:</td>
    <td align="left" class="Verdana11" style="background-color: #dedede;"><?= mysql_get_host_info()?></td>
    <td align="right" class="Verdana11"></td>
  </tr>
</table>
</body>
</html>

// get server status
$cSStatus = explode('  ', mysql_stat());
print_r($cSStatus);


