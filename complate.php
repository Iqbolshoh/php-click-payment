<?php

include 'config.php';
$query = new Database();

error_reporting(0);
header('Content-Type: text/json');
header('Charset: UTF-8');

$request = $_POST;

$merchant_id = 'SIZNING_MERCHANT_ID';
$service_id = 'SIZNING_SERVICE_ID';
$merchant_user_id = 'SIZNING_MERCHANT_USER_ID';
$secret_key = 'SIZNING_SECRET_KEY';

// Check if all parameters are sent
if (
    !(
        isset($request['click_trans_id']) &&
        isset($request['service_id']) &&
        isset($request['merchant_trans_id']) &&
        isset($request['amount']) &&
        isset($request['action']) &&
        isset($request['error']) &&
        isset($request['error_note']) &&
        isset($request['sign_time']) &&
        isset($request['sign_string']) &&
        isset($request['click_paydoc_id'])
    )
) {
    echo json_encode(array(
        'error' => -8,
        'error_note' => 'Error in request from click'
    ));
    exit;
}

// Check hash
$sign_string = md5(
    $request['click_trans_id'] .
    $request['service_id'] .
    $secret_key .
    $request['merchant_trans_id'] .
    $request['merchant_prepare_id'] .
    $request['amount'] .
    $request['action'] .
    $request['sign_time']
);

// Check sign string for validity
if ($sign_string != $request['sign_string']) {
    echo json_encode(array(
        'error' => -1,
        'error_note' => 'SIGN CHECK FAILED!'
    ));
    exit;
}

// Check if action is valid
if ((int) $request['action'] != 1) {
    echo json_encode(array(
        'error' => -3,
        'error_note' => 'Action not found'
    ));
    exit;
}

// merchant_trans_id - User's ID entered in the application
// Check if we have a user in the database with this ID

$user = $request['merchant_trans_id'];
if (!$user) {
    echo json_encode(array(
        'error' => -5,
        'error_note' => 'User does not exist'
    ));
    exit;
}

// Check if the merchant_prepare_id exists
$prepared = $request['merchant_prepare_id'];

if (!$prepared) {
    echo json_encode(array(
        'error' => -6,
        'error_note' => 'Transaction does not exist'
    ));
    exit;
} else {
    $summa = $request['amount'];
    $vaqt = time();
    $trans_id = $request['click_trans_id'];
    $host = "HOST";
    $user_d = "USER";
    $password = "PAROL";
    $db = "DATA_BASE_NAME";
    $link = mysqli_connect($host, $user_d, $password, $db);

    if (!$link) {
        exit();
    } else {
        // Insert payment record into the database
        $sql = mysqli_query($link, "INSERT INTO payments (user_id, amount, time, click_trans_id) 
            VALUES ('$user', '$summa', FROM_UNIXTIME($vaqt), '$trans_id')");

        if ($sql === true) {
            // Successfully inserted payment
        } else {
            // Failed to insert payment
        }

        // Retrieve the payment record to get the log_id
        $sql = mysqli_query($link, "SELECT * FROM payments WHERE click_trans_id='$trans_id' ORDER BY id DESC");
        $data = mysqli_fetch_array($sql, MYSQLI_BOTH);
        $log_id = $data['id'];
    }
}

// Error: money was not deducted from the user's card
if ($request['error'] < 0) {
    echo json_encode(array(
        'error' => -6,
        'error_note' => 'Transaction does not exist'
    ));
    exit;
} else {
    // If everything is successful and money was deducted from the user's card, we save it in the database
    echo json_encode(array(
        'error' => 0,
        'error_note' => 'Success',
        'click_trans_id' => $request['click_trans_id'],
        'merchant_trans_id' => $request['merchant_trans_id'],
        'merchant_confirm_id' => $log_id,
    ));
    exit;
}
?>