<?php
    declare(strict_types=1);

    use Bitrix24\SDK\Services\ServiceBuilderFactory;

    require_once './includes/functions.php';
    require_once '../vendor/autoload.php';

    loadEnv('../config/.env-b24');
    $webhook = $_ENV['WEBHOOK_CRM'];
    $entityTypeId = 1060; //spa entity type id to be provided by input form
    $order = ['id' => 'ASC',];
    $filter = [];
    $select = ['*'];
    //$startItem = 0;

    $b24Service = ServiceBuilderFactory::createServiceBuilderFromWebhook($webhook);

    try {
        $response = $b24Service
                    ->core
                    ->call(
                        'crm.item.list',
                        [
                            'entityTypeId' => $entityTypeId,
                            'order' => $order,
                            'filter' => $filter,
                            'select' => $select,
                            //'start' => $startItem,
                        ]
                    );

        $itemsList = $response
            ->getResponseData()
            ->getResult();

        //echo 'Success' . '<br><br>';
        //echo 'Items List for SPA EntityTypeID = ' . $entityTypeId . ':' . '<br>';
        //foreach ($itemsList['items'] as $item) {
        //    echo '- ID: ' . $item['id'] . '; Title: ' . $item['title'] . '<br>';
        //}

        echo '<pre>';
        echo json_encode($itemsList, JSON_PRETTY_PRINT);
        echo '</pre>';
    } catch (Throwable $e) {
            print("Error: " . $e->getMessage() . PHP_EOL);
    }
?>