<?php

namespace App\Http\Controllers;

use App\Models\EmailLog;
use Illuminate\Http\Request;

class EmailLogController extends Controller
{
    public function index(Request $request)
    {
        $query = EmailLog::with('user')->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $query->where('to_email', 'like', '%' . $request->search . '%');
        }

        $logs = $query->paginate(20);
        return view('email-logs.index', compact('logs'));
    }
}
