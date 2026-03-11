<?php

namespace TeamStream\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeamInvitation extends Model
{
    protected $fillable = ['team_id', 'email', 'role'];

    public function team(): BelongsTo
    {
        return $this->belongsTo(config('teamstream.models.team'));
    }
}
