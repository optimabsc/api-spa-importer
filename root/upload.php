<?php
    //initialise variables
    $pageTitle = "File Upload Status";
    $statusMessage = "";
    $uploadAccepted = true;
    $regexB24Webhook = "/^https:\/\/[A-Za-z0-9_-]+\.bitrix24\.com\/rest\/[0-9]\/[a-z0-9]+\/$/";
    
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        //initialise variables
        $targetDir = "../var/tmp/";
        $targetFile = $targetDir . basename($_FILES["uploadFile"]["name"]);
        $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
        $maxFileSize = 256000000; //256Mb        

        //check if webhook is provided and valid
        if (empty($_POST["webhook"]) || !preg_match($regexB24Webhook, $_POST["webhook"])) {
            $statusMessage .= "Webhook error: webhook is empty or invalid." . "<br>";
            $uploadAccepted = false;
        } else {
            //save webhook to session
            session_start();
            $_SESSION["webhook"] = $_POST["webhook"];
        }

        //check if file was provided
        if (empty($_FILES["uploadFile"]["name"])) {
            $statusMessage .= "Upload failed: no file was provided." . "<br>";
            $uploadAccepted = false;
        } else {
            //check if file already exists
            if (file_exists($targetFile)) {
                $statusMessage .= "Upload failed: file already exists." . "<br>";
                $uploadAccepted = false;
            }

            //check file size
            if ($_FILES["uploadFile"]["size"] > $maxFileSize) {
                $statusMessage .= "Upload failed: file is too large (>" . $maxFileSize . " bytes)." . "<br>";
                $uploadAccepted = false;
            }

            //check file types (csv only)
            if ($fileType != "csv") {
                $statusMessage .= "Upload failed: only CSV files can be uploaded." . "<br>";
                $uploadAccepted = false;
            }

            //check if file can be opened
            if ($handle = fopen($_FILES["uploadFile"]["tmp_name"], "r")) {
                $header = fgetcsv($handle, 0, ",");
                
                //check if file is valid csv (has header row)
                if($header === false || count($header) < 1) {
                    $statusMessage .= "Upload failed: file is empty or invalid." . "<br>";
                    $uploadAccepted = false;
                }
                
                //check column consistency across rows
                while (($row = fgetcsv($handle, 0, ",")) !== false) {
                    if (count($row) != count($header)) {
                        $statusMessage .= "Upload failed: inconsistent number of columns in file." . "<br>";
                        $uploadAccepted = false;
                        break;
                    }
                }

                //close file if upload is accepted
                if ($uploadAccepted) {
                    fclose($handle);
                }
            } else {
                $statusMessage .= "Upload failed: file cannot be opened." . "<br>";
                $uploadAccepted = false;
            }
        }

        //final upload status message
        if ($uploadAccepted) {
            if (move_uploaded_file($_FILES["uploadFile"]["tmp_name"], $targetFile)) {
                $statusMessage .= "The file ". htmlspecialchars(basename($_FILES["uploadFile"]["name"])). " has been uploaded.";
                //save file path and header to session
                //$_SESSION["import_file"] = $targetFile;
                $_SESSION["import_header"] = $header;
            } else {
                $statusMessage .= "Upload failed: there was an error uploading the file.";
            }
        }
    }
?>

<!DOCTYPE html>
<html>
    <head>
        <title><?php echo "Opitma Bitrix24 SPA Importer | " . $pageTitle; ?></title>
    </head>
    <body>
        <h1><?php echo $pageTitle; ?></h1>
        <p><?php echo $statusMessage; ?></p>
        <form action="<?php if ($uploadAccepted) {echo "select.php";} else {echo "index.php";} ?>" method="post">
            <input type="submit" value="<?php if($uploadAccepted) {echo "Next";} else {echo "Back";} ?>" name="submit">
        </form>
    </body>
</html>