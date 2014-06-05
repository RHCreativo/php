/**********************************************************************
 * Software:	JS Embedded Content
 * Versión:	1.1 Final (Oct 13, 2006)
 * Autor:	Raspu (donraspu arroba gmail punto com)
 * Copyleft 2006, Raspu - Algunos derechos reservados. 
 *
 * "JS Embedded Content" es software libre. Se otorga permiso para copiar,
 * distribuir y/o modificar este programa bajo los términos de la Licencia
 * Pública General de GNU, versión 2.0 o cualquier otra versión posterior
 * (a su elección) publicada por la Free Software Foundation.
 * 
 * Puedes consultar una copia de la licencia en http://www.gnu.org/copyleft/gpl.html
 *
 *
 * DESCRIPCIÓN:
 * ------------
 * "JS Embedded Content" es una pequeña aplicación desarrollada
 * mediante Javascript no intrusivo ni obstructivo, que permite la
 * activación automática de todos los elementos incrustados en un
 * documento HTML mediante las etiquetas OBJECT, EMBED y/o APPLET.
 *
 * NAVEGADORES COMPATIBLES:
 * ------------------------
 * - Ópera 9 final o superior (para versiones anteriores no es requerido)
 * - Internet Explorer 5 o superior (Windows, en MAC no he probado)
 * El resto de navegadores no es compatible debido al uso outerHTML,
 * pero como por el momento no lo requieren no es mayor problema.
 *
 * INSTRUCCIONES DE USO:
 * ---------------------
 * Sólo debes incluir el archivo "embeddedcontent.js" colocando el siguiente 
 * código entre las etiquetas <HEAD> y </HEAD> de tus documentos HTML:
 * 
 * <script type="text/javascript" src="embeddedcontent.js" defer="defer"></script>
 *
 * NOTA: el uso del atributo DEFER es imprescindible para poder emular en 
 * I. Explorer el evento DOMContentLoaded.
 **********************************************************************/
var embeddedContent = 
{

	isMSIE : (document.all && !window.opera) ? true : false,
	
	
	/**
	 * Reinserta en el documento HTML los elementos que han sido incrustados mediante
	 * las etiquetas OBJECT, EMBED y/o APPLET, redefiniendo su propiedad outerHTML
	 */
	reinsertContent : function()
	{	
	var totalNodes = new Array(3);
		totalNodes['OBJECT'] = document.getElementsByTagName('OBJECT').length;
		totalNodes['EMBED'] = document.getElementsByTagName('EMBED').length;
		totalNodes['APPLET'] = document.getElementsByTagName('APPLET').length;
		for(var tagName in totalNodes)
		{
			var counter = totalNodes[tagName] - 1;
			for(var node; node = document.getElementsByTagName(tagName)[counter]; counter--)
			{
				sourceCode = embeddedContent.getSourceCode(node);
				if(sourceCode)
				{
					node.outerHTML = sourceCode;
				}
			}
		}
		embeddedContent.isMSIE = null;
	},
	
	
	/**
	 * Obtiene el código HTML completo de un determinado nodo.
	 * @param	node (object) - El nodo analizado
	 * @return	sourceCode (string) - El código HTML obtenido
	 */
	getSourceCode : function(node)
	{
		var sourceCode = node.outerHTML;
		switch(node.nodeName)
		{
			case 'EMBED':
				return sourceCode;
			break;
			case 'OBJECT':
			case 'APPLET':
				var openTag = sourceCode.substr(0, sourceCode.indexOf('>') + 1).toLowerCase();
				var closeTag = sourceCode.substr(sourceCode.length - 9).toLowerCase();
				if(closeTag != '</object>' && closeTag != '</applet>')
				{
					/* Si el nodo está mal formado (etiquetas de apertura y cierre) se debe 
					anular el script ya que podría devolver un resultado incorrecto */
					return null;
				}
				if(embeddedContent.isMSIE)
				{
					/* Para I. Explorer se debe obtener aparte el código HTML de los nodos hijos,
					ya que la propiedad outerHTML en ocasiones devolverá un resultado incompleto */
					var innerCode = embeddedContent.getInnerCode(node);
					sourceCode = openTag + innerCode + closeTag;
				}
				return sourceCode;
			break;
		}
	},
	
	
	/**
	 * Obtiene el código HTML de los nodos hijos de un determinado nodo. No se debe utilizar directamente
	 * la propiedad innerHTML ya que en ciertos casos I. Explorer devolverá un resultado incompleto.
	 * @param	node (object) - El nodo padre que será analizado
	 * @return	innerCode (string) - El código HTML obtenido
	 */
	getInnerCode : function(node)
	{
		var innerCode = '';
		var totalChilds = node.childNodes.length - 1;
		for(var counter = totalChilds, child; child = node.childNodes[counter]; counter--)
		{
			innerCode += child.outerHTML;
		}
		return innerCode;
	}
	
}


/**
 * Activación:
 * -----------
 * Detectamos el soporte de attachEvent() como filtro para I. Explorer y Ópera.
 * Para ejecutar el script sólo necesitamos que el DOM (Document Object Model); para
 * ello en Ópera recurrimos al evento DOMContentLoaded, mientras que en I. Explorer
 * emulamos dicho evento apoyándonos en el atributo DEFER de la etiqueta SCRIPT
 * con la que estamos cargando este archivo.
 */
if(document.attachEvent)
{
	if(window.opera)
	{
		document.attachEvent("DOMContentLoaded", embeddedContent.reinsertContent);
	}
	else
	{
		embeddedContent.reinsertContent();
	}
}