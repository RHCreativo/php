<?
session_start();
set_time_limit (3600);
include("../Admin/Conexion.inc.php");
include("../Admin/Funciones/Funciones.inc.php");

$eid = $_GET['eid'];
$eem = $_GET['eem'];

$cSql = "SELECT UsrCode, UsrEMail1, UsrFirstName1, UsrLastName1 FROM Users WHERE MD5(UsrCode)='".$eid."' AND MD5(UsrEMail1)='".$eem."' LIMIT 1";
$cResultado = mysql_query($cSql) or die("error");
if(mysql_num_rows($cResultado)==0){
	echo 'no hay datos<br>';
	echo $eid.'<br>';
	echo $eem;
	echo '<br> msg='.$_GET['msg'];
	exit;
}else{
	$aRegistro = mysql_fetch_array($cResultado);
}


if($_POST['xid'] and $_POST['xum'] and $_POST['mirtuono_captcha']){
	$Captcha = (string) $_POST["mirtuono_captcha"];
	if(sha1($Captcha) != $_SESSION["mirtuono_captcha"]){
		header('Location: http://'.$_SERVER['SERVER_NAME'].'/unsubscribe/'.$_POST['xid'].'/'.$_POST['xum'].'.html/bc');
		exit;
	}else{
		mysql_query("UPDATE  Users SET UsrActivo = 'No' WHERE MD5(UsrCode)='".$eid."' AND MD5(UsrEMail1)='".$eem."' LIMIT 1") or die('error');
		header('Location: http://'.$_SERVER['SERVER_NAME'].'/unsubscribe/' . $_POST['xid'] .'//'. $_POST['xum'] .'.html/done');
		unset($_POST);
		exit;
	}
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Desubscipción Newsletter - <?= $_SERVER['SERVER_NAME'] ?></title>

<style type="text/css">
a.btn{
	width: 201px;
	height: 36px;
	display: block;
	overflow: hidden;
	clear: both;
	margin: 18px auto 0 auto;
	padding-top: 13px;
	text-align: center;
	background-image: url('http://<?=$_SERVER['SERVER_NAME']?>/imgs/bg_botones_asociarse.png');
	background-position: left top;
	background-repeat: no-repeat;
	font-size: 16px;
	font-weight: 700;
	color: #FFF;
 }
a.btn:hover{
	background-position: -201px 0px;
 }
</style>
</head>

<body bgcolor="#eeecd5" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
<table align="center" width="650" border="0" cellpadding="0" cellspacing="0" bgcolor="#FFFFFF">
  <tr> 
    <td align="center" valign="top" height="96"><div align="left" style="height:96px;"><img src="http://<?=$_SERVER['SERVER_NAME']?>/Admin/Newsletter/Imagenes/Header.jpg" alt="<?=$_SERVER['SERVER_NAME']?>" /></div></td>
  </tr>
  <tr>
    <td valign="top">
      <table width="650" border="0" cellpadding="0" cellspacing="0" style="font-family: Arial, Helvetica, sans-serif; font-size:12px; color:#505050;">
        <tr>
          <td width="30"><img src="http://<?=$_SERVER['SERVER_NAME']?>/Admin/Newsletter/Imagenes/1px.gif" width="30" height="30"></td>
          <td width="270">&nbsp;</td>
          <td width="50">&nbsp;</td>
          <td width="270">&nbsp;</td>
          <td width="30">&nbsp;</td>
        </tr>
        
        <tr>
          <td>&nbsp;</td>
          <td colspan="3"><span style="font-family:'Trebuchet MS', Arial, Helvetica, sans-serif; font-size:14px;">
          <br />
          Estimado: <b><?= $aRegistro['UsrFirstName1'].' '.$aRegistro['UsrLastName1']?></b>
          <br /><br />
          <? if($_GET['msg']=='done'){ ?>
          	Usted ya no recibirá las novedades en su casilla de correo.<br />
            Para volver a activar este servicio, reenvíe el formulario de suscripción.<br /><br />
            Muchas Gracias.
            
            <br />
            <a class="btn" href="http://www.barcelonanews.com.ar">INICIO</a>
            
          <? }else{ ?>
          
          	<? if($_GET['msg']=='bc'){ ?>
          El código de seguridad ingresado no es válido. Inténtelo nuevamente.
            <? }else{ ?>
          ¿Deseas dejar de recibir nuestro newsletter en la casilla de email: <br /><b><?= $aRegistro["UsrEMail1"] ?></b>?
            <? } ?>

          <br /><br />
          <form name="unsubscribe" method="post" action="">
          <input type="hidden" name="xid" value="<?=$eid?>" />
          <input type="hidden" name="xum" value="<?=$eem?>" />
          <label for="mirtuono-captcha"><span style="font-size:.9em">Completa el código de seguridad</span></label>
          <table cellpadding="0" cellspacing="0" border="0"><tr>
            <td style="padding-right:30px; padding-top: 8px;"><label for="mirtuono-captcha"><img src="http://<?=$_SERVER['SERVER_NAME']?>/phpMailer/inc.captcha.php"></label></td>
            <td style="text-align:right"><input name="mirtuono_captcha" id="mirtuono-captcha" type="text" maxlength="6" class="itxt4ch"></td>
          </tr></table>
          
          <a class="btn" href="javascript:document.unsubscribe.submit();">DESUBSCRIBIR</a>
          </form>
          </span>
          
          <? } ?>
          </td>
          <td>&nbsp;</td>
        </tr>
        
        <tr>
          <td colspan="5"><img src="http://<?=$_SERVER['SERVER_NAME']?>/Admin/Newsletter/Imagenes/1px.gif" width="650" height="20"></td>
        </tr>
        <tr>
          <td>&nbsp;</td>
          <td valign="top"><p style="font-family:'Trebuchet MS', Arial, Helvetica, sans-serif; font-size:12px;">
          Barcelona - Club de Cata<br>
          <a href="http://<?=$_SERVER['SERVER_NAME']?>"><?=$_SERVER['SERVER_NAME']?></a></p></td>
          <td>&nbsp;</td>
          <td align="right"><p style="font-family:'Trebuchet MS', Arial, Helvetica, sans-serif; font-size:10px; line-height:1.2em">
          © 2012 - Barcelona Vinos.<br>
          Telefax: 0341-4402305 / 4254784<br>
          Córdoba 2375 - Rosario - Santa Fe - Argentina.</p></td>
          <td>&nbsp;</td>
        </tr>
        
        <tr>
          <td colspan="5" style="text-align:center">
          <img src="http://<?=$_SERVER['SERVER_NAME']?>/Admin/Newsletter/Imagenes/1px.gif" width="650" height="6">
          <span style="font-family:Arial, Helvetica, sans-serif; font-size: 10px; color: #999999">¿Ya no deseas recibir nuestro Newsletter? - Puedes <a href="http://www.barcelonanews.com.ar/NewsletterBaja.php?Usuario=##ClaveBaja##" style="color:#666666; text-decoration:underline" target="_blank">desuscribirte</a> desde aquí.</span>
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>
</body>
</html>
<?
/*
UPDATE  `uv0619_barce2012`.`Users` SET  `UsrPrueba` =  '-' WHERE  `Users`.`UsrCode` =2001 LIMIT 1 ;
UPDATE  `uv0619_barce2012`.`Users` SET  `UsrPrueba` =  '-' WHERE  `Users`.`UsrCode` =2563 LIMIT 1 ;
UPDATE  `uv0619_barce2012`.`Users` SET  `UsrPrueba` =  '-' WHERE  `Users`.`UsrCode` =3687 LIMIT 1 ;
UPDATE  `uv0619_barce2012`.`Users` SET  `UsrPrueba` =  '-' WHERE  `Users`.`UsrCode` =4912 LIMIT 1 ;
UPDATE  `uv0619_barce2012`.`Users` SET  `UsrPrueba` =  '-' WHERE  `Users`.`UsrCode` =6623 LIMIT 1 ;
UPDATE  `uv0619_barce2012`.`Users` SET  `UsrPrueba` =  '-' WHERE  `Users`.`UsrCode` =9649 LIMIT 1 ;
UPDATE  `uv0619_barce2012`.`Users` SET  `UsrPrueba` =  '-' WHERE  `Users`.`UsrCode` =9739 LIMIT 1 ;
UPDATE  `uv0619_barce2012`.`Users` SET  `UsrPrueba` =  '-' WHERE  `Users`.`UsrCode` =9778 LIMIT 1 ;
UPDATE  `uv0619_barce2012`.`Users` SET  `UsrPrueba` =  '-' WHERE  `Users`.`UsrCode` =10011 LIMIT 1 ;
*/
/*
UPDATE  `uv0619_barce2012`.`Users` SET  `UsrPrueba` =  'Si' WHERE  `Users`.`UsrPrueba` ='-';
*/
?>