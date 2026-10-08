<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$contract = json_decode((string) file_get_contents($root . '/contracts/sdk-v1.json'), true, 512, JSON_THROW_ON_ERROR);
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root . '/src'));
$source = '';

foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $source .= (string) file_get_contents($file->getPathname()) . "\n";
    }
}

$missing = [];
foreach ($contract['operations'] as $operation) {
    if (!in_array('php', $operation['consumers'] ?? [], true)) {
        continue;
    }

    $path = preg_replace('#^/v1#', '', $operation['path']);
    $method = $operation['method'] === 'GET' ? 'get' : 'post';
    $needle = "->{$method}('{$path}'";
    $publicNeedle = "->publicPost('{$path}'";

    if (!str_contains($source, $needle) && !($operation['id'] === 'checkout.create' && str_contains($source, $publicNeedle))) {
        $missing[] = "{$operation['id']}: {$operation['method']} {$path}";
    }
}

if ($missing !== []) {
    fwrite(STDERR, "PHP SDK contract {$contract['contract_version']} failed:\n- " . implode("\n- ", $missing) . "\n");
    exit(1);
}

echo "PHP SDK contract {$contract['contract_version']} passed.\n";
