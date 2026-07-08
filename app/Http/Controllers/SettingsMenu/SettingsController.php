<?php

namespace App\Http\Controllers\SettingsMenu;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SettingsController extends Controller
{
    public function index()
    {
        return Inertia::render('Jadwal/MenuSettings/index');
    }
}
