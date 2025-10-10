<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CampaignMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'campaign_id',
        'phone_number',
        'message_content',
        'status',
        'sent_at',
        'delivered_at',
        'read_at',
        'failed_at',
        'error_message',
        'whatsapp_message_id'
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
        'read_at' => 'datetime',
        'failed_at' => 'datetime'
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_SENT = 'sent';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_READ = 'read';
    const STATUS_FAILED = 'failed';

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    public function replies()
    {
        return $this->hasMany(CampaignReply::class);
    }

    public function getStatusColorAttribute()
    {
        return match($this->status) {
            self::STATUS_PENDING => 'gray',
            self::STATUS_SENT => 'blue',
            self::STATUS_DELIVERED => 'green',
            self::STATUS_READ => 'purple',
            self::STATUS_FAILED => 'red',
            default => 'gray'
        };
    }

    public function getStatusIconAttribute()
    {
        return match($this->status) {
            self::STATUS_PENDING => 'clock',
            self::STATUS_SENT => 'paper-plane',
            self::STATUS_DELIVERED => 'check',
            self::STATUS_READ => 'check-double',
            self::STATUS_FAILED => 'times',
            default => 'question'
        };
    }

    public function markAsSent($whatsappMessageId = null)
    {
        $this->update([
            'status' => self::STATUS_SENT,
            'sent_at' => now(),
            'whatsapp_message_id' => $whatsappMessageId
        ]);
    }

    public function markAsDelivered()
    {
        $this->update([
            'status' => self::STATUS_DELIVERED,
            'delivered_at' => now()
        ]);
    }

    public function markAsRead()
    {
        $this->update([
            'status' => self::STATUS_READ,
            'read_at' => now()
        ]);
    }

    public function markAsFailed($errorMessage = null)
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'failed_at' => now(),
            'error_message' => $errorMessage
        ]);
    }
}