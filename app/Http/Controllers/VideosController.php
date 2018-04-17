<?php

namespace App\Http\Controllers;

use App\Http\Controllers;
use App\Helpers\Utility;
use App\models\Video_views;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\view;
use Monolog\Handler\Curl\Util;
use Validator;
use DB;
use App\User;
use App\Models\Videos;
use App\Models\VideosLikes;
use App\Models\VideosComments;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Http\Request;
use Auth;
use Dawson\Youtube\Youtube;


class VideosController extends Controller
{
    private $responseCode, $responseMessage, $responseData;
    public function getVideos(Request $request)
    {

        $params = Utility::getRequestData($request);
        Utility::sanitize($params->data);

        $video = new Videos();
        if (!array_key_exists('status', $params->data)) {
            $params->data["status"] = "";
        }
        $videos = $video->getVideos($params->data["status"],
            $params->data["start"],$params->data["limit"]);

        $this->setupResponseData($videos);

        $this->responseCode = Utility::RESPONSE_CODE_OK;
        $this->responseMessage = "Success";
        $this->responseData = array(
            "videos" =>$videos
        );

        return Utility::getResponseData($this->responseCode, $this->responseMessage, $this->responseData);
    }

    public function getVideosByArtist(Request $request)
    {

        $params = Utility::getRequestData($request);
        Utility::sanitize($params->data);

        $video = new Videos();
        if (!array_key_exists('status', $params->data)) {
            $params->data["status"] = "";
        }
        $videos = $video->getVideosByArtist($params->data["artist_uid"],
            $params->data["start"],$params->data["limit"]);

        $this->setupResponseData($videos);

        $this->responseCode = Utility::RESPONSE_CODE_OK;
        $this->responseMessage = "Success";
        $this->responseData = array(
            "videos" =>$videos
        );

        return Utility::getResponseData($this->responseCode, $this->responseMessage, $this->responseData);
    }

    public function getVideosByType(Request $request)
    {

        $params = Utility::getRequestData($request);
        Utility::sanitize($params->data);

        $video = new Videos();

        $videos = $video->getVideosByType($params->data["type"],
            $params->data["start"],$params->data["limit"]);

        $this->setupResponseData($videos);

        $this->responseCode = Utility::RESPONSE_CODE_OK;
        $this->responseMessage = "Success";
        $this->responseData = array(
            "videos" =>$videos
        );

        return Utility::getResponseData($this->responseCode, $this->responseMessage, $this->responseData);
    }

    public function getVideo(Request $request)
    {

        $params = Utility::getRequestData($request);
        Utility::sanitize($params->data);

        if (!array_key_exists('comment_start', $params->data)) {
            $params->data["comment_start"] = "0";
        }

        if (!array_key_exists('comment_limit', $params->data)) {
            $params->data["comment_limit"] = "10";
        }

        $video_view = new Video_views();
        $get= new Videos();
        $video = $get->getVideo($params->data["slug"]);

        //var_dump($video);

        if(count($video) > 0){
            $this->setupResponseDataSingle($video);
            $updateViews = Utility::updateViews($video);
            $updateVideoViews = $get->updateUserVideoViews($video[0]->uid,$updateViews);
            $create_view = $video_view->createView($video[0]->uid);
            $this->responseCode = Utility::RESPONSE_CODE_OK;
            $this->responseMessage = "Success";
            $this->responseData = array(
                "video" =>$video
            );
        }
        else{
            $this->responseCode = Utility::RESPONSE_CODE_NOT_FOUND;
            $this->responseMessage = "Not Found";
            $this->responseData = array();
        }



        return Utility::getResponseData($this->responseCode, $this->responseMessage, $this->responseData);
    }


    public function create(Request $request){

        $title = $request->get('title');

        Utility::sanitize($title);
        //print_r($title); exit();
        $jwt = $request->get('user');
        Utility::sanitize($jwt);
        $tag = $request->get('tag');
        Utility::sanitize($tag);
        $type = $request->get('type');
        Utility::sanitize($type);
        $artist_uid = $request->get('artist_uid');
        Utility::sanitize($artist_uid);
        $upload_type = $request->get('video_upload_type');
        Utility::sanitize($upload_type);

        $video_file_name = '';
        $file_name = '';
        $link = ($upload_type == Utility::YOUTUBE_VIDEO_TYPE) ? $link = $request->get('link') : "" ;
        Utility::sanitize($link);

        if(Utility::hasAccess($jwt)) {

            if ($request->file('video_cover') != '') {
                $file_name = time() . "_" . Utility::generateUID(null, 10) . "." . $request->file('video_cover')->getClientOriginalExtension();
                $request->file('video_cover')->move(
                    Utility::getTempImagesPath(), $file_name
                );
            }

            if ($upload_type == Utility::FILE_VIDEO_TYPE) {

                $video_file_name = time() . "_" . Utility::generateUID(null, 10) . "." . $request->file('video_file')->getClientOriginalExtension();
                $request->file('video_file')->move(
                    Utility::getTempVideoFilesPath(), $video_file_name
                );


            }

            $video = new Videos();

            $create = $video->createVideo($type,$upload_type,$video_file_name,$artist_uid, $title,$file_name, $link,$tag);
            if ($request->file('video_cover') != '') {
                $srcFile = Utility::getTempImagesPath() . $file_name;
                if (Utility::moveToCDN($srcFile, $file_name, Utility::CDN_IMAGE)) {
                    //Update CDN STATUS in db table
                    $video->hasPushedToCDN($create->uid);
                    $create->video_cover = Utility::CDN_IMAGE_URL . $file_name;
                } else {
                    $create->video_cover = Utility::TEMP_IMAGE_URL . $file_name;
                    //UPLOAD TO CDN FAILED OR FTP COULD NOT CONNECT
                    //CRON WOULD HANDLE FAILED UPLOADS
                }
            }

            if($upload_type == Utility::FILE_VIDEO_TYPE){
                $videoSrcFile = Utility::getTempVideoFilesPath() . $video_file_name;
                if(Utility::moveToCDN($videoSrcFile , $video_file_name, Utility::CDN_VIDEO)){
                    //Update CDN STATUS in db table
                    $video->hasPushedToCDN($create->uid);
                    $create->video_file = Utility::CDN_VIDEO_URL . $video_file_name;
                }
                else{
                    $create->video_file = Utility::TEMP_VIDEO_URL . $video_file_name;
                    //UPLOAD TO CDN FAILED OR FTP COULD NOT CONNECT
                    //CRON WOULD HANDLE FAILED UPLOADS
                }

            }

            $this->responseCode = Utility::RESPONSE_CODE_CREATED;
            $this->responseMessage = "Success";
            $this->responseData = array(
                "video" =>$create
            );

        }
        else{
            $this->responseCode = Utility::RESPONSE_CODE_INVALID_REQUEST;
            $this->responseMessage = "Unauthenticated User";

            $this->responseData = array();
        }

        return Utility::getResponseData($this->responseCode, $this->responseMessage, $this->responseData);
    }


    public function createUserVideo(Request $request){

        $title = $request->get('title');
        Utility::sanitize($title);
        $jwt = $request->get('user');
        Utility::sanitize($jwt);
        $type = $request->get('type');
        Utility::sanitize($type);
        $upload_type = $request->get('video_upload_type');
        Utility::sanitize($upload_type);

        $video_file_name = '';
        if($upload_type == Utility::YOUTUBE_VIDEO_TYPE) {

            $link = $request->get('link');
            Utility::sanitize($link);

        }
        $link = ($upload_type == Utility::YOUTUBE_VIDEO_TYPE) ? $link = $request->get('link') : "" ;

        if(Utility::hasAccess($jwt)) {
            if ($request->file('video_cover') != ''){
                $file_name = time() . "_" . Utility::generateUID(null, 10) . "." . $request->file('video_cover')->getClientOriginalExtension();
            $request->file('video_cover')->move(
                Utility::getTempImagesPath(), $file_name
            );
        }
            if ($upload_type == Utility::FILE_VIDEO_TYPE) {

                $video_file_name = time() . "_" . Utility::generateUID(null, 10) . "." . $request->file('video_file')->getClientOriginalExtension();
                $request->file('video_file')->move(
                    Utility::getTempImagesPath(), $video_file_name
                );


            }

            $video = new Videos();

            $create = $video->createUserVideo($type,$upload_type,$video_file_name, $title, $file_name, $link);

            if($request->file('video_cover') != '') {
                $srcFile = Utility::getTempImagesPath() . $file_name;
                if (Utility::moveToCDN($srcFile, $file_name, Utility::CDN_IMAGE)) {
                    //Update CDN STATUS in db table
                    $video->hasPushedToCDN($create->uid);
                    $create->video_cover = Utility::CDN_IMAGE_URL . $file_name;
                } else {
                    $create->video_cover = Utility::TEMP_IMAGE_URL . $file_name;
                    //UPLOAD TO CDN FAILED OR FTP COULD NOT CONNECT
                    //CRON WOULD HANDLE FAILED UPLOADS
                }
            }
            if($upload_type == Utility::FILE_VIDEO_TYPE){

                if(Utility::moveToCDN($srcFile , $video_file_name, Utility::CDN_VIDEO)){
                    //Update CDN STATUS in db table
                    $video->hasPushedToCDN($create->uid);
                    $create->video_file = Utility::CDN_VIDEO_URL . $video_file_name;
                }
                else{
                    $create->video_file = Utility::TEMP_VIDEO_URL . $video_file_name;
                    //UPLOAD TO CDN FAILED OR FTP COULD NOT CONNECT
                    //CRON WOULD HANDLE FAILED UPLOADS
                }

            }

            $this->responseCode = Utility::RESPONSE_CODE_CREATED;
            $this->responseMessage = "Success";
            $this->responseData = array(
                "video" =>$create
            );

        }
        else{
            $this->responseCode = Utility::RESPONSE_CODE_INVALID_REQUEST;
            $this->responseMessage = "Unauthenticated User";

            $this->responseData = array();
        }

        return Utility::getResponseData($this->responseCode, $this->responseMessage, $this->responseData);
    }

    public function edit(Request $request){

        $title = $request->get('title');
        Utility::sanitize($title);
        $jwt = $request->get('user');
        Utility::sanitize($jwt);
        $tag = $request->get('tag');
        Utility::sanitize($tag);
        $type = $request->get('type');
        Utility::sanitize($type);
        $link = $request->get('link');
        Utility::sanitize($link);
        $artist_uid = $request->get('artist_uid');
        Utility::sanitize($artist_uid);
        $uid = $request->get('uid');
        Utility::sanitize($uid);

        if(Utility::hasAccess($jwt)) {

            $video = new Videos();
            $create = $video->editVideo( $uid, $type,
                $artist_uid, $title,$link,$tag);

            $this->responseCode = Utility::RESPONSE_CODE_CREATED;
            $this->responseMessage = "Success";
            $this->responseData = array();

        }
        else{
            $this->responseCode = Utility::RESPONSE_CODE_INVALID_REQUEST;
            $this->responseMessage = "Unauthenticated User";

            $this->responseData = array();
        }

        return Utility::getResponseData($this->responseCode, $this->responseMessage, $this->responseData);
    }

    public function editVideoCover(Request $request){

        $uid = $request->get('uid');
        $jwt = $request->get('user');
        $videoCover = time() . "_" . Utility::generateUID(null, 10) . "." . $request->file('video_cover')->getClientOriginalExtension();
        Utility::sanitize($jwt);
        Utility::sanitize($uid);

        if(Utility::hasAccess($jwt)) {

            $request->file('video_cover')->move(
                Utility::getTempImagesPath(), $videoCover
            );



            $video = new Videos();
            $edit = $video->editVideoCover( $uid,
                $videoCover);

            $srcFile = Utility::getTempImagesPath() . $videoCover;
            //echo $srcFile . "\n";

            if(Utility::moveToCDN($srcFile , $videoCover, Utility::CDN_IMAGE)){
                //Update CDN STATUS in db table
                $video->hasPushedToCDN($uid);
                $videoCover = Utility::CDN_IMAGE_URL . $videoCover;
            }
            else{
                $videoCover = Utility::TEMP_IMAGE_URL . $videoCover;

            }

            $this->responseCode = Utility::RESPONSE_CODE_CREATED;
            $this->responseMessage = "Success";
            $this->responseData = array(
                "videoCover" => $videoCover
            );

        }
        else{
            $this->responseCode = Utility::RESPONSE_CODE_INVALID_REQUEST;
            $this->responseMessage = "Unauthenticated User";

            $this->responseData = array();
        }

        return Utility::getResponseData($this->responseCode, $this->responseMessage, $this->responseData);

    }

    public function delete(Request $request)
    {

        $params = Utility::getRequestData($request);
        Utility::sanitize($params->data);
        if(Utility::hasAccess($params->data["user"])) {
            $get= new Videos();
            $video = $get->deleteVideo($params->data["uid"]);

            $this->responseCode = Utility::RESPONSE_CODE_NO_CONTENT;
            $this->responseMessage = "Success";
            $this->responseData = array();

        }
        else{
            $this->responseCode = Utility::RESPONSE_CODE_INVALID_REQUEST;
            $this->responseMessage = "Unauthenticated User";

            $this->responseData = array();
        }

        return Utility::getResponseData($this->responseCode, $this->responseMessage, $this->responseData);
    }

    public function loadMoreComments(Request $request)
    {

        $params = Utility::getRequestData($request);
        Utility::sanitize($params->data);

        $comment = new VideosComments();

        $feed = $comment->loadMore($params->data["last_id"],'video_uid',$params->data["post_uid"]);
        Utility::realDateInterval($feed);
        Utility::formatDateFields($feed);
        $this->setupResponseDataLoad($feed,'0',$params->data["limit"]);
        $this->responseCode = Utility::RESPONSE_CODE_OK;
        $this->responseMessage = "Success";
        $this->responseData = array(
            "comments" =>$feed
        );


        return Utility::getResponseData($this->responseCode, $this->responseMessage, $this->responseData);
    }

    public function loadMoreLikes(Request $request)
    {

        $params = Utility::getRequestData($request);
        Utility::sanitize($params->data);

        $comment = new VideosLikes();

        $feed = $comment->loadMore($params->data["last_id"],'video_uid',$params->data["post_uid"]);
        Utility::realDateInterval($feed);
        Utility::formatDateFields($feed);
        $this->setupResponseDataLoad($feed,'0',$params->data["limit"]);
        $this->responseCode = Utility::RESPONSE_CODE_OK;
        $this->responseMessage = "Success";
        $this->responseData = array(
            "likes" =>$feed
        );


        return Utility::getResponseData($this->responseCode, $this->responseMessage, $this->responseData);
    }

    private function setupResponseDataSingle($data,$start,$limit){
        $total_row = count($data);
        foreach ($data as $d){
            $d->total_rows = $total_row;
            if($d->video_cover != '') {
                if ($d->cdn_status == Utility::CDN_STATUS_YES) {
                    $d->real_cover = Utility::CDN_IMAGE_URL . $d->video_cover;
                } else {
                    $d->real_cover = Utility::TEMP_IMAGE_URL . $d->video_cover;
                }
            }

            if($d->video_type == Utility::FILE_VIDEO_TYPE){

                if($d->video_cdn_status == Utility::CDN_STATUS_YES){
                    $d->video_file = Utility::CDN_VIDEO_URL . $d->file_name;
                }
                else{
                    $d->video_file = Utility::TEMP_VIDEO_URL . $d->file_name;
                }

            }
            if($d->artist_uid != '') {
                $d->artist;
                if ($d->artist->cdn_status == Utility::CDN_STATUS_YES) {
                    $d->artist->real_avatar = Utility::CDN_IMAGE_URL . $d->artist->avatar;

                } else {
                    $d->artist->real_avatar = Utility::TEMP_IMAGE_URL . $d->artist->avatar;
                }
            }

            $model = new VideosComments();
            $fetch_comment = $model->fetchComment('video_uid', $d->uid, $start, $limit);
            $d->total_comments = count($fetch_comment);
            foreach($fetch_comment as $com){
                $user_data = [];
                $user = new User();
                $log_user = $user->getSingleUserByUid($com->user_id);
                $user_data['full_name'] = $log_user->full_name;
                $user_data['email'] = $log_user->email;
                $user_data['username'] = $log_user->username;
                $user_data['bio'] = $log_user->bio;
                $user_data['slug'] = $log_user->slug;

                if($log_user->cdn_status == Utility::CDN_STATUS_YES){
                    $user_data['avatar']  = Utility::CDN_IMAGE_URL . $log_user->avatar;
                }
                else{
                    $user_data['avatar']  = Utility::TEMP_IMAGE_URL . $log_user->avatar;
                }

                $com->user_data = $user_data;
                $com->comment_time = Utility::commentHumanTiming($com->created_at);

            }

            $model2 = new VideosLikes();
            $fetch_likes = $model2->fetchLikes('video_uid', $d->uid, $start, $limit);
            $d->total_likes = count($fetch_likes);
            foreach($fetch_likes as $com){
                $user_data = [];
                $user = new User();
                $log_user = $user->getSingleUserByUid($com->user_id);
                $user_data['full_name'] = $log_user->full_name;
                $user_data['email'] = $log_user->email;
                $user_data['username'] = $log_user->username;
                $user_data['bio'] = $log_user->bio;
                $user_data['slug'] = $log_user->slug;

                if($log_user->cdn_status == Utility::CDN_STATUS_YES){
                    $user_data['avatar']  = Utility::CDN_IMAGE_URL . $log_user->avatar;
                }
                else{
                    $user_data['avatar']  = Utility::TEMP_IMAGE_URL . $log_user->avatar;
                }

                $com->user_data = $user_data;
                $com->like_time = Utility::commentHumanTiming($com->created_at);

            }
            $d->likes = $fetch_likes;

            $d->comments = $fetch_comment;

        }
    }

    private function setupResponseData($data){
        $total_row = count($data);
        foreach ($data as $d){
            $d->total_rows = $total_row;
            if($d->cdn_status == Utility::CDN_STATUS_YES){
                $d->real_cover = Utility::CDN_IMAGE_URL . $d->video_cover;
            }
            else{
                $d->real_cover = Utility::TEMP_IMAGE_URL . $d->video_cover;
            }

            if($d->video_type == Utility::FILE_VIDEO_TYPE){

                if($d->video_cdn_status == Utility::CDN_STATUS_YES){
                    $d->video_file = Utility::CDN_VIDEO_URL . $d->file_name;
                }
                else{
                    $d->video_file = Utility::TEMP_VIDEO_URL . $d->file_name;
                }

            }

            if($d->artist_uid != '') {
                $d->artist;
                if ($d->artist->cdn_status == Utility::CDN_STATUS_YES) {
                    $d->artist->real_avatar = Utility::CDN_IMAGE_URL . $d->artist->avatar;

                } else {
                    $d->artist->real_avatar = Utility::TEMP_IMAGE_URL . $d->artist->avatar;
                }
            }

            $model = new VideosComments();
            $model2 = new VideosLikes();
            $count_likes = $model2->getLikes("video_uid",$d->uid);
            $count_comment = $model->getComment("video_uid",$d->uid);
            $d->total_comments = (count($count_comment) == 0) ? "" : count($count_comment);
            $d->total_likes = (count($count_likes) == 0) ? "" : count($count_likes);

        }
    }



}
