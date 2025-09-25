<?php
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        //initialise variables
        $targetDir = "../var/tmp/";
        $targetFile = $targetDir . basename($_FILES["uploadFile"]["name"]);
        $uploadAccepted = true;
        $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
        $maxFileSize = 256000000; //256Mb

        //check if file already exists
        if (file_exists($targetFile)) {
            echo "Upload failed: file already exists." . "<br>";
            $uploadAccepted = false;
        }

        //check file size (500KB max)
        if ($_FILES["uploadFile"]["size"] > $maxFileSize) {
            echo "Upload failed: file is too large (>" . $maxFileSize . " bytes)." . "<br>";
            $uploadAccepted = false;
        }

        //check file types (csv only)
        if ($fileType != "csv") {
            echo "Upload failed: only CSV files can be uploaded." . "<br>";
            $uploadAccepted = false;
        }

        //check if file can be opened
        if ($handle = fopen($_FILES["uploadFile"]["tmp_name"], "r")) {
            $header = fgetcsv($handle, 0, ",");
            
            //check if file is valid csv (has header row)
            if($header === false || count($header) < 1) {
                echo "Upload failed: file is empty or invalid." . "<br>";
                $uploadAccepted = false;
            }
            
            //check column consistency across rows
            while (($row = fgetcsv($handle, 0, ",")) !== false) {
                if (count($row) != count($header)) {
                    echo "Upload failed: inconsistent number of columns in file." . "<br>";
                    $uploadAccepted = false;
                    break;
                }
            }

            //close file if upload is accepted
            if ($uploadAccepted) {
                fclose($handle);
            }
        } else {
            echo "Upload failed: file cannot be opened." . "<br>";
            $uploadAccepted = false;
        }

        //check if upload is acceptable
        if (!$uploadAccepted) {
            echo "<a href=\"upload_file.php\">Try again.</a>";
        } else {
            if (move_uploaded_file($_FILES["uploadFile"]["tmp_name"], $targetFile)) {
                echo "The file ". htmlspecialchars(basename($_FILES["uploadFile"]["name"])). " has been uploaded.";
            } else {
                echo "Upload failed: there was an error uploading the file.";
            }
        }
    }
?>