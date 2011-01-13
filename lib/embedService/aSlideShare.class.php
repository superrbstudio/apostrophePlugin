<?php

// TODO: Do we comment code thoroughly?
// TODO: Move private methods to the bottom (Reorder methods as necessary)
// TODO: Drop all curly braces to newlines
// TODO: Ugly image in media library?
// TODO: double spaces when you hit 'return' in the edit box in media library?

class aSlideShare extends aEmbedService {
	protected $features = null;
	protected $apiUrl = null;
	protected $apiKey = null;
	protected $sharedSecret = null;
	protected $showTypes = null;

	// Constructor for aSlideShare (obtains API key and shared secret)
	public function __construct() {
		$settings = sfConfig::get('app_a_slideshare');
		
		$this->features = array('thumbnail', 'search', 'browseUser');
		$this->apiUrl = 'http://www.slideshare.net/api/2/';
		
		/* The following SlideShare show types correspond to the given numbers (which are pulled from their API):
		 *		Presentation => 0
		 *		Document	 => 1
		 *		Portfolio	 => 2
		 *		Video		 => 3
		 *
		 * Each type of show requires a different SWF player to embed it properly, so if we pass in the numerical type of the
		 * show in question, we will receive the correct player to use.
		 */
		$this->showPlayers = array('ssplayer2.swf', 'doc_player.swf', 'ssplayer2.swf', 'playerv.swf');
		
		if (isset($settings['apiKey'])) {
			$this->apiKey = $settings['apiKey'];
		}
		
		if (isset($settings['sharedSecret'])) {
			$this->sharedSecret = $settings['sharedSecret'];
		}
	}

	public function configured() {
		if (isset($this->apiKey) && isset($this->sharedSecret)) {
			return true;
		}
		
		return false;
	}
	
	public function configurationHelpUrl() {
		// TODO: Create this wiki page
		return 'http://trac.apostrophenow.org/wiki/EmbedSlideShare';
	}
	
	public function supports($feature) {
		return in_array($feature, $this->features);
	}
	
	// This is the only function that actually talks to the SlideShare API
	private function getData($call, $params) {
		$timeStamp = time();
		$hash = sha1($this->sharedSecret.$timeStamp);
		
		try {
			$params['api_key'] = $this->apiKey;
			$params['ts'] = $timeStamp;
			$params['hash'] = $hash;
			$url = $this->apiUrl . "$call?" . http_build_query($params);
			
			$result = file_get_contents($url);
		} catch (Exception $e) {} // TODO: What to do with exception?
		
		return utf8_encode($result);
	}
	
	private function searchApi($call, $params, $browseUser=false) {
		$slideshowInfo = array();
		
		$data = new SimpleXMLElement($this->getData($call, $params));
		
		foreach ($data->Slideshow as $show) {
			$slideshowInfo[] = array('id' => (int) $show->ID,
								     'url' => (string) $show->URL,
								     'title' => (string) $show->Title,
								     'description' => (string) $show->Description);
		}
		
		if ($browseUser) {
			return array('total' => (int) $data->User->Count, 'results' => $slideshowInfo);
		} else {
			return array('total' => (int) $data->Meta->TotalResults, 'results' => $slideshowInfo);
		}
	}

	public function search($q, $page=1, $perPage=50) {
		$call = 'search_slideshows';
		$params = array('q' => $q, 'page' => $page, 'items_per_page' => $perPage);
		
		return $this->searchApi($call, $params);
	}
	
	public function browseUser($user, $page=1, $perPage=50) {
		$call = 'get_slideshows_by_user';
		$offset = ($page-1)*$perPage; // Search by user doesn't support page and items_per_page, so we must calculate an offset
		$params = array('username_for' => $user, 'limit' => $perPage, 'offset' => $offset);
		
		return $this->searchApi($call, $params, true);
	}
	
	public function getUserInfo($user) {
		// SlideShare has no API call to retrieve user information (only their groups, slideshows, and contacts)
		return array('name' => $user, 'description' => '');
	}
	
	// Will retrieve slideshow from given ID, URL (intelligently decides which to use)
	private function getSlideInfo($id) {
		echo "getSlideInfo($id)<br><br>";
		$call = 'get_slideshow';
		$tags = '';
		$params = (strpos($id, 'http://') !== false) ? array('slideshow_url' => $id, 'detailed' => 1) : array('slideshow_id' => $id, 'detailed' => 1);

		$data = new SimpleXMLElement($this->getData($call, $params));
		
		// Convert tags into comma-separated list
		foreach ($data->Tags->Tag as $tag) {
			$tags .= $tag . ', ';
		}
		
		if (strlen($tags) > 0) {
			$tags = substr($tags, 0, -2); // Remove the trailing comma
		}
		
		return array('id' => (int) $data->ID,
					 'url' => (string) $data->URL,
					 'title' => (string) $data->Title,
					 'description' => (string) $data->Description,
					 'tags' => $tags,
					 'credit' => (string) $data->Username,
					 'thumbnail' => (string) $data->ThumbnailURL,
					 'embedUrl' => (string) $data->PPTLocation,
					 'showType' => (int) $data->SlideshowType);
	}

	public function getInfo($id) {
		echo "getInfo()";
		$data = $this->getSlideInfo($id);
				
		return array('id' => $data['id'],
					 'url' => $data['url'],
					 'title' => $data['title'],
					 'description' => aHtml::simplify($data['description']),
					 'tags' => $data['tags'],
					 'credit' => $data['credit']);
	}

	public function embed($id, $width, $height, $title='', $wmode='opaque', $autoplay=false) {
		echo "embed()";
		$slideInfo = $this->getSlideInfo($id);
		$player = $this->showPlayers[$slideInfo['showType']];

return <<<EOT
<object id="__sse$id" width="$width" height="$height">
    <param name="movie" value="http://static.slidesharecdn.com/swf/$player?doc={$slideInfo['embedUrl']}" />
    <param name="allowFullScreen" value="true" />
    <param name="allowScriptAccess" value="always" />
    <embed name="__sse$id" src="http://static.slidesharecdn.com/swf/$player?doc={$slideInfo['embedUrl']}" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true" width="$width" height="$height"></embed>
</object>
EOT;
}

	public function getIdFromUrl($url) {
		echo "getIdFromUrl(), url=$url<br><br>";
		if (strpos($url, 'slideshare.net') !== false) {
			$slideInfo = $this->getSlideInfo($url);
			return $slideInfo['id'];
		}
		
		return false;
	}

	public function getUrlFromId($id) {
		echo "getUrlFromId()";
		$slideInfo = $this->getSlideInfo($id);
		return $slideInfo['url'];
	}

	public function getIdFromEmbed($embed) {
		if ((strpos($embed, 'static.slideshare') !== false) && (strpos($embed, 'ssplayer2.swf') !== false)) {
			// Extract the ID from the first line in the embed code (eg: <object id="__sse6504467" width=...>)
			if (preg_match('/__sse(\d+)/', $embed, $matches)) {
				return $matches[1];
			}
		}
		
		return false;
	}
	
	public function getThumbnail($id) {
		echo "getThumbnail()";
		$slideInfo = $this->getSlideInfo($id);
		
		if (strlen($slideInfo['thumbnail']) > 0) {
			return $slideInfo['thumbnail'];
		}
		
		return false;
	}
	
	public function getName() {
		return 'SlideShare';
	}
}

?>
