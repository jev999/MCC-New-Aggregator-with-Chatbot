<?php

namespace App\Http\Controllers;

use App\Models\AdminLoginLog;

class AdminLoginLogController extends Controller
{
    public function index()
    {
        $logs = AdminLoginLog::with('admin')
            ->latest('logged_at')
            ->latest('created_at')
            ->paginate(50);

        return view('superadmin.admin_login_logs.index', compact('logs'));
    }
}

