<?php

class aTwitterLegacyConverter {

  protected $consumer_key;
  protected $consumer_secret;
  protected $user_agent;
  protected $access_token;

  public function __construct($key, $secret, $agent)
  {
    $this->consumer_key = $key;
    $this->consumer_secret = $secret;
    $this->user_agent = $agent;
    // since we don't need to make a new access token each time we have a request,
    // create it here and store it for the life of the class.
    $this->access_token = $this->getAccessToken();
  }

  // return true if this URL has anything to do with twitter or twitter handles
  public function isURLValid($url)
  {
    error_log('asking for validate feed bb');
    if(preg_match("#api.twitter.com#", $url) || preg_match("/^@(\w+)$/", $url)){
      return true;
    } else {
      return false;
    }
  }

  // get the feed!
  public function getRSSFeedFromURL($url)
  {

    // if the URL matches a v1 API
    if(preg_match("#api.twitter.com/1/#", $url)){
      // get an array of queries
      $parameters = $this->getParametersFromUrl($url);
      // make the call to twitter
      $twitter_response = $this->getUserTimelineFromParameters($parameters);
      if (!$twitter_response) {
        return null;
      }
      // json_decode the response so we can mess with it
      $decoded_tweets = json_decode($twitter_response, true);
      if(isset($decoded_tweets['errors']))
      {
        return null;
      }
      // create a valid RSS feed and return it
      return $this->twitterRssFromJson($decoded_tweets, $parameters['screen_name']);
    }

    // if the URL is already a v1.1 API call
    else if(preg_match("#api.twitter.com/1.1/statuses/#", $url)){
      $user = $this->getUsernameFromTwitterUrl($url);
      // make the call to twitter using the URL directly
      $twitter_response = $this->getUserTimelineFromValidUrl($url);
      if (!$twitter_response) {
        return null;
      }
      // make an array out of the json object
      $decoded_tweets = json_decode($twitter_response, true);
      if(isset($decoded_tweets['errors']))
      {
        return null;
      }
      // create a valid RSS feed and return it
      return $this->twitterRssFromJson($decoded_tweets, $user);
    }

    else if(preg_match("#api.twitter.com/1.1/lists/#", $url, $matches)){
      // match a username followed by a / followed by a list name, e.g. @username/list

      $user = $this->getUsernameFromListUrl($url);
      $list = $this->getListNameFromListUrl($url);

      // extract the shit

      $twitter_response = $this->getTweetsFromUsernameAndList($user, $list);
      if(!$twitter_response) {
        return null;
      }

      $decoded_tweets = json_decode($twitter_response, true);
      if(isset($decoded_tweets['errors']))
      {
        return null;
      }
      // return the RSS feed
      return $this->twitterRssFromJson($decoded_tweets, $user);
    }

    // if the string passed in is a twitter handle, e.g. @username
    else if(preg_match("/^@(\w+)$/", $url, $matches)){
      // get the username without the @ symbol
      $user = $matches[1];
      // make the call to twitter
      $twitter_response = $this->getUserTimelineFromUsername($user);
      if (!$twitter_response) {
        return null;
      }
      // json_decode so we can access the data
      $decoded_tweets = json_decode($twitter_response, true);
      if(isset($decoded_tweets['errors']))
      {
        return null;
      }
      // return the RSS feed
      return $this->twitterRssFromJson($decoded_tweets, $user);
    }

    // is it an old-old-school user-id link?
    else if(preg_match('#twitter\.com/statuses/user_timeline/(\d+)\.rss#', $url, $matches)){
      // isolate the user_id
      $user_id = $matches[1];
      // make the call to twitter using the user_id
      $twitter_response = $this->getUserTimelineFromUserId($user_id);
      // json_decode so we can access the data
      $decoded_tweets = json_decode($twitter_response, true);
      if(isset($decoded_tweets['errors']))
      {
        return null;
      }
      // we'll need to get the user name to pass to twitterRssFromJson
      $user = $decoded_tweets[0]['user']['screen_name'];
      return $this->twitterRssFromJson($decoded_tweets, $user);
    }

    // what do we do if the url doesn't validate?
    else {
      return null;
    }
  }

  // this is a way to bypass the RSS feed generation and grab the full JSON response.
  // it assumes a well-formed URL has been passed in.
  public function getRawJsonResponse($url)
  {
    return $this->getUserTimelineFromValidUrl($url);
  }

  protected function makeBearerToken()
  {
    // the twitter docs say to encode these, even though at present
    // the result will be exactly the same
    $encoded_key = urlencode($this->consumer_key);
    $encoded_secret = urlencode($this->consumer_secret);
    // the token is formatted as "key:secret"
    $raw_bearer_token = $encoded_key.':'.$encoded_secret;
    // base64 encode it
    return base64_encode($raw_bearer_token);
  }

  protected function getParametersFromUrl($url)
  {
    $parsed_url = parse_url($url);
    $query = $parsed_url['query'];
    parse_str($query, $array_of_queries);
    return $array_of_queries;
  }

  protected function getUsernameFromTwitterUrl($url)
  {
    // parse the URL so that we can isolate the query
    $parsed_url = parse_url($url);
    // here's the query...
    $query = $parsed_url['query'];
    // ...but in case there are multiple queries in this URL, separate them...
    parse_str($query, $array_of_queries);
    // and get just the screen_name portion.
    $user = $array_of_queries['screen_name'];
    return $user;
  }

  protected function getUsernameFromListUrl($url)
  {
    $parsed_url = parse_url($url);
    $query = $parsed_url['query'];
    // ...but in case there are multiple queries in this URL, separate them...
    parse_str($query, $array_of_queries);
    // and get just the screen_name portion.
    $user = $array_of_queries['owner_screen_name'];
    return $user;
  }

  protected function getListNameFromListUrl($url)
  {
    $parsed_url = parse_url($url);
    $query = $parsed_url['query'];
    // ...but in case there are multiple queries in this URL, separate them...
    parse_str($query, $array_of_queries);
    // and get just the screen_name portion.
    $list = $array_of_queries['slug'];
    return $list;
  }

  protected function getAccessToken()
  {
    // get the base64 encoded bearer token
    $bearer_token = $this->makeBearerToken();
    // url for authentication
    $url = "https://api.twitter.com/oauth2/token";
    // authentication headers
    $headers = array(
      "POST /oauth2/token HTTP/1.1",
      "Host: api.twitter.com",
      "User-Agent: ".$this->user_agent,
      "Authorization: Basic ".$bearer_token."",
      "Content-Type: application/x-www-form-urlencoded;charset=UTF-8",
      "Content-Length: 29",
      "grant_type=client_credentials"
    );
    // make a cURL with the headers
    $curl_request = curl_init();
    curl_setopt($curl_request, CURLOPT_URL, $url);
    curl_setopt($curl_request, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl_request, CURLOPT_POST, 1);
    curl_setopt($curl_request, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl_request, CURLOPT_POSTFIELDS, "grant_type=client_credentials");
    curl_setopt($curl_request, CURLOPT_HEADER, 0); // get only the body in the response, no headers
    $result = curl_exec($curl_request);
    curl_close($curl_request);
    $decoded_result = json_decode($result);
    return $decoded_result->access_token;
  }

  protected function twitterCurlRequest($get_url, $url)
  {
    $headers = array(
      "GET ".$get_url." HTTP/1.1",
      "Host: api.twitter.com",
      "User-Agent: ".$this->user_agent,
      "Authorization: Bearer ".$this->access_token.""
    );
    $curl_request = curl_init();
    curl_setopt($curl_request, CURLOPT_URL, $url);
    curl_setopt($curl_request, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl_request, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl_request, CURLOPT_HEADER, 0);
    $result = curl_exec($curl_request);
    curl_close($curl_request);
    // if everything went well $result should a JSON string jam-packed with tweets
    return $result;
  }

  protected function getUserTimelineFromParameters($parameters)
  {
    // build a new query from the parameters
    $query = http_build_query($parameters);
    // make the URLs
    $get_url = "/1.1/statuses/user_timeline.json?".$query."";
    $url = "https://api.twitter.com/1.1/statuses/user_timeline.json?".$query."";
    // do it
    return $this->twitterCurlRequest($get_url, $url);
  }


  protected function getUserTimelineFromValidUrl($url)
  {
    $exploded_url = explode("api.twitter.com", $url);
    $get_url = $exploded_url[1];
    return $this->twitterCurlRequest($get_url, $url);
  }

  protected function getUserTimelineFromUsername($username)
  {
    $get_url = "/1.1/statuses/user_timeline.json?screen_name=".$username."";
    $url = "https://api.twitter.com/1.1/statuses/user_timeline.json?screen_name=".$username."";
    return $this->twitterCurlRequest($get_url, $url);
  }

  protected function getUserTimelineFromUserId($user_id)
  {
    $get_url = "/1.1/statuses/user_timeline.json?user_id=".$user_id."";
    $url = "https://api.twitter.com/1.1/statuses/user_timeline.json?user_id=".$user_id."";
    return $this->twitterCurlRequest($get_url, $url);
  }

  protected function getTweetsFromUsernameAndList($user, $list)
  {
    $get_url = "/1.1/lists/statuses.json?owner_screen_name=".$user."&slug=".$list."";
    $url = "https://api.twitter.com/1.1/lists/statuses.json?owner_screen_name=".$user."&slug=".$list."";
    return $this->twitterCurlRequest($get_url, $url);
  }

  protected function twitterRssFromJson($json, $user)
  {
    // error_log(print_r($json));
    $full_name = $user;
    // sanity check so we don't get an error trying to access the name
    if(count($json) > 0){
      $full_name = $json[0]['user']['name'];
    }

    $user = htmlspecialchars($user);
    $full_name = htmlspecialchars($full_name);

    // build the RSS feed
    $output = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
  <rss xmlns:georss=\"http://www.georss.org/georss\" version=\"2.0\" xmlns:twitter=\"http://api.twitter.com\" xmlns:atom=\"http://www.w3.org/2005/Atom\">
    <channel>
      <title>Twitter / ".$user."</title>
      <link>http://twitter.com/".$user."</link>
      <atom:link type=\"application/rss+xml\" href=\"http://api.twitter.com/1/statuses/user_timeline.rss?screen_name=".$user."\" rel=\"self\"/>
      <description>Twitter updates from ".$full_name." / ".$user.".</description>
      <language>en-us</language>
      <ttl>40</ttl>
    ";
    // loop through each item
    for($i=0; $i < count($json); $i++){
      $text = htmlspecialchars($json[$i]['text']);
      // before we build the item, check if there's some place data
      $place_data = "<twitter:place />";
      if($json[$i]['place']){
        $place_data = "<georss:point>".$json[$i]['geo']['coordinates'][0]." ".$json[$i]['geo']['coordinates'][1]."</georss:point>
      <twitter:place />";
      }
      $output .= "<item>
      <title>".$json[$i]['user']['screen_name'].": ".$text."</title>
      <description>".$json[$i]['user']['screen_name'].": ".$text."</description>
      <pubDate>".date(DATE_RFC1123, strtotime($json[$i]['created_at']))."</pubDate>
      <guid>http://twitter.com/".$user."/statuses/".$json[$i]['id']."</guid>
      <link>http://twitter.com/".$user."/statuses/".$json[$i]['id']."</link>
      <twitter:source>".htmlspecialchars($json[$i]['source'])."</twitter:source>
      ".$place_data."
    </item>
    ";
    }
    $output .= "</channel></rss>";
    return $output;
  }


}

?>