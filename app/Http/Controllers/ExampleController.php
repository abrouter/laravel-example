<?php
declare(strict_types = 1);

namespace App\Http\Controllers;

use Abrouter\Client\Client;
use Abrouter\Client\DTO\EventDTO;

class ExampleController
{
    public function __invoke(Client $client)
    {
        try {
            $userId = uniqid();
            $buttonColor = $client->experiments()->run($userId, 'button_color');
            $client->statistics()->sendEvent(new EventDTO(
                null,
                $userId,
                'visited_test_page'
            ));

            return view('button', [
                'color' => $buttonColor->getBranchId(),
                'parallelRunning' => config('abrouter.parallelRunning.enabled') === true,
            ]);
        } catch (\Exception $e) {
            die(var_dump($e->getMessage()));
        }

    }
}
