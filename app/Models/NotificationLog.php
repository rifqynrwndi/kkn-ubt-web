<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationLog extends Model
{
    protected $fillable = [
        'title',
        'message',
        'type',
        'recipients',
        'action_url',
        'action_text',
        'sent_by',
    ];

    protected $casts = [
        'recipients' => 'array',
    ];

    public function sender()
    {
        return $this->belongsTo(User::class, 'sent_by');
    }
}
