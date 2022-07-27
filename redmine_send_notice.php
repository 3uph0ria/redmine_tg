<?php

//update_issue(48368);
include_once 'include/Database.php';
include_once 'include/Redmine.php';
$config = require 'include/config.php';
$Database = new Database();
$Redmine = new Redmine();

$user = $Database->GetUser($_GET['chat_id']);
$chat_id = $_GET['chat_id'];
$img = $Database->GetImg($chat_id);
$data['issue']['notes'] = 'Добавил ' .  $user['FullName'] . "\n" . $user['Text'];
$j = 0;

for($i = 0; $i < Count($img); $i++)
{
    // Получаем инфу о фотографии по ее FileId
    $ch = curl_init($config['tgApiUrl'] . $config['tgToken'] . '/getFile');
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, array('file_id' => $img[$i]['FileId']));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, false);
    $res = curl_exec($ch);
    curl_close($ch);
    $res = json_decode($res, true);

    // Есди все "ок" качаем ее к нам на хост в папочку "img"
    if ($res['ok'])
    {
        $src = $config['tgApiFile'] .  $config['tgToken'] . '/' . $res['result']['file_path'];
        $dest = $user['Id'] . '-' . time() . '-' . basename($src);
        $Database->UpdateImgName($img[$i]['Id'], $dest);
        copy($src,  'img/' . $dest);
    }

    // Получаем токен для загрузки фотографии на Redmine
    $upload_url = $config['rdUrl'] . 'uploads.json?key=' . $config['rdToken'];
    $request['type'] = 'post';
    $request['content_type'] = 'application/octet-stream';
    $filecontent = file_get_contents('img/' . $dest);
    $token = curl_redmine($upload_url, $request, $filecontent);

    $ch = curl_init();

    // Записали токен с инфой о фотографии в массив
    $data['issue']['uploads'][$j]['token'] = $token->upload->token;
    $data['issue']['uploads'][$j]['filename'] = $dest;
    $data['issue']['uploads'][$j]['content_type'] = "image/jpeg";
    $j++;
}

$data = json_encode($data);
$data = json_decode($data);

$planio_url = $config['rdUrl'] . "issues/" . $user['ActiveIssue'] . ".json?key=" . $config['rdToken'];
$ch = curl_init($planio_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
curl_setopt($ch, CURLOPT_POSTFIELDS,http_build_query($data));

 $response = curl_exec($ch);
echo true;

$Database->UpdateUserText(null, $chat_id);
$Database->DelImg($chat_id);



function curl_redmine($redmine_url,$request='',$post_data='')
{
    if(!isset($request['type'])){ $request['type']=null; }
    if(!isset($request['content_type'])){ $request['content_type']=null; }
    $ch = curl_init();
    $agent = $_SERVER["HTTP_USER_AGENT"];
    curl_setopt($ch, CURLOPT_USERAGENT, $agent);
    curl_setopt($ch, CURLOPT_URL, $redmine_url );

    if($request['type'] == 'post')
    {
        curl_setopt($ch, CURLOPT_POST,1);
        curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: '.$request['content_type'],
                'Content-Length: ' . strlen($post_data))
        );
    }
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    $postResult = curl_exec($ch);
    $response   =   json_decode($postResult);


    return $response;
}


