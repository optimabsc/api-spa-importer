<?php
    require_once './includes/functions.php';

    //initialize sample data
    $sampleColumnData = "Value1, Value2, Value3, Value4";

    //convert CSV column data to array
    $resultArray = csvColumnToArray($sampleColumnData);

    //display result
    echo "<pre>";
    print_r($resultArray);
    echo "</pre>";
?>