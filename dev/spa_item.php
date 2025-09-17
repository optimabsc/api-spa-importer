<?php
        declare(strict_types=1);

        use Bitrix24\SDK\Services\ServiceBuilderFactory;

        require_once '../vendor/autoload.php';

        $b24Service = ServiceBuilderFactory::createServiceBuilderFromWebhook(
                'https://optimabsc.bitrix24.com/rest/1/ebfmpby3bbiih63o/'
        );

        try {
                $entityTypeId = 1040;
                $id = 11;

                $itemResult = $b24Service
                        ->getCRMScope()
                        ->item()
                        ->get($entityTypeId,$id);

                $item = $itemResult->item();

                print("ID: " . $item->id . PHP_EOL);
                print("Title: " . $item->title . PHP_EOL);
        } catch (Throwable $e) {
                print("Error: " . $e->getMassage() . PHP_EOL);
        }
?>
