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
if ($_SESSION["gbl".$conf["VariablesSESSION"]."Alias"]=="" || $_REQUEST["Accion"]!="Modificar") {
  header ("Location: Index.php");
  exit(0);
}

$cModulo = $_POST["Modulo"] ;

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
if ($cnfPerEditar!='S' and $_REQUEST["Accion"]=="Modificar") {
  header ("Location: Index.php");
  exit(0);
}


// Tabla a actualizar
$cSql = "SELECT * FROM sysFrom WHERE ModNombre='" . $cModulo . "'";
$nResultado = mysql_query ($cSql) or fErrorSQL($conf["EstadoSitio"], "<br /><br /><b>Error en la consulta:</b><br />" . $cSql . "<br /><br /><b>Tipo de error:</b><br />" . mysql_error() . "<br />");
$aRegistro = mysql_fetch_array($nResultado);
$cTabla = $aRegistro["QryFrom"]."_Lng";
mysql_free_result ($nResultado);


$cCodigoPHP = "" ;


  // Listado de loa campos que viene por POST
  $cSql = "SHOW COLUMNS FROM ".$cTabla;
  $nResultado = mysql_query ($cSql) or fErrorSQL($conf["EstadoSitio"], "<br /><br /><b>Error en la consulta:</b><br />" . $cSql . "<br /><br /><b>Tipo de error:</b><br />" . mysql_error() . "<br />");
  while ($aRegistro = mysql_fetch_array($nResultado)) {
    $cFilterFlds .= "'".$aRegistro[0] . "', ";
  }
  mysql_free_result ($nResultado);

  $cFilterFlds = "AND CpoNombre IN (".substr_replace($cFilterFlds, '', -2, 1).")";


  // Armado de la matriz de Campos y Valores
  $cSql = "SELECT "
        . "  CpoNombre, CpoEtiqueta, CpoTipo, CpoOpciones, CpoDependencias, CpoMaesEscl "
        . "FROM sysCambios "
        . "WHERE ModNombre='" . $cModulo . "' AND CpoTipo<>'2L'  AND CpoTipo<>'hr' ".$cFilterFlds." ORDER BY CpoOrdenPpal, CpoOrdenSec" ;
  $nResultado = mysql_query ($cSql) or fErrorSQL($conf["EstadoSitio"], "<br /><br /><b>Error en la consulta:</b><br />" . $cSql . "<br /><br /><b>Tipo de error:</b><br />" . mysql_error() . "<br />");
  $nIndiceCps = 0 ;
  while ($aRegistro = mysql_fetch_array($nResultado)) {
    $aCampos[$nIndiceCps]["Campo"] = $aRegistro["CpoNombre"];
    if ($aRegistro["CpoTipo"]=="S") {
      $aCampos[$nIndiceCps]["Valor"] = fPonerBarras($_SESSION[$aRegistro["CpoNombre"]]) ;
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

        $cNuevoArch = $cDirArchivos . "/" . $_FILES[$aRegistro["CpoNombre"]]['name'];
        $cCodigoPHP .= "move_uploaded_file(\"" . $_FILES[$aRegistro["CpoNombre"]]['tmp_name'] . "\", \"" . $cNuevoArch . "\"); \n" ;
        $cCodigoPHP .= "chmod(\"" . $cNuevoArch . "\", 0755 );  // Octal \n" ;

        $aCampos[$nIndiceCps]["Valor"] = $_FILES[$aRegistro["CpoNombre"]]['name'] ;
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

    $nIndiceCps++;

    if ($nIndiceCps==1) {
      // Agrego la Partícula del Idioma
      $aCampos[$nIndiceCps]["Campo"] = "LanParticle";
      $aCampos[$nIndiceCps]["Valor"] = $_POST["Part"];
      $nIndiceCps++;
    }

  }
  mysql_free_result ($nResultado);


// Si llegó hasta acá no hay información duplicada
//    entonces ejecutamos Código PHP "retenido"
eval($cCodigoPHP);


// ... y me fijo si hay que "Modificar" o "Agregar"
$cSql = "SELECT * FROM ".$cTabla." WHERE ".$aCampos[0]["Campo"]."='".$aCampos[0]["Valor"]."' AND LanParticle='".$_POST["Part"]."' LIMIT 0,1";
$nResultado = mysql_query ($cSql) or fErrorSQL($conf["EstadoSitio"], "<br /><br /><b>Error en la consulta:</b><br />" . $cSql . "<br /><br /><b>Tipo de error:</b><br />" . mysql_error() . "<br />");

if ($aRegistro = mysql_fetch_array($nResultado))
  $cAccion = "Modificar" ;
else
  $cAccion = "Agregar" ;


//    y luego agregamos efectivamente el registro
$cSql = fModiDataSp($cTabla, $cAccion, $aCampos) ;
mysql_query ($cSql) or fErrorSQL($conf["EstadoSitio"], "<br /><br /><b>Error en la consulta:</b><br />" . $cSql . "<br /><br /><b>Tipo de error:</b><br />" . mysql_error() . "<br />");

echo($cSql);


echo("<script language=\"JavaScript\">") ;
echo("  window.close();") ;
echo("</script>") ;



//******************************************************************************
//* Función: fModiDataSp                                                       *
//* -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-= *
//*                                                                            *
function fModiDataSp($cTabla, $cQue, $aCampos) {

  if ( $cQue=="Agregar" ) {
    $cInstrSql = "INSERT INTO " . $cTabla . " (" ;
    for ($nElem=0; $nElem<count($aCampos); $nElem++) {
       if ( $nElem!=0 ) {
          $cInstrSql .= ", " ;
       }
       $cInstrSql .= $aCampos[$nElem]["Campo"] ;
    }
    $cInstrSql .= ") VALUES ('" ;
    for ($nElem=0; $nElem<count($aCampos); $nElem++) {
      if ( $nElem!=0 ) {
        $cInstrSql .= "', '" ;
      }
      $cInstrSql .= $aCampos[$nElem]["Valor"] ;
    }
    $cInstrSql .= "')" ;

  } else if ( $cQue=="Modificar" ) {
    $cInstrSql = "UPDATE " . $cTabla . " SET " ;
    for ($nElem=2; $nElem<count($aCampos); $nElem++) {
      if ( $nElem!=2 ) {
        $cInstrSql .= ", " ;
      }
      $cInstrSql .= $aCampos[$nElem]["Campo"] . "='" . $aCampos[$nElem]["Valor"] . "'" ;
    }
    $cInstrSql .= " WHERE " . $aCampos[0]["Campo"] . "='" . $aCampos[0]["Valor"] . "' AND " . $aCampos[1]["Campo"] . "='" . $aCampos[1]["Valor"] . "'" ;

  }

  return $cInstrSql ;

} ?>