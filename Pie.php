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
include("Funciones/Funciones.inc.php");
include("Lenguajes/" . $conf["Lenguaje"]);

// Control de Accesos y Permisos
if ($_SESSION["gbl".$conf["VariablesSESSION"]."Alias"]=="") {
  header ("Location: Index.php");
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
    "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
  <title>Untitled Document</title>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">

  <link rel="stylesheet" href="Estilos/hde.css" type="text/css">

  <style type="text/css">
    a { font-family: Verdana, Arial; font-size: 11px; font-style: normal; font-weight: bold; color: #FFFFFF; text-decoration: none; }
  </style>

  <script language="JavaScript"  type="text/javascript" src="Funciones/Funciones.js"></script>

  <script language="JavaScript" type="text/javascript" >
    //<![CDATA[
    function Actualizar() {
      // Antes usaba esta opción. Firefox sugiere usar el estandar W3C document.getElementById [FChesta 2005-05-29]
      // parent.Cuerpo.location.href='<?= $_SESSION["pdpIrA"]?>.php?Modulo=<?= $_SESSION["gblModulo"]?>&Orden=<?= $_SESSION["pdpOrden"]?>&Forma=<?= $_SESSION["pdpForma"]?>&CpoFiltro1='+fPie.FiltroCampo1.value+'&TipFiltro1='+fPie.FiltroTipo1.value+'&TxtFiltro1='+escape(fPie.FiltroTexto1.value)+'&NexFiltro='+fPie.FiltroNexo.value+'&CpoFiltro2='+fPie.FiltroCampo2.value+'&TipFiltro2='+fPie.FiltroTipo2.value+'&TxtFiltro2='+escape(fPie.FiltroTexto2.value);
      parent.Cuerpo.location.href='<?= $_SESSION["pdpIrA"]?>.php?Modulo=<?= $_SESSION["gblModulo"]?>&Orden=<?= $_SESSION["pdpOrden"]?>&Forma=<?= $_SESSION["pdpForma"]?>&CpoFiltro1='+document.getElementById('FiltroCampo1').value+'&TipFiltro1='+document.getElementById('FiltroTipo1').value+'&TxtFiltro1='+escape(document.getElementById('FiltroTexto1').value)+'&NexFiltro='+document.getElementById('FiltroNexo').value+'&CpoFiltro2='+document.getElementById('FiltroCampo2').value+'&TipFiltro2='+document.getElementById('FiltroTipo2').value+'&TxtFiltro2='+escape(document.getElementById('FiltroTexto2').value)+'&Cantidad='+document.getElementById('CntFilas').value;
    }

    function CambiarPagina(vModulo, vOrdenCampo, vOrdenForma, vFiltroCampo1, vFiltroTipo1, vFiltroTexto1, vFiltroNexo, vFiltroCampo2, vFiltroTipo2, vFiltroTexto2, vInicio, vCantidad) {
      <?
      /*
      Desconozco la razón por la cual cuando se llama a la función desde el onChange de la lista de páginas los
        valores de vFiltroTexto1 y vFiltroTexto2 vienen "codificados" y no así cuando la función es llamada mediante
        el tag <a href=...>
      */
      ?>
      if (vInicio == -1) {
         vInicio = document.getElementById('Pagina').value
         vFiltroTexto1 = unescape(vFiltroTexto1);
         vFiltroTexto2 = unescape(vFiltroTexto2);
      } 
      if (vInicio == -2) {
         vInicio = 0;
         vCantidad = document.getElementById('CntFilas').value
         vFiltroTexto1 = unescape(vFiltroTexto1);
         vFiltroTexto2 = unescape(vFiltroTexto2);
      }
      parent.Cuerpo.location.href='<?= $_SESSION["pdpIrA"]?>.php?Modulo='+vModulo+'&Orden='+vOrdenCampo+'&Forma='+vOrdenForma+'&CpoFiltro1='+vFiltroCampo1+'&TipFiltro1='+vFiltroTipo1+'&TxtFiltro1='+escape(vFiltroTexto1)+'&NexFiltro='+vFiltroNexo+'&CpoFiltro2='+vFiltroCampo2+'&TipFiltro2='+vFiltroTipo2+'&TxtFiltro2='+escape(vFiltroTexto2)+'&Inicio='+vInicio+'&Cantidad='+vCantidad;
    }
    //]]>
  </script>
</head>

<body bgcolor="#38628A" text="#000000" style="margin:0;">
<form name="fPie" method="post" action="" onsubmit="javascript:Actualizar()">
<table width="100%" border="0" cellspacing="0" cellpadding="5">
  <tr bgcolor="#FFFFFF">
    <td align="center" valign="middle">
      <table width="98%">
        <tr valign="middle">
          <td bgcolor="#929292" align="right" valign="middle"><font size="-7"><?= $_SESSION["pdpAgregar"]?>&nbsp;&nbsp;&nbsp;
            </font></td>
        </tr>
      </table>
    </td>
  </tr>
</table>
<table width="100%" border="0" cellspacing="0" cellpadding="5">
  <tr align="center" valign="middle">
    <td width="10%">
      <table width="90%" border="0" cellspacing="0" cellpadding="0">
        <tr>
          <td style="border:#000000 1px solid" colspan="3" class="Verdana11" bgcolor="#DC9137">&nbsp;&nbsp;<?= $txt['Exportar']?></td>
        </tr>
        <tr>
          <td style="border-bottom:#000000 1px solid; border-right:#000000 1px solid; border-left:#000000 1px solid" width="40%" bgcolor="#D3D3D3" align="center" height="30">
            <?= $_SESSION["pdpExportar"]?>
          </td>
        </tr>
      </table>
    </td>
    <td width="90%">
      <table width="100%" border="0" cellspacing="0" cellpadding="0">
        <tr>
          <td style="border:#000000 1px solid" colspan="8" class="Verdana11" bgcolor="#DC9137">&nbsp;&nbsp;<?= $txt['Filtrar']?></td>
        </tr>
        <tr>
          <td style="border-bottom:#000000 1px solid; border-left: #000000 1px solid" bgcolor="#D3D3D3" valign="middle" height="30">&nbsp;
            <select name="FiltroCampo1" id="FiltroCampo1" size="1" class="Verdana11" style="width:120">
              <?= $_SESSION["pdpCpoFiltro1"]?>
            </select>
          </td>
          <td style="border-bottom:#000000 1px solid" bgcolor="#D3D3D3" valign="middle" height="30">&nbsp;
            <select name="FiltroTipo1" id="FiltroTipo1" size="1" class="Verdana11">
              <?= $_SESSION["pdpTipFiltro1"]?>
            </select>
          </td>
          <td style="border-bottom:#000000 1px solid" bgcolor="#D3D3D3" valign="middle" height="30">&nbsp;
            <input type="text" name="FiltroTexto1" id="FiltroTexto1" class="Verdana11" size="15" value="<?= htmlspecialchars(fSacarBarras($_SESSION["pdpTxtFiltro1"]))?>">
          </td>
          <? if ($_SESSION["gbl".$conf["VariablesSESSION"]."CntFiltros"]==2) { ?>
            <td style="border-bottom: #000000 1px solid" bgcolor="#D3D3D3" valign="middle" height="30">&nbsp;
              <select name="FiltroNexo" id="FiltroNexo" size="1" class="Verdana11">
                <?= $_SESSION["pdpNexFiltro"]?>
              </select>
            </td>
            <td style="border-bottom:#000000 1px solid" bgcolor="#D3D3D3" valign="middle" height="30">&nbsp;
              <select name="FiltroCampo2" id="FiltroCampo2" size="1" class="Verdana11" style="width:120">
                <?= $_SESSION["pdpCpoFiltro2"]?>
              </select>
            </td>
            <td style="border-bottom:#000000 1px solid" bgcolor="#D3D3D3" valign="middle" height="30">&nbsp;
              <select name="FiltroTipo2" id="FiltroTipo2" size="1" class="Verdana11">
                <?= $_SESSION["pdpTipFiltro2"]?>
              </select>
            </td>
            <td style="border-bottom:#000000 1px solid" bgcolor="#D3D3D3" valign="middle" height="30">&nbsp;
              <input type="text" name="FiltroTexto2" id="FiltroTexto2" class="Verdana11" size="15" value="<?= htmlspecialchars(fSacarBarras($_SESSION["pdpTxtFiltro2"]))?>">
            </td>
          <? } else { ?>
            <input type="hidden" name="FiltroNexo" id="FiltroNexo" value="AND" />
            <input type="hidden" name="FiltroCampo2" id="FiltroCampo2" value="" />
            <input type="hidden" name="FiltroTipo2" id="FiltroTipo2" value="" />
            <input type="hidden" name="FiltroTexto2" id="FiltroTexto2" value="" />
          <? } ?>
          <td style="border-bottom:#000000 1px solid; border-right: #000000 1px solid" bgcolor="#D3D3D3" valign="middle" align="center" height="30">
            &nbsp;<input type="submit" class="blanco" name="Aceptar" value="<?= $txt['Aceptar']?>">&nbsp;
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>
<table width="100%" border="0" cellspacing="0" cellpadding="5">
  <tr bgcolor="#929292">
    <td style="border-top:#000000 1px solid; color:#ffffff;" class="Verdana11" align="left">
      <?= $_SESSION["pdpTiempo"]?>
    </td>
    <td style="border-top:#000000 1px solid; color:#ffffff;" class="Verdana11" align="right">
      <?= $_SESSION["pdpPredet"]?>&nbsp;<?= $_SESSION["pdpCntFilas"]?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?= $_SESSION["pdpPagina"]?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?= $_SESSION["pdpPrimera"]?>&nbsp;&nbsp;<?= $_SESSION["pdpAnterior"]?>&nbsp;&nbsp;|&nbsp;&nbsp;<?= $_SESSION["pdpSiguiente"]?>&nbsp;&nbsp;<?= $_SESSION["pdpUltima"]?>
    </td>
  </tr>
</table>
</form>
</body>
</html>