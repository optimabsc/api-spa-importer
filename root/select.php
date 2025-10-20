<?php
    declare(strict_types=1);

    use Bitrix24\SDK\Services\ServiceBuilderFactory;

    require_once '../vendor/autoload.php';

    //initialise variables
    $pageTitle = "Select SPA";
    $statusMessage = "";
    session_start();
    $webhook = $_SESSION["webhook"];    

    $b24Service = ServiceBuilderFactory::createServiceBuilderFromWebhook($webhook);

    try {
        $response = $b24Service
            ->core
            ->call(
                'crm.type.list',
                [
                    'order' => [
                        'id' => 'ASC',
                    ],                    
                ]
            );

        $spaList = $response
            ->getResponseData()
            ->getResult();
            
    } catch (Exception $e) {
        error_log($e->getMessage());
        echo 'Error fetching SPA list: ' . $e->getMessage();
    }
?>

<!DOCTYPE html>
<html>
    <head>
        <title><?php echo "Optima Bitrix24 SPA Importer | " . $pageTitle; ?></title>
    </head>
    <body>
        <h1><?php echo $pageTitle; ?></h1>
        <form action="mapping.php" method="post">
            <p>Select SPA for import:</p>
            <p><select name="spa_entity_type_id" id="spa_entity_type_id">
                <option value="">-- select SPA --</option>
                <?php
                    foreach ($spaList['types'] as $row) {
                        echo '<option value=' . $row['entityTypeId'] . '>' . $row['title'] . ' [TypeID: ' . $row['entityTypeId'] . ']</option>';
                    }
                ?>
            </select></p>
            <input type="submit" value="Select SPA" name="submit">
        </form>

        <?php //debugging
            echo "<br>SESSION contents:<br>";
            echo "<pre>";
            echo json_encode($_SESSION, JSON_PRETTY_PRINT);
            echo "</pre>";
        ?>

    </body>
</html>