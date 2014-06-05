<?
/*
******************************************************************************
* Administrador de Contenidos                                                *
* -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-= *
*                                                                            *
* (C) 2005, Fabián Chesta                                                    *
*                                                                            *
* Comentarios:                                                               *
*                                                                            *
******************************************************************************
*/
session_start();

set_time_limit (3600);

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
$nResultado = mysql_query ($cSql) or fErrorSQL($conf["EstadoSitio"], "<br /><br /><b>Error en la consulta:</b><br />" . $cSql . "<br /><br /><b>Tipo de error:</b><br />" . mysql_error() . "<br />");
$aRegistro  = mysql_fetch_array($nResultado);

$cnfPerAcciones = $aRegistro["PerAcciones"];

mysql_free_result ($nResultado);


// Control de Accesos y Permisos
if ($cnfPerAcciones!='S') {
  echo ("No tiene autorización para ejecutar este programa.");
  exit(0);
}


// Determina cual es el Newsletter que el usuario desea enviar
$cSql = "SELECT NwsEdicTitulo, NwsEdicContHTML, NwsEdicContTexto, NwsEdicEnvio FROM NwsEdicion WHERE NwsEdicCodigo=" . $_REQUEST["Codigo"] ;
$nResultado = mysql_query ($cSql) or fErrorSQL($conf["EstadoSitio"], "<br /><br /><b>Error en la consulta:</b><br />" . $cSql . "<br /><br /><b>Tipo de error:</b><br />" . mysql_error() . "<br />");
$aRegistro  = mysql_fetch_array($nResultado);

/*                                // Control de envío completo
                                  if ($aRegistro["NwsEdicEnvio"]=="Totalmente") {
                                    echo ("Este Newsletter ya fue enviado totalmente.");
                                    exit ;
                                  }                                                   */
$cEstadoEnvi = $aRegistro["NwsEdicEnvio"] ;

$cAsunto     = $aRegistro["NwsEdicTitulo"] ;           // Asunto del mail
$cDesdeMail  = $conf["DireccionMailPredeterminada"] ;  // Dirección desde la cual se envía
$cRetorMail  = $conf["DireccionMailRetorno"] ;         // Dirección de retorno
$nCuantos    = $conf["CantidadMailsPorEnvio"] ;        // Cantidad de registros que toma para cada envío

$cContDinHTML  = $aRegistro["NwsEdicContHTML"] ;         // Contenido HTML del mail
$cContDinTexto = $aRegistro["NwsEdicContTexto"] ;        // Contenido Texto del mail

mysql_free_result ($nResultado);


// Determina Fecha y Hora del último envío
$cSql = "SELECT COUNT(*) AS ccCntEnvios, DATE_FORMAT(MAX(NwsEnviFecha),'%d-%m-%Y %H:%i:%s') AS ccUltEnvio FROM NwsEnvio WHERE NwsEdicCodigo=" . $_REQUEST["Codigo"] ;
$nResultado = mysql_query ($cSql) or fErrorSQL($conf["EstadoSitio"], "<br /><br /><b>Error en la consulta:</b><br />" . $cSql . "<br /><br /><b>Tipo de error:</b><br />" . mysql_error() . "<br />");
$aRegistro  = mysql_fetch_array($nResultado);

$nCntEnvios   = $aRegistro["ccCntEnvios"] ;            // Cantidad de mails enviados
$dFechaUltEnv = $aRegistro["ccUltEnvio"] ;             // Texto inicial

mysql_free_result ($nResultado);


// Si aún no ha sido enviado totalmente, determina la
// cantidad de suscriptores que falta recibir el mail
if ($cEstadoEnvi=='Totalmente') {
  $nCntTotal  = $nCntEnvios ;                          // Cantidad de mails enviados

} else {
  $cSql = "SELECT "
        . "  COUNT(NwsSuscCodigo) AS ccCntTotal "
        . "FROM NwsSuscriptores "
        . "WHERE NwsSuscActivo='Si'" ;
  $nResultado = mysql_query ($cSql) or fErrorSQL($conf["EstadoSitio"], "<br /><br /><b>Error en la consulta:</b><br />" . $cSql . "<br /><br /><b>Tipo de error:</b><br />" . mysql_error() . "<br />");
  $aRegistro  = mysql_fetch_array($nResultado);

  $nCntTotal  = $aRegistro["ccCntTotal"] ;             // Cantidad de mails enviados

  mysql_free_result ($nResultado);
}


// Arma el contenido del mail
$cContHTML   = "<table width=\"500\" border=\"0\" cellspacing=\"5\" cellpadding=\"0\">" ;
$cContTexto  = "" ;

$cContHTML  .= "  <tr> \n";
$cContHTML  .= "    <td> \n";
$cContHTML  .= "      <p><font color=\"#336633\">" . $cContDinHTML . "</font></p> \n";
$cContHTML  .= "    </td> \n";
$cContHTML  .= "  </tr> \n";
$cContHTML  .= "  <tr> \n";
$cContHTML  .= "    <td>&nbsp;</td> \n";
$cContHTML  .= "  </tr> \n";

$cContTexto  = $cContDinTexto . " \n\n";

$cContHTML .= "</table>" ;

?>

<html>
<head>
  <title>Envío de Newsletter</title>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">

  <link rel="stylesheet" href="Estilos/hde.css" type="text/css">

  <script language="JavaScript">
    function changeCaption(ele,txt) {
      if (document.getElementById) {
        document.getElementById(ele).firstChild.data = txt;
      } else if (document.all) {
        document.all[ele].firstChild.data = txt;
      }
    }
  </script> 
</head>

<body onUnload="javascript:top.Principal.Cuerpo.location.reload();" bgcolor="#FFFFFF" text="#000000" leftmargin="0" topmargin="10" marginwidth="0" marginheight="0">
<table width="95%" border="1" cellspacing="0" cellpadding="1" align="center" bordercolor="#FFFFFF" class="gralTabla">
  <tr>
    <td colspan="4" align="center" bgcolor="#DC9137">
      <span class="gralNormal"><b>Envío de Newsletter</b></span>
    </td>
  </tr>
  <tr height="25">
    <td bgcolor="#929292" width="25%" align="right">
      <b>T&iacute;tulo:</b>&nbsp;
    </td>
    <td colspan="3" bgcolor="#D3D3D3" width="75%" align="left">
      &nbsp;<?= $cAsunto?>
    </td>
  </tr>
  <tr height="25">
    <td bgcolor="#929292" width="25%" align="right">
      <b>Enviado:</b>&nbsp;
    </td>
    <td colspan="3" bgcolor="#D3D3D3" width="75%" align="left">
      &nbsp;<?= $cEstadoEnvi . " ( " . $nCntEnvios . " de " . $nCntTotal . " )"?>
    </td>
  </tr>
  <tr height="25">
    <td bgcolor="#929292" width="25%" align="right">
      <b>Ultimo Envío:</b>&nbsp;
    </td>
    <td colspan="3" bgcolor="#D3D3D3" width="75%" align="left">
      &nbsp;<?= $dFechaUltEnv?>
    </td>
  </tr>
  <tr height="25">
    <td bgcolor="#929292" width="25%" align="right">
      <b>Desde eMail:</b>&nbsp;
    </td>
    <td colspan="3" bgcolor="#D3D3D3" width="75%" align="left">
      &nbsp;<?= htmlentities($cDesdeMail, ENT_QUOTES, "UTF-8")?>
    </td>
  </tr>
  <tr height="25">
    <td bgcolor="#929292" width="25%" align="right">
      <b>Envíos consecutivos:</b>&nbsp;
    </td>
    <td colspan="3" bgcolor="#D3D3D3" width="75%" align="left">
      &nbsp;<?= $nCuantos?>
    </td>
  </tr>
  <tr height="25" bgcolor="#DC9137">
    <? if ($_GET['Desde']=='EnvPrueba') { ?>
      <td colspan="4">
        &nbsp;<span id="EstadoEnvio">Enviando Prueba</span>
      </td>
    <? } elseif ($_GET['Desde']=='EnvNews') { ?>
      <td colspan="4">
        &nbsp;<span id="EstadoEnvio">Enviando Newsletter</span>
      </td>
    <? } elseif ($cEstadoEnvi=='Totalmente') { ?>
      <td colspan="4" align="center">
        <input class="blanco" type="submit" name="EnvTotal" value="Newsletter Totalmente Enviado" onClick="javascript:parent.window.close()">&nbsp;
      </td>
    <? } else { ?>
      <td colspan="4" align="center">
        <input class="blanco" type="submit" name="EnvPrueba" value="Enviar Pruebas" onClick="javascript:location.href='Enviar.php?Desde=EnvPrueba&Codigo=<?= $_REQUEST["Codigo"]?>'">&nbsp;
        <input class="blanco" type="submit" name="EnvNewsletter" value="Enviar Newsletter" onClick="javascript:location.href='Enviar.php?Desde=EnvNews&Codigo=<?= $_REQUEST["Codigo"]?>'">
      </td>
    <? } ?>
  </tr>
</table>


<? 
if ($_GET['Desde']=='EnvPrueba' || $_GET['Desde']=='EnvNews') {

  include('./htmlMimeMail/htmlMimeMail.php');

  if ($_GET['Desde']=='EnvPrueba') {
    $cFiltro = "NwsSuscPrueba='Si'" ;
    $cEstado = "Enviando Prueba" ;

  } else {
    $cFiltro = "NwsSuscActivo='Si' AND NwsSuscCodigo NOT IN (SELECT NwsSuscCodigo FROM NwsEnvio WHERE NwsEdicCodigo=" . $_REQUEST["Codigo"] . ")";
    $cEstado = "Enviando Newsletter" ;
  }

  $cSql = "SELECT "
        . "  NwsSuscCodigo, NwsSuscNombre, NwsSuscMail "
        . "FROM NwsSuscriptores "
        . "WHERE " . $cFiltro . " "
        . "LIMIT " . $nCuantos ;
  $nResultado = mysql_query ($cSql) or fErrorSQL($conf["EstadoSitio"], "<br /><br /><b>Error en la consulta:</b><br />" . $cSql . "<br /><br /><b>Tipo de error:</b><br />" . mysql_error() . "<br />");

  $nFilasTot = mysql_num_rows($nResultado) ;
  $nFilasAct = 0 ;

  while ($aRegistro = mysql_fetch_array($nResultado)) {
    $nFilasAct++ ;

    $oMail = new htmlMimeMail();
    $oMail->setHeader('X-Mailer', 'HTML Mime mail');
    $oMail->setHeader('MIME-Version', '1.0');
//  $oMail->setHeader('Content-type', 'text/html;charset=ISO-8859-9');
//  $oMail->setTextCharset("utf-8");
//  $oMail->setHTMLCharset("utf-8");
//  $oMail->setHeadCharset("utf-8");

    $cTexto = $oMail->getFile("./Newsletter/Newsletter.txt");
    $cTexto = str_replace("##Contenido##", $cContTexto, $cTexto);
    $cTexto = str_replace("##Nombre##", $aRegistro["NwsSuscNombre"], $cTexto);
    $cTexto = str_replace("##EMail##", $aRegistro["NwsSuscMail"], $cTexto);
    $cTexto = str_replace("##ClaveBaja##", md5("#".$aRegistro["NwsSuscCodigo"]."#".$aRegistro["NwsSuscMail"]."#"), $cTexto);

    $cHTML  = $oMail->getFile("./Newsletter/Newsletter.html");
    $cHTML  = str_replace("##Contenido##", $cContHTML, $cHTML);
    $cHTML  = str_replace("##Nombre##", $aRegistro["NwsSuscNombre"], $cHTML);
    $cHTML  = str_replace("##EMail##", $aRegistro["NwsSuscMail"], $cHTML);
    $cHTML  = str_replace("##ClaveBaja##", md5("#".$aRegistro["NwsSuscCodigo"]."#".$aRegistro["NwsSuscMail"]."#"), $cHTML);

    $oMail->setHtml($cHTML, $cTexto, '../');
    $oMail->setFrom($cDesdeMail);
    $oMail->setReturnPath($cRetorMail);
    $oMail->setSubject($cAsunto);
    $oMail->send(array($aRegistro["NwsSuscMail"]));

    unset($oMail);

    if ($_GET['Desde']=='EnvNews') {
      $cSql = "INSERT INTO NwsEnvio (NwsEdicCodigo, NwsSuscCodigo) VALUES (" . $_REQUEST["Codigo"] . ", " . $aRegistro["NwsSuscCodigo"] . ")";
      $nResulIns = mysql_query ($cSql) or fErrorSQL($conf["EstadoSitio"], "<br /><br /><b>Error en la consulta:</b><br />" . $cSql . "<br /><br /><b>Tipo de error:</b><br />" . mysql_error() . "<br />");
    }

    ?>
    <script language="JavaScript">
      changeCaption('EstadoEnvio', '<?= $cEstado?> - <?= str_replace("'","\'",$aRegistro["NwsSuscNombre"])?> [<?= $aRegistro["NwsSuscMail"]?>] - <?= $nFilasAct?> de <?= $nFilasTot?>');
    </script>
    <?
  }
  mysql_free_result ($nResultado);

  if ($_GET['Desde']=='EnvNews') {
    $cSql = "SELECT "
          . "  COUNT(NwsSuscCodigo) AS ccCntRegistros "
          . "FROM NwsSuscriptores "
          . "WHERE NwsSuscActivo='Si' AND NwsSuscCodigo NOT IN (SELECT NwsSuscCodigo FROM NwsEnvio WHERE NwsEdicCodigo=" . $_REQUEST["Codigo"] . ")";
    $nResultado = mysql_query ($cSql) or fErrorSQL($conf["EstadoSitio"], "<br /><br /><b>Error en la consulta:</b><br />" . $cSql . "<br /><br /><b>Tipo de error:</b><br />" . mysql_error() . "<br />");

    $aRegistro = mysql_fetch_array($nResultado) ;

    $nCntFaltan = $aRegistro["ccCntRegistros"];

    mysql_free_result ($nResultado);

    $cSql = "UPDATE NwsEdicion SET NwsEdicEnvio='" . ($nCntFaltan==0?"Totalmente":"Parcialmente") . "' WHERE NwsEdicCodigo=" . $_REQUEST["Codigo"] ;
    $nResulIns = mysql_query ($cSql) or fErrorSQL($conf["EstadoSitio"], "<br /><br /><b>Error en la consulta:</b><br />" . $cSql . "<br /><br /><b>Tipo de error:</b><br />" . mysql_error() . "<br />");

    ?>
    <script language="JavaScript">
      // ERROR JS -> Actualizar la pagina principal
      // opener.location.reload();
      opener.Cuerpo.location.reload();
      // document.top.Principal.Cuerpo.location.reload();
      // document.getElementById('Principal').location.reload();
      // Info.location.reload();
    </script><?
  } 
} ?>

</body>
</html>
