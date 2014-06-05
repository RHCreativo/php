/**
 * Abre un anueva ventana con dimensiones específicas.
 **/
function Abrir(Pagina,Tipo,Ancho,Alto) {

  if (Tipo=='MasInfo') {
    Ancho  = 600 ;
    Alto   = 420 ;
    Arriba = 100 ;
    Izquie = 100 ;
    cCambioTam = 'no' ;
  } else if (Tipo=='InfoRel') {
    Ancho  = screen.width*0.8 ;
    Alto   = 420 ;
    Arriba = 25 ;
    Izquie = 50 ;
    cCambioTam = 'no' ;
  } else if (Tipo=='Registro') {
    Ancho  = 800 ;
    Alto   = screen.height-150 ;
    Arriba = 25 ;
    Izquie = 50 ;
    cCambioTam = 'no' ;
  } else if (Tipo=='Acciones') {
    Ancho  = Ancho ;
    Alto   = Alto ;
    Arriba = 100 ;
    Izquie = 100 ;
    cCambioTam = 'yes' ;
  } else if (Tipo=='Imagen') {
    Ancho  = Ancho+20 ;
    Alto   = Alto+20 ;
    Arriba = 100 ;
    Izquie = 100 ;
    cCambioTam = 'no' ;
  } else if (Tipo=='Documento') {
    Ancho  = screen.width*0.8 ;
    Alto   = screen.height*0.8 ;
    Arriba = screen.height*0.1 ;
    Izquie = screen.width*0.1 ;
    cCambioTam = 'yes' ;
  }
  window.open(Pagina,'','toolbar=no,location=no,directories=no,status=no,menubar=no,resizable='+cCambioTam+',copyhistory=no, scrollbars=yes,width='+Ancho+',height='+Alto+',top='+Arriba+',left='+Izquie);
}



/**
 * Muestra / Oculta layers en el caso de listas con valores "ampliables"
 * 
 * Controlar compatibilidad entre browsers y ver si es posible
 *   reemplazar esta utilidad agregando filas "on-the-fly"
 **/
function setVisibility (id, visibility) {
  if (document.layers)
    document[id].visibility = visibility == 'visible' ? 'show' : 'hidden';
  else if (document.all)
    document.all[id].style.visibility = visibility;
  else if (document.getElementById)
    document.getElementById(id).style.visibility = visibility;
}



/**
 * Funciones para manejo de Cookies
 **/
function createCookie(name,value,days) {
  if (days) {
    var date = new Date();
    date.setTime(date.getTime()+(days*24*60*60*1000));
    var expires = "; expires="+date.toGMTString();
  }
  else var expires = "";
  document.cookie = name+"="+value+expires+"; path=/";
}

function readCookie(name) {
  var nameEQ = name + "=";
  var ca = document.cookie.split(';');
  for(var i=0;i < ca.length;i++) {
    var c = ca[i];
    while (c.charAt(0)==' ') c = c.substring(1,c.length);
    if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
  }
  return null;
}

function eraseCookie(name) {
  createCookie(name,"",-1);
}



/**
 * Controla que el NroCUIT sea correcto
 **/
function ControlCUIT ( NroCUIT ) {

  var nNroCUIT = replaceSubstring(NroCUIT, '-', '') ;
  var i, j, k = 0 ;

  if ( NroCUIT.length!=13 || NroCUIT.substr( 2,1)!='-' || NroCUIT.substr(11,1)!='-' || isNaN(nNroCUIT)) {
    return (false);
  }

  j = 2 ;
  for (i=9; i>=0; i--) {
    k = k + parseInt(nNroCUIT.substr( i,1))*j ;
    j++ ;
    if ( j==8 ) {
      j = 2;
    }
  }
  k = k % 11
  if (k!=0) {
    k = 11-k
  }

  if ( k == parseInt(nNroCUIT.substr(10,1)) ) {
    return (true);
  } else {
    return (false);
  }

}



/**
 * Controla las pulsaciones de tecla para permitir solo números,
 *   signos menos y puntos decimales
 **/
function keyCheck(eventObj, obj, tipo, decimales) {
  var keyCode ;

  // Check for Browser Type
  if (document.all) {
    keyCode = eventObj.keyCode ;
  } else {
    keyCode = eventObj.which ;
  }

  var str=obj.value

  if (tipo=="D") {
    if ((keyCode<48 || keyCode>57) && (keyCode!=45) && (keyCode!=46)) {
      return false ;
    }
    if ( (str.indexOf(".")>=0) && (str.length-str.indexOf(".")>decimales) ) {
      return false ;
    }
    if ( (keyCode==45) && (str.length!=0) ) {
      return false ;
    }
  } else {
    if (keyCode<48 || keyCode>57) {
      return false ;
    }
  }

  return true ;
}



/**
 * Reemplaza dento de inputString cada ocurrencia de 
 *   fromString por toString
 **/
function replaceSubstring(inputString, fromString, toString) {
   // Goes through the inputString and replaces every occurrence of fromString with toString
   var temp = inputString;
   if (fromString == "") {
      return inputString;
   }
   if (toString.indexOf(fromString) == -1) { // If the string being replaced is not a part of the replacement string (normal situation)
      while (temp.indexOf(fromString) != -1) {
         var toTheLeft = temp.substring(0, temp.indexOf(fromString));
         var toTheRight = temp.substring(temp.indexOf(fromString)+fromString.length, temp.length);
         temp = toTheLeft + toString + toTheRight;
      }
   } else { // String being replaced is part of replacement string (like "+" being replaced with "++") - prevent an infinite loop
      var midStrings = new Array("~", "`", "_", "^", "#");
      var midStringLen = 1;
      var midString = "";
      // Find a string that doesn't exist in the inputString to be used
      // as an "inbetween" string
      while (midString == "") {
         for (var i=0; i < midStrings.length; i++) {
            var tempMidString = "";
            for (var j=0; j < midStringLen; j++) { tempMidString += midStrings[i]; }
            if (fromString.indexOf(tempMidString) == -1) {
               midString = tempMidString;
               i = midStrings.length + 1;
            }
         }
      } // Keep on going until we build an "inbetween" string that doesn't exist
      // Now go through and do two replaces - first, replace the "fromString" with the "inbetween" string
      while (temp.indexOf(fromString) != -1) {
         var toTheLeft = temp.substring(0, temp.indexOf(fromString));
         var toTheRight = temp.substring(temp.indexOf(fromString)+fromString.length, temp.length);
         temp = toTheLeft + midString + toTheRight;
      }
      // Next, replace the "inbetween" string with the "toString"
      while (temp.indexOf(midString) != -1) {
         var toTheLeft = temp.substring(0, temp.indexOf(midString));
         var toTheRight = temp.substring(temp.indexOf(midString)+midString.length, temp.length);
         temp = toTheLeft + toString + toTheRight;
      }
   } // Ends the check to see if the string being replaced is part of the replacement string or not
   return temp; // Send the updated string back to the user
} // Ends the "replaceSubstring" function



/**
 * Set de funciones para validar los números de 
 *   tarjeta de crédito
 **/
function allDigits(str) {
  return inValidCharSet(str,"0123456789");
}

function inValidCharSet(str,charset) {
  var result = true;

  for (var i=0;i<str.length;i++)
    if (charset.indexOf(str.substr(i,1))<0)                 {
      result = false;
      break;
    }

  return result;
}

function LuhnCheck(str) {
  var result = true;

  var sum = 0;
  var mul = 1;
  var strLen = str.length;

  for (i=0; i<strLen; i++) {
    var digit    = str.substring(strLen-i-1,strLen-i);
    var tproduct = parseInt(digit ,10)*mul;

    if (tproduct >= 10)
      sum += (tproduct % 10) + 1;
    else
      sum += tproduct;

    if (mul == 1)
      mul++;
    else
      mul--;
  }
  if ((sum % 10) != 0)
    result = false;

  return result;
}

function validateCCNum(cardType,cardNum) {
  var result = false;
  cardType = cardType.toUpperCase();

  var cardLen    = cardNum.length;
  var firstdig   = cardNum.substring(0,1);
  var seconddig  = cardNum.substring(1,2);
  var first4digs = cardNum.substring(0,4);

  switch (cardType) {
    case "VISA":
      result = ((cardLen == 16) || (cardLen == 13)) && (firstdig == "4");
      break;
    case "AMEX":
      var validNums = "47";
      result = (cardLen == 15) && (firstdig == "3") && (validNums.indexOf(seconddig)>=0);
      break;
    case "MASTERCARD":
      var validNums = "12345";
      result = (cardLen == 16) && (firstdig == "5") && (validNums.indexOf(seconddig)>=0);
      break;
    case "DISCOVER":
      result = (cardLen == 16) && (first4digs == "6011");
      break;
    case "DINERS":
      var validNums = "068";
      result = (cardLen == 14) && (firstdig == "3") && (validNums.indexOf(seconddig)>=0);
      break;
  }
  return result;
}
