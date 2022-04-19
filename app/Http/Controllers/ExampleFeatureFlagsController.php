<?php
declare(strict_types = 1);

namespace App\Http\Controllers;

use Abrouter\Client\Client;

class ExampleFeatureFlagsController
{
    public function __invoke(Client $client)
    {
        $enabledButtonFeatureFlag = $client->featureFlags()->run('enabled_button_feature_flag');
        $disabledButtonFeatureFlag = $client->featureFlags()->run('disabled_button_feature_flag');

        return view('featureFlags', [
            'enabledButtonFeatureFlag' => $enabledButtonFeatureFlag,
            'disabledButtonFeatureFlag' => $disabledButtonFeatureFlag,
        ]);
    }
}
