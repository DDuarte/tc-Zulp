<?php include 'main.php'; ?>
<!DOCTYPE HTML>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>ZULP</title>
    <link href="style.css" rel="stylesheet" type="text/css">
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
                        <h4 id=log><?php if ($log != '') echo "Log"; ?></h4>
                        <div id=logcont><?php echo $log ?></div>
                    </td>
                   
                </tr>
                <td><i><small><br />&copy; Nay</small></i></td>
            </table>
        </div>
        <div class="content">
            <form action="" enctype="multipart/form-data" method="post" name="form">
                <table>
                    <tr>
                        <td>Input:</td>
                    </tr>
                    <tr>
                        <td><textarea name="formdata[blockdata]"><?php echo $formData["blockdata"]; ?></textarea></td>
                    </tr>
                    <tr>
                        <td><input type="submit" name="formdata[send]" id="save" class="fancy"/></td>
                    </tr>
                    <?php if (!empty($result)) { ?>
                    <tr>
                        <td>Result:</td>
                    </tr>
                    <tr>
                        <td><div class="content" id="txt"><?php echo $sqlres->parse_code(); $dbh = null;?></div></td>
                    <?php } ?>
                    </tr>
                </table>
            </form>
        </div>
    </div>
</body>
</html>