<?php include('main.php') ?>
<!DOCTYPE HTML>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Mah Parser</title>
    <link href="style.css" rel="stylesheet" type="text/css">
    <script type="text/javascript">
        function toggleDiv() {
            if (document.getElementById("LoginBox").style.display == "block") {
                document.getElementById("LoginBox").style.display = "none";
            }
            else {
                document.getElementById("LoginBox").style.display = "block";
            }
        }
        function SelectAll(id)
        {
            document.getElementById(id).focus();
            document.getElementById(id).select();
        }
    </script>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="logo/header.png" id="logo"/>
        </div>
        <div>
            <table id="box" align=right>
                <tr>
                    <td>
                        <h4>Information</h4>
                        <?php echo $stat ?>
                    </td>
                </tr>
                <tr>
                    <td>
                        <h4 id=log><?php if (isset($log)) echo "Log"; ?></h4>
                        <div id=logcont><?php echo $log ?></div>
                        <input type="button" value="Change DB" onclick="toggleDiv()" class="fancy" />
                        <div id="LoginBox" style="display:none;">
                            <form method="post" />
                                TODO<br />
                                <input type="text" name="server" placeholder="Server" /><br />
                                <input type="text" name="user" placeholder="Username" /><br />
                                <input type="text" name="pass" placeholder="Password" /><br />
                                <input type="text" name="db" placeholder="World Database" /><br />
                        </div>
                    </td>
                </tr>
                    <td><i><small><br />
                    Initial work by Dark0r, [M]axx; Re-wrote and improved by Nay - Smth&trade; 2010</small></i>
                    </td>
            </table>
        </div>
        <div class="content">
            <form action="" enctype="multipart/form-data" method="post" name="form">
                <table>
                    <tr>
                        <td>Input:</td>
                    </tr>
                    <tr>
                        <td>
                            <textarea name="formdata[blockdata]"><?php echo $formData["blockdata"]; ?></textarea>
                        </td>
                    </tr>
                    <tr>
                        <td><input type="submit" name="formdata[send]" id="save" class="fancy"/></td>
                    </tr>
                    <?php if (!empty($result)) { ?>
                    <tr>
                        <td>Result:</td>
                    </tr>
                    <tr>
                        <td>
                            <div class="content" id="txt"><?php echo $sqlres->parse_code(); $dbh = null;?></div>
                        </td>
                    <?php } ?>
                    </tr>
                </table>
            </form>
        </div>
    </div>
</body>
</html>