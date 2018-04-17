<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', function () {
    return view('index');
});

//BEGIN OF USERS ACCOUNT ROUTE

Route::post('login',array('as'=>'login','uses'=>'UsersController@login'));
Route::post('login/user',array('as'=>'user_login','uses'=>'UsersController@userLogin'));
Route::post('logout',array('as'=>'logout','uses'=>'UsersController@logout'));
Route::post('admin',array('as'=>'profile','uses'=>'UsersController@profileDetails'));
Route::post('signup',array('as'=>'signup','uses'=>'UsersController@signup'));
Route::post('signup/social',array('as'=>'socialSignup','uses'=>'UsersController@socialSignup'));
Route::post('account/activation',array('as'=>'activate_user','uses'=>'UsersController@userActivation'));
Route::post('user/create',array('as'=>'create_user','uses'=>'UsersController@createUser'));
Route::post('user/edit',array('as'=>'edit_user','uses'=>'UsersController@editUser'));
Route::post('user/edit/password',array('as'=>'edit_password','uses'=>'UsersController@editPassword'));
Route::post('user/delete',array('as'=>'delete_user','uses'=>'UsersController@deleteUser'));
Route::post('user/edit/avatar',array('as'=>'edit_user_avatar','uses'=>'UsersController@editUserAvatar'));
Route::post('user/edit/artist/avatar',array('as'=>'edit_user_avatar','uses'=>'UsersController@editArtistAvatar'));
Route::post('user/edit/artist/name',array('as'=>'edit_user_avatar','uses'=>'UsersController@editArtistName'));
Route::post('users',array('as'=>'get_users','uses'=>'UsersController@getUsers'));
Route::post('users/artist',array('as'=>'get_users_artist','uses'=>'UsersController@getUsersArtist'));
Route::post('user',array('as'=>'get_user','uses'=>'UsersController@getSingleUser'));
Route::post('user/auth',array('as'=>'get_auth_user','uses'=>'UsersController@getAuthUser'));
Route::post('users/recent',array('as'=>'get_users_recent','uses'=>'UsersController@getRecentUsers'));
Route::post('session/verify',array('as'=>'session_verify','uses'=>'UsersController@sessionVerify'));

//END OF USERS ACCOUNT ROUTE
Route::post('album/cdn/test',
    array('uses'=>'AlbumsController@albumCDNTest'));

//SEARCH ROUTE BEGINS HERE
Route::post('search/all',array('as'=>'search_all','uses'=>'SearchController@getSearch'));
//SEARCH ROUTE ENDS HERE

//VIDEO ROUTE BEGINS HERE
Route::post('/videos',
    array('uses'=>'VideosController@getVideos'));

Route::post('/videos/artist',
    array('uses'=>'VideosController@getVideosByArtist'));

Route::post('/videos/type',
    array('uses'=>'VideosController@getVideosByType'));

Route::post('/video',
    array('uses'=>'VideosController@getVideo'));

Route::post('/video/create',
    array('uses'=>'VideosController@create'));

Route::post('user/video/create',
    array('uses'=>'VideosController@createUserVideo'));

Route::post('/video/edit',
    array('uses'=>'VideosController@edit'));

Route::post('/video/delete',
    array('uses'=>'VideosController@delete'));

Route::post('/video/edit/video_cover',
    array('uses'=>'VideosController@editVideoCover'));

Route::post('/videotype/create',
    array('uses'=>'VideoTypesController@create'));

Route::post('/videotype/edit',
    array('uses'=>'VideoTypesController@edit'));

Route::post('/videotype/delete',
    array('uses'=>'VideoTypesController@delete'));

Route::post('/videotype/types',
    array('uses'=>'VideoTypesController@getVideoTypes'));

Route::post('/videotype/type',
    array('uses'=>'VideoTypesController@getVideoType'));

Route::post('/test_stuff',
    array('uses'=>'VideosController@test'));

//END OF VIDEO ROUTES

//NEWS ROUTE BEGINS HERE
Route::post('/news',
    array('uses'=>'NewsController@getNews'));

Route::any('/news/item',
    array('uses'=>'NewsController@getSingleNews'));

Route::post('/news/create',
    array('uses'=>'NewsController@create'));

Route::post('/news/edit',
    array('uses'=>'NewsController@edit'));

Route::post('/news/edit/cover_img',
    array('uses'=>'NewsController@editCoverImg'));

Route::post('/news/delete',
    array('uses'=>'NewsController@delete'));

Route::post('/news/category/news',
    array('uses'=>'NewsController@getNewsCategory')); //ALL NEWS THAT BELONGS TO A PARTICULAR NEWS CATEGORY

//END OF NEWS ROUTE

//NEWS SECTION ROUTE BEGINS HERE

Route::post('/news/section/create',
    array('uses'=>'NewsSectionController@create'));

Route::post('/news/section/edit',
    array('uses'=>'NewsSectionController@edit'));

Route::post('/news/section/add/images',
    array('uses'=>'NewsSectionController@addImages'));

Route::post('/news/section/delete',
    array('uses'=>'NewsSectionController@delete'));

Route::post('/news/section/photo/delete',
    array('uses'=>'NewsSectionController@deletePhoto'));

//END OF NEWS SECTION ROUTE

//NEWS CATEGORY ROUTE
Route::post('/news/category/create',
    array('uses'=>'NewsCatgController@create'));

Route::post('/news/category/edit',
    array('uses'=>'NewsCatgController@edit'));

Route::post('/news/category/delete',
    array('uses'=>'NewsCatgController@delete'));

Route::post('/news/category/',
    array('uses'=>'NewsCatgController@getNewsCatg'));

Route::post('/news/category/item',
    array('uses'=>'NewsCatgController@getSingleNewsCatg'));

Route::post('/test_code',
    array('uses'=>'NewsCatgController@test'));

//END OF NEWS CATEGORY ROUTES


//ARTISTS ROUTE BEGINS HERE
Route::post('/artists',
    array('uses'=>'ArtistsController@getArtists'));
Route::post('/artists/recent',
    array('uses'=>'ArtistsController@getRecentArtists'));

Route::any('/artist',
    array('uses'=>'ArtistsController@getArtist'));

Route::post('/artists/create',
    array('uses'=>'ArtistsController@create'));

Route::post('/artists/edit/name',
    array('uses'=>'ArtistsController@editName'));

Route::post('/artists/edit/avatar',
    array('uses'=>'ArtistsController@editAvatar'));

Route::post('/artists/delete',
    array('uses'=>'ArtistsController@delete'));


//END OF ARTISTS ROUTES

//ALBUMS ROUTE BEGINS HERE
Route::post('/albums',
    array('uses'=>'AlbumsController@getAlbums'));
Route::post('/albums/recent',
    array('uses'=>'AlbumsController@getRecentAlbums'));

Route::any('/album',
    array('uses'=>'AlbumsController@getAlbum'));

Route::post('/albums/create',
    array('uses'=>'AlbumsController@create'));

Route::post('/albums/edit',
    array('uses'=>'AlbumsController@editAlbum'));

Route::post('/albums/edit/cover',
    array('uses'=>'AlbumsController@editAlbumCover'));

Route::post('/albums/delete',
    array('uses'=>'AlbumsController@delete'));


//END OF ALBUMS ROUTES


//GENRES ROUTE BEGINS HERE
Route::post('/genres',
    array('uses'=>'GenresController@getGenres'));

Route::any('/genre',
    array('uses'=>'GenresController@getGenre'));

Route::post('/genres/create',
    array('uses'=>'GenresController@create'));

Route::post('/genres/edit',
    array('uses'=>'GenresController@edit'));

Route::post('/genres/delete',
    array('uses'=>'GenresController@delete'));

//END OF GENRES ROUTES

//USER ALBUMS ROUTE BEGINS HERE
Route::post('/user/albums',
    array('uses'=>'UserAlbumsController@getAlbums'));
Route::post('/user/albums/recent',
    array('uses'=>'UserAlbumsController@getRecentAlbums'));

Route::any('/user/album',
    array('uses'=>'UserAlbumsController@getAlbum'));

Route::post('/user/albums/create',
    array('uses'=>'UserAlbumsController@create'));

Route::post('/user/albums/edit',
    array('uses'=>'UserAlbumsController@editAlbum'));

Route::post('/user/albums/edit/cover',
    array('uses'=>'UserAlbumsController@editAlbumCover'));

Route::post('/user/albums/delete',
    array('uses'=>'UserAlbumsController@delete'));


//END OF USER ALBUMS ROUTES

//MUSIC ROUTE BEGINS HERE
Route::post('songs/genre',
    array('uses'=>'MusicController@getByGenre'));
Route::post('songs/artist',
    array('uses'=>'MusicController@getByArtist'));
Route::post('/songs',
    array('uses'=>'MusicController@getSongs'));
Route::post('/songs/recent',
    array('uses'=>'MusicController@getRecentSongs'));
Route::post('/song',
    array('uses'=>'MusicController@getSong'));
Route::post('songs/create',
    array('uses'=>'MusicController@create'));
Route::post('songs/edit',
    array('uses'=>'MusicController@editSong'));
Route::post('songs/edit/file',
    array('uses'=>'MusicController@editSongFile'));
Route::post('songs/edit/cover',
    array('uses'=>'UserMusicController@editMusicCover'));
Route::post('songs/delete',
    array('uses'=>'MusicController@delete'));


//END OF MUSIC ROUTES

//USERS MUSIC ROUTE BEGINS HERE
Route::post('/user/songs',
    array('uses'=>'UserMusicController@getSongs'));
Route::post('/user/songs/recent',
    array('uses'=>'UserMusicController@getRecentSongs'));
Route::post('/user/song',
    array('uses'=>'UserMusicController@getSong'));
Route::post('user/songs/create',
    array('uses'=>'UserMusicController@create'));
Route::post('user/songs/edit',
    array('uses'=>'UserMusicController@editSong'));
Route::post('user/songs/edit/file',
    array('uses'=>'UserMusicController@editSongFile'));
Route::post('user/songs/delete',
    array('uses'=>'UserMusicController@delete'));
//END OF USERS MUSIC ROUTES

//LIVE FEED ROUTE BEGINS HERE
Route::post('/live_feed/',
    array('uses'=>'LiveFeedController@getLiveFeed'));

Route::post('/live_feed/updates',
    array('uses'=>'LiveFeedController@getLiveFeedUpdates'));

Route::post('/live_feed/news/add',
    array('uses'=>'LiveFeedController@newsToLiveFeed'));

Route::any('/live_feed/item',
    array('uses'=>'LiveFeedController@getSingleLiveFeed'));

Route::post('/live_feed/create',
    array('uses'=>'LiveFeedController@create'));

Route::post('/live_feed/edit',
    array('uses'=>'LiveFeedController@edit'));

Route::post('/live_feed/edit/thumbnail',
    array('uses'=>'LiveFeedController@editThumbnail'));

Route::post('/live_feed/edit/attachment',
    array('uses'=>'LiveFeedController@editAttachment'));

Route::post('/live_feed/delete',
    array('uses'=>'LiveFeedController@delete'));

Route::post('/live_feed/poster/live_feed',
    array('uses'=>'LiveFeedController@getPosterLiveFeed'));

Route::post('/live_feed/unique/poster/live_feed',
    array('uses'=>'LiveFeedController@getUniquePosterLiveFeed'));

Route::post('/live_feed/scrapesite',
    array('uses'=>'LiveFeedController@scrapeSite'));

//END OF LIVE FEED ROUTE

//LIVE TV ROUTE BEGINS HERE
Route::post('/livetv/',
    array('uses'=>'LivetvController@getLivetv'));

Route::post('/livetv/current',
    array('uses'=>'LivetvController@getCurrentLiveVideo'));

Route::post('/livetv/updates',
    array('uses'=>'LivetvController@getLivetvUpdates'));

Route::any('/livetv/item',
    array('uses'=>'LivetvController@getSingleLivetv'));

Route::post('/livetv/create',
    array('uses'=>'LivetvController@create'));

Route::post('/livetv/edit',
    array('uses'=>'LivetvController@edit'));

Route::post('/livetv/edit/thumbnail',
    array('uses'=>'LivetvController@editThumbnail'));

Route::post('/livetv/edit/attachment',
    array('uses'=>'LivetvController@editAttachment'));

Route::post('/livetv/delete',
    array('uses'=>'LivetvController@delete'));

Route::post('/livetv/poster/livetv',
    array('uses'=>'LivetvController@getPosterLivetv'));

Route::post('/livetv/unique/poster/livetv',
    array('uses'=>'LivetvController@getUniquePosterLivetv'));

Route::post('/livetv/scrapesite',
    array('uses'=>'LivetvController@scrapeSite'));

Route::post('/livetv/status',
    array('uses'=>'LivetvController@ChangeLivetvStatus'));

//END OF LIVE TV ROUTE

//FOLLOW ROUTE BEGINS HERE
Route::post('/follow/user',
    array('uses'=>'UsersFollowController@followUser'));

Route::any('/unfollow/user',
    array('uses'=>'UsersFollowController@unfollowUser'));

Route::post('/following/user',
    array('uses'=>'UsersFollowController@getFollowingUser'));

Route::post('/user/following',
    array('uses'=>'UsersFollowController@getUserFollowed'));

Route::post('/follow/block/user',
    array('uses'=>'UsersFollowController@blockFollower'));

Route::post('/follow/unblock/user',
    array('uses'=>'UsersFollowController@unblockFollower'));

//END OF FOLLOW ROUTE

//PLAYlIST ROUTE BEGINS HERE
Route::post('/playlist/create',
    array('uses'=>'PlaylistController@create'));

Route::any('/playlist/edit',
    array('uses'=>'PlaylistController@edit'));

Route::post('/playlist/delete',
    array('uses'=>'PlaylistController@delete'));

Route::post('/playlist/all',
    array('uses'=>'PlaylistController@getAllPlaylist'));

/*Route::post('/playlist',
    array('uses'=>'PlaylistController@getPlaylist'));*/

Route::post('user/playlist/all',
    array('uses'=>'PlaylistController@getAllUserPlaylist'));

/*Route::post('user/playlist',
    array('uses'=>'PlaylistController@getUserPlaylist'));*/

Route::post('playlist/songs',
    array('uses'=>'PlaylistFilesController@getPlaylistFilesByPlaylist'));

Route::post('playlist/song/add',
    array('uses'=>'PlaylistFilesController@create'));

Route::post('playlist/song/delete',
    array('uses'=>'PlaylistFilesController@delete'));

//END OF PLAYlIST ROUTE

//COMMENT SECTION

Route::post('album/comment',
    array('uses'=>'AlbumCommentsController@create'));

Route::post('albums/comment/load/more',
    array('uses'=>'AlbumsController@loadMoreComments'));

Route::post('albums/comment/all',
    array('uses'=>'AlbumCommentsController@getComments'));

Route::post('songs/comment',
    array('uses'=>'MusicCommentsController@create'));

Route::post('songs/comment/load/more',
    array('uses'=>'MusicController@loadMoreComments'));

Route::post('songs/comment/all',
    array('uses'=>'MusicCommentsController@getComments'));


Route::post('news/comment',
    array('uses'=>'NewsCommentsController@create'));

Route::post('news/comment/load/more',
    array('uses'=>'NewsController@loadMoreComments'));

Route::post('news/comment/all',
    array('uses'=>'NewsCommentsController@getComments'));


Route::post('videos/comment',
    array('uses'=>'VideosCommentsController@create'));

Route::post('videos/comment/load/more',
    array('uses'=>'VideosController@loadMoreComments'));

Route::post('videos/comment/all',
    array('uses'=>'VideosCommentsController@getComments'));


Route::post('live_feed/comment',
    array('uses'=>'LiveFeedsCommentsController@create'));

Route::post('live_feed/comment/load/more',
    array('uses'=>'LiveFeedsCommentsController@loadMoreComments'));

Route::post('live_feed/comment/all',
    array('uses'=>'LiveFeedsCommentsController@getComments'));

//END OF COMMENT SECTION

//REACT SECTION

Route::post('albums/react',
    array('uses'=>'AlbumLikesController@create'));

Route::post('albums/like/load/more',
    array('uses'=>'AlbumsController@loadMoreLikes'));


Route::post('songs/react',
    array('uses'=>'MusicLikesController@create'));

Route::post('songs/like/load/more',
    array('uses'=>'MusicController@loadMoreLikes'));


Route::post('news/react',
    array('uses'=>'NewsLikesController@create'));

Route::post('news/like/load/more',
    array('uses'=>'NewsController@loadMoreLikes'));


Route::post('videos/react',
    array('uses'=>'VideosLikesController@create'));

Route::post('videos/like/load/more',
    array('uses'=>'VideosController@loadMoreLikes'));


Route::post('live_feed/react',
    array('uses'=>'LiveFeedsLikesController@create'));

Route::post('live_feed/like/load/more',
    array('uses'=>'LiveFeedsController@loadMoreLikes'));


//END OF REACT SECTION