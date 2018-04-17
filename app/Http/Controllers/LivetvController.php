<?php

namespace App\Http\Controllers;

use App\Http\Controllers;
use App\Helpers\FTP;
use App\Helpers\Utility;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\view;
use Monolog\Handler\Curl\Util;
use phpDocumentor\Reflection\Types\Null_;
use Validator;
use DB;
use App\User;
use App\Models\Livetv;
use App\Models\LivetvComments;
use App\Models\LivetvLikes;
use App\Models\NewsCatg;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Http\Request;
use Auth;
use Input;

class LivetvController extends Controller
{
    //

    private $responseCode, $responseMessage, $responseData;

    public function getLivetv(Request $request)
    {

        $params = Utility::getRequestData($request);
        Utility::sanitize($params->data);

        $livetv = new Livetv();
        if (!array_key_exists('status', $params->data)) {
            $params->data["status"] = "";
        }


        $tv = $livetv->getLivetv($params->data["status"],
            $params->data["start"],$params->data["limit"]);
        Utility::realDateInterval($tv);
        Utility::formatDateFields($tv);
        $this->alignResponseData($tv);
        $this->responseCode = Utility::RESPONSE_CODE_OK;
        $this->responseMessage = "Success";
        $this->responseData = array(
            "Livetv" =>$tv
        );


        return Utility::getResponseData($this->responseCode, $this->responseMessage, $this->responseData);
    }

    public function getLivetvUpdates(Request $request)
    {

        $params = Utility::getRequestData($request);
        Utility::sanitize($params->data);

        $livetv = new Livetv();

        $tv = $livetv->getLivetvUpdates($params->data["last_id"]);
        Utility::realDateInterval($tv);
        Utility::formatDateFields($tv);
        $this->alignResponseData($tv);
        $this->responseCode = Utility::RESPONSE_CODE_OK;
        $this->responseMessage = "Success";
        $this->responseData = array(
            "Livetv" =>$tv
        );


        return Utility::getResponseData($this->responseCode, $this->responseMessage, $this->responseData);
    }


    public function getPosterLivetv(Request $request)
    {

        $params = Utility::getRequestData($request); //convert json request to array object data
        Utility::sanitize($params->data); //a must sanitization

        $get= new Livetv();
        $tv = $get->getPosterLivetv($params->data["acc_type"],
            $params->data["start"],$params->data["limit"]);
        Utility::formatDateFields($tv);

        if(count($tv) > 0){
            $this->alignResponseData($tv);
            $this->responseCode = Utility::RESPONSE_CODE_OK;
            $this->responseMessage = "Success";
            $this->responseData = array(
                "posterLivetv" =>$tv
            );
        }
        else{
            $this->responseCode = Utility::RESPONSE_CODE_NOT_FOUND;
            $this->responseMessage = "Not Found";
            $this->responseData = array();
        }

        return Utility::getResponseData($this->responseCode, $this->responseMessage, $this->responseData);
    }

    public function getUniquePosterLivetv(Request $request)
    {

        $params = Utility::getRequestData($request); //convert json request to array object data
        Utility::sanitize($params->data); //a must sanitization

        $get= new Livetv();
        $tv = $get->getUniquePosterLivetv(Auth::user()->uid,
            $params->data["start"],$params->data["limit"]);
        Utility::formatDateFields($tv);

        if(count($tv) > 0){
            $this->alignResponseData($tv);
            $this->responseCode = Utility::RESPONSE_CODE_OK;
            $this->responseMessage = "Success";
            $this->responseData = array(
                "posterLivetv" =>$tv
            );
        }
        else{
            $this->responseCode = Utility::RESPONSE_CODE_NOT_FOUND;
            $this->responseMessage = "Not Found";
            $this->responseData = array();
        }

        return Utility::getResponseData($this->responseCode, $this->responseMessage, $this->responseData);
    }

    public function getSingleLivetv(Request $request)
    {

        $params = Utility::getRequestData($request); //convert json request to array object data
        Utility::sanitize($params->data); //a must sanitization

        if (!array_key_exists('comment_start', $params->data)) {
            $params->data["comment_start"] = "0";
        }

        if (!array_key_exists('comment_limit', $params->data)) {
            $params->data["comment_limit"] = "10";
        }

        $get= new Livetv();
        $tv = $get->getSingleLivetv($params->data["uid"]);


        Utility::formatDateFields($tv);
        $this->alignResponseDataSingle($tv,$params->data["comment_start"],$params->data["comment_limit"]);

        if(count($tv) > 0){
            $updateViews = Utility::updateViews($tv);
            $updateNewsViews = $get->updateLivetvViews($tv[0]->uid,$updateViews);
            $this->responseCode = Utility::RESPONSE_CODE_OK;
            $this->responseMessage = "Success";
            $this->responseData = array(
                "livetv" =>$tv
            );
        }
        else{
            $this->responseCode = Utility::RESPONSE_CODE_NOT_FOUND;
            $this->responseMessage = "Not Found";
            $this->responseData = array();
        }

        return Utility::getResponseData($this->responseCode, $this->responseMessage, $this->responseData);
    }

    public function getCurrentLiveVideo(Request $request)
    {

        $params = Utility::getRequestData($request); //convert json request to array object data
        Utility::sanitize($params->data); //a must sanitization

        if (!array_key_exists('comment_start', $params->data)) {
            $params->data["comment_start"] = "0";
        }

        if (!array_key_exists('comment_limit', $params->data)) {
            $params->data["comment_limit"] = "10";
        }

        $get= new Livetv();
        $tv = $get->getActiveLiveVideo();


        Utility::formatDateFields($tv);
        $this->alignResponseDataSingle($tv,$params->data["comment_start"],$params->data["comment_limit"]);

        if(count($tv) > 0){
            $this->responseCode = Utility::RESPONSE_CODE_OK;
            $this->responseMessage = "Success";
            $this->responseData = array(
                "livetv" =>$tv
            );
        }
        else{
            $this->responseCode = Utility::RESPONSE_CODE_NOT_FOUND;
            $this->responseMessage = "Not Found";
            $this->responseData = array(
                "livetv" =>[]
            );
        }

        return Utility::getResponseData($this->responseCode, $this->responseMessage, $this->responseData);
    }


    public function create(Request $request){

        $jwt = $request->get('user');
        Utility::sanitize($jwt);
        $type = $request->get('video_type');
        Utility::sanitize($type);
        $video_status = $request->get('video_status');
        Utility::sanitize($video_status);
        $title = $request->get('title');
        Utility::sanitize($title);

        $video_file = '';

        if(Utility::hasAccess($jwt)) {
            $thumbnail_file = time() . "_" . Utility::generateUID(null, 10) . "." . $request->file('thumbnail')->getClientOriginalExtension();

            $request->file('thumbnail')->move(
                Utility::getTempImagesPath(), $thumbnail_file
            );
            //print_r($request->file('attachment')[1]); exit;
            if($type == Utility::FILE_VIDEO_TYPE){
                $file = $request->file('attachment');

                    $attachment = time() . "_" . Utility::generateUID(null, 10) . "." . $file->getClientOriginalExtension();
                $video_file = $attachment;
                    $file->move(
                        Utility::getTempImagesPath(), $attachment
                    );
                //print_r($append); exit;
            }

            $tv_type = '';
            $attachment_type = '';
            if($type == Utility::FILE_VIDEO_TYPE){
                $tv_type = Utility::FILE_VIDEO_TYPE;
                $attachment_type = $video_file;
            }

            if($type == Utility::YOUTUBE_VIDEO_TYPE){

                $youtube_id = $request->get('youtube_id');
                Utility::sanitize($content);
                $tv_type = Utility::YOUTUBE_VIDEO_TYPE;
                $attachment_type = $youtube_id;
            }

            $livetv = new Livetv();
            $create = $livetv->createLivetv( Auth::user()->uid,Auth::user()->acc_type,
                $title,$thumbnail_file,$tv_type, $attachment_type,$video_status);

            $activeLiveVideo = $livetv->getActiveLiveVideo();
            if($video_status == Utility::STATUS_ACTIVE){
                if(count($activeLiveVideo) > 0){
                    $deactivate = $livetv->activateVideoToLive($activeLiveVideo[0]->uid,Utility::STATUS_INACTIVE);
                    $activate = $livetv->activateVideoToLive($create->uid,Utility::STATUS_ACTIVE);
                }else{
                    $activate = $livetv->activateVideoToLive($create->uid,Utility::STATUS_INACTIVE);
                }
            }


            //MOVE THUMBNAIL IMAGE FILE TO CDN
            $srcFile = Utility::getTempImagesPath() . $thumbnail_file;
            if(Utility::moveToCDN($srcFile , $thumbnail_file, Utility::CDN_IMAGE)){
                //Update CDN STATUS in db table
                $livetv->hasPushedToCDN($create->uid,'thumbnail_cdn_status');
                $create->thumbnail = Utility::CDN_IMAGE_URL . $thumbnail_file;
            }
            else{
                $create->thumbnail = Utility::TEMP_IMAGE_URL . $thumbnail_file;
                //UPLOAD TO CDN FAILED OR FTP COULD NOT CONNECT
                //CRON WOULD HANDLE FAILED UPLOADS
            }

            //MOVE VIDEO TO CDN IF VIDEO TYPE IS VIDEO FILE
            if($request->get('video_type') == Utility::FILE_VIDEO_TYPE){
                $file = $request->file('attachment');

                $attachment = time() . "_" . Utility::generateUID(null, 10) . "." . $file->getClientOriginalExtension();
                $srcFile = Utility::getTempImagesPath() . $attachment;

                if (Utility::moveToCDN($srcFile, $attachment, Utility::CDN_VIDEO)) {
                    //Update CDN STATUS in db table
                    $livetv->hasPushedToCDN($create->uid, 'video_cdn_status');
                    $create->real_video =  Utility::CDN_VIDEO_URL . $attachment;
                } else {
                    $create->temp_video =  Utility::TEMP_VIDEO_URL . $attachment;
                    //UPLOAD TO CDN FAILED OR FTP COULD NOT CONNECT
                    //CRON WOULD HANDLE FAILED UPLOADS
                }


                //print_r($append); exit;
            }

            $this->responseCode = Utility::RESPONSE_CODE_CREATED;
            $this->responseMessage = "Success";
            $this->responseData = array(
                "live_tv" =>$create
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

        $jwt = $request->get('user');
        Utility::sanitize($jwt);
        $uid = $request->get('uid');
        Utility::sanitize($uid);
        $title = $request->get('title');
        Utility::sanitize($title);
        $youtube_id = $request->get('youtube_id');
        Utility::sanitize($content);

        if(Utility::hasAccess($jwt)) {

            $livetv = new Livetv();
            $create = $livetv->updateLivetv($request->get('uid'),
                $request->get('title'),$youtube_id);

            //ADD NEW IMAGE PATH URL TO RESPONSE


            $this->responseCode = Utility::RESPONSE_CODE_CREATED;
            $this->responseMessage = "Success";

        }
        else{
            $this->responseCode = Utility::RESPONSE_CODE_INVALID_REQUEST;
            $this->responseMessage = "Unauthenticated User";

        }

        return Utility::getResponseData($this->responseCode, $this->responseMessage);
    }

    public function editThumbnail(Request $request){

        $uid = $request->get('uid');
        $jwt = $request->get('user');
        $image = time() . "_" . Utility::generateUID(null, 10) . "." . $request->file('thumbnail')->getClientOriginalExtension();
        Utility::sanitize($jwt);
        Utility::sanitize($uid);

        if(Utility::hasAccess($jwt)) {

            $request->file('thumbnail')->move(
                Utility::getTempImagesPath(), $image
            );



            $lifetv = new Livetv();
            $edit = $lifetv->updateImage( $uid,'thumbnail',
                $image);

            $srcFile = Utility::getTempImagesPath() . $image;
            //echo $srcFile . "\n";

            if(Utility::moveToCDN($srcFile , $image, Utility::CDN_IMAGE)){
                //Update CDN STATUS in db table
                $lifetv->hasPushedToCDN($uid,'thumbnail_cdn_status');
                $image = Utility::CDN_IMAGE_URL . $image;
            }
            else{
                $image = Utility::TEMP_IMAGE_URL . $image;

            }

            $this->responseCode = Utility::RESPONSE_CODE_CREATED;
            $this->responseMessage = "Success";
            $this->responseData = array(
                "thumbnail" => $image
            );

        }
        else{
            $this->responseCode = Utility::RESPONSE_CODE_INVALID_REQUEST;
            $this->responseMessage = "Unauthenticated User";

            $this->responseData = array();
        }

        return Utility::getResponseData($this->responseCode, $this->responseMessage, $this->responseData);

    }

    public function editAttachment(Request $request){

        $uid = $request->get('uid');
        $jwt = $request->get('user');
        $image = time() . "_" . Utility::generateUID(null, 10) . "." . $request->file('attachment')->getClientOriginalExtension();
        Utility::sanitize($jwt);
        Utility::sanitize($uid);

        if(Utility::hasAccess($jwt)) {

            $request->file('attachment')->move(
                Utility::getTempImagesPath(), $image
            );



            $lifetv = new Livetv();
            $edit = $lifetv->updateImage( $uid,'video',
                $image);

            $srcFile = Utility::getTempImagesPath() . $image;
            //echo $srcFile . "\n";

            if(Utility::moveToCDN($srcFile , $image, Utility::CDN_IMAGE)){
                //Update CDN STATUS in db table
                $lifetv->hasPushedToCDN($uid,'video_cdn_status');
                $image = Utility::CDN_IMAGE_URL . $image;
            }
            else{
                $image = Utility::TEMP_IMAGE_URL . $image;

            }

            $this->responseCode = Utility::RESPONSE_CODE_CREATED;
            $this->responseMessage = "Success";
            $this->responseData = array(
                "attachment" => $image
            );

        }
        else{
            $this->responseCode = Utility::RESPONSE_CODE_INVALID_REQUEST;
            $this->responseMessage = "Unauthenticated User";

            $this->responseData = array();
        }

        return Utility::getResponseData($this->responseCode, $this->responseMessage, $this->responseData);

    }

    public function ChangeLivetvStatus(Request $request)
    {

        $params = Utility::getRequestData($request);
        Utility::sanitize($params->data);
        if(Utility::hasAccess($params->data["user"])) {
            $uid = $params->data["uid"];
            $livetv= new Livetv();
            $activeLiveVideo = $livetv->getActiveLiveVideo();

                if(count($activeLiveVideo) > 0){
                    $deactivate = $livetv->activateVideoToLive($activeLiveVideo[0]->uid, Utility::STATUS_INACTIVE);
                    $activate = $livetv->activateVideoToLive($uid,Utility::STATUS_ACTIVE);
                }else{
                    $activate = $livetv->activateVideoToLive($uid,Utility::STATUS_INACTIVE);
                }


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

    public function delete(Request $request)
    {

        $params = Utility::getRequestData($request);
        Utility::sanitize($params->data);
        if(Utility::hasAccess($params->data["user"])) {
            $get= new Livetv();
            $tv = $get->deleteLivetv($params->data["uid"]);

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

    public function deletePhoto(Request $request)
    {

        $params = Utility::getRequestData($request); //convert json request to array object data
        Utility::sanitize($params->data); //a must sanitization

        $get= new Livetv();
        $get_section = $get->getUniqueLivetv($params->data["livetv_uid"]);
        $photo = $params->data["image"];
        $existing_images = [];
        $existing_local_images = [];
        print_r(json_encode($get_section));exit;
        $existing_images1 = json_decode($get_section->images,TRUE);
        $existing_local_images1 = json_decode($get_section->local_images,TRUE);
        foreach($existing_images1 as $image){
            $existing_images[] = $image;
        }
        foreach($existing_local_images1 as $image){
            $existing_local_images[] = $image;
        }

        if(count($existing_images) > 0){
            foreach($existing_images as $key=>$value){
                if($value == $photo){
                    unset($existing_images[$key]);
                }
            }
        }
        if(count($existing_local_images) > 0){
            foreach($existing_local_images as $key=>$value){
                if($value == $photo){
                    unset($existing_local_images[$key]);
                }
            }
        }

        //CONVERT IMAGES BACK TO JSON OBJECT FOR DATABASE STORAGE
        $images = json_encode($existing_images);
        $local_images = json_encode($existing_local_images);

        $create = $get->updateLivetvImage( $params->data['livetv_uid'],
            $images, $local_images);

        if($create){

            $this->responseCode = Utility::RESPONSE_CODE_OK;
            $this->responseMessage = "Deleted";
        }
        else{
            $this->responseCode = Utility::RESPONSE_CODE_NOT_FOUND;
            $this->responseMessage = "Not Found";
        }

        return Utility::getResponseData($this->responseCode, $this->responseMessage);
    }

    public function scrapeSite(Request $request){
        $params = Utility::getRequestData($request);
        Utility::sanitize($params->data);
        //print_r($params->data);
        $url = $params->data["url"];

        $response = Utility::scrapeSite($url);


        return $response;
        //print_r($response);
    }


    private function  alignResponseData($livetv){
        foreach($livetv as $new){
            if($new->thumbnail_cdn_status == Utility::CDN_STATUS_YES){
                $new->real_thumbnail = Utility::CDN_IMAGE_URL . $new->thumbnail;
            }
            else{
                $new->real_thumbnail = Utility::TEMP_IMAGE_URL . $new->thumbnail;
            }

            $model = new LivetvComments();
            $model2 = new LivetvLikes();
            $count_likes = $model2->getLikes("livetv_uid",$new->uid);
            $count_comment = $model->getComment("livetv_uid",$new->uid);
            $new->total_comments = (count($count_comment) == 0) ? "" : count($count_comment);
            $new->total_likes = (count($count_likes) == 0) ? "" : count($count_likes);

            if($new->video_type == Utility::FILE_VIDEO_TYPE){

                if($new->video_cdn_status == Utility::CDN_STATUS_YES){
                    $new->real_video = Utility::CDN_VIDEO_URL . $new->video;
                }
                else{
                    $new->real_video = Utility::TEMP_VIDEO_URL . $new->video;
                }

            }

            if($new->video_type == Utility::YOUTUBE_VIDEO_TYPE){
                $new->real_video = 'https://www.youtube.com/embed/'.$new->video;

            }

            unset($new->status);
            unset($new->created_at);
            unset($new->updated_at);
            unset($new->cdn_status);
            unset($new->thumbnail);

        }
    }

    private function  alignResponseDataSingle($livetv,$start,$limit){
        foreach($livetv as $new){
            if($new->thumbnail_cdn_status == Utility::CDN_STATUS_YES){
                $new->real_thumbnail = Utility::CDN_IMAGE_URL . $new->thumbnail;
            }
            else{
                $new->real_thumbnail = Utility::TEMP_IMAGE_URL . $new->thumbnail;
            }

            $new->uploaded_at = Utility::commentHumanTiming($new->created_at);
            $new->uploaded_by = $new->poster_user->full_name;

            $model = new LivetvComments();
            $fetch_comment = $model->fetchComment('livetv_uid', $new->Uid, $start, $limit);
            $new->total_comments = count($fetch_comment);
            foreach($fetch_comment as $com){
                $user_data = [];
                $user = new User();
                $log_user = $user->getSingleUserByUid($com->user_id);
                if(count($log_user)>0) {
                    $user_data['full_name'] = $log_user->full_name;
                    $user_data['email'] = $log_user->email;
                    $user_data['username'] = $log_user->username;
                    $user_data['bio'] = $log_user->bio;
                    $user_data['slug'] = $log_user->slug;

                    if ($log_user->cdn_status == Utility::CDN_STATUS_YES) {
                        $user_data['avatar'] = Utility::CDN_IMAGE_URL . $log_user->avatar;
                    } else {
                        $user_data['avatar'] = Utility::TEMP_IMAGE_URL . $log_user->avatar;
                    }

                    $com->user_data = $user_data;
                    $com->comment_time = Utility::commentHumanTiming($com->created_at);
                }
            }
            //END OF COMMENTS

            $model2 = new LivetvLikes();
            $fetch_likes = $model2->fetchLikes('livetv_uid', $new->uid, $start, $limit);
            $new->total_likes = count($fetch_likes);
            foreach($fetch_likes as $com) {
                $user_data = [];
                $user = new User();
                $log_user = $user->getSingleUserByUid($com->user_id);
                if (count($log_user) > 0) {
                    $user_data['full_name'] = $log_user->full_name;
                    $user_data['email'] = $log_user->email;
                    $user_data['username'] = $log_user->username;
                    $user_data['bio'] = $log_user->bio;
                    $user_data['slug'] = $log_user->slug;

                    if ($log_user->cdn_status == Utility::CDN_STATUS_YES) {
                        $user_data['avatar'] = Utility::CDN_IMAGE_URL . $log_user->avatar;
                    } else {
                        $user_data['avatar'] = Utility::TEMP_IMAGE_URL . $log_user->avatar;
                    }

                    $com->user_data = $user_data;
                    $com->like_time = Utility::commentHumanTiming($com->created_at);

                }
                $new->likes = $fetch_likes;

                $new->comments = $fetch_comment;
            }

            if($new->video_type == Utility::FILE_VIDEO_TYPE){

                if($new->video_cdn_status == Utility::CDN_STATUS_YES){
                    $new->real_video = Utility::CDN_VIDEO_URL . $new->video;
                }
                else{
                    $new->real_video = Utility::TEMP_VIDEO_URL . $new->video;
                }

            }

            if($new->video_type == Utility::YOUTUBE_VIDEO_TYPE){
                $new->real_video = 'https://www.youtube.com/embed/'.$new->video.'?autoplay=1';

            }

            unset($new->status);
            unset($new->created_at);
            unset($new->updated_at);
            unset($new->cdn_status);
            unset($new->thumbnail);

        }
    }


    public function test(Request $request){

        $params =  Utility::getRequestData($request);
        Utility::sanitize($params->data);
        Utility::hasAccess($params->data['token']);


        // echo json_encode($request);
    }

}

