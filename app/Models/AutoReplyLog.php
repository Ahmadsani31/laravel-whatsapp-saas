<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AutoReplyLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'auto_reply_id',
        'campaign_reply_id',
        'phone_number',
        'sent_message',
        'sent_at',
        'was_successful',
        'error_message'
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'was_successful' => 'boolean'
    ];

    public function autoReply()
    {
        return $this->belongsTo(AutoReply::class);
    }

    public function campaignReply()
    {
        return $this->belongsTo(CampaignReply::class);
    }
}