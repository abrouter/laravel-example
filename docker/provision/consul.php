<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

include '/app/vendor/autoload.php';

use SensioLabs\Consul\ServiceFactory;

$filePathApp = '/app/docker/config/consul/app.yml';
$filePathInfrastructure = '/app/docker/config/consul/infrastructure.yml';

if (!file_exists($filePathApp)) {
    echo 'File not found:' . $filePathApp . PHP_EOL;
    die;
}

if (!isset($_SERVER['CONSUL_HTTP_ADDR'])) {
    echo 'Env variable CONSUL_HTTP_ADDR is not set.';
    die;
}

$search = ['$SERVICE_REGION$', '$SERVICE_NAME$'];
$replace = [$_SERVER['SERVICE_REGION'], $_SERVER['SERVICE_NAME']];


if (isset($_SERVER['SERVICE_CLUSTER'])) {
    array_push($search, '$SERVICE_CLUSTER$');
    array_push($replace, $_SERVER['SERVICE_CLUSTER']);
}

$contentApp = file_get_contents($filePathApp);
$contentApp = str_replace($search, $replace, $contentApp);

if (isset($argv[1]) && $argv[1] == 'i') {
    if (!file_exists($filePathInfrastructure)) {
        fprintf(STDERR, 'File not found:' . $filePathInfrastructure . PHP_EOL);
        exit(1);
    }

    $contentInfrastructure = file_get_contents($filePathInfrastructure);
    $contentInfrastructure = str_replace($search, $replace, $contentInfrastructure);
}

if ($argc > 2 ) {
    fprintf(STDOUT, 'Application' . PHP_EOL . $contentApp . PHP_EOL);
    fprintf(STDOUT, 'Infrastructure' . PHP_EOL . $contentInfrastructure . PHP_EOL);
    exit(0);
}

function consulMock(string $content) {
    if (!function_exists('yaml_parse')) {
        function yaml_parse(string $content) {
            $lines = explode("\n", $content);
            $keys = [];
            foreach ($lines as $line) {
                $explode = explode(':', $line);
                $key = $explode[0];
                unset($explode[0]);
                $value = join(':', $explode);
                if (!empty($key) && !empty($value)) {
                    $keys[$key] = trim($value);
                }
            }

            return $keys;
        }
    }

    $keys = yaml_parse($content);
    $sf   = new ServiceFactory(['base_uri' => $_SERVER['CONSUL_HTTP_ADDR']]);

    /** @var \SensioLabs\Consul\Services\KV $kv */
    $kv = $sf->get('kv');

    foreach ($keys as $key => $value) {
        try {
            $kv->get($key);
        } catch (\SensioLabs\Consul\Exception\ServerException $e) {
            fprintf(STDERR, $e->getMessage() . PHP_EOL);
            exit(1);
        } catch (\SensioLabs\Consul\Exception\ClientException $e) {
            if ($e->getCode() == 404) {
                $kv->put($key, $value);
            } else {
                fprintf(STDERR, $e->getMessage() . PHP_EOL);
                exit(1);
            }
        } catch (\Throwable $e) {
            fprintf(STDERR, $e->getMessage() . PHP_EOL);
            exit(1);
        }
    }
}

consulMock($contentApp);

if (isset($contentInfrastructure)) {
    consulMock($contentInfrastructure);
}
