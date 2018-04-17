<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Helpers\Utility;

class VideoTypes extends Model
{
    //
	protected $table = 'afrobt_video_types';

    protected $guarded = [];
    public $timestamps = false;

    public function createVideoType($type){

        $data = array(
            'type' => $type,
            'status' => Utility::STATUS_ACTIVE
        );

        return self::create($data);

    }

    public function editVideoType($id, $type)
    {
        return self::where('id' , $id)
            ->update(['type' => $type] );
    }

    public function deleteVideoType($id)
    {
        return self::where('id' , $id)
            ->update(['status' => Utility::STATUS_DELETED] );
    }

    public static function getVideoTypes($status = ""){
        $status = ($status === "" ? Utility::STATUS_ACTIVE : $status);
        return self::where('status' , $status)
            ->orderBy('type', 'asc')->get();
    }

    public static function getVideoType($id){
        return self::where('id' , $id)
            ->where("status", Utility::STATUS_ACTIVE)
            ->get();
    }

}
