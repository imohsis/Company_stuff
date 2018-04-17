<?php

namespace App\models;

use App\Helpers\Utility;
use Illuminate\Database\Eloquent\Model;
use Auth;

class Music_views extends Model
{
    //
    protected $table = 'afrobt_music_views';
    protected $guarded = [];

    public  function createView( $videoUid){

        $data = array(
            'music_uid' => $videoUid
        );
        return self::create($data);
    }

}
