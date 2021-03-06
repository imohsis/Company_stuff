<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;
use App\Helpers\Utility;
use Auth;

class LivetvComments extends Model
{
    //
    protected $table = 'afrobt_livetv_comments';
    protected $guarded = [];
    public $timestamps = true;
    public function user(){
        return $this->belongsTo('App\User', 'user_id', 'uid');
    }

    public function comment($column, $postUid, $commentText)
    {
        return Utility::comment($this->table, $column, $postUid, $commentText);
    }

    public function fetchComment($column, $postUid, $start, $limit)
    {
        return Utility::fetchComment($this->table, $column, $postUid, $start, $limit);
    }

    public function getComment($column,$postUid)
    {
        return self::where($column,$postUid)->get();
    }

    public function loadMore($lastID,$column,$postUid){

        return self::where('status' , Utility::STATUS_ACTIVE )
            ->where($column,$postUid)
            ->where('id','>',$lastID)
            ->orderBy('created_at', 'desc')->get();
    }

}
