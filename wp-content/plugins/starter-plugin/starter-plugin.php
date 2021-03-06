<?php
/*
Plugin Name: Starter Plugin
Plugin URI: https://github.com/galbus/wordpress-starter
Description: A starter Wordpress plugin
Author: Pressing Space
Version: 0.0.1 (Development)
Author URI: https://pressingspace.com/
Text Domain: starter-plugin
*/
 
/**
 * PressingSpace_StarterPlugin
 */
class PressingSpace_StarterPlugin
{ 
    // Plugin version
    const VER = '0.0.1-dev';

    // Database version
    const DB_VER = 1;

    /**
     * Setup the environment for the plugin
     */
    public function bootstrap()
    {
        // Activate/deactivate hooks
        register_activation_hook( __FILE__, array( $this, 'activate' ) );
        register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );

        // Register custom post types
        add_action( 'init', array( $this, 'register_post_types' ) );

        // Customise the admin menu
        if ( is_admin() ) {
            add_action( 'admin_menu', array( $this, 'remove_admin_menu_items' ) );
        }
    }

    /**
     * Do some stuff upon activation
     * - Add custom user roles
     * - Flush rewrite rules
     */
    public function activate()
    {
        $this->add_roles();
        flush_rewrite_rules();
    }

    /**
     * Undo relevant stuff on deactivation
     * - Remove custom user roles
     * - Flush rewrite rules
     */
    public function deactivate()
    {
        $this->remove_roles();
        flush_rewrite_rules();
    }

    /**
     * Define a custom set of admin roles
     */
    public function get_roles( bool $reverse = false )
    {
        $groups = array(
            'group-1'    => __( 'Group 1' ),
            'group-2'    => __( 'Group 2' ),
            'group-3'    => __( 'Group 3' ),
            'group-4'    => __( 'Group 4' ),
            'group-5'    => __( 'Group 5' ),
            'group-6'    => __( 'Group 6' ),
            'generic'    => __( 'Main Question Board' ),
        );
        if ( $reverse ) {
            return array_reverse( $groups );
        }
        return $groups;
    }

    /**
     * Adds roles so we can organise categories
     */
    public function add_roles()
    {
        foreach ( $this->get_roles(true) as $id => $name ) {
            add_role(
                $id,
                __( $name ),
                array(
                    // role $id is used to grant/restrict access to content
                    $id                      => true,
                    // all roles can access generic
                    'generic'                => true,
                    'read'                   => true,
                    'delete_posts'           => true,
                    'edit_posts'             => true,
                    'delete_published_posts' => false,
                    'publish_posts'          => false,
                    'upload_files'           => false,
                    'edit_published_posts'   => false,
                )
            );
        }
    }

    /**
     * Adds roles so we can organise categories
     */
    public function remove_roles()
    {
        foreach ( $this->get_roles() as $id => $name ) {
            remove_role( $id );
        }
    }

    /**
     * Persist plugin options so we can work out when an upgrade has happened
     */
    public function init_options()
    {
        update_option( 'PressingSpace_StarterPlugin_ver', self::VER );
        add_option( 'PressingSpace_StarterPlugin_db_ver', self::DB_VER );
    }

    /**
     * Display an error message when the plugin deactivates itself.
     */
    public function admin_notices()
    {
        echo '<div class="error">
                <p>The <strong>Starter Plugin</strong> plugin has deactivated itself because a dependent plugin has been disabled.</p>
                <p>Please make sure that the following dependent plugins are all enabled:</p>
                <ul>
                    <li><a href="">Dependent plugin name</a></li>
                </ul>
            </div>';
    }

    /**
     * Register post types and taxonomies
     * @see http://codex.wordpress.org/Function_Reference/register_post_type
     */
    public function register_post_types()
    {
        /**
         * Questions
         */
        $labels = array(
            'name'               => _x( 'Question', 'post type general name', 'starter-plugin' ),
            'singular_name'      => _x( 'Question', 'post type singular name', 'starter-plugin' ),
            'menu_name'          => _x( 'Questions', 'admin menu', 'starter-plugin' ),
            'name_admin_bar'     => _x( 'Question', 'add new on admin bar', 'starter-plugin' ),
            'add_new'            => _x( 'Add New', 'question', 'starter-plugin' ),
            'add_new_item'       => __( 'Add New Question', 'starter-plugin' ),
            'new_item'           => __( 'New Item', 'starter-plugin' ),
            'edit_item'          => __( 'Edit Item', 'starter-plugin' ),
            'view_item'          => __( 'View Item', 'starter-plugin' ),
            'all_items'          => __( 'All Questions', 'starter-plugin' ),
            'search_items'       => __( 'Search Questions', 'starter-plugin' ),
            'parent_item_colon'  => __( 'Parent Item:', 'starter-plugin' ),
            'not_found'          => __( 'No Items found.', 'starter-plugin' ),
            'not_found_in_trash' => __( 'No Items found in Trash.', 'starter-plugin' )
        );
        $args = array(
            'labels'              => $labels,
            'description'         => __( 'Questions' ), 
            'public'              => true,
            'publicly_queryable'  => true,
            'exclude_from_search' => false,
            'show_ui'             => true,
            'query_var'           => true,
            'menu_position'       => 24, 
            'menu_icon'           => 'dashicons-format-status', 
            'rewrite' => array(
                'slug'              => 'questions', /* url slug */
                'with_front'        => true,
            ),
            'has_archive'         => 'questions', /* rename url slug */
            'capability_type'     => 'post',
            'hierarchical'        => false,
            'supports'            => array( 'title', 'editor', 'author', 'revisions', 'comments'),
            'delete_with_user'    => true, // bool (defaults to TRUE if the post type supports 'author')
            'map_meta_cap'        => true, // bool (defaults to FALSE)
        );
        register_post_type( 'question', $args );

        /**
         * Group taxonomy
         */
        register_taxonomy(
            'group', 
            array('question'), 
            array(
                'hierarchical' => true, 
                'labels' => array(
                    'name' => __( 'Groups' ), 
                    'singular_name' => __( 'Group' ), 
                    'search_items' =>  __( 'Search Groups' ), 
                    'all_items' => __( 'All Groups' ), 
                    'parent_item' => __( 'Parent Groups' ), 
                    'parent_item_colon' => __( 'Parent Groups:' ), 
                    'edit_item' => __( 'Edit Group' ), 
                    'update_item' => __( 'Update Group' ), 
                    'add_new_item' => __( 'Add New Group' ), 
                    'new_item_name' => __( 'New Group Name' ) 
                ),
                'rewrite' => array( 'slug' => 'groups' ),
                'show_admin_column' => true,
                'show_ui' => true,
                'query_var' => 'groups',
            )
        );
    }

    /**
     * Remove some of the admin menu options
     */
    public function remove_admin_menu_items()
    {
        remove_menu_page('edit.php');
    }
}
 
global $PressingSpace_StarterPlugin;
$PressingSpace_StarterPlugin = new PressingSpace_StarterPlugin();
$PressingSpace_StarterPlugin->bootstrap();
