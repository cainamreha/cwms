<?php
namespace Concise;



/**
 * Media-Player
 * 
 */

class MediaPlayer extends Modules
{


	/**
	 * Erstellt einen Mediaplayer (Audio/Video-Playlist)
	 *
	 * @param	array	$name file-Objektname(n)
     * @param	array 	$title file-Objekttitel(n)
     * @param	array	$poster Breite des Players (default = DEF_PLAYER_WIDTH)
     * @param	array	$artist Animation des Players (default = 0)
     * @param	string	$skin Link zum File (default = 0)
     * @param	int		$type Art des Players (default = 0; 0 = Player, 1 = Playlist)
	 * @param	int		$autoplay falls 1, Player spielt automatisch (default = 0)
     * @param	int		$width Player-Breite (default = 640)
	 * @param	int		$height Player-Höhe (default = 360)
     * @param	int		$link Link zum File (default = 0)
     * @param	int		$uniqueID ID als Zusatz zur tag-id (default = 1)
	 * @access	public
	 * @return	string
	 */
	public function __construct()
	{
		
	}
	
	
	public function getMediaPlayer($name, $title, $poster = array(), $artist = array(), $skin = "blue.monday", $type = 0, $width = 640, $height = 360, $autoplay = 0, $link = 0, $uniqueID = 1)
	{
		
		// Javascript-File zuweisen
		$this->scriptFiles[]	= "extLibs/jquery/jplayer/jplayer/jquery.jplayer.min.js";
		$this->scriptFiles[]	= "extLibs/jquery/jplayer/add-on/jplayer.playlist.min.js";
		
		// CSS-File zuweisen
		$this->cssFiles[]		= "extLibs/jquery/jplayer/skin/" . $skin . "/css/jplayer." . $skin . ".css";
		
		$suppliedFiles	= array();
		$playlist		= "";
		$posterPath		= 'poster: "' . PROJECT_HTTP_ROOT . self::getMediaFilePath("", "poster");
		$i				= 0;
		
		if($i == 0 || $type == 1) {
				
			foreach($name as $file) {
				
				$file = trim($file);
				
				if($file != "") {
					
					$baseName	= substr($file, 0, strrpos($file,'.'));
					$fileExt	= substr($file, strrpos($file,'.') +1);
					
					$playlist .=	'{' . "\r\n";
												
					if($fileExt == "ogg") { // Vor mp3, wegen Bug in Chrome
						$mediaFile	= self::getMediaFilePath($baseName, "audio");
						if(file_exists(PROJECT_DOC_ROOT . $mediaFile . '.ogg')) {
							$playlist .= 'oga: "' . PROJECT_HTTP_ROOT . $mediaFile . '.ogg",';
							$suppliedFiles[]	= "oga";
						}
					}
					if($fileExt == "mp3") {
						$mediaFile	= self::getMediaFilePath($baseName, "audio");
						if(file_exists(PROJECT_DOC_ROOT . $mediaFile . '.mp3')) {
							$playlist .= "\r\n" . 'mp3: "' . PROJECT_HTTP_ROOT . $mediaFile . '.mp3",';
							$suppliedFiles[]	= "mp3";
						}
						$fileExt 			= "ogg";
					}
					if($fileExt == "m4v") {
						$mediaFile	= self::getMediaFilePath($baseName, "video");
						if(file_exists(PROJECT_DOC_ROOT . $mediaFile . '.m4v')) {
							$playlist .= "\r\n" . 'm4v: "' . PROJECT_HTTP_ROOT . $mediaFile . '.m4v",';
							$suppliedFiles[]	= "m4v";
						}
						$fileExt 			= "webm";
					}
					elseif($fileExt == "mp4") {
						$mediaFile	= self::getMediaFilePath($baseName, "video");
						if(file_exists(PROJECT_DOC_ROOT . $mediaFile . '.mp4')) {
							$playlist .= "\r\n" . 'm4v: "' . PROJECT_HTTP_ROOT . $mediaFile . '.mp4",';
							$suppliedFiles[]	= "m4v";
						}
						$fileExt 			= "webm";
					}
					if($fileExt == "webm") {
						$mediaFile	= self::getMediaFilePath($baseName, "video");
						if(file_exists(PROJECT_DOC_ROOT . $mediaFile . '.webm')) {
							$playlist .= "\r\n" . 'webmv: "' . PROJECT_HTTP_ROOT . $mediaFile . '.webm",';
							$suppliedFiles[]	= "webmv";
						}
						$fileExt 			= "ogv";
					}
					if($fileExt == "ogv") {
						$mediaFile	= self::getMediaFilePath($baseName, "video");
						if(file_exists(PROJECT_DOC_ROOT . $mediaFile . '.ogv')) {
							$playlist .= "\r\n" . 'ogv: "' . PROJECT_HTTP_ROOT . $mediaFile . '.ogv",';
							$suppliedFiles[]	= "ogv";
						}
					}
					
					if(count($poster) > 0) {
						
						if(isset($poster[$i]))
							$playlist .=	"\r\n" . $posterPath . $poster[$i] . '",';
						else
							$playlist .=	"\r\n" . $posterPath . $poster[0] . '",';
					}
					
					// Falls Playlist
					if($type == 1)
						$playlist .=	($link ? "\r\n" . 'free: "true",' : '') .
										"\r\n" . 'title: "' . ($title[$i] != "" ? $title[$i] : $name[$i]) . '",' .
										"\r\n" . 'artist: "' . (isset($artist[$i]) ? $artist[$i] : (isset($artist[0]) ? $artist[0] : '')) . '",';
									
					$playlist = substr($playlist, 0, -1) . "\r\n";
					
					$playlist .= 	'},';
					
				} // Ende if file != ""
				
				$i++;
				
			} // Ende foreach
		
		} // Ende if $=0
		
		$playlist = substr($playlist, 0, -1) . "";
		
		// Falls Cover-Bilder angegeben sind, Videotyp unterstützen, damit diese angezeigt werden
		if(count($poster) > 0)
			$suppliedFiles[] = "ogv";
		
		$suppliedFiles = array_unique($suppliedFiles);
		
		// Falls Einzel-Player
		if($type == 0) {
			
			$this->scriptCode[] =	'$(document).ready(function(){' . "\r\n" .
									'$("#jquery_jplayer_'.$uniqueID.'").jPlayer({' . "\r\n" .
										'ready: function () {' . "\r\n" .
											'$(this).jPlayer("setMedia", ' . ($playlist != "" ? $playlist : '""') .
											')' . ($autoplay ? '.jPlayer("play")' : '') . ';' . "\r\n" .
										'},' . "\r\n" .
										'play: function() { // To avoid both jPlayers playing together.' . "\r\n" .
											'$(this).jPlayer("pauseOthers");' . "\r\n" .
										'},' . "\r\n" .
										'swfPath: "' . PROJECT_HTTP_ROOT . '/extLibs/jquery/jplayer/jplayer",' . "\r\n" .
										'jPlayer: "#jquery_jplayer_' . $uniqueID . '",' . "\r\n" .
										'preload: "metadata",' . "\r\n" .
										'solution: "html,flash",' . "\r\n" .
										'supplied: "' . implode(",", $suppliedFiles) . '",' . "\r\n" .
										'cssSelectorAncestor: "#jp_container_' . $uniqueID . '",
										useStateClassSkin: true,
										autoBlur: false,
										smoothPlayBar: true,
										keyEnabled: true,
										remainingDuration: true,
										toggleDuration: true,
										errorAlerts: false,
										warningAlerts: false,
										size: {' . "\r\n" .
											'width: "' . $width . 'px",' . "\r\n" .
											'height: "' . $height . 'px",' . "\r\n" .
											'cssClass: "jp-video-' . ($width > 720 ? 1080 : ($width > 480 ? 720 : ($width > 360 ? 480 : ($width > 270 ?  360 : 270)))) . 'p"' . "\r\n" .
										'}' . "\r\n" .
									'});' . "\r\n" .
									'});' . "\r\n";
			
			$player =	'<div id="jp_container_'.$uniqueID.'" class="jp-video jp-video-' . ($width > 720 ? 1080 : ($width > 480 ? 720 : ($width > 360 ? 480 : ($width > 270 ?  360 : 270)))) . 'p" role="application" aria-label="media player">' . "\r\n" .
						'<div class="jp-type-single">' . "\r\n";
						
			if(isset($title[0]) && trim($title[0]) != "")
				$player	.=	'<div class="jp-details"><div class="jp-title">' . $title[0] . '</div></div>' . "\r\n";
							
			$player .=		'<div id="jquery_jplayer_'.$uniqueID.'" class="jp-jplayer"></div>' . "\r\n" .
							'<div class="jp-gui">' . "\r\n" .
								'<div class="jp-video-play">' . "\r\n" .
									'<button class="jp-video-play-icon" tabindex="0" role="button">play</button>' . "\r\n" .
								'</div>' . "\r\n" .
								'<div class="jp-interface">' . "\r\n" .
									'<div class="jp-progress">' . "\r\n" .
										'<div class="jp-seek-bar">' . "\r\n" .
											'<div class="jp-play-bar"></div>' . "\r\n" .
										'</div>' . "\r\n" .
									'</div>' . "\r\n" .
									'<div class="jp-current-time" aria-label="time" role="timer"></div>' . "\r\n" .
									'<div class="jp-duration" aria-label="duration" role="timer"></div>' . "\r\n" .
									'<div class="jp-controls-holder">' . "\r\n" .
										'<div class="jp-volume-controls">' . "\r\n" .
											'<button class="jp-mute" tabindex="0" role="button" title="mute">mute</button>' . "\r\n" .
											'<button class="jp-volume-max" tabindex="0" role="button" title="max volume">max volume</button>' . "\r\n" .
											'<div class="jp-volume-bar">' . "\r\n" .
												'<div class="jp-volume-bar-value"></div>' . "\r\n" .
											'</div>' . "\r\n" .
										'</div>' . "\r\n" .
										'<div class="jp-controls">' . "\r\n" .
											'<button class="jp-play" tabindex="0" role="button">play</button>' . "\r\n" .
											'<button class="jp-stop" tabindex="0" role="button">stop</button>' . "\r\n" .
										'</div>' . "\r\n" .
										'<div class="jp-toggles">' . "\r\n" .
											'<button class="jp-repeat" tabindex="0" role="button" title="repeat">repeat</button>' . "\r\n" .
											'<button class="jp-full-screen" tabindex="0" role="button" title="full screen">full screen</button>' . "\r\n" .
										'</div>' . "\r\n" .
									'</div>' . "\r\n" .
								'</div>' . "\r\n" .
							'</div>' . "\r\n" .
							'<div class="jp-no-solution">' . "\r\n" .
								'<span>Update Required</span>
								To play the media you will need to either update your browser to a recent version or update your <a href="http://get.adobe.com/flashplayer/" target="_blank">Flash plugin</a>.' . "\r\n" .
							'</div>' . "\r\n" .
						'</div>' . "\r\n" .
					'</div>' . "\r\n";
		}
		
		// Falls Playlist-Player
		else {
			
			$this->scriptCode[] =	'$(document).ready(function(){' . "\r\n" .
									'new jPlayerPlaylist({' . "\r\n" .
									'jPlayer: "#jquery_jplayer_' . $uniqueID . '",' . "\r\n" .
									'cssSelectorAncestor: "#jp_container_' . $uniqueID . '"' . "\r\n" .
									'},[' . $playlist . '],{' . "\r\n" .
									'playlistOptions: {' . "\r\n" .
									'enableRemoveControls: true,' . "\r\n" .
									'preload: "metadata",' . "\r\n" .
									'autoPlay: ' . ($autoplay ? 'true' : 'false') . "\r\n" .
									'},' . "\r\n" .
									'swfPath: "' . PROJECT_HTTP_ROOT . '/extLibs/jquery/jplayer/jplayer",' . "\r\n" .
									'solution: "html,flash",' . "\r\n" .
									'supplied: "' . implode(",", $suppliedFiles) . '",
									useStateClassSkin: true,
									autoBlur: false,
									smoothPlayBar: true,
									keyEnabled: true,
									remainingDuration: true,
									toggleDuration: true,
									errorAlerts: false,
									warningAlerts: false,
									wmode: "window"});' . "\r\n" .
									'});' . "\r\n";
		
		
			$player =	'<div id="jp_container_' . $uniqueID . '" class="jp-video jp-video-' . ($width > 720 ? 1080 : ($width > 480 ? 720 : ($width > 360 ? 480 : ($width > 270 ?  360 : 270)))) . 'p" role="application" aria-label="media player">' . "\r\n" .
						'<div class="jp-type-' . ($type == 0 ? 'single' : 'playlist') . '">' . "\r\n" .
							'<div id="jquery_jplayer_' . $uniqueID . '" class="jp-jplayer"></div>' . "\r\n" .
							'<div class="jp-gui">' . "\r\n" .
							'<div class="jp-video-play">' . "\r\n" .
								'<button class="jp-video-play-icon" tabindex="0" role="button">play</button>' . "\r\n" .
							'</div>' . "\r\n" .
							'<div class="jp-gui jp-interface">' . "\r\n" .
								'<div class="jp-progress">' . "\r\n" .
									'<div class="jp-seek-bar">' . "\r\n" .
										'<div class="jp-play-bar"></div>' . "\r\n" .
									'</div>' . "\r\n" .
								'</div>' . "\r\n" .
								'<div class="jp-current-time" aria-label="time" role="timer"></div>' . "\r\n" .
								'<div class="jp-duration" aria-label="duration" role="timer"></div>' . "\r\n" .
								'<div class="jp-details"><div class="jp-title"></div></div>' . "\r\n" .
								'<div class="jp-controls-holder">' . "\r\n" .
									'<div class="jp-volume-controls">' . "\r\n" .
										'<button class="jp-mute" tabindex="0" role="button" title="mute">mute</button>' . "\r\n" .
										'<button class="jp-volume-max" tabindex="0" role="button" title="max volume">max volume</button>' . "\r\n" .
										'<div class="jp-volume-bar">' . "\r\n" .
										  '<div class="jp-volume-bar-value"></div>' . "\r\n" .
										'</div>' . "\r\n" .
									'</div>' . "\r\n" .
									'<div class="jp-controls">' . "\r\n" .
										'<button class="jp-previous" tabindex="0" role="button">previous</button>' . "\r\n" .
										'<button class="jp-play" tabindex="0" role="button">play</button>' . "\r\n" .
										'<button class="jp-stop" tabindex="0" role="button">stop</button>' . "\r\n" .
										'<button class="jp-next" tabindex="0" role="button">next</button>' . "\r\n" .
									'</div>' . "\r\n" .
									'<div class="jp-toggles">' . "\r\n" .
										'<button class="jp-shuffle" tabindex="0" role="button" title="shuffle">shuffle</button>' . "\r\n" .
										'<button class="jp-repeat" tabindex="0" role="button" title="repeat">repeat</button>' . "\r\n" .
										'<button class="jp-full-screen" tabindex="0" role="button" title="full screen">full screen</button>' . "\r\n" .
									'</div>' . "\r\n" .
								'</div>' . "\r\n" .
							'</div>' . "\r\n" .
						  '</div>' . "\r\n" .
						  '<div class="jp-playlist">' . "\r\n" .
							'<ul>' . "\r\n" .
								'<li></li>' . "\r\n" .
							'</ul>' . "\r\n" .
						  '</div>' . "\r\n" .
						  '<div class="jp-no-solution">' . "\r\n" .
							'<span>Update Required</span>To play the media you will need to either update your browser to a recent version or update your <a href="http://get.adobe.com/flashplayer/" target="_blank">Flash plugin</a>.' . "\r\n" .
					'</div>' . "\r\n" .
					'</div>' . "\r\n" .
					'</div>' . "\r\n";
		}
		
		return $player;
	}


	/**
	 * Erstellt einen Mediaplayer
	 *
	 * @param	array	$name file-Objektname(n)
     * @param	array 	$title file-Objekttitel(n)
	 * @access	public
	 * @return	string
	 */
	public function getMediaFilePath($basename, $type)
	{
	
		// Falls Files-Folder
		if(strpos($basename, "/") !== false)
			return '/' . CC_FILES_FOLDER . '/' . $basename;
		
		switch($type) {
		
			case "audio":
				return '/' . CC_AUDIO_FOLDER . '/' . $basename;
		
			case "video":
				return '/' . CC_VIDEO_FOLDER . '/' . $basename;
		
			case "poster":
				return '/' . CC_IMAGE_FOLDER . '/' . $basename;
		}
		return $basename;
	}		
	
}
