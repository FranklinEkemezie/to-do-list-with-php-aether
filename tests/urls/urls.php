<?php

/**
 * @throws Exception
 */
$getUrls = function (string $method): array {
    $jsonFile = __DIR__ . "/" . strtoupper($method) . ".json";
    if (! file_exists($jsonFile)) {
        throw new \Exception("JSON file: $jsonFile not found");
    }

    $urlsJson = file_get_contents($jsonFile);
    if (! $urlsJson) {
        throw new \Exception("Could not get the content of the JSON file");
    }

    $urls = json_decode($urlsJson, true);
    if (! is_array($urls)) {
        print_r($urls);
        throw new \Exception("Invalid JSON or URL configuration");
    }

    return $urls;
};

return [
    'GET'   => $getUrls('GET'),
    'POST'  => $getUrls('POST'),
    'PUT'   => $getUrls('PUT'),
    'PATCH' => $getUrls('PATCH'),
    'DELETE'=> $getUrls('DELETE')
];