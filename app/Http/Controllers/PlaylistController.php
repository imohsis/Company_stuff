<?php

namespace App\Http\Controllers;

use App\Http\Controllers;
use Illuminate\Support\Facades\view;
use Validator;
use Input;
use DB;
use App\User;
use App\Models\Playlist;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Http\Request;
use App\Helpers\Utility;

class PlaylistController extends Controller
{
    private $responseCode, $responseMessage, $responseData;
    public function index()
    {

    }

    public function create(Request $request){
        $params = Utility::getRequestData($request);
        Utility::sanitize($params->data);
        //print_r($params->data);
        if(Utility::hasAccess($params->data["user"])){
            $model = new Playlist();
            $data =  $model->createPlaylist($params->data['title'],$params->data['type']);

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


    public function edit(Request $request){

        $params = Utility::getRequestData($request);
        Utility::sanitize($params->data);
        if(Utility::hasAccess($params->data["user"])) {
            $model = new Playlist();

            $data = $model->editPlaylist($params->data['uid'], $params->data['title']);
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

    public function delete(Request $request){

        $params = Utility::getRequestData($request);
        Utility::sanitize($params->data);
        if(Utility::hasAccess($params->data["user"])) {

            $model = new Playlist();
            $data = $model->deletePlaylist($params->data['uid']);


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

    public function getAllPlaylist(Request $request){

        $params = Utility::getRequestData($request);
        Utility::sanitize($params->data);


        if (!array_key_exists('status', $params->data)) {
            $params->data["status"] = "";
        }


        if (!array_key_exists('acc_type', $params->data)) {
            $params->data["acc_type"] = "";
        }

        $model = new Playlist();
        $data = $model->getAllPlaylist($params->data["status"],$params->data["acc_type"]);


        $this->responseCode = Utility::RESPONSE_CODE_OK;
        $this->responseMessage = "Success";

        $this->responseData = array(

            "playlist" => $data

        );

        return Utility::getResponseData($this->responseCode, $this->responseMessage, $this->responseData);
    }

    public function getAllUserPlaylist(Request $request){

        $params = Utility::getRequestData($request);
        Utility::sanitize($params->data);
        $jwt = $params->data['user'];

        if(Utility::hasAccess($jwt)) {
            if (!array_key_exists('status', $params->data)) {
                $params->data["status"] = "";
            }

            $model = new Playlist();
            $data = $model->getAllUserPlaylist($params->data["status"]);


            $this->responseCode = Utility::RESPONSE_CODE_OK;
            $this->responseMessage = "Success";

            $this->responseData = array(

                "playlist" => $data

            );
        }else{
            $this->responseCode = Utility::RESPONSE_CODE_INVALID_REQUEST;
            $this->responseMessage = "Unauthenticated User";

            $this->responseData = array();
        }

        return Utility::getResponseData($this->responseCode, $this->responseMessage, $this->responseData);
    }

    /*public function getPlaylist(Request $request){

        $params = Utility::getRequestData($request);
        Utility::sanitize($params->data);


        $model = new Playlist();
        $data = $model->getPlaylist($params->data["slug"]);


        if(count($data) > 0){
            $this->responseCode = Utility::RESPONSE_CODE_OK;
            $this->responseMessage = "Success";

            $this->responseData = array(

                "playlist" => $data

            );


        }
        else{
            $this->responseCode = Utility::RESPONSE_CODE_NOT_FOUND;
            $this->responseMessage = "Not Found";

            $this->responseData = array();
        }

        return Utility::getResponseData($this->responseCode, $this->responseMessage, $this->responseData);
    }*/

    /*public function getUserPlaylist(Request $request){

        $params = Utility::getRequestData($request);
        Utility::sanitize($params->data);


        $model = new Playlist();
        $data = $model->getUserPlaylist($params->data["slug"]);


        if(count($data) > 0){
            $this->responseCode = Utility::RESPONSE_CODE_OK;
            $this->responseMessage = "Success";

            $this->responseData = array(

                "playlist" => $data

            );


        }
        else{
            $this->responseCode = Utility::RESPONSE_CODE_NOT_FOUND;
            $this->responseMessage = "Not Found";

            $this->responseData = array();
        }

        return Utility::getResponseData($this->responseCode, $this->responseMessage, $this->responseData);
    }*/


}
