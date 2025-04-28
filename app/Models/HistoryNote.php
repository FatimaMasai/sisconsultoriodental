<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HistoryNote extends Model
{
    protected $fillable = [
        'history_id',
        'note',
    ];
 

    public function history()
    {
        return $this->belongsTo(History::class);
    }

    
}
