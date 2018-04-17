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
use App\Models\LiveFeed;
use App\Models\LiveFeedsComments;
use App\Models\LiveFeedLikes;
use App\Models\News;
use App\Models\NewsCatg;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Http\Request;
use Auth;
use Input;

class LiveFeedController extends Controller
{
    //

    private $responseCode, $responseMessage, $responseData;

    public function getLiveFeed(Request $request)
    {

        $params = Utility::getRequestData($request);
        Utility::sanitize($params->data);

        $liveFeed = new LiveFeed();
        if (!array_key_exists('status', $params->data)) {
            $params->data["status"] = "";
        }


        $feed = $liveFeed->getLiveFeed($params->data["status"],
            $params->data["start"],$params->data["limit"]);
        Utility::realDateInterval($feed);
        Utility::formatDateFields($feed);
        $this->alignResponseData($feed);
        $this->responseCode = Utility::RESPONSE_CODE_OK;
        $this->responseMessage = "Success";
        $this->responseData = array(
            "LiveFeed" =>$feed
        );


        return Utility::getResponseData($this->responseCode, $this->responseMessage, $this->responseData);
    }

    public function getLiveFeedUpdates(Request $request)
    {

        $params = Utility::getRequestData($request);
        Utility::sanitize($params->data);

        $liveFeed = new LiveFeed();

        $feed = $liveFeed->getLiveFeedUpdates($params->data["last_id"]);
        Utility::realDateInterval($feed);
        Utility::formatDateFields($feed);
        $this->alignResponseData($feed);
        $this->responseCode = Utility::RESPONSE_CODE_OK;
        $this->responseMessage = "Success";
        $this->responseData = array(
            "LiveFeed" =>$feed
        );


        return Utility::getResponseData($this->responseCode, $this->responseMessage, $this->responseData);
    }


    public function getPosterLiveFeed(Request $request)
    {

        $params = Utility::getRequestData($request); //convert json request to array object data
        Utility::sanitize($params->data); //a must sanitization

        $get= new LiveFeed();
        $feed = $get->getPosterLiveFeed($params->data["acc_type"],
            $params->data["start"],$params->data["limit"]);
        Utility::formatDateFields($feed);

        if(count($feed) > 0){
            $this->alignResponseData($feed);
            $this->responseCode = Utility::RESPONSE_CODE_OK;
            $this->responseMessage = "Success";
            $this->responseData = array(
                "posterLiveFeed" =>$feed
            );
        }
        else{
            $this->responseCode = Utility::RESPONSE_CODE_NOT_FOUND;
            $this->responseMessage = "Not Found";
            $this->responseData = array();
        }

        return Utility::getResponseData($this->responseCode, $this->responseMessage, $this->responseData);
    }

    public function getUniquePosterLiveFeed(Request $request)
    {

        $params = Utility::getRequestData($request); //convert json request to array object data
        Utility::sanitize($params->data); //a must sanitization

        $get= new LiveFeed();
        $feed = $get->getUniquePosterLiveFeed(Auth::user()->uid,
            $params->data["start"],$params->data["limit"]);
        Utility::formatDateFields($feed);

        if(count($feed) > 0){
            $this->alignResponseData($feed);
            $this->responseCode = Utility::RESPONSE_CODE_OK;
            $this->responseMessage = "Success";
            $this->responseData = array(
                "posterLiveFeed" =>$feed
            );
        }
        else{
            $this->responseCode = Utility::RESPONSE_CODE_NOT_FOUND;
            $this->responseMessage = "Not Found";
            $this->responseData = array();
        }

        return Utility::getResponseData($this->responseCode, $this->responseMessage, $this->responseData);
    }

    public function getSingleLiveFeed(Request $request)
    {

        $params = Utility::getRequestData($request); //convert json request to array object data
        Utility::sanitize($params->data); //a must sanitization

        $get= new LiveFeed();
        $feed = $get->getSingleLiveFeed($params->data["uid"]);


        Utility::formatDateFields($feed);
        $this->alignResponseData($feed);

        if(count($feed) > 0){
            $updateViews = Utility::updateViews($feed);
            $updateNewsViews = $get->updateLiveFeedViews($feed[0]->uid,$updateViews);
            $this->responseCode = Utility::RESPONSE_CODE_OK;
            $this->responseMessage = "Success";
            $this->responseData = array(
                "liveFeed" =>$feed
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

        $jwt = $request->get('user');
        Utility::sanitize($jwt);
        $type = $request->get('feed_type');
        Utility::sanitize($type);
        $title = $request->get('title');
        Utility::sanitize($title);
        $content = $request->get('content');
        Utility::sanitize($content);



        $cdn_images = [];
        $real_images = [];
        $temp_images = [];

        if(Utility::hasAccess($jwt)) {
            $thumbnail_file = time() . "_" . Utility::generateUID(null, 10) . "." . $request->file('thumbnail')->getClientOriginalExtension();
            $srcFile = Utility::getTempImagesPath() . $thumbnail_file;
            $request->file('thumbnail')->move(
                Utility::getTempImagesPath(), $thumbnail_file
            );
            //print_r($request->file('attachment')[1]); exit;
            if($request->get('feed_type') == '1'){
                $files = $request->file('attachment');
                foreach($files as $file) {
                    $attachment = time() . "_" . Utility::generateUID(null, 10) . "." . $file->getClientOriginalExtension();
                    $srcFile = Utility::getTempImagesPath() . $attachment;

                    $file->move(
                        Utility::getTempImagesPath(), $attachment
                    );

                    $append[] = $srcFile;
                    if (Utility::moveToCDN($srcFile, $attachment, Utility::CDN_IMAGE)) {
                        //Update CDN STATUS in db table
                        //$liveFeed->hasPushedToCDN($create->uid, 'feed_attachment_cdn_status');
                        array_push($cdn_images,$attachment);
                        $real_images[] =  Utility::CDN_IMAGE_URL . $attachment;
                    } else {
                        $temp_images[] = $attachment;
                        $real_images[] =   Utility::TEMP_IMAGE_URL . $attachment;
                        //UPLOAD TO CDN FAILED OR FTP COULD NOT CONNECT
                        //CRON WOULD HANDLE FAILED UPLOADS
                    }

                }
                //print_r($append); exit;
            }

            $feed_type = '';
            $attachment_type = '';
            $local_images = '';
            if($type == '1'){
                $feed_type = 'image';
                $attachment_type = json_encode($cdn_images);
                $local_images = json_encode($temp_images);
            }
            //print_r($attachment_type); exit;
            if($type == '2'){
                $feed_type = 'video';
                $attachment_type = $request->get('attachment');
                $local_images = '';
            };
            if($type == '3'){
                $feed_type = 'text';
                $attachment_type = '';
                $local_images = '';
            }
            $liveFeed = new LiveFeed();
            $create = $liveFeed->createLiveFeed( Auth::user()->uid,Auth::user()->acc_type,
                $request->get('title'),$thumbnail_file,$request->get('content'),$feed_type, $attachment_type,$local_images);

            $srcFile = Utility::getTempImagesPath() . $thumbnail_file;
            if(Utility::moveToCDN($srcFile , $thumbnail_file, Utility::CDN_IMAGE)){
                //Update CDN STATUS in db table
                $liveFeed->hasPushedToCDN($create->uid,'thumbnail_cdn_status');
                $create->thumbnail = Utility::CDN_IMAGE_URL . $thumbnail_file;
            }
            else{
                $create->thumbnail = Utility::TEMP_IMAGE_URL . $thumbnail_file;
                //UPLOAD TO CDN FAILED OR FTP COULD NOT CONNECT
                //CRON WOULD HANDLE FAILED UPLOADS
            }

            $create->real_images = $real_images;
            $this->responseCode = Utility::RESPONSE_CODE_CREATED;
            $this->responseMessage = "Success";
            $this->responseData = array(
                "live_feed" =>$create
            );

        }
        else{
            $this->responseCode = Utility::RESPONSE_CODE_INVALID_REQUEST;
            $this->responseMessage = "Unauthenticated User";

            $this->responseData = array();
        }

        return Utility::getResponseData($this->responseCode, $this->responseMessage, $this->responseData);
    }

    public function newsToLiveFeed(Request $request){

        $jwt = $request->get('user');
        Utility::sanitize($jwt);
        $liveFeedStatus = $request->get('live_feed_status');
        Utility::sanitize($liveFeedStatus);
        $newsUid = $request->get('news_uid');
        Utility::sanitize($newsUid);

        if(Utility::hasAccess($jwt)) {

            $news = new News();
            $liveFeed = new LiveFeed();
            $create = [];
            if($liveFeedStatus == '1') {
                $getNewsLiveFeed = $liveFeed->getNewsLiveFeed($newsUid);
                if (count($getNewsLiveFeed) == 0) {

                $create = $liveFeed->newsToLiveFeed($newsUid, Auth::user()->uid, Auth::user()->acc_type,
                    $liveFeedStatus);
                $updateNewsLiveFeed = $news->updateNewsLivefeed($newsUid, $liveFeedStatus);
            }
            }else{
                $updateNewsLiveFeed = $news->updateNewsLiveFeed($newsUid, $liveFeedStatus);
                $updateLiveFeedStatus = $liveFeed->updateLiveFeedStatus($newsUid, $liveFeedStatus);

            }

            $this->responseCode = Utility::RESPONSE_CODE_CREATED;
            $this->responseMessage = "Success";
            $this->responseData = array(
                "news_live_feed" =>[]
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
        $content = $request->get('content');
        Utility::sanitize($content);

        if(Utility::hasAccess($jwt)) {

            $liveFeed = new LiveFeed();
            $create = $liveFeed->updateLiveFeed($request->get('uid'),
                $request->get('title'),$request->get('content'));

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



            $lifeFeed = new LiveFeed();
            $edit = $lifeFeed->updateImage( $uid,'thumbnail',
                $image);

            $srcFile = Utility::getTempImagesPath() . $image;
            //echo $srcFile . "\n";

            if(Utility::moveToCDN($srcFile , $image, Utility::CDN_IMAGE)){
                //Update CDN STATUS in db table
                $lifeFeed->hasPushedToCDN($uid,'thumbnail_cdn_status');
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



            $lifeFeed = new LiveFeed();
            $edit = $lifeFeed->updateImage( $uid,'feed_attachment',
                $image);

            $srcFile = Utility::getTempImagesPath() . $image;
            //echo $srcFile . "\n";

            if(Utility::moveToCDN($srcFile , $image, Utility::CDN_IMAGE)){
                //Update CDN STATUS in db table
                $lifeFeed->hasPushedToCDN($uid,'feed_attachment_cdn_status');
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

    public function delete(Request $request)
    {

        $params = Utility::getRequestData($request);
        Utility::sanitize($params->data);
        if(Utility::hasAccess($params->data["user"])) {
            $get= new LiveFeed();
            $feed = $get->deleteLiveFeed($params->data["uid"]);

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

        $get= new LiveFeed();
        $get_section = $get->getUniqueLiveFeed($params->data["live_feed_uid"]);
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

        $create = $get->updateLiveFeedImage( $params->data['live_feed_uid'],
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


    private function  alignResponseData($liveFeed){
        foreach($liveFeed as $new){
            if($new->thumbnail_cdn_status == Utility::CDN_STATUS_YES){
                $new->real_thumbnail = Utility::CDN_IMAGE_URL . $new->thumbnail;
            }
            else{
                $new->real_thumbnail = Utility::TEMP_IMAGE_URL . $new->thumbnail;
            }

            $model = new LiveFeedsComments();
            $model2 = new LiveFeedLikes();
            $count_likes = $model2->getLikes("live_feed_uid",$new->uid);
            $count_comment = $model->getComment("live_feed_uid",$new->uid);
            $new->total_comments = (count($count_comment) == 0) ? "" : count($count_comment);
            $new->total_likes = (count($count_likes) == 0) ? "" : count($count_likes);

            if($new->feed_type == 'image'){

                $cdn_photos = json_decode($new->images,TRUE);
                $get_images = [];
                if(count($cdn_photos) >0) {
                    foreach ($cdn_photos as $photo) {
                        $get_images[] = ['image_url' => Utility::CDN_IMAGE_URL . $photo, 'image_name' => $photo];

                    }
                }
                $temp_photos = json_decode($new->local_images,TRUE);
                if(count($temp_photos) >0) {
                    foreach ($temp_photos as $photo) {
                        $get_images[] = ['image_url' => Utility::TEMP_IMAGE_URL . $photo, 'image_name' => $photo];

                    }
                }
                $new->all_images = $get_images;

            }

            if($new->feed_type == 'news'){
               $new->title = $new->live_feed_news->news_title;
                $new->content = $new->live_feed_news->news_title;
               $new->feed_attachment = $new->live_feed_news->cover_img;

                if($new->live_feed_news->cdn_status == Utility::CDN_STATUS_YES){
                    $new->feed_attachment = Utility::CDN_IMAGE_URL . $new->live_feed_news->cover_img;
                }
                else{
                    $new->feed_attachment = Utility::TEMP_IMAGE_URL . $new->live_feed_news->cover_img;
                }

                $newsCatg = new NewsCatg();
                //$new->cat_name = $new->category->cat_name;
                $split_cat = str_split($new->live_feed_news->cat_id);
                $new->cat_name = $newsCatg->getCategories($split_cat);

                $new->news_slug = $new->live_feed_news->slug;

            }

            if($new->user_type == '2'){
                $new->poster_name = $new->poster_artist->artist_name;
            }else{
                $new->poster_name = $new->poster_user->full_name;
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

