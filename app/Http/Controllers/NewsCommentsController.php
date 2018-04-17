<?php

namespace App\Http\Controllers;

use App\Http\Controllers;
use Illuminate\Support\Facades\view;
use Validator;
use Input;
use DB;
use App\User;
use App\Models\NewsComments;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Http\Request;
use App\Helpers\Utility;

class NewsCommentsController extends Controller
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
            $model = new NewsComments();
            $data =  $model->comment('news_uid',$params->data['post_uid'],$params->data['comment']);

            $this->responseCode = Utility::RESPONSE_CODE_CREATED;
            $this->responseMessage = "Success";
            //if($data){
            $get_comment = $model->getComment("news_uid",$params->data['post_uid']);
            //$data->count_comment = count($get_comment);
            $this->responseData = array(
                "comment" => $data
            );
            //}

        }
        else{
            $this->responseCode = Utility::RESPONSE_CODE_INVALID_REQUEST;
            $this->responseMessage = "Unauthenticated User";

            $this->responseData = array();
        }


        return Utility::getResponseData($this->responseCode, $this->responseMessage, $this->responseData);
    }

    public function getComments(Request $request){
        $params = Utility::getRequestData($request);
        Utility::sanitize($params->data);

            $model = new NewsComments();

            $this->responseCode = Utility::RESPONSE_CODE_CREATED;
            $this->responseMessage = "Success";

            $get_comment = $model->fetchComment('news_uid', $params->data['post_uid'],
                $params->data['start'], $params->data['limit']);

            foreach($get_comment as $com){
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

            $this->responseData = array(
                "comment" => $get_comment
            );


        return Utility::getResponseData($this->responseCode, $this->responseMessage, $this->responseData);
    }


}
