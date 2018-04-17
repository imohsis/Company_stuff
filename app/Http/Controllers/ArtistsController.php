<?php

namespace App\Http\Controllers;

use App\Helpers\FTP;
use App\Http\Controllers;
use Illuminate\Support\Facades\view;
use Monolog\Handler\Curl\Util;
use Validator;
use Input;
use DB;
use App\User;
use App\Models\Artists;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Http\Request;
use App\Helpers\Utility;


class ArtistsController extends Controller
{
    private $responseCode, $responseMessage, $responseData;
    public function getArtists(Request $request)
    {

        $params = Utility::getRequestData($request);
        Utility::sanitize($params->data);

        $artist= new Artists();
        if (!array_key_exists('status', $params->data)) {
            $params->data["status"] = "";
        }
        $artists = $artist->getArtists($params->data["status"],
            $params->data["start"],$params->data["limit"]);

        $this->setupResponseData($artists);



        $this->responseCode = Utility::RESPONSE_CODE_OK;
        $this->responseMessage = "Success";
        $this->responseData = array(
            "artists" =>$artists
        );

        return Utility::getResponseData($this->responseCode, $this->responseMessage, $this->responseData);
    }

    public function getRecentArtists(Request $request)
    {

        $params = Utility::getRequestData($request);
        Utility::sanitize($params->data);

        $artist= new Artists();
        if (!array_key_exists('status', $params->data)) {
            $params->data["status"] = "";
        }
        $artists = $artist->getRecentArtists($params->data["status"],
            $params->data["start"],$params->data["limit"]);

        $this->setupResponseData($artists);

        $this->responseCode = Utility::RESPONSE_CODE_OK;
        $this->responseMessage = "Success";
        $this->responseData = array(
            "artists" =>$artists
        );

        return Utility::getResponseData($this->responseCode, $this->responseMessage, $this->responseData);
    }

    public function getArtist(Request $request)
    {

        $params = Utility::getRequestData($request);
        Utility::sanitize($params->data);

        $get= new Artists();
        $artist= $get->getArtist($params->data["slug"]);

        if(count($artist) > 0){

            $this->setupResponseData($artist);

            $this->responseCode = Utility::RESPONSE_CODE_OK;
            $this->responseMessage = "Success";
            $this->responseData = array(
                "artist" =>$artist
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

        //$params = Utility::getRequestData($request);
        $name =$request->get('name');
        $jwt = $request->get('user');
        $avatarName = time() . "_" . Utility::generateUID(null, 10) . "." . $request->file('avatar')->getClientOriginalExtension();
        Utility::sanitize($name);
        Utility::sanitize($jwt);

        if(Utility::hasAccess($jwt)) {

            $request->file('avatar')->move(
                Utility::getTempImagesPath(), $avatarName
            );



            $artist = new Artists();
            $create = $artist->createArtist( $name,
                $avatarName);

            $srcFile = Utility::getTempImagesPath() . $avatarName;
            //echo $srcFile . "\n";

            if(Utility::moveToCDN($srcFile , $avatarName, Utility::CDN_IMAGE)){
                //Update CDN STATUS in db table
                $artist->hasPushedToCDN($create->uid);
                $create->real_avatar = Utility::CDN_IMAGE_URL . $avatarName;
            }
            else{
                $create->real_avatar = Utility::TEMP_IMAGE_URL . $avatarName;
                //UPLOAD TO CDN FAILED OR FTP COULD NOT CONNECT
                //CRON WOULD HANDLE FAILED UPLOADS
            }

            $this->responseCode = Utility::RESPONSE_CODE_CREATED;
            $this->responseMessage = "Success";
            $this->responseData = array(
                "artist" =>$create
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
            $artist = new Artists();
            $edit = $artist->editArtist( $params->data['uid'], $params->data['name'],
                $params->data['avatar']);

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


    public function editName(Request $request){

        $params = Utility::getRequestData($request);
        Utility::sanitize($params->data);
        if(Utility::hasAccess($params->data["user"])) {
            $artist = new Artists();
            $edit = $artist->editName( $params->data['uid'], $params->data['name']);

            $this->responseCode = Utility::RESPONSE_CODE_CREATED;
            $this->responseMessage = "Success";
            $this->responseData = array(
                "name" => $params->data['name']
            );

        }
        else{
            $this->responseCode = Utility::RESPONSE_CODE_INVALID_REQUEST;
            $this->responseMessage = "Unathenticated User";

            $this->responseData = array();
        }

        return Utility::getResponseData($this->responseCode, $this->responseMessage, $this->responseData);
    }


    public function editAvatar(Request $request){

        $uid = $request->get('uid');
        $jwt = $request->get('user');
        $avatarName = time() . "_" . Utility::generateUID(null, 10) . "." . $request->file('avatar')->getClientOriginalExtension();
        Utility::sanitize($jwt);
        Utility::sanitize($uid);

        if(Utility::hasAccess($jwt)) {

            $request->file('avatar')->move(
                Utility::getTempImagesPath(), $avatarName
            );



            $artist = new Artists();
            $edit = $artist->editAvatar( $uid,
                $avatarName);

            $srcFile = Utility::getTempImagesPath() . $avatarName;
            //echo $srcFile . "\n";

            if(Utility::moveToCDN($srcFile , $avatarName, Utility::CDN_IMAGE)){
                //Update CDN STATUS in db table
                $artist->hasPushedToCDN($uid);
                $avatarName = Utility::CDN_IMAGE_URL . $avatarName;
            }
            else{
                $avatarName = Utility::TEMP_IMAGE_URL . $avatarName;

            }

            $this->responseCode = Utility::RESPONSE_CODE_CREATED;
            $this->responseMessage = "Success";
            $this->responseData = array(
                "avatar" => $avatarName
            );

        }
        else{
            $this->responseCode = Utility::RESPONSE_CODE_INVALID_REQUEST;
            $this->responseMessage = "Unathenticated User";

            $this->responseData = array();
        }

        return Utility::getResponseData($this->responseCode, $this->responseMessage, $this->responseData);
    }



    public function delete(Request $request)
    {

        $params = Utility::getRequestData($request);
        Utility::sanitize($params->data);
        if(Utility::hasAccess($params->data["user"])) {
            $get= new Artists();
            $delete = $get->deleteArtist($params->data["uid"]);

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

    private function setupResponseData($artists){
        $total_row = count($artists);
        foreach ($artists as $artist){
            $artist->total_rows = $total_row;
            if($artist->cdn_status == Utility::CDN_STATUS_YES){
                $artist->real_avatar = Utility::CDN_IMAGE_URL . $artist->avatar;
            }
            else{
                $artist->real_avatar = Utility::TEMP_IMAGE_URL . $artist->avatar;
            }
            $artist->albums;

            foreach ($artist->albums as $aab){
                if($aab->cdn_status == Utility::CDN_STATUS_YES){
                    $aab->real_cover = Utility::CDN_IMAGE_URL . $aab->album_cover;

                }
                else{
                    $aab->real_cover = Utility::TEMP_IMAGE_URL . $aab->album_cover;
                }
            }

            $artist->songs;

            foreach ($artist->songs as $aas){
                if($aas->cdn_status == Utility::CDN_STATUS_YES){
                    $aas->real_file = Utility::CDN_AUDIO_URL . $aas->filename;
                }
                else{
                    $aas->real_file = Utility::TEMP_AUDIO_URL . $aas->filename;
                }

                $aas->album;
                if($aas->album->cdn_status == Utility::CDN_STATUS_YES){
                    $aas->album->real_cover = Utility::CDN_IMAGE_URL . $aas->album->album_cover;

                }
                else{
                    $aas->album->real_cover = Utility::TEMP_IMAGE_URL . $aas->album->album_cover;
                }
            }

            $artist->videos;

            foreach ($artist->videos as $aav){
                if($aav->cdn_status == Utility::CDN_STATUS_YES){
                    $aav->real_cover = Utility::CDN_IMAGE_URL . $aav->video_cover;

                }
                else{
                    $aav->real_cover = Utility::TEMP_IMAGE_URL . $aav->video_cover;
                }
            }

        }
    }



}
