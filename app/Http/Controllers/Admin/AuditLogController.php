<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;

class AuditLogController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:admin.audit_logs.index')->only('index');
    }

    public function index()
    {
        $logs = AuditLog::with('user')->orderBy('id', 'desc')->paginate(30);

        return view('admin.audit_logs.index', compact('logs'));
    }
}
