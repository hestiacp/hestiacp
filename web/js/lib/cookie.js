var today       = new Date();
var expiryyear  = new Date(today.getTime() + 365 * 24 * 60 * 60 * 1000);
var expirymonth = new Date(today.getTime() + 30 * 24 * 60 * 60 * 1000);
var expiryday   = new Date(today.getTime() + 24 * 60 * 60 * 1000);

function getCookie(name)
{
 var reg= new RegExp("; "+name+";|; "+name+"=([^;]*)");
 var matches=reg.exec('; '+document.cookie+';');
 if (matches) return ((matches[1])?unescape(matches[1]):'');
 return null;
}

function setCookie (name,value,expires,path,domain,secure)
{
	document.cookie = name + "=" + escape (value) +
	((expires) ? "; expires=" + expires.toGMTString() : "") +
	((path) ? "; path=" + path : "") +
	((domain) ? "; domain=" + domain : "") +
	((secure) ? "; secure" : "");

	return getCookie(name)!=null?true:false;
}

function deleteCookie (name,path,domain)
{
	if (getCookie(name)!=null)
	{
		document.cookie = name + "=" +
		((path) ? "; path=" + path : "") +
		((domain) ? "; domain=" + domain : "") +
		"; expires=Thu, 01-Jan-1970 00:00:01 GMT";
	}
}

function cookieEnabled()
{
	testCookieName="_testCookie_";
	if (setCookie(testCookieName,1))
	{
		deleteCookie(testCookieName);
		return true;
	}
	else return false;
}