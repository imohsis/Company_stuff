<?php

namespace App\Models;

use App\Helpers\Utility;
use Illuminate\Database\Eloquent\Model;
use Auth;

class UsersFollow extends Model
{
    //
	protected $table = 'afrobt_users_follow';
    protected $guarded = [];
    public $timestamps = false;

    public function getFollowingUser($userUid)
    {
        return self::where("follower_id", $userUid)
            ->where("status", Utility::STATUS_ACTIVE)
            ->get();
    }

    public function getUserFollowed($followUid){
        return self::where("user_id" , $followUid)
            ->where("status",Utility::STATUS_ACTIVE)
            ->get();
    }

    public function followUser($userUid, $followerUid){
        $data = array(
            'user_id' => $userUid,
            'follower_id' => $followerUid,
            'status' => Utility::STATUS_ACTIVE,
            'block_status' => Utility::BLOCK_FOLLOWER_STATUS_INACTIVE
        );
        return self::create($data);
    }

    public function unfollowUser($userUid, $followerUid){
        $data = array(
            'status' => Utility::UNFOLLOW_STATUS
        );
        return self::where("user_id" , $userUid)->where("follower_id" , $followerUid)
            ->update($data);
    }

    public function blockFollower($userUid, $followerUid){
        $data = array(
            'block_status' => Utility::BLOCK_FOLLOWER_STATUS_ACTIVE
        );
        return self::where("user_id" , $followerUid)->where("follower_id" , $userUid)
                    ->where("status" , Utility::STATUS_ACTIVE)
            ->update($data);
    }

    public function unblockFollower($userUid, $followerUid){
        $data = array(
            'block_status' => Utility::BLOCK_FOLLOWER_STATUS_INACTIVE
        );
        return self::where("user_id" , $followerUid)->where("follower_id" , $userUid)
            ->update($data);
    }

    public function checkFollower($userUid,$followerUid){
        return self::where("user_id" , $userUid)
            ->where("follower_id" , $followerUid)
            ->where("status",Utility::STATUS_ACTIVE)
            ->get();
    }

}
