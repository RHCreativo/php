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
include("Funciones/Funciones.inc.php");
include("Lenguajes/" . $conf["Lenguaje"]);


// Variables del Form anterior
$Alias    = (isset($_POST["Alias"])?fPonerBarras($_POST["Alias"]):"");
$Clave    = (isset($_POST["Clave"])?fPonerBarras($_POST["Clave"]):"");
$CodSeg   = (isset($_POST["CodSeguridad"])?fPonerBarras($_POST["CodSeguridad"]):"");
$IdPagina = (isset($_POST["IdPagina"])?$_POST["IdPagina"]:"");

$Mensaje = "";

if ( $Alias!="" && $Clave!="" && $IdPagina!="" && $CodSeg!="" ) {

  if ( ($IdPagina==$_SESSION["gbl".$conf["VariablesSESSION"]."IdPagina"]) ) {

    if ( $_SESSION["gblSecurityCode"]==$CodSeg && !empty($_SESSION["gblSecurityCode"]) ) {

      // Arma la instrucción SQL y luego la ejecuta
      $cSql = "SELECT *, DATE_FORMAT(UsuUltLogin,'%d-%m-%Y %H:%i') as ccUltLogin FROM sysUsuarios WHERE UsuAlias='" . $Alias . "' AND UsuClave=MD5('" . $Clave . "')";

      $nResultado = $nConexion->query ($cSql) or fErrorSQL($conf["EstadoSitio"], "<br /><br /><b>Error en la consulta:</b><br />" . $cSql . "<br /><br /><b>Tipo de error:</b><br />" . mysqli_error($nConexion) . "<br />");


      // Verifica si el usuario es válido
      if ( !($nResultado->num_rows) ) {

        $Mensaje = $txt['ErrorIngreso'];

        // Datos del usuario que intentó loguearse
        $cSql = "INSERT INTO sysLogins "
              . "  (LogUser, LogStatus, LogIP, LogBrowser) "
              . " VALUES "
              . "  ('".$Alias."', 'Error', '".$_SERVER["REMOTE_ADDR"]."', '".$_SERVER["HTTP_USER_AGENT"]."')" ;
        $nConexion->query ($cSql) or fErrorSQL($conf["EstadoSitio"], "<br /><br /><b>Error en la consulta:</b><br />" . $cSql . "<br /><br /><b>Tipo de error:</b><br />" . mysqli_error($nConexion) . "<br />");

      } else {

        $aRegistro = $nResultado->fetch_object();

        $_SESSION["gbl".$conf["VariablesSESSION"]."IdPagina"]   = "";
        $_SESSION["gbl".$conf["VariablesSESSION"]."Alias"]      = $aRegistro->UsuAlias;
        $_SESSION["gbl".$conf["VariablesSESSION"]."Nombre"]     = $aRegistro->UsuNombre;
        $_SESSION["gbl".$conf["VariablesSESSION"]."UltLogin"]   = $aRegistro->ccUltLogin;
        $_SESSION["gbl".$conf["VariablesSESSION"]."CntFiltros"] = $aRegistro->UsuCntFiltros;
        /*
        for ($nCampo=1; $nCampo<=mysql_num_fields($nResultado); $nCampo++) {
          if (substr(mysql_field_name($nResultado,$nCampo),0,3)=="ses") {
            $_SESSION[mysql_field_name($nResultado,$nCampo)] = $aRegistro[mysql_field_name($nResultado,$nCampo)];
          }
        }
        */
        // Arma la instrucción SQL y luego la ejecuta
        $cSql = "UPDATE sysUsuarios SET UsuUltLogin=NOW() WHERE UsuAlias='" . $Alias . "'";
        $nConexion->query ($cSql) or fErrorSQL($conf["EstadoSitio"], "<br /><br /><b>Error en la consulta:</b><br />" . $cSql . "<br /><br /><b>Tipo de error:</b><br />" . mysqli_error($nConexion) . "<br />");


        // Datos del usuario que se loguea
        $cSql = "INSERT INTO sysLogins "
              . "  (LogUser, LogStatus, LogIP, LogBrowser) "
              . " VALUES "
              . "  ('".$Alias."', 'Ok', '".$_SERVER["REMOTE_ADDR"]."', '".$_SERVER["HTTP_USER_AGENT"]."')" ;
        $nConexion->query ($cSql) or fErrorSQL($conf["EstadoSitio"], "<br /><br /><b>Error en la consulta:</b><br />" . $cSql . "<br /><br /><b>Tipo de error:</b><br />" . mysqli_error($nConexion) . "<br />");


        // Mantenimiento de la tabla sysLogins (guardo solo los ultimos 6 meses)
        $cSql = "DELETE FROM sysLogins WHERE LogTime < NOW()-INTERVAL 6 MONTH" ;
        $nConexion->query ($cSql) or fErrorSQL($conf["EstadoSitio"], "<br /><br /><b>Error en la consulta:</b><br />" . $cSql . "<br /><br /><b>Tipo de error:</b><br />" . mysqli_error($nConexion) . "<br />");


        header ("Location: Divide.php?Opcion=0");
        exit(0);

      }

      // Cierra los Objetos de acceso a los datos y libera las variables
      mysqli_free_result ($nResultado);

    } else {

      $Mensaje = $txt['ErrorCodSeg'];

      // Datos del usuario que intentó loguearse
      $cSql = "INSERT INTO sysLogins "
            . "  (LogUser, LogStatus, LogIP, LogBrowser) "
            . " VALUES "
            . "  ('".$Alias."', 'SecCode', '".$_SERVER["REMOTE_ADDR"]."', '".$_SERVER["HTTP_USER_AGENT"]."')" ;
      $nConexion->query ($cSql) or fErrorSQL($conf["EstadoSitio"], "<br /><br /><b>Error en la consulta:</b><br />" . $cSql . "<br /><br /><b>Tipo de error:</b><br />" . mysqli_error($nConexion) . "<br />");

    }

  } else {

    $Mensaje = $txt['ErrorForm'];

    // Datos del usuario que intentó loguearse
    $cSql = "INSERT INTO sysLogins "
          . "  (LogUser, LogStatus, LogIP, LogBrowser) "
          . " VALUES "
          . "  ('".$Alias."', 'IdPagina', '".$_SERVER["REMOTE_ADDR"]."', '".$_SERVER["HTTP_USER_AGENT"]."')" ;
    $nConexion->query ($cSql) or fErrorSQL($conf["EstadoSitio"], "<br /><br /><b>Error en la consulta:</b><br />" . $cSql . "<br /><br /><b>Tipo de error:</b><br />" . mysqli_error($nConexion) . "<br />");

  }

}


unset($_SESSION["gblSecurityCode"]);

$cIdPagina = md5(uniqid(rand(), true));
$_SESSION["gbl".$conf["VariablesSESSION"]."IdPagina"] = $cIdPagina;
?>

<html>
<head>
    <title>.:. <?= $conf["NombreCliente"]?> .:.<?= ($conf["EstadoSitio"]!="Production"?$txt['ModoPrueba']:"")?></title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">

    <link rel="stylesheet" href="Estilos/hde.css" type="text/css">
</head>

<script language="JavaScript">
<!--
function Validar(elForm) {
  if (elForm.Alias.value == "" ) {
    alert("<?= $txt['JSMsgCpoUsuario']?>");
    elForm.Alias.focus();
    return (false);
  }

  if (elForm.Clave.value == "" ) {
    alert("<?= $txt['JSMsgCpoClave']?>");
    elForm.Clave.focus();
    return (false);
  }

  if (elForm.CodSeguridad.value == "" ) {
    alert("<?= $txt['JSMsgCpoCodSeg']?>");
    elForm.CodSeguridad.focus();
    return (false);
  }

  return (true);
}
-->
</script>

<body bgcolor="#38628A" text="#000000" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0" onLoad="if (self != top) top.location = self.location;">

<form  action="<?= $_SERVER["PHP_SELF"]?>" method="POST" name="Login" onSubmit="return Validar(this)" target="_self">
<input type="hidden" name="IdPagina" value="<?= $cIdPagina?>">
<table width="100%" border="0" cellspacing="0" cellpadding="0" height="97%">
  <tr>
    <td bgcolor="#38628A" align="left" valign="top" height="33%"><br>&nbsp;&nbsp;<img src="Imagenes/<?= $conf["LogoCliente"]?>"></td>
  </tr>
  <tr>
    <td height="1%" bgcolor="#FFFFFF"></td>
  </tr>
  <tr>
    <td bgcolor="#5A84AC" height="40%" align="center" valign="middle">
      <table border="0" align="center" cellpadding="0" cellspacing="0">
        <tr bgcolor="#DC9137">
          <td style="BORDER: #000000 1px solid" colspan="3" height="15"><font size="-7">&nbsp;</font></td>
        </tr>
        <tr bgcolor="#D3D3D3" valign="middle">
          <td style="BORDER-LEFT: #000000 1px solid" class="Verdana11" height="33" width="150" align="right"><?= $txt['Usuario']?>:&nbsp;</td>
          <td height="33" align="left">
            &nbsp;<input type="text" name="Alias" size="10" maxlength="25" class="Verdana11" value="<?= fSacarBarras($Alias)?>">&nbsp;
          </td>
          <td style="BORDER-RIGHT: #000000 1px solid" height="33" width="150" align="center">&nbsp;</td>
        </tr>
        <tr bgcolor="#D3D3D3" valign="middle">
          <td style="BORDER-LEFT: #000000 1px solid" class="Verdana11" height="33" width="150" align="right"><?= $txt['Clave']?>:&nbsp;</td>
          <td height="33" align="left">
            &nbsp;<input type="password" name="Clave" size="10" maxlength="25" class="Verdana11" value="<?= fSacarBarras($Clave)?>">&nbsp;
          </td>
          <td style="BORDER-RIGHT: #000000 1px solid" height="33" width="150" align="center">&nbsp;</td>
        </tr>
        <tr bgcolor="#D3D3D3" valign="middle">
          <td style="BORDER-LEFT: #000000 1px solid" class="Verdana11" height="33" width="150" align="right"><?= $txt['CodSeguridad']?>:&nbsp;</td>
          <td height="33" align="left">
            &nbsp;<input type="text" name="CodSeguridad" size="10" maxlength="10" class="Verdana11" value="">&nbsp;
          </td>
          <td style="BORDER-RIGHT: #000000 1px solid" height="33" width="150" align=" left">&nbsp;<img src="captcha/CaptchaSecurityImages.php?width=100&height=30&random=<?= md5(microtime().mt_rand(1919,19691969))?>" /></td>
        </tr>
        <tr bgcolor="#D3D3D3" valign="middle">
          <td style="BORDER-BOTTOM: #000000 1px solid; BORDER-LEFT: #000000 1px solid" class="Verdana11" height="33" width="150" align="right">&nbsp;</td>
          <td style="BORDER-BOTTOM: #000000 1px solid" height="33" align="left">
            &nbsp;<input class="blanco" type="submit" value="<?= $txt['Ingresar']?>">
          </td>
          <td style="BORDER-BOTTOM: #000000 1px solid; BORDER-RIGHT: #000000 1px solid" width="150" height="33" align="center">&nbsp;</td>
        </tr>
        <? if ( $Mensaje ) { ?>
          <tr bgcolor="#5A84AC">
            <td colspan="3" height="15"></td>
          </tr>
          <tr bgcolor="#DC9137">
            <td style="BORDER: #000000 1px solid" colspan="3" height="15"><font size="-7">&nbsp;</font></td>
          </tr>
          <tr bgcolor="#D3D3D3" align="center" valign="middle">
            <td colspan="3" style="BORDER-RIGHT: #000000 1px solid; BORDER-BOTTOM: #000000 1px solid; BORDER-LEFT: #000000 1px solid" class="Verdana11" height="20"><img src="Imagenes/imgAlerta.gif" width="11" height="11" align="absmiddle">&nbsp;<?= $Mensaje?></td>
          </tr>
        <? } ?>
      </table>
    </td>
  </tr>
  <tr>
    <td height="1%" bgcolor="#FFFFFF"></td>
  </tr>
  <tr>
    <td bgcolor="#38628A" align="right" valign="bottom" height="33%"><img src="Imagenes/<?= $conf["LogoDesarrollador"]?>">&nbsp;&nbsp;</td>
  </tr>
</table>
</form>

</body>
</html>