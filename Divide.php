<?php
/*
******************************************************************************
* Administrador de Contenidos                                                *
* -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-= *
*                                                                            *
* (C) 2002, Federico Teiserskis                                              *
*                                                                            *
* Comentarios:                                                               *
*                                                                            *
******************************************************************************
*/
session_start();

// Archivos de Conexión y Configuración
include("Conexion.inc.php");
include("Lenguajes/" . $conf["Lenguaje"]);

// Control de Accesos y Permisos
if ($_SESSION["gbl".$conf["VariablesSESSION"]."Alias"]=="") {
  header ("Location: Index.php");
}
?>

<?php if ($_GET["Opcion"]==0) {       /* División desde el ingreso */
  // Variables para el Pie de Página
  // ===============================
  $_SESSION["pdpIrA"]        = "" ;
  $_SESSION["pdpExportar"]   = "" ;
  $_SESSION["pdpCpoFiltro1"] = "" ;
  $_SESSION["pdpTipFiltro1"] = "" ;
  $_SESSION["pdpTxtFiltro1"] = "" ;
  $_SESSION["pdpNexFiltro"]  = "" ;
  $_SESSION["pdpCpoFiltro2"] = "" ;
  $_SESSION["pdpTipFiltro2"] = "" ;
  $_SESSION["pdpTxtFiltro2"] = "" ;
  $_SESSION["pdpOrden"]      = "" ;
  $_SESSION["pdpForma"]      = "" ;
  $_SESSION["pdpTiempo"]     = "" ;
  $_SESSION["pdpPagina"]     = "" ;
  $_SESSION["pdpCntFilas"]   = "" ;
  $_SESSION["pdpPredet"]     = "" ;
  $_SESSION["pdpPrimera"]    = "" ;
  $_SESSION["pdpAnterior"]   = "" ;
  $_SESSION["pdpSiguiente"]  = "" ;
  $_SESSION["pdpUltima"]     = "" ;
  $_SESSION["pdpAgregar"]    = "" ;

  // Arma la instrucción SQL y luego la ejecuta
  $cSql = "SELECT FLOOR(MIN(ModOrden)/100)+1 AS nNroPriLin, FLOOR(MAX(ModOrden)/100)+1 AS nNroUltLin FROM sysModulos INNER JOIN sysModUsu ON sysModulos.ModNombre=sysModUsu.ModNombre WHERE UsuAlias='" . $_SESSION["gbl".$conf["VariablesSESSION"]."Alias"] . "' AND PerVer='S'";

  $nResultado = $nConexion->query ($cSql) or fErrorSQL($conf["EstadoSitio"], "<br /><br /><b>Error en la consulta:</b><br />" . $cSql . "<br /><br /><b>Tipo de error:</b><br />" . mysqli_error($nConexion) . "<br />");
  
  $aRegistro = $nResultado->fetch_object();

  $nCntLinMenu = $aRegistro->nNroUltLin-$aRegistro->nNroPriLin+1 ;

  mysqli_free_result ($nResultado) ;
  ?>
  <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN"
      "http://www.w3.org/TR/html4/frameset.dtd">
  <html>
  <head>
    <title>.:. <?= $conf["NombreCliente"]?> .:.<?= ($conf["EstadoSitio"]!="Production"?$txt['ModoPrueba']:"")?></title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  </head>
  <frameset name="Marcos" rows="<?= (56+23*$nCntLinMenu+7)?>,*" frameborder="NO" border="0" framespacing="0">
    <frame name="Cabecera" scrolling="NO" noresize src="Menu.php">
    <frame name="Principal" src="Divide.php?Opcion=4">
  </frameset>
  </html>

<?php } elseif ($_GET["Opcion"]==1) { /* División desde el menu principal */ 
  $_SESSION["gbl".$conf["VariablesSESSION"]."Modulo"] = $_GET["Modulo"];
  $_SESSION["gbl".$conf["VariablesSESSION"]."Tipo"]   = $_GET["Tipo"];
  $_SESSION["gbl".$conf["VariablesSESSION"]."Duplic"] = $_GET["Duplic"]; ?>
  <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN"
      "http://www.w3.org/TR/html4/frameset.dtd">
  <html>
  <head>
    <title></title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  </head>
  <frameset name="SubMarcos" rows="*,117" frameborder="NO" border="0" framespacing="0">
    <frame name="Cuerpo" src="Info.php">
    <frame name="Pie" scrolling="NO" noresize src="">
  </frameset>
  </html>

<?php } elseif ($_GET["Opcion"]==2) { /* División para más información */ ?>
  <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN"
      "http://www.w3.org/TR/html4/frameset.dtd">
  <html>
  <head>
    <title></title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  </head>
  <frameset name="SubMarcos" rows="*,30" frameborder="NO" border="0" framespacing="0">
    <frame name="Cuerpo" src="InfoMas.php?Modulo=<?= $_GET["Modulo"]?>&amp;Codigo=<?= $_GET["Codigo"]?>">
    <frame name="Pie" scrolling="NO" noresize src="Divide.php?Opcion=3">
  </frameset>
  </html>

<?php } elseif ($_GET["Opcion"]==3) { /* Pie de la pantalla de más información e información relacionada*/ ?>
  <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
      "http://www.w3.org/TR/html4/loose.dtd">
  <html>
  <head>
    <title></title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">

    <style type="text/css">
    <!--
      a {  font-family: Verdana, Arial; font-size: 11px; font-style: normal; font-weight: bold; color: #FFFFFF; TEXT-DECORATION: none}
    -->
    </style>
  </head>
  <body bgcolor="#FFFFFF" text="#000000" style="margin:0;">
  <table width="100%" border="0" cellspacing="0" cellpadding="5" height="100%">
    <tr>
      <td height="30" bgcolor="#929292" align="right"><a href="javascript:parent.window.close()"><?= $txt['CerrarVen']?>&nbsp;&nbsp;<img src="Imagenes/botCerrar.gif" width="9" height="9" border="0" alt=""></a>&nbsp;&nbsp;</td>
    </tr>
  </table>
  </body>
  </html>

<?php } elseif ($_GET["Opcion"]==4) { /* Pantalla inicial */ ?>
  <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
      "http://www.w3.org/TR/html4/loose.dtd">
  <html>
  <head>
    <title></title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">

    <link rel="stylesheet" href="Estilos/hde.css" type="text/css">

    <style type="text/css">
      html, body { margin:0; height:100%; }
    </style>
  </head>
  <body>
  <table width="90%" border="0" cellspacing="0" cellpadding="0" align="center" style="height:100%;">
    <tr>
      <td align="center" valign="middle" class="Verdana11">
        <?= str_replace('#', $conf["NombreCliente"], $txt['MensajeInicial'])?>
        <?= ($conf["EstadoSitio"]!="Production"?"<br/><br/>".$txt['ModoPrueba']:"")?>
      </td>
    </tr>
  </table>
  </body>
  </html>

<?php } elseif ($_GET["Opcion"]==5) { /* División para información relacionada */ ?>
  <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN"
      "http://www.w3.org/TR/html4/frameset.dtd">
  <html>
  <head>
    <title></title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  </head>
  <frameset name="SubMarcos" rows="*,30" frameborder="NO" border="0" framespacing="0">
    <frame name="Cuerpo" src="Info.php?Desde=Relacion&amp;Codigo=<?= $_GET["Codigo"]?>&amp;ModuloRel=<?= $_GET["ModuloRel"]?>&amp;ModuloAct=<?= $_GET["ModuloAct"]?>&amp;CampoRel=<?= $_GET["CampoRel"]?>&amp;Codigo=<?= $_GET["Codigo"]?>">
    <frame name="Pie" scrolling="NO" noresize src="Divide.php?Opcion=3">
  </frameset>
  </html>

<?php } elseif ($_GET["Opcion"]==6) { /* División para acciones personalizadas a nivel de registro */ ?>
  <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN"
      "http://www.w3.org/TR/html4/frameset.dtd">
  <html>
  <head>
    <title></title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  </head>
  <frameset name="SubMarcos" rows="*,30" frameborder="NO" border="0" framespacing="0">
    <frame name="Cuerpo" src="<?= $_GET["Pagina"]?>?Codigo=<?= $_GET["Codigo"]?>">
    <frame name="Pie" scrolling="NO" noresize src="Divide.php?Opcion=3">
  </frameset>
  </html>

<?php } ?>