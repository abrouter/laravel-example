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
            $userId = $_SESSION['userId'] ?? uniqid(); // $_SESSION['guestId'] here's for example. Always use some user id / guest id when sending event
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
