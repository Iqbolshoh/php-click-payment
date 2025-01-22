<?php
error_reporting(0);

include 'config.php';
$query = new Database();

$transaction_id = "iqbolshoh_777";
$payment_amount = 1000.00;

$payment_data = [
    'service_id' => SERVICE_ID,
    'merchant_id' => MERCHANT_ID,
    'amount' => $payment_amount,
    'transaction_param' => $transaction_id
];

$payment_url = 'https://my.click.uz/services/pay?' . http_build_query($payment_data);

header("Location: $payment_url");
exit;
?>