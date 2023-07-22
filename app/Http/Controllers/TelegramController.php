<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use App\Services\TelegramService;
use Illuminate\Support\Facades\DB;

class TelegramController extends Controller
{
    public function handle(Request $request)
    {
        $input = $request->all();

        $bot = new TelegramService;
        $bot->setData($input);

        // step 
        $step = $request->session()->get($bot->ChatID() . '_step') ?? 0;
        $name = $request->session()->get($bot->ChatID() . '_name') ?? NULL;
        $url = $request->session()->get($bot->ChatID() . '_url') ?? NULL;
        $git = $request->session()->get($bot->ChatID() . '_git') ?? NULL;


        if ($bot->Text() == '/start') {
            $bot->sendMessage([
                'chat_id' => $bot->ChatID(),
                'text' => 'salom ' .  $bot->FirstName()
            ]);
            $request->session()->put('user_id_' . $bot->ChatID() . '_step', 0);
        }

        if ($bot->Text() == '/add' and $bot->checkAdmin()) {
            $bot->sendMessage([
                'chat_id' => $bot->ChatID(),
                'text' => 'Loyiha uchun nomni kiriting:'
            ]);
            $request->session()->put('user_id_' . $bot->ChatID() . '_step', 1);
        } elseif ($bot->Text() == '/add')
            $bot->sendMessage([
                'chat_id' => $bot->ChatID(),
                'text' => 'Ushbu buyruq siz uchun emas.'
            ]);

        if ($step == 1) {
            $bot->sendMessage([
                'chat_id' => $bot->ChatID(),
                'text' => 'Loyiha uchun urlni yuboring.'
            ]);
            $request->session()->put('user_id_' . $bot->ChatID() . '_name', $bot->Text());
            $request->session()->put('user_id_' . $bot->ChatID() . '_step', 2);
        }

        if ($step == 2) {
            $bot->sendMessage([
                'chat_id' => $bot->ChatID(),
                'text' => 'Github uchun urlni yuboring.'
            ]);
            $request->session()->put('user_id_' . $bot->ChatID() . '_url', $bot->Text());
            $request->session()->put('user_id_' . $bot->ChatID() . '_step', 3);
        }

        if ($step == 3) {
            $bot->sendMessage([
                'chat_id' => $bot->ChatID(),
                'text' => 'Logotipni yuboring.'
            ]);
            $request->session()->put('user_id_' . $bot->ChatID() . '_git', $bot->Text());
            $request->session()->put('user_id_' . $bot->ChatID() . '_step', 4);
        }

        if ($step == 4) {
            $bot->sendMessage([
                'chat_id' => $bot->ChatID(),
                'text' => 'Hammasi saqlandi!.'
            ]);

            $photo_name = $bot->UniqueStr();
            $bot->downloadFile($bot->PhotoId(), 'public/images/' . $photo_name);

            DB::beginTransaction();
            $post = new Post();
            $post->name = $name;
            $post->img = env('APP_URL') . '/public/images/' . $photo_name;
            $post->url = $url;
            $post->github_url = $git;
            DB::commit();

            $request->session()->put('user_id_' . $bot->ChatID() . '_step', 0);
            $request->session()->forget($bot->ChatID() . '_name');
            $request->session()->forget($bot->ChatID() . '_url');
            $request->session()->forget($bot->ChatID() . '_git');
        }
    }
}
