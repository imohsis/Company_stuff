<?php

namespace App\Http\Controllers;
use App\Http\Controllers;
use Illuminate\Support\Facades\view;
use Validator;
use Input;
use DB;
use App\User;
use App\Models\Music;
use App\Models\MusicLikes;
use App\Models\MusicComments;
use App\Models\Music_views;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Http\Request;
use App\Helpers\Utility;



class MusicController extends Controller
{
    private $responseCode, $responseMessage, $responseData;
    public function getSongs(Request $request)
    {

        $params = Utility::getRequestData($request);
        Utility::sanitize($params->data);

        $model = new Music();
        if (!array_key_exists('status', $params->data)) {
            $params->data["status"] = "";
        }
        $data = $model->getSongs($params->data["status"],
            $params->data["start"],$params->data["limit"]);

        $this->setupResponseData($data);

        $this->responseCode = Utility::RESPONSE_CODE_OK;
        $this->responseMessage = "Success";
        $this->responseData = array(
            "songs" => $data
        );

        return Utility::getResponseData($this->responseCode, $this->responseMessage, $this->responseData);
    }

    public function getRecentSongs(Request $request)
    {

        $params = Utility::getRequestData($request);
        Utility::sanitize($params->data);

        $model= new Music();
        if (!array_key_exists('status', $params->data)) {
            $params->data["status"] = "";
        }
        $data = $model->getRecentSongs($params->data["status"],
            $params->data["start"],$params->data["limit"]);

        $this->setupResponseData($data);

        $this->responseCode = Utility::RESPONSE_CODE_OK;
        $this->responseMessage = "Success";
        $this->responseData = array(
            "songs" =>$data
        );

        return Utility::getResponseData($this->responseCode, $this->responseMessage, $this->responseData);
    }

    public function getTopSongs(Request $request)
    {

        $params = Utility::getRequestData($request);
        Utility::sanitize($params->data);

        $model= new Music();
        if (!array_key_exists('status', $params->data)) {
            $params->data["status"] = "";
        }
        $data = $model->getRecentSongs($params->data["status"],
            $params->data["start"],$params->data["limit"]);

        $this->setupResponseData($data);

        $this->responseCode = Utility::RESPONSE_CODE_OK;
        $this->responseMessage = "Success";
        $this->responseData = array(
            "songs" =>$data
        );

        return Utility::getResponseData($this->responseCode, $this->responseMessage, $this->responseData);
    }

    public function getSong(Request $request)
    {

        $params = Utility::getRequestData($request);
        Utility::sanitize($params->data);

        if (!array_key_exists('comment_start', $params->data)) {
            $params->data["comment_start"] = "0";
        }

        if (!array_key_exists('comment_limit', $params->data)) {
            $params->data["comment_limit"] = "10";
        }

        $model= new Music();
        $music_view = new Music_views();
        $data= $model->getSong($params->data["slug"]);

        if(count($data) > 0){
            $this->setupResponseDataSingle($data,$params->data["start"],$params->data["limit"]);

            $updateViews = Utility::updateViews($data);
            $updateMusicViews = $model->updateUserSongViews($data[0]->uid,$updateViews);
            $create_view = $music_view->createView($data[0]->uid);
            $this->responseCode = Utility::RESPONSE_CODE_OK;
            $this->responseMessage = "Success";
            $this->responseData = array(
                "song" =>$data
            );
        }
        else{
            $this->responseCode = Utility::RESPONSE_CODE_NOT_FOUND;
            $this->responseMessage = "Not Found";
            $this->responseData = array();
        }



        return Utility::getResponseData($this->responseCode, $this->responseMessage, $this->responseData);
    }

    public function getByGenre(Request $request)
    {

        $params = Utility::getRequestData($request);
        Utility::sanitize($params->data);

        $model= new Music();
        if (!array_key_exists('status', $params->data)) {
            $params->data["status"] = "";
        }
        $data = $model->getByGenre($params->data["genre_id"],$params->data["status"],
            $params->data["start"],$params->data["limit"]);

        $this->setupResponseData($data);

        $this->responseCode = Utility::RESPONSE_CODE_OK;
        $this->responseMessage = "Success";
        $this->responseData = array(
            "songs" =>$data
        );

        return Utility::getResponseData($this->responseCode, $this->responseMessage, $this->responseData);
    }

    public function getByArtist(Request $request)
    {

        $params = Utility::getRequestData($request);
        Utility::sanitize($params->data);

        $model= new Music();
        if (!array_key_exists('status', $params->data)) {
            $params->data["status"] = "";
        }
        $data = $model->getByArtist($params->data["artist_uid"],$params->data["status"],
            $params->data["start"],$params->data["limit"]);

        $this->setupResponseData($data);

        $this->responseCode = Utility::RESPONSE_CODE_OK;
        $this->responseMessage = "Success";
        $this->responseData = array(
            "songs" =>$data
        );

        return Utility::getResponseData($this->responseCode, $this->responseMessage, $this->responseData);
    }

    public function create(Request $request){

        $title =$request->get('title');
        $tag =$request->get('tag');
        $artistUid =$request->get('artist_uid');
        $genreId =$request->get('genre_id');
        $albumId =$request->get('album_id');
        $jwt = $request->get('user');

//        $musicFile = time() . "_" . Utility::generateUID(null, 10) . "."
//            . $request->file('music_file')->getClientOriginalExtension();
        Utility::sanitize($title);
        Utility::sanitize($tag);
        Utility::sanitize($artistUid);
        Utility::sanitize($genreId);
        Utility::sanitize($albumId);
        Utility::sanitize($jwt);

        if(Utility::hasAccess($jwt)) {
            $songFiles = $request->file('song_files');
            $returnData = array();
            foreach($songFiles as $file){

                $musicFile = time() . "_" . Utility::generateUID(null, 10) . "."
                    . $file->getClientOriginalExtension();

                $file->move(
                    Utility::getTempAudioFilesPath(), $musicFile
                );
                $srcFile = Utility::getTempAudioFilesPath() . $musicFile;
                $getID3  = new \getID3();
                $fileID3Info = $getID3 ->analyze($srcFile);
                $duration =  @$fileID3Info['playtime_string'];;

                $model = new Music();
                $data = $model->createSong($title,$genreId,$artistUid,$albumId,$musicFile,
                    $duration,$tag);



                if(Utility::moveToCDN($srcFile , $musicFile, Utility::CDN_AUDIO)){
                    //Update CDN STATUS in db table
                    $model->hasPushedToCDN($data->uid);
                    $data->real_file = Utility::CDN_AUDIO_URL . $musicFile;
                }
                else{
                    $data->real_file = Utility::TEMP_AUDIO_URL . $musicFile;
                    //UPLOAD TO CDN FAILED OR FTP COULD NOT CONNECT
                    //CRON WOULD HANDLE FAILED UPLOADS
                }
                $returnData[] = $data;


            }
            /*
            $request->file('music_file')->move(
                Utility::getTempAudioFilesPath(), $musicFile
            );
            $srcFile = Utility::getTempAudioFilesPath() . $musicFile;
            $getID3  = new \getID3();
            $fileID3Info = $getID3 ->analyze($srcFile);
            $duration =  @$fileID3Info['playtime_string'];;

            $model = new Music();
            $data = $model->createSong($title,$genreId,$artistUid,$albumId,$musicFile,
                $duration);



            if(Utility::moveToCDN($srcFile , $musicFile, Utility::CDN_AUDIO)){
                //Update CDN STATUS in db table
                $model->hasPushedToCDN($data->uid);
                $data->real_file = Utility::CDN_AUDIO_URL . $musicFile;
            }
            else{
                $data->real_file = Utility::TEMP_AUDIO_URL . $musicFile;
                //UPLOAD TO CDN FAILED OR FTP COULD NOT CONNECT
                //CRON WOULD HANDLE FAILED UPLOADS
            }
            */

            $this->responseCode = Utility::RESPONSE_CODE_CREATED;
            $this->responseMessage = "Success";
            $this->responseData = array(
                "music" =>$returnData
            );

        }
        else{
            $this->responseCode = Utility::RESPONSE_CODE_INVALID_REQUEST;
            $this->responseMessage = "Unauthenticated User";

            $this->responseData = array();
        }

        return Utility::getResponseData($this->responseCode, $this->responseMessage, $this->responseData);
    }

    public function editSong(Request $request){

        $params = Utility::getRequestData($request);
        Utility::sanitize($params->data);
        if(Utility::hasAccess($params->data["user"])) {
            $model = new Music();
            $data = $model->editSong( $params->data['uid'], $params->data['title'],
                $params->data['artist_uid'] ,$params->data['genre_id'],$params->data['album_id'],$params->data['tag']);

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


    public function editSongFile(Request $request){

        $uid = $request->get('uid');
        $jwt = $request->get('user');
        $musicFile = time() . "_" . Utility::generateUID(null, 10) . "." . $request->file('song_file')->getClientOriginalExtension();
        Utility::sanitize($jwt);
        Utility::sanitize($uid);

        if(Utility::hasAccess($jwt)) {

            $request->file('song_file')->move(
                Utility::getTempAudioFilesPath(), $musicFile
            );

            $srcFile = Utility::getTempAudioFilesPath() . $musicFile;
            $getID3  = new \getID3();
            $fileID3Info = $getID3 ->analyze($srcFile);
            $duration =  @$fileID3Info['playtime_string'];;

            $model = new Music();
            $edit = $model->editSongFile( $uid,
                $srcFile , $duration);


            if(Utility::moveToCDN($srcFile , $musicFile, Utility::CDN_AUDIO)){
                //Update CDN STATUS in db table
                $model->hasPushedToCDN($uid);
                $musicFile= Utility::CDN_AUDIO_URL . $musicFile;
            }
            else{
                $musicFile= Utility::TEMP_AUDIO_URL . $musicFile;
                //UPLOAD TO CDN FAILED OR FTP COULD NOT CONNECT
                //CRON WOULD HANDLE FAILED UPLOADS
            }

            $this->responseCode = Utility::RESPONSE_CODE_CREATED;
            $this->responseMessage = "Success";
            $this->responseData = array(
                "song_file" => $musicFile,
                "duration" => $duration
            );

        }
        else{
            $this->responseCode = Utility::RESPONSE_CODE_INVALID_REQUEST;
            $this->responseMessage = "Unathenticated User";

            $this->responseData = array();
        }

        return Utility::getResponseData($this->responseCode, $this->responseMessage, $this->responseData);
    }


    public function delete(Request $request)
    {

        $params = Utility::getRequestData($request);
        Utility::sanitize($params->data);
        if(Utility::hasAccess($params->data["user"])) {
            $model= new Music();
            $data = $model->deleteSong($params->data["uid"]);

            $this->responseCode = Utility::RESPONSE_CODE_NO_CONTENT;
            $this->responseMessage = "Success";
            $this->responseData = array();

        }
        else{
            $this->responseCode = Utility::RESPONSE_CODE_INVALID_REQUEST;
            $this->responseMessage = "Unathenticated User";

            $this->responseData = array();
        }

        return Utility::getResponseData($this->responseCode, $this->responseMessage, $this->responseData);
    }

    public function loadMoreComments(Request $request)
    {

        $params = Utility::getRequestData($request);
        Utility::sanitize($params->data);

        $comment = new MusicComments();

        $feed = $comment->loadMore($params->data["last_id"],'music_uid',$params->data["post_uid"]);
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

        $comment = new MusicLikes();

        $feed = $comment->loadMore($params->data["last_id"],'music_uid',$params->data["post_uid"]);
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
                $d->real_file = Utility::CDN_AUDIO_URL . $d->filename;
            }
            else{
                $d->real_file = Utility::TEMP_AUDIO_URL . $d->filename;
            }
            $d->artist;
            if($d->artist->cdn_status == Utility::CDN_STATUS_YES){
                $d->artist->real_avatar = Utility::CDN_IMAGE_URL . $d->artist->avatar;

            }
            else{
                $d->artist->real_avatar = Utility::TEMP_IMAGE_URL . $d->artist->avatar;
            }
            $album = ['real_cover'];
            if($d->album_id != 0 or $d->album_id != ""){
            $d->album;
            if($d->album->cdn_status == Utility::CDN_STATUS_YES){
                $d->album->real_cover = Utility::CDN_IMAGE_URL . $d->album->album_cover;

            }
            else{
                $d->album->real_cover = Utility::TEMP_IMAGE_URL . $d->album->album_cover;
            }
                }else{
                $d->album[0] = Utility::DEFAULT_SONG_LOGO;
            }

            $model = new MusicComments();
            $fetch_comment = $model->fetchComment('music_uid', $d->uid, $start, $limit);
            $d->total_comments = count($fetch_comment);
            foreach($fetch_comment as $com) {
                $user_data = [];
                $user = new User();
                $log_user = $user->getSingleUserByUid($com->user_id);
                if(count($log_user)>0){
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

            $model2 = new MusicLikes();
            $fetch_likes = $model2->fetchLikes('music_uid', $d->uid, $start, $limit);
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
                $d->real_file = Utility::CDN_AUDIO_URL . $d->filename;
            }
            else{
                $d->real_file = Utility::TEMP_AUDIO_URL . $d->filename;
            }
            $d->artist;
            if($d->artist->cdn_status == Utility::CDN_STATUS_YES){
                $d->artist->real_avatar = Utility::CDN_IMAGE_URL . $d->artist->avatar;

            }
            else{
                $d->artist->real_avatar = Utility::TEMP_IMAGE_URL . $d->artist->avatar;
            }
            $album = ['real_cover'];
            if($d->album_id != 0 or $d->album_id != "") {
                $d->album;

                    if ($d->album->cdn_status == Utility::CDN_STATUS_YES) {
                        $d->album->real_cover = Utility::CDN_IMAGE_URL . $d->album->album_cover;

                    } else {

                        $d->album->real_cover = Utility::TEMP_IMAGE_URL . $d->album->album_cover;
                    }

            }/*else{
                $d->album[0] = Utility::DEFAULT_SONG_LOGO;
            }*/

            $model = new MusicComments();
            $model2 = new MusicLikes();
            $count_likes = $model2->getLikes("music_uid",$d->uid);
            $count_comment = $model->getComment("music_uid",$d->uid);
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

    public function testID3(Request $request){
        $getID3  = new \getID3();

        $musicFile = time() . "_" . Utility::generateUID(null, 10) . "."
            . $request->file('music_file')->getClientOriginalExtension();

        $request->file('music_file')->move(
            Utility::getTempAudioFilesPath(), $musicFile);

        $srcFile = Utility::getTempAudioFilesPath() . $musicFile;
        echo $srcFile . "\n";

        $ThisFileInfo = $getID3 ->analyze($srcFile);
        //$idTags = $oReader->getTagsInfo($srcFile); // obtaining ID3 tags info

        //print_r($ThisFileInfo);
        echo "duration -". @$ThisFileInfo['playtime_string'];

    }

 
}
