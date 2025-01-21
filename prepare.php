<?php

error_reporting(0);
header('Content-Type: text/json');
header('Charset: UTF-8');

$request = $_POST;

$merchant_id = 'SIZNING_MERCHANT_ID';
$service_id = 'SIZNING_SERVICE_ID';
$merchant_user_id = 'SIZNING_MERCHANT_USER_ID';
$secret_key = 'SIZNING_SECRET_KEY';
file_put_contents('prepare_log.txt', $request);
$myfile = fopen("newfile.txt", "w") or die("Unable to open file!");
// Проверка отправлено-ли все параметры
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
    fwrite('prepare_log.txt', "Error in request from click \n");
    echo json_encode(array(
        'error' => -8,
        'error_note' => 'Error in request from click'
    ));

    exit;
} else {
    fwrite($myfile, "trans_id=" . $request['click_trans_id'] . "\n");
    fwrite($myfile, "service_id=" . $request['service_id'] . "\n");
    fwrite($myfile, "merchant_trans_id=" . $request['merchant_trans_id'] . "\n");
    fwrite($myfile, "amount=" . $request['amount'] . "\n");
    fwrite($myfile, "action=" . $request['action'] . "\n");
    fwrite($myfile, "error=" . $request['error'] . "\n");
    fwrite($myfile, "error_note=" . $request['error_note'] . "\n");
    fwrite($myfile, "sign_time=" . $request['sign_time'] . "\n");
    fwrite($myfile, "sign_string=" . $request['sign_string'] . "\n");
    fwrite($myfile, "click_paydoc_id=" . $request['click_paydoc_id'] . "\n");
}

// Проверка хеша
$sign = $request['click_trans_id'] .
    $request['service_id'] .
    $secret_key .
    $request['merchant_trans_id'] .
    $request['amount'] .
    $request['action'] .
    $request['sign_time'];

$sign_string = md5($sign);
// check sign string to possible
if ($sign_string != $request['sign_string']) {

    echo json_encode(array(
        'error' => -1,
        'error_note' => 'SIGN CHECK FAILED!'
    ));
    fwrite('prepare_log.txt', "SIGN CHECK FAILED! \n");
    exit;
} else {
    fwrite($myfile, "md5dan o'tdi \n");
}
if ((int) $request['action'] != 0) {

    echo json_encode(array(
        'error' => -3,
        'error_note' => 'Action not found'
    ));
    fwrite('prepare_log.txt', "Action not found! \n");
    exit;
} else {
    fwrite($myfile, "Actiondan o'tdi \n");
}
// merchant_trans_id - это ID пользователья который он ввел в приложении
// Здесь нужно проверить если у нас в базе пользователь с таким ID

$user = $request['merchant_trans_id'];
if (!$user) {
    fwrite($myfile, "Userdan o'tmadi \n");
    echo json_encode(array(
        'error' => -5,
        'error_note' => 'User does not exist'
    ));
    exit;
} else {
    $url = "MANZIL";
    $host = "HOST";
    $user_d = "USER";
    $password = "PAROL";
    $db = "DATA_BASE_NAME";
    $link = mysqli_connect($host, $user_d, $password, $db);
    if (!$link) {
        fwrite($myfile, "Xato: MySQL bilan aloqa o'rnatib bo'lmadi." . PHP_EOL);
        fwrite($myfile, "Errno xato kodi: " . mysqli_connect_errno() . PHP_EOL);
        fwrite($myfile, "Xatolik matni: " . mysqli_connect_error() . PHP_EOL);
        exit();
    } else {
        $sql = mysqli_query($link, "SELECT * from user_temp WHERE telefon='$user' order by id desc");
        if ($sql == true) {
            fwrite($myfile, "sql1 ishladi user tempga kirdim \n");
        } else {
            fwrite($myfile, "sql1 ishlamadi user tempga kirolmadim \n");
        }
        $row = mysqli_fetch_array($sql, MYSQLI_BOTH);
        $name = $row['ism'];
        $telefon = $user;
        $login = $row['login'];
        $parol = $row['parol'];
        $faoliyat = $row['faoliyat'];
        $rol = "user";
        fwrite($myfile, "Malumotlar : $name ,$telefon, $login, $parol, $faoliyat  \n");
        $sql = mysqli_query($link, "INSERT INTO user (ism,login,telefon,parol,faoliyat,rol) VALUES ('$name','$login','$telefon','$parol','$faoliyat','$rol')");
        if ($sql == true) {
            fwrite($myfile, "sql2 ishladi userga kirdim \n");
        } else {
            fwrite($myfile, "sql2 ishlamadi userga kirolmadim \n");
        }
        $sql = mysqli_query($link, "SELECT * from user WHERE telefon='$telefon' order by id desc");
        if ($sql == true) {
            fwrite($myfile, "sql3 ishladi userga kirdim \n");
        } else {
            fwrite($myfile, "sql3 ishlamadi userga kirolmadim \n");
        }
        $data = mysqli_fetch_array($sql, MYSQLI_BOTH);
        $log_id = $data['id'];
        fwrite($myfile, "logid=" . $log_id . " \n");
    }
    fwrite($myfile, "Userdan o'tdi \n");
}
// Если это заказ тогда нужно проверить еще статус заказа, все еще заказ актуален или нет
// если проверка не проходит то нужно возвращать ошибку -4

// и еще нужно проверить сумму заказа
// если не проходит тогда нужно возвращать ошибку -2

// Еще одна проверка статуса заказа, не закрыть или нет
// если заказ отменен тогда нужно возвращать ошибку - 9

// Все проверки прошли успешно, тог здесь будем сохранять в базу что подготовка к оплате успешно прошла
// можно сделать отдельную таблицу чтобы сохранить входящих данных как лог
// и присвоит на параметр merchant_prepare_id номер лога

$myJSON = json_encode(array(
    'error' => 0,
    'error_note' => 'Success',
    'click_trans_id' => $request['click_trans_id'],
    'merchant_trans_id' => $request['merchant_trans_id'],
    'merchant_prepare_id' => $log_id,
));
echo $myJSON;
fwrite($myfile, $myJSON . "\n");
exit;