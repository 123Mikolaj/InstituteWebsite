<?php
/*
Plugin Name: read sylabus
Plugin URI: https://ii.uken.krakow.pl
Description: Wtyczka dedykowana dla II
Version: 1.0
Author: Estarte
License: GNU
*/

function sylabusy_menu()
{
  add_menu_page(
    'Sylabusy',           // Page title
    'Sylabusy',           // Menu title
    'manage_options',     // Capability
    'sylabusy_start',     // Menu slug
    'sylabusy_start',     // Function to display the page content
    '',                   // Icon URL (leave empty for default)
    4                     // Menu position
  );
}
add_action('admin_menu', 'sylabusy_menu');

function sylabusy_start()
{
  // Get search query
  $search_query = isset($_GET['sylabus_search']) ? sanitize_text_field($_GET['sylabus_search']) : '';

  // Get all subjects (przedmiot) with search query
  $args = array(
    'post_type' => 'przedmiot',
    'posts_per_page' => -1,
    's' => $search_query
  );
  $przedmioty = get_posts($args);

  // Create an array to hold the subjects with their semester info and similarity score
  $subjects = array();

  foreach ($przedmioty as $przedmiot) {
    // Retrieve the sylabus file ID or URL
    $sylabus_id = get_post_meta($przedmiot->ID, 'ii_file', true);
    $sylabus_url = $sylabus_id ? wp_get_attachment_url($sylabus_id) : '#';
    $sylabus_text = $sylabus_id ? basename($sylabus_url) : 'Brak pliku';

    // Get the permalink for the przedmiot
    $przedmiot_permalink = get_permalink($przedmiot->ID);

    // Retrieve the semesters for this subject
    $semesters = get_post_meta($przedmiot->ID, 'ii_semestry', true);

    // Convert semester titles to numeric values for sorting
    $semester_values = array();
    if (!empty($semesters) && is_array($semesters)) {
      foreach ($semesters as $semester_id) {
        $semester_post = get_post($semester_id);
        if ($semester_post && $semester_post->post_status != 'trash') {
          $semester_title = $semester_post->post_title;
          // Extract numeric part from the semester title, e.g., "Semestr 1" => 1
          if (preg_match('/(\d+)/', $semester_title, $matches)) {
            $semester_values[] = intval($matches[1]);
          }
        }
      }
    }

    // Get the minimum semester value for sorting
    $min_semester = !empty($semester_values) ? min($semester_values) : PHP_INT_MAX;

    // Calculate similarity score
    $similarity_score = similarity_score($search_query, $przedmiot->post_title);

    $subjects[] = array(
      'ID' => $przedmiot->ID,
      'title' => $przedmiot->post_title,
      'sylabus_url' => $sylabus_url,
      'sylabus_text' => $sylabus_text,
      'permalink' => $przedmiot_permalink,
      'semester_list' => $semesters, // Store original semester list for display
      'semester' => $min_semester,
      'similarity_score' => $similarity_score
    );
  }

  // Sort by similarity score (highest first), then by semester (lowest first)
  usort($subjects, function ($a, $b) {
    // First sort by similarity score (highest first)
    if ($b['similarity_score'] != $a['similarity_score']) {
      return $b['similarity_score'] - $a['similarity_score'];
    }
    // Then sort by semester (lowest first)
    return $a['semester'] - $b['semester'];
  });

  echo '<h2>Sylabusy</h2>';
  echo '<form method="get" action="">';
  echo '<input type="hidden" name="page" value="sylabusy_start">';
  echo '<input type="text" name="sylabus_search" value="' . esc_attr($search_query) . '" placeholder="Szukaj przedmiotów...">';
  echo '<input type="submit" value="Szukaj">';
  echo '</form>';

  echo '<table class="widefat striped">';
  echo '<thead>';
  echo '<tr>';
  echo '<th>Przedmiot</th>';
  echo '<th>Sylabus</th>';
  echo '<th>Semestr</th>';
  echo '</tr>';
  echo '</thead>';
  echo '<tbody>';

  // Check if there are any subjects
  if (empty($subjects)) {
    echo '<tr><td colspan="3">Nie znaleziono przedmiotów.</td></tr>';
  } else {
    foreach ($subjects as $subject) {
      // Display the data
      $semester_list = '';
      if (!empty($subject['semester_list']) && is_array($subject['semester_list'])) {
        foreach ($subject['semester_list'] as $semester_id) {
          $semester_post = get_post($semester_id);
          if ($semester_post && $semester_post->post_status != 'trash') {
            $semester_permalink = get_permalink($semester_post->ID);
            $semester_list .= '<a href="' . esc_url($semester_permalink) . '">' . esc_html($semester_post->post_title) . '</a><br>';
          }
        }
      } else {
        $semester_list = 'Brak semestrów';
      }

      echo '<tr>';
      echo '<td><a href="' . esc_url($subject['permalink']) . '">' . esc_html($subject['title']) . '</a></td>'; // Link to subject page
      echo '<td><a href="' . esc_url($subject['sylabus_url']) . '" target="_blank">' . esc_html($subject['sylabus_text']) . '</a></td>'; // Display sylabus link
      echo '<td>' . $semester_list . '</td>'; // Display original semester list
      echo '</tr>';
    }
  }

  echo '</tbody>';
  echo '</table>';
}

function similarity_score($search, $text)
{
  // Use Levenshtein distance to calculate similarity score
  $distance = levenshtein(strtolower($search), strtolower($text));
  // The maximum possible distance is the length of the longer string
  $max_len = max(strlen($search), strlen($text));
  // Calculate similarity score as a percentage
  return ($max_len - $distance) / $max_len * 100;
}
