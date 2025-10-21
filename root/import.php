<?php
    declare(strict_types=1);

    use Bitrix24\SDK\Services\ServiceBuilderFactory;

    require_once '../vendor/autoload.php';

    //initialise variables
    $pageTitle = "Import Status";
    $statusMessage = "";
    $importAccepted = true;
    $importSucceeded = true;
    session_start();
    $webhook = $_SESSION["webhook"];
    $importFile = $_SESSION["import_file"];
    $spaFieldList = $_SESSION["spa_field_list"];
    $spaEntityTypeId = $_SESSION["spa_entity_type_id"];
    $fieldMapping = $_POST;

    $b24Service = ServiceBuilderFactory::createServiceBuilderFromWebhook($webhook);

    if($_SERVER['REQUEST_METHOD'] == 'POST') {
        //check for empty mappings
        foreach ($fieldMapping as $importColumn => $spaField) {
            if (empty($spaField)) {
                $statusMessage = "Import Error: All import columns must be mapped to SPA fields." . "<br>";
                $importAccepted = false;
                break;
            }
        }

        //check for duplicate SPA field mappings
        $mappedFields = array_values($fieldMapping);
        if (count($mappedFields) !== count(array_unique($mappedFields))) {
            $statusMessage = "Import Error: Duplicate SPA field mappings detected." . "<br>";
            $importAccepted = false;
        }

        if ($importAccepted) {
            $statusMessage = "Import started..." . "<br>";

            //open import file
            if (($handle = fopen($importFile, "r")) !== false) {
                $header = fgetcsv($handle, 0, ",");

                //remove spaces from header names
                for ($i = 0; $i < count($header); $i++) {
                    $header[$i] = str_replace(' ', '_', $header[$i]);
                }

                //read each row and prepare for import
                while (($data = fgetcsv($handle, 0, ",")) !== false) {
                    $row = [];
                    foreach ($fieldMapping as $importColumn => $spaField) {
                        $columnIndex = array_search($importColumn, $header);
                        if ($columnIndex !== false) {
                            //trim spaces, new lines, tabs and double quotes from data
                            $data[$columnIndex] = trim($data[$columnIndex], " \t\n\r\"\0\x0B");

                            if ($spaFieldList['fields'][$spaField]['isMultiple'] === true) {
                                //multiple value fields
                                $dataArray = [];
                                $dataArray = array_map('trim', explode('|', $data[$columnIndex]));
                                $row[$spaField] = $dataArray;
                            } else {
                                //single value fields
                                $row[$spaField] = $data[$columnIndex];
                            }
                        }
                    }

                    //write data to Bitrix24
                    try {
                        $response = $b24Service
                            ->core
                            ->call(
                                'crm.item.add',
                                [
                                    'entityTypeId' => $spaEntityTypeId,
                                    'fields' => $row,
                                    'params' => [
                                        'REGISTER_SONET_EVENT' => 'N'
                                    ]
                                ]
                            );

                    } catch (Exception $e) {
                        error_log($e->getMessage());
                        $statusMessage .= "Import failed: Data could not be imported; " . $e . "<br>";
                        $importSucceeded = false;
                        break;
                    }
                }

                if($importSucceeded) {
                    $statusMessage .= "Import completed successfully." . "<br>";
                }

                //delete temporary import file
                fclose($handle);
                if (file_exists($importFile)) {
                    if (unlink($importFile)) {
                        $statusMessage .= "Temporary import file deleted." . "<br>";
                    } else {
                        $statusMessage .= "Warning: could not delete temporary import file." . "<br>";
                    }
                } else {
                    $statusMessage .= "Warning: temporary import file not found for deletion." . "<br>";
                }
            } else {
                $statusMessage .= "Import failed: couldn't open file." . "<br>";
                $importSucceeded = false;
            }
        }
    }
?>

<!DOCTYPE html>
<html>
    <head>
        <title><?php echo "Optima Bitrix24 SPA Importer | " . $pageTitle; ?></title>
    </head>
    <body>
        <h1><?php echo $pageTitle; ?></h1>
        <p><?php echo $statusMessage; ?></p>
        <form action=<?php if ($importAccepted && $importSucceeded) {echo "index.php";} else {echo "mapping.php";} ?> method="post">
            <input type="submit" value='<?php if ($importAccepted && $importSucceeded) {echo "New Import";} else {echo "Back";} ?>'>
        </form>
    </body>
</html>