<?php

require_once 'config.php';

error_reporting(0);
header('Content-Type: text/json');
header('Charset: UTF-8');

$request = $_POST;

$merchant_id = 'SIZNING_MERCHANT_ID';
$service_id = 'SIZNING_SERVICE_ID';
$merchant_user_id = 'SIZNING_MERCHANT_USER_ID';
$secret_key = 'SIZNING_SECRET_KEY';

// So'rovda barcha parametrlar mavjudligini tekshirish
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
        'error_note' => 'Clickdan so\'rovda xato mavjud'
    ));
    exit;
}

$database = new Database();

// Xeshni tekshirish (so'rovning xavfsizligini tasdiqlash)
$sign = $request['click_trans_id'] .
    $request['service_id'] .
    $secret_key .
    $request['merchant_trans_id'] .
    $request['amount'] .
    $request['action'] .
    $request['sign_time'];

$sign_string = md5($sign);

// Xeshni tekshirish
if ($sign_string != $request['sign_string']) {
    echo json_encode(array(
        'error' => -1,
        'error_note' => 'XESH TEKSHIRUVI XATO!'
    ));
    exit;
}

// Action parametrini tekshirish
if ((int) $request['action'] != 0) {
    echo json_encode(array(
        'error' => -3,
        'error_note' => 'Action topilmadi'
    ));
    exit;
}

// Merchant_trans_id bo'yicha foydalanuvchini tekshirish
$user = $request['merchant_trans_id'];
if (!$user) {
    echo json_encode(array(
        'error' => -5,
        'error_note' => 'Foydalanuvchi topilmadi'
    ));
    exit;
}

$user_data = $database->select("users", "*", "username = ?", [$user], "s");

if (is_array($user_data) && count($user_data) > 0) {
    $row = $user_data[0];
    $name = $row['full_name'];
    $username = $user;
    $login = $row['login'];
    $password = $row['password'];

    // Foydalanuvchini asosiysi jadvalga qo'shish
    $data = [
        'full_name' => $name,
        'login' => $login,
        'username' => $username,
        'password' => $password,
        'role' => 'user'
    ];
    $user_id = $database->insert("users", $data);

    $data = $database->select('users', '*', "username = ?", [$username], "s");
    $log_id = $data[0]['id'];
}

// Natijani JSON formatida qaytarish
echo json_encode(array(
    'error' => 0,
    'error_note' => 'Muvaffaqiyatli',
    'click_trans_id' => $request['click_trans_id'],
    'merchant_trans_id' => $request['merchant_trans_id'],
    'merchant_prepare_id' => $log_id,
));

exit;
?>