<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Helpers\Utility;


class Genres extends Model
{
    //
	protected $table = 'afrobt_genres';
    protected $guarded = [];
    public $timestamps = false;

    public function createGenre($name){

        $data = array(
            'genre' => $name,
            'status' => Utility::STATUS_ACTIVE
        );

        return self::create($data);

    }

    public function editGenre($id, $name)
    {
        return self::where('id' , $id)
            ->update(['genre' => $name] );
    }

    public function deleteGenre($id)
    {
        return self::where('id' , $id)
            ->update(['status' => Utility::STATUS_DELETED] );
    }

    public function getGenres($status = ""){
        $status = ($status === "" ? Utility::STATUS_ACTIVE : $status);
        return self::where('status' , $status)
            ->orderBy('genre', 'asc')->get();
    }

    public function getGenre($id){
        return self::where('id' , $id)
            ->where("status", Utility::STATUS_ACTIVE)
            ->get();
    }




}
