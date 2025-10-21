<?php
    declare(strict_types=1);

    use Bitrix24\SDK\Services\ServiceBuilderFactory;

    require_once '../vendor/autoload.php';

    //initialise variables
    $pageTitle = "Field Mapping";
    $statusMessage = "";
    $spaEntityTypeId = $_POST['spa_entity_type_id'];
    $selectAccepted = true;
    session_start();
    $webhook = $_SESSION["webhook"];
    $importHeaders = $_SESSION["import_header"];
    $_SESSION["spa_entity_type_id"] = $spaEntityTypeId;

    $b24Service = ServiceBuilderFactory::createServiceBuilderFromWebhook($webhook);

    if($_SERVER['REQUEST_METHOD'] == 'POST' && empty($spaEntityTypeId)) {
        $statusMessage .= "SPA Selection Error: No SPA was selected." . "<br>";
        $selectAccepted = false;
    } else {
        try {
            $response = $b24Service
                ->core
                ->call(
                    'crm.item.fields',
                    [
                        'entityTypeId' => $spaEntityTypeId,
                    ]
                );

            $spaFieldList = $response
                ->getResponseData()
                ->getResult();

            $_SESSION["spa_field_list"] = $spaFieldList;

        } catch (Exception $e) {
            error_log($e->getMessage());
            echo 'Error fetching SPA field list: ' . $e->getMessage();
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
        <form action=<?php if ($selectAccepted) {echo "import.php";} else {echo "select.php";} ?> method="post">
            <?php
                if ($selectAccepted) {
                    echo "<p>Map the import file columns to the SPA fields:</p>";
                    echo "<table border='0'><tr><th>Import File Column</th><th>SPA Field Mapping</th></tr>";
                    foreach ($importHeaders as $importColumnName) {
                        echo "<tr>";
                        echo "<td>" . $importColumnName . "</td>";
                        echo "<td>";
                        echo "<select name='" . $importColumnName  . "' id='" . $importColumnName  . "'>";
                        echo "<option value=''>-- select SPA field --</option>";
                        foreach ($spaFieldList['fields'] as $fieldCode => $fieldDetails) {
                            echo "<option value='" . $fieldCode . "'>" . $fieldDetails['title'] . " [" . $fieldDetails['type'] . "]</option>";
                        }                        
                        echo "</select>";
                        echo "</td>";
                        echo "</tr>";
                    }
                    echo "</table>";
                    echo "<br>";
                } else {
                    echo "<p>" . $statusMessage . "</p>";
                }
            ?>
            <input type="submit" value=<?php if ($selectAccepted) {echo "Import";} else {echo "Back";} ?>>
        </form>
    </body>
</html>