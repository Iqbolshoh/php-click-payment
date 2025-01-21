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
        'error_note' => 'Clickdan sorovda xato mavjud'
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

include './config.php';

$db = new Database();

// Foydalanuvchini vaqtincha jadvaldan tekshirish
$sql = $db->select("user_temp", "*", "telefon = ?", [$user], "s");
if (empty($sql)) {
    echo json_encode(array(
        'error' => -5,
        'error_note' => 'Foydalanuvchi vaqtinchalik jadvalda topilmadi'
    ));
    exit;
}

$row = $sql[0];
$name = $row['ism'];
$telefon = $user;
$login = $row['login'];
$parol = $row['parol'];
$faoliyat = $row['faoliyat'];
$rol = "user";

// Foydalanuvchini asosiy jadvalga qo'shish
$db->insert("user", [
    'ism' => $name,
    'login' => $login,
    'telefon' => $telefon,
    'parol' => $parol,
    'faoliyat' => $faoliyat,
    'rol' => $rol
]);

// Yangi foydalanuvchi ma'lumotlarini olish
$sql = $db->select("user", "*", "telefon = ?", [$telefon], "s");
if (empty($sql)) {
    echo json_encode(array(
        'error' => -5,
        'error_note' => 'Foydalanuvchi asosiy jadvalda topilmadi'
    ));
    exit;
}

$data = $sql[0];
$log_id = $data['id'];

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
