<?
/*
******************************************************************************
* Administrador de Contenidos                                                *
* -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-= *
*                                                                            *
* (C) 2002, Fabián Chesta                                                    *
*                                                                            *
*                                                                            *
******************************************************************************
*/
session_start();

// Archivos de Conexión y Configuración
include("Conexion.inc.php");
include("Lenguajes/" . $conf["Lenguaje"]);
include("Funciones/Funciones.inc.php");

$_SESSION["gbl".$conf["VariablesSESSION"]."Modulo"] = $_GET["Modulo"];

// Determina los permisos necesarios para las diferentes acciones
//$cSql = "SELECT PerAgregar, VerCntLineas FROM sysModulos LEFT JOIN sysModUsu ON sysModulos.ModNombre=sysModUsu.ModNombre WHERE sysModulos.ModNombre='" . $_SESSION["gbl".$conf["VariablesSESSION"]."Modulo"] . "' AND sysModUsu.UsuAlias='" . $_SESSION["gbl".$conf["VariablesSESSION"]."Alias"] . "'";
//$nResultado = mysql_query ($cSql) or die("Error en la consulta: <b>" . $cSql . "</b> <br>Tipo de error: <b>" . mysql_error() . "</b>");
//$aRegistro  = mysql_fetch_array($nResultado);

//mysql_free_result ($nResultado);


//echo("##".$cSql."##");

// Control de Accesos y Permisos
$cnfPerAgregar='S';
if ($_SESSION["gbl".$conf["VariablesSESSION"]."Alias"]=="" or $cnfPerAgregar!='S') {
  header ("Location: Index.php");
}


//$pa = $_POST['PassActual'];
$mensaje = "Este formulario le permite cambiar su contraseña.</br></br>Sugerimos utilizar una combinación de letras mayúsculas, minúsculas y números para mayor seguridad.";

if($_POST['NewPass']){

if($_POST['NewPass'] != $_POST['ConfirmPass']){// si no son iguales
  // error!... redireccionas o pones un mensaje. 
  $mensaje = "Las nuevas contraseñas no coinciden, reingreselas por favor";
}else{
  // conexión a BD (obviada aquí).
  
  $sql = "UPDATE sysUsuarios SET UsuClave='".md5($_POST['NewPass'])."' WHERE UsuAlias='".$_SESSION["gbl".$conf["VariablesSESSION"]."Alias"]."' AND UsuClave='".md5($_POST['PassActual'])."'";
  mysql_query($sql) or die("error <b>$sql</b> :::".mysql_error());
  if(mysql_affected_rows() == 1){
    // Todo ok!!
	$mensaje = "Contraseña cambiada correctamente!";
  }else{
    // Password incorrecto.
	$mensaje = "La contraseña ingresada no coincide con su contraseña actual";
  }
}

}
?> 
<html>
<head>
<title></title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link rel="stylesheet" href="Estilos/hde.css" type="text/css" />

<script language="JavaScript" type="text/JavaScript">
  <!--
  function fValidar(elForm) {
  /*
  	if(elForm.NewPass.value != elForm.ConfirmPass.value) {
      alert("'Las contraseñas no coinciden");
      elForm.ConfirmPass.focus();
      return false;
    }
	*/
    return (true);
  }
  //-->
  </script>
</head>

<body bgcolor="#FFFFFF" text="#000000" leftmargin="0" topmargin="30" marginwidth="0" marginheight="0">

  <form onSubmit="return fValidar(this)" method="post" enctype="multipart/form-data">
  <? // if (##Confirmación de cambio contraseña##) { ?>
<!--
      <span class="gralNormal"><b><center>
        La contraseña fue cambiada.
      </center></b></span>
-->
  <? // } ?>
  <? // if (##Contraseña incorrecta##) { ?>
      <span class="gralNormal"><b><center>
<!--        La contraseña actual ingresada no es correcta. -->
		<br /><br />
		<?= $mensaje ?>
      </center></b></span>
  <? // } ?>
  <br />
  <br />
  <table border="1" cellspacing="0" cellpadding="1" align="center" bordercolor="#FFFFFF" class="gralTabla">
    <tr bgcolor="#929292" align="center" height="20">
      <td><b>&nbsp;Cambiar contrase&ntilde;a para el usuario:&nbsp;</b></td>
      <td align="left">&nbsp;&nbsp;&nbsp;<font color="#FFFFFF"><strong><?= $_SESSION["gbl".$conf["VariablesSESSION"]."Alias"]?></strong></font></td>
    </tr>
    <tr bgcolor="#D3D3D3">
      <td align="center">Ingrese su contrase&ntilde;a actual:</td>
      <td>&nbsp;
          <input name="PassActual" type="password" id="PassActual" value="" size="40" maxlength="100">
        &nbsp;</td>
    </tr>
    <tr bgcolor="#D3D3D3">
      <td align="center">Ingrese una nueva contrase&ntilde;a:</td>
      <td>&nbsp;
          <input name="NewPass" type="password" id="NewPass" value="" size="40" maxlength="100">
        &nbsp;</td>
    </tr>
        <tr bgcolor="#D3D3D3">
          <td align="center">Confirme la nueva contrase&ntilde;a:</td>
          <td>&nbsp;
		  <input name="ConfirmPass" type="password" id="ConfirmPass" value="" size="40" maxlength="100">
          &nbsp;</td>
        </tr>
  </table>
  <br><center><input type="submit" name="action" value="Cambiar contrase&ntilde;a">
  </center>
  </form>

</body>
</html>