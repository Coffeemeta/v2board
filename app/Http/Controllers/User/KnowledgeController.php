<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\Request;
use App\Models\Knowledge;

class KnowledgeController extends Controller
{
    public function fetch(Request $request)
    {
        if ($request->input('id')) {
            $knowledge = Knowledge::where('id', $request->input('id'))
                ->where('show', 1)
                ->first()
                ->toArray();
            if (!$knowledge) abort(500, '知识不存在');
            $user = User::find($request->session()->get('id'));
            $userService = new UserService();
            $appleId = $userService->isAvailable($user) ? config('v2board.apple_id') : '没有有效订阅无法使用本站提供的AppleID';
            $appleIdPassword = $userService->isAvailable($user) ? config('v2board.apple_id_password') : '没有有效订阅无法使用本站提供的AppleID';
            $subscribeUrl = config('v2board.subscribe_url', config('v2board.app_url', env('APP_URL'))) . '/api/v1/client/subscribe?token=' . $user['token'];
            $knowledge['body'] = str_replace('{{siteName}}', config('v2board.app_name', 'V2Board'), $knowledge['body']);
            $knowledge['body'] = str_replace('{{appleId}}', $appleId, $knowledge['body']);
            $knowledge['body'] = str_replace('{{appleIdPassword}}', $appleIdPassword, $knowledge['body']);
            $knowledge['body'] = str_replace('{{subscribeUrl}}', $subscribeUrl, $knowledge['body']);
            $knowledge['body'] = str_replace('{{urlEncodeSubscribeUrl}}', urlencode($subscribeUrl), $knowledge['body']);
            return response([
                'data' => $knowledge
            ]);
        }
        $knowledges = Knowledge::select(['id', 'category', 'title', 'updated_at'])
            ->where('language', $request->input('language'))
            ->where('show', 1)
            ->orderBy('sort', 'ASC')
            ->get()
            ->groupBy('category');
        return response([
            'data' => $knowledges
        ]);
    }
}
