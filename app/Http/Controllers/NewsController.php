<?php

namespace App\Http\Controllers;

use App\Http\Controllers;
use App\Helpers\Utility;
use App\Models\NewsSection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\view;
use Monolog\Handler\Curl\Util;
use Validator;
use DB;
use App\User;
use App\Models\News;
use App\Models\NewsLikes;
use App\Models\NewsComments;
use App\Models\NewsCatg;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Http\Request;
use Auth;
// include composer autoload
//require 'vendor/autoload.php';

// import the Intervention Image Manager Class
//use Intervention\Image\ImageManager;
use Image;

class NewsController extends Controller
{
    private $responseCode, $responseMessage, $responseData;
    public function getNews(Request $request)
    {

        $params = Utility::getRequestData($request);
        Utility::sanitize($params->data);

            $news = new News();
            if (!array_key_exists('status', $params->data)) {
                $params->data["status"] = "";
            }
            /*if($params->data["status"] > 50){
                $params->data["status"] = 100;
            }*/

            $news = $news->getNews($params->data["status"],
                $params->data["start"],$params->data["limit"]);

            Utility::formatDateFields($news);
            $this->alignResponseData($news);
            $this->responseCode = Utility::RESPONSE_CODE_OK;
            $this->responseMessage = "Success";
            $this->responseData = array(
                "News" =>$news
            );


        return Utility::getResponseData($this->responseCode, $this->responseMessage, $this->responseData);
    }

    public function getNewsCategory(Request $request)
    {

        $params = Utility::getRequestData($request); //convert json request to array object data
        Utility::sanitize($params->data); //a must sanitization

            $get= new News();
            $news = $get->getNewsCategory($params->data["cat_id"],
                 $params->data["start"],$params->data["limit"]);
                    Utility::formatDateFields($news);
            //var_dump($video);

            if(count($news) > 0){
                $this->alignResponseData($news);
                $this->responseCode = Utility::RESPONSE_CODE_OK;
                $this->responseMessage = "Success";
                $this->responseData = array(
                    "newsCategory" =>$news
                );
            }
            else{
                $this->responseCode = Utility::RESPONSE_CODE_NOT_FOUND;
                $this->responseMessage = "Not Found";
                $this->responseData = array();
            }

        return Utility::getResponseData($this->responseCode, $this->responseMessage, $this->responseData);
    }

    public function getSingleNews(Request $request)
    {

        $params = Utility::getRequestData($request); //convert json request to array object data
        Utility::sanitize($params->data); //a must sanitization

        if (!array_key_exists('comment_start', $params->data)) {
            $params->data["comment_start"] = "0";
        }

        if (!array_key_exists('comment_limit', $params->data)) {
            $params->data["comment_limit"] = "10";
        }

        $get= new News();
        $news = $get->getSingleNews($params->data["slug"]);


        Utility::formatDateFields($news);
        $this->alignResponseDataSingle($news,$params->data["comment_start"],$params->data["comment_limit"]);

        if(count($news) > 0){
            $updateViews = Utility::updateViews($news);
            $updateNewsViews = $get->updateNewsViews($news[0]->uid,$updateViews);

            $this->responseCode = Utility::RESPONSE_CODE_OK;
            $this->responseMessage = "Success";
            $this->responseData = array(
                "news" =>$news
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

        //$params = Utility::getRequestData($request);
        $title = $request->get('title');
        Utility::sanitize($title);
        $jwt = $request->get('user');
        Utility::sanitize($jwt);
        $cat_id = $request->get('cat_id');
        $tag = $request->get('tag');
        Utility::sanitize($cat_id);
        //print_r($cat_id); exit();
        $cat_id = str_replace(',','',$cat_id);
        if(Utility::hasAccess($jwt)) {

            $file_name = time() . "_" . Utility::generateUID(null, 10) . "." . $request->file('cover_img')->getClientOriginalExtension();
            $request->file('cover_img')->move(
                Utility::getTempImagesPath(), $file_name
            );

            $news = new News();
            $create = $news->createNews( $cat_id,
                $title,$tag, $file_name );

            $srcFile = Utility::getTempImagesPath() . $file_name;
            if(Utility::moveToCDN($srcFile , $file_name, Utility::CDN_IMAGE)){
                //Update CDN STATUS in db table
                $news->hasPushedToCDN($create->uid);
                $create->cover_img = Utility::CDN_IMAGE_URL . $file_name;
            }
            else{
                $create->cover_img = Utility::TEMP_IMAGE_URL . $file_name;
                //UPLOAD TO CDN FAILED OR FTP COULD NOT CONNECT
                //CRON WOULD HANDLE FAILED UPLOADS
            }

            $this->responseCode = Utility::RESPONSE_CODE_CREATED;
            $this->responseMessage = "Success";
            $this->responseData = array(
                "news_uid" =>$create->uid
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

        $params = Utility::getRequestData($request);
        Utility::sanitize($params->data['title']);
        Utility::sanitize($params->data['user']);
        Utility::sanitize($params->data['tag']);
        Utility::sanitize($params->data['uid']);

        if(Utility::hasAccess($params->data['user'])) {

            $news = new News();
            $create = $news->editNews($params->data['uid'],$params->data['title'],$params->data['tag']);

            $this->responseCode = Utility::RESPONSE_CODE_CREATED;
            $this->responseMessage = "Success";
            $this->responseData = array(
                "news_uid" =>$params->data['uid']
            );

        }
        else{
            $this->responseCode = Utility::RESPONSE_CODE_INVALID_REQUEST;
            $this->responseMessage = "Unauthenticated User";

            $this->responseData = array();
        }

        return Utility::getResponseData($this->responseCode, $this->responseMessage, $this->responseData);
    }

    public function editCoverImg(Request $request){

        $uid = $request->get('uid');
        $jwt = $request->get('user');
        $coverImg = time() . "_" . Utility::generateUID(null, 10) . "." . $request->file('cover_img')->getClientOriginalExtension();
        Utility::sanitize($jwt);
        Utility::sanitize($uid);

        if(Utility::hasAccess($jwt)) {

            $request->file('cover_img')->move(
                Utility::getTempImagesPath(), $coverImg
            );



            $news = new News();
            $edit = $news->editCoverImg( $uid,
                $coverImg);

            $srcFile = Utility::getTempImagesPath() . $coverImg;
            //echo $srcFile . "\n";

            if(Utility::moveToCDN($srcFile , $coverImg, Utility::CDN_IMAGE)){
                //Update CDN STATUS in db table
                $news->hasPushedToCDN($uid);
                $coverImg = Utility::CDN_IMAGE_URL . $coverImg;
            }
            else{
                $coverImg = Utility::TEMP_IMAGE_URL . $coverImg;

            }

            $this->responseCode = Utility::RESPONSE_CODE_CREATED;
            $this->responseMessage = "Success";
            $this->responseData = array(
                "cover_img" => $coverImg
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
            $get= new News();
            $news = $get->deleteNews($params->data["news_uid"]);

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

        $comment = new NewsComments();

        $feed = $comment->loadMore($params->data["last_id"],'news_uid',$params->data["post_uid"]);
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

        $comment = new NewsLikes();

        $feed = $comment->loadMore($params->data["last_id"],'news_uid',$params->data["post_uid"]);
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

    private function  alignResponseDataSingle($news,$start,$limit){
        $total_row = count($news);
        foreach($news as $new){
            $new->total_rows = $total_row;
            if($new->cdn_status == Utility::CDN_STATUS_YES){
                $new->real_cover_img = Utility::CDN_IMAGE_URL . $new->cover_img;
            }
            else{
                $new->real_cover_img = Utility::TEMP_IMAGE_URL . $new->cover_img;
            }
            $new->uploaded_at = Utility::commentHumanTiming($new->created_at);
            $new->uploaded_by = $new->admin->full_name;
            unset($new->id);
            unset($new->status);
            unset($new->admin_id);
            unset($new->created_at);
            unset($new->updated_at);
            unset($new->cdn_status);
            unset($new->cover_img);
            $relate = new News();
            $newsCatg = new NewsCatg();
            //$new->cat_name = $new->category->cat_name;
            $split_cat = str_split($new->cat_id);
            $new->cat_name = $newsCatg->getCategories($split_cat);


            unset($new->category);
            unset($new->category);
            unset($new->cat_id);

            $fetchRelated = $relate->getRelatedNews($new->news_title);
            //$fetchRelated = DB::select("SELECT * FROM `afrobt_news` WHERE `news_title` LIKE '%$new->news_title%'");
            $relateArray = [];
            $relateObject = [];
            foreach($fetchRelated as $related){
                if ($related->cdn_status == Utility::CDN_STATUS_YES) {
                    $relateArray['real_cover_img'] = Utility::CDN_IMAGE_URL . $related->cover_img;
                } else {
                    $relateArray['real_cover_img'] = Utility::TEMP_IMAGE_URL . $related->cover_img;
                }
                if($new->news_title != $related->news_title) {
                    $relateArray['news_title'] = $related->news_title;
                    $relateArray['slug'] = $related->slug;


                    $relateObject[] = $relateArray;
                }else{
                    unset($related->id);
                    unset($related->status);
                    unset($related->real_cover_img);
                    unset($related->admin_id);
                    unset($related->views);
                    unset($related->slug);
                    unset($related->news_title);
                    unset($related->cover_img);
                    unset($related->cat_id);
                    unset($related->uid);
                    unset($related->created_at);
                    unset($related->updated_at);
                    unset($related->cdn_status);
                    unset($related->is_livefeed);
                }
            }

            $new->related_news = $relateObject;

            $newsSection = new NewsSection();
            $countSection = $newsSection->countSection($new->uid);
            $new->section;

            if(count($countSection) > 0){
            foreach($new->section as $section){
                $section->section_title = $section->title;
                $section->section_content = $section->content;
                $get_images = [];


                $cdn_photos = json_decode($section->images,TRUE);

                if(count($cdn_photos) >0) {
                    foreach ($cdn_photos as $photo) {
                        $get_images[] = ['image_url' => Utility::CDN_IMAGE_URL . $photo, 'image_name' => $photo];

                    }
                }
                $temp_photos = json_decode($section->local_images,TRUE);
                if(count($temp_photos) >0) {
                    foreach ($temp_photos as $photo) {
                        $get_images[] = [Utility::TEMP_IMAGE_URL . $photo,$photo];

                    }
                }

                if($section->link !== ""){
                    $section->youtube_video = 'https://www.youtube.com/embed/'.$section->link;
                }

                $section->section_images = $get_images;

                //unset($section->id);
                //unset($section->content);
                unset($section->images);
                unset($section->local_images);
            }

            }else{
                $new->section = [];
            }

            $model = new NewsComments();
            $fetch_comment = $model->fetchComment('news_uid', $new->Uid, $start, $limit);
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

            $model2 = new NewsLikes();
            $fetch_likes = $model2->fetchLikes('news_uid', $new->uid, $start, $limit);
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
        }
    }

    private function  alignResponseData($news){
        $total_row = count($news);
        foreach($news as $new){
            $new->total_rows = $total_row;
            if($new->cdn_status == Utility::CDN_STATUS_YES){
                $new->real_cover_img = Utility::CDN_IMAGE_URL . $new->cover_img;
            }
            else{
                $new->real_cover_img = Utility::TEMP_IMAGE_URL . $new->cover_img;
            }
            unset($new->id);
            unset($new->status);
            unset($new->admin_id);
            unset($new->created_at);
            unset($new->updated_at);
            unset($new->cdn_status);
            unset($new->cover_img);

            $relate = new News();
            $newsCatg = new NewsCatg();
            //$new->cat_name = $new->category->cat_name;
            $split_cat = str_split($new->cat_id);
            $new->cat_name = $newsCatg->getCategories($split_cat);

            unset($new->category);
            unset($new->category);
            unset($new->cat_id);

            $newsSection = new NewsSection();
            $countSection = $newsSection->countSection($new->uid);
            $new->section;
            if(count($countSection) > 0){
            foreach($new->section as $section){
                $section->section_title = $section->title;
                $section->section_content = $section->content;
                $get_images = [];


                $cdn_photos = json_decode($section->images,TRUE);

                if(count($cdn_photos) >0) {
                    foreach ($cdn_photos as $photo) {
                        $get_images[] = ['image_url' => Utility::CDN_IMAGE_URL . $photo, 'image_name' => $photo];

                    }
                }
                $temp_photos = json_decode($section->local_images,TRUE);
                if(count($temp_photos) >0) {
                    foreach ($temp_photos as $photo) {
                        $get_images[] = [Utility::TEMP_IMAGE_URL . $photo,$photo];

                    }
                }

                $section->section_images = $get_images;

                //unset($section->id);
                //unset($section->content);
                unset($section->images);
                unset($section->local_images);
            }
            }else{
                $new->section = [];
            }

            $model = new NewsComments();
            $model2 = new NewsLikes();
            $count_likes = $model2->getLikes("news_uid",$new->id);
            $count_comment = $model->getComment("news_uid",$new->id);
            $new->total_comments = (count($count_comment) == 0) ? "" : count($count_comment);
            $new->total_likes = (count($count_likes) == 0) ? "" : count($count_likes);

        }
    }

    public function test(Request $request){

        $params =  Utility::getRequestData($request);
        Utility::sanitize($params->data);
        Utility::hasAccess($params->data['token']);


        // echo json_encode($request);
    }

    private function setupResponseDataLoad($data,$start,$limit){

        foreach($data as $com){
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
            $com->comment_time = Utility::commentHumanTiming($com->created_at) /*$com->created_at->diffForHumans()*/;

        }

    }

    public function template(Request $request){

        $params = Utility::getRequestData($request);
        Utility::sanitize($params->data);
        if(Utility::hasAccess($params->data["user"])) {
            //Modify this block
            $videoType = new VideoTypes();
            $create = $videoType->createVideoType($params->data['type']);
            //

            $this->responseCode = Utility::RESPONSE_CODE_CREATED;
            $this->responseMessage = "Success";

            $this->responseData = array(
                "data" => array(
                    "id" => $create->id,
                    "type" => $create->type
                )
            );
        }
        else{
            $this->responseCode = Utility::RESPONSE_CODE_INVALID_REQUEST;
            $this->responseMessage = "Unathenticated User";

            $this->responseData = array();
        }

        return Utility::getResponseData($this->responseCode, $this->responseMessage, $this->responseData);
    }


}
