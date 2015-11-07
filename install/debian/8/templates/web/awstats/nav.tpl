<html>
<head>
    <title>Awstats navigation</title>
    <script language="javascript">
        function change() {
            top.stats.location= document.period.select.value + '/';
        }
    </script>
</head>
<body>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
<tr>
    <td><img src="logo.png" alt="vesta"></td>
    <td><form name="period" action="" method="get">
        <select name="select" ONCHANGE="change()">
%select_month%
        </select>
    </form>
    </td>
</tr>
</table>
</body>
</html>
