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

    // Averiguo el username y estado
    $cSql = "SELECT CstUser, CstStatus FROM Customers WHERE CstId=" . $_GET["Codigo"];
    $nResultado = mysql_query ($cSql) or fErrorSQL($conf["EstadoSitio"], "<br /><br /><b>Error en la consulta:</b><br />" . $cSql . "<br /><br /><b>Tipo de error:</b><br />" . mysql_error() . "<br />");
    $aRegistro  = mysql_fetch_array($nResultado);
    
    $cCstUser   = $aRegistro["CstUser"];
    $cCstStatus = $aRegistro["CstStatus"];
    
    mysql_free_result ($nResultado);
    
    ?>
    <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
        "http://www.w3.org/TR/html4/loose.dtd">
    <html>
    <head>
      <title>User <?= $cCstUser?> Stats</title>
      <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    
      <link rel="stylesheet" href="Estilos/hde.css" type="text/css">
    
    </head>
    
    <body bgcolor="#FFFFFF" text="#000000" leftmargin="0" topmargin="10" marginwidth="0" marginheight="0">
    <table width="95%" border="1" cellspacing="0" cellpadding="1" align="center" bordercolor="#FFFFFF" class="gralTabla">
      <tr>
        <td width="100%" colspan="5" align="center" bgcolor="#DC9137">
          <span class="gralNormal"><b>Username: <i><?= $cCstUser?></i> - Status: <i><?= $cCstStatus?></i></b></span>
        </td>
      </tr>
      <?
      $aLogStatus[1]["Cond"] = "1 DAY" ;
      $aLogStatus[1]["Text"] = "24 hours" ;
      $aLogStatus[2]["Cond"] = "2 DAY" ;
      $aLogStatus[2]["Text"] = "48 hours" ;
      $aLogStatus[3]["Cond"] = "7 DAY" ;
      $aLogStatus[3]["Text"] = "week" ;
      $aLogStatus[4]["Cond"] = "1 MONTH" ;
      $aLogStatus[4]["Text"] = "month" ;

      for ($nIndice=1; $nIndice<=4; $nIndice++) { ?>
        <tr>
          <td colspan="5" height="5"></td>
        </tr>
        <tr height="25">
          <td colspan="5" bgcolor="#929292">
            &nbsp;<b>Stats for last <?= $aLogStatus[$nIndice]["Text"]?></b>&nbsp;
          </td>
        </tr>
        <tr height="25">
          <td width="49%" colspan="2" bgcolor="#929292" align="center">
            Login Attempts
          </td>
          <td width="51%" colspan="3" bgcolor="#929292" align="center">
            From Different
          </td>
        </tr>
        <tr height="25">
          <td width="32%" bgcolor="#929292" align="center">
            Status
          </td>
          <td width="17%" bgcolor="#929292" align="center">
            Quantity
          </td>
          <td width="17%" bgcolor="#929292" align="center">
            Countries
          </td>
          <td width="17%" bgcolor="#929292" align="center">
            IPs
          </td>
          <td width="17%" bgcolor="#929292" align="center">
            Browsers
          </td>
        </tr><?
        $cSql = "SELECT "
              . "  CASE LogStatus "
              . "    WHEN 'Ok' THEN '1' "
              . "    WHEN 'Error' THEN '2' "
              . "    WHEN 'DeniedUser' THEN '3' "
              . "    WHEN 'DeniedForm' THEN '4' "
              . "    WHEN 'DeniedIP' THEN '5' "
              . "    ELSE '6' END AS RegOrden,"
              . "  CASE LogStatus"
              . "    WHEN 'Ok' THEN 'Successfully' "
              . "    WHEN 'Error' THEN 'Error' "
              . "    WHEN 'DeniedUser' THEN 'User Blocked' "
              . "    WHEN 'DeniedForm' THEN 'Form Error' "
              . "    WHEN 'DeniedIP' THEN 'IP Blocked' "
              . "    ELSE 'Other' END AS UsrStatus,"
              . "  LogStatus, "
              . "  COUNT(*) AS CntAccesos, "
              . "  COUNT(DISTINCT LogCC) AS DifCC, "
              . "  COUNT(DISTINCT LogIP) AS DifIP, "
              . "  COUNT(DISTINCT LogBrowser) AS DifBrowser "
              . "FROM Logins "
              . "WHERE CstUser='".$cCstUser."' "
              . "  AND LogTime > NOW() - INTERVAL ".$aLogStatus[$nIndice]["Cond"]." "
              . "GROUP BY LogStatus "
              . "ORDER BY RegOrden";
        $nResultado = mysql_query ($cSql) or fErrorSQL($conf["EstadoSitio"], "<br /><br /><b>Error en la consulta:</b><br />" . $cSql . "<br /><br /><b>Tipo de error:</b><br />" . mysql_error() . "<br />");
        
        if (mysql_num_rows($nResultado) ) {
          while ($aRegistro=mysql_fetch_array($nResultado)) { ?>
            <tr height="25">
              <td bgcolor="#D3D3D3" align="right">
                <?= $aRegistro["UsrStatus"]?>:
              </td>
              <td bgcolor="#D3D3D3" align="right">
                <?= $aRegistro["CntAccesos"]?>
              </td>
              <td bgcolor="#D3D3D3" align="right">
                <?= $aRegistro["DifCC"]?>
              </td>
              <td bgcolor="#D3D3D3" align="right">
                <?= $aRegistro["DifIP"]?>
              </td>
              <td bgcolor="#D3D3D3" align="right">
                <?= $aRegistro["DifBrowser"]?>
              </td>
            </tr><?
          }
        } else { ?>
          <tr height="25">
            <td bgcolor="#D3D3D3" colspan="5" align="center">
              No data for last <?= $aLogStatus[$nIndice]["Text"]?>
            </td>
          </tr><?
        }
        mysql_free_result ($nResultado); 
      } ?>
    </table>
    </body>
    </html>
