<script type="text/javascript">
var checked=false;
var frmname='';
function checkedAll(frmname)
{
    var valus= document.getElementById(frmname);
    if (checked==false)
    {
	checked=true;
    }
    else
    {
	checked = false;
    }
    for (var i =0; i < valus.elements.length; i++) 
    {
	valus.elements[i].checked=checked;
    }
}
</script>
<form id ="cbexample">
<input type="checkbox" name="chk1">Apple
<input type="checkbox" name="chk2">Banana
<a onclick='checkedAll("cbexample");'>Select All</a>
</form>
