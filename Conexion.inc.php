<?php
if (strstr($_SERVER["SCRIPT_NAME"],"/Admin/")) {
  //error_reporting(E_ALL);

  ini_set("arg_separator.input","&amp;");
  ini_set("arg_separator.output","&amp;");
}


$conf['EstadoSitio'] = "Production";   // Production, Test


if (substr($_SERVER["HTTP_HOST"],0,9)=="localhost") {

  $conf['SubDir'] = "/PAckGroup";

  // Server Local
  // ============
  $cServidor = "localhost";
  $cDB       = "gz000408_pkgroup2011";
  $cUsuario  = "root";
  $cClave    = "rootpass";

} else {

  $conf['SubDir'] = "/newsite";

  // Server Remoto (ST)
  // ==================

//  $cServidor = "localhost";
  $cServidor = "127.0.0.1";
  $cDB       = "uv0871_supertry2011";
  $cUsuario  = "uv0871";
  $cClave    = "ujaew67PJHY";


}


$conf['DirUpload']   = str_replace(str_replace($conf['SubDir'], "", $_SERVER["SCRIPT_NAME"]), "", $_SERVER["SCRIPT_FILENAME"]) . "/Upload/";

$nConexion = mysql_connect($cServidor, $cUsuario, $cClave) or die("Fallo en la conexion<br>");

if (!mysql_select_db($cDB)){
   echo "Error: No selecciona la DB<br>";
}


//mysql_query("set collation_connection = @@collation_database");

$cSql = "SET NAMES 'utf8'";
mysql_query ($cSql) or fErrorSQL($conf["EstadoSitio"], "<br /><br /><b>Error en la consulta:</b><br />" . $cSql . "<br /><br /><b>Tipo de error:</b><br />" . mysql_error() . "<br />");
//$db_charset = mysql_query( "SHOW VARIABLES LIKE 'character_set_database'" );
//$charset_row = mysql_fetch_assoc( $db_charset );
//mysql_query( "SET NAMES '" . $charset_row['Value'] . "'" );
//unset( $db_charset, $charset_row );



if (strstr($_SERVER["SCRIPT_NAME"],"/Admin/")) {

  setcookie('FCKeditorUserFilesPath',$conf['SubDir'].'/Upload/FCKeditor/', time()+3600*24*30*12*10) ;
  setcookie('FCKeditorUserFilesAbsolutePath',$conf['DirUpload'].'FCKeditor/', time()+3600*24*30*12*10) ;

  // Valores de Configuración del Administrador
  // ==========================================
  $cSql = "SELECT sysCnfCodigo, sysCnfValor FROM sysConfig";
  $nResultado = mysql_query ($cSql) or fErrorSQL($conf["EstadoSitio"], "<br /><br /><b>Error en la consulta:</b><br />" . $cSql . "<br /><br /><b>Tipo de error:</b><br />" . mysql_error() . "<br />");
  while ($aRegistro = mysql_fetch_array($nResultado)) {;
    $conf[$aRegistro["sysCnfCodigo"]] = $aRegistro["sysCnfValor"] ;
  }
  mysql_free_result ($nResultado);

} else {

  // Valores de Configuración del Sitio
  // ==================================


  // Averiguo si el sitio tiene tabla "Idiomas"
  $cTblIdiomas = "No" ;

  $nResultado  = mysql_list_tables ($cDB);
  for ($i=0; $i < mysql_num_rows ($nResultado); $i++) {
    if ( mysql_tablename ($nResultado, $i)=="Idiomas" ) {
      $cTblIdiomas = "Si" ;
      break;
    }
  }

  if ( $cTblIdiomas=="Si" ) {
    if ($_GET["Idioma"]) {
      // Cuando el visitante "cambia" de Idioma
      $cSql = "SELECT IdiCodigo, IdiTextos, IdiCampos FROM Idiomas WHERE IdiParticula='" . $_GET["Idioma"] . "'";
      $nResultado = mysql_query ($cSql) or fErrorSQL($conf["EstadoSitio"], "<br /><br /><b>Error en la consulta:</b><br />" . $cSql . "<br /><br /><b>Tipo de error:</b><br />" . mysql_error() . "<br />");
      $aRegistro = mysql_fetch_array($nResultado);

      $_SESSION["gbl".$conf["VariablesSESSION"]."IdiCod"] = $aRegistro["IdiCodigo"];
      $_SESSION["gbl".$conf["VariablesSESSION"]."Idioma"] = $aRegistro["IdiTextos"];
      $_SESSION["gbl".$conf["VariablesSESSION"]."Partic"] = $_GET["Idioma"];
      $_SESSION["gbl".$conf["VariablesSESSION"]."Campos"] = $aRegistro["IdiCampos"];

      mysql_free_result ($nResultado);

    } elseif (!$_SESSION["gbl".$conf["VariablesSESSION"]."Idioma"]) {
      // Cuando no hay Idioma definido
      $cSql = "SELECT IdiCodigo, IdiTextos, IdiParticula, IdiCampos FROM Idiomas WHERE IdiDefault='Si'" ;
      $nResultado = mysql_query ($cSql) or fErrorSQL($conf["EstadoSitio"], "<br /><br /><b>Error en la consulta:</b><br />" . $cSql . "<br /><br /><b>Tipo de error:</b><br />" . mysql_error() . "<br />");
      $aRegistro = mysql_fetch_array($nResultado);

      $_SESSION["gbl".$conf["VariablesSESSION"]."IdiCod"] = $aRegistro["IdiCodigo"];
      $_SESSION["gbl".$conf["VariablesSESSION"]."Idioma"] = $aRegistro["IdiTextos"];
      $_SESSION["gbl".$conf["VariablesSESSION"]."Partic"] = $aRegistro["IdiParticula"];
      $_SESSION["gbl".$conf["VariablesSESSION"]."Campos"] = $aRegistro["IdiCampos"];

      mysql_free_result ($nResultado);
    }

    // Elimino la variable Idioma del QueryString
    $cQString = str_replace('Idioma='.$_GET["Idioma"],'',str_replace('Idioma='.$_GET["Idioma"].'&','',str_replace('&Idioma='.$_GET["Idioma"],'',$_SERVER["QUERY_STRING"]))) ;

    // Si estoy en Buscar.php y en el QueryString figura la Pagina la elimino
    if (substr($_SERVER["SCRIPT_NAME"],-10)=="Buscar.php" and strrpos($cQString,"&Pagina")) {
      $cQString = substr($cQString,0,strrpos($cQString,"&Pagina")) ;
    }

    // Genero el mismo QueryString pero cambiando los idiomas
    $cSql = "SELECT IdiParticula FROM Idiomas" ;
    $nResultado = mysql_query ($cSql) or fErrorSQL($conf["EstadoSitio"], "<br /><br /><b>Error en la consulta:</b><br />" . $cSql . "<br /><br /><b>Tipo de error:</b><br />" . mysql_error() . "<br />");
    while ($aRegistro = mysql_fetch_array($nResultado)) {
      $aQString[$aRegistro["IdiParticula"]] = str_replace(".".$_SESSION["gbl".$conf["VariablesSESSION"]."Partic"].".",".".$aRegistro["IdiParticula"].".",$_SERVER["SCRIPT_NAME"]) . '?' . $cQString.($cQString!=''?'&':'').'Idioma='.$aRegistro["IdiParticula"];
    }
    mysql_free_result ($nResultado);
  }
}
?>