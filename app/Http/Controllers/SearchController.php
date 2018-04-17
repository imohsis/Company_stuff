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
use App\Models\Search;
use App\Models\Artists;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Http\Request;
use App\Helpers\Utility;


class SearchController extends Controller
{
    private $responseCode, $responseMessage, $responseData;

    public function getSearch(Request $request)
    {
        $params = Utility::getRequestData($request);
        Utility::sanitize($params->data);
        //print_r($params->data);

        $model   = new Search();
        $keyword = $params->data['keyword'];
        $start   = $params->data['start'];
        $limit   = $params->data['limit'];

        if(!empty($keyword)) 
        {
            $data = $model->getSearch($keyword, $start, $limit);
            if(!empty($data)) 
            {
                $this->responseCode    = "200";
                $this->responseMessage = "Success";
                $this->responseData    = array("result" => $data);
            }
            else
            {
                $this->responseCode    = Utility::RESPONSE_CODE_INVALID_REQUEST;
                $this->responseMessage = "No match found!!!";
                $this->responseData    = "";
            }
        } 
        else 
        {
            $this->responseCode    = Utility::RESPONSE_CODE_INVALID_REQUEST;
            $this->responseMessage = "Search something...";
            $this->responseData    = '';
        }

        return Utility::getResponseData($this->responseCode, $this->responseMessage, $this->responseData);
    }

}
