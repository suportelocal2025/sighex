<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        return match($user->papel) {
            'superintendente' => redirect('/superintendente'),
            'diretor' => redirect('/diretor'),
            'rh' => redirect('/rh'),
            'administrativo' => redirect('/admin'),
            default => redirect('/login'),
        };
    }
}
