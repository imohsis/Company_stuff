<?php

namespace App\Http\Controllers;

use App\Helpers\Utility;
use App\Http\Controllers;
use Illuminate\Support\Facades\view;
use Validator;
use Input;
use DB;
use Hash;
use Auth;
use App\Helpers\JWT;
use App\User;
use App\models\UsersFollow;
use App\models\Music;
use App\models\Videos;
use App\models\LiveFeed;
use App\Models\Users;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Http\Request;
use Mail;

class UsersController extends Controller
{
    private $responseCode, $responseMessage, $responseData;

    public function sessionVerify(Request $request)
    {

        $params = Utility::getRequestData($request);
        Utility::sanitize($params->data);
        if(Utility::hasAccess($params->data["user"])) {

            $this->responseCode = Utility::RESPONSE_CODE_OK;
            $this->responseMessage = "Success";
            $this->responseData = array();

        }
        else{
            $this->responseCode = Utility::RESPONSE_CODE_SERVER_ERROR;
            $this->responseMessage = "Unauthenticated User";

            $this->responseData = array();
        }

        return Utility::getResponseData($this->responseCode, $this->responseMessage, $this->responseData);
       
    }

    public function getUsers(Request $request)
    {

        $params = Utility::getRequestData($request);
        Utility::sanitize($params->data);

        $user = new User();
        if (!array_key_exists('status', $params->data)) {
            $params->data["status"] = "";
        }
        if (!array_key_exists('acc_type', $params->data)) {
            $params->data["acc_type"] = "";
        }
        //print_r($params->data["acc_type"]); exit();
        $users = $user->getUsers($params->data["status"],
            $params->data["start"],$params->data["limit"], $params->data["acc_type"]);

        Utility::formatDateFields($users);
        $this->setupResponseData($users);
        $this->responseCode = Utility::RESPONSE_CODE_OK;
        $this->responseMessage = "Success";
        $this->responseData = array(
            "users" =>$users
        );


        return Utility::getResponseData($this->responseCode, $this->responseMessage, $this->responseData);
    }


    public function getUsersArtist(Request $request)
    {

        $params = Utility::getRequestData($request);
        Utility::sanitize($params->data);

        $user = new User();
        if (!array_key_exists('status', $params->data)) {
            $params->data["status"] = "";
        }
        if (!array_key_exists('acc_type', $params->data)) {
            $params->data["acc_type"] = "";
        }
        //print_r($params->data["acc_type"]); exit();
        $users = $user->getUsersArtists($params->data["status"],
            $params->data["start"],$params->data["limit"], $params->data["acc_type"]);

        Utility::formatDateFields($users);
        $this->setupResponseData($users);
        $this->responseCode = Utility::RESPONSE_CODE_OK;
        $this->responseMessage = "Success";
        $this->responseData = array(
            "users" =>$users
        );


        return Utility::getResponseData($this->responseCode, $this->responseMessage, $this->responseData);
    }

    public function getSingleUser(Request $request)
    {

        $params = Utility::getRequestData($request);
        Utility::sanitize($params->data);

        $user = new User();
        $slug = ($params->data["slug"] == "") ? Auth::user()->slug : $params->data["slug"];
        $users = $user->getSingleUser($slug);

        Utility::formatDateFields($users);
        $this->setupResponseData($users);
        $this->responseCode = Utility::RESPONSE_CODE_OK;
        $this->responseMessage = "Success";
        $this->responseData = array(
            "users" =>$users
        );


        return Utility::getResponseData($this->responseCode, $this->responseMessage, $this->responseData);
    }

    public function getAuthUser(Request $request)
    {

        $params = Utility::getRequestData($request);
        Utility::sanitize($params->data);
        $jwt = $params->data["user"];

        if(Utility::hasAccess($jwt)) {
            $user = new User();
            $slug = ($params->data["slug"] == "") ? Auth::user()->slug : $params->data["slug"];
            $users = $user->getSingleUser($slug);

            Utility::formatDateFields($users);
            $this->setupResponseData($users);
            $this->responseCode = Utility::RESPONSE_CODE_OK;
            $this->responseMessage = "Success";
            $this->responseData = array(
                "users" => $users
            );
            return Utility::getResponseData($this->responseCode, $this->responseMessage, $this->responseData);
        }else{
            $this->responseCode = Utility::RESPONSE_CODE_INVALID_REQUEST;
            $this->responseMessage = "Unauthenticated user";
            $this->responseData = array(
                "users" => []
            );
        return Utility::getResponseData($this->responseCode, $this->responseMessage, $this->responseData);
        }
    }

    public function getRecentUsers(Request $request)
    {

        $params = Utility::getRequestData($request);
        Utility::sanitize($params->data);

        $user = new User();
        if (!array_key_exists('status', $params->data)) {
            $params->data["status"] = "";
        }
        if (!array_key_exists('acc_type', $params->data)) {
            $params->data["acc_type"] = "";
        }

        $users = $user->getRecentUsers($params->data["status"],
            $params->data["start"],$params->data["limit"],$params->data["acc_type"]);

        Utility::formatDateFields($users);
        $this->setupResponseData($users);
        $this->responseCode = Utility::RESPONSE_CODE_OK;
        $this->responseMessage = "Success";
        $this->responseData = array(
            "users" =>$users
        );


        return Utility::getResponseData($this->responseCode, $this->responseMessage, $this->responseData);
    }

    public function login(Request $request)
    {
        $data = Utility::getRequestData($request);
        $username = $data->data['username'];
        $password = $data->data['password'];
        $login_type = $data->data['login_type'];
        $credentials = array('username' => $username, 'password' => $password,'login_type' => $login_type,
            'status' => Utility::STATUS_ACTIVE,'acc_verification' => Utility::STATUS_ACTIVE);
        if (Auth::attempt($credentials)) {
            if (Auth::user()->acc_type == 1 or Auth::user()->acc_type == 10) {

            $token = [
                "username" => Auth::user()->username,
                "email" => Auth::user()->email,
                "full_name" => Auth::user()->full_name,
                "acc_type" => Auth::user()->acc_type
            ];

            $token = JWT::encode($token);
            //echo $jwt;
            Session()->put('token', $token);
            $_SESSION['tokens'] = $token;


            $response = ([
                'signature' => $token,
                'acc_type' => Auth::user()->acc_type,
                'tokens' => $token
            ]);
        }else{
                $response = [];

                return Utility::getResponseData(Utility::RESPONSE_CODE_NOT_FOUND, "Not an Administrator", $response);

            }
            return Utility::getResponseData(Utility::RESPONSE_CODE_OK, "Success", $response);

        }

        $response =[
            'username' => $username,
            'password' => $password
        ];

        return Utility::getResponseData(Utility::RESPONSE_CODE_INVALID_REQUEST, "An error occurred", $response);
    }

    public function userLogin(Request $request)
    {
        $data = Utility::getRequestData($request);
        $username = $data->data['username'];
        $password = $data->data['password'];
        $password = ($password == '') ? 'DefaultPassword' : $password;
        $login_type = $data->data['login_type'];
        $credentials = array('username' => $username, 'password' => $password,'login_type' => $login_type,
            'status' => Utility::STATUS_ACTIVE,'acc_verification' => Utility::STATUS_ACTIVE);
        if (Auth::attempt($credentials)) {
            $token = [
                "username" => Auth::user()->username,
                "email" => Auth::user()->email,
                "full_name" => Auth::user()->full_name,
                "acc_type" => Auth::user()->acc_type
            ];

            $token = JWT::encode($token);
            //echo $jwt;
            Session()->put('token', $token);
            $_SESSION['tokens'] = $token;


            $response = ([
                'signature' => $token,
                'acc_type' => Auth::user()->acc_type,
                'tokens' => $token
            ]);
            return Utility::getResponseData(Utility::RESPONSE_CODE_OK, "Success", $response);

        }

        $response =[
            'username' => $username,
            'password' => $password,
            'login_type' => $login_type
        ];

        return Utility::getResponseData(Utility::RESPONSE_CODE_INVALID_REQUEST, "An error occurred", $response);
    }

    public function logout(Request $request){

        $params = Utility::getRequestData($request);
        if(Session()->get('token')== $params->data['signature']){
            Auth::logout();
        return Utility::getResponseData(Utility::RESPONSE_CODE_NO_CONTENT, "Success");
        }
        return Utility::getResponseData(Utility::RESPONSE_CODE_NOT_FOUND, "Failed");

    }

    public function profileDetails(Request $request){

        $data = Utility::getRequestData($request);
        //return $data->data; die();
        $token = $data->data['user'];
        $jwt_decode = JWT::decode($token);

//        $r = new stdClass();
//        $r->sessionToken = session()->get('token');
//        $r->jwt = $token;
        //return Session()->get('token') ;
       /* if(Utility::hasAccess($token)) {*/

        $admin = [
            "link1" => [
                'text' => 'Home',
                'icon' => 'home',
                'sref' => 'home'
            ],
            "link2" => [
                'text' => 'News',
                'icon' => 'newspaper-o',
                'sref' => 'newsDefault.news'
            ],
            "link3" => [
                'text' => 'Music',
                'icon' => 'music',
                'sref' => 'music.artist'
            ],
            "link4" => [
                'text' => 'Videos',
                'icon' => 'film',
                'sref' => 'videos.list'
            ],
            "link5" => [
                'text' => 'Live Feeds',
                'icon' => 'rss-square',
                'sref' => 'livefeed'
            ],
            "link6" => [
                'text' => 'Users',
                'icon' => 'rss-square',
                'sref' => 'users.users_list'
            ],

            "link7" => [
                'text' => 'Live Tv',
                'icon' => 'rss-square',
                'sref' => 'livetv'
            ]
        ];
        $users = [];
        $new_array = [];
        foreach($jwt_decode as $key=>$value){
            $new_array[$key] = $value;
        }
        $urls = ($jwt_decode->acc_type == Utility::ADMIN_ACCOUNT_TYPE or $jwt_decode->acc_type == Utility::MANAGEMENT) ? $admin : $users;
        $new_array['links'] = $urls;
            $data = $new_array; 
            return Utility::getResponseData(Utility::RESPONSE_CODE_OK, "Success", $data);

        /*} else {
            return Utility::getResponseData(Utility::RESPONSE_CODE_INVALID_REQUEST, "An error occurred");
        }*/
    }

    //SIGN UP METHOD
    public function signup(Request $request)
    {

        $usersTable = new User();

        $data = Utility::getRequestData($request);
        $username = $data->data['username'];
        $password = $data->data['password'];
        $email = $data->data['email'];
        $account_type = $data->data['acc_type'];
        $fullName = $data->data['full_name'];
        $token = $usersTable->generateToken();
        $hash_password = Hash::make($password);
        $link = 'http://www.afrobeat.com/account/activation/'.$token;
        $title = 'Afrobeat Account Activation';
        $content = 'Hello '.$username;

        $checkUser = $usersTable->checkUserLoginTypeUsername($username);
        $checkUserEmail = $usersTable->checkUserLoginTypeEmail($email,Utility::AFROBEATLogin);
        if(count($checkUser) > 0 || count($checkUserEmail) > 0 || $checkUserEmail->email != ''){
            $this->responseCode = Utility::RESPONSE_CODE_INVALID_REQUEST;
            $this->responseMessage = "Username/Email already exists";
            $this->responseData = array(
                "user" => $username
            );

        }else{
            $create = $usersTable->createTempUser($username,$hash_password,$email,$fullName, $account_type,$token);
            $this->responseCode = Utility::RESPONSE_CODE_CREATED;
            $this->responseMessage = "Success";
            $this->responseData = array(
                "user" => $create->full_name
            );

            Mail::send('emails.send', ['title' => $title, 'content' => $content, 'link_url' => $link],
                function ($message) use ($email,$fullName)
                {

                    $message->from('info@afrobeat.com', 'No Reply');

                    $message->to($email)->subject('Please confirm and verify your account');

                });


        }
        return Utility::getResponseData($this->responseCode, $this->responseMessage, $this->responseData);

    }

    //CREATE USER
    public function createUser(Request $request)
    {

        $jwt = $request->get('user');
        Utility::sanitize($jwt);
        $email = $request->get('email');
        Utility::sanitize($email);
        $fullName = $request->get('full_name');
        Utility::sanitize($fullName);
        $account_type = $request->get('acc_type');
        Utility::sanitize($account_type);
        $password = 'DefaultPassword';

        if(Utility::hasAccess($jwt)) {
            $username = str_replace(' ', '', $fullName);
            $token = csrf_token();
            $hash_password = Hash::make($password);

            if ($request->file('avatar') != '') {

            $file_name = time() . "_" . Utility::generateUID(null, 10) . "." . $request->file('avatar')->getClientOriginalExtension();
            $request->file('avatar')->move(
                Utility::getTempImagesPath(), $file_name
            );
        }else{
            $file_name = '';
            }

            $usersTable = new User();

            $checkUser = $usersTable->checkUserLoginTypeUsername($username);
            $checkUserEmail = $usersTable->checkUserLoginTypeEmail($email, Utility::AFROBEATLogin);
            $rand = mt_rand(100, 999);
            /*if (count($checkUser) > 0) {

                $username = $fullName . $rand;

            }*/
            $username2 = (count($checkUser) > 0) ? $username.$rand : $username;

            if (count($checkUserEmail) > 0) {

                $this->responseCode = Utility::RESPONSE_CODE_NOT_FOUND;
                $this->responseMessage = "User already exists";
                $this->responseData = array(
                    "user" => $username
                );
            }else{

            $create = $usersTable->createUser($username2, $hash_password, $email, $fullName,
                $account_type, $file_name, $token);

                if ($request->file('avatar') != '') {
                    $srcFile = Utility::getTempImagesPath() . $file_name;
                    if (Utility::moveToCDN($srcFile, $file_name, Utility::CDN_IMAGE)) {
                        //Update CDN STATUS in db table
                        $usersTable->hasPushedToCDN($create->uid);
                        $create->cover_img = Utility::CDN_IMAGE_URL . $file_name;
                    } else {
                        $create->cover_img = Utility::TEMP_IMAGE_URL . $file_name;
                        //UPLOAD TO CDN FAILED OR FTP COULD NOT CONNECT
                        //CRON WOULD HANDLE FAILED UPLOADS
                    }
                }

            $this->responseCode = Utility::RESPONSE_CODE_CREATED;
            $this->responseMessage = "Success";
            $this->responseData = array(
                "user" => $create->full_name
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

    //CREATE USER
    public function socialSignup(Request $request)
    {

        $params = Utility::getRequestData($request);

        $loginType = $params->data['login_type'];
        //$email = $params->data['email'];
        $id = $params->data['id'];
        $fullName = $params->data['full_name'];
        $account_type = $params->data['acc_type'];
        $password = 'DefaultPassword';

            $username = str_replace(' ', '', $fullName);
            $token = csrf_token();
            $hash_password = Hash::make($password);

            $usersTable = new User();

            $checkUser = $usersTable->checkUserLoginTypeUsername($username);
            //$checkUserEmail = $usersTable->checkUserLoginTypeEmail($email, Utility::FACEBOOKLogin);
            $checkSocialId = $usersTable->checkSocialId($id, Utility::FACEBOOKLogin);
           /* if (count($checkUser) > 0) {
                $rand = mt_rand(100, 999);
                $username = $fullName . $rand;

            }*/

            //
            if (count($checkSocialId) > 0 || count($checkUser) > 0) {
                $this->responseCode = Utility::RESPONSE_CODE_INVALID_REQUEST;
                $this->responseMessage = "User already exists";
                $this->responseData = array(
                    "user" => []
                );
            }else{

                $create = $usersTable->socialSignup($username, $hash_password, $fullName,
                    $account_type, $token,$loginType,$id);



                $this->responseCode = Utility::RESPONSE_CODE_CREATED;
                $this->responseMessage = "Success";
                $this->responseData = array(
                    "user" => $create->full_name
                );
            }


        return Utility::getResponseData($this->responseCode, $this->responseMessage, $this->responseData);


    }

    public function editArtistName(Request $request){

        $params = Utility::getRequestData($request);
        Utility::sanitize($params->data);
        if(Utility::hasAccess($params->data["user"])) {
            $artist = new User();
            $edit = $artist->editName( $params->data['uid'], $params->data['name']);

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

    public function editArtistAvatar(Request $request){

        $uid = $request->get('uid');
        $jwt = $request->get('user');
        $avatarName = time() . "_" . Utility::generateUID(null, 10) . "." . $request->file('avatar')->getClientOriginalExtension();
        Utility::sanitize($jwt);
        Utility::sanitize($uid);

        if(Utility::hasAccess($jwt)) {

            $request->file('avatar')->move(
                Utility::getTempImagesPath(), $avatarName
            );



            $artist = new User();
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
            $this->responseMessage = "Unauthenticated User";

            $this->responseData = array();
        }

        return Utility::getResponseData($this->responseCode, $this->responseMessage, $this->responseData);
    }



    //EDIT USER
    public function editUser(Request $request)
    {

        $params = Utility::getRequestData($request);

        $jwt = $params->data['user'];
        $fullName = $params->data['full_name'];
        $email = $params->data['email'];
        $username = $params->data['username'];

        if(Utility::hasAccess($jwt)) {

            $token = csrf_token();

            $usersTable = new User();

            $checkUser = $usersTable->checkUser($username);
            if (count($checkUser) > 0 && $checkUser[0]->username !== Auth::user()->username) {
                $this->responseCode = Utility::RESPONSE_CODE_INVALID_REQUEST;
                $this->responseMessage = "Username already exists";
                $this->responseData = array(
                    "user" => $username
                );


            }else{
            $create = $usersTable->editUser($email, $fullName,$username);

            $this->responseCode = Utility::RESPONSE_CODE_CREATED;
            $this->responseMessage = "Success";
            $this->responseData = array(
                "user" => 'success'
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

    //SIGN UP METHOD
    public function editPassword(Request $request)
    {

        $data = Utility::getRequestData($request);
        $user = $data->data['user'];
        $old_password = Hash::make($data->data['old_password']);
        $password = $data->data['password'];
        $confirm_password = $data->data['confirm_password'];
        $usersTable = new User();

        if(Utility::hasAccess($user)) {
            $checkUser = $usersTable->checkPassword($old_password);
            if (/*count($checkUser) > 0*/
            Hash::check($data->data['old_password'], Auth::user()->password)
            ) {
                if ($password == $confirm_password) {
                    $hash_password = Hash::make($password);
                    $create = $usersTable->editPassword(Auth::user()->uid, $hash_password);
                    $this->responseCode = Utility::RESPONSE_CODE_CREATED;
                    $this->responseMessage = "Success";
                    $this->responseData = array(
                        "password" => []
                    );
                } else {

                    $this->responseCode = Utility::RESPONSE_CODE_NOT_FOUND;
                    $this->responseMessage = "Passwords do not match";
                    $this->responseData = array(
                        "password" => []
                    );

                }

            } else {

                $this->responseCode = Utility::RESPONSE_CODE_NOT_FOUND;
                $this->responseMessage = 'Incorrect Password';
                $this->responseData = array(
                    "password" => []
                );


            }
        } else{
            $this->responseCode = Utility::RESPONSE_CODE_INVALID_REQUEST;
            $this->responseMessage = 'Unauthenticated User';
            $this->responseData = array(
                "password" => []
            );
        }
        return Utility::getResponseData($this->responseCode, $this->responseMessage, $this->responseData);

    }


    public function editUserAvatar(Request $request){

        $uid = Auth::user()->uid;
        $jwt = $request->get('user');
        $coverImg = time() . "_" . Utility::generateUID(null, 10) . "." . $request->file('avatar')->getClientOriginalExtension();
        Utility::sanitize($jwt);
        Utility::sanitize($uid);

        if(Utility::hasAccess($jwt)) {

            $request->file('avatar')->move(
                Utility::getTempImagesPath(), $coverImg
            );



            $user = new User();
            $edit = $user->editAvatar( $uid,
                $coverImg);

            $srcFile = Utility::getTempImagesPath() . $coverImg;
            //echo $srcFile . "\n";

            if(Utility::moveToCDN($srcFile , $coverImg, Utility::CDN_IMAGE)){
                //Update CDN STATUS in db table
                $user->hasPushedToCDN($uid);
                $coverImg = Utility::CDN_IMAGE_URL . $coverImg;
            }
            else{
                $coverImg = Utility::TEMP_IMAGE_URL . $coverImg;

            }

            $this->responseCode = Utility::RESPONSE_CODE_CREATED;
            $this->responseMessage = "Success";
            $this->responseData = array(
                "avatar" => $coverImg
            );

        }
        else{
            $this->responseCode = Utility::RESPONSE_CODE_INVALID_REQUEST;
            $this->responseMessage = "Unauthenticated User";

            $this->responseData = array();
        }

        return Utility::getResponseData($this->responseCode, $this->responseMessage, $this->responseData);

    }

    public function deleteUser(Request $request)
    {

        $params = Utility::getRequestData($request);
        Utility::sanitize($params->data);
        if(Utility::hasAccess($params->data["user"])) {
            $get= new User();
            $users = $get->deleteUser($params->data["user_uid"]);

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


    public function userActivation(Request $request)
    {

        $data = Utility::getRequestData($request);
        $token = $data->data['token'];
        $user = new User();
        $checkToken = $user->checkToken($token);
        if(count($checkToken) >0){
            $activate = $user->activateUser($token);

            $this->responseCode = Utility::RESPONSE_CODE_CREATED;
            $this->responseMessage = "Success";
            $this->responseData = array(
                "user" => $checkToken
            );

        }else{

            $this->responseCode = Utility::RESPONSE_CODE_INVALID_REQUEST;
            $this->responseMessage = "Token does not exist";
            $this->responseData = array(
                "user" => $token
            );

        }

        return Utility::getResponseData($this->responseCode, $this->responseMessage, $this->responseData);

    }

    private function setupResponseData($artists){
        $total_row = count($artists);
        foreach ($artists as $artist) {
            $artist->total_rows = $total_row;
            if ($artist->avatar != '') {

            if ($artist->cdn_status == Utility::CDN_STATUS_YES) {
                $artist->real_avatar = Utility::CDN_IMAGE_URL . $artist->avatar;
            } else {
                $artist->real_avatar = Utility::TEMP_IMAGE_URL . $artist->avatar;
            }

        }else{
            $artist->real_avatar = Utility::DEFAULT_AVATAR;
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
                if($aas->album_id != 0 or $aas->album_id != "") {
                    $aas->album;
                    if ($aas->album->cdn_status == Utility::CDN_STATUS_YES) {
                        $aas->album->real_cover = Utility::CDN_IMAGE_URL . $aas->album->album_cover;

                    } else {
                        $aas->album->real_cover = Utility::TEMP_IMAGE_URL . $aas->album->album_cover;
                    }
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

            if(Auth::check()){
            $artist->playlist;
            }

            /*$follow = new UsersFollow();
            $user = new User();
            $music = new Music();
            $usersFollowed = $follow->getUserFollowed(Auth::user()->uid);
            $followingUser = $follow->getFollowingUser(Auth::user()->uid);
            $youFollowedUid = [];
            $followedYouUid = [];
            $usersPostUid = [];
            foreach($usersFollowed as $get){
                $youFollowedUid[] = $get->follower_id;
                $usersPostUid[] = $get->follower_id;
            }
            foreach($followingUser as $get){
                $followedYouUid[] = $get->user_id;
            }

            $usersPostUid[] = Auth::user()->uid;
            $peopleFollowingYou = $user->getMassiveUsers($followedYouUid);
            $peopleYouFollow = $user->getMassiveUsers($youFollowedUid);
            $musicPosts = $music->getMassiveUserSongs($usersPostUid);
            $realUser = $user->getSingleUser(Auth::user()->uid);
            $this->setupMusicResponseData($musicPosts);
            $artist->following_you = $peopleFollowingYou;
            $artist->you_followed = $peopleYouFollow;
            $artist->all_post = $musicPosts;

            if($realUser[0]->feed_perm == Utility::LIVE_FEED_PERMISSION){
                $feed = new LiveFeed();
                $liveFeeds = $feed->getUniquePosterLiveFeed(Auth::user()->uid);
                $this->alignLiveFeedResponseData($liveFeeds);
                $artist->live_feeds = $liveFeeds;
            }*/

        }
    }

    private function setupMusicResponseData($data){


        foreach ($data as $d){

            if($d->cdn_status == Utility::CDN_STATUS_YES){
                $d->real_file = Utility::CDN_AUDIO_URL . $d->filename;
            }
            else{
                $d->real_file = Utility::TEMP_AUDIO_URL . $d->filename;
            }
            $d->artist;
            if($d->artist->cdn_status == Utility::CDN_STATUS_YES){
                $d->artist->real_avatar = Utility::CDN_IMAGE_URL . $d->artist->avatar;

            }
            else{
                $d->artist->real_avatar = Utility::TEMP_IMAGE_URL . $d->artist->avatar;
            }
            $d->album;
            if($d->album->cdn_status == Utility::CDN_STATUS_YES){
                $d->album->real_cover = Utility::CDN_IMAGE_URL . $d->album->album_cover;

            }
            else{
                $d->album->real_cover = Utility::TEMP_IMAGE_URL . $d->album->album_cover;
            }


        }
    }

    private function  alignLiveFeedResponseData($liveFeed){
        foreach($liveFeed as $new){
            if($new->thumbnail_cdn_status == Utility::CDN_STATUS_YES){
                $new->real_thumbnail = Utility::CDN_IMAGE_URL . $new->thumbnail;
            }
            else{
                $new->real_thumbnail = Utility::TEMP_IMAGE_URL . $new->thumbnail;
            }

            if($new->feed_type == 'image'){

                if($new->feed_attachment_cdn_status == Utility::CDN_STATUS_YES){
                    $new->real_attachment = Utility::CDN_IMAGE_URL . $new->feed_attachment;
                }
                else{
                    $new->real_attachment = Utility::TEMP_IMAGE_URL . $new->feed_attachment;
                }

            }
            if($new->user_type == '2'){
                $new->poster_name = $new->poster_artist->artist_name;
            }else{
                $new->poster_name = $new->poster_user->full_name;
            }


            unset($new->status);
            unset($new->created_at);
            unset($new->updated_at);
            unset($new->cdn_status);
            unset($new->thumbnail);

        }
    }

    public function showLifetime (){
        print_r(Config::get(session.lifetime));
    }

}
