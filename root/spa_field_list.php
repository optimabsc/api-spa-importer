<?php
    declare(strict_types=1);

    use Bitrix24\SDK\Services\ServiceBuilderFactory;

    require_once './includes/functions.php';
    require_once '../vendor/autoload.php';

    loadEnv('../config/.env-b24');
    //$webhook = $_ENV['WEBHOOK_CRM'];
    $webhook = $_ENV['WEBHOOK_CRM_DALY'];
    $entityTypeId = 1060; //spa entity type id to be provided by input form

    $b24Service = ServiceBuilderFactory::createServiceBuilderFromWebhook($webhook);

    try {
        $response = $b24Service
            ->core
            ->call(
                'crm.item.fields',
                [
                    'entityTypeId' => $entityTypeId,
                ]
            );

        $spaFieldList = $response
            ->getResponseData()
            ->getResult();
            
        echo 'Success' . '<br><br>';
        echo 'Field List for SPA EntityTypeID = ' . $entityTypeId . ':' . '<br>';

        foreach ($spaFieldList['fields'] as $spaFieldName => $spaFieldData) {
            echo '- ' . $spaFieldData['title'];
            echo ' (' . $spaFieldName . '; ';
            echo 'type = ' . $spaFieldData['type'] . ')' . '<br>';
        }

        echo '<pre>';
        echo json_encode($spaFieldList, JSON_PRETTY_PRINT);
        echo '</pre>';

        //display json response as table for fields with items [DALY]
        echo '<h2>Fields with Items</h2>';
        echo '<table border="1" cellpadding="5" cellspacing="0">';
        echo '<tr><th>Field Name</th><th>Title</th><th>Type</th><th>Items</th></tr>';
        foreach ($spaFieldList['fields'] as $spaFieldName => $spaFieldData) {
            if (isset($spaFieldData['items']) && is_array($spaFieldData['items'])) {
                echo '<tr>';
                echo '<td>' . htmlspecialchars($spaFieldName) . '</td>';
                echo '<td>' . htmlspecialchars($spaFieldData['title']) . '</td>';
                echo '<td>' . htmlspecialchars($spaFieldData['type']) . '</td>';
                echo '<td>';
                foreach ($spaFieldData['items'] as $items) {
                    echo htmlspecialchars($items['ID']) . ' : ' . htmlspecialchars($items['VALUE']) . '<br>';
                }
                echo '</td>';
                echo '</tr>';
            }
        }
        echo '</table>';

    } catch (Exception $e) {
        error_log($e->getMessage());
        echo 'Error fetching SPA field list: ' . $e->getMessage();
    }
?>