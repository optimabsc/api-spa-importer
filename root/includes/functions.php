<?php
    //cleanse input data
    function cleanse_input_data($data){
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }

    //.env loader
    function loadEnv($filePath) {
        if (!file_exists($filePath)) {
            throw new Exception("Environment file not found: $filePath");
        }

        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (str_starts_with(trim($line), '#')) continue; // Skip comments
            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            $_ENV[$key] = $value;
        }
    }

    //convert multiple values in csv column delimited with commas to array
    function csvColumnToArray($columnData) {
        $items = explode(',', $columnData);
        $trimmedItems = array_map('trim', $items);
        return $trimmedItems;
    }

?>
