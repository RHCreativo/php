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

if ($_REQUEST["Accion"]=="Agregar" or $_REQUEST["Accion"]=="Modificar")
  $cModulo = $_POST["Modulo"] ;
else 
  $cModulo = $_SESSION["gbl".$conf["VariablesSESSION"]."Modulo"] ;

// Determina los permisos necesarios para las diferentes acciones
$cSql = "SELECT ModTexto, PerEditar, PerAgregar, PerBorrar FROM sysModulos LEFT JOIN sysModUsu ON sysModulos.ModNombre=sysModUsu.ModNombre WHERE sysModulos.ModNombre='" . $cModulo . "' AND sysModUsu.UsuAlias='" . $_SESSION["gbl".$conf["VariablesSESSION"]."Alias"] . "'";
$nResultado = mysql_query ($cSql) or fErrorSQL($conf["EstadoSitio"], "<br /><br /><b>Error en la consulta:</b><br />" . $cSql . "<br /><br /><b>Tipo de error:</b><br />" . mysql_error() . "<br />");
$aRegistro  = mysql_fetch_array($nResultado);

$cnfModNombre = $aRegistro["ModTexto"];

$cnfPerAgregar = $aRegistro["PerAgregar"];
$cnfPerEditar  = $aRegistro["PerEditar"];
$cnfPerBorrar  = $aRegistro["PerBorrar"];

mysql_free_result ($nResultado);


// Control de Permisos
if (($cnfPerBorrar!='S' and $_REQUEST["Accion"]=="Borrar") or ($cnfPerAgregar!='S' and $_REQUEST["Accion"]=="Agregar")  or ($cnfPerEditar!='S' and $_REQUEST["Accion"]=="Modificar") ) {
  header ("Location: Index.php");
  exit(0);
}


// Tabla a actualizar
$cSql = "SELECT * FROM sysFrom WHERE ModNombre='" . $cModulo . "'";
$nResultado = mysql_query ($cSql) or fErrorSQL($conf["EstadoSitio"], "<br /><br /><b>Error en la consulta:</b><br />" . $cSql . "<br /><br /><b>Tipo de error:</b><br />" . mysql_error() . "<br />");
$aRegistro = mysql_fetch_array($nResultado);
$cTabla = $aRegistro["QryFrom"];
mysql_free_result ($nResultado);


// Para verificar que no haya campos definidos como 
//   UNIQUE con valores duplicados cuando el Usuario
//   intenta grabar un registro
$cIndicesUniq = "N" ;
if ( $_REQUEST["Accion"]!="Borrar" ) {
  $cSql = "SHOW INDEX FROM " . $cTabla ;
  $nResultado = mysql_query ($cSql) or fErrorSQL($conf["EstadoSitio"], "<br /><br /><b>Error en la consulta:</b><br />" . $cSql . "<br /><br /><b>Tipo de error:</b><br />" . mysql_error() . "<br />");
  while ($aRegistro = mysql_fetch_array($nResultado)) {
    if ($aRegistro["Non_unique"]==0 && $aRegistro["Key_name"]!="PRIMARY") {
      $aIndices[$aRegistro["Key_name"]] .= "#" . $aRegistro["Column_name"] . "='@v@" . $aRegistro["Column_name"] . "@v@'#" ;
      $aMensaje[$aRegistro["Key_name"]] .= "#@e@" . $aRegistro["Column_name"] . "@e@#" ;
      $cIndicesUniq = "S" ;
    }
  }
  mysql_free_result ($nResultado);
}

if ($cIndicesUniq=="S") {
  foreach( $aIndices as $cClave => $cValor ) {
    $aIndices[$cClave] = str_replace("#", "", str_replace("##", " AND ", $cValor)) ;
    $cMsjEtiqRegDupl  .= str_replace("##", " ".strtolower($txt['Y'])." ", $aMensaje[$cClave]) ;
  }
  $cMsjEtiqRegDupl = "(" . str_replace("#", "", str_replace("##", ") ".strtolower($txt['O'])." (", $cMsjEtiqRegDupl)) . ")" ;
}


$cCodigoPHP = "" ;
$cCodigoCoo = "" ;


if ( $_SESSION["gbl".$conf["VariablesSESSION"]."Tipo"]=="I" and $_REQUEST["Accion"]=="Borrar" ) {

  // Para eliminar el archivo de imagen juntamente con el registro de la tabla Imagenes
  $cSql = "SELECT CONCAT(IF(ArcTipo<>'',CONCAT(ArcTipo,'/'),''),ArcDirectorio,'/',ArcNombre) AS ccArchivo FROM Archivos WHERE ArcCodigo=" . $_GET["Codigo"] ;
  $nResultado = mysql_query ($cSql) or fErrorSQL($conf["EstadoSitio"], "<br /><br /><b>Error en la consulta:</b><br />" . $cSql . "<br /><br /><b>Tipo de error:</b><br />" . mysql_error() . "<br />");
  $aRegistro = mysql_fetch_array($nResultado) ;

  if (is_file("../Upload/" . $aRegistro["ccArchivo"])) {
    unlink("../Upload/" . $aRegistro["ccArchivo"]) ;
  }

  mysql_free_result ($nResultado);

  $aCampos[0]["Campo"] = "ArcCodigo";
  $aCampos[0]["Valor"] = $_GET["Codigo"] ;

} else {

  // Armado de la matriz de Campos y Valores
  $cSql = "SELECT "
        . "  CpoNombre, CpoEtiqueta, CpoTipo, CpoOpciones, CpoDependencias, CpoMaesEscl "
        . "FROM sysCambios "
        . "WHERE ModNombre='" . $cModulo . "'" . ($_REQUEST["Accion"]=="Borrar"?" AND CpoOrdenPpal=0":" AND CpoTipo<>'2L' ORDER BY CpoOrdenPpal, CpoOrdenSec") ;
  $nResultado = mysql_query ($cSql) or fErrorSQL($conf["EstadoSitio"], "<br /><br /><b>Error en la consulta:</b><br />" . $cSql . "<br /><br /><b>Tipo de error:</b><br />" . mysql_error() . "<br />");
  $nIndiceCps = 0 ;
  while ($aRegistro = mysql_fetch_array($nResultado)) {
    $aCampos[$nIndiceCps]["Campo"] = $aRegistro["CpoNombre"];
    if ($_REQUEST["Accion"]=="Borrar") {
      $aCampos[$nIndiceCps]["Valor"] = $_GET["Codigo"] ;
    } elseif ($aRegistro["CpoTipo"]=="S") {
      $aCampos[$nIndiceCps]["Valor"] = fPonerBarras($_SESSION[$aRegistro["CpoNombre"]]) ;
    } elseif ($aRegistro["CpoTipo"]=="P") {
      $aCampos[$nIndiceCps]["Valor"] = md5($_POST[$aRegistro["CpoNombre"]]);
    } elseif ($aRegistro["CpoTipo"]=="H") {
      $aCampos[$nIndiceCps]["Valor"] = str_replace("<A href=", "<A class=\"cssAdmLink\" href=", $_POST[$aRegistro["CpoNombre"]]) ;
    } elseif ($aRegistro["CpoTipo"]=="F") {
      $aCampos[$nIndiceCps]["Valor"] = $_POST[$aRegistro["CpoNombre"]."A"] . "-" . $_POST[$aRegistro["CpoNombre"]."M"] . "-" . $_POST[$aRegistro["CpoNombre"]."D"] ;
    } elseif ($aRegistro["CpoTipo"]=="U") {
      // Valor inicial del campo
      $aCampos[$nIndiceCps]["Valor"] = $_POST[$aRegistro["CpoNombre"]."Act"] ;

      // Blanquear el campo
      if ($_POST[$aRegistro["CpoNombre"]."Rem"]=="Si") {
        $aCampos[$nIndiceCps]["Valor"] = "" ;
      }

      // Blanquear el campo y eliminar el archivo
      if ($_POST[$aRegistro["CpoNombre"]."Del"]=="Si") {
        $aCampos[$nIndiceCps]["Valor"] = "" ;
        if (is_file("../Upload/Directos/" . ($aRegistro["CpoOpciones"]==""?"":$aRegistro["CpoOpciones"]."/") . $_POST[$aRegistro["CpoNombre"]."Act"])) {
          $cCodigoPHP .= "unlink(\"../Upload/Directos/" . ($aRegistro["CpoOpciones"]==""?"":$aRegistro["CpoOpciones"]."/") . $_POST[$aRegistro["CpoNombre"]."Act"] . "\"); \n" ;
        }
      }

      // Upload del archivo nuevo
      if (trim($_FILES[$aRegistro["CpoNombre"]]['name'])!="") {
        $cDirArchivos = $conf['DirUpload'] . "Directos" . ($aRegistro["CpoOpciones"]==""?"":"/".$aRegistro["CpoOpciones"]) ;

        // Controla los permisos del directorio
        if (substr(decoct(fileperms($cDirArchivos)),-3)!="777") {
          $cCodigoPHP .= "chmod(" . $cDirArchivos . ", 0777 );  // Octal \n" ;
        }

        $prefijo = substr(md5(uniqid(rand())),0,6);
		$cNuevoArch = $cDirArchivos . "/" . $prefijo . $_FILES[$aRegistro["CpoNombre"]]['name'];
		//$cNuevoArch = $cDirArchivos . "/" . $_FILES[$aRegistro["CpoNombre"]]['name'];
        
		$cCodigoPHP .= "move_uploaded_file(\"" . $_FILES[$aRegistro["CpoNombre"]]['tmp_name'] . "\", \"" . $cNuevoArch . "\"); \n" ;
        $cCodigoPHP .= "chmod(\"" . $cNuevoArch . "\", 0755 );  // Octal \n" ;

        $aCampos[$nIndiceCps]["Valor"] = $prefijo . $_FILES[$aRegistro["CpoNombre"]]['name'] ;
		//$aCampos[$nIndiceCps]["Valor"] = $_FILES[$aRegistro["CpoNombre"]]['name'] ;
      }
    } elseif ($aRegistro["CpoTipo"]=="LA") {
      if ($_POST[$aRegistro["CpoNombre"]]!="x") {
        $aCampos[$nIndiceCps]["Valor"] = fPonerBarras($_POST[$aRegistro["CpoNombre"]]) ;
      } else {
        $cCampos   = "" ;
        $cValores  = "" ;
        $aOpciones = explode ("\r\n", $aRegistro["CpoDependencias"]);
        for ($i=0; $i<count($aOpciones); $i++) {
          $aOpcNueReg  = explode (":::", $aOpciones[$i]);
          $cCampos    .= $aOpcNueReg[0] . ", ";
          if ($aOpcNueReg[1]=="?") {
            $cValores .= "'" . fPonerBarras($_POST[$aOpcNueReg[0]]) . "', " ;
          } else {
            $cValores .= "'" . $aOpcNueReg[1] . "', ";
          }

          if ($i==0 and $cValores=="'', ") {
            break ;
          }
        }
        $cCampos  = substr_replace($cCampos, '', -2);
        $cValores = substr_replace($cValores, '', -2);

        if ($cValores=="''") {
          $aCampos[$nIndiceCps]["Valor"] = "'0'" ;
        } else {
          // Agrega el registro nuevo
          $cCodigoPHP .= "\$cSql = \"INSERT INTO " . $aRegistro["CpoMaesEscl"] . " (" . $cCampos . ") VALUES (" . $cValores . ")\" ; \n" ;

          $cCodigoPHP .= "\$nResultOpc = mysql_query ($cSql) or die(\"Error en la consulta: \" . \$cSql . \" Tipo de error: \" . mysql_error()) ; \n" ;

          // Busca el código del último registro ingresado
          $cCodigoPHP .= "\$aCampos[" . $nIndiceCps . "][\"Valor\"] = mysql_insert_id() ; \n" ;
        }
      }
    } else {
      $aCampos[$nIndiceCps]["Valor"] = fPonerBarras($_POST[$aRegistro["CpoNombre"]]) ;
    }


    // Agregado de los valores y etiquetas para verificar campos UNIQUE
    if ($cIndicesUniq=="S") {
      foreach( $aIndices as $cClave => $cValor ) {
        $aIndices[$cClave] = str_replace("@v@".$aCampos[$nIndiceCps]["Campo"]."@v@", $aCampos[$nIndiceCps]["Valor"], $cValor) ;
      }
      $cMsjEtiqRegDupl = str_replace("@e@".$aCampos[$nIndiceCps]["Campo"]."@e@", $aRegistro["CpoEtiqueta"], $cMsjEtiqRegDupl) ;
    }


    $nIndiceCps++;
  }
  mysql_free_result ($nResultado);

  /*
  Si el usuario eligió "BORRAR" el registro me fijo si ademas hay que eliminar archivos de imagenes U
  if ($_REQUEST["Accion"]=="Borrar") {
    // Busco todos los campos Imagenes U del módulo
    $cSql = "SELECT * FROM sysCambios WHERE ModNombre='" . $cModulo . "' AND CpoTipo='U'" ;
    $nResultado = mysql_query ($cSql) or fErrorSQL($conf["EstadoSitio"], "<br /><br /><b>Error en la consulta:</b><br />" . $cSql . "<br /><br /><b>Tipo de error:</b><br />" . mysql_error() . "<br />");
    while ($aRegistro = mysql_fetch_array($nResultado)) {
      $aCpoImagen[] = $aRegistro["CpoNombre"]
    }
    // Para cada campo U me fijo si el archivo se usa en otro registro
  }
  */

  // Si el usuario eligió "BORRAR" el registro me fijo si ademas hay que 
  //    eliminar archivos de Idiomas adicionales ( table Tabla_Lng )
  if ($_REQUEST["Accion"]=="Borrar") {
    $cSql = "SHOW TABLES LIKE '".$cTabla."_Lng'";
    $nResultado = mysql_query ($cSql) or fErrorSQL($conf["EstadoSitio"], "<br /><br /><b>Error en la consulta:</b><br />" . $cSql . "<br /><br /><b>Tipo de error:</b><br />" . mysql_error() . "<br />");
    if ($aRegistro = mysql_fetch_array($nResultado)) {
      $cSql = "DELETE FROM ".$cTabla."_Lng WHERE ".$aCampos[0]["Campo"]."='".$aCampos[0]["Valor"]."'";
      mysql_query ($cSql) or fErrorSQL($conf["EstadoSitio"], "<br /><br /><b>Error en la consulta:</b><br />" . $cSql . "<br /><br /><b>Tipo de error:</b><br />" . mysql_error() . "<br />");
    }
    mysql_free_result ($nResultado);
  }

}


// Arnmado de la SELECT para verificar campos UNIQUE
if ($cIndicesUniq=="S") {
  $cSql = "SELECT COUNT(*) AS ccCantidad FROM " . $cTabla . " WHERE " ;
  foreach( $aIndices as $cClave => $cValor ) {
    $cCondFil .= "#@xXx@#" . $cValor . "#@xXx@#" ;
  }
  if ( $_REQUEST["Accion"]=="Modificar" ) {
    $cSql .= $aCampos[0]["Campo"] . "<>'" . $aCampos[0]["Valor"] . "' AND " ;
  }
  $cSql .= "(" . str_replace("#@xXx@#", "", str_replace("#@xXx@##@xXx@#", ") OR (", $cCondFil)) . ")" ;
  $nResultado = mysql_query ($cSql) or fErrorSQL($conf["EstadoSitio"], "<br /><br /><b>Error en la consulta:</b><br />" . $cSql . "<br /><br /><b>Tipo de error:</b><br />" . mysql_error() . "<br />");

  $aRegistro = mysql_fetch_array($nResultado) ;
  if ($aRegistro["ccCantidad"]==0) {
    $cIndicesUniq = "N" ;
  }
  mysql_free_result ($nResultado);
}


if ($cIndicesUniq=="S") {
  // Clave UNIQUE duplicada ?>
  <html>
  <head>
    <title></title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">

    <script language="JavaScript" type="text/javascript">
      self.resizeTo(400,250);
      self.moveTo((screen.width-400)/2, (screen.height-250)/2);
    </script>

    <link rel="stylesheet" href="Estilos/hde.css" type="text/css">

    <style type="text/css">
    <!--
      a {  font-family: Verdana, Arial; font-size: 11px; font-style: normal; font-weight: bold; color: #FFFFFF; TEXT-DECORATION: none}
    -->
    </style>
  </head>
  <body bgcolor="#FFFFFF" text="#000000" style="margin:0;">
  <table width="100%" border="0" cellspacing="0" cellpadding="5" height="100%" class="gralTabla">
    <tr>
      <td height="90%" align="center" valign="middle"><b><?= str_replace('#', $cMsjEtiqRegDupl, $txt['InfoDuplicada'])?></b></td>
    </tr>
    <tr>
      <td height="30" bgcolor="#929292" align="right"><a href="javascript:window.close()"><?= $txt['CerrarVen']?>&nbsp;&nbsp;<img src="Imagenes/botCerrar.gif" width="9" height="9" border="0" alt=""></a>&nbsp;&nbsp;</td>
    </tr>
  </table>
  </body>
  </html><?
  exit;

} 


// Si llegó hasta acá no hay información duplicada
//    entonces ejecutamos Código PHP "retenido"
eval($cCodigoPHP);

//    y luego agregamos efectivamente el registro
$cSql = fModiData($cTabla, $_REQUEST["Accion"], $aCampos) ;

$nResultado = mysql_query ($cSql) or fErrorSQL($conf["EstadoSitio"], "<br /><br /><b>Error en la consulta:</b><br />" . $cSql . "<br /><br /><b>Tipo de error:</b><br />" . mysql_error() . "<br />");


// Busca el código del último registro ingresado / modificado
//  para poder actualizar (ABM) tablas vinculadas con 2L
$nCodigo = ($_REQUEST["Accion"]=="Agregar"?mysql_insert_id():$aCampos[0]["Valor"]) ;

// Armado de la matriz de Campos y Valores
//  para poder actualizar (ABM) tablas vinculadas con 2L
$cSql = "SELECT * FROM sysCambios WHERE ModNombre='" . $cModulo . "' AND (CpoOrdenPpal=0 OR CpoTipo='2L') ORDER BY CpoOrdenPpal, CpoOrdenSec" ;
$nResultado = mysql_query ($cSql) or fErrorSQL($conf["EstadoSitio"], "<br /><br /><b>Error en la consulta:</b><br />" . $cSql . "<br /><br /><b>Tipo de error:</b><br />" . mysql_error() . "<br />");

// Me fijo si existen campos 2L
if (mysql_num_rows($nResultado)>1) {

  $aRegistro = mysql_fetch_array($nResultado) ;
  $cCampoRel = $aRegistro["CpoNombre"];

  while ($aRegistro = mysql_fetch_array($nResultado)) {

    // Elimino los registros anteriores
    if ($_REQUEST["Accion"]=="Borrar" or $_REQUEST["Accion"]=="Modificar") {
      $cSql = "DELETE FROM " . $aRegistro["CpoMaesEscl"] . " WHERE " . $cCampoRel . "=" . $nCodigo ;
      $nResultRel = mysql_query ($cSql) or fErrorSQL($conf["EstadoSitio"], "<br /><br /><b>Error en la consulta:</b><br />" . $cSql . "<br /><br /><b>Tipo de error:</b><br />" . mysql_error() . "<br />");
    }

    // Agrego los registros actuales
    if (($_REQUEST["Accion"]=="Modificar" OR $_REQUEST["Accion"]=="Agregar") && $_POST[$aRegistro["CpoNombre"]]) {
      $aCodigos = explode (",", $_POST[$aRegistro["CpoNombre"]]);

      for ($i=0; $i<count($aCodigos); $i++) {
        $cSql = "INSERT INTO " . $aRegistro["CpoMaesEscl"] . " (" . $aRegistro["CpoNombre"] . ", " . $cCampoRel . ") VALUES(" . $aCodigos[$i] . ", " . $nCodigo . ")" ;
        $nResultRel = mysql_query ($cSql) or fErrorSQL($conf["EstadoSitio"], "<br /><br /><b>Error en la consulta:</b><br />" . $cSql . "<br /><br /><b>Tipo de error:</b><br />" . mysql_error() . "<br />");
      }
    }

  }
}

include("AfterABM.inc.php");

echo("<script language=\"JavaScript\">") ;
if ( $_REQUEST["Accion"]=="Borrar" ) {
  echo("  location.href='Info.php?Inicio=" . $_GET["Inicio"] . "&Orden=" . $_GET["Orden"] . "&Forma=" . $_GET["Forma"] . "&CpoFiltro1=" . $_GET["CpoFiltro1"] . "&TipFiltro1=" . $_GET["TipFiltro1"] . "&TxtFiltro1=" . fSacarBarras($_GET["TxtFiltro1"]) . "&NexFiltro=" . $_GET["NexFiltro"] . "&CpoFiltro2=" . $_GET["CpoFiltro2"] . "&TipFiltro2=" . $_GET["TipFiltro2"] . "&TxtFiltro2=" . fSacarBarras($_GET["TxtFiltro2"]) . "&Inicio=" . $_GET["Inicio"] . "&Cantidad=" . $_GET["Cantidad"] . "';") ;
} else {
  echo("  window.close();") ;
}
echo("</script>") ;

?>