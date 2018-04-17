<!DOCTYPE html>
<html ng-app="Afrobeat" lang="en">
<head>
    <title>wr.Music</title>
    <!--<base href="http://localhost/afrobeat/" />-->

    <!-- Meta -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=320, initial-scale=1">
    <meta name="HandheldFriendly" content="True"/>
    <!-- Styles -->
    <link rel="stylesheet" type="text/css" href="css/fonts.css" />

    <link rel="stylesheet" type="text/css" href="css/semantic.css" />
    <link rel="stylesheet" type="text/css" href="css/grid.css">
    <link rel="stylesheet" type="text/css" href="css/feed.css">
    <link rel="stylesheet" type="text/css" href="css/style.css" />
    <link  href="css/perfect-scrollbar.css" rel="stylesheet">
    <!-- Scripts -->
    
</head>
<body>
<div class="ui wide overlay demo  sidebar ">
	<div class="left_bar custom_sroll for_small">
		<div class="ui launch icon button" data-transition="overlay">
			<img src="img/sidebar.png">
		</div>
		<div class="user clearfix">
			<div class="avatar"><img src="img/avatar.png"></div>
			<div class="controls">
				<a href="#"><img src="img/seting.png"></i></a>
				<a href="#"><img src="img/user.png"></a>
				<a href="#"><img src="img/log_out.png"></a>
			</div>
		</div>
		<ul>
			<li class="active"><a href="#" class="nav" ui-sref="music"><i class="icon music"></i> Music <img src="img/arrow_down.png"></i></a></li>
			<li><a href="news.html" class="nav"><i class="icon chart"></i> Charts</a></li>
			<li><a href="#" class="nav" ui-sref="albums"><i class="icon album"></i> Albums</a></li>
			<li><a href="#" class="nav"><i class="icon video"></i> Video Clips</a></li>
			<li><a href="#" class="nav"><i class="icon recent"></i> Recent</a></li>
			<li><a href="#" class="nav"><i class="icon popular"></i> Most Popular</a></li>
			<li><a href="#" class="nav"><i class="icon fav"></i> Favorites</a></li>
			<li><a href="#" class="nav"><i class="icon my-video"></i> My videos</a></li>
			<li><a href="#" class="nav" ui-sref="artists"><i class="icon artist"></i> Artists</a></li>
			<li><a href="#" class="nav"><i class="icon genre"></i> Genres</a></li>
			<li><a href="#" class="nav" ui-sref="playlist"><i class="icon playlist"></i> My Playlists</a></li>
			<li><a href="#" class="nav" ui-sref="events"><i class="icon event"></i> Events <img src="img/arrow_down.png"></a></li>
		</ul>
	</div>
	<div class="player custom_sroll">
		<a href="#"><img src="img/sidebar_logo.png"></a>
		<div class="album_picture"><img src="img/album_picture.jpg"></div>
		<p class="song">The Adventures of Rain <br>Dance Maggie</p>
		<p class="artist">Artist: <span>Red Hot Chili Peppers</span></p>
		<p class="artist">Album: <span>Love Lust Faith + Dreams</span></p>
		<ul class="player_buttons">
			<li><button class="stop"></button></li>
			<li><button class="prev"></button></li>
			<li><button class="play"></button></li>
			<li><button class="next"></button></li>
			<li><button class="sound"></button></li>
			<li><div class="value"><span></span></div></li>
		</ul>
		<div class="duration">
			<span></span>
		</div>
		<div class="time"><p>-4:45</p></div>
		<div class="author">
			<div class="ava_container"><img src="img/user_small.jpg"></div>
			<div class="user_name">
				<p><a href="#">Anastasia Verd</a></p>
				<p><span>3 years, 1 months ago</span></p>
			</div>
		</div>
	</div>
</div>

<div class="left_bar out_side custom_sroll ">
	<div class="ui launch icon button" data-transition="overlay">
		<img src="img/sidebar.png">
	</div>
	<ul>
		<li class="active"><a href="#" title="Music" class="nav" ui-sref="music"><i class="icon music"></i></a></li>
		<li><a href="news.html"  title="Charts" ><i class="icon chart"></i></a></li>
		<li><a href="#"  title="Albums"  ui-sref="albums"><i class="icon album"></i></a></li>
		<li><a href="#"  title="Video Clips" ><i class="icon video"></i></a></li>
		<li><a href="#"  title="Recent" ><i class="icon recent"></i></a></li>
		<li><a href="#"  title="Popular" ><i class="icon popular"></i></a></li>
		<li><a href="#"  title="Favorites" ><i class="icon fav"></i></a></li>
		<li><a href="#"  title="My Videos" ><i class="icon my-video"></i></a></li>
		<li><a href="#"  title="Artists"  ui-sref="artists"><i class="icon artist"></i></a></li>
		<li><a href="#"  title="Genres" ><i class="icon genre"></i></a></li>
		<li><a href="#"  title="Playlist" ui-sref="playlist"><i class="icon playlist"></i></a></li>
		<li><a href="#"  title="Events" ui-sref="events"><i class="icon event"></i></a></li>
	</ul>
</div>

<div class="container pusher">
    
    <div ui-view></div>


	<div class="right_bar">
	<div class="user clearfix">
		<div class="avatar"><img src="img/avatar.png"></div>
		<div class="controls">
			<a href="#"><img src="img/seting.png"></i></a>
			<a href="#"><img src="img/user.png"></a>
			<a href="#"><img src="img/log_out.png"></a>
		</div>
	</div>
	<div class="notifications">
		<p>Notifications <a href="#"></a></p>
		<ul>
			<li>
				<div class="img_container"><img src="img/notific-1.jpg"></div>
				<div class="body_container">
					<p>User  <a href="#">Savannah Durham </a> has invited you to the event ”<a href="#">The hunting party China tour</a>” </p>
					<p><span>15 minutes ago</span></p>
				</div>
			</li>
			<li>
				<div class="img_container"><img src="img/notific-2.jpg"></div>
				<div class="body_container">
					<p>User  <a href="#">Lily Bradberry  </a> has invited you to the event ”<a href="#">30 Seconds to Mars</a>” </p>
					<p><span>15 minutes ago</span></p>
				</div>
			</li>
			<li>
				<div class="img_container"><img src="img/notific-3.jpg"></div>
				<div class="body_container">
					<p>User  <a href="#">Julia Albertson </a> has invited you to the event ”<a href="#">Red Hot Chili Peppers</a>” </p>
					<p><span>15 minutes ago</span></p>
				</div>
			</li>
			<li>
				<div class="img_container"><img src="img/notific-4.jpg"></div>
				<div class="body_container">
					<p>User  <a href="#">Natalie Waller </a> has invited you to the event ”<a href="#">Chicago</a>” </p>
					<p><span>15 minutes ago</span></p>
				</div>
			</li>
		</ul>
	</div>
	<div class="artist_list">
		<p>List of artists </p>
		<div class="ui  icon input">
	        <input type="text" placeholder="Search...">
	        <img src="img/search.png">
      	</div>
      	<p class="search"><img src="img/search_load.png"><span> search</span></p>
      	<ul>
      		<li>
      			<div class="img_container"><img src="img/search_item1.jpg"></div>
      			<div class="body_container">
      				<a href="#">Madonna</a>
      				<p>328 Songs <span> • 27 Album</span></p>
      			</div>
      		</li>
      		<li>
      			<div class="img_container"><img src="img/search_item2.jpg"></div>
      			<div class="body_container">
      				<a href="#">Maroon 5</a>
      				<p>523 Songs <span> • 5 Album</span></p>
      			</div>
      		</li>
      		<li>
      			<div class="img_container"><img src="img/search_item2.jpg"></div>
      			<div class="body_container">
      				<a href="#">Miley Cyrus</a>
      				<p>52 Songs <span> • 3 Album</span></p>
      			</div>
      		</li>
      	</ul>
	</div>
</div>
</div>
<!-- Script -->
<script src="js/angular/angular.js" type="text/javascript"></script>
<script src="js/angular-ui-router/release/angular-ui-router.js" type="text/javascript"></script>
<script src="js/app.js" type="text/javascript"></script>
<script src="js/controllers.js" type="text/javascript"></script>
<script src="js/factories.js" type="text/javascript"></script>
<script src="js/jquery-1.11.0.js" type="text/javascript"></script>
<script src="js/script.js" type="text/javascript"></script>
<script src="js/semantic.min.js" type="text/javascript"></script>
<script src="js/feed.js" type="text/javascript"></script>
<script src="js/perfect-scrollbar.jquery.js"></script>
<script src="js/perfect-scrollbar.js"></script>
<script>
	$('.custom_sroll').perfectScrollbar();
</script>
</body>
</html>