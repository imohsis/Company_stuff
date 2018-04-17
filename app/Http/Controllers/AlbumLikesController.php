<?php

namespace App\Http\Controllers;

use App\Http\Controllers;
use Illuminate\Support\Facades\view;
use Validator;
use Input;
use DB;
use App\User;
use Auth;
use App\Models\AlbumLikes;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Http\Request;
use App\Helpers\Utility;
use App\Helpers\JWT;

class AlbumLikesController extends Controller
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
            $model = new AlbumLikes();


            $this->responseCode = Utility::RESPONSE_CODE_CREATED;
            $userLiked = $model->getUserLiked('album_id',$params->data['post_uid']);
            if(count($userLiked) > 0) {
                $deleteLiked = $model->deleteLiked('album_id',$params->data['post_uid']);
                $this->responseMessage = "dislike";
                $this->responseData = array(
                    "like" => 'deleted'
                );
            }else{
                $data =  $model->like('album_id',$params->data['post_uid'],$params->data['type']);
                $this->responseMessage = "liked";
                $this->responseData = array(
                    "like" => $data
                );

            }


        }
        else{
            $this->responseCode = Utility::RESPONSE_CODE_INVALID_REQUEST;
            $this->responseMessage = "Unauthenticated";

            $this->responseData = array();
        }


        return Utility::getResponseData($this->responseCode, $this->responseMessage, $this->responseData);
    }

 
}
