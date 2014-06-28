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
* 
2L	= Doble lista multiselección
A	= Archivo
B	= CheckBox
C	= Color
E	= Email
F	= Fecha
H	= HTML
L	= Lista Desplegable
LA	= 
LE	= (lista esclavo)
LM	= (lista maestro)
M	= Memo
N	= Numérico
RH	= Radio Horizontal
RV	= Radio Vertical
S	= Separador (solo va _pref+Codigo y pone un separador)
T 	= texto
TR	= Textos Relacion    ------>  NO EN USO, TOMAR DE LA FAZENDA
U 	= Upload
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
$cSql = "SELECT ModTexto, PerEditar, PerAgregar FROM sysModulos LEFT JOIN sysModUsu ON sysModulos.ModNombre=sysModUsu.ModNombre WHERE sysModulos.ModNombre='" . $_SESSION["gbl".$conf["VariablesSESSION"]."Modulo"] . "' AND sysModUsu.UsuAlias='" . $_SESSION["gbl".$conf["VariablesSESSION"]."Alias"] . "'";
$nResultado = mysql_query ($cSql) or fErrorSQL($conf["EstadoSitio"], "<br /><br /><b>Error en la consulta:</b><br />" . $cSql . "<br /><br /><b>Tipo de error:</b><br />" . mysql_error() . "<br />");
$aRegistro  = mysql_fetch_array($nResultado);

$cnfModNombre   = $aRegistro["ModTexto"];

$cnfPerAgregar  = $aRegistro["PerAgregar"];
$cnfPerEditar   = $aRegistro["PerEditar"];

mysql_free_result ($nResultado);


// Control de Permisos
if ($cnfPerAgregar!='S' and $cnfPerEditar!='S') {
  header ("Location: Index.php");
  exit(0);
}

$cFormAction = "ABM.php" ;

if ( $_GET["Codigo"]!="0" ) {

  $cBotonSubmit = $txt['Modificar'];
  $cAccion      = "Modificar";

  // Tabla de la SELECT
  $cSql = "SELECT * FROM sysFrom WHERE ModNombre='" . $_SESSION["gbl".$conf["VariablesSESSION"]."Modulo"] . "'";
  $nResultado = mysql_query ($cSql) or fErrorSQL($conf["EstadoSitio"], "<br /><br /><b>Error en la consulta:</b><br />" . $cSql . "<br /><br /><b>Tipo de error:</b><br />" . mysql_error() . "<br />");
  $aRegistro = mysql_fetch_array($nResultado);
  $cTblFrom  = $aRegistro["QryFrom"] ;
  $cTblAlias = $aRegistro["QryFromAlias"] ;
  mysql_free_result ($nResultado);


  // Armado de la SELECT
  $cnfConsulta = "SELECT ";     $cFilterLang = "" ;     $cFilterFlds = "" ;
  if ($_GET["Lang"]=="Si") {
    $cFormAction = "ABMLang.php" ;
    // Existe la tabla de IDIOMAS alternativos
    $cTblFrom  = $aRegistro["QryFrom"]."_Lng" ;
    $cTblAlias = "" ;
    // hay filtro adicional
    $cFilterLang = " AND LanParticle='".$_GET["Part"]."'" ;
    // busco los campos habilitados
    $cSql = "SHOW COLUMNS FROM ".$cTblFrom;
    $nResultado = mysql_query ($cSql) or fErrorSQL($conf["EstadoSitio"], "<br /><br /><b>Error en la consulta:</b><br />" . $cSql . "<br /><br /><b>Tipo de error:</b><br />" . mysql_error() . "<br />");
    $nRegAct = 0 ;
    while ($aRegistro = mysql_fetch_array($nResultado)) {
      if ($nRegAct==0) {
        $cnfCodigo = $aRegistro[0];
      } elseif ($nRegAct>1) {
        $cnfConsulta .= $aRegistro[0] . ", ";
        $cFilterFlds .= "'".$aRegistro[0] . "', ";
      }
      $nRegAct++;
    }
    mysql_free_result ($nResultado);

    $cFilterFlds = "AND CpoNombre IN (".substr_replace($cFilterFlds, '', -2, 1).")";

  } else {
    $cSql = "SELECT * FROM sysCambios WHERE ModNombre='" . $_SESSION["gbl".$conf["VariablesSESSION"]."Modulo"] . "' AND CpoTipo NOT IN ('O', '2L') ORDER BY CpoOrdenPpal, CpoOrdenSec";
    $nResultado = mysql_query ($cSql) or fErrorSQL($conf["EstadoSitio"], "<br /><br /><b>Error en la consulta:</b><br />" . $cSql . "<br /><br /><b>Tipo de error:</b><br />" . mysql_error() . "<br />");
    while ($aRegistro = mysql_fetch_array($nResultado)) {
      if ($aRegistro["CpoOrdenPpal"]==0) {
        $cnfCodigo = $aRegistro["CpoNombre"];
      } else {
        $cnfConsulta .= $aRegistro["CpoNombre"] . ", ";
      }
    }
    mysql_free_result ($nResultado);
  }

  $cnfConsulta = substr_replace($cnfConsulta, '', -2, 1);

  // Cláusula FROM dentro de la SELECT
  $cnfConsulta .= "FROM ";
  if ($cTblAlias=='') {
    $cnfConsulta .= $cTblFrom . " ";
  } else {
    $cnfConsulta .= $cTblFrom . " AS " . $cTblAlias . " ";
  }

  // Cláusula WHERE dentro de la SELECT
  $cnfConsulta .= "WHERE " . $cnfCodigo . "='".$_GET["Codigo"]."'".$cFilterLang;

  // Arma la instrucción SQL y luego la ejecuta
  $cSql = $cnfConsulta;
  $nResultado = mysql_query ($cSql) or fErrorSQL($conf["EstadoSitio"], "<br /><br /><b>Error en la consulta:</b><br />" . $cSql . "<br /><br /><b>Tipo de error:</b><br />" . mysql_error() . "<br />");
  $aRegistro  = mysql_fetch_row($nResultado);

  $nCntCampos = count($aRegistro) ;

  // Array con los distintos Campos y Valores de cada uno
  for ($nIndiceCps=0; $nIndiceCps<$nCntCampos; $nIndiceCps++) {
    $aCampo[mysql_field_name($nResultado,$nIndiceCps)] = $aRegistro[$nIndiceCps] ;
  }

  mysql_free_result ($nResultado);

} else {

  $cBotonSubmit = $txt['Grabar'];
  $cAccion      = "Agregar";

}

$cCpoReq = "N" ;

$cHTML       = "";
$cJScript    = "";
$cJScriptDin = "";
$cJSCpoHTML  = "";
$cJSMaesEscl = "";

$cFormEnctype = "";

$nAltoVentana = 50+40+45+25; // 50px IE Título + 40px Título Cabecera + 45px Botones Fondo + 25px IE Barra de Estado

$nOrdenPpal     = 0;
$nCntCamposHTML = 0;

// Arma la instrucción SQL y luego la ejecuta
$cSql = "SELECT * FROM sysCambios WHERE ModNombre='" . $_SESSION["gbl".$conf["VariablesSESSION"]."Modulo"] . "' AND CpoOrdenPpal<>0  AND CpoTipo<>'O' ".$cFilterFlds." ORDER BY CpoOrdenPpal, CpoOrdenSec" ;

$nResultado = mysql_query ($cSql) or fErrorSQL($conf["EstadoSitio"], "<br /><br /><b>Error en la consulta:</b><br />" . $cSql . "<br /><br /><b>Tipo de error:</b><br />" . mysql_error() . "<br />");

// Construcción del Formulario.
while($aRegistro = mysql_fetch_array($nResultado)) {

  if ($nOrdenPpal==$aRegistro["CpoOrdenPpal"]) {
    $cFilaNueva  = "No";
    $cHTML       = substr_replace ($cHTML, '', -17);
  } else {
    $cFilaNueva  = "Si";
    $nOrdenPpal  = $aRegistro["CpoOrdenPpal"] ;
  }
  $cColorFte = "#000000" ;

  // Escribimos la etiqueta
  if ($cFilaNueva=="Si") {
    $cHTML .= "\t <tr height=\"25\"> \n";
    $cHTML .= "\t\t <td width=\"180\" bgcolor=\"#929292\" align=\"right\" valign=\"top\"> \n";
  } else {
    $cHTML .= "&nbsp;";
  }
  // Controlamos si el campo es obligatorio
  if ($aRegistro["CpoRequerido"]=="S") {
    $cCpoReq = "S" ;

    $cColorFte = $conf["ColorCamposObligatorios"] ;

    if ($aRegistro["CpoTipo"]=="T" or $aRegistro["CpoTipo"]=="N" or $aRegistro["CpoTipo"]=="M" or $aRegistro["CpoTipo"]=="E") {
      $cJScript .= "\t\t if(elForm." . $aRegistro["CpoNombre"] . ".value == \"\") { \n" ;
      $cJScript .= "\t\t\t alert(\"" . str_replace("#",$aRegistro["CpoEtiqueta"],$txt['MsgFrmCpoOblig']) . "\"); \n" ;
      $cJScript .= "\t\t\t elForm." . $aRegistro["CpoNombre"] . ".focus(); \n" ;
      $cJScript .= "\t\t\t return false; \n" ;
      $cJScript .= "\t\t } \n" ;
    } elseif ($aRegistro["CpoTipo"]=="U") {
      $cJScript .= "\t\t if(elForm." . $aRegistro["CpoNombre"] . ".value == \"\" && (elForm." . $aRegistro["CpoNombre"] . "Act.value == \"\" || elForm." . $aRegistro["CpoNombre"] . "Rem.checked || elForm." . $aRegistro["CpoNombre"] . "Del.checked)) { \n" ;
      $cJScript .= "\t\t\t alert(\"" . str_replace("#",$aRegistro["CpoEtiqueta"],$txt['MsgFrmCpoOblig']) . "\"); \n" ;
      $cJScript .= "\t\t\t elForm." . $aRegistro["CpoNombre"] . ".focus(); \n" ;
      $cJScript .= "\t\t\t return false; \n" ;
      $cJScript .= "\t\t } \n" ;
    } elseif ($aRegistro["CpoTipo"]=="RV" or $aRegistro["CpoTipo"]=="RH") {
      $cJScript .= "\t\t if(CheckRadio(elForm." . $aRegistro["CpoNombre"] . ") == false) { \n" ;
      $cJScript .= "\t\t\t alert(\"" . str_replace("#",$aRegistro["CpoEtiqueta"],$txt['MsgFrmCpoOblig']) . "\"); \n" ;
      $cJScript .= "\t\t\t elForm." . $aRegistro["CpoNombre"] . "[0].focus(); \n" ;
      $cJScript .= "\t\t\t return false; \n" ;
      $cJScript .= "\t\t } \n" ;
    } elseif (substr($aRegistro["CpoTipo"],0,1)=="L") {
      $cJScript .= "\t\t if(CheckList(elForm." . $aRegistro["CpoNombre"] . ") == false) { \n" ;
      $cJScript .= "\t\t\t alert(\"" . str_replace("#",$aRegistro["CpoEtiqueta"],$txt['MsgFrmCpoOblig']) . "\"); \n" ;
      $cJScript .= "\t\t\t elForm." . $aRegistro["CpoNombre"] . ".focus(); \n" ;
      $cJScript .= "\t\t\t return false; \n" ;
      $cJScript .= "\t\t } \n" ;
    } elseif ($aRegistro["CpoTipo"]=="F") {
      $cJScript .= "\t\t if ( elForm." . $aRegistro["CpoNombre"] . "D.value==\"\" ) { \n" ;
      $cJScript .= "\t\t\t alert(\"" . str_replace("#",$aRegistro["CpoEtiqueta"],$txt['MsgFrmCpoOblig']) . "\"); \n" ;
      $cJScript .= "\t\t\t elForm." . $aRegistro["CpoNombre"] . "D.focus(); \n" ;
      $cJScript .= "\t\t\t return false; \n" ;
      $cJScript .= "\t\t } \n" ;
      $cJScript .= "\t\t if ( elForm." . $aRegistro["CpoNombre"] . "M.value==\"00\" ) { \n" ;
      $cJScript .= "\t\t\t alert(\"" . str_replace("#",$aRegistro["CpoEtiqueta"],$txt['MsgFrmCpoOblig']) . "\"); \n" ;
      $cJScript .= "\t\t\t elForm." . $aRegistro["CpoNombre"] . "M.focus(); \n" ;
      $cJScript .= "\t\t\t return false; \n" ;
      $cJScript .= "\t\t } \n" ;
      $cJScript .= "\t\t if ( elForm." . $aRegistro["CpoNombre"] . "A.value==\"\" ) { \n" ;
      $cJScript .= "\t\t\t alert(\"" . str_replace("#",$aRegistro["CpoEtiqueta"],$txt['MsgFrmCpoOblig']) . "\"); \n" ;
      $cJScript .= "\t\t\t elForm." . $aRegistro["CpoNombre"] . "A.focus(); \n" ;
      $cJScript .= "\t\t\t return false; \n" ;
      $cJScript .= "\t\t } \n" ;
    } elseif ($aRegistro["CpoTipo"]=="H") {
      // Agregar validacion campo Memo con formato HTML
    }
  }
  if ($aRegistro["CpoJScriptDin"]!="") {
    $cJScriptDin .= $aRegistro["CpoJScriptDin"] . "\n" ;
  }
  if ($aRegistro["CpoJScript"]!="") {
    $cJScript .= $aRegistro["CpoJScript"] . "\n" ;
  }
  if ($cFilaNueva=="Si") {
    if ($aRegistro["CpoToolTip"]) {
      $cHTML .= "\t\t\t <img src=\"Imagenes/icoToolTip.png\" align=\"left\" alt=\"\" title=\"\" onmouseover=\"showhint('" . str_replace("\r\n", "<br />", $aRegistro["CpoToolTip"]) . "', this, event, '200px')\" height=\"16\" width=\"16\"> \n" ;
    }
    $cHTML .= "\t\t\t <b><font color=\"" . $cColorFte . "\">" . $aRegistro["CpoEtiqueta"] . ":<font></b>&nbsp; \n" ;
    $cHTML .= "\t\t </td> \n" ;
    $cHTML .= "\t\t <td width=\"540\" bgcolor=\"#D3D3D3\" valign=\"middle\"> \n" ;
  } else {
    if ($aRegistro["CpoEtiqueta"]!='')
      $cHTML .= "&nbsp;<font color=\"" . $cColorFte . "\">" . $aRegistro["CpoEtiqueta"] . "<font>&nbsp;" ;
  }
  if ($aRegistro["CpoTipo"]=="N") {
    // Campo Numérico
    $nAltoVentana += 30;
    $cHTML .= "\t\t\t <input type=\"text\" name=\"" . $aRegistro["CpoNombre"] . "\" size=\"" . $aRegistro["CpoAnchoVis"] . "\" maxlength=\"" . $aRegistro["CpoAnchoTot"] . "\" value=\"" . (isset($aCampo[$aRegistro["CpoNombre"]])?$aCampo[$aRegistro["CpoNombre"]]:"") . "\" " . $aRegistro["CpoAgregado"] . " onchange=\"setDirtyFlag();\" style=\"text-align: right\"> \n" ;

    $cJScript .= "\t\t if(isNaN(elForm." . $aRegistro["CpoNombre"] . ".value) && elForm." . $aRegistro["CpoNombre"] . ".value != \"\") { \n" ;
    $cJScript .= "\t\t\t alert(\"" . str_replace("#",$aRegistro["CpoEtiqueta"],$txt['MsgFrmCpoNumer']) . "\"); \n" ;
    $cJScript .= "\t\t\t elForm." . $aRegistro["CpoNombre"] . ".focus(); \n" ;
    $cJScript .= "\t\t\t return false; \n" ;
    $cJScript .= "\t\t } \n" ;

    $cJScript .= "\t\t if(elForm." . $aRegistro["CpoNombre"] . ".value > " . $aRegistro["CpoMaximo"] . " && elForm." . $aRegistro["CpoNombre"] . ".value != \"\") { \n" ;
    $cJScript .= "\t\t\t alert(\"" . str_replace(array("#","XX"),array($aRegistro["CpoEtiqueta"],$aRegistro["CpoMaximo"]),$txt['MsgFrmCpoNoMay']) . "\"); \n" ;
    $cJScript .= "\t\t\t elForm." . $aRegistro["CpoNombre"] . ".focus(); \n" ;
    $cJScript .= "\t\t\t return false; \n" ;
    $cJScript .= "\t\t } \n" ;

    $cJScript .= "\t\t if(elForm." . $aRegistro["CpoNombre"] . ".value < " . $aRegistro["CpoMinimo"] . " && elForm." . $aRegistro["CpoNombre"] . ".value != \"\") { \n" ;
    $cJScript .= "\t\t\t alert(\"" . str_replace(array("#","XX"),array($aRegistro["CpoEtiqueta"],$aRegistro["CpoMinimo"]),$txt['MsgFrmCpoNoMen']) . "\"); \n" ;
    $cJScript .= "\t\t\t elForm." . $aRegistro["CpoNombre"] . ".focus(); \n" ;
    $cJScript .= "\t\t\t return false; \n" ;
    $cJScript .= "\t\t } \n" ;

  } elseif ($aRegistro["CpoTipo"]=="M") {
    // Campo Memo
    $nAltoVentana += (20*$aRegistro["CpoAlto"]);
    $cHTML .= "\t\t\t <textarea rows=\"" . $aRegistro["CpoAlto"] . "\" cols=\"" . $aRegistro["CpoAnchoVis"] . "\" name=\"" . $aRegistro["CpoNombre"] . "\" " . $aRegistro["CpoAgregado"] . " onchange=\"setDirtyFlag();\">" . (isset($aCampo[$aRegistro["CpoNombre"]])?$aCampo[$aRegistro["CpoNombre"]]:"") . "</textarea> \n" ;

  } elseif ($aRegistro["CpoTipo"]=="A") {
    // Campo Archivos
    $nAltoVentana += 30;
    $cHTML .= "\t\t\t <select name=\"" . $aRegistro["CpoNombre"] . "\" size=\"" . $aRegistro["CpoAlto"] . "\" " . $aRegistro["CpoAgregado"] . " onchange=\"setDirtyFlag();\"> \n" ;
    $cHTML .= "\t\t\t <option value=\"\"></option> \n" ;

    // Encuentra las imagenes del directorio
    if ($Directorio = opendir($conf['DirArchivos'] . $aRegistro["CpoOpciones"] . "/")) {
       while (false !== ($Archivo = readdir($Directorio))) {
          if ($Archivo != "." && $Archivo != "..") {
             $aArchivos[] = $Archivo;
          }
       }
       closedir($Directorio);
       sort($aArchivos);
    }
    reset($aArchivos);
    while (list($nPosicion, $cValor) = each($aArchivos)) {
       $cHTML .= "\t\t\t <option value=\"" . $cValor . "\"" . ($cValor==(isset($aCampo[$aRegistro["CpoNombre"]])?$aCampo[$aRegistro["CpoNombre"]]:"")?" selected":"") . ">" . $cValor . "</option> \n" ;
    }
    unset($aArchivos);
    $cHTML .= "\t\t\t </select> \n" ;

  } elseif ($aRegistro["CpoTipo"]=="H") {
    // Campo Memo en formato HTML
    $nAltoVentana += (10+$aRegistro["CpoAlto"]);
    $cHTML .= "\t\t\t <script language=\"javascript\" type=\"text/javascript\"> \n" ;
    $cHTML .= "\t\t\t\t <!-- \n" ;
    $cHTML .= "\t\t\t\t var oFCKeditor = new FCKeditor( '" . $aRegistro["CpoNombre"] . "' ) ; \n" ;
    $cHTML .= "\t\t\t\t oFCKeditor.BasePath = '".$conf['SubDir']."/Admin/FCKeditor/' ; \n" ;
    $cHTML .= "\t\t\t\t oFCKeditor.Config[ 'SkinPath' ] = '".$conf['SubDir']."/Admin/FCKeditor/editor/skins/default/' ;  \n" ;
    $cHTML .= "\t\t\t\t oFCKeditor.Config[ 'AutoDetectLanguage' ] = false ; \n" ;
    $cHTML .= "\t\t\t\t oFCKeditor.Config[ 'DefaultLanguage' ] = 'es' ; \n" ;
    $cHTML .= "\t\t\t\t oFCKeditor.Config[ 'LinkBrowserURL' ] = '".$conf['SubDir']."/Admin/FCKeditor/editor/filemanager/browser/default/browser.html?Connector=connectors/php/connector.php' ; \n" ;
    $cHTML .= "\t\t\t\t oFCKeditor.Config[ 'ImageBrowserURL' ] = '".$conf['SubDir']."/Admin/FCKeditor/editor/filemanager/browser/default/browser.html?Type=Image&Connector=connectors/php/connector.php' ; \n" ;
    $cHTML .= "\t\t\t\t oFCKeditor.Width = " . $aRegistro["CpoAnchoVis"] . " ; \n" ;
    $cHTML .= "\t\t\t\t oFCKeditor.Height = " . $aRegistro["CpoAlto"] . " ; \n" ;
    $cHTML .= "\t\t\t\t oFCKeditor.Value = '" . (isset($aCampo[$aRegistro["CpoNombre"]])?AddSlashes(str_replace(chr(13).chr(10),"",$aCampo[$aRegistro["CpoNombre"]])):"") . "' ; \n" ;
    $cHTML .= "\t\t\t\t oFCKeditor.Create() ; \n" ;
    $cHTML .= "\t\t\t\t //--> \n" ;
    $cHTML .= "\t\t\t </script> \n" ;

	$cJSCpoHTML .= "<script language=\"javascript\" type=\"text/javascript\" src=\"FCKeditor/fckeditor.js\"></script> \n" ;

  } elseif ($aRegistro["CpoTipo"]=="F") {
    // Campo Fecha
    $nAltoVentana += 30;
    if (!isset($aCampo[$aRegistro["CpoNombre"]]) or $aCampo[$aRegistro["CpoNombre"]]=='0000-00-00') {
      $cDia = "" ;
      $cMes = "" ;
      $cAno = "" ;
    } else {
      $cDia = substr($aCampo[$aRegistro["CpoNombre"]],8,2) ;
      $cMes = substr($aCampo[$aRegistro["CpoNombre"]],5,2) ;
      $cAno = substr($aCampo[$aRegistro["CpoNombre"]],0,4) ;
    }

    $cHTMLDia  = "\t\t\t <input type=\"text\" name=\"" . $aRegistro["CpoNombre"] . "D\" size=\"2\" maxlength=\"2\" value=\"" . $cDia . "\" onchange=\"setDirtyFlag();\"> \n" ;
    $cHTMLMes  = "\t\t\t <select name=\"" . $aRegistro["CpoNombre"] . "M\" size=\"1\" onchange=\"setDirtyFlag();\"> \n" ;
    $cHTMLMes .= "\t\t\t\t <option value=\"00\"></option> \n" ;
    $cHTMLMes .= "\t\t\t\t <option value=\"01\"" . ("01"==$cMes?" selected":"") . ">" . $txt["Enero"] . "</option> \n" ;
    $cHTMLMes .= "\t\t\t\t <option value=\"02\"" . ("02"==$cMes?" selected":"") . ">" . $txt["Febrero"] . "</option> \n" ;
    $cHTMLMes .= "\t\t\t\t <option value=\"03\"" . ("03"==$cMes?" selected":"") . ">" . $txt["Marzo"] . "</option> \n" ;
    $cHTMLMes .= "\t\t\t\t <option value=\"04\"" . ("04"==$cMes?" selected":"") . ">" . $txt["Abril"] . "</option> \n" ;
    $cHTMLMes .= "\t\t\t\t <option value=\"05\"" . ("05"==$cMes?" selected":"") . ">" . $txt["Mayo"] . "</option> \n" ;
    $cHTMLMes .= "\t\t\t\t <option value=\"06\"" . ("06"==$cMes?" selected":"") . ">" . $txt["Junio"] . "</option> \n" ;
    $cHTMLMes .= "\t\t\t\t <option value=\"07\"" . ("07"==$cMes?" selected":"") . ">" . $txt["Julio"] . "</option> \n" ;
    $cHTMLMes .= "\t\t\t\t <option value=\"08\"" . ("08"==$cMes?" selected":"") . ">" . $txt["Agosto"] . "</option> \n" ;
    $cHTMLMes .= "\t\t\t\t <option value=\"09\"" . ("09"==$cMes?" selected":"") . ">" . $txt["Setiembre"] . "</option> \n" ;
    $cHTMLMes .= "\t\t\t\t <option value=\"10\"" . ("10"==$cMes?" selected":"") . ">" . $txt["Octubre"] . "</option> \n" ;
    $cHTMLMes .= "\t\t\t\t <option value=\"11\"" . ("11"==$cMes?" selected":"") . ">" . $txt["Noviembre"] . "</option> \n" ;
    $cHTMLMes .= "\t\t\t\t <option value=\"12\"" . ("12"==$cMes?" selected":"") . ">" . $txt["Diciembre"] . "</option> \n" ;
    $cHTMLMes .= "\t\t\t </select> \n" ;
    $cHTMLAno  = "\t\t\t <input type=\"text\" name=\"" . $aRegistro["CpoNombre"] . "A\" size=\"4\" maxlength=\"4\" value=\"" . $cAno . "\" onchange=\"setDirtyFlag();\"> \n" ;

    $cHTML .= str_replace(array("##D##", "##M##", "##Y##"), array($cHTMLDia, $cHTMLMes, $cHTMLAno), str_replace(array("D", "M", "Y"), array("##D##", "##M##", "##Y##"), $conf["Fecha"])) ;

    $cJScript .= "\t\t if ( elForm." . $aRegistro["CpoNombre"] . "D.value!=\"\" || elForm." . $aRegistro["CpoNombre"] . "M.value!=\"00\" || elForm." . $aRegistro["CpoNombre"] . "A.value!=\"\" ) { \n" ;
    $cJScript .= "\t\t\t if ( isNaN(elForm." . $aRegistro["CpoNombre"] . "D.value) || elForm." . $aRegistro["CpoNombre"] . "D.value>31 || elForm." . $aRegistro["CpoNombre"] . "D.value<1 ) { \n" ;
    $cJScript .= "\t\t\t\t alert(\"" . str_replace("#",$aRegistro["CpoEtiqueta"],$txt['MsgFrmCpoNoVal']) . "\"); \n" ;
    $cJScript .= "\t\t\t\t elForm." . $aRegistro["CpoNombre"] . "D.focus(); \n" ;
    $cJScript .= "\t\t\t\t return false; \n" ;
    $cJScript .= "\t\t\t } \n" ;
    $cJScript .= "\t\t\t if ( elForm." . $aRegistro["CpoNombre"] . "D.value.length==1 ) { \n" ;
    $cJScript .= "\t\t\t\t elForm." . $aRegistro["CpoNombre"] . "D.value = \"0\"+elForm." . $aRegistro["CpoNombre"] . "D.value; \n" ;
    $cJScript .= "\t\t\t } \n" ;
    $cJScript .= "\t\t\t if ( elForm." . $aRegistro["CpoNombre"] . "A.value.length < 4 || isNaN(elForm." . $aRegistro["CpoNombre"] . "A.value) ) { \n" ;
    $cJScript .= "\t\t\t\t alert(\"" . str_replace("#",$aRegistro["CpoEtiqueta"],$txt['MsgFrmCpoNoVal']) . "\"); \n" ;
    $cJScript .= "\t\t\t\t elForm." . $aRegistro["CpoNombre"] . "A.focus(); \n" ;
    $cJScript .= "\t\t\t\t return false; \n" ;
    $cJScript .= "\t\t\t } \n" ;
    $cJScript .= "\t\t\t if ( ( elForm." . $aRegistro["CpoNombre"] . "D.value==31 ) && ( ( elForm." . $aRegistro["CpoNombre"] . "M.value==\"11\" ) || ( elForm." . $aRegistro["CpoNombre"] . "M.value==\"04\" ) || ( elForm." . $aRegistro["CpoNombre"] . "M.value==\"06\" ) || ( elForm." . $aRegistro["CpoNombre"] . "M.value==\"09\" ) ) ) { \n" ;
    $cJScript .= "\t\t\t\t alert(\"" . str_replace("#",$aRegistro["CpoEtiqueta"],$txt['MsgFrmCpoNoVal']) . "\"); \n" ;
    $cJScript .= "\t\t\t\t elForm." . $aRegistro["CpoNombre"] . "D.focus(); \n" ;
    $cJScript .= "\t\t\t\t return false; \n" ;
    $cJScript .= "\t\t\t } \n" ;
    $cJScript .= "\t\t\t if ( (elForm." . $aRegistro["CpoNombre"] . "M.value==\"02\") && ( ( ( elForm." . $aRegistro["CpoNombre"] . "A.value%4!=0 ) && ( elForm." . $aRegistro["CpoNombre"] . "D.value>28 ) ) || ( ( elForm." . $aRegistro["CpoNombre"] . "A.value%4==0 ) && ( elForm." . $aRegistro["CpoNombre"] . "D.value>29 ) ) ) ) { \n" ;
    $cJScript .= "\t\t\t\t alert(\"" . str_replace("#",$aRegistro["CpoEtiqueta"],$txt['MsgFrmCpoNoVal']) . "\"); \n" ;
    $cJScript .= "\t\t\t\t elForm." . $aRegistro["CpoNombre"] . "D.focus(); \n" ;
    $cJScript .= "\t\t\t\t return false; \n" ;
    $cJScript .= "\t\t\t } \n" ;
    $cJScript .= "\t\t } \n" ;

  } elseif ($aRegistro["CpoTipo"]=="B") {
    $nAltoVentana += 25;
    // Check Box
    $aOpcEtiVal = explode (":::", $aRegistro["CpoOpciones"]) ;
    $cHTML .= "\t\t\t " . $aOpcEtiVal[0] . "<input type=\"checkbox\" name=\"" . $aRegistro["CpoNombre"] . "\" value=\"" . $aOpcEtiVal[1] . "\"" . ($aOpcEtiVal[1]==(isset($aCampo[$aRegistro["CpoNombre"]])?$aCampo[$aRegistro["CpoNombre"]]:"")?" checked":"") . " " . $aRegistro["CpoAgregado"] . " onchange=\"setDirtyFlag();\"> \n" ;

  } elseif ($aRegistro["CpoTipo"]=="RV" or $aRegistro["CpoTipo"]=="RH") {
    // Botones de Radio
    $aOpciones = explode ("\r\n", $aRegistro["CpoOpciones"]);

    for ($i=0; $i<count($aOpciones); $i++) {
      $aOpcEtiVal = explode (":::", $aOpciones[$i]);
      $cHTML .= "\t\t\t <input type=\"radio\" name=\"" . $aRegistro["CpoNombre"] . "\" value=\"" . $aOpcEtiVal[1] . "\"" . ($aOpcEtiVal[1]==(isset($aCampo[$aRegistro["CpoNombre"]])?$aCampo[$aRegistro["CpoNombre"]]:"")?" checked":"") . " " . $aRegistro["CpoAgregado"] . " onchange=\"setDirtyFlag();\">" . $aOpcEtiVal[0] . ($aRegistro["CpoTipo"]=="RH"?"&nbsp;&nbsp;":"<br>") . " \n" ;
    }
    $nAltoVentana += ($aRegistro["CpoTipo"]=="RH"?25:(22*count($aOpciones)));

  } elseif (substr($aRegistro["CpoTipo"],0,1)=="L") {
    // Lista Desplegable
    $nAltoVentana += ($aRegistro["CpoAlto"]==1?30:(20*$aRegistro["CpoAlto"]));
    if ($aRegistro["CpoTipo"]=="LM") {

      $cCpoMaestro = $aRegistro["CpoNombre"];

      $cSeteoMaesEscl = "";
      $aOpciones = explode (":::", $aRegistro["CpoMaesEscl"]);
      for ($i=0; $i<count($aOpciones); $i++) {
        $cSeteoMaesEscl .= "SeteoLista(" . $aRegistro["CpoNombre"] . "," . $aOpciones[$i] . "); setDirtyFlag();" ;
      }
      $cHTML .= "\t\t\t <select name=\"" . $aRegistro["CpoNombre"] . "\" size=\"" . $aRegistro["CpoAlto"] . "\" onChange=\"" . $cSeteoMaesEscl . "\" " . $aRegistro["CpoAgregado"] . "> \n" ;


      $cSql=$aRegistro["CpoDependencias"];

      $nResultOpc = mysql_query ($cSql) or fErrorSQL($conf["EstadoSitio"], "<br /><br /><b>Error en la consulta:</b><br />" . $cSql . "<br /><br /><b>Tipo de error:</b><br />" . mysql_error() . "<br />");

      $nCodMaestroAnt = -1;
      while($aRegistOpc = mysql_fetch_array($nResultOpc)) {
        if ($nCodMaestroAnt!=$aRegistOpc["CodMaestro"]) {
          $nCodMaestroAnt = $aRegistOpc["CodMaestro"];
          $nIndice        = 1;
          $cJSMaesEscl .= "A" . $aRegistOpc["CodMaestro"] . "=new Array(); \n";
          $cJSMaesEscl .= " A" . $aRegistOpc["CodMaestro"] . "[0]=new Array(\"\", 0); \n";
        }
        $cJSMaesEscl .= " A" . $aRegistOpc["CodMaestro"] . "[" . $nIndice . "]=new Array(\"" . $aRegistOpc["DesEsclavo"] . "\", " . $aRegistOpc["CodEsclavo"] . "); \n";
        $nIndice += 1;
      }
      mysql_free_result ($nResultOpc) ;


    } else {
      $cHTML .= "\t\t\t <select name=\"" . $aRegistro["CpoNombre"] . "\" size=\"" . $aRegistro["CpoAlto"] . "\" " . ($aRegistro["CpoTipo"]=="LA"?"onchange=\"if (this.value=='x') {setVisibility('Lyr" . $aRegistro["CpoNombre"] . "', 'visible')} else {setVisibility('Lyr" . $aRegistro["CpoNombre"] . "', 'hidden')}; setDirtyFlag(); return true;\" ":"") . $aRegistro["CpoAgregado"] . "> \n" ;
    }

    if (substr($aRegistro["CpoOpciones"],1,6)=="SELECT") {
      if (substr($aRegistro["CpoOpciones"],0,1)=="+") {
        if ($aRegistro["CpoTipo"]=="LE") {
          $cHTML .= "\t\t\t\t <option value=\"\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</option> \n" ;
        } else {
          $cHTML .= "\t\t\t\t <option value=\"\"></option> \n" ;
        }
      }

      if ($aRegistro["CpoTipo"]=="LA") {
        $cHTML .= "\t\t\t\t <option value=\"x\">-- Otro/a " . $aRegistro["CpoEtiqueta"] . " --</option> \n" ;
      }

      if ($aRegistro["CpoTipo"]!="LE" or (isset($aCampo[$cCpoMaestro]) and $aCampo[$cCpoMaestro]!="")) {

        $cSql     = "" ;
        $cSqlOrig = substr($aRegistro["CpoOpciones"],1) ;

        $nPosIni = strpos($cSqlOrig,"{");
        while ( !($nPosIni === false) ) {
          $nPosFin = strpos($cSqlOrig,"}");
          $nPosSep = strpos($cSqlOrig,"_");

          $cSql .= substr($cSqlOrig, 0, $nPosIni);

          $cQueEs    = "_" . substr($cSqlOrig, $nPosIni+1, $nPosSep-$nPosIni-1);
          $cVariable = substr($cSqlOrig, $nPosSep+1, $nPosFin-$nPosSep-1);

          $cSql .= ${$cQueEs}[$cVariable];

          $cSqlOrig = substr($cSqlOrig, $nPosFin+1);

          $nPosIni = strpos($cSqlOrig,"{");
        }
        $cSql .= str_replace ( "#", "WHERE " . $cCpoMaestro . "=" . (isset($aCampo[$cCpoMaestro])?$aCampo[$cCpoMaestro]:""), $cSqlOrig);

        $nResultOpc = mysql_query ($cSql) or fErrorSQL($conf["EstadoSitio"], "<br /><br /><b>Error en la consulta:</b><br />" . $cSql . "<br /><br /><b>Tipo de error:</b><br />" . mysql_error() . "<br />");

        while($aRegistOpc = mysql_fetch_array($nResultOpc)) {
          $cHTML .= "\t\t\t\t <option value=\"" . $aRegistOpc[0] . "\"" . ($aRegistOpc[0]==(isset($aCampo[$aRegistro["CpoNombre"]])?$aCampo[$aRegistro["CpoNombre"]]:"")?" selected":"") . ">" . $aRegistOpc[1] . "</option> \n" ;
        }
        mysql_free_result ($nResultOpc) ;

      }

    } else {
      $aOpciones = explode ("\r\n", $aRegistro["CpoOpciones"]);
      for ($i=0; $i<count($aOpciones); $i++) {
        $aOpcEtiVal = explode (":::", $aOpciones[$i]);
        $cHTML .= "\t\t\t\t <option value=\"" . $aOpcEtiVal[1] . "\"" . ($aOpcEtiVal[1]==(isset($aCampo[$aRegistro["CpoNombre"]])?$aCampo[$aRegistro["CpoNombre"]]:"")?" selected":"") . ">" . $aOpcEtiVal[0] . "</option> \n" ;
      }
    }

    $cHTML .= "\t\t\t </select> \n" ;

    if ($aRegistro["CpoTipo"]=="LA") {

      $cNueCampos = "";
      $aOpciones  = explode ("\r\n", $aRegistro["CpoDependencias"]);
      for ($i=0; $i<count($aOpciones); $i++) {
        $aOpcNueReg  = explode (":::", $aOpciones[$i]);
        if ($aOpcNueReg[1]=="?") {
          $cNueCampos .= "'" . $aOpcNueReg[0] . "', ";
        }
      }
      $cNueCampos = substr_replace($cNueCampos, '', -2);

      $cSql = "SELECT * FROM sysCambios WHERE ModNombre='" . $aRegistro["CpoMaesEscl"] . "' AND CpoNombre IN (" . $cNueCampos . ") ORDER BY CpoOrdenPpal" ;

      $nResultOpc = mysql_query ($cSql) or fErrorSQL($conf["EstadoSitio"], "<br /><br /><b>Error en la consulta:</b><br />" . $cSql . "<br /><br /><b>Tipo de error:</b><br />" . mysql_error() . "<br />");

      $cJScript .= "\t\t if(elForm." . $aRegistro["CpoNombre"] . ".value == \"x\") { \n" ;

      $cHTML .= "\t\t\t\t <span id=\"Lyr" . $aRegistro["CpoNombre"] . "\" class=\"ocultable\"> \n" ;
      $cHTML .= "\t\t\t\t\t <table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"1\" class=\"gralTabla\"> \n" ;
      while($aRegistOpc = mysql_fetch_array($nResultOpc)) {

        if ($aRegistOpc["CpoJScriptDin"]!="") {
          $cJScriptDin .= $aRegistOpc["CpoJScriptDin"] . "\n" ;
        }

        if ($aRegistOpc["CpoRequerido"]=="S") {
          $cCpoReq = "S" ;

          $cJScript .= "\t\t\t if(elForm." . $aRegistOpc["CpoNombre"] . ".value == \"\") { \n" ;
          $cJScript .= "\t\t\t\t alert(\"" . str_replace("#",$aRegistro["CpoEtiqueta"],$txt['MsgFrmCpoOblig']) . "\"); \n" ;
          $cJScript .= "\t\t\t\t elForm." . $aRegistOpc["CpoNombre"] . ".focus(); \n" ;
          $cJScript .= "\t\t\t\t return false; \n" ;
          $cJScript .= "\t\t\t } \n" ;
        }

        if ($aRegistOpc["CpoJScript"]!="") {
          $cJScript .= $aRegistOpc["CpoJScript"] . "\n" ;
        }

        $cHTML .= "\t\t\t\t\t\t <tr height=\"25\"> \n";
        $cHTML .= "\t\t\t\t\t\t\t <td width=\"25%\" bgcolor=\"#929292\" align=\"right\"> \n";
        $cHTML .= "\t\t\t\t\t\t\t\t <b><font color=\"" . ($aRegistOpc["CpoRequerido"]=="S"?$conf["ColorCamposObligatorios"]:"#000000") . "\">" . $aRegistOpc["CpoEtiqueta"] . ":<font></b>&nbsp; \n" ;
        $cHTML .= "\t\t\t\t\t\t\t </td> \n" ;
        $cHTML .= "\t\t\t\t\t\t\t <td width=\"75%\" bgcolor=\"#D3D3D3\" valign=\"middle\"> \n" ;
        $cHTML .= "\t\t\t\t\t\t\t\t <input type=\"text\" name=\"" . $aRegistOpc["CpoNombre"] . "\" size=\"" . min(40,$aRegistOpc["CpoAnchoVis"]) . "\" maxlength=\"" . $aRegistOpc["CpoAnchoTot"] . "\" value=\"\" " . $aRegistOpc["CpoAgregado"] . "> \n" ;
        $cHTML .= "\t\t\t\t\t\t\t </td> \n" ;
        $cHTML .= "\t\t\t\t\t\t </tr> \n" ;
      }
      $cHTML .= "\t\t\t\t\t </table> \n" ;
      $cHTML .= "\t\t\t\t </span> \n" ;

      $cJScript .= "\t\t } \n" ;

      mysql_free_result ($nResultOpc) ;
    }

  } elseif ($aRegistro["CpoTipo"]=="2L") {
    // Dos Listas (para selección de multiples registros)
    $nAltoVentana += (20+20*$aRegistro["CpoAlto"]);
    $cHTML .= "\t\t\t <table width=\"96%\" border=\"0\" cellspacing=\"0\" cellpadding=\"1\" align=\"center\" class=\"gralTabla\"> \n" ;
    $cHTML .= "\t\t\t\t <tr> \n" ;
    $cHTML .= "\t\t\t\t\t <th>".$txt['TitDisp']."</th> \n" ;
    $cHTML .= "\t\t\t\t\t <th></th> \n" ;
    $cHTML .= "\t\t\t\t\t <th>".$txt['TitSelec']."</th> \n" ;
    $cHTML .= "\t\t\t\t </tr> \n" ;
    $cHTML .= "\t\t\t\t <tr> \n" ;
    $cHTML .= "\t\t\t\t\t <td align=\"center\"> \n" ;
    $cHTML .= "\t\t\t\t\t\t <select name=\"" . $aRegistro["CpoNombre"] . "F\" size=\"" . $aRegistro["CpoAlto"] . "\" multiple style=\"width:" . $aRegistro["CpoAnchoVis"] . "\" onchange=\"setDirtyFlag();\"> \n" ;

    $cSql = str_replace("##Codigo##", $_GET["Codigo"], $aRegistro["CpoOpciones"]) ;
    $nResultOpc = mysql_query ($cSql) or fErrorSQL($conf["EstadoSitio"], "<br /><br /><b>Error en la consulta:</b><br />" . $cSql . "<br /><br /><b>Tipo de error:</b><br />" . mysql_error() . "<br />");

    while($aRegistOpc = mysql_fetch_array($nResultOpc)) {
      $cHTML .= "\t\t\t\t\t\t\t <option value=\"" . $aRegistOpc[0] . "\">" . $aRegistOpc[1] . "</option> \n" ;
    }
    mysql_free_result ($nResultOpc) ;

    $cHTML .= "\t\t\t\t\t\t </select> \n" ;
    $cHTML .= "\t\t\t\t\t </td> \n" ;
    $cHTML .= "\t\t\t\t\t <td align=\"center\"> \n" ;
    $cHTML .= "\t\t\t\t\t\t\t<input type=\"button\" onClick=\"fMoverValor(" . $aRegistro["CpoNombre"] . "F, " . $aRegistro["CpoNombre"] . "D, 'D', 'Todos', " . $aRegistro["CpoNombre"] . ");\" value=\"".$txt['RegTodos']." &gt;&gt;\" style=\"width:100\"><br> \n" ;
    $cHTML .= "\t\t\t\t\t\t\t<input type=\"button\" onClick=\"fMoverValor(" . $aRegistro["CpoNombre"] . "F, " . $aRegistro["CpoNombre"] . "D, 'D', 'Seleccion', " . $aRegistro["CpoNombre"] . ");\" value=\"".$txt['RegSelec']." &gt;&gt;\" style=\"width:100\"><br><br> \n" ;
    $cHTML .= "\t\t\t\t\t\t\t<input type=\"button\" onClick=\"fMoverValor(" . $aRegistro["CpoNombre"] . "D, " . $aRegistro["CpoNombre"] . "F, 'F', 'Seleccion', " . $aRegistro["CpoNombre"] . ");\" value=\"".$txt['RegSelec']." &lt;&lt;\" style=\"width:100\"><br> \n" ;
    $cHTML .= "\t\t\t\t\t\t\t<input type=\"button\" onClick=\"fMoverValor(" . $aRegistro["CpoNombre"] . "D, " . $aRegistro["CpoNombre"] . "F, 'F', 'Todos', " . $aRegistro["CpoNombre"] . ");\" value=\"".$txt['RegTodos']." &lt;&lt;\" style=\"width:100\"> \n" ;
    $cHTML .= "\t\t\t\t\t </td> \n" ;
    $cHTML .= "\t\t\t\t\t <td align=\"center\"> \n" ;
    $cHTML .= "\t\t\t\t\t\t <select name=\"" . $aRegistro["CpoNombre"] . "D\" size=\"" . $aRegistro["CpoAlto"] . "\" multiple style=\"width:" . $aRegistro["CpoAnchoVis"] . "\" onchange=\"setDirtyFlag();\"> \n" ;

    $cSql = str_replace("##Codigo##", $_GET["Codigo"], $aRegistro["CpoDependencias"]) ;
    $nResultOpc = mysql_query ($cSql) or fErrorSQL($conf["EstadoSitio"], "<br /><br /><b>Error en la consulta:</b><br />" . $cSql . "<br /><br /><b>Tipo de error:</b><br />" . mysql_error() . "<br />");

    $cValSelecc = "" ;
    while($aRegistOpc = mysql_fetch_array($nResultOpc)) {
      $cHTML .= "\t\t\t\t\t\t\t <option value=\"" . $aRegistOpc[0] . "\">" . $aRegistOpc[1] . "</option> \n" ;
      $cValSelecc .= ($cValSelecc==""?"":",") . $aRegistOpc[0] ;
    }
    mysql_free_result ($nResultOpc) ;

    $cHTML .= "\t\t\t\t\t\t </select> \n" ;
    $cHTML .= "\t\t\t\t\t\t <input type=\"hidden\" value=\"" . $cValSelecc . "\" name=\"" . $aRegistro["CpoNombre"] . "\"> \n" ;
    $cHTML .= "\t\t\t\t\t </td> \n" ;
    $cHTML .= "\t\t\t\t </tr> \n" ;
    $cHTML .= "\t\t\t </table> \n" ;


  } elseif ($aRegistro["CpoTipo"]=="U") {
    // Campo tipo file para Upload de Archivos
    $nAltoVentana += 30;
    $cValorActual   = (isset($aCampo[$aRegistro["CpoNombre"]])?$aCampo[$aRegistro["CpoNombre"]]:"") ;
    $cSubDirectorio = ($aRegistro["CpoOpciones"]==""?"":$aRegistro["CpoOpciones"]."/") ;

    $cFormEnctype = "enctype=\"multipart/form-data\"" ;

    $cHTML .= "<input type=\"hidden\" name=\"" . $aRegistro["CpoNombre"] . "Act\" value=\"" . $cValorActual . "\"> \n" ;

    $cHTML .= "\t\t\t <table width=\"530\" border=\"0\" cellspacing=\"0\" cellpadding=\"2\" align=\"left\" class=\"gralTabla\"> \n" ;
    if ($cValorActual!="" && is_file("../Upload/Directos/" . $cSubDirectorio . $cValorActual)) {
      $cFileExte = strtolower(end(explode(".", $cValorActual)));
      $aImageExtAllowed = array('jpg', 'jpeg', 'png', 'gif','bmp','swf');
      if (in_array($cFileExte, $aImageExtAllowed)) {
        $aPropImg = getimagesize("../Upload/Directos/" . $cSubDirectorio . $cValorActual) ; 
        $nAltoVentana += 15;
        if ($aPropImg[0]>420) {
          $nAltoVentana += ($aPropImg[1]*(420/$aPropImg[0]));
          $cAnchoAlto = "width=\"420\" height=\"".($aPropImg[1]*(420/$aPropImg[0]))."\"" ;
          $cPorcImage = " ( " . str_replace("#", round((420/$aPropImg[0])*100,0), $txt['Reduccion']) . " )" ;
        } else {
          $nAltoVentana += $aPropImg[1];
          $cAnchoAlto = $aPropImg[3] ;
          $cPorcImage = "" ;
        }
        $cHTML .= "\t\t\t\t <tr> \n" ;
        $cHTML .= "\t\t\t\t\t <td colspan=\"2\">" . $cValorActual . $cPorcImage . "</td> \n" ;
        $cHTML .= "\t\t\t\t </tr> \n" ;
        $cHTML .= "\t\t\t\t <tr> \n" ;
        $cHTML .= "\t\t\t\t\t <td width=\"30\"> \n" ;

        if ($cFileExte=="swf") {
          $cHTML .= "\t\t\t\t\t\t <OBJECT classid=\"clsid:D27CDB6E-AE6D-11cf-96B8-444553540000\" codebase=\"http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=7,0,0,0\" " . $cAnchoAlto . " id=\"" . str_replace(".swf", "", $cValorActual) . "\"> \n" ;
          $cHTML .= "\t\t\t\t\t\t   <PARAM NAME=movie VALUE=\"../Upload/Directos/" . $cSubDirectorio . $cValorActual . "\"> \n" ;
          $cHTML .= "\t\t\t\t\t\t   <PARAM NAME=quality VALUE=high> \n" ;
          $cHTML .= "\t\t\t\t\t\t   <PARAM NAME=bgcolor VALUE=#D3D3D3> \n" ;
          $cHTML .= "\t\t\t\t\t\t   <EMBED src=\"../Upload/Directos/" . $cSubDirectorio . $cValorActual . "\" quality=high bgcolor=#D3D3D3 " . $cAnchoAlto . " NAME=\"" . str_replace(".swf", "", $cValorActual) . "\" ALIGN=\"\" TYPE=\"application/x-shockwave-flash\" PLUGINSPAGE=\"http://www.macromedia.com/go/getflashplayer\"></EMBED> \n" ;
          $cHTML .= "\t\t\t\t\t\t </OBJECT> \n" ;

        } else {
          $cHTML .= "\t\t\t\t\t\t <img src=\"../Upload/Directos/" . $cSubDirectorio . $cValorActual . "\" " . $cAnchoAlto . " border=\"0\"> \n" ;

        }

      } else { 
        $cHTML .= "\t\t\t\t <tr> \n" ;
        $cHTML .= "\t\t\t\t\t <td colspan=\"2\">" . $cValorActual . "</td> \n" ;
        $cHTML .= "\t\t\t\t </tr> \n" ;
        $cHTML .= "\t\t\t\t <tr> \n" ;
        $cHTML .= "\t\t\t\t\t <td width=\"30\" align=\"center\"> \n" ;
        $cHTML .= "\t\t\t\t\t\t <a href=\"Download.php?file=../Upload/Directos/" . $cSubDirectorio . $cValorActual . "\"><img src=\"Imagenes/icoArchivos.gif\" width=\"25\" height=\"32\" border=\"0\" alt=\"\"></a> \n" ;

      }
      $cHTML .= "\t\t\t\t\t </td> \n" ;
      $cHTML .= "\t\t\t\t\t <td width=\"500\" valign=\"top\"> \n" ;
      $cHTML .= "\t\t\t\t\t\t <input type=\"checkbox\" name=\"" . $aRegistro["CpoNombre"] . "Rem\" value=\"Si\" onchange=\"setDirtyFlag();\" >".$txt['Desvincular']."<br /> \n" ;
      $cHTML .= "\t\t\t\t\t\t <input type=\"checkbox\" name=\"" . $aRegistro["CpoNombre"] . "Del\" value=\"Si\" onchange=\"setDirtyFlag();\" >".$txt['Eliminar']."<br /> \n" ;
      $cHTML .= "\t\t\t\t\t </td> \n" ;
      $cHTML .= "\t\t\t\t </tr> \n" ;
    } 
    $cHTML .= "\t\t\t\t <tr> \n" ;
    $cHTML .= "\t\t\t\t\t <td colspan=\"2\"> \n" ;
    $cHTML .= "\t\t\t\t\t\t <input type=\"file\" name=\"" . $aRegistro["CpoNombre"] . "\" size=\"" . $aRegistro["CpoAnchoVis"] . "\" onchange=\"setDirtyFlag();\"> \n" ;
    $cHTML .= "\t\t\t\t\t </td> \n" ;
    $cHTML .= "\t\t\t\t </tr> \n" ;
    $cHTML .= "\t\t\t </table> \n" ;


  } elseif ($aRegistro["CpoTipo"]=="E") {
    // eMail
    $nAltoVentana += 30;
    $cHTML .= "\t\t\t <input type=\"text\" name=\"" . $aRegistro["CpoNombre"] . "\" size=\"" . $aRegistro["CpoAnchoVis"] . "\" maxlength=\"" . $aRegistro["CpoAnchoTot"] . "\" value=\"" . htmlentities((isset($aCampo[$aRegistro["CpoNombre"]])?$aCampo[$aRegistro["CpoNombre"]]:""), ENT_QUOTES, "UTF-8") . "\" " . $aRegistro["CpoAgregado"] . " onchange=\"setDirtyFlag();\"> \n" ;

    $cJScript .= "\t\t if ( elForm." . $aRegistro["CpoNombre"] . ".value!=\"\" ) { \n" ;
    $cJScript .= "\t\t\t if(!elForm." . $aRegistro["CpoNombre"] . ".value.match(/(\\w+[\\w|\\.|-]*\\w+)(@\\w+[\\w|\.|-]*\\w+\\.\\w{2,4})/)) { \n" ;
    $cJScript .= "\t\t\t\t alert(\"" . str_replace("#",$aRegistro["CpoEtiqueta"],$txt['MsgFrmCpoNoVal']) . "\"); \n" ;
    $cJScript .= "\t\t\t\t elForm." . $aRegistro["CpoNombre"] . ".focus(); \n" ;
    $cJScript .= "\t\t\t\t return false; \n" ;
    $cJScript .= "\t\t\t } \n" ;
    $cJScript .= "\t\t } \n" ;
  
  // ** -> FEDE - COLOR
  } elseif ($aRegistro["CpoTipo"]=="C") {
    // eMail
    $nAltoVentana += 80;
    /*$cHTML .= "\t\t\t <input type=\"text\" name=\"" . $aRegistro["CpoNombre"] . "\" size=\"" . $aRegistro["CpoAnchoVis"] . "\" maxlength=\"" . $aRegistro["CpoAnchoTot"] . "\" value=\"" . htmlentities((isset($aCampo[$aRegistro["CpoNombre"]])?$aCampo[$aRegistro["CpoNombre"]]:""), ENT_QUOTES, "UTF-8") . "\" " . $aRegistro["CpoAgregado"] . " onchange=\"setDirtyFlag();\"> \n" ;*/
	
	$cHTML .= "\t\t\t <div id=\"colorpicker201\" class=\"colorpicker201\"></div><input type=\"button\" onclick=\"showColorGrid2('input_field_3','sample_3');\" value=\"Select\">&nbsp;<input type=\"text\" ID=\"input_field_3\" name=\"" . $aRegistro["CpoNombre"] . "\" size=\"" . $aRegistro["CpoAnchoVis"] . "\" maxlength=\"" . $aRegistro["CpoAnchoTot"] . "\" value=\"" . htmlentities((isset($aCampo[$aRegistro["CpoNombre"]])?$aCampo[$aRegistro["CpoNombre"]]:""), ENT_QUOTES, "UTF-8") . "\" " . $aRegistro["CpoAgregado"] . " onchange=\"setDirtyFlag();\">&nbsp;<input type=\"text\" ID=\"sample_3\" size=\"1\" value=\"\" style=\"background-color:" . htmlentities((isset($aCampo[$aRegistro["CpoNombre"]])?$aCampo[$aRegistro["CpoNombre"]]:""), ENT_QUOTES, "UTF-8") . "\"> \n";
	
  // ** -> fin.FEDE - COLOR

  } else {
    // Asume campo de Texto Normal para cualquier otro caso
    $nAltoVentana += 30;
    $cHTML .= "\t\t\t <input type=\"text\" name=\"" . $aRegistro["CpoNombre"] . "\" size=\"" . $aRegistro["CpoAnchoVis"] . "\" maxlength=\"" . $aRegistro["CpoAnchoTot"] . "\" value=\"" . htmlentities((isset($aCampo[$aRegistro["CpoNombre"]])?$aCampo[$aRegistro["CpoNombre"]]:""), ENT_QUOTES, "UTF-8") . "\" " . $aRegistro["CpoAgregado"] . " onchange=\"setDirtyFlag();\"> \n" ;

  }

  $cHTML .= "\t\t </td> \n" ;
  $cHTML .= "\t </tr> \n";

}
mysql_free_result ($nResultado) ;
?>

<html>
<head>
  <title><?= $cnfModNombre ?></title>

  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <meta http-equiv="imagetoolbar" content="no">

  <link rel="stylesheet" href="Estilos/hde.css" type="text/css">

  <style type="text/css">
    .ocultable { position: relative; visibility: hidden; }
  </style>

  <?= $cJSCpoHTML ?>

  <script language="JavaScript" src="Funciones/Funciones.js"></script>
  <script language="JavaScript" src="Funciones/ToolTips.js"></script>
  <!-- fede -->
  <script  src="201a.js" type="text/javascript"></script>
  <!-- ./fede -->
  <? if (is_file("Funciones/Particulares.js")) { ?>
    <script language="JavaScript" src="Funciones/Particulares.js"></script>
  <? } ?>

  <script type="text/javascript" src="Funciones/embeddedcontent.js" defer="defer"></script>

  <script language="JavaScript">

    if ((screen.height-150)><?= $nAltoVentana?>) {
      self.resizeTo(800,<?= $nAltoVentana?>);
    }


    var needToConfirm = false;

    function setDirtyFlag() {
      needToConfirm = true; // Call this function if some changes is made to the web page and requires an alert.
                            // Of-course you could call this is Keypress event of a text box or so...
    }

    function releaseDirtyFlag() {
      needToConfirm = false; // Call this function if dosent requires an alert.
                             // This could be called when save button is clicked
    }

    window.onbeforeunload = confirmExit;
    function confirmExit() {
      if (needToConfirm)
        return "<?= str_replace("#", $cBotonSubmit, $txt['MsgFrmConfCier'])?>";
    }

    <?= $cJSMaesEscl ?>

    // Si se envio el formulario, actualiza la pagina principal
    cActualizar = "No";
    function Actualizar() {
      if (cActualizar=="Si") {
        self.opener.top.Principal.Cuerpo.location.reload()
      }
    }

    <?= eval($cJScriptDin)?>

    // Validacion del Formulario con JavaScript
    function Validar(elForm) {
      <?= $cJScript ?>
      elForm.botGrabar.disabled=true; 
      <? if ($cAccion=="Modificar" and $_SESSION["gbl".$conf["VariablesSESSION"]."Duplic"]=="S" and $cnfPerAgregar=="S" and $_GET["Lang"]!="Si") { ?>
        elForm.botAgregar.disabled=true; 
      <? } ?>
      cActualizar = "Si";
      releaseDirtyFlag();
      return true;
    }

    // Controla que haya sido seleccionado algun valor de un boton de radio
    function CheckRadio(objRadio) {
      for(var n=0; n<objRadio.length; n++) {
        if(objRadio[n].checked) {
          return true;
        }
      }
      return false;
    }

    // Controla que haya sido seleccionado algun elemento de una lista desplegable
    function CheckList(objLista) {
      for(var n=1; n<objLista.length; n++) {
        if(objLista.options[n].selected) {
          return true;
        }
      }
      return false;
    }

    // Para manejar listas dependientes de otras
    function SeteoLista(Maestro,Esclavo) {
      if (Maestro.selectedIndex==0) {
        Esclavo.options.length=0;
        return;
      }
      NuevaLista=eval('A'+Maestro.value);
      Esclavo.options.length=0;
      for(i=0;i<NuevaLista.length;i++) {
        Esclavo.options[i]=new Option(NuevaLista[i][0],NuevaLista[i][1]);
      }
      Esclavo.selectedIndex=0;
    }

    // Para mover valores entre dos listas
    function fMoverValor( ListaFuente, ListaDestino, cActualizarLista, cCuales, Seleccionados ) {

      var ElementosListaFuente  = ListaFuente ;
      var ElementosListaDestino = ListaDestino ;
      var nLargoInicial = ElementosListaDestino.options.length;
      var nFila;

      var aItemsMovidos     = new Array();
      var aCodigoItems      = new Array();
      var aValoresDestino   = new Array();

      // Define qué ítems se van a trasladar
      for ( var i = 0; i < ElementosListaFuente.options.length; i++ ) {
        if( ElementosListaFuente.options[i].selected==true || cCuales=="Todos") {
          aItemsMovidos[aItemsMovidos.length] = i;
        }
      }

      // Mueve los ítems de una a otra lista
      for ( var i = 0; i < aItemsMovidos.length; i++ ) {
        ElementosListaDestino.options[nLargoInicial+i] = new Option( ElementosListaFuente.options[aItemsMovidos[i]-i].text, ElementosListaFuente.options[aItemsMovidos[i]-i].value );
        ElementosListaFuente.options[aItemsMovidos[i]-i] = null;
      }

      // Define los valores y ordena la lista destino (la lista destino se actualiza siempre)
      for ( var i = 0; i < ElementosListaDestino.length; i++ ) {
        aValoresDestino[aValoresDestino.length] = new Array (ElementosListaDestino.options[i].text, ElementosListaDestino.options[i].value);
      }
      aValoresDestino.sort();

      ElementosListaDestino.length = 0;
      for(nFila = 0; nFila < aValoresDestino.length; nFila++) {
        ElementosListaDestino[nFila] = new Option(aValoresDestino[nFila][0], aValoresDestino[nFila][1]);
      }

      // Determina la lista "de la izquierda" y toma sus valores
      if (cActualizarLista=='F') {
        var ElementosSeleccion = ElementosListaFuente ;
      } else {
        var ElementosSeleccion = ElementosListaDestino ;
      }

      for ( var i = 0; i < ElementosSeleccion.length; i++ ) {
        aCodigoItems[aCodigoItems.length] = ElementosSeleccion.options[i].value;
      }
      Seleccionados.value = aCodigoItems.join(",");

    }

  </script>

</head>

<body bgcolor="#FFFFFF" text="#000000" onUnload="javascript:Actualizar()" onLoad="document.forms[0].elements[3].focus();" style="margin:0;">

<form name="Datos" method="post" action="<?= $cFormAction?>" onSubmit="return Validar(this)" <?= $cFormEnctype?> style="margin:0;">

<input type="hidden" name="Accion" value="<?= $cAccion?>">
<input type="hidden" name="Modulo" value="<?= $_SESSION["gbl".$conf["VariablesSESSION"]."Modulo"]?>">
<input type="hidden" name="<?= $cnfCodigo?>" value="<?= $_GET["Codigo"]?>">

<?
$cSql = "SELECT * FROM sysCambios WHERE ModNombre='" . $_SESSION["gbl".$conf["VariablesSESSION"]."Modulo"] . "' AND CpoOrdenPpal<>0 AND CpoTipo='O' ORDER BY CpoOrdenPpal, CpoOrdenSec" ;
$nResultado = mysql_query ($cSql) or fErrorSQL($conf["EstadoSitio"], "<br /><br /><b>Error en la consulta:</b><br />" . $cSql . "<br /><br /><b>Tipo de error:</b><br />" . mysql_error() . "<br />");

while($aRegistro = mysql_fetch_array($nResultado)) { ?>
  <input type="hidden" name="<?= $aRegistro["CpoNombre"]?>" value="<?= $aRegistro["CpoOpciones"]?>" /> <?
}
mysql_free_result ($nResultado) ; 
?>

<table width="95%" border="1" cellspacing="0" cellpadding="1" align="center" bordercolor="#FFFFFF" class="gralTabla">
  <tr height="5">
    <td colspan="2"></td>
  </tr>
  <tr height="25" bgcolor="#DC9137">
    <td colspan="2" align="center" valign="middle">
      <span class="gralNormal"><b><?= $cnfModNombre ?></b><?= ($_GET["Flag"]?"&nbsp;&nbsp;<img src=\"Lenguajes/flags/".$_GET["Flag"].".gif\" width=\"20\" height=\"12\" border=\"0\" valign=\"middle\" alt=\"\">":"")?></span>
    </td>
  </tr>
<?= $cHTML ?>
  <tr height="25" bgcolor="#DC9137">
    <td colspan="2" align="center">
      <input class="blanco" type="submit" name="botGrabar" value="<?= $cBotonSubmit ?>">
      <? if ($cAccion=="Modificar" and $_SESSION["gbl".$conf["VariablesSESSION"]."Duplic"]=="S" and $cnfPerAgregar=="S" and $_GET["Lang"]!="Si") { ?>
        <input class="blanco" type="submit" name="botAgregar" value="<?= $txt['NuevoRegis']?>" onClick="document.Datos.Accion.value='Agregar'">&nbsp;
      <? } ?>
    </td>
  </tr>
<?
// Si existe algun campo obligatorio, se coloca un comentario
//    explicando el significado del color distinto.
if ($cCpoReq=="S") { ?>
  <tr>
    <td colspan="2" align="center">
      <?= $txt['MsgCpoObli']?>
    </td>
  </tr><?
} ?>
</table><?

if ($_GET["Lang"]=="Si") { ?>
  <input type="hidden" name="Part" value="<?= $_GET["Part"]?>"><?
} ?>

</form>

</body>
</html>