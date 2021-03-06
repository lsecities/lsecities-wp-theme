<?php
/**
 * Data management for Research Projects
 *
 * @package LSECities2012
 */
namespace LSECitiesWPTheme\research_project;

// Exit if accessed directly
if ( !defined('ABSPATH')) exit;

function pods_prepare_research_project($pod_slug) {
  $pod = pods('research_project');
  $pod->find(array('where' => "t.slug = '" . $pod_slug . "'"));

  if(!$pod->total_found()) {
    redirect_to_404();
  }

  $pod->fetch();

  // for menus etc.
  global $this_pod;
  $this_pod = new \LC\PodObject($pod, 'Research');

  // prepare array for return data structure
  $obj = array();

  lc_data('META_last_modified', $pod->field('modified'));

  var_trace('pod_slug: ' . $pod_slug);

  $obj['title'] = $pod->field('name');
  $obj['tagline'] = $pod->field('tagline');
  $obj['summary'] = do_shortcode($pod->display('summary'));
  $obj['blurb'] = do_shortcode($pod->display('blurb'));
  $obj['keywords'] = $pod->field('keywords');
  $obj['web_uri'] = $pod->field('web_uri');
  
  $obj['project_timespan'] = get_project_timespan($pod);

  $obj['free_form_project_teams'] = $pod->display('free_form_project_teams');
  $obj['free_form_other_actants'] = $pod->display('free_form_other_actants');

  list($obj['project_coordinators'], $obj['project_coordinators_string']) = get_project_people($pod, 'coordinators');
  list($obj['project_researchers'], $obj['project_researchers_string']) = get_project_people($pod, 'researchers');
  list($obj['project_partners'], $obj['project_partners_string']) = get_project_organizations($pod, 'partners');
  list($obj['project_funders'], $obj['project_funders_string']) = get_project_organizations($pod, 'funders');
  
  $obj['research_strand_title'] = $pod->field('research_strand.name');
  $obj['research_strand_summary'] = $pod->display('research_strand.summary');

  $obj['project_status'] = $pod->field('project_status.name');
  
  // featured post
  $featured_post['ID'] = $pod->field('featured_post.ID');
  if($featured_post['ID']) {
    $obj['featured_post']['permalink'] = get_permalink($featured_post['ID']);
    $obj['featured_post']['thumbnail_url'] = wp_get_attachment_url(get_post_thumbnail_id($featured_post['ID']));
    $obj['featured_post']['title'] = get_the_title($featured_post['ID']);
  }

  // hardcoded list of WP categories used to group research outputs linked to a research project
  // TODO: get this list via get_categories()
  $obj['research_output_categories'] = array('book', 'journal-article', 'book-chapter', 'research-data', 'research-report', 'conference-newspaper', 'conference-proceedings', 'conference-report', 'report', 'working-paper', 'blog-post', 'interview', 'magazine-article', 'essay', 'book-review');

  $obj['research_outputs'] = get_project_research_outputs($pod);
  
  // Data visualization collections
  $obj['data_visualization_collections'] = get_project_data_visualization_collections($pod);
  
  // Grid slideshows
  $obj['grid_slideshows'] = get_project_grid_slideshows($pod);
  
  // news
  $obj['project_news'] = get_project_news($pod);
  $obj['news_categories'] = news_categories($pod->field('news_categories'));
    
  // hardcoded list of WP categories used to group events linked to a research project
  $obj['research_external_event_categories'] = array('conference', 'presentation', 'public-lecture', 'workshop');

  // hardcoded list of WP categories used to group event calendars linked to a research project
  // (this variable is currently not used)
  $obj['event_calendar_categories'] = array('lse-cities-event');
  
  // events
  $obj['events_blurb'] = $pod->display('events_blurb');
  $main_calendar_events = get_project_events($pod, TRUE);
  $obj['research_events'] = $main_calendar_events[0];
  $obj['research_external_events'] = get_project_events($pod, FALSE, $obj['research_external_event_categories'], $obj['research_outputs']);
  
  // prepare heading gallery
  $heading_gallery_permalink = $pod->field('heading_gallery.slug');
  $obj['heading_gallery'] = \LSECitiesWPTheme\photo_gallery_get_galleria_data($heading_gallery_permalink, 'fullbleed');

  // if we have research photo galleries/photo essays, prepare them
  $photo_galleries = $pod->field('photo_galleries');
  
  if(is_array($photo_galleries)) {
    foreach($photo_galleries as $photo_gallery) {
      $obj['research_photo_galleries'][] = \LSECitiesWPTheme\photo_gallery_get_galleria_data($photo_gallery['slug'], 'fullbleed wait', TRUE);
    }
  }

  return $obj;
}

/**
 * Generate list of data visualization collections linked to this
 * research project, each including metadata and table of contents
 * 
 * @param Object $pod the Research project Pod object
 * @return Object The data structure with collections of data
 *    visualization collections
 */
function get_project_data_visualization_collections($pod) {
  $data_visualization_collections = $pod->field('data_visualization_collections');
  
  $obj = [];
  
  if(is_array($data_visualization_collections)) {
    foreach($data_visualization_collections as $data_visualization_collection) {
      $obj[] = [
        'publication' => \LSECitiesWPTheme\publication\pods_prepare_publication($data_visualization_collection['slug']),
        'publication_sections' => \LSECitiesWPTheme\publication\pods_prepare_table_of_contents($data_visualization_collection['slug'])
      ];
    }
  }
  
  return $obj;
}

/**
 * Generate list of grid slideshows linked to this
 * research project
 * 
 * @param Object $pod the Research project Pod object
 * @return Object The data structure with collections of grid
 *  slideshows
 */
function get_project_grid_slideshows($pod) {
  $grid_slideshows = $pod->field('grid_slideshows');
  
  $obj = [];
  
  if(is_array($grid_slideshows)) {
    foreach($grid_slideshows as $item) {
      $grid_slideshow_pod = pods('grid_slideshow', $item['permalink']);
      $obj[] = [
        'title' => $item['name'],
        'abstract' => $grid_slideshow_pod->field('parent_article.abstract'),
        'uri' => lc_data('pod_to_route')['grid_slideshow'] . '/' . $grid_slideshow_pod->field('parent_article.slug') . '/en-gb'  // TECHNICAL_DEBT: hardcoded language suffix
      ];
    }
  }
  
  return $obj;
}

function get_project_timespan($pod) {
  // project duration
  $project_duration = '';

  // get years from start and date fields
  try {
    if($pod->field('date_start')) {
      $project_start = new \DateTime($pod->field('date_start') . '-01-01');
      $project_start = $project_start->format('Y');
    }
  } catch (\Exception $e) {
    error_log('Project start year must be a 4-digit number, "' . $pod->field('date_start') . '" was provided.');
  }
  
  try {
    if($pod->field('date_end')) {
      $project_end = new \DateTime($pod->field('date_end') . '-12-31');
      $project_end = $project_end->format('Y');
    }
  } catch (\Exception $e) {
    error_log('Project end year must be a 4-digit number, "' . $pod->field('date_end') . '" was provided.');
  }

  // get freeform duration text, if available
  $project_duration_freeform = $pod->field('duration');

  // if freeform duration is available, use this
  if($project_duration_freeform) {
    $project_duration = $project_duration_freeform;
  } elseif($project_start and $project_end) { // otherwise use start and end year
    // if start and end year are the same, just display the year, otherwise display the range
    if($project_start === $project_end) {
	$project_duration = $project_start;
    } else {
      $project_duration = $project_start . ' - ' . $project_end;
    }
  }
  
  return $project_duration;
}

function get_project_people($pod, $role) {
  // build a list of all current members of staff
  $staff = pods('people_group', 'lsecities-staff');
  $all_staff = $staff->field('members.slug');

  $project_people_string = '';
  $project_people = array();
  
  $project_people_list = $pod->field($role);
  
  if(count($project_people_list)) {
    foreach($project_people_list as $project_person) {
      // add person to array
      $project_people[] = array(
        'slug' => $project_person['slug'],
        'name' => $project_person['name'],
        'family_name' => $project_person['family_name'],
        'uri' => '/' . get_page_uri(2177) . '#p-' . $project_person['slug']
      );
      
      // append person to string, adding link to staff profile if person is current member of staff
      if($project_person['slug'] and array_search($project_person['slug'], $all_staff) !== FALSE) {
        $project_people_string .= "\n" . '<a href="/' . get_page_uri(2177) . '#p-' . $project_person['slug'] . '">';
      }
      $project_people_string .= $project_person['name'] . ' ' . $project_person['family_name'];
      if($project_person['slug'] and array_search($project_person['slug'], $all_staff) !== FALSE) {
        $project_people_string .= '</a>';
      }
      $project_people_string .= ', ';
    }
  }
  $project_people_string = substr($project_people_string, 0, -2);
  return array($project_people, $project_people_string);
}

function get_project_organizations($pod, $role) {
  $project_organizations_string = '';
  $project_organizations = array();
  
  // generate list of organizations
  $project_organizations_list = sort_linked_field($pod->field($role), 'name', SORT_ASC);

  if(count($project_organizations_list)) {
    foreach($project_organizations_list as $project_organization) {
      $project_organizations[] = array(
        'name' => $project_organization['name'],
        'web_uri' => $project_organization['web_uri']
      );
      
      if($project_organization['web_uri'] and preg_match('/^https?:\/\//', $project_organization['web_uri'])) {
        $project_organizations_string .= '<a href="' . $project_organization['web_uri'] . '">' . $project_organization['name'] . '</a>, ';
      } else {
        $project_organizations_string .= $project_organization['name'] . ', ';
      }
    }
  }
  $project_organizations_string = substr($project_organizations_string, 0, -2);
  return array($project_organizations, $project_organizations_string);
}

function get_project_research_outputs($pod) {
  // initialize result array
  $research_outputs = array();
  
  /**
   * First add all the research outputs linked as 'research_outputs'
   * These are mostly publications for which a full 'publication' Pod
   * hasn't been created
   */
  $linked_publications = (array)$pod->field('research_outputs');
  
  if(is_array($linked_publications)) {
    foreach($linked_publications as $publication) {
      $research_output = pods('research_output', $publication['slug']);

      if(!$research_output->exists()) {
        continue;
      }

      $research_outputs[$research_output->field('category.slug')][] = array(
        'slug' => 'research_outputs__' . $research_output->field('slug'),
        'title' => $research_output->field('name'),
        'citation' => $research_output->field('citation'),
        'date' => date_string($research_output->field('date')),
        'uri' => $research_output->field('uri')
      );
    }
  }

  /**
   * Now add to the research outputs found so far all the publications
   * from the publication_wrappers aka Publications pod
   */
  $linked_publications = $pod->field('research_output_publications');
  
  if(is_array($linked_publications)) {
    foreach($linked_publications as $publication) {
      $research_output = pods('publication_wrappers', $publication['slug']);

      if(!$research_output->exists()) {
        continue;
      }
      
      // get ID of WordPress page linked to this publication object
      $linked_wp_page_id = $research_output->field('publication_web_page.ID');
      
      var_trace(var_export($research_output->field('category'), true), 'output category');
      var_trace($linked_wp_page_id, 'publication_web_page.ID');

      $__publication_pdf = $research_output->field('publication_pdf');
      $pdf_uri = $__publication_pdf ? wp_get_attachment_url($__publication_pdf['ID']) : '';
      $pdf_filesize = $__publication_pdf ? sprintf("%0.1f MB", filesize(get_attached_file($__publication_pdf['ID'], TRUE)) / 1e+6 ) : '';
        
      // only add publication to list if publication has a linked WP page; otherwise emit warning
      if($linked_wp_page_id) {
        $research_outputs[$research_output->field('category.slug')][] = array(
          'slug' => $research_output->field('category.slug') . '__' . $research_output->field('slug'),
          'title' => $research_output->field('name'),
          'citation' => $research_output->field('name'),
          'date' => date_string($research_output->field('publishing_date')),
          'uri' => get_permalink($linked_wp_page_id),
          'pdf_uri' => $pdf_uri,
          'pdf_filesize' => $pdf_filesize
        );
      } else {
        trigger_error('No WordPress page linked to Publication with ID ' . $research_output->id(), E_USER_NOTICE);
      }
    }
  }
  
  // Now sort publications within each category, by date first, then by title
  foreach($research_outputs as $category => $ros) {
    foreach($ros as $key => $value) {
      $ro_date[$key] = $value['date'];
      $ro_title[$key] = $value['title'];
    }
    
    array_multisort($ro_date, SORT_DESC, $ro_title, SORT_ASC, $ros);
    
    $sorted_research_outputs[$category] = $ros;
  }
  
  return $sorted_research_outputs;
}

/**
 * Fetch lists of events associated to the project, grouped by category
 * Events from the main LSE Events calendar and events defined as
 * Research outputs can be fetched here.
 * 
 * @param Object $pod The Research Project pod
 * @param bool $include_events_from_main_calendar Whether to fetch any
 *    associated events from the main events calendar (Events pod)
 * @param array $research_event_categories An array of slugs of
 *    WP categories under which additional events are grouped
 * @param array $research_outputs A list of research outputs associated
 *    to the project, from which events are selected
 * @return array The list of events associated to the project, grouped
 *    by category
 */
function get_project_events($pod, $include_events_from_main_calendar = FALSE, $research_event_categories = [], $research_outputs = []) {
  $research_events = [];

  // Only include events from the main calendar if asked to do so
  if($include_events_from_main_calendar and $pod->field('events')) {
    foreach($pod->field('events', array('orderby' => 'date_start DESC')) as $event) {
      $research_events[0][] = array(
        'title' => $event['name'],
        'citation' => $event['name'],
        'date' => $event['date_start'],
        'uri' => PODS_BASEURI_EVENTS . '/' . $event['slug']
      );
    }
  }

  foreach($research_event_categories as $category_slug) {
    if(is_array($research_outputs[$category_slug])) {
      foreach($research_outputs[$category_slug] as $event) {
        $research_events[$category_slug][] = $event;
      }
    }
  }

  // Now sort events within each category, by date
  foreach($research_events as $category => $evs) {
    foreach($evs as $key => $value) {
      $ev_date[$key] = $value['date'];
    }
    
    array_multisort($ev_date, SORT_DESC, $evs);
    
    $sorted_research_events[$category] = $evs;
  }
    
  return $sorted_research_events;
}

function get_project_news($pod) {
  $project_news = array();

  // try linked_posts first
  $linked_posts = $pod->field([ 'name' => 'linked_posts.ID', 'orderby' => 'post_date DESC']);
  
  if(!empty($linked_posts)) {
    foreach($linked_posts as $post) {
      $project_news[] = [
        'permalink' => get_permalink($post),
        'title' => get_the_title($post),
        'date' => get_post_time('j M Y', FALSE, $post)
      ];
    }
    
    var_trace(var_export($project_news, true), 'project_news'); 
    
    return $project_news;
  }
  
  return NULL;
}
