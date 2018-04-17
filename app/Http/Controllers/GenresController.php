<?php

namespace App\Http\Controllers;

use App\Http\Controllers;
use Illuminate\Support\Facades\view;
use Validator;
use Input;
use DB;
use App\User;
use App\Models\Genres;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Http\Request;
use App\Helpers\Utility;


class GenresController extends Controller
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
            $model = new Genres();
            $data =  $model->createGenre($params->data['name']);

            $this->responseCode = Utility::RESPONSE_CODE_CREATED;
            $this->responseMessage = "Success";

            $this->responseData = array(
                "genre" => $data
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
            $model = new Genres();

            $data = $model->editGenre($params->data['id'], $params->data['name']);
            $this->responseCode = Utility::RESPONSE_CODE_CREATED;
            $this->responseMessage = "Success";

            $this->responseData = array(
                "name" => $params->data['name']
            );


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

            $model = new Genres();
            $data = $model->deleteGenre($params->data['id']);


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

    public function getGenres(Request $request){

        $params = Utility::getRequestData($request);
        Utility::sanitize($params->data);


        if (!array_key_exists('status', $params->data)) {
            $params->data["status"] = "";
        }

        $model = new Genres();
        $data = $model->getGenres($params->data["status"]);


        $this->responseCode = Utility::RESPONSE_CODE_OK;
        $this->responseMessage = "Success";

        $this->responseData = array(

            "genres" => $data

        );

        return Utility::getResponseData($this->responseCode, $this->responseMessage, $this->responseData);
    }

    public function getGenre(Request $request){

        $params = Utility::getRequestData($request);
        Utility::sanitize($params->data);


        $model = new Genres();
        $data = $model->getGenre($params->data["id"]);


        if(count($data) > 0){
            $this->responseCode = Utility::RESPONSE_CODE_OK;
            $this->responseMessage = "Success";

            $this->responseData = array(

                "genre" => $data

            );


        }
        else{
            $this->responseCode = Utility::RESPONSE_CODE_NOT_FOUND;
            $this->responseMessage = "Not Found";

            $this->responseData = array();
        }

        return Utility::getResponseData($this->responseCode, $this->responseMessage, $this->responseData);
    }


}
