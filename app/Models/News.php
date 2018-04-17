<?php

namespace App\Models;

use App\Helpers\Utility;
use Illuminate\Database\Eloquent\Model;
use Auth;
use DB;
class News extends Model
{
    //
    protected $table = 'afrobt_news';

    protected $guarded = [];

    public function section(){
        return $this->hasMany('App\Models\NewsSection', 'news_uid', 'uid');
    }

    public function category(){
        return $this->belongsTo('App\Models\NewsCatg', 'cat_id', 'id');
    }

    public function user(){
        return $this->belongsTo('App\User', 'user_id', 'uid');
    }

    public function admin(){
        return $this->belongsTo('App\User', 'admin_id', 'uid');
    }

    public function getNews($status = "", $start = 0, $limit = 20){
        $status = ($status === "" ? Utility::STATUS_ACTIVE : $status);
        return self::where('status' , $status)
            ->skip($start)->take($limit)
            ->orderBy('created_at', 'desc')->get();
    }

    public static function getRelatedNews($value){
        return static::where('status', '=',Utility::STATUS_ACTIVE)->orWhere(function ($query) use($value){
            $query->orwhere('news_title','LIKE','%'.$value.'%');
        })->skip('0')->take('5')
            ->orderBy('created_at', 'desc')->get();
    }

    public static function getRelatedNews1($value){
        $query = DB::query("select * from afrobt_news where
            news_title!= '".$value."' and status = '".Utility::STATUS_ACTIVE."' and
            match(news_title) against('+{$value}*' IN BOOLEAN MODE) ");
        return $query;
    }

    public function getSingleNews($slug){
        return self::where("slug" , $slug)
            ->where("status",Utility::STATUS_ACTIVE)
            ->get();
    }

    public function getNewsCategory($cat_id,$start = 0, $limit = 20){

        return self::where("cat_id" ,'LIKE', '%'.$cat_id.'%')
            ->where("status",Utility::STATUS_ACTIVE )
            ->skip($start)->take($limit)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public  function createNews( $catId, $title,$tag,
                                 $coverImg){

        $uid = Utility::generateUID($this->table);
        $slug = Utility::generateSlug($title, $this->table);

        $data = array(
            'uid' => $uid,
            'admin_id' => Auth::user()->uid,
            'cat_id' => $catId,
            'news_title' => $title,
            'tag' => $tag,
            'cover_img' => $coverImg,
            'status' => Utility::STATUS_ACTIVE,
            'slug' => $slug

        );
        return self::create($data);
    }

    public function editNews($uid, $title,$tag){
        $data = array(
            'news_title' => $title,
            'tag' => $tag
        );
        return self::where("uid" , $uid)
            ->update($data);
    }

    public function updateNewsLiveFeed($newsUid, $liveFeedStatus){
        $data = array(
            'is_livefeed' => $liveFeedStatus
        );
        return self::where("uid" , $newsUid)
            ->update($data);
    }

    public function deleteNews($uid){

        return self::where('uid' , $uid)
            ->update(['status' => Utility::STATUS_DELETED] );
    }

    public function updateNewsViews($uid,$new_count){

        return Utility::updateViewsTable($this->table, $uid, $new_count);
    }

    public function hasPushedToCDN($uid)
    {
        return Utility::updateCDNStatus($this->table, $uid, Utility::CDN_STATUS_YES);
    }

    public function editCoverImg($uid,  $coverImg){
        $data = array(
            'cover_img' => $coverImg

        );
        return self::where("uid" , $uid)
            ->update($data);
    }

}
