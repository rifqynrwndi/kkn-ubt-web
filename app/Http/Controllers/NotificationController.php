<?php

namespace App\Http\Controllers;

use App\Models\NotificationLog;
use App\Models\User;
use App\Notifications\GeneralNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(): View
    {
        if (auth()->user()->hasRole('superadmin')) {
            $notifications = NotificationLog::with('sender')
                ->latest()
                ->paginate(20);

            return view('notifications.admin-index', compact('notifications'));
        }

        $notifications = auth()->user()
            ->notifications()
            ->latest()
            ->paginate(20);

        return view('notifications.index', compact('notifications'));
    }

    public function create(): View
    {
        $users = User::role('mahasiswa')
            ->select('id', 'name', 'email')
            ->orderBy('name')
            ->get();

        return view('notifications.create', compact('users'));
    }

    public function send(Request $request): RedirectResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'type' => 'required|in:info,success,warning,danger',
            'recipient_mode' => 'required|in:all_mahasiswa,unverified_mahasiswa,incomplete_biodata,manual',
            'users' => 'nullable|array',
            'users.*' => 'exists:users,id',
            'action_url' => 'nullable|url',
            'action_text' => 'nullable|string|max:50',
        ]);

        $users = $this->resolveRecipients($request);

        if ($users->isEmpty()) {
            return back()->with('error', 'Tidak ada penerima yang ditemukan.');
        }

        $log = NotificationLog::create([
            'title' => $request->title,
            'message' => $request->message,
            'type' => $request->type,
            'recipients' => $users->pluck('name')->toArray(),
            'action_url' => $request->action_url,
            'action_text' => $request->action_text ?: 'View',
            'sent_by' => auth()->id(),
        ]);

        foreach ($users as $user) {
            $user->notify(new GeneralNotification(
                title: $request->title,
                message: $request->message,
                actionUrl: $request->action_url,
                actionText: $request->action_text ?: 'View',
                type: $request->type,
                notificationLogId: $log->id
            ));
        }

        return redirect()
            ->route('notifications.index')
            ->with('success', 'Notifikasi berhasil dikirim ke ' . $users->count() . ' pengguna.');
    }

    public function markAsRead(string $id): RedirectResponse
    {
        $notification = auth()->user()
            ->notifications()
            ->findOrFail($id);

        $notification->markAsRead();

        return isset($notification->data['action_url'])
            ? redirect($notification->data['action_url'])
            : redirect()->route('notifications.index');
    }

    public function markAllAsRead(): RedirectResponse
    {
        auth()->user()->unreadNotifications->markAsRead();

        return back()->with('success', 'Semua notifikasi ditandai telah dibaca.');
    }

    public function destroy(string $id): RedirectResponse
    {
        auth()->user()
            ->notifications()
            ->findOrFail($id)
            ->delete();

        return back()->with('success', 'Notifikasi berhasil dihapus.');
    }

    public function destroyAll(): RedirectResponse
    {
        auth()->user()->notifications()->delete();

        return back()->with('success', 'Semua notifikasi berhasil dihapus.');
    }

    public function destroyHistory(string $id): RedirectResponse
    {
        $log = NotificationLog::findOrFail($id);

        DB::table('notifications')
            ->where('data->notification_log_id', $log->id)
            ->delete();

        $log->delete();

        return back()->with(
            'success',
            'Riwayat notifikasi dan notifikasi penerima berhasil dihapus.'
        );
    }

    public function clearHistory(): RedirectResponse
    {
        DB::table('notifications')
            ->whereNotNull('data->notification_log_id')
            ->delete();

        NotificationLog::truncate();

        return back()->with(
            'success',
            'Seluruh riwayat notifikasi berhasil dihapus.'
        );
    }

    public function unreadCount(): JsonResponse
    {
        return response()->json([
            'count' => auth()->user()->unreadNotifications()->count(),
        ]);
    }

    public function recent(): JsonResponse
    {
        return response()->json(
            auth()->user()
                ->notifications()
                ->latest()
                ->take(5)
                ->get()
        );
    }

    private function resolveRecipients(Request $request)
    {
        return match ($request->recipient_mode) {
            'all_mahasiswa' => User::role('mahasiswa')->get(),

            'unverified_mahasiswa' => User::role('mahasiswa')
                ->whereNull('email_verified_at')
                ->get(),

            'incomplete_biodata' => User::role('mahasiswa')
                ->where('is_biodata_complete', false)
                ->get(),

            'manual' => User::whereIn('id', $request->users ?? [])->get(),

            default => collect(),
        };
    }
}
