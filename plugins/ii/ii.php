<?php
/*
Plugin Name: Instytut Informatyki
Plugin URI: https://ii.uken.krakow.pl
Description: Wtyczka dedykowana dla II
Version: 1.0
Author: Estarte
License: GNU
*/
function ii_register_resources()
{

    //  wp_register_script("wbsi-jquery", plugins_url("plugins/jquery-1.11.3/jquery3.min.js", __FILE__), array(), "3.5", false);
    // wp_register_script("wbsi-bootstrap", plugins_url("plugins/bootstrap-3.3.5-dist/js/bootstrap.min.js", __FILE__), array(), "3.3.5", false);
    // wp_register_script("wbsi-fontawesome", plugins_url("plugins/fontawesome/all.js", __FILE__), array(), "1.0", false);

    wp_register_script("wbsi-script", plugins_url("script.js", __FILE__), array(), "1.0", false);


    // wp_register_style("wbsi-bootstrap", plugins_url("plugins/bootstrap-3.3.5-dist/css/bootstrap.min.css", __FILE__), array(), "3.3.5", "all");
    // wp_register_style("wbsi-fontawesome", plugins_url("plugins/fontawesome/all.css", __FILE__), array(), "1.0", "all");
    // wp_register_style("wbsi-style", plugins_url("css/style.css", __FILE__), array(), "1.0", "all");

    // wp_register_script("wbsi-4freewall", plugins_url("plugins/4freewall/freewall.js", __FILE__), array(), "1.0", false);
    // wp_register_style("wbsi-4freewallstyle", plugins_url("plugins/4freewall/freewall-style.css", __FILE__), array(), "1.0", "all");

    // wp_enqueue_script("wbsi-jquery");
    // wp_enqueue_script("wbsi-fontawesome");
    // wp_enqueue_script("wbsi-4freewall");
    // wp_enqueue_script("wbsi-object2vr_player");
    // wp_enqueue_script("wbsi-skin");
    wp_enqueue_script("wbsi-script");
    // wp_enqueue_style("wbsi-4freewallstyle");
    // wp_enqueue_style("wbsi-style");
}
add_action('init', 'ii_register_resources');



// Tworzenie Custom Postów
function ii_register_post_types()
{

    // Rejestracja typu wpisu 'Pracownicy'
    $labels_pracownik = array(
        'name'                => 'Pracownicy',
        'singular_name'       => 'Pracownik',
        'menu_name'           => 'Pracownicy',
        'parent_item_colon'   => 'Nadrzędna',
        'all_items'           => 'Wszyscy',
        'view_item'           => 'Zobacz',
        'add_new_item'        => 'Dodaj',
        'add_new'             => 'Dodaj',
        'edit_item'           => 'Edytuj',
        'update_item'         => 'Aktualizuj',
        'search_items'        => 'Szukaj',
        'not_found'           => 'Nie znaleziono',
        'not_found_in_trash'  => 'Nie znaleziono'
    );

    $args_pracownik = array(
        'label' => 'pracownik',
        'rewrite' => array(
            'slug' => 'pracownik'
        ),
        'description'         => 'Pracownicy',
        'labels'              => $labels_pracownik,
        'supports'            => array('title', 'editor', 'thumbnail'),
        'taxonomies'          => array(),
        'hierarchical'        => false,
        'public'              => true,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'show_in_nav_menus'   => true,
        'show_in_admin_bar'   => true,
        'menu_position'       => 8,
        'menu_icon'           => 'dashicons-groups',
        'can_export'          => true,
        'has_archive'         => false,
        'exclude_from_search' => false,
        'publicly_queryable'  => true,
        'map_meta_cap'        => true

    );
    register_post_type('pracownik', $args_pracownik);

    // Rejestracja typu wpisu 'Przedmioty'
    $labels_przedmiot = array(
        'name'                => 'Przedmioty',
        'singular_name'       => 'Przedmiot',
        'menu_name'           => 'Przedmioty',
        'parent_item_colon'   => 'Nadrzędna',
        'all_items'           => 'Wszystkie',
        'view_item'           => 'Zobacz',
        'add_new_item'        => 'Dodaj',
        'add_new'             => 'Dodaj',
        'edit_item'           => 'Edytuj',
        'update_item'         => 'Aktualizuj',
        'search_items'        => 'Szukaj',
        'not_found'           => 'Nie znaleziono',
        'not_found_in_trash'  => 'Nie znaleziono'
    );

    $args_przedmiot = array(
        'label' => 'przedmiot',
        'rewrite' => array(
            'slug' => 'przedmiot'
        ),
        'description'         => 'Przedmioty',
        'labels'              => $labels_przedmiot,
        'supports'            => array('title', 'editor'),
        'taxonomies'          => array(),
        'hierarchical'        => false,
        'public'              => true,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'show_in_nav_menus'   => true,
        'show_in_admin_bar'   => true,
        'menu_position'       => 9,
        'menu_icon'           => 'dashicons-welcome-learn-more',
        'can_export'          => true,
        'has_archive'         => false,
        'exclude_from_search' => false,
        'publicly_queryable'  => true,
        'map_meta_cap'        => true

    );
    register_post_type('przedmiot', $args_przedmiot);

    // Rejestracja typu wpisu 'Roczniki'
    $labels_rocznik = array(
        'name'                => 'Roczniki',
        'singular_name'       => 'Rocznik',
        'menu_name'           => 'Roczniki',
        'parent_item_colon'   => 'Nadrzędna',
        'all_items'           => 'Wszystkie',
        'view_item'           => 'Zobacz',
        'add_new_item'        => 'Dodaj',
        'add_new'             => 'Dodaj',
        'edit_item'           => 'Edytuj',
        'update_item'         => 'Aktualizuj',
        'search_items'        => 'Szukaj',
        'not_found'           => 'Nie znaleziono',
        'not_found_in_trash'  => 'Nie znaleziono'
    );

    $args_rocznik = array(
        'label' => 'rocznik',
        'rewrite' => array(
            'slug' => 'rocznik'
        ),
        'description'         => 'Roczniki',
        'labels'              => $labels_rocznik,
        'supports'            => array('title', 'editor'),
        'taxonomies'          => array(),
        'hierarchical'        => false,
        'public'              => true,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'show_in_nav_menus'   => true,
        'show_in_admin_bar'   => true,
        'menu_position'       => 9,
        'menu_icon'           => 'dashicons-calendar-alt',
        'can_export'          => true,
        'has_archive'         => false,
        'exclude_from_search' => false,
        'publicly_queryable'  => true,
        'map_meta_cap'        => true

    );
    register_post_type('rocznik', $args_rocznik);

    // Rejestracja typu wpisu 'Semestry'
    $labels_semestr = array(
        'name'                => 'Semestry',
        'singular_name'       => 'Semestr',
        'menu_name'           => 'Semestry',
        'parent_item_colon'   => 'Nadrzędna',
        'all_items'           => 'Wszystkie',
        'view_item'           => 'Zobacz',
        'add_new_item'        => 'Dodaj',
        'add_new'             => 'Dodaj',
        'edit_item'           => 'Edytuj',
        'update_item'         => 'Aktualizuj',
        'search_items'        => 'Szukaj',
        'not_found'           => 'Nie znaleziono',
        'not_found_in_trash'  => 'Nie znaleziono'
    );

    $args_semestr = array(
        'label' => 'semestr',
        'rewrite' => array(
            'slug' => 'semestr'
        ),
        'description'         => 'Semestry',
        'labels'              => $labels_semestr,
        'supports'            => array('title', 'editor'),
        'taxonomies'          => array(),
        'hierarchical'        => false,
        'public'              => true,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'show_in_nav_menus'   => true,
        'show_in_admin_bar'   => true,
        'menu_position'       => 9,
        'menu_icon'           => 'dashicons-welcome-learn-more',
        'can_export'          => true,
        'has_archive'         => false,
        'exclude_from_search' => false,
        'publicly_queryable'  => true,
        'map_meta_cap'        => true

    );
    register_post_type('semestr', $args_semestr);

    // Rejestracja typu wpisu 'Wydazenia'
    $labels_wydarzenie = array(
        'name'                => 'Wydarzenia',
        'singular_name'       => 'Wydarzenie',
        'menu_name'           => 'Wydarzenia',
        'parent_item_colon'   => 'Nadrzędne',
        'all_items'           => 'Wszystkie',
        'view_item'           => 'Zobacz',
        'add_new_item'        => 'Dodaj',
        'add_new'             => 'Dodaj',
        'edit_item'           => 'Edytuj',
        'update_item'         => 'Aktualizuj',
        'search_items'        => 'Szukaj',
        'not_found'           => 'Nie znaleziono',
        'not_found_in_trash'  => 'Nie znaleziono'
    );

    $args_wydarzenie = array(
        'label' => 'wydarzenie',
        'rewrite' => array(
            'slug' => 'wydarzenie'
        ),
        'description'         => 'Wydarzenia',
        'labels'              => $labels_wydarzenie,
        'supports'            => array('title', 'editor'),
        'taxonomies'          => array(),
        'hierarchical'        => false,
        'public'              => true,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'show_in_nav_menus'   => true,
        'show_in_admin_bar'   => true,
        'menu_position'       => 9,
        'menu_icon'           => 'dashicons-welcome-learn-more',
        'can_export'          => true,
        'has_archive'         => false,
        'exclude_from_search' => false,
        'publicly_queryable'  => true,
        'map_meta_cap'        => true

    );
    register_post_type('wydarzenie', $args_wydarzenie);
}
add_action('init', 'ii_register_post_types', 0);


// Dodanie stylów globalnych
function enqueue_global_styles() {
    wp_enqueue_style(
        'plugin-global-styles',
        plugin_dir_url(__FILE__) . 'css/style.css',
        array(),
        '1.0',
        'all'
    );
}
add_action('wp_enqueue_scripts', 'enqueue_global_styles');


// Dodanie klas dla stron
add_filter('body_class', function($classes) {
    if (is_page(34041)) {
        $classes[] = 'sylabusy-page';
    }

    if (is_page(11)) {
        $classes[] = 'pracownicy-page';
    }

    if (is_page(14)) {
        $classes[] = 'struktura-page';
    }

    return $classes;
});



include(plugin_dir_path(__FILE__) . 'pracownicy.php');
include(plugin_dir_path(__FILE__) . 'przedmioty.php');
include(plugin_dir_path(__FILE__) . 'roczniki.php');
include(plugin_dir_path(__FILE__) . 'semesters.php');
include(plugin_dir_path(__FILE__) . 'wydarzenia.php');