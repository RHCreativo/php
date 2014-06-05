<?
if ($cModulo=="Galleries" && ($_REQUEST["Accion"]=="Agregar" || $_REQUEST["Accion"]=="Modificar")) {

  $cSql = "SELECT SitLogWatermark FROM Sites WHERE SitId=" . $_POST["SitId"] ;
  $nResultado = mysql_query ($cSql) or fErrorSQL($conf["EstadoSitio"], "<br /><br /><b>Error en la consulta:</b><br />" . $cSql . "<br /><br /><b>Tipo de error:</b><br />" . mysql_error() . "<br />");

  $aRegistro = mysql_fetch_array($nResultado) ;

  $cLogWatermark = $aRegistro["SitLogWatermark"] ;

  mysql_free_result ($nResultado);

  // Genero los Thumbnails
  for ($nImagen=1; $nImagen<=6; $nImagen++) {
    if (trim($_FILES["GalImage".$nImagen."Xl"]['name'])!="") {
      fCrop("../Upload/Directos/", trim($_FILES["GalImage".$nImagen."Xl"]['name']), 145, 95, "No") ;
    }
  }

  // Achico las imágenes (MAX 800 x 800) y luego les pongo el Watermark
  for ($nImagen=1; $nImagen<=6; $nImagen++) {
    if (trim($_FILES["GalImage".$nImagen."Xl"]['name'])!="") {
      fResize("../Upload/Directos/".trim($_FILES["GalImage".$nImagen."Xl"]['name']), 800, 800) ;
      if ($cLogWatermark!="") {
        fWatermark("../Upload/Directos/".trim($_FILES["GalImage".$nImagen."Xl"]['name']), "../Upload/Directos/".$cLogWatermark, 9) ;
      }
    }
  }

}



if ($cModulo=="SpecialGalleries" && ($_REQUEST["Accion"]=="Agregar" || $_REQUEST["Accion"]=="Modificar")) {

  $cSql = "SELECT SitLogWatermark FROM Sites WHERE SitId=" . $_POST["SitId"] ;
  $nResultado = mysql_query ($cSql) or fErrorSQL($conf["EstadoSitio"], "<br /><br /><b>Error en la consulta:</b><br />" . $cSql . "<br /><br /><b>Tipo de error:</b><br />" . mysql_error() . "<br />");

  $aRegistro = mysql_fetch_array($nResultado) ;

  $cLogWatermark = $aRegistro["SitLogWatermark"] ;

  mysql_free_result ($nResultado);

  // Genero los Thumbnails
  for ($nImagen=1; $nImagen<=16; $nImagen++) {
    if (trim($_FILES["GalImage".str_pad($nImagen, 2, "0", STR_PAD_LEFT)]['name'])!="") {
      fCrop("../Upload/Directos/", trim($_FILES["GalImage".str_pad($nImagen, 2, "0", STR_PAD_LEFT)]['name']), 165, 165, "No") ;
    }
  }

  // Achico las imágenes (MAX 800 x 800) y luego les pongo el Watermark
  for ($nImagen=1; $nImagen<=16; $nImagen++) {
    if (trim($_FILES["GalImage".str_pad($nImagen, 2, "0", STR_PAD_LEFT)]['name'])!="") {
      fResize("../Upload/Directos/".trim($_FILES["GalImage".str_pad($nImagen, 2, "0", STR_PAD_LEFT)]['name']), 800, 800) ;
      if ($cLogWatermark!="") {
        fWatermark("../Upload/Directos/".trim($_FILES["GalImage".str_pad($nImagen, 2, "0", STR_PAD_LEFT)]['name']), "../Upload/Directos/".$cLogWatermark, 9) ;
      }
    }
  }


}








//******************************************************************************
//* Función: fCrop                                                             *
//* -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-= *
//*                                                                            *
function fCrop($cPath, $cImage, $nAncho, $nAlto, $cCentrar) { 

  $nRatioThumb = $nAncho / $nAlto ;

  // Get dimensions of existing image
  $dimensions = getimagesize($cPath . $cImage);
  $nRatioOrig = $dimensions[0] / $dimensions[1] ;

  $cQueCambiar = ($nRatioOrig==$nRatioThumb?"Nothing":($nRatioThumb>$nRatioOrig?"Height":"Width"));

  $nCropWidth  = ($cQueCambiar<>"Width"?$dimensions[0]:($dimensions[1]*$nRatioThumb)) ;
  $nCropHeight = ($cQueCambiar<>"Height"?$dimensions[1]:($dimensions[0]/$nRatioThumb)) ;

  $nCropIniX   = ($dimensions[0]-$nCropWidth)/2 ;
  $nCropIniY   = ($cCentrar=="Si"?($dimensions[1]-$nCropHeight)/2:0) ;

  // Prepare canvas
  $canvas = imagecreatetruecolor($nAncho,$nAlto);
  $piece = imagecreatefromjpeg($cPath . $cImage);

  // Generate the cropped image
  imagecopyresampled($canvas, $piece, 0,0, $nCropIniX, $nCropIniY, $nAncho, $nAlto, $nCropWidth, $nCropHeight);

  // Write image
  imagejpeg($canvas, $cPath . "tn_" . $cImage);

  // Clean-up
  imagedestroy($canvas);
  imagedestroy($piece);

}


//******************************************************************************
//* Función: fWatermark                                                        *
//* -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-= *
//*                                                                            *
function fWatermark($cImage, $cWatermark, $nPosicion) { 

  $imagesource =  $cImage;
  $filetype = substr($imagesource,strlen($imagesource)-4,4);
  $filetype = strtolower($filetype);
  if($filetype == ".gif")  $image = @imagecreatefromgif($imagesource);  
  if($filetype == ".jpg")  $image = @imagecreatefromjpeg($imagesource);  
  if($filetype == ".png")  $image = @imagecreatefrompng($imagesource);  
  if (!$image) die();
  $watermark = @imagecreatefromgif($cWatermark);
  $imagewidth = imagesx($image);
  $imageheight = imagesy($image);  
  $watermarkwidth =  imagesx($watermark);
  $watermarkheight =  imagesy($watermark);
  if ($nPosicion==1) {
    $startwidth = 15;
    $startheight = 15;
  } elseif ($nPosicion==2) {
    $startwidth = (($imagewidth - $watermarkwidth)/2);
    $startheight = 15;
  } elseif ($nPosicion==3) {
    $startwidth = ($imagewidth - $watermarkwidth - 15);
    $startheight = 15;
  } elseif ($nPosicion==4) {
    $startwidth = 15;
    $startheight = (($imageheight - $watermarkheight)/2);
  } elseif ($nPosicion==5) {
    $startwidth = (($imagewidth - $watermarkwidth)/2);
    $startheight = (($imageheight - $watermarkheight)/2);
  } elseif ($nPosicion==6) {
    $startwidth = ($imagewidth - $watermarkwidth - 15);
    $startheight = (($imageheight - $watermarkheight)/2);
  } elseif ($nPosicion==7) {
    $startwidth = 15;
    $startheight = ($imageheight - $watermarkheight - 15);
  } elseif ($nPosicion==8) {
    $startwidth = (($imagewidth - $watermarkwidth)/2);
    $startheight = ($imageheight - $watermarkheight - 15);
  } elseif ($nPosicion==9) {
    $startwidth = ($imagewidth - $watermarkwidth - 15);
    $startheight = ($imageheight - $watermarkheight - 15);
  }
  imagecopy($image, $watermark,  $startwidth, $startheight, 0, 0, $watermarkwidth, $watermarkheight);
  imagejpeg ($image, $cImage);
  imagedestroy($image);
  imagedestroy($watermark);
}


//******************************************************************************
//* Función: fResize                                                           *
//* -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-= *
//*                                                                            *
function fResize($cImage, $nMaxAncho, $nMaxAlto, $cTexto="", $cColor="#ffffff") { 

  // Load image
  $rImagenOrig = fAbrirImagen($cImage);
  if ($rImagenOrig === false) { die ('Unable to open image'); }

  // Get original width and height
  $nAnchoOrig = imagesx($rImagenOrig);
  $nAltoOrig  = imagesy($rImagenOrig);

  // New width and height
  $aValoresWH = fValoresWH($nAnchoOrig, $nAltoOrig, $nMaxAncho, $nMaxAlto) ;
  $nAnchoNuev = $aValoresWH["W"] ;
  $nAltoNuev  = $aValoresWH["H"] ;

  // Resample
  $rImagenNuev = imagecreatetruecolor($nAnchoNuev, $nAltoNuev);
  imagecopyresampled($rImagenNuev, $rImagenOrig, 0, 0, 0, 0, $nAnchoNuev, $nAltoNuev, $nAnchoOrig, $nAltoOrig);

  // Si se quiere colocar algún texto
  if ($cTexto) {
    // Get identifier for color
    $aColor = hex2rgb($cColor);
    $nColor = imagecolorallocate($rImagenNuev, $aColor["R"], $aColor["G"], $aColor["B"]);

    // Add text to image
    imagestring($rImagenNuev, 3, 5, imagesy($rImagenNuev)-20, $cTexto, $nColor);
  }

  imagejpeg($rImagenNuev,$cImage);

  imagedestroy($rImagenOrig);
  imagedestroy($rImagenNuev);

}


//******************************************************************************
//* Función: hex2rgb                                                           *
//* -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-= *
//*                                                                            *
//* http://blog.3wstudio.com.ar/index.php?idPost=60                            *
//*                                                                            *
function hex2rgb($cHex){

  $cHex = eregi_replace("[^a-fA-F0-9]", "", $cHex);

  switch( strlen($cHex) ) {
    case 2:
      $cHex = $cHex."0000";
      break;
    case 3:
      $cHex = substr($cHex,0,1).substr($cHex,0,1).substr($cHex,1,1).substr($cHex,1,1).substr($cHex,2,1).substr($cHex,2,1);
      break;
    case 4:
      $cHex = $cHex."00";
      break;
    case 6:
      break;
    default:
      $cHex = "000000";
      break;
   }

  $rgb['R'] = hexdec(substr($cHex,0,2)) ;
  $rgb['G'] = hexdec(substr($cHex,2,2)) ;
  $rgb['B'] = hexdec(substr($cHex,4,2)) ;

  return $rgb ;
}



//******************************************************************************
//* Función: fValoresWH                                                        *
//* -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-= *
//*                                                                            *
function fValoresWH($nAncho, $nAlto, $nMaxAncho, $nMaxAlto) {

  if ($nMaxAncho==0 || $nMaxAncho=="")
    $nMaxAncho = $nAncho;

  if ($nMaxAlto==0 || $nMaxAlto=="")
    $nMaxAlto = $nAlto;

  if ($nAncho<=$nMaxAncho && $nAlto<=$nMaxAlto) {
    $aTamano["W"] = $nAncho ;
    $aTamano["H"] = $nAlto ;
  } elseif ($nAncho>$nMaxAncho && $nAlto>$nMaxAlto && $nAncho/$nAlto>=$nMaxAncho/$nMaxAlto) {
    $aTamano["W"] = $nMaxAncho ;
    $aTamano["H"] = round($nAlto*($nMaxAncho/$nAncho),0) ;
  } elseif ($nAncho>$nMaxAncho && $nAlto>$nMaxAlto) {
    $aTamano["W"] = round($nAncho*($nMaxAlto/$nAlto),0) ;
    $aTamano["H"] = $nMaxAlto ;
  } elseif ($nAncho>$nMaxAncho) {
    $aTamano["W"] = $nMaxAncho ;
    $aTamano["H"] = round($nAlto*($nMaxAncho/$nAncho),0) ;
  } elseif ($nAlto>$nMaxAlto) {
    $aTamano["W"] = round($nAncho*($nMaxAlto/$nAlto),0) ;
    $aTamano["H"] = $nMaxAlto ;
  }
  return $aTamano;
}



//******************************************************************************
//* Función: fAbrirImagen                                                      *
//* -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-= *
//*                                                                            *
function fAbrirImagen($cArchivo) {
  # JPEG:
  $rImagen = @imagecreatefromjpeg($cArchivo);
  if ($rImagen !== false) { return $rImagen; }

  # GIF:
  $rImagen = @imagecreatefromgif($cArchivo);
  if ($rImagen !== false) { return $rImagen; }

  # PNG:
  $rImagen = @imagecreatefrompng($cArchivo);
  if ($rImagen !== false) { return $rImagen; }

  return false;
}









?>