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

if ($_GET["Accion"]=="LogOut") {
  $_SESSION["gbl".$conf["VariablesSESSION"]."Alias"]    = "";
  $_SESSION["gbl".$conf["VariablesSESSION"]."Nombre"]   = "";
  $_SESSION["gbl".$conf["VariablesSESSION"]."Empresa"]  = "";
  $_SESSION["gbl".$conf["VariablesSESSION"]."UltLogin"] = "";
}

// Control de Accesos y Permisos
if ($_SESSION["gbl".$conf["VariablesSESSION"]."Alias"]=="") {
  header ("Location: Index.php");
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
    "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
    <title>Untitled Document</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">

    <link rel="stylesheet" href="Estilos/hde.css" type="text/css">

    <style type="text/css">
      a {  font-family: Verdana, Arial; font-size: 11px; font-style: normal; font-weight: bold; color: #ffffff; text-decoration: none; }
    </style>

</head>

<body bgcolor="#38628A" text="#000000" style="margin:0;">
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td colspan="2" height="56" valign="top">
      <table width="100%" border="0" cellspacing="0" cellpadding="5">
        <tr>
          <td align="left" valign="top" width="166"><a href="Divide.php?Opcion=4" target="Principal"><img src="Imagenes/<?= $conf["LogoCliente"]?>" border="0" alt=""></a></td>
          <td align="left" valign="top">
            <span class="Verdana11" style="color:#ffffff;"><?= $txt['Bienvenido']?>, <?= $_SESSION["gbl".$conf["VariablesSESSION"]."Nombre"]?></span><br />
            <span class="Verdana09" style="color:#ffffff;"><?= $txt['UltimaVisita']?> <?= $_SESSION["gbl".$conf["VariablesSESSION"]."UltLogin"]?></span><br />
          </td>
          <td align="right" valign="top" width="166"><img src="Imagenes/<?= $conf["LogoDesarrollador"]?>" border="0" alt="" height="54"></td>
        </tr>
      </table>
    </td>
  </tr>
  <?
  // Arma la instrucción SQL y luego la ejecuta
  $cSql = "SELECT FLOOR(MIN(ModOrden)/100)+1 AS nNroPriLin, FLOOR(MAX(ModOrden)/100)+1 AS nNroUltLin FROM sysModulos INNER JOIN sysModUsu ON sysModulos.ModNombre=sysModUsu.ModNombre WHERE UsuAlias='" . $_SESSION["gbl".$conf["VariablesSESSION"]."Alias"] . "' AND PerVer='S'";
  $nResultado = mysql_query ($cSql) or fErrorSQL($conf["EstadoSitio"], "<br /><br /><b>Error en la consulta:</b><br />" . $cSql . "<br /><br /><b>Tipo de error:</b><br />" . mysql_error() . "<br />");

  $aRegistro = mysql_fetch_array($nResultado);

  $nNroPriLin  = $aRegistro["nNroPriLin"] ;
  $nNroUltLin  = $aRegistro["nNroUltLin"] ;
  $nCntLinMenu = $nNroUltLin-$nNroPriLin+1 ;

  mysql_free_result ($nResultado) ;

  for($nFila=$nNroPriLin; $nFila<=$nNroUltLin; $nFila++) { ?>
    <tr bgcolor="#929292">
      <td width="99%" class="Verdana11" valign="middle" height="23">
        <?
        $cOpcLinMenu = "" ;

        // Arma la instrucción SQL y luego la ejecuta
        $cSql = "SELECT ModOrden, sysModulos.ModNombre, ModTexto, ModTipo, ModLink, ModPerDuplicar FROM sysModulos INNER JOIN sysModUsu ON sysModulos.ModNombre=sysModUsu.ModNombre WHERE FLOOR(ModOrden/100)+1=" . $nFila . " AND UsuAlias='" . $_SESSION["gbl".$conf["VariablesSESSION"]."Alias"] . "' AND PerVer='S' ORDER BY ModOrden" ;

        $nResultado = mysql_query ($cSql) or fErrorSQL($conf["EstadoSitio"], "<br /><br /><b>Error en la consulta:</b><br />" . $cSql . "<br /><br /><b>Tipo de error:</b><br />" . mysql_error() . "<br />");

        while($aRegistro = mysql_fetch_array($nResultado)) {
          if ($aRegistro["ModTipo"]=="N" or $aRegistro["ModTipo"]=="I") {
            $cOpcLinMenu .= "&nbsp;<a href=\"Divide.php?Opcion=1&amp;Modulo=" . $aRegistro["ModNombre"] . "&amp;Tipo=" . $aRegistro["ModTipo"] . "&amp;Duplic=" . $aRegistro["ModPerDuplicar"] . "\" target=\"Principal\">" . $aRegistro["ModTexto"] . "</a>&nbsp;" ;
          } else {
            $cOpcLinMenu .= "&nbsp;<a href=\"" . $aRegistro["ModLink"] . (strstr($aRegistro["ModLink"],"?")?"&amp;":"?") . "Modulo=" . $aRegistro["ModNombre"] . "&amp;Tipo=" . $aRegistro["ModTipo"] . "&amp;Duplic=" . $aRegistro["ModPerDuplicar"] . "\" target=\"Principal\">" . $aRegistro["ModTexto"] . "</a>&nbsp;" ;
          }
          $cOpcLinMenu .= $conf["SeparadorOpcionesMenu"] ;
        }
        mysql_free_result ($nResultado) ;

        echo( $conf["SeparadorOpcionesMenu"]==""?$cOpcLinMenu:substr_replace($cOpcLinMenu, '', strlen($conf["SeparadorOpcionesMenu"])*(-1), strlen($conf["SeparadorOpcionesMenu"])) ) ;
        ?>
      </td><? 
      if ($nFila==$nNroPriLin) {?>
        <td width="1%" rowspan="<?= $nCntLinMenu?>" class="Verdana11" valign="middle">
          <!-- <a href="SystemStats.php" target="Principal"><?= $txt['SystemStats']?></a>&nbsp;<?= $conf["SeparadorOpcionesMenu"]?>&nbsp; -->
		  <a href="Menu.php?Accion=LogOut"><?= $txt['Salir']?></a>&nbsp;
        </td><? 
      } ?>
    </tr><?
  }
  ?>
  <tr>
    <td colspan="2" height="7" bgcolor="#5A84AC"></td>
  </tr>
</table>
</body>
</html>