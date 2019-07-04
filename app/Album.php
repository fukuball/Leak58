<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Album extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title', 'user_id',
    ];

    /**
     * Get the user.
     */
    public function user()
    {
        return $this->belongsTo('App\User');
    }

    /**
     * Get the songs.
     */
    public function songs()
    {
        return $this->hasMany('App\Song');
    }

    /**
     * Process something to return leak.
     */
    public function processSomethingToReturn()
    {
        $songs = $this->songs;
        // do something here...
        return $songs;
    }
}
