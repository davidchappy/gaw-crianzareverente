<?php
function my_theme_enqueue_styles()
{
    $parent_style = 'parent-style'; // Estos son los estilos del tema padre recogidos por el tema hijo.
    wp_enqueue_style($parent_style, get_template_directory_uri() . '/style.css');
    wp_enqueue_style(
        'child-style',
        get_stylesheet_directory_uri() . '/style.css',
        array($parent_style),
        wp_get_theme()->get('Version')
    );
}
add_action('wp_enqueue_scripts', 'my_theme_enqueue_styles');

/*================================================
#Load custom Blog Module ================================================*/

function divi_custom_blog_module()
{
    get_template_part('/includes/Blog');
    $dcfm = new custom_ET_Builder_Module_Blog();
    remove_shortcode('et_pb_blog');
    add_shortcode('et_pb_blog', array($dcfm, '_shortcode_callback'));
}

add_action('et_builder_ready', 'divi_custom_blog_module');

function divi_custom_blog_class($classlist)
{
    // Blog Module 'classname' overwrite.
    $classlist['et_pb_blog'] = array('classname' => 'custom_ET_Builder_Module_Blog', );
    return $classlist;
}

add_filter('et_module_classes', 'divi_custom_blog_class');

// Enqueue admin JS only on Authors taxonomy admin page
add_action('admin_enqueue_scripts', function ($hook) {
	global $pagenow;
	$screen = get_current_screen();
	$taxonomy = isset($_GET['taxonomy']) ? sanitize_key($_GET['taxonomy']) : ($screen ? $screen->taxonomy : '');
	if ($pagenow === 'edit-tags.php' && $taxonomy === 'author') {
		wp_enqueue_script(
			'crianzareverente-admin-authors',
			get_stylesheet_directory_uri() . '/assets/js/admin-authors.js',
			['jquery'],
			wp_get_theme()->get('Version'),
			true
		);
	}
});

// Improve admin search for Authors taxonomy: ensure display name (term name) is considered
add_action('pre_get_terms', function ($query) {
    if (!is_admin()) {
        return;
    }
    global $pagenow;
    if ($pagenow !== 'edit-tags.php') {
        return;
    }
    if (empty($query->query_vars['taxonomy'])) {
        return;
    }
    $taxonomies = (array) $query->query_vars['taxonomy'];
    if (!in_array('author', $taxonomies, true)) {
        return;
    }

    // Prefer the plugin-provided flag if present; otherwise use the current search box value
    $search = '';
    if (!empty($query->query_vars['custom_author_search'])) {
        $search = trim((string) $query->query_vars['custom_author_search']);
    } elseif (!empty($_GET['s'])) {
        $search = trim((string) wp_unslash($_GET['s']));
    } elseif (!empty($query->query_vars['search'])) {
        $search = trim((string) $query->query_vars['search'], '*');
    }

    if ($search === '') {
        return;
    }

    // Stash the clean search term so our terms_clauses can OR-match t.name
    $query->query_vars['cr_author_name_like'] = $search;
}, 99);

add_filter('terms_clauses', function ($clauses, $taxonomies, $args) {
    if (!is_admin()) {
        return $clauses;
    }
    global $pagenow, $wpdb;
    if ($pagenow !== 'edit-tags.php') {
        return $clauses;
    }
    $taxonomies = (array) $taxonomies;
    if (!in_array('author', $taxonomies, true)) {
        return $clauses;
    }

    // Pull the search string from our pre_get_terms flag or from args
    $search = '';
    if (!empty($args['cr_author_name_like'])) {
        $search = (string) $args['cr_author_name_like'];
    } elseif (!empty($args['custom_author_search'])) {
        $search = (string) $args['custom_author_search'];
    } elseif (!empty($_GET['s'])) {
        $search = (string) wp_unslash($_GET['s']);
    }

    $search = trim($search);
    if ($search === '') {
        return $clauses;
    }

    // Append an OR on term name for display name matching
    $like = '%' . $wpdb->esc_like($search) . '%';
    $clauses['where'] .= $wpdb->prepare(' OR t.name LIKE %s', $like);
    return $clauses;
}, 20, 3);




?>
