<?php
/**
 * Template Name: Pods - Research project
 * Description: The template used for Research projects
 *
 * @package LSECities2012
 */
?><?php
/* URI: /objects/research-projects */
$BASE_URI = PODS_BASEURI_RESEARCH_PROJECTS;
$TRACE_ENABLED = is_user_logged_in();
global $IN_CONTENT_AREA, $HIDE_CURRENT_PROJECTS, $HIDE_PAST_PROJECTS;
$TRACE_PREFIX = 'pods-research-projects';
$pods_toplevel_ancestor = 306;

$pod_slug = get_post_meta($post->ID, 'pod_slug', true);
if($pod_slug) {
  $pod_from_page = true;
} else {
  $pod_slug = pods_url_variable(2);
}

if(!$pod_from_page) {
  // set toplevel ancestor explicitly as we are outside of WP's hierarchy
  global $pods_toplevel_ancestor;

  $pods_toplevel_ancestor = 306;
}

var_trace('pod_slug: ' . $pod_slug, $TRACE_PREFIX, $TRACE_ENABLED);
$pod = new Pod('research_project', $pod_slug);

global $this_pod;
$this_pod = new LC\PodObject($pod, 'Research');

$pod_title = $pod->get_field('name');
$pod_tagline = $pod->get_field('tagline');
$web_uri = $pod->get_field('web_uri');
$pod_summary = do_shortcode($pod->get_field('summary'));
$pod_blurb = do_shortcode($pod->get_field('blurb'));
$pod_keywords = $pod->get_field('keywords');

try {
  if($pod->get_field('date_start')) { 
    $project_start = new DateTime($pod->get_field('date_start') . '-01-01');
    $project_start = $project_start->format('Y');
  }
  
  if($pod->get_field('date_end')) {
    $project_end = new DateTime($pod->get_field('date_end') . '-12-31');
    $project_end = $project_end->format('Y');
  }
} catch (Exception $e) {}

// build a list of all current members of staff
$staff = new Pod('people_group', 'lsecities-staff');
$all_staff = $staff->get_field('members.slug');
var_trace($all_staff, 'all_staff');

$project_coordinators_list = $pod->get_field('coordinators');
$project_coordinators_count = count($project_coordinators_list);
foreach($project_coordinators_list as $project_coordinator) {
  if($project_coordinator['slug'] and array_search($project_coordinator['slug'], $all_staff) !== FALSE) {
    $project_coordinators .= "\n" . '<a href="/' . get_page_uri(2177) . '#p-' . $project_coordinator['slug'] . '">';
  }
  $project_coordinators .= $project_coordinator['name'] . ' ' . $project_coordinator['family_name'];
  if($project_coordinator['slug'] and array_search($project_coordinator['slug'], $all_staff) !== FALSE) {
    $project_coordinators .= '</a>';
  }
  $project_coordinators .= ', ';
}
$project_coordinators = substr($project_coordinators, 0, -2);

$project_researchers_list = $pod->get_field('researchers');
$project_researchers_count = count($project_researchers_list);
foreach($project_researchers_list as $project_researcher) {
  if($project_researcher['slug'] and array_search($project_researcher['slug'], $all_staff) !== FALSE) {
    $project_researchers .= "\n" . '<a href="/' . get_page_uri(2177) . '#p-' . $project_researcher['slug'] . '">';
  }
  $project_researchers .= $project_researcher['name'] . ' ' . $project_researcher['family_name'];
  if($project_researcher['slug'] and array_search($project_researcher['slug'], $all_staff) !== FALSE) {
    $project_researchers .= '</a>';
  }
  $project_researchers .= ', ';
}
$project_researchers = substr($project_researchers, 0, -2);

$project_partners_list = $pod->get_field('contributors');
$project_partners_count = count($project_partners_list);
foreach($project_partners_list as $project_partner) {
  $project_partners .= $project_partner['name'] . ', ';
}
$project_partners = substr($project_partners, 0, -2);

$research_strand_title = $pod->get_field('research_strand.name');
$research_strand_summary = $pod->get_field('research_strand.summary');

$project_status = $pod->get_field('project_status.name');

// prepare heading gallery
$gallery = galleria_prepare($pod, 'fullbleed wireframe');

// if we have research photo galleries/photo essays, prepare them
$research_photo_galleries = galleria_prepare_multi($pod, 'fullbleed wireframe', 'photo_galleries');

$news_categories = news_categories($pod->get_field('news_category'));

if($pod->get_field('events')) {
  $events = $pod->get_field('events');
}
?><?php get_header(); ?>

<div role="main">
  <?php if ( have_posts() ) : the_post(); endif; ?>
  <div id="post-<?php the_ID(); ?>" <?php post_class('lc-article lc-research-project'); ?>>
    <div class='ninecol' id='contentarea'>
      <div class='top-content'>
        <?php if(count($gallery['slides'])) : ?>
        <header class='heading-image'>
          <?php include('inc/components/galleria.inc.php'); ?>
        </header>
        <?php endif; ?>
        
        <article class='wireframe eightcol row'>
          <header class='entry-header'>
            <h1><?php echo $pod_title; ?></h1>
            <?php if($pod_tagline): ?><h2><?php echo $pod_tagline; ?></h2><?php endif ; ?>
            <?php if($pod_summary): ?>
            <div class="abstract"><?php echo $pod_summary; ?></div>
            <?php endif; ?>
            
            <?php if((is_array($pod->get_field('news_category')) and count($pod->get_field('news_category')) > 0) or count($events)): ?>
            <script>jQuery(function() { jQuery("article").organicTabs(); });</script>
            <ul class="nav organictabs row">
              <li class="threecol"><a class="current" href="#project-info">Research profile</a></li>
              <?php if(is_array($pod->get_field('news_category')) and count($pod->get_field('news_category')) > 0): ?>
              <li class="threecol"><a href="#news_area">Project news</a></li>
              <?php endif; ?>
              <?php if(count($events)): ?>
              <li class="threecol"><a href="#linked-events">Events</a></li>
              <?php endif; ?>
            </ul>
            <?php endif; ?>
          </header>
          <div class='entry-content article-text list-wrap'>
            <section id="project-info">
              <?php echo $pod->get_field('blurb'); ?>
                <?php
                var_trace($research_photo_galleries, 'research_photo_galleries');
                if(count($research_photo_galleries)): ?>
                <section id="photo-essays">
                  <header><h1>Photo essays</h1></header>
                  <?php
                  foreach($research_photo_galleries as $key => $gallery): ?>
                    <div class="sixcol<?php if((($key + 1) % 2) == 0): ?> last<?php endif; ?>">
                    <?php
                    include('inc/components/galleria.inc.php'); ?>
                    </div>
                    <?php
                  endforeach; // ($research_photo_galleries as $key => $gallery) ?>
                </section>
                <?php
                endif; ?>
              </section>
            </section>
            <?php
            if(true):
              if(is_array($pod->get_field('news_category')) and count($pod->get_field('news_category')) > 0):
              // latest news in categories defined for this research project
              $more_news = new WP_Query('posts_per_page=10' . news_categories($pod->get_field('news_category'))); ?>
              <section id="news_area" class="hide">
                <header><h1>Project news</h1></header>
                <ul>
                <?php
                    while ($more_news->have_posts()) :
                      $more_news->the_post();
                ?>
                  <li><a href="<?php the_permalink(); ?>"><?php the_time('j M'); ?> | <?php the_title() ?></a></li>
                <?php
                    endwhile;
                ?>
                </ul>
              </section> <!-- #news_area -->
            <?php
             endif; // ($pod->get_field('news_category')) and count($pod->get_field('news_category')) > 0)
            // linked events
            if(count($events)): ?>
            <section id="linked-events" class="hide">
              <header><h1>Events</h1></header>
              <ul>
            <?php
              foreach($events as $event): ?>
              <li>
                <a href="<?php echo PODS_BASEURI_EVENTS . '/' . $event['slug']; ?>"><?php echo date('j F Y', strtotime($event['date_start'])) . ' | ' . $event['name']; ?></a>
              </li>
            <?php endforeach; // ($events as $event) ?>
              </ul>
            </section>
            <?php
            endif; // (count($events)) 
            endif; // (is_user_logged_in()) ?>
          </div> <!-- .entry-content.article-text -->
        </article>
        <aside class='wireframe fourcol last entry-meta' id='keyfacts'>
          <dl>
          <?php if($web_uri): ?>
            <dt>Website</dt>
            <dd><a href="<?php echo $web_uri; ?>"><?php echo $web_uri; ?></a></dd>
          <?php endif; ?>
          <?php if($project_coordinators): ?>
            <dt>Project <?php echo $project_coordinators_count > 1 ?'coordinators' : 'coordinator'; ?></dt>
            <dd><?php echo $project_coordinators; ?></dd>
          <?php endif; ?>
          <?php if($project_researchers): ?>
            <dt><?php echo $project_researchers_count > 1 ? 'Researchers' : 'Researcher'; ?></dt>
            <dd><?php echo $project_researchers; ?></dd>
          <?php endif; ?>
          <?php if($project_partners): ?>
            <dt>Project <?php echo $project_partners_count > 1 ? 'partners' : 'partner'; ?></dt>
            <dd><?php echo $project_partners; ?></dd>
          <?php endif; ?>
          <?php if($research_strand_title): ?>
            <dt>Research strand</dt>
            <dd><?php echo $research_strand_title; ?></dd>
          <?php endif; ?>
          <?php if($project_start and $project_end): ?>
            <dt>Duration</dt>
            <dd><?php echo $project_start; ?> - <?php echo $project_end; ?></dd>
          <?php endif; ?>
          <?php if($pod_keywords): ?>
            <dt>Keywords</dt>
            <dd><?php echo $pod_keywords; ?></dd>
          <?php endif; ?>
          </dl>
        </aside><!-- #keyfacts -->
      </div><!-- .top-content -->
      <div class='extra-content twelvecol'>
      </div><!-- .extra-content -->
    </div><!-- #contentarea -->
    <?php
      $IN_CONTENT_AREA = false;
      if($project_status == 'active') {
        $HIDE_CURRENT_PROJECTS = false;
        $HIDE_PAST_PROJECTS = true;
      } else {
        $HIDE_CURRENT_PROJECTS = true;
        $HIDE_PAST_PROJECTS = false;
      }
      get_template_part('nav'); ?>
  </div><!-- #post-<?php the_ID(); ?> -->

</div><!-- role='main'.row -->

<?php get_sidebar(); ?>

<?php get_footer(); ?>
