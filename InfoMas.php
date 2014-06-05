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

$cnfModulo = $_GET["Modulo"] ;


// Determina los permisos necesarios para las diferentes acciones
$cSql = "SELECT PerVer FROM sysModulos LEFT JOIN sysModUsu ON sysModulos.ModNombre=sysModUsu.ModNombre WHERE sysModulos.ModNombre='" . $cnfModulo . "' AND sysModUsu.UsuAlias='" . $_SESSION["gbl".$conf["VariablesSESSION"]."Alias"] . "'";
$nResultado = mysql_query ($cSql) or fErrorSQL($conf["EstadoSitio"], "<br /><br /><b>Error en la consulta:</b><br />" . $cSql . "<br /><br /><b>Tipo de error:</b><br />" . mysql_error() . "<br />");
$aRegistro  = mysql_fetch_array($nResultado);

$cnfPerVer      = $aRegistro["PerVer"];

mysql_free_result ($nResultado);


// Control de Permisos
if ($cnfPerVer!='S') {
  header ("Location: Index.php");
  exit(0);
}


// Armado de la SELECT
$cSql = "SELECT * FROM sysMasInfo WHERE ModNombre='" . $cnfModulo . "' ORDER BY MInPosicion";
$nResultado = mysql_query ($cSql) or fErrorSQL($conf["EstadoSitio"], "<br /><br /><b>Error en la consulta:</b><br />" . $cSql . "<br /><br /><b>Tipo de error:</b><br />" . mysql_error() . "<br />");
$cnfConsulta = "SELECT ";
$nIndiceCps  = 0;
while ($aRegistro = mysql_fetch_array($nResultado)) {
   if ($aRegistro["MInPosicion"]==0) {
      $cnfCodigo = $aRegistro["MInCampo"];
   } else {
      if ($aRegistro["MInCampoAlias"]=='') {
         $cnfConsulta .= $aRegistro["MInCampo"] . ", ";
      } else {
         $cnfConsulta .= $aRegistro["MInCampo"] . " AS " . $aRegistro["MInCampoAlias"] . ", ";
      }
     // Array con los distintos Campos, Posicion e Imagenes de cada uno
     $aCampo[$nIndiceCps]["Camp"] = $aRegistro["MInCampoNombre"];
     $aCampo[$nIndiceCps]["Posi"] = $aRegistro["MInEtiqPosicion"];
     $aCampo[$nIndiceCps]["Imag"] = $aRegistro["MInCampoImagen"];

     if ($aCampo[$nIndiceCps]["Imag"]=="U" && strstr($aCampo[$nIndiceCps]["Camp"],"[")) {
       $aCampo[$nIndiceCps]["SubD"] = trim(str_replace("]", "", str_replace("[", "", strstr($aCampo[$nIndiceCps]["Camp"], "[")))) ;
       $aCampo[$nIndiceCps]["Camp"] = trim(str_replace(strstr($aCampo[$nIndiceCps]["Camp"], "["), "", $aCampo[$nIndiceCps]["Camp"])) ;
     } else {
       $aCampo[$nIndiceCps]["SubD"] = "" ;
     }

     $nIndiceCps++;
   }
}
mysql_free_result ($nResultado);

$cnfConsulta = substr_replace($cnfConsulta, '', -2, 1);


// Cláusula FROM dentro de la SELECT
$cSql = "SELECT * FROM sysFrom WHERE ModNombre='" . $cnfModulo . "'";
$nResultado = mysql_query ($cSql) or fErrorSQL($conf["EstadoSitio"], "<br /><br /><b>Error en la consulta:</b><br />" . $cSql . "<br /><br /><b>Tipo de error:</b><br />" . mysql_error() . "<br />");
$aRegistro = mysql_fetch_array($nResultado);
$cnfConsulta .= "FROM ";
if ($aRegistro["QryFromAlias"]=='') {
   $cnfConsulta .= $aRegistro["QryFrom"] . " ";
} else {
   $cnfConsulta .= $aRegistro["QryFrom"] . " AS " . $aRegistro["QryFromAlias"] . " ";
}
mysql_free_result ($nResultado);


// Cláusula JOIN dentro de la SELECT
$cSql = "SELECT * FROM sysJoin WHERE ModNombre='" . $cnfModulo . "' AND QryJoinUso IN ('M','A')";
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


// Cláusula WHERE dentro de la SELECT
$cnfConsulta .= "WHERE " . $cnfCodigo . "=" . $_GET["Codigo"];


// Arma la instrucción SQL y luego la ejecuta
$cSql = $cnfConsulta;
$nResultado = mysql_query ($cSql) or fErrorSQL($conf["EstadoSitio"], "<br /><br /><b>Error en la consulta:</b><br />" . $cSql . "<br /><br /><b>Tipo de error:</b><br />" . mysql_error() . "<br />");
$aRegistro  = mysql_fetch_row($nResultado);

$nCntCampos = count($aRegistro) ;

for ($nIndiceCps=0; $nIndiceCps<$nCntCampos; $nIndiceCps++) {
   $aCampo[$nIndiceCps]["Cont"] = $aRegistro[$nIndiceCps];
   $aCampo[$nIndiceCps]["Tipo"] = mysql_field_type($nResultado,$nIndiceCps);
}

mysql_free_result ($nResultado);

?>

<html>
<head>
  <title></title>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">

  <link rel="stylesheet" href="Estilos/hde.css" type="text/css">

  <script language="JavaScript">
    parent.document.title = '<?= $aCampo[0]["Cont"]?>'
  </script>

</head>

<script language="JavaScript" src="Funciones/Funciones.js"></script>

<body bgcolor="#FFFFFF" text="#000000" leftmargin="0" topmargin="10" marginwidth="0" marginheight="0">

<table width="95%" border="1" cellspacing="0" cellpadding="1" align="center" bordercolor="#FFFFFF" class="gralTabla">
  <tr>
    <td colspan="2" align="center" bgcolor="#DC9137">
      <span class="gralNormal"><b><?= $txt['DatosAdic']?></b></span>
    </td>
  </tr>
  <? for ($nIndiceCps=1; $nIndiceCps<$nCntCampos; $nIndiceCps++) {

       if ($aCampo[$nIndiceCps]["Posi"]=='I') { ?>
         <tr height="25">
           <td bgcolor="#929292" width="25%" align="right">
             <b><?= $aCampo[$nIndiceCps]["Camp"]?>:</b>&nbsp;
           </td>
           <td bgcolor="#D3D3D3" width="75%" align="left">
             <?
             if ($aCampo[$nIndiceCps]["Cont"]!="") { 
               if ($aCampo[$nIndiceCps]["Imag"]=="S") {
                 echo(substr_count($aCampo[$nIndiceCps]["Cont"],"/")==1?$aCampo[$nIndiceCps]["Cont"]:substr(strstr($aCampo[$nIndiceCps]["Cont"],"/"),1)); 
                 unset($aPropImg);  unset($cInfoImg);
                 if (is_file("../Upload/" . $aCampo[$nIndiceCps]["Cont"])) {
                   $aPropImg = getimagesize("../Upload/" . $aCampo[$nIndiceCps]["Cont"]) ;
                   $cInfoImg = $aPropImg[0]."x".$aPropImg[1] . " - " . filesize($conf['DirUpload'] . $aCampo[$nIndiceCps]["Cont"]) . " bytes" ;
                   ?>&nbsp;<a href="javascript:Abrir('VerImagen.php?Imagen=<?= $aCampo[$nIndiceCps]["Cont"]?>&Info=<?= $cInfoImg?>&Ancho=<?= $aPropImg[0]?>&Alto=<?= $aPropImg[1]?>','Imagen',<?= $aPropImg[0]?>,<?= $aPropImg[1]?>)"><img src="Imagenes/imgIconoDatos.gif" width="10" height="11" border="0" align="absmiddle" title="<?= $txt['VerImagen']?>"></a><?
                 } else {
                   //echo("Imagen no disponible")
                 }
               } elseif ($aCampo[$nIndiceCps]["Imag"]=="U") {
                 echo ($aCampo[$nIndiceCps]["Cont"]); 
                 unset($aPropImg);  unset($cInfoArc);
                 $cFileName = "../Upload/Directos/" . ($aCampo[$nIndiceCps]["SubD"]==""?"":$aCampo[$nIndiceCps]["SubD"]."/") . $aCampo[$nIndiceCps]["Cont"];
                 $cFileExte = strtolower(end(explode(".", $aCampo[$nIndiceCps]["Cont"])));
                 if (is_file($cFileName)) {
                   $aImageExtAllowed = array('jpg', 'jpeg', 'png', 'gif','bmp');
                   if (in_array($cFileExte, $aImageExtAllowed)) {
                     $aPropImg = getimagesize($cFileName) ;
                     $cInfoArc = $aPropImg[0]."x".$aPropImg[1] . " - " . filesize($conf['DirUpload'] . "Directos/" . ($aCampo[$nIndiceCps]["SubD"]==""?"":$aCampo[$nIndiceCps]["SubD"]."/") . $aCampo[$nIndiceCps]["Cont"]) . " bytes" ;
                     ?>&nbsp;&nbsp;<a href="javascript:Abrir('VerImagen.php?Imagen=Directos/<?=  ($aCampo[$nIndiceCps]["SubD"]==""?"":$aCampo[$nIndiceCps]["SubD"]."/") . $aCampo[$nIndiceCps]["Cont"]?>&Info=<?= $cInfoArc?>&Ancho=<?= $aPropImg[0]?>&Alto=<?= $aPropImg[1]?>','Imagen',<?= $aPropImg[0]?>,<?= $aPropImg[1]?>)"><img src="Imagenes/imgIconoDatos.gif" width="10" height="11" border="0" align="absmiddle" title="<?= $txt['VerImagen']?>"></a><?
                   } else {
                     ?>&nbsp;&nbsp;<a href="Download.php?file=<?= $cFileName?>"><img src="Imagenes/imgIconoDatos.gif" width="10" height="11" border="0" align="absmiddle" title="<?= $txt['VerArchivo']?>"></a><?
                   }
                 } else {
                   //echo("Imagen no disponible") ;
                 }
               } elseif ($aCampo[$nIndiceCps]["Imag"]=="A") {
                 echo(substr_count($aCampo[$nIndiceCps]["Cont"],"/")==1?$aCampo[$nIndiceCps]["Cont"]:substr(strstr($aCampo[$nIndiceCps]["Cont"],"/"),1)); 
                 if (is_file("../Upload/" . $aCampo[$nIndiceCps]["Cont"])) {
                   ?>&nbsp;&nbsp;<a href="javascript:Abrir('../Upload/<?= $aCampo[$nIndiceCps]["Cont"]?>','Documento')"><img src="Imagenes/imgIconoDatos.gif" width="10" height="11" border="0" align="absmiddle" title="<?= $txt['VerDocumento']?>"></a><?
                 } else {
                   //echo("Archivo no disponible") ;
                 }
               } else {
                 echo(strstr("#int#",$aCampo[$nIndiceCps]["Tipo"])?number_format($aCampo[$nIndiceCps]["Cont"]):(strstr("#real#",$aCampo[$nIndiceCps]["Tipo"])?number_format($aCampo[$nIndiceCps]["Cont"],2):nl2br(htmlspecialchars($aCampo[$nIndiceCps]["Cont"])))) ;
               }
             } ?>
           </td>
         </tr> <?
       } else { ?>
         <tr bgcolor="#929292" height="25">
           <td width="100%" colspan="2" align="center">
             <b><?= $aCampo[$nIndiceCps]["Camp"]?></b>
           </td>
         </tr>
         <tr bgcolor="#D3D3D3" height="25">
           <td width="100%" colspan="2" align="left">
             <?= nl2br(htmlspecialchars($aCampo[$nIndiceCps]["Cont"]))?>
           </td>
         </tr> <?
       }
     } ?>
</table>

</body>
</html>