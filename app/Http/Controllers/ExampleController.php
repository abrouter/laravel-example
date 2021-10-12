<?php
declare(strict_types = 1);

namespace App\Http\Controllers;

use Abrouter\Client\Client;

class ExampleController
{
    public function __invoke(Client $client)
    {
        $buttonColor = $client->experiments()->run(uniqid(), 'D1D06000-0000-0000-00005030');
        return view('button', [
            'color' => $buttonColor->getBranchId(),
        ]);
    }
}
