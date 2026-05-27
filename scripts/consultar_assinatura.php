<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Efi\EfiPay;

$options = [
    'client_id'     => 'Client_Id_ead3ef674ebc3a1dfff02329087c0986a1260a15',
    'client_secret' => 'Client_Secret_504a79084f1b414a916a40605acbf0ad9da45ea5',
    'certificate'   => __DIR__ . '/../certs/producao.p12',
    'sandbox'       => false,
    'debug'         => false,
];

$subscription_id = 1408291;

try {
    $efi      = new EfiPay($options);
    $resposta = $efi->detailSubscription(['id' => $subscription_id]);

    $data  = $resposta['data'];
    $items = $data['plan']['items'] ?? [];
    $total = array_sum(array_column($items, 'value'));

    echo "=== Assinatura {$subscription_id} ===\n";
    echo "Status       : " . ($data['status'] ?? '-') . "\n";
    echo "Valor na Efi : R$ " . number_format($total / 100, 2, ',', '.') . "\n";
    echo "Plano        : " . ($data['plan']['name'] ?? '-') . "\n";
    echo "\nItens:\n";
    foreach ($items as $item) {
        echo "  - " . $item['name'] . ": R$ " . number_format($item['value'] / 100, 2, ',', '.') . "\n";
    }

    echo "\nÚltimas cobranças:\n";
    $charges = $data['charges'] ?? [];
    foreach (array_slice(array_reverse($charges), 0, 5) as $charge) {
        echo "  - ID: " . $charge['id'] . " | Status: " . $charge['status'] . " | Valor: R$ " . number_format($charge['total'] / 100, 2, ',', '.') . " | Data: " . ($charge['paid_at'] ?? $charge['created_at'] ?? '-') . "\n";
    }

} catch (\Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}
