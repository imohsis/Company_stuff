<?php

namespace App\Http\Controllers;

use App\Http\Controllers;
use App\Helpers\Utility;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\view;
use Monolog\Handler\Curl\Util;
use Validator;
use DB;
use App\User;
use App\Models\News;
use App\Models\Playlist;
use App\Models\PlaylistFiles;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Http\Request;
use Auth;

// import the Intervention Image Manager Class
//use Intervention\Image\ImageManager;
use Image;

class PlaylistFilesController extends Controller
{

    private $responseCode, $responseMessage, $responseData;


    public function getPlaylistFilesByPlaylist(Request $request)
    {

        $params = Utility::getRequestData($request); //convert json request to array object data
        Utility::sanitize($params->data); //a must sanitization

        $get= new PlaylistFiles();
        $news = $get->getPlaylistFilesByPlaylist($params->data["playlist_uid"]);


        Utility::formatDateFields($news);
        $this->alignResponseData($news);

        if(count($news) > 0){

            $this->responseCode = Utility::RESPONSE_CODE_OK;
            $this->responseMessage = "Success";
            $this->responseData = array(
                "playlist" =>$news
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
        $params = Utility::getRequestData($request);
        Utility::sanitize($params->data);
        //print_r($params->data);
        if(Utility::hasAccess($params->data["user"])){
            $model = new PlaylistFiles();
            $data =  $model->createPlaylistFiles($params->data['playlist_uid'],$params->data['file_uid']);

            $this->responseCode = Utility::RESPONSE_CODE_CREATED;
            $this->responseMessage = "Success";

            $this->responseData = array(
                "playlist" => $data
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
            $get= new PlaylistFiles();
            $songFile = $get->deleteSongFile($params->data["id"]);

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


    private function  alignResponseData($news){
        foreach($news as $new){

            $new->playlist_title = $new->my_playlist->title;
            $new->playlist_type = $new->my_playlist->type;

        }
    }

    public function test(Request $request){

        $params =  Utility::getRequestData($request);
        Utility::sanitize($params->data);
        Utility::hasAccess($params->data['token']);


        // echo json_encode($request);
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
            $this->responseMessage = "Unauthenticated User";

            $this->responseData = array();
        }

        return Utility::getResponseData($this->responseCode, $this->responseMessage, $this->responseData);
    }


}
