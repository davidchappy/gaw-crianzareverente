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




?>
