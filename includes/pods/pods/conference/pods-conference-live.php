<?php

// Exit if accessed directly
if ( !defined('ABSPATH')) exit;

function pods_prepare_conference_live($pod_slug) {
  $pod = pods('conference', $pod_slug);
  
  $obj = array();
  
  $obj['live_streaming_video_embedcode'] = $pod->field('live_streaming_video_embedcode');
  $obj['live_streaming_notice'] = $pod->field('live_streaming_notice');
  $obj['live_twitter_querystring'] = $pod->field('live_twitter_querystring');
  $obj['live_twitter_widget_id'] = $pod->field('live_twitter_widget_id');
  
  $live_storify_stories_uris = preg_replace('/^https?:\/\//', '', explode("\n", $pod->field('live_storify_stories')));

  foreach($live_storify_stories_uris as $story_uri) {
    $obj['live_storify_stories'] .= '<script src="//' . $story_uri . '.js?template=slideshow"></script>
    <noscript>[<a href="//' . $story_uri . '" target="_blank">View the story on Storify</a>]</noscript>
    ';
  }
  
  return $obj;
}
