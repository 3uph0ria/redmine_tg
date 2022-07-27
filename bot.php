<?php
// определяем кодировку
header('Content-type: text/html; charset=utf-8');
// Создаем объект бота
$bot = new Bot();
// Обрабатываем пришедшие данные
$bot->init('php://input');

class Bot
{

    private $botToken = '';
    private $apiUrl = '';

    public function init($data)
    {
        include_once ($_SERVER['DOCUMENT_ROOT'] . '/redmine_tg/include/Database.php');
        include_once ($_SERVER['DOCUMENT_ROOT'] . '/redmine_tg/include/Redmine.php');

        $Database = new Database();
        $Redmine = new Redmine();
        $config = require 'include/config.php';



        $this->botToken = $config['tgToken'];
        $this->apiUrl = $config['tgApiUrl'];

        // создаем массив из пришедших данных от API Telegram
        $arrData = $this->getData($data);

        // лог
         $this->setFileLog($arrData);

        if (array_key_exists('message', $arrData))
        {
            $chat_id = $arrData['message']['chat']['id'];
            $message = $arrData['message']['text'];
            $first_name = $arrData['message']['from']['first_name'];

        }
        elseif (array_key_exists('callback_query', $arrData))
        {
            $chat_id = $arrData['callback_query']['message']['chat']['id'];
            $message = $arrData['callback_query']['data'];
            $first_name = $arrData['message']['from']['first_name'];
        }

        $user = $Database->GetUser($chat_id);

        if(!$user)
        {
            $Database->AddUser($first_name, $chat_id);
            $user = $Database->GetUser($chat_id);
        }

        if($user['Banned'] == 1)
        {
            exit();
        }

        if(strpos($message, 'permission_') !== false)
        {
            $tmp = explode('permission_', $message);
            $Database->UpdatePermission($tmp[1], $user['Id']);
            $user = $Database->GetUser($chat_id);

            if($tmp[1] == 1)
            {
                $dataSend = array('text' => "Ваша роль для получения уведомлений подтверждена", 'chat_id' => $chat_id);
                $this->requestToTelegram($dataSend, "sendMessage");
                exit;
            }
            else
            {
                $dataSend = array(
                    'text' => "Роль успешно установлена, введите фамилию и имя",
                    'chat_id' => $chat_id
                );
                $this->requestToTelegram($dataSend, "sendMessage");
            }
        }



        if(!$user['PermissionId'])
        {
            $permissions = $Database->GetPermissions();
            for($i = 0; $i < Count($permissions); $i++)
            {
                $arrProjects[$i][0]['text'] = $permissions[$i]['Name'];
                $arrProjects[$i][0]['callback_data'] = 'permission_' . $permissions[$i]['Id'];
            }

            $projectsKeyboard =  $this->getInlineKeyBoard($arrProjects);

            $dataSend = array(
                'text' => "Выберите роль",
                'chat_id' => $chat_id,
                'reply_markup' => $projectsKeyboard
            );
            $this->requestToTelegram($dataSend, "sendMessage");
            exit;
        }

        if(!$user['FullName'] && !$arrData['callback_query']['data'])
        {
            if($arrData['message']['text'])
            {
                $Database->UpdateUserFullName($arrData['message']['text'], $user['Id']);

                $projects = $Redmine->GetData('projects');

                for($i = 0; $i < Count($projects->projects); $i++)
                {
                    $arrProjects[$i][0]['text'] = $projects->projects[$i]->name;
                    $arrProjects[$i][0]['callback_data'] = 'project_' . $projects->projects[$i]->id;
                }

                $projectsKeyboard =  $this->getInlineKeyBoard($arrProjects);

                $dataSend = array(
                    'text' => "Фамилия и имя успешно установлено, выберите проект",
                    'chat_id' => $chat_id,
                    'reply_markup' => $projectsKeyboard
                );
                $this->requestToTelegram($dataSend, "sendMessage");
                exit();
            }
            else
            {
                $dataSend = array(
                    'text' => "Введите фамилию и имя",
                    'chat_id' => $chat_id
                );
                $this->requestToTelegram($dataSend, "sendMessage");
                exit();
            }
        }


        if($message == "/start")
        {
            $dataSend = array('text' => "Вы уже выбрали роль. Для внесения изменений или вопросы по боту - @InCeDeNt Алексей Воропаев", 'chat_id' => $chat_id);
            $this->requestToTelegram($dataSend, "sendMessage");
            exit();
        }

        if($message == "Проекты" || $message == 'К списку проектов' || strpos($message, 'new_project') !== false)
        {
            $projects = $Redmine->GetData('projects');

            for($i = 0; $i < Count($projects->projects); $i++)
            {
                $arrProjects[$i][0]['text'] = $projects->projects[$i]->name;
                $arrProjects[$i][0]['callback_data'] = 'project_' . $projects->projects[$i]->id;
            }

            $projectsKeyboard =  $this->getInlineKeyBoard($arrProjects);

            $dataSend = array(
                'text' => "Выберите проект",
                'chat_id' => $chat_id,
                'reply_markup' => $projectsKeyboard
            );
            $this->requestToTelegram($dataSend, "sendMessage");
            exit();
        }

        if($message == 'Задачи' || $message == 'К списку задач' || strpos($message, 'project_') !== false)
        {
            if($message == 'Задачи')
            {
                $ProjectId = $user['ProjectId'];
            }
            else if($message == 'К списку задач')
            {
                $Database->UpdateUserText(null, $chat_id);
                $Database->DelImg($chat_id);
                $ProjectId = $user['ProjectId'];
            }
            else
            {
                $tmp = explode('project_', $message);
                $Database->UpdateUserProject($tmp[1], $chat_id);
                $ProjectId = $tmp[1];
            }

            $issues = $Redmine->GetDataParam('issues', '&project_id=' . $ProjectId);

            $j = 0;

            for($i = 0; $i < Count($issues->issues); $i++)
            {
                if(($issues->issues[$i]->status->name == 'В работе исполнитель' || $issues->issues[$i]->status->name == 'В работе подрядчик' || $issues->issues[$i]->status->name == 'В работе исполнитель и ИТР') && $issues->issues[$i]->tracker->name == 'Работы на объекте')
                {
                    $arrProjects[$j][0]['text'] = $issues->issues[$i]->subject;
                    $arrProjects[$j][0]['callback_data'] = 'issue_' . $issues->issues[$i]->id;

                    $j++;
                }

                $arrProjects[$j][0]['text'] = 'К списку проектов';
                $arrProjects[$j][0]['callback_data'] = 'new_project';
            }

            $projectsKeyboard =  $this->getInlineKeyBoard($arrProjects);

            $dataSend = array(
                'text' => "Выберите задачу",
                'chat_id' => $chat_id,
                'resize_keyboard' => true,
                'reply_markup' => $projectsKeyboard
            );
            $this->requestToTelegram($dataSend, "sendMessage");
            exit();
        }

        if(strpos($message, 'issue_') !== false)
        {
            $tmp = explode('issue_', $message);
            $Database->UpdateActiveIssue($tmp[1], $chat_id);

            $dataSend = array('text' => "\n*Добавьте комментарий по выполненным работам и фото, после этого нажмите кнопку 'Отправить отчет'*", 'chat_id' => $chat_id,  'parse_mode' => 'Markdown');
            $this->requestToTelegram($dataSend, "sendMessage");

            $Database->UpdateUserText(null, $chat_id);
            $Database->DelImg($chat_id);

            exit();
        }

        if($message == 'Отправить отчет')
        {
            if($user['Text'])
            {
                $check = 0;

                // Пока все фотки не загрузятся в задачу, сообщения об успехе не будет
                while(true)
                {
                    $check = file_get_contents($config['sendPath'] . $chat_id);

                    if($check == 1)
                    {
                        $dataSend = array('text' => "Отчет успешно отправлен.", 'chat_id' => $chat_id, 'parse_mode' => 'Markdown');
                        $this->requestToTelegram($dataSend, "sendMessage");

                        $issue = $Redmine->GetIssue($user['ActiveIssue']);
                        $author = $Database->GetAuthor($issue->issue->author->id);
                        $dataSend = array('text' => 'Исполнитель ' . $user['FullName'] . ' отправил отчет по выполненным работам к задаче ' . $Redmine->rdUrl . 'issues/' . $user['ActiveIssue'] . ' прошу вас проверить.', 'chat_id' => $author['PeerId']);
                        $this->requestToTelegram($dataSend, "sendMessage");

                        break;
                    }
                }

                exit();
            }
            else
            {
                $dataSend = array('text' => "\n*Вы не указали текст примечания!*", 'chat_id' => $chat_id, 'parse_mode' => 'Markdown');
                $this->requestToTelegram($dataSend, "sendMessage");
                exit();
            }
        }

        // Добавление текста и фотографий в БД для отправки в задачу
        if($user['ActiveIssue'])
        {
            if($arrData['message']['text'])
            {
                $textMessage = $arrData['message']['text'];
            }
            else if($arrData['message']['caption'])
            {
                $textMessage = $arrData['message']['caption'];
            }

            if($textMessage)
            {
                $Database->UpdateUserText($user['Text'] . "\n" . $textMessage, $chat_id);

                $justKeyboard = $this->getKeyBoard([[["text" => "Отправить отчет"]], [["text" => "К списку задач"]] ]);

                $dataSend = array('text' => "Выберите действие", 'chat_id' => $chat_id, 'reply_markup' => $justKeyboard);
                $this->requestToTelegram($dataSend, "sendMessage");
            }

            if($arrData['message']['photo'])
            {
                $photo = array_pop($arrData['message']['photo']);
                $Database->AddImg($chat_id, $photo['file_id'], $photo['file_name']);
                exit();
            }

            if($arrData['message']['document'])
            {
                $Database->AddImg($arrData['message']['chat']['id'], $arrData['message']['document']['file_id'], $arrData['message']['document']['file_name']);
                exit();
            }
        }
    }

    /**
     * создаем inline клавиатуру
     * @return string
     */
    private function getInlineKeyBoard($data)
    {
        $inlineKeyboard = array(
            "inline_keyboard" => $data,
        );
        return json_encode($inlineKeyboard);
    }

    /**
     * создаем клавиатуру
     * @return string
     */
    private function getKeyBoard($data)
    {
        $keyboard = array(
            "keyboard" => $data,
            "one_time_keyboard" => false,
            "resize_keyboard" => true
        );
        return json_encode($keyboard);
    }

    private function setFileLog($data)
    {
        $fh = fopen('logs/log.txt', 'a') or die('can\'t open file');
        ((is_array($data)) || (is_object($data))) ? fwrite($fh, print_r($data, TRUE) . "\n") : fwrite($fh, $data . "\n");
        fclose($fh);
    }

    /**
     * Парсим что приходит преобразуем в массив
     * @param $data
     * @return mixed
     */
    private function getData($data)
    {
        return json_decode(file_get_contents($data), TRUE);
    }

    /** Отправляем запрос в Телеграмм
     * @param $data
     * @param string $type
     * @return mixed
     */
    private function requestToTelegram($data, $type)
    {
        $result = null;

        if (is_array($data)) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->apiUrl . $this->botToken . '/' . $type);
            curl_setopt($ch, CURLOPT_POST, count($data));
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            $result = curl_exec($ch);
            curl_close($ch);
        }
        return $result;
    }
}
