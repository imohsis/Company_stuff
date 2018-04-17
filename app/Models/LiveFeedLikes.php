<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;
use App\Helpers\Utility;
use Auth;

class LiveFeedLikes extends Model
{
    //
    protected $table = 'afrobt_live_feeds_likes';

    protected $guarded = [];

    public function like($column, $postUid, $type)
    {
        return Utility::like($this->table,$column, $postUid, $type);
    }

    public function likeExist($column, $postUid)
    {
        return Utility::likeExist($this->table,$column, $postUid);
    }

    public function fetchLikes($column, $postUid, $start, $limit)
    {
        return Utility::getLikes($this->table, $column, $postUid, $start, $limit);
    }

    public function getLikes($column,$postUid)
    {
        return self::where($column,$postUid)->get();
    }

    public function getUserLiked($column,$postUid)
    {
        return self::where($column,$postUid)
            ->where('user_id',Auth::user()->uid)->get();
    }

    public function deleteLiked($column,$postUid){

        return self::where($column , $postUid)
            ->where("user_id",Auth::user()->uid)->delete();
    }


    public function loadMore($lastID,$column,$postUid){

        return self::where($column,$postUid)
            ->where('id','>',$lastID)
            ->orderBy('created_at', 'desc')->get();
    }


}
