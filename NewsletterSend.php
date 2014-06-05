<?
session_start();
set_time_limit (3600);

// Archivos de Conexión y Configuración
include("Conexion.inc.php");
include("Lenguajes/" . $conf["Lenguaje"]);
include("Funciones/Funciones.inc.php");

// Determina los permisos necesarios para las diferentes acciones
$cSql = "SELECT ModTexto, PerAcciones FROM sysModulos LEFT JOIN sysModUsu ON sysModulos.ModNombre=sysModUsu.ModNombre WHERE sysModulos.ModNombre='" . $_SESSION["gbl".$conf["VariablesSESSION"]."Modulo"] . "' AND sysModUsu.UsuAlias='" . $_SESSION["gbl".$conf["VariablesSESSION"]."Alias"] . "'";
$nResultado = mysql_query ($cSql) or die("Error en la consulta: <b>" . $cSql . "</b> <br>Tipo de error: <b>" . mysql_error() . "</b>");
$aRegistro  = mysql_fetch_array($nResultado);

$cnfPerAcciones = $aRegistro["PerAcciones"];

mysql_free_result ($nResultado);


// Control de Accesos y Permisos
if ($_SESSION["gbl".$conf["VariablesSESSION"]."Alias"]=="" or $cnfPerAcciones!='S') {
	echo ("No tiene autorizaci&oacute;n para ejecutar este programa.");
	exit ;
}


// Determina cual es el Newsletter que el usuario desea enviar
$cSql =  "SELECT NwsEdicCodigo, NwsEdicTitulo, NwsEdicContHTML, NwsEdicContTexto, NwsEdicEnvio, NwsEdicPlantilla"
		." FROM NwsEdicion"
		." WHERE NwsEdicion.NwsEdicCodigo='".$_REQUEST["Codigo"]."'";
$nResultado = mysql_query ($cSql) or die("Error en la consulta: <b>" . $cSql . "</b> <br>Tipo de error: <b>" . mysql_error() . "</b>");
$aRegistro  = mysql_fetch_array($nResultado);

/*                                // Control de envío completo
                                  if ($aRegistro["NwsEdicEnvio"]=="Totalmente") {
                                    echo ("Este Newsletter ya fue enviado totalmente.");
                                    exit ;
                                  }                                                   */
$cLetterID	 = $aRegistro["NwsEdicCodigo"] ;
$cEstadoEnvi = $aRegistro["NwsEdicEnvio"] ;
$cAsunto     = $aRegistro["NwsEdicTitulo"] ;			// Asunto del mail
$nPlantilla	 = $aRegistro["NwsEdicPlantilla"] ;			// 
$cCliente    = $conf["NombreCliente"] ;					// Nombre del cliente
$cContacto   = $conf["DatosContacto"] ;					// Datos del cliente (pie)
$cDesdeMail  = $conf["DireccionMailPredeterminada"] ;	// Dirección desde la cual se envía
$cDesdeNombre= $conf["NombreMailPredeterminada"] ;		// 
$cRetorMail  = $conf["DireccionMailRetorno"] ;			// Dirección de retorno
$nCuantos    = $conf["CantidadMailsPorEnvio"] ;			// Cantidad de registros que toma para cada envío
$s_name      = "http://".$_SERVER['SERVER_NAME'];		// Dirección URL del servidor

if($nPlantilla=='Comunicado'){
	$docPlantilla = 'NewsletterComunicado';
}else if($nPlantilla=='Flyer'){
	$docPlantilla = 'NewsletterFlyer';
}else{
	$docPlantilla = 'NewsletterStandar';
}

$cContDinHTML   = str_ireplace('src="/', 'src="http://'.$_SERVER['HTTP_HOST'].'/', $aRegistro["NwsEdicContHTML"]);	// Texto del mail en HTML
$cContDinTexto = $aRegistro["NwsEdicContTexto"] ;        											// Contenido Texto del mail
mysql_free_result ($nResultado);


/* SE CREAN LOS FILTROS DE ACUERDO A LOS USUARIOS PERTENECIENTES A LOS GRUPOS SELECCIONADOS */
$cFiltroSus  = "1=2" ;
$cSql =  "SELECT NwsGruCodigo, NwsEdicCodigo"
		." FROM NwsRelNlGru"
		." WHERE NwsEdicCodigo='".$_REQUEST["Codigo"]."'";
	$nResultado = mysql_query ($cSql) or die("Error en la consulta: <b>" . $cSql . "</b> <br>Tipo de error: <b>" . mysql_error() . "</b>");		

	if(mysql_num_rows($nResultado)==0){
		echo 'El newsletter no tiene ning&uacute;n grupo asociado al que realizar el env&iacute;o.<br>Edite el mismo e incluya uno o m&aacute;s grupos de usuarios.';
	}else{
		while ( $aRegistro=mysql_fetch_array($nResultado) ) {
			$cFiltroSus .= " OR NwsGruCodigo = '".$aRegistro['NwsGruCodigo']."'";
		}
	}
mysql_free_result ($nResultado);
$cFiltroSus  = "(" . $cFiltroSus . ")" ;				// regla final de filtrado


// Determina Fecha y Hora del último envío
$cSql = "SELECT COUNT(*) AS ccCntEnvios, DATE_FORMAT(MAX(NwsEnviFecha),'%d-%m-%Y %H:%i:%s') AS ccUltEnvio FROM NwsEnvio WHERE NwsEdicCodigo=" . $_REQUEST["Codigo"] ;
$nResultado = mysql_query ($cSql) or die("Error en la consulta: <b>" . $cSql . "</b> <br>Tipo de error: <b>" . mysql_error() . "</b>");
$aRegistro  = mysql_fetch_array($nResultado);

$nCntEnvios   = $aRegistro["ccCntEnvios"] ;            // Cantidad de mails enviados
$dFechaUltEnv = $aRegistro["ccUltEnvio"] ;             // Timestamp del ultimo envío
mysql_free_result ($nResultado);


// Si aún no ha sido enviado totalmente, determina la
// cantidad de suscriptores que falta recibir el mail
if ($cEstadoEnvi=='Totalmente') {
  $nCntTotal  = $nCntEnvios ;                          // Se ha enviado el newsletter a todos los suscriptores

} else {
  $cSql =  " SELECT COUNT(UsrCode) AS ccCntTotal "
  		  ." FROM (SELECT DISTINCT NwsRelUsrGru.UsrCode FROM NwsRelUsrGru LEFT JOIN NwsUsuarios ON NwsRelUsrGru.UsrCode=NwsUsuarios.UsrCode WHERE 1=1 AND UsrEMail!='' AND ".$cFiltroSus.") AS newTable";
  $nResultado = mysql_query ($cSql) or die("Error en la consulta: <b>" . $cSql . "</b> <br>Tipo de error: <b>" . mysql_error() . "</b>");
  $aRegistro  = mysql_fetch_array($nResultado);

  $nCntTotal  = $aRegistro["ccCntTotal"] ;             // Cantidad de usuarios q recibirán el mail
  mysql_free_result ($nResultado);
}


// Novedades a incluir en el Newsletter
$cNotas = "";
$cNotasTxt = "";

if($nPlantilla=='Standar'){	// IF > si la plantilla es Standar e incluye novedades
  $cSql = "SELECT "
      . "  NwsContenido.NovCodigo, NovTitulo, NovApostilla, NovImagen AS ccImagen "
      . "FROM NwsContenido "
      . "  INNER JOIN Novedades ON NwsContenido.NovCodigo=Novedades.NovCodigo "
      . "WHERE NwsContenido.NwsEdicCodigo=" . $_REQUEST["Codigo"] . " "
      . "ORDER BY NovCodigo DESC" ;
  $nResultado = mysql_query ($cSql) or die("Error en la consulta: <b>" . $cSql . "</b> <br>Tipo de error: <b>" . mysql_error() . "</b>");
/*if(mysql_num_rows($nResultado)>0){
	$cContHTML	.= "<tr><td><p style=\"color:#666;font-size:12px; font-weight:bold\">Otras Novedades:<br />......................................................................................................</p></td></tr>";
}*/
// >>>>>>>>>>>> MEJORAR LA GRAFICA DE LAS NEWS EN EL STANDAR
  while ( $aRegistro=mysql_fetch_array($nResultado) ) {
	if ($aRegistro["ccImagen"] && is_file("../Upload/Directos/Novedades/" . $aRegistro["ccImagen"])) {
	  $aPropImg = getimagesize ("../Upload/Directos/Novedades/" . $aRegistro["ccImagen"]);
	  $cImagen = "<img src=\"../Upload/Directos/Novedades/" . $aRegistro["ccImagen"] . "\" width=\"120\" align=\"left\">" ;
	} else {
      $cImagen = "" ;
	}
 
	$cNotas  .= "  <tr> \n";
	$cNotas  .= "    <td>&nbsp;</td> \n";
	$cNotas  .= "    <td colspan=\"3\" valign=\"top\"> \n";
	$cNotas  .= "      <a href=\"".$s_name."/novedades.php?n=" . $aRegistro["NovCodigo"] . "\"><p style=\"font-family:Verdana, Geneva, sans-serif; font-size:11px; color: #666; text-decoration:none;\"><b>" . $aRegistro["NovTitulo"] . "</b></p></a> \n";
	$cNotas  .= "    </td> \n";
	$cNotas  .= "    <td>&nbsp;</td> \n";
	$cNotas  .= "  </tr> \n";
	$cNotas  .= "  <tr> \n";
	$cNotas  .= "    <td>&nbsp;</td> \n";
	$cNotas  .= "    <td colspan=\"3\" valign=\"top\"> \n";
	  $cNotas  .= "<table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\">";
	  $cNotas  .= "<tr><td valign=\"top\">".$cImagen."</td><td width=\"4\">&nbsp;</td>";
	  $cNotas  .= "<td valign=\"top\"><p style=\"font-family:Verdana, Geneva, sans-serif; font-size:11px; color: #666\">".nl2br($aRegistro["NovApostilla"])."</p><a href=\"".$s_name."/novedades.php?n=" . $aRegistro["NovCodigo"] . "\"><span  style=\"font-family:Verdana, Geneva, sans-serif; font-size:10px; color: #666; text-align:right\">  ver nota »</span></a></td></tr>";
	  $cNotas  .= "</table>";
	$cNotas  .= "    </td> \n";
	$cNotas  .= "    <td>&nbsp;</td> \n";
	$cNotas  .= "  </tr> \n";
	$cNotas  .= "  <tr> \n";
	$cNotas  .= "    <td>&nbsp;</td> \n";
	$cNotas  .= "    <td colspan=\"3\" valign=\"top\">&nbsp;</td> \n";
	$cNotas  .= "    <td>&nbsp;</td> \n";
	$cNotas  .= "  </tr> \n";
	
	$cNotasTxt .= $aRegistro["NovTitulo"] . " \n";
	$cNotasTxt .= $aRegistro["NovApostilla"] . " \n\n";
  }
  mysql_free_result ($nResultado);
}	// cierra el if (si la plantilla es Standar e incluye novedades)

?>

<html>
<head>
  <title>Env&iacute;o de Newsletter</title>
  <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">

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

<body bgcolor="#FFFFFF" text="#000000" leftmargin="0" topmargin="10" marginwidth="0" marginheight="0">
<table width="95%" border="1" cellspacing="0" cellpadding="1" align="center" bordercolor="#FFFFFF" class="gralTabla">
  <tr>
    <td colspan="4" align="center" bgcolor="#DC9137">
      <span class="gralNormal"><b>Env&iacute;o de Newsletter</b></span>
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
<!--      &nbsp;<?= $cEstadoEnvi . " ( " . $nCntEnvios . " de " . $nCntTotal . " )"?>-->
      &nbsp;<?= $cEstadoEnvi ?>
    </td>
  </tr>
  <tr height="25">
    <td bgcolor="#929292" width="25%" align="right">
      <b>Ultimo Env&iacute;o:</b>&nbsp;
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
      &nbsp;<?= htmlentities($cDesdeMail)?>
    </td>
  </tr>
  <tr height="25">
    <td bgcolor="#929292" width="25%" align="right">
      <b>Env&iacute;os consecutivos:</b>&nbsp;
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
        <input class="blanco" type="submit" name="EnvPrueba" value="Enviar Pruebas" onClick="javascript:location.href='NewsletterSend.php?Desde=EnvPrueba&Codigo=<?= $_REQUEST["Codigo"]?>'">&nbsp;
        <input class="blanco" type="submit" name="EnvNewsletter" value="Enviar Newsletter" onClick="javascript:location.href='NewsletterSend.php?Desde=EnvNews&Codigo=<?= $_REQUEST["Codigo"]?>'">
      </td>
    <? } ?>
  </tr>
</table>


<? 
if ($_GET['Desde']=='EnvPrueba' || $_GET['Desde']=='EnvNews') {
	
  if ($_GET['Desde']=='EnvPrueba') {
    $cFiltro = "UsrPrueba='Si'" ;
    $cEstado = "Enviando Prueba" ;

  } else {
    $cFiltro = "UsrActivo='Si' AND UsrEMail!='' AND " . $cFiltroSus . " AND NwsRelUsrGru.UsrCode NOT IN (SELECT UsrCode FROM NwsEnvio WHERE NwsEdicCodigo=" . $_REQUEST["Codigo"] . ")";
    $cEstado = "Enviando Newsletter" ;
  }


  if ($_GET['Desde']=='EnvPrueba') {
  $cSql = "SELECT "
        . "  UsrCode, UsrNombre, UsrEMail, UsrPassword "
        . " FROM NwsUsuarios "
        . " WHERE " . $cFiltro . " ";
  }else{
		
  // consulta a grupos de usuarios
  // Pregunto que grupos se asocian con este Newsletter y los escribo como filtro para la consulta a la tabla usuarios
  
  $cSql = " SELECT NwsRelUsrGru.UsrCode, UsrNombre, UsrEMail, UsrPassword "
  		. " FROM NwsRelUsrGru "
		. "  INNER JOIN NwsUsuarios ON NwsRelUsrGru.UsrCode=NwsUsuarios.UsrCode "
		. "WHERE " . $cFiltro . " "
		. "GROUP BY NwsRelUsrGru.UsrCode "
        . "LIMIT " . $nCuantos ;
  }
  $nResultado = mysql_query ($cSql) or die("Error en la consulta: " . $cSql . " Tipo de error: " . mysql_error());

  $nFilasTot = mysql_num_rows($nResultado) ;
  $nFilasAct = 0 ;

  while ($aRegistro = mysql_fetch_array($nResultado)) {
	$nFilasAct++ ;
	//$link_baja = $s_name."/unsubscribe.php?eid=".md5($aRegistro["UsrCode"])."&eem=".md5($aRegistro["UsrEMail1"]);
	$link_baja = $s_name."/unsubscribe/".md5($aRegistro["UsrCode"])."/".md5($aRegistro["UsrEMail1"]).'.html';

    require_once './phpMailer/class.phpmailer.php';
	$mail = new PHPMailer(true);
	try {
		
		$mail->CharSet = 'UTF-8';
		$mail->SetFrom($cDesdeMail, $cDesdeNombre);
		$mail->AddReplyTo($cRetorMail, $cDesdeNombre);
		$mail->Subject = $cAsunto;
		$mail->AddAddress($aRegistro['UsrEMail'], $aRegistro['UsrNombre']);
		
		//si la imágen de encabezado es otra, cambiar ruta y nombre
		$mail->AddEmbeddedImage("./Newsletter/Imagenes/Header.jpg", "my-attach", "header.jpg");
		$body  = file_get_contents('./Newsletter/'.$docPlantilla.'.html');
		
		$body  = str_replace("##imgheader##", ('<img alt="'.$cCliente.'" src="cid:my-attach">'), $body);
		$body  = str_replace("##TitCliente##", $cCliente, $body);
		$body  = str_replace("##DatosContacto##", $cContacto, $body);
		$body  = str_replace("##s_name##", $s_name, $body);
		$body  = str_replace("##Contenido##", $cContDinHTML, $body);
		$body  = str_replace("##Nombre##", $aRegistro['UsrNombre'], $body);
		$body  = str_replace("##EMail##", $aRegistro["UsrEMail"], $body);
		$body  = str_replace("##Clave##", $aRegistro["UsrPassword"], $body);
		$body  = str_replace("##LinkBaja##", $link_baja, $body);
		$body  = str_replace("##Notas##", $cNotas, $body);

		//$body  = str_replace("##LinkBaja##", "http://".$_SERVER['SERVER_NAME']."/"$link_baja, $body);
		
		$mail->MsgHTML($body);
		$mail->IsHTML(true);
		
		$txtAlt  = file_get_contents('./Newsletter/'.$docPlantilla.'.txt');
		$txtAlt  = str_replace("##TitCliente##", stripslashes($cCliente), $txtAlt);
		$txtAlt  = str_replace("##DatosContacto##", stripslashes($cContacto), $txtAlt);
		$txtAlt  = str_replace("##s_name##", stripslashes($s_name), $txtAlt);
		$txtAlt  = str_replace("##Contenido##", stripslashes($cContTexto), $txtAlt);
		$txtAlt  = str_replace("##Nombre##", stripslashes(ucwords(strtolower($aRegistro['UsrNombre']))), $txtAlt);
		$txtAlt  = str_replace("##EMail##", stripslashes($aRegistro["UsrEMail"]), $txtAlt);
		$txtAlt  = str_replace("##Clave##", stripslashes($aRegistro["UsrPassword"]), $txtAlt);
		$txtAlt  = str_replace("##ClaveBaja##", stripslashes(md5("#".$aRegistro["UsrCode"]."#".$aRegistro["UsrEMail"]."#")), $txtAlt);
		$txtAlt  = str_replace("##LinkBaja##", stripslashes($link_baja), $body);
		$txtAlt  = str_replace("##Notas##", stripslashes($cNotasTxt), $txtAlt);
		$mail->AltBody = $txtAlt; // optional - MsgHTML will create an alternate automatically
			  
		$mail->Send();
		
		echo $aRegistro['UsrEMail']. " - Message Sent OK</p>\n";
		unset($mail);
	} catch (phpmailerException $e) {
		echo $e->errorMessage(); //Pretty error messages from PHPMailer
	} catch (Exception $e) {
		echo $e->getMessage(); //Boring error messages from anything else!
	}
	
	
    // escribo el enviado en la tabla NwsEnvio
	if ($_GET['Desde']=='EnvNews') {
      $cSql = "INSERT INTO NwsEnvio (NwsEdicCodigo, UsrCode) VALUES (" . $_REQUEST["Codigo"] . ", " . $aRegistro["UsrCode"] . ")";
      $nResulIns = mysql_query ($cSql) or die("Error en la consulta: <b>" . $cSql . "</b> <br>Tipo de error: <b>" . mysql_error() . "</b>");
    }

    ?>
    <script language="JavaScript">
      changeCaption('EstadoEnvio', '<?= $cEstado?> - <?= str_replace("'","\'",$aRegistro["UsrFirstName1"])?> [<?= $aRegistro["UsrEMail1"]?>] - <?= $nFilasAct?> de <?= $nFilasTot?>');
    </script>
    <?
  }
  mysql_free_result ($nResultado);

  if ($_GET['Desde']=='EnvNews') {
	$cSql =  " SELECT COUNT(UsrCode) AS ccCntRegistros "
			." FROM (SELECT DISTINCT NwsRelUsrGru.UsrCode FROM NwsRelUsrGru LEFT JOIN NwsUsuarios ON NwsRelUsrGru.UsrCode=NwsUsuarios.UsrCode WHERE 1=1 AND UsrEMail!='' AND ".$cFiltroSus." AND NwsRelUsrGru.UsrCode NOT IN (SELECT UsrCode FROM NwsEnvio WHERE NwsEdicCodigo=".$_REQUEST["Codigo"].")) AS newTable";
	$nResultado = mysql_query ($cSql) or die("Error en la consulta: <b>" . $cSql . "</b> <br>Tipo de error: <b>" . mysql_error() . "</b>");


/*    $cSql = "SELECT "
          . "  COUNT(UsrCode) AS ccCntRegistros "
          . "FROM Users "
          . "WHERE UsrActivo='Si' AND " . $cFiltroSus . " AND UsrCode NOT IN (SELECT UsrCode FROM NwsEnvio WHERE NwsEdicCodigo=" . $_REQUEST["Codigo"] . ")";
    $nResultado = mysql_query ($cSql) or die("Error en la consulta: " . $cSql . " Tipo de error: " . mysql_error());*/

    $aRegistro = mysql_fetch_array($nResultado);

    $nCntFaltan = $aRegistro["ccCntRegistros"];
	$nCntEnviados = $nCntTotal-$nCntFaltan;

    mysql_free_result ($nResultado);

    $cSql = "UPDATE NwsEdicion SET NwsEdicEnvio='" . ($nCntFaltan==0?"Totalmente":"Parcialmente (".$nCntEnviados.' de '.$nCntTotal.")") . "' WHERE NwsEdicCodigo=" . $_REQUEST["Codigo"] ;
    $nResulIns = mysql_query ($cSql) or die("Error en la consulta: <b>" . $cSql . "</b> <br>Tipo de error: <b>" . mysql_error() . "</b>");

    ?>
    <script language="JavaScript">
      // ERROR JS -> Actualizar la pagina principal
      opener.Cuerpo.location.reload();
      //document.top.Principal.Cuerpo.location.reload();
      //document.getElementById('Principal').location.reload();
      // Info.location.reload();
	  //self.opener.top.Principal.Cuerpo.location.reload()
    </script><?
  } 
} ?>
</body>
</html>