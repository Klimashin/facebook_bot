<?php
require_once '../fb_api_controller.php';
require_once '../config.php';

set_time_limit(0);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fbEventData = json_decode(file_get_contents('php://input'));

    if (LOGGING_ON) { logToFile('request.log', print_r($fbEventData, 1)); }

    foreach ($fbEventData->entry as $event) {
        try {
            processFbEvent($event);
        } catch (Exception $e) {
            logToFile('error.log', $e->getMessage());
        }
    }
} else {
    if ($_GET['hub_mode'] == 'subscribe') {
        if ($_GET['hub_verify_token'] == GENERATED_VERIFY_TOKEN) {
            echo $_GET['hub_challenge'];
        }
    } else {
        header($_SERVER["SERVER_PROTOCOL"] . " 400 Bad Request");
    }
}

function processFbEvent($event)
{
    foreach ($event->changes as $change) {
        switch ($change->field) {
            case 'feed':
                // process new comment logic
                if ($change->value->item == 'comment'
                    && $change->value->verb == 'add'
                    && $change->value->parent_id == $change->value->post_id //it's not a reply
                    && $change->value->sender_id != PAGE_ID //it's not a comment by page
                    && (USER_COMMENT_AUTO_LIKE_ON || USER_COMENT_AUTO_REPLY_ON)
                ) {
                    $params = [
                        'client_id' => APP_ID,
                        'client_secret' => APP_SECRET,
                        'access_token' => PAGE_VERIFY_TOKEN,
                        'fields' => 'from{id}',
                    ];
                    $postAuthorId = json_decode(fb_api_get("/" . $change->value->post_id, $params))->from->id;
                    logToFile('curl.log', $postAuthorId);

                    if ($postAuthorId != PAGE_ID) { continue; }

                    if (USER_COMMENT_AUTO_LIKE_ON) {
                        $params = [
                            'client_id' => APP_ID,
                            'client_secret' => APP_SECRET,
                            'access_token' => PAGE_VERIFY_TOKEN,
                        ];
                        fb_api_post("/{$change->value->comment_id}/likes", $params);
                    }

                    if (USER_COMMENT_AUTO_REPLY_ON) {
                        $params = [
                            'client_id' => APP_ID,
                            'client_secret' => APP_SECRET,
                            'access_token' => PAGE_VERIFY_TOKEN,
                            'message' => USER_COMMENT_AUTO_REPLY_MSG,
                        ];
                        fb_api_post("/{$change->value->comment_id}/comments", $params);
                    }
                }

                // process new post logic
                if ($change->value->item == 'post'
                    && $change->value->verb == 'add'
                    && $change->value->sender_id != PAGE_ID
                    && (USER_POST_AUTO_LIKE_ON || USER_POST_REPLY_ON)
                ) {
                    if (USER_POST_AUTO_LIKE_ON) {
                        $params = [
                            'client_id' => APP_ID,
                            'client_secret' => APP_SECRET,
                            'access_token' => PAGE_VERIFY_TOKEN,
                        ];
                        fb_api_post("/{$change->value->post_id}/likes", $params);
                    }

                    if (USER_POST_AUTO_REPLY_ON) {
                        $params = [
                            'client_id' => APP_ID,
                            'client_secret' => APP_SECRET,
                            'access_token' => PAGE_VERIFY_TOKEN,
                            'message' => USER_POST_AUTO_REPLY_MSG,
                        ];
                        fb_api_post("/{$change->value->post_id}/comments", $params);
                    }
                }
                break;

            case 'conversations':
                if (CONVERSATION_REPLY_ON) {
                    $params = [
                        'client_id' => APP_ID,
                        'client_secret' => APP_SECRET,
                        'access_token' => PAGE_VERIFY_TOKEN,
                        'fields' => 'from,created_time',
                        'since' => time() - CONVERSATION_AUTO_REPLY_COOLDOWN,
                    ];
                    $lastMsgs = json_decode(fb_api_get("/{$change->value->thread_id}/messages", $params));
                    logToFile('curl.log', print_r($lastMsgs, 1));

                    $needReply = true;
                    foreach ($lastMsgs as $msg) {
                        if ($msg['from']['id'] == PAGE_ID) {
                            $needReply = false;
                            break;
                        }
                    }

                    if ($needReply) {
                        $params = [
                            'client_id' => APP_ID,
                            'client_secret' => APP_SECRET,
                            'access_token' => PAGE_VERIFY_TOKEN,
                            'message' => CONVERSATION_AUTO_REPLY_MSG,
                        ];
                        fb_api_post("/{$change->value->thread_id}/messages", $params);
                    }
                }
                break;

            default:
                //do nothing
        }
    }
}

function logToFile($filename, $msg)
{
    $fd = fopen($filename, "a");
    $str = "[" . date("Y/m/d h:i:s", mktime()) . "] " . $msg;
    fwrite($fd, $str . "\n");
    fclose($fd);
}
