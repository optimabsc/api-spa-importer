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
    $importHeaders = $_SESSION["import_header"];
    $importFile = $_SESSION["import_file"];
    //$importFilename = basename($importFile);
    $spaEntityTypeId = $_SESSION["spa_entity_type_id"];
    $fieldMapping = $_POST;
    $row = [];

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
            $statusMessage = "Import starting..." . "<br>";

            //open import file
            //$importFilePath = "../var/tmp/" . $_SESSION["import_file"];
            //$importFile = $_SESSION["import_file"];
            if (($handle = fopen($importFile, "r")) !== false) {
                $header = fgetcsv($handle, 0, ",");

                //read each row and prepare for import
                while (($data = fgetcsv($handle, 0, ",")) !== false) {
                    $row = [];
                    foreach ($fieldMapping as $importColumn => $spaField) {
                        $columnIndex = array_search($importColumn, $header);
                        if ($columnIndex !== false) {
                            $row[$spaField] = $data[$columnIndex];
                        }
                    }

                    //write import to Bitrix24
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
        <title><?php echo "Opitma Bitrix24 SPA Importer | " . $pageTitle; ?></title>
    </head>
    <body>
        <h1><?php echo $pageTitle; ?></h1>
        <p><?php echo $statusMessage; ?></p>
        <form action=<?php if ($importAccepted && $importSucceeded) {echo "index.php";} else {echo "mapping.php";} ?> method="post">
            <input type="submit" value='<?php if ($importAccepted && $importSucceeded) {echo "New Import";} else {echo "Back";} ?>'>
        </form>

        <?php
            foreach ($fieldMapping as $importColumn => $spaField) {
                echo "<p>Import Column: " . htmlspecialchars($importColumn) . " => SPA Field: " . htmlspecialchars($spaField) . "</p>";
            }  
            echo "<pre>";
            print_r($fieldMapping);
            echo "</pre>";
        ?>
    </body>
</html>