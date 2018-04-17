<?php
/**
 * Created by PhpStorm.
 * User: Jide Kolade
 * Date: 7/26/2016
 * Time: 2:46 PM
 */

namespace App\Helpers;

use Illuminate\Http\Request;
use App\Helpers\JWT;
use DB;
use Auth;
use Illuminate\Support\Facades\Session;
use Psy\Exception\ErrorException;
use Illuminate\Support\Facades\Storage;

class Utility
{

    const STATUS_INACTIVE = 0, STATUS_ACTIVE = 1, STATUS_DELETED = 2;
    const BLOCK_FOLLOWER_STATUS_ACTIVE = 1, BLOCK_FOLLOWER_STATUS_INACTIVE = 2, UNFOLLOW_STATUS = 3;
    const CDN_STATUS_NO = 0, CDN_STATUS_YES = 1;
    const CDN_URL = "";
    const TEMP_UPLOAD_PATH = "";
    const API_VERSION = "1.0";
    const RESPONSE_CODE_OK = "200", RESPONSE_CODE_CREATED = "201",
        RESPONSE_CODE_NO_CONTENT = "204", RESPONSE_CODE_INVALID_REQUEST = "400",
        RESPONSE_CODE_NOT_FOUND = "404", RESPONSE_CODE_SERVER_ERROR = "500";
    const ADMIN_ACCOUNT_TYPE = '1', ARTIST_ACCOUNT_TYPE = '2', USER_ACCOUNT_TYPE = '3',
        SHOW_PROMOTER = '7', MANAGER = '6', DJ = '5', PRODUCER = '4', MANAGEMENT = '10';
    const LIVE_FEED_PERMISSION = 1, IS_LIVE_FEED_STATUS = 1, NOT_LIVE_FEED_STATUS = 0;
    const YOUTUBE_VIDEO_TYPE = 1, FILE_VIDEO_TYPE = 2;
    const LIKE = 1, LOVE =2, HAHA = 3, WOW = 4, SAD = 5, ANGRY = 6;
    const AFROBEATLogin = 1 ,FACEBOOKLogin = 2, TWITTERLogin = 3;

    const TEMP_FILE_BASE_URL = "http://afrobeat.com/api/public/TEMP/";
    const TEMP_IMAGE_URL = self::TEMP_FILE_BASE_URL . "images/";
    const TEMP_AUDIO_URL = self::TEMP_FILE_BASE_URL . "audios/";
    const TEMP_VIDEO_URL = self::TEMP_FILE_BASE_URL . "videos/";

    const CDN_BASE_URL = "http://afrobeat.com/CDN_FILES/";
    const CDN_IMAGE_URL = 'https://s3.eu-central-1.amazonaws.com/static.afrobeat.com/images/';
    const CDN_AUDIO_URL = 'https://s3.eu-central-1.amazonaws.com/static.afrobeat.com/audios/';
    const CDN_VIDEO_URL = 'https://s3.eu-central-1.amazonaws.com/static.afrobeat.com/videos/';
    const DEFAULT_AVATAR = "http://afrobeat.com/api/public/thumbs/avatar.png";
    const DEFAULT_SONG_LOGO = "http://afrobeat.com/api/public/thumbs/afrobeat_logo.png";
    const CDN_IMAGE_URL_TEST = 'https://s3.eu-central-1.amazonaws.com/static.afrobeat.com/images/';

    const CDN_IMAGE = "img", CDN_AUDIO = "aud",CDN_VIDEO = "vid";

    public static function generateUID($table = null, $limit = 12) {
        $uid = "";
        $array = array_merge(range(0, 1), range(7, 9));
        for ($i = 0; $i < $limit; $i++) {
            $uid.=$array[array_rand($array)];
        }
        if($table === null){
            return $uid;
        }
        else{
            if(self::uidExists($uid , $table)){
                $uid =  self::generateUID($table , $limit);
            }
            return $uid;

        }

    }

    public static function genRememberToken($table = null, $limit = 12) {
        $token = "";
        $array = array_merge(range(0, 1), range(7, 9));
        for ($i = 0; $i < $limit; $i++) {
            $token.=$array[array_rand($array)];
        }
        $token.= time();
        if($table === null){
            return md5($token);
        }
        else{
            if(self::tokenExists($token , $table)){
                $token =  self::genRememberToken($table , $limit);
            }
            return md5($token);

        }

    }

    public static function generateSlug($nameTitle , $table,  $rand = false){
        $name1 = str_replace('/', '-', $nameTitle);
        $name2 = str_replace('&', '-', $name1);
        $name = str_replace(';', '-', $name2);
        $slug = !$rand ? strtolower(str_replace(' ', '-', $name)) :
            strtolower(str_replace(' ', '-', $name)) . '-' . self::generateUID(null , 4);

        if(self::slugExists($slug , $table)){
            
            $slug =  self::generateSlug($slug, $table , true);
        }

        return $slug;

    }

    public static function slugExists($slug, $table){
        $exists =  DB::table($table)->where('slug' , $slug)->count();
        if($exists){
            return true;
        }
        else{
            return false;
        }
        //return $exists > 0 ? true : false;
    }

    public static function uidExists($uid, $table){
        $exists =  DB::table($table)->where('uid' , $uid)->count();
        return $exists > 0 ? true : false;
    }

    public static function tokenExists($token, $table){
        $exists =  DB::table($table)->where('remember_token' , $token)->count();
        return $exists > 0 ? true : false;
    }

    public static function getRequestData(Request $request){
        return (object)$request->json()->all();
    }

    public static function getResponseData($code, $msg, $responseData = array()){

        $response = array(
            'version' => self::API_VERSION,
            "info" => [
                "code" => $code,
                "msg" => $msg
            ],
            "data" => $responseData
        );
        return response()->json($response);

    }

    /*
     * sanitizes parameter against xss
     */
    public static function sanitize(&$data){

        if(is_array($data)){
            foreach ($data as &$d){
                $d = htmlentities($d);
            }
        }
        else{
            $data = htmlentities($data);
        }


    }

    public static function hasAccess($signature){
        if($signature == Session()->get('token') && Auth::check()){
            return true;
        }
        return false;


    }

    public static function convertDate(&$date){

        return date("d M Y H:i:s", strtotime($date));
    }

    public static function formatDateFields(&$data){
        foreach($data as &$val){
         //$val->formatted_date = self::convertDate($val->created_at);
         $val->formatted_date = date('d M Y', strtotime($val->created_at));
        }
        return $data;
    }

    public static function getTempImagesPath(){
        return base_path() . '/public/TEMP/images/';
    }

    public static function getTempAudioFilesPath(){
        return base_path() . '/public/TEMP/audios/';
    }
    public static function getTempVideoFilesPath(){
        return base_path() . '/public/TEMP/videos/';
    }

    public static function updateCDNStatus($table, $uid, $cdn_status){
        return DB::table($table)
            ->where('uid' , $uid
            )->update(['cdn_status' => $cdn_status]);
    }

    public static function updateCDNStatusById($table, $id, $cdn_status){
        return DB::table($table)
            ->where('id' , $id
            )->update(['cdn_status' => $cdn_status]);
    }

    public static function updateAnyCDNStatus($table,$column, $id, $cdn_status){
        return DB::table($table)
            ->where('uid' , $id
            )->update([$column => $cdn_status]);
    }



    public static function moveToCDN($sourceFilePath, $destinationFileName, $fileType){
        $ftp = new FTP();
        $ftpConnectionStatus = $ftp->connect();
        if($ftpConnectionStatus){
            $uploadStatus = false;
            switch ($fileType){
                case self::CDN_IMAGE :
                    $uploadStatus = $ftp->moveImageToCDN($sourceFilePath , $destinationFileName);
                    break;
                case self::CDN_AUDIO :
                    $uploadStatus = $ftp->moveAudioToCDN($sourceFilePath , $destinationFileName);
                    break;
                case self::CDN_VIDEO :
                    $uploadStatus = $ftp->moveVideoToCDN($sourceFilePath , $destinationFileName);
                    break;
            }

            if($uploadStatus){
                @unlink($sourceFilePath);
            }
            return $uploadStatus;

        }
        return $ftpConnectionStatus;
    }

    public static function moveToCDNTest($sourceFilePath, $destinationFileName, $fileType){
        $ftp = new FTPtest();
            $uploadStatus = false;
            switch ($fileType){
                case self::CDN_IMAGE :
                    $uploadStatus = $ftp->moveImageToCDN($sourceFilePath , $destinationFileName);
                    break;
                case self::CDN_AUDIO :
                    $uploadStatus = $ftp->moveAudioToCDN($sourceFilePath , $destinationFileName);
                    break;
                case self::CDN_VIDEO :
                    $uploadStatus = $ftp->moveVideoToCDN($sourceFilePath , $destinationFileName);
                    break;
            }

            if($uploadStatus){
                @unlink($sourceFilePath);
            }
            return $uploadStatus;


    }

    public static function updateViews($array_content){
        $add_number = 1;
        if(Auth::check()){
            $add_number = (Auth::user()->role == '1') ? 0 : 1;
        }else {
            $add_number = 1;
        }
        return $array_content[0]->views + $add_number;
    }

    public static function updateViewsTable($table, $uid, $new_view){
        return DB::table($table)
            ->where('uid' , $uid
            )->update(['views' => $new_view]);
    }

    public static function resizeImages($image_path,$width,$height,$dest_path){
        // configure with favored image driver (gd by default)
        //Image::configure(array('driver' => 'imagick'));

        // and you are ready to go ...
        return Image::make($image_path)->resize($width, $height)->save($dest_path);
    }

    public static function humanTiming ($time)
    {

        $time = time() - intval($time); // to get the time since that moment
        $time = ($time<1)? 1 : $time;
        $tokens = array (
            31536000 => 'year',
            2592000 => 'month',
            604800 => 'week',
            86400 => 'day',
            3600 => 'hour',
            60 => 'minute',
            1 => 'second'
        );

        foreach ($tokens as $unit => $text) {
            if ($time < $unit) continue;
            $numberOfUnits = floor($time / $unit);
            return $numberOfUnits.' '.$text.(($numberOfUnits>1)?'s':'');
        }

    }

    public static function realDateInterval(&$dataObject){

        foreach($dataObject as &$value){

            $old_date = $value->created_at;
            $time = strtotime($old_date);
            //$diff = self::humanTiming($time).' ago';
            $diff = self::humanTiming($time);
            $value->time_past = $diff;
            //$value->time_past = $value->created_at->diffForHumans();
        }
        return $dataObject;

    }

    public static function commentHumanTiming($value){

            $old_date = $value;
            $time = strtotime($old_date);
            //$diff = self::humanTiming($time).' ago';
            $diff = self::humanTiming($time);
            return  $diff;
            //return $value->diffForHumans();


    }

    public static function scrapeSite($url){
        $ch = curl_init();

        $scraperUrlService ="http://playground.maxmorgandesign.com/url-details.php?url=" . $url;
        curl_setopt($ch,CURLOPT_URL,$scraperUrlService);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
        //  curl_setopt($ch,CURLOPT_HEADER, false);

        $output=curl_exec($ch);

        curl_close($ch);
        //print_r($output);
        return $output;
    }

    public static function comment($table,$column, $postUid, $commentText){
        return DB::table($table)
            ->insert([
                'user_id' => Auth::user()->uid,
                $column => $postUid,
                'comment_text' => $commentText,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
    }

    public static function fetchComment($table,$column, $postUid, $start, $limit){
        return DB::table($table)
            ->where($column,$postUid)
            ->skip($start)->take($limit)
            ->orderBy('created_at', 'desc')->get();
    }

    public static function like($table,$column, $postUid, $type)
    {

        return DB::table($table)
            ->insert([
                'user_id' => Auth::user()->uid,
                $column => $postUid,
                'type' => $type,
                'created_at' => date('Y-m-d H:i:s'),
            ]);

    }

    public static function likeExist($table,$column, $postUid)
    {

        return DB::table($table)
            ->where($column, $postUid)
            ->where('user_id', Auth::user()->uid)->get();


    }

    public static function fetchLikes($table,$column, $postUid)
    {

        return DB::table($table)
            ->where($column, $postUid)->get();


    }

    public static function getLikes($table,$column, $postUid, $start, $limit)
    {

        return DB::table($table)
            ->where($column, $postUid)
            ->skip($start)->take($limit)->get();


    }



}