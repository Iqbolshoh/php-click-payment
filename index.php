<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="https://m.click.uz/favicon.ico">
    <title>Click Payment</title>
    <style>
        .payment-button {
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

        .payment-button i {
            background: url('https://m.click.uz/static/img/logo.png') no-repeat top left;
            width: 30px;
            height: 25px;
            margin-right: 10px;
        }

        .payment-button:hover {
            background-color: #1c8ed7;
        }
    </style>
</head>

<body>
    <button onclick="window.location.href='redirect_payment.php'" class="payment-button">
        <i></i>Pay with CLICK
    </button>
</body>

</html>