<?php

namespace App\Http\Controllers;

use App\Helpers\Utility;
use App\Http\Controllers;
use Illuminate\Support\Facades\view;
use Validator;
use Input;
use DB;
use App\User;
use App\Models\NewsCatg;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Http\Request;

class NewsCatgController extends Controller
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
            $newsCatg = new NewsCatg();
            $create =  $newsCatg->createNewsCatg($params->data['catName']);

            $this->responseCode = Utility::RESPONSE_CODE_CREATED;
            $this->responseMessage = "Success";

            $this->responseData = array(
                "data" => array(
                    "id" => $create->id,
                    "catName" => $create->cat_name
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


    public function edit(Request $request){

        $params = Utility::getRequestData($request);
        Utility::sanitize($params->data);
        if(Utility::hasAccess($params->data["user"])) {
            $videoType = new NewsCatg();

            $edit = $videoType->editNewsCatg($params->data['id'], $params->data['catName']);
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

            $newsCatg = new NewsCatg();
            $delete = $newsCatg->deleteNewsCatg($params->data['id']);


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

    public function getNewsCatg(Request $request){

        $params = Utility::getRequestData($request);
        Utility::sanitize($params->data);


        if (!array_key_exists('status', $params->data)) {
            $params->data["status"] = "";
        }

        $newsCatg = new NewsCatg();
        $catName = $newsCatg->getNewsCatgs($params->data["status"]);


        $this->responseCode = Utility::RESPONSE_CODE_OK;
        $this->responseMessage = "Success";

        $this->responseData = array(

            "catName" => $catName

        );

        return Utility::getResponseData($this->responseCode, $this->responseMessage, $this->responseData);
    }

    public function getSingleNewsCatg(Request $request){

        $params = Utility::getRequestData($request);
        Utility::sanitize($params->data);


            $newsCatg = new NewsCatg();
            $catName = $newsCatg->getSingleNewsCatg($params->data["id"]);


            if(count($catName) > 0){
                $this->responseCode = Utility::RESPONSE_CODE_OK;
                $this->responseMessage = "Success";

                $this->responseData = array(

                    "catName" => $catName

            );


        }
        else{
            $this->responseCode = Utility::RESPONSE_CODE_INVALID_REQUEST;
            $this->responseMessage = "Unauthenticated User";

            $this->responseData = array();
        }

        return Utility::getResponseData($this->responseCode, $this->responseMessage, $this->responseData);
    }



    public function template(Request $request){

        $params = Utility::getRequestData($request);
        Utility::sanitize($params->data);
        if(Utility::hasAccess($params->data["user"])) {
            //Modify this block
            $newsCatg = new NewsCatg();
            $create = $newsCatg->creatNewsCatg($params->data['catName']);
            //

            $this->responseCode = Utility::RESPONSE_CODE_CREATED;
            $this->responseMessage = "Success";

            $this->responseData = array(
                "data" => array(
                    "id" => $create->id,
                    "catName" => $create->type
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
