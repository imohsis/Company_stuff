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
use Illuminate\Support\Facades\Redirect;
use Illuminate\Http\Request;
use Auth;
use Requests;
use App\Helpers\Utility;

class UserMusicController extends Controller
{
    private $responseCode, $responseMessage, $responseData;
    public function getSongs(Request $request)
    {

        $params = Utility::getRequestData($request);
        Utility::sanitize($params->data);
        if(Utility::hasAccess($params->data["user"])) {
            $model = new Music();
            if (!array_key_exists('status', $params->data)) {
                $params->data["status"] = "";
            }
            $data = $model->getUserSongs(Auth::user()->uid, $params->data["status"],
                $params->data["start"], $params->data["limit"]);

            $this->setupResponseData($data);

            $this->responseCode = Utility::RESPONSE_CODE_OK;
            $this->responseMessage = "Success";
            $this->responseData = array(
                "songs" => $data
            );
        }else{
            $this->responseCode = Utility::RESPONSE_CODE_INVALID_REQUEST;
            $this->responseMessage = "Success";
            $this->responseData = array(
                "songs" => []
            );
        }

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
        $data = $model->getUserRecentSongs(Auth::user()->uid,$params->data["status"],
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

        $model= new Music();
        $data= $model->getUserSong(Auth::user()->uid,$params->data["uid"]);

        if(count($data) > 0){
            $this->setupResponseData($data);
            $updateViews = Utility::updateViews($data);
            $updateSongViews = $model->updateUserSongViews(Auth::user()->uid,$data[0]->uid,$updateViews);
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


    public function create(Request $request){

        $title = $request->get('title');
        $genreId = $request->get('genre_id');
        $albumId =$request->get('album_id');
        $privacy =$request->get('privacy');
        $jwt = $request->get('user');

//        $musicFile = time() . "_" . Utility::generateUID(null, 10) . "."
//            . $request->file('music_file')->getClientOriginalExtension();
        Utility::sanitize($title);
        Utility::sanitize($genreId);
        Utility::sanitize($albumId);
        Utility::sanitize($privacy);
        Utility::sanitize($jwt);

        if(Utility::hasAccess($jwt)) {
            $songFiles = $request->file('song_files');


            $returnData = array();
            if($albumId != ''){

            foreach ($songFiles as $file) {

                $musicFile = time() . "_" . Utility::generateUID(null, 10) . "."
                    . $file->getClientOriginalExtension();

                $file->move(
                    Utility::getTempAudioFilesPath(), $musicFile
                );


                $srcFile = Utility::getTempAudioFilesPath() . $musicFile;
                $getID3 = new \getID3();
                $fileID3Info = $getID3->analyze($srcFile);
                $duration = @$fileID3Info['playtime_string'];;


                $model = new Music();

                $data = $model->createUserSong($title, $genreId, $albumId, $musicFile, '',
                    $duration, $privacy);


                if (Utility::moveToCDN($srcFile, $musicFile, Utility::CDN_AUDIO)) {
                    //Update CDN STATUS in db table
                    $model->hasPushedToCDN($data->uid);
                    $data->real_file = Utility::CDN_AUDIO_URL . $musicFile;
                } else {
                    $data->real_file = Utility::TEMP_AUDIO_URL . $musicFile;
                    //UPLOAD TO CDN FAILED OR FTP COULD NOT CONNECT
                    //CRON WOULD HANDLE FAILED UPLOADS
                }



                $returnData[] = $data;


            }
        }else{
                $songFiles = $request->file('song_files');
                $musicCover = $request->file('music_cover');
                foreach ($songFiles as $file) {

                    $musicFile = time() . "_" . Utility::generateUID(null, 10) . "."
                        . $file->getClientOriginalExtension();

                    $file->move(
                        Utility::getTempAudioFilesPath(), $musicFile
                    );
                    $srcFile = Utility::getTempAudioFilesPath() . $musicFile;
                    $getID3 = new \getID3();
                    $fileID3Info = $getID3->analyze($srcFile);
                    $duration = @$fileID3Info['playtime_string'];

                    foreach ($musicCover as $file) {

                        $cover = time() . "_" . Utility::generateUID(null, 10) . "."
                            . $file->getClientOriginalExtension();

                        $file->move(
                            Utility::getTempImagesPath(), $cover
                        );
                    }

                    $srcCover = Utility::getTempImagesPath() . $cover;

                    $model = new Music();

                    $data = $model->createUserSong($title, $genreId, $albumId, $musicFile, $cover,
                        $duration, $privacy);


                    if (Utility::moveToCDN($srcFile, $musicFile, Utility::CDN_AUDIO)) {
                        //Update CDN STATUS in db table
                        $model->hasPushedToCDN($data->uid);
                        $data->real_file = Utility::CDN_AUDIO_URL . $musicFile;
                    } else {
                        $data->real_file = Utility::TEMP_AUDIO_URL . $musicFile;
                        //UPLOAD TO CDN FAILED OR FTP COULD NOT CONNECT
                        //CRON WOULD HANDLE FAILED UPLOADS
                    }

                    if (Utility::moveToCDN($srcCover, $cover, Utility::CDN_IMAGE)) {
                        //Update CDN STATUS in db table
                        $model->hasPushedToCDN($data->uid);
                        $data->real_cover = Utility::CDN_IMAGE_URL . $cover;
                    } else {
                        $data->real_cover = Utility::CDN_IMAGE_URL . $cover;
                        //UPLOAD TO CDN FAILED OR FTP COULD NOT CONNECT
                        //CRON WOULD HANDLE FAILED UPLOADS
                    }

                    $returnData[] = $data;


                }

            }

            $this->responseCode = Utility::RESPONSE_CODE_CREATED;
            $this->responseMessage = "Success";
            $this->responseData = array(
                "songs" =>$returnData
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
            $data = $model->editUserSong( $params->data['uid'], $params->data['title'],
                $params->data['genre_id'],$params->data['album_id'],$params->data['privacy']);

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
            $edit = $model->editUserSongFile( $uid,
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

    public function editMusicCover(Request $request){

        $uid = Auth::user()->uid;
        $jwt = $request->get('user');
        $coverImg = time() . "_" . Utility::generateUID(null, 10) . "." . $request->file('music_cover')->getClientOriginalExtension();
        Utility::sanitize($jwt);
        Utility::sanitize($uid);

        if(Utility::hasAccess($jwt)) {

            $request->file('music_cover')->move(
                Utility::getTempImagesPath(), $coverImg
            );



            $user = new User();
            $edit = $user->editMusicCover( $uid,
                $coverImg);

            $srcFile = Utility::getTempImagesPath() . $coverImg;
            //echo $srcFile . "\n";

            if(Utility::moveToCDN($srcFile , $coverImg, Utility::CDN_IMAGE)){
                //Update CDN STATUS in db table
                $user->hasPushedToCDN($uid);
                $coverImg = Utility::CDN_IMAGE_URL . $coverImg;
            }
            else{
                $coverImg = Utility::TEMP_IMAGE_URL . $coverImg;

            }

            $this->responseCode = Utility::RESPONSE_CODE_CREATED;
            $this->responseMessage = "Success";
            $this->responseData = array(
                "music_cover" => $coverImg
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
            $model= new Music();
            $data = $model->deleteUserSong($params->data["uid"]);

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
            $d->album;
            if($d->album->cdn_status == Utility::CDN_STATUS_YES){
                $d->album->real_cover = Utility::CDN_IMAGE_URL . $d->album->album_cover;

            }
            else{
                $d->album->real_cover = Utility::TEMP_IMAGE_URL . $d->album->album_cover;
            }

            $model = new MusicComments();
            $fetch_comment = $model->fetchComment('music_uid', $d->uid, $start, $limit);
            $d->count_comment = count($fetch_comment);
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

            $model2 = new MusicLikes();
            $fetch_likes = $model2->fetchLikes('music_uid', $d->uid, $start, $limit);
            $d->count_likes = count($fetch_likes);
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


}
