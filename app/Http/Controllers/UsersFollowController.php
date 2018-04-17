<?php

namespace App\Http\Controllers;

use App\Http\Controllers;
use Illuminate\Support\Facades\view;
use Validator;
use Input;
use DB;
use App\Helpers\Utility;
use App\User;
use App\Models\UsersFollow;
use Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Http\Request;
use Requests;

class UsersFollowController extends Controller
{
    private $responseCode, $responseMessage, $responseData;

    public function getFollowingUser(Request $request){

        $params = Utility::getRequestData($request);
        Utility::sanitize($params->data['user']);

        if(Utility::hasAccess($params->data['user'])) {

            $follow = new UsersFollow();

            $change = $follow->getFollowingUser(Auth::user()->uid);
            $hold_userId = [];
            if(count($change) >0){
                foreach($change as $user){
                    $hold_userId[] = $user->user_id;
                }
                $user = new User();
                $following = $user->follow($hold_userId);
            }else{
                $following = [];
            }

            $this->responseCode = Utility::RESPONSE_CODE_CREATED;
            $this->responseMessage = "Success";
            $this->responseData = array(
                "follow" =>$following
            );

        }
        else{
            $this->responseCode = Utility::RESPONSE_CODE_INVALID_REQUEST;
            $this->responseMessage = "Unauthenticated User";

            $this->responseData = array();
        }

        return Utility::getResponseData($this->responseCode, $this->responseMessage, $this->responseData);
    }

    public function getUserFollowed(Request $request){

        $params = Utility::getRequestData($request);
        Utility::sanitize($params->data['user']);

        if(Utility::hasAccess($params->data['user'])) {

            $follow = new UsersFollow();

            $change = $follow->getUserFollowed(Auth::user()->uid);
            $hold_userId = [];
            if(count($change) >0){
                foreach($change as $user){
                    $hold_userId[] = $user->follower_id;
                }
                $user = new User();
                $followed = $user->follow($hold_userId);
            }else{
                $followed = [];
            }

            $this->responseCode = Utility::RESPONSE_CODE_CREATED;
            $this->responseMessage = "Success";
            $this->responseData = array(
                "follow" =>$followed
            );

        }
        else{
            $this->responseCode = Utility::RESPONSE_CODE_INVALID_REQUEST;
            $this->responseMessage = "Unauthenticated User";

            $this->responseData = array();
        }

        return Utility::getResponseData($this->responseCode, $this->responseMessage, $this->responseData);
    }


    public function followUser(Request $request){

        $params = Utility::getRequestData($request);
        Utility::sanitize($params->data['user']);

        if(Utility::hasAccess($params->data['user'])) {

            $follow = new UsersFollow();
            $followUid = $params->data['follow_uid'];
            $checkFollowers = $follow->checkFollower(Auth::user()->uid,$followUid);

            if(count($checkFollowers) >0){

            }else{
                $create = $follow->followUser(Auth::user()->uid, $followUid);
            }

            $this->responseCode = Utility::RESPONSE_CODE_CREATED;
            $this->responseMessage = "Success";
            $this->responseData = array(
                "follow" =>$create
            );

        }
        else{
            $this->responseCode = Utility::RESPONSE_CODE_INVALID_REQUEST;
            $this->responseMessage = "Unauthenticated User";

            $this->responseData = array();
        }

        return Utility::getResponseData($this->responseCode, $this->responseMessage, $this->responseData);
    }

    public function unfollowUser(Request $request){

        $params = Utility::getRequestData($request);
        Utility::sanitize($params->data['user']);

        if(Utility::hasAccess($params->data['user'])) {

            $follow = new UsersFollow();
            $followUid = $params->data['follow_uid'];

                $change = $follow->unfollowUser(Auth::user()->uid, $followUid);

            $this->responseCode = Utility::RESPONSE_CODE_CREATED;
            $this->responseMessage = "Success";
            $this->responseData = array(
                "follow" =>$followUid
            );

        }
        else{
            $this->responseCode = Utility::RESPONSE_CODE_INVALID_REQUEST;
            $this->responseMessage = "Unauthenticated User";

            $this->responseData = array();
        }

        return Utility::getResponseData($this->responseCode, $this->responseMessage, $this->responseData);
    }

    public function blockFollower(Request $request){

        $params = Utility::getRequestData($request);
        Utility::sanitize($params->data['user']);

        if(Utility::hasAccess($params->data['user'])) {

            $follow = new UsersFollow();
            $followUid = $params->data['follow_uid'];

            $change = $follow->blockFollower(Auth::user()->uid, $followUid);

            $this->responseCode = Utility::RESPONSE_CODE_CREATED;
            $this->responseMessage = "Success";
            $this->responseData = array(
                "follow" =>$change
            );

        }
        else{
            $this->responseCode = Utility::RESPONSE_CODE_INVALID_REQUEST;
            $this->responseMessage = "Unauthenticated User";

            $this->responseData = array();
        }

        return Utility::getResponseData($this->responseCode, $this->responseMessage, $this->responseData);
    }

    public function unblockFollower(Request $request){

        $params = Utility::getRequestData($request);
        Utility::sanitize($params->data['user']);

        if(Utility::hasAccess($params->data['user'])) {

            $follow = new UsersFollow();
            $followUid = $params->data['follow_uid'];

            $change = $follow->unblockFollower(Auth::user()->uid, $followUid);

            $this->responseCode = Utility::RESPONSE_CODE_CREATED;
            $this->responseMessage = "Success";
            $this->responseData = array(
                "follow" =>$change
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
