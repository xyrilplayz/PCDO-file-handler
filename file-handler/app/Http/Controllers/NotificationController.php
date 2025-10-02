<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\notifications;

class NotificationController extends Controller
{
    // NotificationController.php
    public function index()
    {
        // Load schedule -> coopProgram -> cooperative (with email inside coop_programs)
        $notifications = Notifications::with('schedule.coopProgram.cooperative')->latest()->get();

        return view('show-history', compact('notifications'));
    }

    public function show($id)
    {
        $notification = Notifications::with('schedule.coopProgram.cooperative')->findOrFail($id);

        return view('showhistoryspecific', compact('notification'));
    }


}
