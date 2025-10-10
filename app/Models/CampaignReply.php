<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CampaignReply extends Model
{
    use HasFactory;

    protected $fillable = [
        'campaign_id',
        'campaign_message_id',
        'phone_number',
        'message_content',
        'whatsapp_message_id',
        'received_at',
        'is_processed'
    ];

    protected $casts = [
        'received_at' => 'datetime',
        'is_processed' => 'boolean'
    ];

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    public function campaignMessage()
    {
        return $this->belongsTo(CampaignMessage::class);
    }

    public function markAsProcessed()
    {
        $this->update(['is_processed' => true]);
    }
}