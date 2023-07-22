<?php

namespace App\Services;

use App\Traits\HttpResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TelegramService
{
    use HttpResponse;

    const CHANNEL_POST = 'channel_post';
    const CALLBACK_QUERY = 'callback_query';
    const EDITED_MESSAGE = 'edited_message';
    const INLINE_QUERY = 'inline_query';
    const MESSAGE = 'message';
    const PHOTO = 'photo';
    const VIDEO = 'video';
    const AUDIO = 'audio';
    const VOICE = 'voice';
    const CONTACT = 'contact';
    const LOCATION = 'location';
    const REPLY = 'reply';
    const ANIMATION = 'animation';
    const STICKER = 'sticker';
    const DOCUMENT = 'document';

    private $bot_token = '';
    private $data = [];
    private $updates = [];

    public function __construct()
    {
        $this->bot_token = config('services.telegram_bot.api_key');
        $this->data = $this->getData();
    }

    private function endpoint(string $api, array $content, $post = true): array
    {
        $url = 'https://api.telegram.org/bot' . $this->bot_token . '/' . $api;

        if ($post)
            $response = $this->sendAPIRequest($url, $content);
        else
            $response = $this->sendAPIRequest($url, [], false);

        return $response;
    }

    public function checkAdmin(): bool
    {
        if ($this->ChatID() == config('services.telegram_bot.admin_id'))
            return true;
        else
            return false;
    }

    public function UniqueStr(): string
    {
        return time() . '_' . Str::random(10);
    }

    public function getMe()
    {
        return $this->endpoint('getMe', [], false);
    }

    public function respondSuccess()
    {
        http_response_code(200);

        return json_encode(['status' => 'success']);
    }

    public function sendMessage(array $content)
    {
        return $this->endpoint('sendMessage', $content);
    }

    public function forwardMessage(array $content)
    {
        return $this->endpoint('forwardMessage', $content);
    }

    public function sendPhoto(array $content)
    {
        return $this->endpoint('sendPhoto', $content);
    }

    public function sendAudio(array $content)
    {
        return $this->endpoint('sendAudio', $content);
    }

    public function sendDocument(array $content)
    {
        return $this->endpoint('sendDocument', $content);
    }

    public function sendAnimation(array $content)
    {
        return $this->endpoint('sendAnimation', $content);
    }

    public function sendSticker(array $content)
    {
        return $this->endpoint('sendSticker', $content);
    }

    public function sendVideo(array $content)
    {
        return $this->endpoint('sendVideo', $content);
    }

    public function sendVoice(array $content)
    {
        return $this->endpoint('sendVoice', $content);
    }

    public function sendLocation(array $content)
    {
        return $this->endpoint('sendLocation', $content);
    }

    public function editMessageLiveLocation(array $content)
    {
        return $this->endpoint('editMessageLiveLocation', $content);
    }

    public function stopMessageLiveLocation(array $content)
    {
        return $this->endpoint('stopMessageLiveLocation', $content);
    }

    public function setChatStickerSet(array $content)
    {
        return $this->endpoint('setChatStickerSet', $content);
    }

    public function deleteChatStickerSet(array $content)
    {
        return $this->endpoint('deleteChatStickerSet', $content);
    }

    public function sendMediaGroup(array $content)
    {
        return $this->endpoint('sendMediaGroup', $content);
    }

    public function sendVenue(array $content)
    {
        return $this->endpoint('sendVenue', $content);
    }

    public function sendContact(array $content)
    {
        return $this->endpoint('sendContact', $content);
    }

    public function sendChatAction(array $content)
    {
        return $this->endpoint('sendChatAction', $content);
    }

    public function getUserProfilePhotos(array $content)
    {
        return $this->endpoint('getUserProfilePhotos', $content);
    }

    private function getFile($file_id): string
    {
        $content = ['file_id' => $file_id];
        return $this->endpoint('getFile', $content)['result']['file_path'];
    }

    public function kickChatMember(array $content)
    {
        return $this->endpoint('kickChatMember', $content);
    }

    public function leaveChat(array $content)
    {
        return $this->endpoint('leaveChat', $content);
    }

    public function unbanChatMember(array $content)
    {
        return $this->endpoint('unbanChatMember', $content);
    }

    public function getChat(array $content)
    {
        return $this->endpoint('getChat', $content);
    }

    public function getChatAdministrators(array $content)
    {
        return $this->endpoint('getChatAdministrators', $content);
    }

    public function getChatMembersCount(array $content)
    {
        return $this->endpoint('getChatMembersCount', $content);
    }

    public function getChatMember(array $content)
    {
        return $this->endpoint('getChatMember', $content);
    }

    public function answerInlineQuery(array $content)
    {
        return $this->endpoint('answerInlineQuery', $content);
    }

    public function setGameScore(array $content)
    {
        return $this->endpoint('setGameScore', $content);
    }

    public function answerCallbackQuery(array $content)
    {
        return $this->endpoint('answerCallbackQuery', $content);
    }

    public function editMessageText(array $content)
    {
        return $this->endpoint('editMessageText', $content);
    }

    public function editMessageCaption(array $content)
    {
        return $this->endpoint('editMessageCaption', $content);
    }

    public function editMessageReplyMarkup(array $content)
    {
        return $this->endpoint('editMessageReplyMarkup', $content);
    }

    public function downloadFile(string $file_id, string $local_path): void
    {

        $file_url = 'https://api.telegram.org/file/bot' . $this->bot_token . '/' . $this->getFile($file_id);
        $photo_response = Http::get($file_url);
        $photo_data = $photo_response->body();
        Storage::put($local_path, $photo_data);
    }

    public function setWebhook(string $url, string $certificate = '')
    {
        if ($certificate == '')
            $requestBody = ['url' => $url];
        else
            $requestBody = ['url' => $url, 'certificate' => @$certificate];

        return $this->endpoint('setWebhook', $requestBody, true);
    }

    public function getData()
    {
        if (empty($this->data))
            return json_decode(file_get_contents('php://input'), true);
        else
            return $this->data;
    }

    public function setData(array $data)
    {
        $this->data = $data;
    }

    public function Text(): string
    {
        $type = $this->getUpdateType();
        if ($type == self::CALLBACK_QUERY)
            return @$this->data['callback_query']['data'];
        elseif ($type == self::CHANNEL_POST)
            return @$this->data['channel_post']['text'];
        elseif ($type == self::EDITED_MESSAGE)
            return @$this->data['edited_message']['text'];
        else
            return @$this->data['message']['text'];
    }

    public function PhotoId(): string
    {
        $type = $this->getUpdateType();
        $count = count($this->data['photo']) - 1;
        if ($type == self::PHOTO)
            return @$this->data['photo'][$count]['file_id'];
        else
            return $this->log('no photo');
    }

    public function Caption(): string
    {
        $type = $this->getUpdateType();
        if ($type == self::CHANNEL_POST)
            return @$this->data['channel_post']['caption'];
        else
            return @$this->data['message']['caption'];
    }

    public function ChatID(): string
    {
        $type = $this->getUpdateType();
        if ($type == self::CALLBACK_QUERY)
            return @$this->data['callback_query']['message']['chat']['id'];
        elseif ($type == self::CHANNEL_POST)
            return @$this->data['channel_post']['chat']['id'];
        elseif ($type == self::EDITED_MESSAGE)
            return @$this->data['edited_message']['chat']['id'];
        elseif ($type == self::INLINE_QUERY)
            return @$this->data['inline_query']['from']['id'];
        else
            return $this->data['message']['chat']['id'];
    }

    public function MessageID(): int
    {
        $type = $this->getUpdateType();
        if ($type == self::CALLBACK_QUERY)
            return @$this->data['callback_query']['message']['message_id'];
        elseif ($type == self::CHANNEL_POST)
            return @$this->data['channel_post']['message_id'];
        elseif ($type == self::EDITED_MESSAGE)
            return @$this->data['edited_message']['message_id'];
        else
            return $this->data['message']['message_id'];
    }

    public function ReplyToMessageID()
    {
        return $this->data['message']['reply_to_message']['message_id'];
    }

    public function ReplyToMessageFromUserID()
    {
        return $this->data['message']['reply_to_message']['forward_from']['id'];
    }

    public function Inline_Query()
    {
        return $this->data['inline_query'];
    }

    public function Callback_Query()
    {
        return $this->data['callback_query'];
    }

    public function Callback_ID()
    {
        return $this->data['callback_query']['id'];
    }

    public function Callback_Data()
    {
        return $this->data['callback_query']['data'];
    }

    public function Callback_Message()
    {
        return $this->data['callback_query']['message'];
    }

    public function Callback_ChatID()
    {
        return $this->data['callback_query']['message']['chat']['id'];
    }

    public function Date()
    {
        return $this->data['message']['date'];
    }

    public function FirstName(): string
    {
        $type = $this->getUpdateType();
        if ($type == self::CALLBACK_QUERY)
            return @$this->data['callback_query']['from']['first_name'];
        elseif ($type == self::CHANNEL_POST)
            return @$this->data['channel_post']['from']['first_name'];
        elseif ($type == self::EDITED_MESSAGE)
            return @$this->data['edited_message']['from']['first_name'];
        else
            return @$this->data['message']['from']['first_name'];
    }

    public function LastName(): string
    {
        $type = $this->getUpdateType();
        if ($type == self::CALLBACK_QUERY)
            return @$this->data['callback_query']['from']['last_name'];
        elseif ($type == self::CHANNEL_POST)
            return @$this->data['channel_post']['from']['last_name'];
        elseif ($type == self::EDITED_MESSAGE)
            return @$this->data['edited_message']['from']['last_name'];
        else
            return @$this->data['message']['from']['last_name'];
    }

    public function Username(): string
    {
        $type = $this->getUpdateType();
        if ($type == self::CALLBACK_QUERY)
            return @$this->data['callback_query']['from']['username'];
        elseif ($type == self::CHANNEL_POST)
            return @$this->data['channel_post']['from']['username'];
        elseif ($type == self::EDITED_MESSAGE)
            return @$this->data['edited_message']['from']['username'];
        else
            return @$this->data['message']['from']['username'];
    }

    public function Location()
    {
        return $this->data['message']['location'];
    }

    public function UpdateID()
    {
        return $this->data['update_id'];
    }

    public function UpdateCount()
    {
        return count($this->updates['result']);
    }

    public function UserID(): string
    {
        $type = $this->getUpdateType();
        if ($type == self::CALLBACK_QUERY)
            return $this->data['callback_query']['from']['id'];
        elseif ($type == self::CHANNEL_POST)
            return $this->data['channel_post']['from']['id'];
        elseif ($type == self::EDITED_MESSAGE)
            return @$this->data['edited_message']['from']['id'];
        else
            return $this->data['message']['from']['id'];
    }

    public function FromID()
    {
        return $this->data['message']['forward_from']['id'];
    }

    public function FromChatID()
    {
        return $this->data['message']['forward_from_chat']['id'];
    }

    public function messageFromGroup()
    {
        if ($this->data['message']['chat']['type'] == 'private')
            return false;
        else
            return true;
    }

    public function messageFromGroupTitle()
    {
        if ($this->data['message']['chat']['type'] != 'private')
            return $this->data['message']['chat']['title'];
        else
            return '';
    }

    public function buildKeyBoard(array $options, $onetime = false, $resize = false, $selective = true)
    {
        $replyMarkup = [
            'keyboard'          => $options,
            'one_time_keyboard' => $onetime,
            'resize_keyboard'   => $resize,
            'selective'         => $selective,
        ];
        $encodedMarkup = json_encode($replyMarkup, true);
        return $encodedMarkup;
    }

    public function buildInlineKeyBoard(array $options)
    {
        $replyMarkup = [
            'inline_keyboard' => $options,
        ];
        $encodedMarkup = json_encode($replyMarkup, true);
        return $encodedMarkup;
    }

    public function buildInlineKeyboardButton(
        $text,
        $url = '',
        $callback_data = '',
        $switch_inline_query = null,
        $switch_inline_query_current_chat = null,
        $callback_game = '',
        $pay = ''
    ) {
        $replyMarkup = [
            'text' => $text,
        ];
        if ($url != '')
            $replyMarkup['url'] = $url;
        elseif ($callback_data != '')
            $replyMarkup['callback_data'] = $callback_data;
        elseif (!is_null($switch_inline_query))
            $replyMarkup['switch_inline_query'] = $switch_inline_query;
        elseif (!is_null($switch_inline_query_current_chat))
            $replyMarkup['switch_inline_query_current_chat'] = $switch_inline_query_current_chat;
        elseif ($callback_game != '')
            $replyMarkup['callback_game'] = $callback_game;
        elseif ($pay != '')
            $replyMarkup['pay'] = $pay;

        return $replyMarkup;
    }

    public function buildKeyboardButton($text, $request_contact = false, $request_location = false)
    {
        $replyMarkup = [
            'text'             => $text,
            'request_contact'  => $request_contact,
            'request_location' => $request_location,
        ];

        return $replyMarkup;
    }

    public function buildKeyBoardHide($selective = true)
    {
        $replyMarkup = [
            'remove_keyboard' => true,
            'selective'       => $selective,
        ];
        $encodedMarkup = json_encode($replyMarkup, true);

        return $encodedMarkup;
    }

    public function buildForceReply($selective = true)
    {
        $replyMarkup = [
            'force_reply' => true,
            'selective'   => $selective,
        ];
        $encodedMarkup = json_encode($replyMarkup, true);

        return $encodedMarkup;
    }

    public function sendInvoice(array $content)
    {
        return $this->endpoint('sendInvoice', $content);
    }

    public function copyMessage(array $content)
    {
        return $this->endpoint('copyMessage', $content);
    }

    public function answerShippingQuery(array $content)
    {
        return $this->endpoint('answerShippingQuery', $content);
    }

    public function answerPreCheckoutQuery(array $content)
    {
        return $this->endpoint('answerPreCheckoutQuery', $content);
    }

    public function sendVideoNote(array $content)
    {
        return $this->endpoint('sendVideoNote', $content);
    }

    public function restrictChatMember(array $content)
    {
        return $this->endpoint('restrictChatMember', $content);
    }

    public function promoteChatMember(array $content)
    {
        return $this->endpoint('promoteChatMember', $content);
    }

    public function exportChatInviteLink(array $content)
    {
        return $this->endpoint('exportChatInviteLink', $content);
    }

    public function setChatPhoto(array $content)
    {
        return $this->endpoint('setChatPhoto', $content);
    }

    public function deleteChatPhoto(array $content)
    {
        return $this->endpoint('deleteChatPhoto', $content);
    }

    public function setChatTitle(array $content)
    {
        return $this->endpoint('setChatTitle', $content);
    }

    public function setChatDescription(array $content)
    {
        return $this->endpoint('setChatDescription', $content);
    }

    public function pinChatMessage(array $content)
    {
        return $this->endpoint('pinChatMessage', $content);
    }

    public function unpinChatMessage(array $content)
    {
        return $this->endpoint('unpinChatMessage', $content);
    }

    public function getStickerSet(array $content)
    {
        return $this->endpoint('getStickerSet', $content);
    }

    public function uploadStickerFile(array $content)
    {
        return $this->endpoint('uploadStickerFile', $content);
    }

    public function createNewStickerSet(array $content)
    {
        return $this->endpoint('createNewStickerSet', $content);
    }

    public function addStickerToSet(array $content)
    {
        return $this->endpoint('addStickerToSet', $content);
    }

    public function setStickerPositionInSet(array $content)
    {
        return $this->endpoint('setStickerPositionInSet', $content);
    }

    public function deleteStickerFromSet(array $content)
    {
        return $this->endpoint('deleteStickerFromSet', $content);
    }

    public function deleteMessage(array $content)
    {
        return $this->endpoint('deleteMessage', $content);
    }

    public function serveUpdate($update)
    {
        $this->data = $this->updates['result'][$update];
    }

    public function getUpdateType()
    {
        $update = $this->data;
        if (isset($update['inline_query']))
            return self::INLINE_QUERY;
        elseif (isset($update['callback_query']))
            return self::CALLBACK_QUERY;
        elseif (isset($update['edited_message']))
            return self::EDITED_MESSAGE;
        elseif (isset($update['message']['text']))
            return self::MESSAGE;
        elseif (isset($update['message']['photo']))
            return self::PHOTO;
        elseif (isset($update['message']['video']))
            return self::VIDEO;
        elseif (isset($update['message']['audio']))
            return self::AUDIO;
        elseif (isset($update['message']['voice']))
            return self::VOICE;
        elseif (isset($update['message']['contact']))
            return self::CONTACT;
        elseif (isset($update['message']['location']))
            return self::LOCATION;
        elseif (isset($update['message']['reply_to_message']))
            return self::REPLY;
        elseif (isset($update['message']['animation']))
            return self::ANIMATION;
        elseif (isset($update['message']['sticker']))
            return self::STICKER;
        elseif (isset($update['message']['document']))
            return self::DOCUMENT;
        elseif (isset($update['channel_post']))
            return self::CHANNEL_POST;
        else
            return false;
    }

    private function sendAPIRequest(string $url, array $content, bool $post = true): array
    {
        try {
            if (isset($content['chat_id'])) {
                $url = $url . '?chat_id=' . $content['chat_id'];
                unset($content['chat_id']);
            }

            $result = Http::post($url, $content);

            if ($result->successful())
                return $result->json();
            else
                return [];
            $this->log($result->status());
        } catch (\Exception $e) {
            return $this->log($e);
        }
    }
}