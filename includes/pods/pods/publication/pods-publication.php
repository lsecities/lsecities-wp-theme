<?php
namespace LSECitiesWPTheme\publication;

// Exit if accessed directly
if ( !defined('ABSPATH')) exit;

function pods_prepare_publication($pod_slug) {
  // empty stub, for the moment
  return;
}

/**
 * Prepare the ToC of a publication, to be used
 * in side navigation (article pod) or publication pages
 * @param string $pod_slug the slug of the publication Pod
 * @return array data structure with publication ToC data (articles
 * grouped by section, if applicable)
 */
function pods_prepare_table_of_contents($pod_slug) {
  // retrieve pod by slug
  $pod = pods('publication_wrappers', $pod_slug);

  // return if no such pod was found
  if(!$pod->exists()) {
    return;
  }

  // initialize output array
  $obj = array();

  $obj['title'] = $pod->field('name');

  if(count($pod->field('articles'))) {
    $sections = array();
    foreach(preg_split("/\n/", $pod->field('sections')) as $section_line) {
      preg_match("/^(\d+)?\s?(.*)$/", $section_line, $matches);
      array_push($sections, array( 'id' => $matches[1], 'title' => $matches[2]));
    }

    if(!count($sections)) {
      $sections = array("010" => "");
    }

    foreach($sections as $section) {
      $articles = array();

      foreach($pod->field('articles', array('orderby' => 'sequence ASC')) as $article) {

        $article_pod = pods('article', $article['id']);
        $article_lang2 = $article_pod->field('language');

        var_trace(var_export($article_lang2, true), 'article_lang2');

        if(preg_match("/^" . $section['id'] . "/", $article['sequence'])) {
          $this_article = array();

          $this_article['uri'] = PODS_BASEURI_ARTICLES . '/' . $article['slug'] . '/en-gb/';
          $this_article['title'] = $article['name'];
          if(!empty($article_lang2['name'])) {
            $this_article['lang2_uri'] = PODS_BASEURI_ARTICLES . '/' . $article['slug'] . '/' . strtolower($article_lang2['language_code']) . '/';
            $this_article['lang2_title'] = $article['title_lang2'];
            $this_article['lang2_langname'] = $article_lang2['name'];
          }

          $this_article['authors'] = array();

          foreach(sort_linked_field($article_pod->field('authors'), 'family_name', SORT_ASC) as $author) {
            $this_article['authors'][] = $author['name'] . ' ' . $author['family_name'];
          }

          /**
           * TODO:
           * add fields:
           * heading_image
           */
          $articles[] = array(
            'title' => $this_article['title'],
            'uri' => $this_article['uri'],
            'authors' => $this_article['authors'],
            'heading_image' => pods_image_url($article_pod->field('heading_image'), 'original'),
            'lang2_title' => $this_article['lang2_title'],
            'lang2_uri' => $this_article['lang2_uri'],
            'lang2_langname' => $this_article['lang2_langname']
          );
        }
      }

      $obj['sections'][] = array(
        'id' => $section['id'],
        'title' => $section['title'],
        'articles' => $articles
      );
    }
  }

	var_trace(var_export($obj, true), 'PUBLICATION_TOC');

  return $obj;
}
