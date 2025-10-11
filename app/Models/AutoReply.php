<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AutoReply extends Model
{
    use HasFactory;

    protected $fillable = [
        'campaign_id',
        'trigger_keywords',
        'reply_message',
        'is_active',
        'delay_seconds',
        'send_once_per_contact'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'send_once_per_contact' => 'boolean',
        'delay_seconds' => 'integer'
    ];

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    /**
     * Check if message matches trigger keywords
     */
    public function matchesMessage($messageContent)
    {
        if (empty($this->trigger_keywords)) {
            return true; // No keywords means reply to all messages
        }

        $keywords = array_map('trim', explode(',', strtolower($this->trigger_keywords)));
        $messageContent = strtolower($messageContent);

        foreach ($keywords as $keyword) {
            if (strpos($messageContent, $keyword) !== false) {
                return true;
            }
        }

        return false;
    }
}
