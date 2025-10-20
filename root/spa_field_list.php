<?php
    declare(strict_types=1);

    use Bitrix24\SDK\Services\ServiceBuilderFactory;

    require_once './includes/functions.php';
    require_once '../vendor/autoload.php';

    loadEnv('../config/.env-b24');
    $webhook = $_ENV['WEBHOOK_CRM'];
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

    } catch (Exception $e) {
        error_log($e->getMessage());
        echo 'Error fetching SPA field list: ' . $e->getMessage();
    }
?>