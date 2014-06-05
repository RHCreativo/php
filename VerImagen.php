<?
/*
******************************************************************************
* Administrador de Contenidos                                                *
* -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-= *
*                                                                            *
* (C) 2002, Fabián Chesta                                                    *
*                                                                            *
* Comentarios: Programa para visualizar una imagen                           *
*                                                                            *
******************************************************************************
*/
session_start();

// Archivos de Conexión y Configuración
include("Conexion.inc.php");
include("Lenguajes/" . $conf["Lenguaje"]);

$cImagen = $_GET["Imagen"] ;
$cInfoAd = $_GET["Info"] ;
$nAncho  = $_GET["Ancho"] ;
$nAlto   = $_GET["Alto"] ;

$cFileExte = end(explode(".", $cImagen)); ?>
<html>
<head>
  <title><?= $txt['VisualizImgs']?> - <?= $cImagen?> - <?= $cInfoAd?></title>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

  <script type="text/javascript" src="Funciones/embeddedcontent.js" defer="defer"></script>

  <link rel="stylesheet" href="Estilos/hde.css" type="text/css" />
</head>

<body bgcolor="#000000" text="#000000" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0" style="overflow:hidden;">

<table width="100%" height="100%" cellpadding="0" cellspacing="0" border="0">
  <tr width="100%" height="100%">
    <td align="center"><? 
      if ($cFileExte=="swf") { ?>
        <object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=5,0,0,0" width=<?= $nAncho?> height=<?= $nAlto?> menu="false" id=OBJECT1>
          <param name="movie" value="../Upload/<?= $cImagen?>">
          <param name="quality" value="high">
          <param name="menu" value="0">
          <embed src="../Upload/<?= $cImagen?>" width=<?= $nAncho?> height=<?= $nAlto?> quality="high" menu="0" pluginspage="http://www.macromedia.com/shockwave/download/index.cgi?P1_Prod_Version=ShockwaveFlash" type="application/x-shockwave-flash"></embed>
        </object><? 
      } else { ?>
        <img src="../Upload/<?= $cImagen?>" width=<?= $nAncho?> height=<?= $nAlto?> border="0" title="<?= $cImagen . "\n" . $cInfoAd?>">
      <? } ?>
    </td>
  </tr>
</table>

</body>
</html>