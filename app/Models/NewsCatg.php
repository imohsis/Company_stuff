<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Helpers\Utility;
use Auth;

class NewsCatg extends Model
{
    //
	protected $table = 'afrobt_news_catg';

    protected $guarded = [];
    public $timestamps = false;

    public function createNewsCatg($catg){

        $data = array(
            'cat_name' => $catg,
            'status' => Utility::STATUS_ACTIVE
        );

        return self::create($data);

    }

    public function editNewsCatg($id, $catg)
    {
        return self::where('id' , $id)
            ->update(['cat_name' => $catg] );
    }

    public function deleteNewsCatg($id)
    {
        return self::where('id' , $id)
            ->update(['status' => Utility::STATUS_DELETED] );
    }

    public static function getNewsCatgs($status = ""){
        $status = ($status === "" ? Utility::STATUS_ACTIVE : $status);
        return self::where('status' , $status)
            ->orderBy('cat_name', 'asc')->get();
    }

    public static function getSingleNewsCatg($id){
        return self::where('id' , $id)
            ->where("status", Utility::STATUS_ACTIVE)
            ->get();
    }

    public function getCategories($cat_id = []){

        return self::whereIn("id" ,$cat_id)
            ->where("status",Utility::STATUS_ACTIVE )
            ->orderBy('id', 'desc')
            ->get();
    }


}
