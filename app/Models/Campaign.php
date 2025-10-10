<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Campaign extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'message_type',
        'message_content',
        'template_name',
        'template_params',
        'phone_numbers',
        'status',
        'scheduled_at',
        'started_at',
        'completed_at',
        'total_recipients',
        'sent_count',
        'delivered_count',
        'read_count',
        'failed_count',
        'reply_count',
        'user_id'
    ];

    protected $casts = [
        'phone_numbers' => 'array',
        'template_params' => 'array',
        'scheduled_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'total_recipients' => 'integer',
        'sent_count' => 'integer',
        'delivered_count' => 'integer',
        'read_count' => 'integer',
        'failed_count' => 'integer',
        'reply_count' => 'integer'
    ];

    const STATUS_DRAFT = 'draft';
    const STATUS_SCHEDULED = 'scheduled';
    const STATUS_RUNNING = 'running';
    const STATUS_COMPLETED = 'completed';
    const STATUS_PAUSED = 'paused';
    const STATUS_FAILED = 'failed';

    const MESSAGE_TYPE_TEXT = 'text';
    const MESSAGE_TYPE_TEMPLATE = 'template';

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function messages()
    {
        return $this->hasMany(CampaignMessage::class);
    }

    public function replies()
    {
        return $this->hasMany(CampaignReply::class);
    }

    public function restarts()
    {
        return $this->hasMany(CampaignRestart::class);
    }

    public function getStatusColorAttribute()
    {
        return match($this->status) {
            self::STATUS_DRAFT => 'gray',
            self::STATUS_SCHEDULED => 'blue',
            self::STATUS_RUNNING => 'yellow',
            self::STATUS_COMPLETED => 'green',
            self::STATUS_PAUSED => 'orange',
            self::STATUS_FAILED => 'red',
            default => 'gray'
        };
    }

    public function getProgressPercentageAttribute()
    {
        if ($this->total_recipients == 0) {
            return 0;
        }
        
        return round(($this->sent_count / $this->total_recipients) * 100, 1);
    }

    public function getSuccessRateAttribute()
    {
        if ($this->sent_count == 0) {
            return 0;
        }
        
        return round(($this->delivered_count / $this->sent_count) * 100, 1);
    }

    public function getReadRateAttribute()
    {
        if ($this->delivered_count == 0) {
            return 0;
        }
        
        return round(($this->read_count / $this->delivered_count) * 100, 1);
    }

    public function getReplyRateAttribute()
    {
        if ($this->delivered_count == 0) {
            return 0;
        }
        
        return round(($this->reply_count / $this->delivered_count) * 100, 1);
    }

    public function canStart()
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_SCHEDULED, self::STATUS_PAUSED]);
    }

    public function canPause()
    {
        return $this->status === self::STATUS_RUNNING;
    }

    public function canStop()
    {
        return in_array($this->status, [self::STATUS_RUNNING, self::STATUS_PAUSED]);
    }

    public function canRestart()
    {
        return in_array($this->status, [self::STATUS_COMPLETED, self::STATUS_FAILED]);
    }

    public function canEdit()
    {
        // Campaign can be edited in all states except when running
        return $this->status !== self::STATUS_RUNNING;
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', [self::STATUS_RUNNING, self::STATUS_SCHEDULED]);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }
}