<?php
/**
 * Template Name: Pods - Event programme
 * Description: The template used for event programmes
 *
 * @package LSECities2012
 */
?>

<?php
  /* URI: TBD */
  $TRACE_ENABLED = is_user_logged_in();
  $TRACE_PREFIX = 'pods-event-programme.php -- ';
  $pod_slug = get_post_meta($post->ID, 'pod_slug', true);
  if($TRACE_ENABLED) { error_log($TRACE_PREFIX . 'pod_slug: ' . var_export($publication_slug, true)); }
  $pod = new Pod('event_programme', $pod_slug);
  $pod_title = $pod->get_field('name');
  if($TRACE_ENABLED) { error_log($TRACE_PREFIX . 'title: ' . var_export($pod_title, true)); }
  $pod_subtitle = $pod->get_field('programme_subtitle');
  if($TRACE_ENABLED) { error_log($TRACE_PREFIX . 'subtitle: ' . var_export($subtitle, true)); }
  $subsessions = $pod->get_field('sessions.slug');
  if(count($subsessions) == 1) { $subsessions = array(0 => $subsessions); }
  
  /**
   * If we use special fields from the speakers objects to generate
   * speaker blurb and affiliation information for the programme.
   * The following field will be set and contain the prefix for the
   * fields to use in the people pod.
   * e.g. if the special_ec2012 prefix is provided, we expect to
   * fetch speaker blurb and affiliation from the following fields
   * in the people pod:
   * - special_ec2012_blurb
   * - special_ec2012_affiliation
  */
  $special_fields_prefix = $pod->get_field('special_author_fields');
  var_trace($special_fields_prefix, 'special_fields_prefix');
  
  $for_conference = $pod->get_field('for_conference.slug');
  $for_event = $pod->get_field('for_event.slug');
  
  $page_title = !empty($for_conference) ? "Conference programme" : "Event programme";
  
  if($TRACE_ENABLED) { error_log($TRACE_PREFIX . 'sessions: ' . var_export($subsessions, true)); }
  
function process_session($session_slug) {
  global $TRACE_ENABLED;
  global $special_fields_prefix;
  var_trace($special_fields_prefix, 'special_fields_prefix');
  $ALLOWED_TAGS_IN_BLURBS = '<strong><em>';
  
  $pod = new Pod('event_session', $session_slug);
  
  $session_id = $pod->get_field('slug');
  $session_title = $pod->get_field('name');
  $session_subtitle = $pod->get_field('session_subtitle');
  $show_times = $pod->get_field('show_times');
  $session_start = new DateTime($pod->get_field('start'));
  $session_start = $session_start->format('H:i');
  $session_end = new DateTime($pod->get_field('end'));
  $session_end = $pod->get_field('end') == '0000-00-00 00:00:00' ? null : $session_end->format('H:i');
  if($pod->get_field('show_times')) {
    $session_times = is_null($session_end) ? "$session_start&#160;&#160;&#160;" : "$session_start &#8212; $session_end&#160;&#160;&#160;";
  }
  $hide_title = $pod->get_field('hide_title');
  $session_type = $pod->get_field('session_type.slug');
  if($session_type != 'session') { $session_type = "session $session_type"; }
  $session_speakers = $pod->get_field('speakers');
  
  /* If we have event-specific author info, use this */
  if($special_fields_prefix) {
    foreach($session_speakers as $this_speaker) {
      $blurb = '';
      $session_speakers_blurb .= $this_speaker['name'] . ' ' . $this_speaker['family_name'];
      $blurb = $this_speaker[$special_fields_prefix . '_blurb'];
      
      /* if no event-specific blurb is available for speaker, fetch
       * generic speaker role and affiliation information from their
       * record */
      if(!$blurb) { 
        $this_speaker_role = $this_speaker['role'];
        $this_speaker_affiliation = $this_speaker['organization'];
        if($this_speaker_role and $this_speaker_affiliation) {
          $blurb = $this_speaker_role . ', ' . $this_speaker_affiliation;
        } elseif($this_speaker_affiliation) {
          $blurb = $this_speaker_affiliation;
        }
      }
      
      /* if any blurb is available, add it to the session speakers blurb */
      if($blurb) {
        $session_speakers_blurb .= ', ' . $blurb;
      }
      
      /* add separator comma */
      $session_speakers_blurb .= ', ';
    }
    
    /* remove trailing comma */
    $session_speakers_blurb = preg_replace('/, $/', '', $session_speakers_blurb);
    var_trace($session_speakers_blurb, 'session_speakers_blurb');
  } elseif($pod->get_field('speakers_blurb')) { /* otherwise, if per-session blurb is available, use this */
    $session_speakers_blurb = strip_tags($pod->get_field('speakers_blurb'), $ALLOWED_TAGS_IN_BLURBS);
  }
  
  $session_chairs = $pod->get_field('chairs');
  $session_chairs_blurb = strip_tags($pod->get_field('chairs_blurb'), $ALLOWED_TAGS_IN_BLURBS);
  $session_respondents = $pod->get_field('respondents');
  $session_respondents_blurb = strip_tags($pod->get_field('respondents_blurb'), $ALLOWED_TAGS_IN_BLURBS);
  $session_youtube_video = $pod->get_field('media_items.youtube_uri');
  $session_slides = $pod->get_field('media_items.slides_uri');
  $subsessions = $pod->get_field('sessions.slug');
  if($subsessions and count($subsessions) == 1) { $subsessions = array(0 => $subsessions); }
  if($TRACE_ENABLED) { error_log($TRACE_PREFIX . 'session count: ' . count($subsessions)); }
  if($TRACE_ENABLED) { error_log($TRACE_PREFIX . 'sessions: ' . var_export($subsessions, true)); }
  echo "<section id='$session_id' class='$session_type'>";
  if($session_title and !$hide_title) { echo '<header><h1>' . $session_times . $session_title . '</h1></header>'; }
  if($session_subtitle and !$hide_title) { echo "<h3>$session_subtitle</h3>"; }
  if($session_chairs) {
    $caption = count($session_chairs) > 1 ? "Chairs" : "Chair";
    echo "<dl class='session-chairs'><dt>$caption: </dt><dd>$session_chairs_blurb</dd></dl>";
  }
  if($session_speakers) {
    echo "<div>$session_speakers_blurb</div>";
  }
  if($session_youtube_video or $session_slides) {
    echo '<ul class="mediaitems">';
    if($session_youtube_video) {
      echo "<li class='link video'><a class='watchvideo onyoutube' href='http://youtube.com/watch?v=$session_youtube_video'>Watch video</a></li>";
    }
    if($session_slides) {
      echo "<li class='link slides'><a class='downloadthis pdf' href='http://downloads0.cloud.lsecities.net/$session_slides'>Browse slides</a></li>";
    }
    echo '</ul>';
  }
  if($subsessions) {
    foreach($subsessions as $session) {
      process_session($session);
    }
  }
  echo "</section><!-- #$session_id -->";
}
?>

<?php get_header(); ?>

<div role="main" class="row">

<article id="post-<?php the_ID(); ?>" <?php post_class('ninecol lc-article lc-event-programme'); ?>>
  <header class="entry-header">
    <h1 class="entry-title"><?php echo $pod_title; ?></h1>
    <?php if($pod_subtitle) : ?>
    <h2><?php echo $pod_subtitle; ?></h2>
    <?php endif ; ?>
  </header><!-- .entry-header -->
	<div class="entry-content">
    <div id="contentarea">
    <h1><?php echo $page_title; ?></h1>

    <?php if(!empty($pod->data)) : ?>
      <div class="article row">
        <div class="ninecol event-programme">
          <?php
          foreach($subsessions as $session) {
            process_session($session);
          }
          ?>
        </div>
        <div class="threecol last">
          <div>
          </div>
        </div>
      </div>
    <?php endif ?>    
		<?php wp_link_pages( array( 'before' => '<div class="page-link"><span>' . __( 'Pages:', 'twentyeleven' ) . '</span>', 'after' => '</div>' ) ); ?>
    </div><!-- #contentarea -->
	</div><!-- .entry-content -->
</article><!-- #post-<?php the_ID(); ?> -->

<?php get_template_part('nav'); ?>

</div><!-- role='main'.row -->

<?php get_sidebar(); ?>

<?php get_footer(); ?>
