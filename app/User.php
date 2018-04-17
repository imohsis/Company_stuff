<?php

namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;

use App\Helpers\Utility;
use Illuminate\Database\Eloquent\Model;
use Auth;

class User extends Authenticatable
{
    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function albums(){
        return $this->hasMany('App\Models\Albums', 'artist_uid', 'uid');
    }

    public function songs(){
        return $this->hasMany('App\Models\Music', 'artist_uid', 'uid');
    }

    public function videos(){
        return $this->hasMany('App\Models\Videos', 'artist_uid', 'uid');
    }

    public function playlist(){
        return $this->hasMany('App\Models\Playlist', 'user_id', 'uid');
    }

    public function getUsers($status = "", $start = 0, $limit = 20, $accountType){
        $status = ($status === "" ? Utility::STATUS_ACTIVE : $status);
        if($accountType == '1') {
            return self::where('status' , $status)
                ->where('acc_type' , $accountType)
                ->skip($start)->take($limit)
                ->orderBy('created_at', 'desc')->get();
        }else{

            return self::where('status', $status)
                ->whereIn('acc_type' , [Utility::ARTIST_ACCOUNT_TYPE, Utility::USER_ACCOUNT_TYPE,
                    Utility::SHOW_PROMOTER, Utility::MANAGER, Utility::DJ, Utility::PRODUCER])
                ->skip($start)->take($limit)
                ->orderBy('created_at', 'desc')->get();
        }
    }

    public function getUsersArtists($status = "", $start = 0, $limit = 20, $accountType){
        $status = ($status === "" ? Utility::STATUS_ACTIVE : $status);

            return self::where('status', $status)
                ->where('acc_type' , Utility::ARTIST_ACCOUNT_TYPE)
                ->skip($start)->take($limit)
                ->orderBy('created_at', 'desc')->get();

    }

    public function getMassiveUsers($userId = [], $start = 0, $limit = 20){
        $status = Utility::STATUS_ACTIVE;
        return self::whereIn('uid' , $userId)->where('status' , $status)
            ->skip($start)->take($limit)
            ->orderBy('created_at', 'desc')->get();
    }

    public function getSingleUser($slug){
        $status = Utility::STATUS_ACTIVE;
            return self::where('status', $status)
                ->where('slug' , $slug)
                ->orderBy('created_at', 'desc')->get();

    }

    //GET ONLY ONE RECORD BY UID AND DOES NOT NEED A FOR LOOP TO EXTRACT DATA
    public function getSingleUserByUid($uid){
        $status = Utility::STATUS_ACTIVE;
        return self::where('uid' , $uid)
            ->where('status', $status)
            ->orderBy('created_at', 'desc')->first();

    }

    public function getRecentUsers($status = "",$start = 0, $limit = 20,$accountType){
        $status = ($status === "" ? Utility::STATUS_ACTIVE : $status);
        if($accountType == '') {
            return self::where('status', $status)
                ->skip($start)->take($limit)
                ->orderBy('created_at', 'desc')->get();
        }else{
            return self::where('status' , $status)
                ->where('acc_type' , $accountType)
                ->skip($start)->take($limit)
                ->orderBy('created_at', 'desc')->get();
        }
    }


    public  function createUser( $username, $password, $email, $fullName, $accountType, $fileName,$rememberToken){

        $uid = Utility::generateUID($this->table);
        $slug = Utility::generateSlug($fullName, $this->table);
        //print_r($slug); exit;
        $data = array(
            'uid' => $uid,
            'username' => $username,
            'password' => $password,
            'email' => $email,
            'avatar' => $fileName,
            'full_name' => $fullName,
            'acc_type' => $accountType,
            'remember_token' => $rememberToken,
            'login_type' => Utility::AFROBEATLogin,
            'status' => Utility::STATUS_ACTIVE,
            'acc_verification' => Utility::STATUS_ACTIVE,
            'slug' => $slug

        );
        return self::create($data);
    }

    public  function socialSignup( $username, $password, $fullName, $accountType,
                                   $rememberToken,$loginType,$id){

        $uid = Utility::generateUID($this->table);
        $slug = Utility::generateSlug($fullName, $this->table);
        //print_r($slug); exit;
        $data = array(
            'uid' => $uid,
            'username' => $username,
            'password' => $password,
            'full_name' => $fullName,
            'acc_type' => $accountType,
            'remember_token' => $rememberToken,
            'status' => Utility::STATUS_ACTIVE,
            'acc_verification' => Utility::STATUS_ACTIVE,
            'login_type' => $loginType,
            'social_id' => $id,
            'slug' => $slug

        );
        return self::create($data);
    }

    public function generateToken(){
        $token = Utility::genRememberToken($this->table);
        return $token;
    }

    public  function createTempUser( $username, $password, $email, $fullName, $accountType,$rememberToken){

        $uid = Utility::generateUID($this->table);
        $slug = Utility::generateSlug($fullName, $this->table);
        //print_r($slug); exit;
        $data = array(
            'uid' => $uid,
            'username' => $username,
            'password' => $password,
            'email' => $email,
            'full_name' => $fullName,
            'acc_type' => $accountType,
            'login_type' => Utility::AFROBEATLogin,
            'activation_code' => $rememberToken,
            'remember_token' => $rememberToken,
            'status' => Utility::STATUS_ACTIVE,
            'acc_verification' => Utility::STATUS_INACTIVE,
            'slug' => $slug

        );
        return self::create($data);
    }

    public function editUser($email, $fullName,$username){
        $data = array(
            'email' => $email,
            'username' => $username,
            'full_name' => $fullName
        );
        return self::where("uid" , Auth::user()->uid)
            ->update($data);
    }

    public function editPassword($userUid, $password){
        $data = array(
            'password' => $password
        );
        return self::where("uid" , $userUid)
            ->update($data);
    }

    public function editAvatar($uid,  $coverImg){
        $data = array(
            'avatar' => $coverImg

        );
        return self::where("uid" , $uid)
            ->update($data);
    }


    public function editName($uid,  $artist_name){
        $data = array(
            'full_name' => $artist_name

        );
        return self::where("uid" , $uid)
            ->update($data);
    }

    public function deleteUser($uid){

        return self::where('uid' , $uid)
            ->update(['status' => Utility::STATUS_DELETED] );
    }

    public function activateUser($token){
        $data = array(
            'acc_verification' => Utility::STATUS_ACTIVE

        );
        return self::where("remember_token" , $token)
            ->update($data);
    }

    public function checkToken($token){
        return self::where("activation_code" , $token)
            ->where("status",Utility::STATUS_ACTIVE)
            ->get();
    }

    public function follow($userId = []){
        return self::whereIn("uid" , $userId)
            ->where("status",Utility::STATUS_ACTIVE)
            ->get();
    }

    public function checkUser($username){
        return self::where("username" , $username)
            ->where("status",Utility::STATUS_ACTIVE)
            ->get();
    }

    public function checkPassword($password){
        return self::where("uid" , Auth::user()->uid)
            ->where("password",$password)
            ->where("status",Utility::STATUS_ACTIVE)
            ->get();
    }

    public function checkUserEmail($email){
        return self::where("email" , $email)
            ->where("status",Utility::STATUS_ACTIVE)
            ->get();
    }

    public function checkUserLoginTypeUsername($username){
        return self::where("username" , $username)
            ->get();
    }

    public function checkUserLoginTypeEmail($email,$loginType){
        return self::where("email" , $email)
            ->where("login_type",$loginType)
            ->where("status",Utility::STATUS_ACTIVE)
            ->first();
    }

    public function checkSocialId($id,$loginType){
        return self::where("social_id" , $id)
            ->where("login_type",$loginType)
            ->where("status",Utility::STATUS_ACTIVE)
            ->get();
    }

    public function hasPushedToCDN($uid)
    {
        return Utility::updateCDNStatus($this->table, $uid, Utility::CDN_STATUS_YES);
    }

}
