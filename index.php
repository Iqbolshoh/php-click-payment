<?php
error_reporting(0);

include 'config.php';
$query = new Database();

$transID = date("Ymd_His");
$transAmount = 1000.00;

$paymentData = [
    'service_id' => SERVICE_ID,
    'merchant_id' => MERCHANT_ID,
    'amount' => $transAmount,
    'transaction_param' => $transID
];

$paymentUrl = 'https://my.click.uz/services/pay?' . http_build_query($paymentData);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Click Payment</title>
    <style>
        .click_logo {
            padding: 10px 20px;
            color: #fff;
            font-size: 14px;
            font-family: Arial, sans-serif;
            font-weight: bold;
            text-align: center;
            border: 1px solid #037bc8;
            text-shadow: 0px -1px 0px #037bc8;
            border-radius: 5px;
            background: linear-gradient(#27a8e0 0%, #1c8ed7 100%);
            box-shadow: inset 0px 1px 0px #45c4fc;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            text-decoration: none;
        }

        .click_logo i {
            background: url('https://m.click.uz/static/img/logo.png') no-repeat top left;
            width: 30px;
            height: 25px;
            margin-right: 10px;
        }

        .click_logo:hover {
            background-color: #1c8ed7;
        }
    </style>
</head>

<body>
    <a href="<?php echo $paymentUrl; ?>" target="_blank" class="click_logo">
        <i></i>Pay with CLICK
    </a>
</body>

</html>