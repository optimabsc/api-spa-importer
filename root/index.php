<?php
    //includes and initialisation
    $pageTitle = "Webhook and File Upload";

    require_once './includes/functions.php';
    loadEnv('../config/.env-b24');
    $webhook = $_ENV['WEBHOOK_CRM'];
?>

<!DOCTYPE html>
<html>
    <head>
        <title><?php echo "Optima Bitrix24 SPA Importer | " . $pageTitle; ?></title>
    </head>
    <body>
        <h1><?php echo $pageTitle; ?></h1>
        <form action="upload.php" method="post" enctype="multipart/form-data">
            <p>Enter Bitrix24 inbound webhook:<br>
            <input type="password" name="webhook" id="webhook" size="50" value=<?php echo $webhook; ?>></p>
            <p>Select file to upload:<br>
            <input type="file" name="uploadFile" id="uploadFile"></p>
            <input type="submit" value="Submit" name="submit">
        </form>
    </body>
</html>