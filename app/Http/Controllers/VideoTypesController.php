<?php

namespace App\Http\Controllers;

use App\Helpers\Utility;
use App\Http\Controllers;
use Illuminate\Support\Facades\view;
use Validator;
use Input;
use DB;
use App\User;
use App\Models\VideoTypes;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Http\Request;

class VideoTypesController extends Controller
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
            $videoType = new VideoTypes();
            $create =  $videoType->createVideoType($params->data['type']);

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


    public function edit(Request $request){

        $params = Utility::getRequestData($request);
        Utility::sanitize($params->data);
        if(Utility::hasAccess($params->data["user"])) {
            $videoType = new VideoTypes();

            $edit = $videoType->editVideoType($params->data['id'], $params->data['type']);
            $this->responseCode = Utility::RESPONSE_CODE_CREATED;
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

    public function delete(Request $request){

        $params = Utility::getRequestData($request);
        Utility::sanitize($params->data);
        if(Utility::hasAccess($params->data["user"])) {

            $videoType = new VideoTypes();
            $delete = $videoType->deleteVideoType($params->data['id']);


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

    public function getVideoTypes(Request $request){

        $params = Utility::getRequestData($request);
        Utility::sanitize($params->data);

        if (!array_key_exists('status', $params->data)) {
            $params->data["status"] = "";
        }

        $videoType = new VideoTypes();
        $types = $videoType->getVideoTypes($params->data["status"]);


        $this->responseCode = Utility::RESPONSE_CODE_OK;
        $this->responseMessage = "Success";

        $this->responseData = array(

            "types" => $types

        );



        return Utility::getResponseData($this->responseCode, $this->responseMessage, $this->responseData);
    }

    public function getVideoType(Request $request){

        $params = Utility::getRequestData($request);
        Utility::sanitize($params->data);

        $videoType = new VideoTypes();
        $type = $videoType->getVideoType($params->data["id"]);


        if(count($type) > 0){
            $this->responseCode = Utility::RESPONSE_CODE_OK;
            $this->responseMessage = "Success";

            $this->responseData = array(

                "type" => $type

            );
        }
        else{
            $this->responseCode = Utility::RESPONSE_CODE_NOT_FOUND;
            $this->responseMessage = "Not Found";

            $this->responseData = array();
        }




        return Utility::getResponseData($this->responseCode, $this->responseMessage, $this->responseData);
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
