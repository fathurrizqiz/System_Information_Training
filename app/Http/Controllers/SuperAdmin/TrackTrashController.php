<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Services\TrashService;
use Inertia\Inertia;

class TrackTrashController extends Controller
{
    public function index(TrashService $trashService)
    {
        return Inertia::render('SuperAdmin/TrackTrash', [
            'trash' => $trashService->getAllTrash(auth()->user()),
        ]);
    }
}
