<?php
    declare(strict_types=1);

    use Bitrix24\SDK\Services\ServiceBuilderFactory;

    require_once './includes/functions.php';
    require_once '../vendor/autoload.php';

    loadEnv('../config/.env-b24');
    $webhook = $_ENV['WEBHOOK_CRM'];

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
            
        echo 'Success' . '<br><br>';
        echo 'SPA List:' . '<br>';

        foreach ($spaList['types'] as $row) {
            echo '- ' . $row['title'];
            echo ' [ID: ' . $row['id'];
            echo '; TypeID: ' . $row['entityTypeId'] . ']' . '<br>';
        }

        //echo '<pre>';
        //echo json_encode($spaList, JSON_PRETTY_PRINT);
        //echo '</pre>';

    } catch (Exception $e) {
        error_log($e->getMessage());
        echo 'Error fetching SPA list: ' . $e->getMessage();
    }
?>