<?php

namespace App\Http\Controllers;

use App\Http\Controllers;
use App\Helpers\FTP;
use App\Helpers\Utility;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\view;
use Monolog\Handler\Curl\Util;
use phpDocumentor\Reflection\Types\Null_;
use Validator;
use DB;
use App\User;
use App\Models\NewsSection;
use App\Models\News;
use App\Models\NewsCatg;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Http\Request;
use Auth;
use Input;

class NewsSectionController extends Controller
{
    //
    private $responseCode, $responseMessage, $responseData;

    public function create(Request $request){

        $jwt = $request->get('user');
        Utility::sanitize($jwt);
        $news_uid = $request->get('news_uid');
        Utility::sanitize($news_uid);
        $title = $request->get('title');
        Utility::sanitize($title);
        $content = $request->get('content');
        Utility::sanitize($content);
        $link = $request->get('link');
        $embed = $request->get('embed');

        $news = new NewsSection();

        if(Utility::hasAccess($jwt)) {
            $files = $request->file('images');
            //return $files;
            $temp_images = [];
            $cdn_images = [];
            $real_images = [];
            //print_r($files);

            if($request->file('images') != ''){
            foreach($files as $file){
                //return$file;
                $file_name = time() . "_" . Utility::generateUID(null, 10) . "." . $file->getClientOriginalExtension();
                $srcFile = Utility::getTempImagesPath() . $file_name;
                $real_images[] = $file_name;
                $file->move(
                    Utility::getTempImagesPath(), $file_name
                );
                if(Utility::moveToCDN($srcFile , $file_name, Utility::CDN_IMAGE)){
                    //Update CDN STATUS in db table
                    array_push($cdn_images,$file_name);
                    $real_images[] =  Utility::CDN_IMAGE_URL . $file_name;
                }
                else{

                    $temp_images[] = $file_name;
                    $real_images[] =   Utility::TEMP_IMAGE_URL . $file_name;
                    //UPLOAD TO CDN FAILED OR FTP COULD NOT CONNECT
                    //CRON WOULD HANDLE FAILED UPLOADS
                }
            }
                }
            //print_r(json_encode($cdn_images)); die();
            $create = $news->createNewsSection( $request->get('news_uid'),
                $request->get('title'),$request->get('content'), json_encode($cdn_images),
                json_encode($temp_images),$link, $embed );

            $create->real_images =   json_encode($real_images);

            $this->responseCode = Utility::RESPONSE_CODE_CREATED;
            $this->responseMessage = "Success";
            $this->responseData = array(
                "news" =>$create
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

        $jwt = $request->get('user');
        Utility::sanitize($jwt);
        $news_uid = $request->get('news_uid');
        Utility::sanitize($news_uid);
        $section_id = $request->get('section_id');
        Utility::sanitize($section_id);
        $title = $request->get('title');
        Utility::sanitize($title);
        $content = $request->get('content');
        Utility::sanitize($content);
        $link = $request->get('link');
        Utility::sanitize($link);
        $embed = $request->get('embed');
        //Utility::sanitize($embed);

        if(Utility::hasAccess($jwt)) {

            $news = new NewsSection();
            $create = $news->updateNewsSection( $request->get('section_id'), $request->get('news_uid'),
                $request->get('title'),$request->get('content'), $link, $embed);

            //ADD NEW IMAGE PATH URL TO RESPONSE


            $this->responseCode = Utility::RESPONSE_CODE_CREATED;
            $this->responseMessage = "Success";

        }
        else{
            $this->responseCode = Utility::RESPONSE_CODE_INVALID_REQUEST;
            $this->responseMessage = "Unauthenticated User";

        }

        return Utility::getResponseData($this->responseCode, $this->responseMessage);
    }

    public function addImages(Request $request){

        $news_uid = $request->get('news_uid');
        $section_id = $request->get('section_id');
        $jwt = $request->get('user');
        Utility::sanitize($jwt);
        Utility::sanitize($uid);
        Utility::sanitize($section_id);

        if(Utility::hasAccess($jwt)) {

            $files = $request->file('images');
            $temp_images = [];
            $cdn_images = [];
            $real_images = [];
            if($request->file('images') != ''){
            foreach($files as $file){
                $file_name = time() . "_" . Utility::generateUID(null, 10) . "." . $file->getClientOriginalExtension();
                $srcFile = Utility::getTempImagesPath() . $file_name;
                $real_images[] = $file_name;

                $file->move(
                    Utility::getTempImagesPath(), $file_name
                );
                if(Utility::moveToCDN($srcFile , $file_name, Utility::CDN_IMAGE)){
                    //Update CDN STATUS in db table
                    array_push($cdn_images,$file_name);
                    $real_images[] =  Utility::CDN_IMAGE_URL . $file_name;
                }
                else{

                    $temp_images[] = $file_name;
                    $real_images[] =   Utility::TEMP_IMAGE_URL . $file_name;
                    //UPLOAD TO CDN FAILED OR FTP COULD NOT CONNECT
                    //CRON WOULD HANDLE FAILED UPLOADS
                }
            }
            }

            $news = new NewsSection();

            $get_section = $news->getSection($section_id,$news_uid);
            //DECODE THE JSON IMAGES TO PHP ARRAY
            $existing_images = json_decode($get_section->images,TRUE);
            $existing_local_images = json_decode($get_section->local_images,TRUE);
            $saved_image = [];
            $not_saved_image = [];
            // PUSH NEW IMAGES IF CDN IMAGES IS NOT EMPTY

            if(count($existing_images) > 0) {
                foreach ($existing_images as $img) {
                    $saved_image[] = $img;

                }
                //PUSH NEW UPLOADED IMAGES TO EXISTING DECODED IMAGES IF ANY
                foreach ($cdn_images as $img) {
                    $saved_image[] = $img;

                }
            }
            // PUSH NEW IMAGES IF LOCAL IMAGES IS NOT EMPTY
            if(count($existing_local_images) > 0) {
                foreach ($existing_local_images as $img) {
                    $not_saved_image[] = $img;
                }
                foreach ($temp_images as $img) {
                    $not_saved_image[] = $img;
                }
            }

            //CONVERT IMAGES BACK TO JSON OBJECT FOR DATABASE STORAGE
            $images = json_encode($saved_image);
            $local_images = json_encode($not_saved_image);

            $create = $news->updateImage( $request->get('section_id'), $request->get('news_uid'),
                $images, $local_images);

            //ADD NEW IMAGE PATH URL TO RESPONSE

            $this->responseCode = Utility::RESPONSE_CODE_CREATED;
            $this->responseMessage = "Success";
            $this->responseData = array(
                "images" => $real_images
            );

        }
        else{
            $this->responseCode = Utility::RESPONSE_CODE_INVALID_REQUEST;
            $this->responseMessage = "Unauthenticated User";

            $this->responseData = array();
        }

        return Utility::getResponseData($this->responseCode, $this->responseMessage, $this->responseData);

    }

    //DELETE SECTION PHOTO
    public function deletePhoto(Request $request)
    {

        $params = Utility::getRequestData($request); //convert json request to array object data
        Utility::sanitize($params->data); //a must sanitization
        if (Utility::hasAccess($params->data["user"])) {

        $get = new NewsSection();
        $get_section = $get->getSection($params->data["section_id"], $params->data["news_uid"]);
            $get_sections = $get->getSections($params->data["section_id"], $params->data["news_uid"]);
        $photo = $params->data["image"];
        $existing_images = [];
        $existing_local_images = [];
            $array_image = '';
            $array_local_image = '';

            foreach($get_sections as $sec){
                $array_image = $sec->images;
                $array_local_image = $sec->local_images;
            }
            //print_r(json_decode($array_image));     exit;
        $existing_images1 = json_decode($array_image, TRUE);
        $existing_local_images1 = json_decode($array_local_image, TRUE);
            //print_r($existing_images1 );     exit;

        if(is_array($existing_images1)){
            foreach ($existing_images1 as $image) {
                $existing_images[] = $image;
            }
        }
            //print_r($existing_images1 );     exit;
            if(is_array($existing_images1)) {
                foreach ($existing_local_images1 as $image) {
                    $existing_local_images[] = $image;
                }
            }
        if (count($existing_images) > 0) {
            foreach ($existing_images as $key => $value) {
                if ($value == $photo) {
                    unset($existing_images[$key]);
                }
            }
        }
        if (count($existing_local_images) > 0) {
            foreach ($existing_local_images as $key => $value) {
                if ($value == $photo) {
                    unset($existing_local_images[$key]);
                }
            }
        }

        //CONVERT IMAGES BACK TO JSON OBJECT FOR DATABASE STORAGE
        $images = json_encode($existing_images);
        $local_images = json_encode($existing_local_images);

        $create = $get->updateImage($params->data['section_id'], $params->data['news_uid'],
            $images, $local_images);

        if ($create) {

            $this->responseCode = Utility::RESPONSE_CODE_NO_CONTENT;
            $this->responseMessage = "Deleted";
        } else {
            $this->responseCode = Utility::RESPONSE_CODE_NOT_FOUND;
            $this->responseMessage = "Not Found".$get_sections;
        }

    }else{
            $this->responseCode = Utility::RESPONSE_CODE_INVALID_REQUEST;
            $this->responseMessage = "unauthenticated User";
        }

        return Utility::getResponseData($this->responseCode, $this->responseMessage);
    }



}
