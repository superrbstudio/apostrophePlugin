<?php

class aTwitterPassthroughAdapter extends sfFopenAdapter
{
  public function call($browser, $uri, $method = 'GET', $parameters = array(), $headers = array())
  {    
    error_log("hi");
    // https://api.twitter.com/1/statuses/user_timeline.rss?screen_name=bigredtim
    if (preg_match('/api.twitter.com\/1\/.*?rss/', $uri))
    {
      error_log("about to construct converter");
      $legacyConverter = new aTwitterLegacyConverter($this->options['consumer_key'], $this->options['consumer_secret'], $this->options['user_agent']);

      $body = $legacyConverter->getRSSFeedFromURL($uri);
      error_log("got the body");
      if (is_null($body)) {
        $browser->setResponseCode(404);
        return $browser;
      }
      $browser->setResponseCode(200);
      $browser->setResponseText($body);
      return $browser;
    }
    else
    {
      return parent::call($browser, $uri, $method, $parameters, $headers);
    }
  }
}
