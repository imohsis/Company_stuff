<?php

namespace App\Http\Controllers;

use App\Http\Controllers;
use App\Models\AlbumComments;
use Illuminate\Support\Facades\view;
use Validator;
use Input;
use DB;
use App\User;
use App\Models\Albums;
use App\Models\AlbumLikes;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Http\Request;
use App\Helpers\Utility;
use Illuminate\Support\Facades\Storage;

class AlbumsController extends Controller
{
    private $responseCode, $responseMessage, $responseData;
    public function getAlbums(Request $request)
    {

        $params = Utility::getRequestData($request);
        Utility::sanitize($params->data);

        $model = new Albums();
        if (!array_key_exists('status', $params->data)) {
            $params->data["status"] = "";
        }
        $data = $model->getAlbums($params->data["status"],
            $params->data["start"],$params->data["limit"]);

        $this->setupResponseData($data);

        $this->responseCode = Utility::RESPONSE_CODE_OK;
        $this->responseMessage = "Success";
        $this->responseData = array(
            "albums" => $data
        );

        return Utility::getResponseData($this->responseCode, $this->responseMessage, $this->responseData);
    }

    public function getRecentAlbums(Request $request)
    {

        $params = Utility::getRequestData($request);
        Utility::sanitize($params->data);

        $model= new Albums();
        if (!array_key_exists('status', $params->data)) {
            $params->data["status"] = "";
        }
        $data = $model->getRecentAlbums($params->data["status"],
            $params->data["start"],$params->data["limit"]);

        $this->setupResponseData($data);

        $this->responseCode = Utility::RESPONSE_CODE_OK;
        $this->responseMessage = "Success";
        $this->responseData = array(
            "albums" =>$data
        );

        return Utility::getResponseData($this->responseCode, $this->responseMessage, $this->responseData);
    }

    public function getAlbum(Request $request)
    {

        $params = Utility::getRequestData($request);
        Utility::sanitize($params->data);

        if (!array_key_exists('comment_start', $params->data)) {
            $params->data["comment_start"] = "0";
        }

        if (!array_key_exists('comment_limit', $params->data)) {
            $params->data["comment_limit"] = "10";
        }

        $model= new Albums();
        $data= $model->getAlbum($params->data["slug"]);

        if(count($data) > 0){
            $this->setupResponseDataSingle($data,$params->data["comment_start"],$params->data["comment_limit"]);
            $this->responseCode = Utility::RESPONSE_CODE_OK;
            $this->responseMessage = "Success";
            $this->responseData = array(
                "album" =>$data
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
        $name =$request->get('name');
        $artistUid =$request->get('artist_uid');
        $release_date =$request->get('release_date');
        $jwt = $request->get('user');
        $coverName = time() . "_" . Utility::generateUID(null, 10) . "." . $request->file('cover')->getClientOriginalExtension();
        Utility::sanitize($name);
        Utility::sanitize($artistUid);
        Utility::sanitize($release_date);
        Utility::sanitize($jwt);

        if(Utility::hasAccess($jwt)) {

            $request->file('cover')->move(
                Utility::getTempImagesPath(), $coverName
            );



            $model = new Albums();
            $data = $model->createAlbum( $name, $artistUid,
                $coverName,$release_date);

            $srcFile = Utility::getTempImagesPath() . $coverName;
            //echo $srcFile . "\n";

            if(Utility::moveToCDN($srcFile , $coverName, Utility::CDN_IMAGE)){
                //Update CDN STATUS in db table
                $model->hasPushedToCDN($data->id);
                $data->real_cover = Utility::CDN_IMAGE_URL . $coverName;
            }
            else{
                $data->real_cover = Utility::TEMP_IMAGE_URL . $coverName;
                //UPLOAD TO CDN FAILED OR FTP COULD NOT CONNECT
                //CRON WOULD HANDLE FAILED UPLOADS
            }
            $data->artist;
            $this->responseCode = Utility::RESPONSE_CODE_CREATED;
            $this->responseMessage = "Success";
            $this->responseData = array(
                "album" =>$data
            );

        }
        else{
            $this->responseCode = Utility::RESPONSE_CODE_INVALID_REQUEST;
            $this->responseMessage = "Unathenticated User";

            $this->responseData = array();
        }

        return Utility::getResponseData($this->responseCode, $this->responseMessage, $this->responseData);
    }

    public function editAlbum(Request $request){

        $params = Utility::getRequestData($request);
        Utility::sanitize($params->data);
        if(Utility::hasAccess($params->data["user"])) {
            $model = new Albums();
            $edit = $model->editAlbum( $params->data['id'], $params->data['name'],
                $params->data['artist_uid'],$params->data['release_date']);

            $this->responseCode = Utility::RESPONSE_CODE_CREATED;
            $this->responseMessage = "Success";
            $this->responseData = array(
                "name" => $params->data['name'],
                "artist_uid" =>  $params->data['artist_uid']
            );

        }
        else{
            $this->responseCode = Utility::RESPONSE_CODE_INVALID_REQUEST;
            $this->responseMessage = "Unauthenticated User";

            $this->responseData = array();
        }

        return Utility::getResponseData($this->responseCode, $this->responseMessage, $this->responseData);
    }


    public function editAlbumCover(Request $request){

        $id = $request->get('id');
        $jwt = $request->get('user');
        $coverName = time() . "_" . Utility::generateUID(null, 10) . "." . $request->file('cover')->getClientOriginalExtension();
        Utility::sanitize($jwt);
        Utility::sanitize($id);

        if(Utility::hasAccess($jwt)) {

            $request->file('cover')->move(
                Utility::getTempImagesPath(), $coverName
            );



            $model = new Albums();
            $edit = $model->editCover( $id,
                $coverName);

            $srcFile = Utility::getTempImagesPath() . $coverName;

            if(Utility::moveToCDN($srcFile , $coverName, Utility::CDN_IMAGE)){
                //Update CDN STATUS in db table
                $model->hasPushedToCDN($id);
                $coverName = Utility::CDN_IMAGE_URL . $coverName;
            }
            else{
                $coverName = Utility::TEMP_IMAGE_URL . $coverName;

            }

            $this->responseCode = Utility::RESPONSE_CODE_CREATED;
            $this->responseMessage = "Success";
            $this->responseData = array(
                "cover" => $coverName
            );

        }
        else{
            $this->responseCode = Utility::RESPONSE_CODE_INVALID_REQUEST;
            $this->responseMessage = "Unathenticated User";

            $this->responseData = array();
        }

        return Utility::getResponseData($this->responseCode, $this->responseMessage, $this->responseData);
    }

    public function albumCDNTest(Request $request){

        $coverName = time() . "_" . Utility::generateUID(null, 10) . "." . $request->file('cover_image')->getClientOriginalExtension();
        Utility::sanitize($jwt);
        Utility::sanitize($id);

            $request->file('cover_image')->move(
                Utility::getTempImagesPath(), $coverName
            );

            $srcFile = Utility::getTempImagesPath() . $coverName;

            if(Utility::moveToCDNTest($srcFile , $coverName, Utility::CDN_IMAGE)){
                //Update CDN STATUS in db table

                //$coverName = Storage::disk('s3')->url($coverName);
                $coverName = Utility::CDN_IMAGE_URL_TEST . $coverName;
                $true = 'Moved to CDN';
            }
            else{
                $coverName = Utility::TEMP_IMAGE_URL . $coverName;
                $true = 'Not Moved to CDN';
            }

            $this->responseCode = Utility::RESPONSE_CODE_CREATED;
            $this->responseMessage = "Success";
            $this->responseData = array(
                "cover" => $coverName
            );



        return Utility::getResponseData($this->responseCode, $this->responseMessage, $this->responseData);
    }


    public function delete(Request $request)
    {

        $params = Utility::getRequestData($request);
        Utility::sanitize($params->data);
        if(Utility::hasAccess($params->data["user"])) {
            $model= new Albums();
            $data = $model->deleteAlbum($params->data["id"]);

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

        $comment = new AlbumComments();

        $feed = $comment->loadMore($params->data["last_id"],'album_id',$params->data["post_uid"]);
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

        $comment = new AlbumLikes();

        $feed = $comment->loadMore($params->data["last_id"],'album_id',$params->data["post_uid"]);
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
            if($d->cdn_status == Utility::CDN_STATUS_YES){
                $d->real_cover = Utility::CDN_IMAGE_URL . $d->album_cover;
            }
            else{
                $d->real_cover = Utility::TEMP_IMAGE_URL . $d->album_cover;
            }
            $d->artist;
            if($d->artist->cdn_status == Utility::CDN_STATUS_YES){
                $d->artist->real_avatar = Utility::CDN_IMAGE_URL . $d->artist->avatar;

            }
            else{
                $d->artist->real_avatar = Utility::TEMP_IMAGE_URL . $d->artist->avatar;
            }
            //$d->songs;
            foreach ($d->songs as $aas){
                if($aas->cdn_status == Utility::CDN_STATUS_YES){
                    $aas->real_file = Utility::CDN_IMAGE_URL . $aas->filename;

                }
                else{
                    $aas->real_file = Utility::TEMP_IMAGE_URL .$aas->filename;
                }

            }

            $model = new AlbumComments();
            $fetch_comment = $model->fetchComment('album_id', $d->id, $start, $limit);
            $d->total_comments = count($fetch_comment);
            foreach($fetch_comment as $com) {
                $user_data = [];
                $user = new User();
                $log_user = $user->getSingleUserByUid($com->user_id);
                if(count($log_user) > 0){
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

            $model2 = new AlbumLikes();
            $fetch_likes = $model2->fetchLikes('album_id', $d->id, $start, $limit);
            $d->total_likes = count($fetch_likes);
            foreach($fetch_likes as $com){
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
                    $com->like_time = Utility::commentHumanTiming($com->created_at);

                }
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
                $d->real_cover = Utility::CDN_IMAGE_URL . $d->album_cover;
            }
            else{
                $d->real_cover = Utility::TEMP_IMAGE_URL . $d->album_cover;
            }
            $d->artist;
            if($d->artist->cdn_status == Utility::CDN_STATUS_YES){
                $d->artist->real_avatar = Utility::CDN_IMAGE_URL . $d->artist->avatar;

            }
            else{
                $d->artist->real_avatar = Utility::TEMP_IMAGE_URL . $d->artist->avatar;
            }
            //$d->songs;
            foreach ($d->songs as $aas){
                if($aas->cdn_status == Utility::CDN_STATUS_YES){
                    $aas->real_file = Utility::CDN_IMAGE_URL . $aas->filename;

                }
                else{
                    $aas->real_file = Utility::TEMP_IMAGE_URL .$aas->filename;
                }

            }

            $model = new AlbumComments();
            $model2 = new AlbumLikes();
            $count_likes = $model2->getLikes("album_id",$d->id);
            $count_comment = $model->getComment("album_id",$d->id);
            $d->total_comments = (count($count_comment) == 0) ? "" : count($count_comment);
            $d->total_likes = (count($count_likes) == 0) ? "" : count($count_likes);

        }
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



}
