var app = angular.module('Afrobeat' , ['ui.router']);

app.config( function($stateProvider, $urlRouterProvider, $locationProvider){
    $stateProvider
        .state('music' , {
               url :'/',
               templateUrl : 'views/music.html',
               controller : 'MusicController'
       })
        .state('albums' , {
               url :'/albums',
               templateUrl : 'views/albums.html',
               controller : 'AlbumController'
       })
        .state('artists' , {
               url :'/artists',
               templateUrl : 'views/artists.html',
               controller : 'ArtistController'
       })
        .state('playlist' , {
               url :'/playlist',
               templateUrl : 'views/playlists.html',
               controller : 'PlaylistController'
       })
        .state('events' , {
               url :'/events',
               templateUrl : 'views/view_events.html',
               controller : 'EventController'
       })
    ;
    
    $urlRouterProvider.otherwise('/');
   // $locationProvider.html5Mode(true);
});