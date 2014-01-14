<?php
/**
 * Template Name: Pods - Media Archive search (dev)
 * Description: Media Archive search webapp (dev)
 *
 * @package LSECities2012
 */
?><?php
$TRACE_PREFIX = 'pods-media-archive-search';
$pods_toplevel_ancestor = 306;
wp_enqueue_script('angularjs', get_stylesheet_directory_uri() . '/assets/bower_components/angular/angular.min.js', '', '1.2.8', TRUE);
wp_enqueue_script('media_archive_app', get_stylesheet_directory_uri() . '/assets/js/media-archive-app/js/controllers.js', '', '', TRUE);
?><?php get_header(); ?>

<div role="main" ng-app="mediaArchiveApp">
  <?php if ( have_posts() ) : the_post(); endif; ?>
  <div id="post-<?php the_ID(); ?>" <?php post_class('lc-article lc-media-archive-search'); ?>>
    <div class='ninecol' id='contentarea'>
      <div class='top-content'>
        <?php if(count($heading_slides)) : ?>
        <header class='heading-image'>
          <div class='flexslider wireframe'>
            <ul class='slides'>
              <?php foreach($heading_slides as $slide): ?>
              <li><img src='<?php echo $slide; ?>' /></li>
              <?php endforeach; ?>
            </ul>
          </div>
        </header>
        <?php endif; ?>
        
        <article class="wireframe">
                <section class="clearfix queryarea">
                  <header class="entry-header fourcol">
                    <h1 class="entry-title article-title">Media archive</h1>
                    <h2>Search</h2>
                  </header>
                  <div class="eightcol last">
                    <form method="get" action="">
                      <div class="fourcol">
                        <h3>Format</h3>
                        <ul>
                          <li>
                            <label>
                              <input type="checkbox" value="lecture" disabled="disabled">lecture
                            </label>
                          </li>
                          <li>
                            <label>
                              <input type="checkbox" value="talk" disabled="disabled">talk
                            </label>
                          </li>
                          <li>
                            <label>
                              <input type="checkbox" value="conference-session" disabled="disabled">conference session
                            </label>
                          </li>
                        </ul>
                      </div>
                      <div class="fourcol">
                        <h3>Media</h3>
                        <ul>
                          <li>
                            <label>
                              <input type="checkbox" value="audio" disabled="disabled">audio
                            </label>
                          </li>
                          <li>
                            <label>
                              <input type="checkbox" value="video" disabled="disabled">video
                            </label>
                          </li>
                        </ul>
                      </div>
                      <input ng-model="query" type="text" placeholder="free text search: enter keywords here" name="search" id="query" class="tencol last">
                    </form>
                  </div>
                </section>
                <section class="clearfix">
                  <div class="resultsarea">
                    <h2>Search results</h2>
                    <div id="searchresults"></div>
                  </div>
                </section>
                <section class="ngapp" ng-controller="MediaArchiveCtrl">
                  <ul>
                    <li ng-repeat="item in items | filter:query">
                      <h4>{{item.title}}</h4>
                      <div class="media">
                        <span ng-show="item.youtube_uri"><a href="http://youtu.be/{{item.youtube_uri}}">Watch</a></span>
                        <span ng-show="item.audio_uri"><a href="{{item.audio_uri}}">Listen</a></span>
                      </div>
                      <div class="people">
                        <span ng-show="item.session_speakers">
                          <ul class="run-in comma-separated">
                            Speakers: 
                            <li ng-repeat="speaker in item.session_speakers">{{speaker.name}} {{speaker.family_name}}</li>
                          </ul>
                        </span>
                      </div>
                    </li>
                  </ul>
                </section>
              </article>
              
              
        <aside class='wireframe fourcol last entry-meta' id='keyfacts'>
          &nbsp;
        </aside><!-- #keyfacts -->
      </div><!-- .top-content -->
      <div class='extra-content twelvecol'>
      </div><!-- .extra-content -->
    </div><!-- #contentarea -->
  </div><!-- #post-<?php the_ID(); ?> -->
</div><!-- role='main'.row -->

<?php get_sidebar(); ?>

<?php get_footer(); ?>
