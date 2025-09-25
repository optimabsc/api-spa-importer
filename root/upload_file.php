<?php
    //initialise variables
    $pageTitle = "Upload File";
?>

<!DOCTYPE html>
<html>
    <head>
        <title><?php echo $pageTitle; ?></title>
    </head>
    <body>
        <form action="upload.php" method="post" enctype="multipart/form-data">
            <p>Select file to upload:</p>
            <input type="file" name="uploadFile" id="uploadFile">
            <input type="submit" value="Upload File" name="submit">
        </form>
    </body>
</html>