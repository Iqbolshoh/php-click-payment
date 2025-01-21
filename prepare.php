<?php

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

$url = "MANZIL";
$host = "HOST";
$user_d = "USER";
$password = "PAROL";
$db = "DATA_BASE_NAME";
$link = mysqli_connect($host, $user_d, $password, $db);

if (!$link) {
    exit(); // Agar MySQL bilan aloqa o'rnatib bo'lmasa, chiqish
} else {
    // Foydalanuvchini vaqtincha jadvaldan tekshirish
    $sql = mysqli_query($link, "SELECT * from user_temp WHERE telefon='$user' order by id desc");
    $row = mysqli_fetch_array($sql, MYSQLI_BOTH);
    $name = $row['ism'];
    $telefon = $user;
    $login = $row['login'];
    $parol = $row['parol'];
    $faoliyat = $row['faoliyat'];
    $rol = "user";

    // Foydalanuvchini asosiysi jadvalga qo'shish
    $sql = mysqli_query($link, "INSERT INTO user (ism,login,telefon,parol,faoliyat,rol) VALUES ('$name','$login','$telefon','$parol','$faoliyat','$rol')");

    // Yangi foydalanuvchi ma'lumotlarini olish
    $sql = mysqli_query($link, "SELECT * from user WHERE telefon='$telefon' order by id desc");
    $data = mysqli_fetch_array($sql, MYSQLI_BOTH);
    $log_id = $data['id'];
}

// Agar barcha tekshiruvlar muvaffaqiyatli o'tsa, natijani JSON formatida qaytarish
$myJSON = json_encode(array(
    'error' => 0,
    'error_note' => 'Muvaffaqiyatli',
    'click_trans_id' => $request['click_trans_id'],
    'merchant_trans_id' => $request['merchant_trans_id'],
    'merchant_prepare_id' => $log_id,
));
echo $myJSON;
exit;
