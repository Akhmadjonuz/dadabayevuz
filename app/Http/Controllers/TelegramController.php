<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use App\Services\TelegramService;
use App\Traits\HttpResponse;
use Illuminate\Support\Facades\DB;

class TelegramController extends Controller
{
    use HttpResponse;

    public function handle(Request $request)
    {
        try {
            $input = $request->all();

            $bot = new TelegramService;
            $bot->setData($input);

            //user data
            $user = User::where('chat_id', $bot->ChatID())->first();
            $step = $user->step ?? 0;
            $text = $bot->Text();

            if ($text == '/start') {

                // if user not exist create new user
                if (!$user) {
                    $user = new User();
                    $user->chat_id = $bot->ChatID();
                    $user->step = 0;
                    $user->save();
                }

                $bot->sendMessage([
                    'chat_id' => $bot->ChatID(),
                    'text' => 'salom ' .  $bot->FirstName()
                ]);

                // update step to 0
                $user->step = 0;
                $user->save();
            }

            if ($text == '/add' and $bot->checkAdmin()) {
                $bot->sendMessage([
                    'chat_id' => $bot->ChatID(),
                    'text' => 'Loyiha uchun nomni kiriting:'
                ]);

                // update step to 1
                $user->step = 1;
                $user->save();
            } elseif ($text == '/add')
                $bot->sendMessage([
                    'chat_id' => $bot->ChatID(),
                    'text' => 'Ushbu buyruq siz uchun emas.'
                ]);

            if ($step == 1) {
                $bot->sendMessage([
                    'chat_id' => $bot->ChatID(),
                    'text' => 'Loyiha uchun urlni yuboring.'
                ]);

                // update step to 2 and save name
                $user->step = 2;
                $user->name = $text;
                $user->save();
            }

            if ($step == 2) {
                $bot->sendMessage([
                    'chat_id' => $bot->ChatID(),
                    'text' => 'Github uchun urlni yuboring.'
                ]);

                // update step to 3 and save url
                $user->step = 3;
                $user->url = $text;
                $user->save();
            }

            if ($step == 3) {
                $bot->sendMessage([
                    'chat_id' => $bot->ChatID(),
                    'text' => 'Logotipni yuboring.'
                ]);

                // update step to 4 and save github url
                $user->step = 4;
                $user->github_url = $text;
                $user->save();
            }

            if ($step == 4) {
                $bot->sendMessage([
                    'chat_id' => $bot->ChatID(),
                    'text' => 'Hammasi saqlandi!'
                ]);
                
                $photo_name = $bot->UniqueStr();

                DB::beginTransaction();

                $post = new Post();
                $post->name = $user->name;
                $post->img = env('APP_URL') . 'images/' . $photo_name;
                $post->url = $user->url;
                $post->github_url = $user->git;
                $post->save();

                DB::commit();

                $bot->downloadFile($bot->PhotoId(), 'images/' . $photo_name);

                // update step to 0
                $user->step = 0;
                $user->save();
            }
        } catch (\Exception $e) {
            return $this->log($e);
        }
    }
}