<?php
/**
 * Template Name: Pods - Conference
 * Description: The template used for Conference Pods
 *
 * @package LSECities2012
 */
namespace LSECitiesWPTheme\conference;

/**
 * Pods initialization
 */

$obj = prepare_conference(get_post_meta($post->ID, 'pod_slug', true));

/**
 * Copy gallery object to own variable for compatibility with
 * gallery partial.
 */
$gallery = $obj['gallery'];

?><?php get_header(); ?>

<div role="main">

<?php if ( have_posts() ) : the_post(); endif; ?>

<div id="post-<?php the_ID(); ?>" <?php post_class('lc-article lc-conference-frontpage'); ?>>

  <?php \SemanticWP\Templating::get_template_part('lsecities/_conference', $obj); ?>

          <?php get_template_part('nav'); ?>

</div><!-- #post-<?php the_ID(); ?> -->
</div>

<?php get_sidebar(); ?>

<?php get_footer(); ?>
