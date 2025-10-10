<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CampaignRestart extends Model
{
    use HasFactory;

    protected $fillable = [
        'campaign_id',
        'user_id',
        'previous_status',
        'restart_reason',
        'restarted_at'
    ];

    protected $casts = [
        'restarted_at' => 'datetime'
    ];

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}