<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $timestampColumn = $this->timestampColumn();
        $hasEvent = Schema::hasColumn('audit_logs', 'event');
        $hasDescription = Schema::hasColumn('audit_logs', 'description');
        $hasIpAddress = Schema::hasColumn('audit_logs', 'ip_address');
        $hasUserId = Schema::hasColumn('audit_logs', 'user_id');
        $canSearch = $hasDescription || $hasIpAddress || $hasUserId;

        $logs = AuditLog::query()
            ->with('user')
            ->when($hasEvent && $request->filled('event'), fn ($query) => $query->where('event', $request->event))
            ->when($canSearch && $request->filled('search'), function ($query) use ($request, $hasDescription, $hasIpAddress, $hasUserId) {
                $search = $request->search;

                $query->where(function ($query) use ($search, $hasDescription, $hasIpAddress, $hasUserId) {
                    if ($hasDescription) {
                        $query->where('description', 'like', "%{$search}%");
                    }

                    if ($hasIpAddress) {
                        $query->orWhere('ip_address', 'like', "%{$search}%");
                    }

                    if ($hasUserId) {
                        $query->orWhereHas('user', function ($query) use ($search) {
                            $query->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        });
                    }
                });
            })
            ->latest($timestampColumn)
            ->paginate(20)
            ->withQueryString();

        $events = $hasEvent
            ? AuditLog::query()
                ->select('event')
                ->distinct()
                ->orderBy('event')
                ->pluck('event')
            : collect();

        return view('admin.audit.index', compact('logs', 'events', 'timestampColumn'));
    }

    public function show(AuditLog $auditLog): View
    {
        $auditLog->load('user');
        $timestampColumn = $this->timestampColumn();

        return view('admin.audit.show', compact('auditLog', 'timestampColumn'));
    }

    public function statistics(): View
    {
        $timestampColumn = $this->timestampColumn();

        $totalsByEvent = Schema::hasColumn('audit_logs', 'event')
            ? AuditLog::query()
                ->selectRaw('event, count(*) as total')
                ->groupBy('event')
                ->orderByDesc('total')
                ->get()
            : collect();

        $recentLoginFailures = Schema::hasColumn('audit_logs', 'event')
            ? AuditLog::query()
                ->where('event', 'login_failed')
                ->where($timestampColumn, '>=', now()->subDay())
                ->count()
            : 0;

        $todayTotal = AuditLog::query()
            ->whereDate($timestampColumn, today())
            ->count();

        return view('admin.audit.statistics', compact('totalsByEvent', 'recentLoginFailures', 'todayTotal'));
    }

    private function timestampColumn(): string
    {
        return Schema::hasColumn('audit_logs', 'occurred_at') ? 'occurred_at' : 'created_at';
    }
}
