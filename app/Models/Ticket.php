<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $fillable = [
        'subject',
        'company_id',
        'user_id',
        'status',
        'assigned_to',
        'email',
    ];

    public const STATUSES = ['new', 'open', 'waiting', 'resolved'];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function replies()
    {
        return $this->hasMany(TicketReply::class)->orderBy('created_at');
    }

    public function publicReplies()
    {
        return $this->replies()->where('is_internal', false);
    }
}
