<?php

namespace App\models;

use App\Helpers\Utility;
use Illuminate\Database\Eloquent\Model;
use Auth;

class Video_views extends Model
{
    //
    protected $table = 'afrobt_video_views';
    protected $guarded = [];

    public  function createView( $videoUid){

        $data = array(
            'video_uid' => $videoUid
        );
        return self::create($data);
    }

}
