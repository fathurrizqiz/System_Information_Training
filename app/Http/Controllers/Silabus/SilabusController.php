<?php

namespace App\Http\Controllers\Silabus;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SilabusController extends Controller
{
    public function index()
    {
        return Inertia::render('Silabus/index');
    }
}
