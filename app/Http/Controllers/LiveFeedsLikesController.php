<?php

namespace App\Http\Controllers;

use App\Http\Controllers;
use Illuminate\Support\Facades\view;
use Validator;
use Input;
use DB;
use App\User;
use App\Models\LiveFeedLikes;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Http\Request;
use App\Helpers\Utility;
class LiveFeedsLikesController extends Controller
{
    //
    private $responseCode, $responseMessage, $responseData;

    public function index()
    {

    }

    public function create(Request $request){
        $params = Utility::getRequestData($request);
        Utility::sanitize($params->data);
        //print_r($params->data);
        if(Utility::hasAccess($params->data["user"])){
            $model = new LiveFeedLikes();

            $this->responseCode = Utility::RESPONSE_CODE_CREATED;
            $userLiked = $model->getUserLiked('live_feed_uid',$params->data['post_uid']);
            if(count($userLiked) > 0) {
                $deleteLiked = $model->deleteLiked('live_feed_uid',$params->data['post_uid']);
                $this->responseMessage = "dislike";
                $this->responseData = array(
                    "like" => 'deleted'
                );
            }else{
                $data =  $model->like('live_feed_uid',$params->data['post_uid'],$params->data['type']);
                $this->responseMessage = "liked";
                $this->responseData = array(
                    "like" => $data
                );

            }

        }
        else{
            $this->responseCode = Utility::RESPONSE_CODE_INVALID_REQUEST;
            $this->responseMessage = "Unauthenticated User";

            $this->responseData = array();
        }


        return Utility::getResponseData($this->responseCode, $this->responseMessage, $this->responseData);
    }



}
