<?php
    declare(strict_types=1);

    use Bitrix24\SDK\Services\ServiceBuilderFactory;

    require_once './includes/functions.php';
    require_once '../vendor/autoload.php';

    loadEnv('../config/.env-b24');
    $webhook = $_ENV['WEBHOOK_CRM'];
    $entityTypeId = 1040;
    $row = [
        'title' => "New SPA record",
        'ufCrm23String' => "String value",
        'ufCrm23Integer' => 1,
        'ufCrm23Number' => 1.11,
    ];

    $b24Service = ServiceBuilderFactory::createServiceBuilderFromWebhook($webhook);

    try {
        $response = $b24Service
            ->core
            ->call(
                'crm.item.add',
                [
                    'entityTypeId' => $entityTypeId,
                    'fields' => $row,
                    'params' => [
                        'REGISTER_SONET_EVENT' => 'N'
                    ]
                ]
            );

        echo 'Success: Added SPA record';
    } catch (Exception $e) {
        error_log($e->getMessage());
        echo 'Error adding SPA record: ' . $e->getMessage();
    }
?>