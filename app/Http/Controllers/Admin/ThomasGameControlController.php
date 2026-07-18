<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class ThomasGameControlController extends Controller
{
    private const THOMAS_BASE_URL = 'https://thomasgamecompanyltd.queenlive.site';

    public function index()
    {
        $this->authorizeThomasAccess();

        return view('backend.game.thomas_control', [
            'loginUrl' => self::THOMAS_BASE_URL . '/thomas-admin',
            'securityUrl' => self::THOMAS_BASE_URL . '/admin/game-final/security',
            'lobbyUrl' => self::THOMAS_BASE_URL . '/play_bd_game',
            'tenantHost' => parse_url(self::THOMAS_BASE_URL, PHP_URL_HOST),
        ]);
    }

    public function security(): RedirectResponse
    {
        $this->authorizeThomasAccess();

        return redirect()->away(self::THOMAS_BASE_URL . '/admin/game-final/security');
    }

    public function login(): RedirectResponse
    {
        $this->authorizeThomasAccess();

        return redirect()->away(self::THOMAS_BASE_URL . '/thomas-admin');
    }

    public function lobby(): RedirectResponse
    {
        $this->authorizeThomasAccess();

        return redirect()->away(self::THOMAS_BASE_URL . '/play_bd_game');
    }

    private function authorizeThomasAccess(): void
    {
        abort_unless(in_array((int) Auth::id(), [1, 22222], true), 403);
    }
}
