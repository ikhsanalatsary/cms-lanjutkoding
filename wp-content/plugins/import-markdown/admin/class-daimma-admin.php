<?php

/*
 * this class should be used to work with the administrative side of wordpress
 */

class Daimma_Admin
{

    protected static $instance = null;
    private $shared = null;

    private $screen_id_import = null;
    private $screen_id_help = null;
    private $screen_id_pro_version = null;
    private $screen_id_options = null;

    public $regex_capability = '/^\s*[A-Za-z0-9_]+\s*$/';

    private function __construct()
    {

        //assign an instance of the plugin info
        $this->shared = Daimma_Shared::get_instance();

        //Load admin stylesheets and JavaScript
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_styles'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));

        //Add the admin menu
        add_action('admin_menu', array($this, 'me_add_admin_menu'));

        //Load the options API registrations and callbacks
        add_action('admin_init', array($this, 'op_register_options'));

        //this hook is triggered during the creation of a new blog
        add_action('wpmu_new_blog', array($this, 'new_blog_create_options_and_tables'), 10, 6);

        //this hook is triggered during the deletion of a blog
        add_action('delete_blog', array($this, 'delete_blog_delete_options_and_tables'), 10, 1);

    }

    /*
     * return an instance of this class
     */
    public static function get_instance()
    {

        if (null == self::$instance) {
            self::$instance = new self;
        }

        return self::$instance;

    }

    public function enqueue_admin_styles()
    {

        $screen = get_current_screen();

        //menu import
        if ($screen->id == $this->screen_id_import) {
            wp_enqueue_style($this->shared->get('slug') . '-import-menu',
                $this->shared->get('url') . 'admin/assets/css/import.css', array(), $this->shared->get('ver'));
            wp_enqueue_style($this->shared->get('slug') . '-framework-menu',
                $this->shared->get('url') . 'admin/assets/css/framework/menu.css', array(), $this->shared->get('ver'));
        }

	    //Menu Help
	    if ( $screen->id == $this->screen_id_help ) {

		    wp_enqueue_style( $this->shared->get( 'slug' ) . '-menu-help',
			    $this->shared->get( 'url' ) . 'admin/assets/css/menu-help.css', array(), $this->shared->get( 'ver' ) );

	    }

	    //Menu Pro Version
	    if ( $screen->id == $this->screen_id_pro_version ) {

		    wp_enqueue_style( $this->shared->get( 'slug' ) . '-menu-pro-version',
			    $this->shared->get( 'url' ) . 'admin/assets/css/menu-pro-version.css', array(), $this->shared->get( 'ver' ) );

	    }

        //menu options
        if ($screen->id == $this->screen_id_options) {
            wp_enqueue_style($this->shared->get('slug') . '-framework-options',
                $this->shared->get('url') . 'admin/assets/css/framework/options.css', array(),
                $this->shared->get('ver'));
            wp_enqueue_style($this->shared->get('slug') . '-jquery-ui-tooltip',
                $this->shared->get('url') . 'admin/assets/css/jquery-ui-tooltip.css', array(),
                $this->shared->get('ver'));
            wp_enqueue_style($this->shared->get('slug') . '-chosen',
                $this->shared->get('url') . 'admin/assets/inc/chosen/chosen-min.css', array(),
                $this->shared->get('ver'));
            wp_enqueue_style($this->shared->get('slug') . '-chosen-custom',
                $this->shared->get('url') . 'admin/assets/css/chosen-custom.css', array(), $this->shared->get('ver'));
        }

    }

    /*
     * enqueue admin-specific javascript
     */
    public function enqueue_admin_scripts()
    {

        $screen = get_current_screen();

        //menu options
        if ($screen->id == $this->screen_id_options) {
            wp_enqueue_script('jquery-ui-tooltip');
            wp_enqueue_script($this->shared->get('slug') . '-jquery-ui-tooltip-init',
                $this->shared->get('url') . 'admin/assets/js/jquery-ui-tooltip-init.js', 'jquery',
                $this->shared->get('ver'));
            wp_enqueue_script($this->shared->get('slug') . '-chosen-init',
                $this->shared->get('url') . 'admin/assets/js/chosen-init.js', array('jquery'),
                $this->shared->get('ver'));
            wp_enqueue_script($this->shared->get('slug') . '-chosen',
                $this->shared->get('url') . 'admin/assets/inc/chosen/chosen-min.js', array('jquery'),
                $this->shared->get('ver'));
        }

    }

    /*
     * plugin activation
     */
    public function ac_activate($networkwide)
    {

        /*
         * delete options and tables for all the sites in the network
         */
        if (function_exists('is_multisite') and is_multisite()) {

            /*
             * if this is a "Network Activation" create the options and tables
             * for each blog
             */
            if ($networkwide) {

                //get the current blog id
                global $wpdb;
                $current_blog = $wpdb->blogid;

                //create an array with all the blog ids
                $blogids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");

                //iterate through all the blogs
                foreach ($blogids as $blog_id) {

                    //swith to the iterated blog
                    switch_to_blog($blog_id);

                    //create options and tables for the iterated blog
                    $this->ac_initialize_options();

                }

                //switch to the current blog
                switch_to_blog($current_blog);

            } else {

                /*
                 * if this is not a "Network Activation" create options and
                 * tables only for the current blog
                 */
                $this->ac_initialize_options();

            }

        } else {

            /*
             * if this is not a multisite installation create options and
             * tables only for the current blog
             */
            $this->ac_initialize_options();

        }

    }

    //create the options and tables for the newly created blog
    public function new_blog_create_options_and_tables($blog_id, $user_id, $domain, $path, $site_id, $meta)
    {

        global $wpdb;

        /*
         * if the plugin is "Network Active" create the options and tables for
         * this new blog
         */
        if (is_plugin_active_for_network('offline-writer/init.php')) {

            //get the id of the current blog
            $current_blog = $wpdb->blogid;

            //switch to the blog that is being activated
            switch_to_blog($blog_id);

            //create options and database tables for the new blog
            $this->ac_initialize_options();

            //switch to the current blog
            switch_to_blog($current_blog);

        }

    }

    //delete options and tables for the deleted blog
    public function delete_blog_delete_options_and_tables($blog_id)
    {

        global $wpdb;

        //get the id of the current blog
        $current_blog = $wpdb->blogid;

        //switch to the blog that is being activated
        switch_to_blog($blog_id);

        //create options and database tables for the new blog
        $this->un_delete_options();

        //switch to the current blog
        switch_to_blog($current_blog);

    }

    /*
     * initialize plugin options
     */
    private function ac_initialize_options()
    {

        //general
        add_option($this->shared->get('slug') . "_import_post_type", "post");
        add_option($this->shared->get('slug') . "_import_menu_required_capability", "edit_posts");
        add_option($this->shared->get('slug') . "_markdown_parser", "parsedown");
        add_option($this->shared->get('slug') . "_cebe_markdown_html5", "0");
        add_option($this->shared->get('slug') . "_cebe_markdown_keep_list_start_number", "0");
        add_option($this->shared->get('slug') . "_cebe_markdown_enable_new_lines", "0");

    }

    /*
     * plugin delete
     */
    static public function un_delete()
    {

        /*
         * delete options and tables for all the sites in the network
         */
        if (function_exists('is_multisite') and is_multisite()) {

            //get the current blog id
            global $wpdb;
            $current_blog = $wpdb->blogid;

            //create an array with all the blog ids
            $blogids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");

            //iterate through all the blogs
            foreach ($blogids as $blog_id) {

                //switch to the iterated blog
                switch_to_blog($blog_id);

                //create options and tables for the iterated blog
                Daimma_Admin::un_delete_options();

            }

            //switch to the current blog
            switch_to_blog($current_blog);

        } else {

            /*
             * if this is not a multisite installation delete options and
             * tables only for the current blog
             */
            Daimma_Admin::un_delete_options();

        }

    }

    /*
     * delete plugin options
     */
    static public function un_delete_options()
    {

        //assign an instance of Daimma_Shared
        $shared = Daimma_Shared::get_instance();

        //general
        delete_option($shared->get('slug') . "_import_post_type");
        delete_option($shared->get('slug') . "_import_menu_required_capability");
        delete_option($shared->get('slug') . "_markdown_parser");
        delete_option($shared->get('slug') . "_cebe_markdown_html5");
        delete_option($shared->get('slug') . "_cebe_markdown_keep_list_start_number");
        delete_option($shared->get('slug') . "_cebe_markdown_enable_new_lines");

    }

    /*
     * register the admin menu
     */
    public function me_add_admin_menu()
    {

        $icon_svg = 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz4NCjwhLS0gR2VuZXJhdG9yOiBBZG9iZSBJbGx1c3RyYXRvciAyMi4wLjEsIFNWRyBFeHBvcnQgUGx1Zy1JbiAuIFNWRyBWZXJzaW9uOiA2LjAwIEJ1aWxkIDApICAtLT4NCjxzdmcgdmVyc2lvbj0iMS4xIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB4PSIwcHgiIHk9IjBweCINCgkgdmlld0JveD0iMCAwIDE4IDE4IiBzdHlsZT0iZW5hYmxlLWJhY2tncm91bmQ6bmV3IDAgMCAxOCAxODsiIHhtbDpzcGFjZT0icHJlc2VydmUiPg0KPHN0eWxlIHR5cGU9InRleHQvY3NzIj4NCgkuc3Qwe2Rpc3BsYXk6bm9uZTt9DQoJLnN0MXtkaXNwbGF5OmlubGluZTt9DQoJLnN0MntkaXNwbGF5Om5vbmU7ZmlsbDojRkZGRkZGO30NCgkuc3Qze2ZpbGw6I0ZGRkZGRjtmaWx0ZXI6dXJsKCNBZG9iZV9PcGFjaXR5TWFza0ZpbHRlcik7fQ0KCS5zdDR7bWFzazp1cmwoI2FfMV8pO30NCgkuc3Q1e2Rpc3BsYXk6aW5saW5lO2ZpbGw6I0ZGRkZGRjtmaWx0ZXI6dXJsKCNBZG9iZV9PcGFjaXR5TWFza0ZpbHRlcl8xXyk7fQ0KCS5zdDZ7ZGlzcGxheTppbmxpbmU7bWFzazp1cmwoI2FfMl8pO30NCjwvc3R5bGU+DQo8ZyBpZD0iTGF5ZXJfMSIgY2xhc3M9InN0MCI+DQoJPGcgY2xhc3M9InN0MSI+DQoJCTxyZWN0IHk9IjMuNSIgY2xhc3M9InN0MiIgd2lkdGg9IjE4IiBoZWlnaHQ9IjExLjEiLz4NCgkJPHBhdGggY2xhc3M9InN0MCIgZD0iTTIuNiwxMS45VjYuMWgxLjdsMS43LDIuMmwxLjctMi4yaDEuN3Y1LjlINy44VjguNmwtMS43LDIuMkw0LjMsOC42djMuNEgyLjZ6IE0xMy40LDExLjlsLTIuNi0yLjloMS43di0zDQoJCQloMS43djNIMTZMMTMuNCwxMS45eiIvPg0KCQk8ZGVmcz4NCgkJCTxmaWx0ZXIgaWQ9IkFkb2JlX09wYWNpdHlNYXNrRmlsdGVyIiBmaWx0ZXJVbml0cz0idXNlclNwYWNlT25Vc2UiIHg9IjAiIHk9IjMuNSIgd2lkdGg9IjE4IiBoZWlnaHQ9IjExLjEiPg0KCQkJCTxmZUNvbG9yTWF0cml4ICB0eXBlPSJtYXRyaXgiIHZhbHVlcz0iMSAwIDAgMCAwICAwIDEgMCAwIDAgIDAgMCAxIDAgMCAgMCAwIDAgMSAwIi8+DQoJCQk8L2ZpbHRlcj4NCgkJPC9kZWZzPg0KCQk8bWFzayBtYXNrVW5pdHM9InVzZXJTcGFjZU9uVXNlIiB4PSIwIiB5PSIzLjUiIHdpZHRoPSIxOCIgaGVpZ2h0PSIxMS4xIiBpZD0iYV8xXyI+DQoJCQk8cmVjdCB4PSIwIiB5PSIzLjUiIGNsYXNzPSJzdDMiIHdpZHRoPSIxOCIgaGVpZ2h0PSIxMS4xIi8+DQoJCQk8cGF0aCBkPSJNMi42LDExLjlWNi4xaDEuN2wxLjcsMi4ybDEuNy0yLjJoMS43djUuOUg3LjhWOC42bC0xLjcsMi4yTDQuNCw4LjZ2My40SDIuNnogTTEzLjQsMTEuOWwtMi42LTIuOWgxLjd2LTNoMS43djNIMTYNCgkJCQlMMTMuNCwxMS45eiIvPg0KCQk8L21hc2s+DQoJCTxwYXRoIGNsYXNzPSJzdDQiIGQ9Ik0xLjMsMy41aDE1LjRDMTcuNCwzLjUsMTgsNCwxOCw0Ljh2OC41YzAsMC43LTAuNiwxLjMtMS4zLDEuM0gxLjNDMC42LDE0LjUsMCwxNCwwLDEzLjJWNC44DQoJCQlDMCw0LDAuNiwzLjUsMS4zLDMuNXoiLz4NCgk8L2c+DQo8L2c+DQo8ZyBpZD0iTGF5ZXJfMyIgY2xhc3M9InN0MCI+DQoJPGRlZnM+DQoJCTxmaWx0ZXIgaWQ9IkFkb2JlX09wYWNpdHlNYXNrRmlsdGVyXzFfIiBmaWx0ZXJVbml0cz0idXNlclNwYWNlT25Vc2UiIHg9IjAiIHk9IjMuNSIgd2lkdGg9IjE4IiBoZWlnaHQ9IjExLjEiPg0KCQkJPGZlQ29sb3JNYXRyaXggIHR5cGU9Im1hdHJpeCIgdmFsdWVzPSIxIDAgMCAwIDAgIDAgMSAwIDAgMCAgMCAwIDEgMCAwICAwIDAgMCAxIDAiLz4NCgkJPC9maWx0ZXI+DQoJPC9kZWZzPg0KCTxtYXNrIG1hc2tVbml0cz0idXNlclNwYWNlT25Vc2UiIHg9IjAiIHk9IjMuNSIgd2lkdGg9IjE4IiBoZWlnaHQ9IjExLjEiIGlkPSJhXzJfIiBjbGFzcz0ic3QxIj4NCgkJPHJlY3QgeD0iMCIgeT0iMy41IiBzdHlsZT0iZmlsbDojRkZGRkZGO2ZpbHRlcjp1cmwoI0Fkb2JlX09wYWNpdHlNYXNrRmlsdGVyXzFfKTsiIHdpZHRoPSIxOCIgaGVpZ2h0PSIxMS4xIi8+DQoJCTxwYXRoIGQ9Ik0yLjYsMTEuOVY2LjFoMS43bDEuNywyLjJsMS43LTIuMmgxLjd2NS45SDcuOFY4LjZsLTEuNywyLjJMNC40LDguNnYzLjRIMi42eiBNMTMuNCwxMS45bC0yLjYtMi45aDEuN3YtM2gxLjd2M0gxNg0KCQkJTDEzLjQsMTEuOXoiLz4NCgk8L21hc2s+DQoJPHBhdGggY2xhc3M9InN0NiIgZD0iTTEuMywzLjVoMTUuNEMxNy40LDMuNSwxOCw0LDE4LDQuOHY4LjVjMCwwLjctMC42LDEuMy0xLjMsMS4zSDEuM0MwLjYsMTQuNSwwLDE0LDAsMTMuMlY0LjgNCgkJQzAsNCwwLjYsMy41LDEuMywzLjV6Ii8+DQo8L2c+DQo8ZyBpZD0iTGF5ZXJfMiI+DQoJPGcgaWQ9IkxheWVyXzQiPg0KCTwvZz4NCgk8cGF0aCBkPSJNMTYuOCwzLjVIMS4yQzAuNSwzLjUsMCw0LDAsNC43djguNmMwLDAuNywwLjUsMS4yLDEuMiwxLjJoMTUuNmMwLjcsMCwxLjItMC41LDEuMi0xLjJWNC43QzE4LDQsMTcuNSwzLjUsMTYuOCwzLjV6DQoJCSBNOS4yLDExLjlINy41VjguNmwtMS43LDIuMkw0LDguNnYzLjRIMi4zVjYuMUg0bDEuNywyLjJsMS43LTIuMmgxLjdWMTEuOXogTTEzLjEsMTEuOWwtMi42LTIuOWgxLjd2LTNIMTR2M2gxLjdMMTMuMSwxMS45eiIvPg0KPC9nPg0KPC9zdmc+DQo=';

        add_menu_page(
            esc_attr__('IM'),
            esc_attr__('Markdown'),
            get_option($this->shared->get('slug') . '_import_menu_required_capability'),
            $this->shared->get('slug') . '-import',
            array($this, 'me_display_menu_import'),
            $icon_svg
        );

        $this->screen_id_import = add_submenu_page(
            $this->shared->get('slug') . '-import',
            esc_attr__('IM - Import'),
            esc_attr__('Import'),
            get_option($this->shared->get('slug') . '_import_menu_required_capability'),
            $this->shared->get('slug') . '-import',
            array($this, 'me_display_menu_import')
        );

	    $this->screen_id_help = add_submenu_page(
		    $this->shared->get('slug') . '-import',
		    esc_attr__('IM - Help'),
		    esc_attr__('Help'),
		    'manage_options',
		    $this->shared->get('slug') . '-help',
		    array($this, 'me_display_menu_help')
	    );

	    $this->screen_id_pro_version = add_submenu_page(
		    $this->shared->get('slug') . '-import',
		    esc_attr__('IM - Pro Version'),
		    esc_attr__('Pro Version'),
		    'manage_options',
		    $this->shared->get('slug') . '-pro-version',
		    array($this, 'me_display_menu_pro_version')
	    );

        $this->screen_id_options = add_submenu_page(
            $this->shared->get('slug') . '-import',
            esc_attr__('IM - Options'),
            esc_attr__('Options', 'import-markdown'),
            'manage_options',
            $this->shared->get('slug') . '-options',
            array($this, 'me_display_menu_options')
        );

    }

    /*
     * includes the import view
     */
    public function me_display_menu_import()
    {
        include_once('view/import.php');
    }

	/*
	 * includes the help view
	 */
	public function me_display_menu_help()
	{
		include_once('view/help.php');
	}

	/*
	 * includes the pro version view
	 */
	public function me_display_menu_pro_version()
	{
		include_once('view/pro_version.php');
	}

    /*
     * includes the options view
     */
    public function me_display_menu_options()
    {
        include_once('view/options.php');
    }

    /*
     * register options
     */
    public function op_register_options()
    {

        //Section General ----------------------------------------------------------------------------------------------
        add_settings_section(
            'daimma_general_settings_section',
            null,
            null,
            'daimma_general_options'
        );

        add_settings_field(
            'import_post_type',
            esc_attr__('Post Type', 'import-markdown'),
            array($this, 'import_post_type_callback'),
            'daimma_general_options',
            'daimma_general_settings_section'
        );

        register_setting(
            'daimma_general_options',
            'daimma_import_post_type',
            array($this, 'import_post_type_validation')
        );

        add_settings_field(
            'import_menu_required_capability',
            esc_attr__('Required Capability', 'import-markdown'),
            array($this, 'import_menu_required_capability_callback'),
            'daimma_general_options',
            'daimma_general_settings_section'
        );

        register_setting(
            'daimma_general_options',
            'daimma_import_menu_required_capability',
            array($this, 'import_menu_required_capability_validation')
        );

        add_settings_field(
            'markdown_parser',
            esc_attr__('Parser', 'import-markdown'),
            array($this, 'markdown_parser_callback'),
            'daimma_general_options',
            'daimma_general_settings_section'
        );

        register_setting(
            'daimma_general_options',
            'daimma_markdown_parser',
            array($this, 'markdown_parser_validation')
        );

        add_settings_field(
            'cebe_markdown_html5',
            esc_attr__('Cebe Markdown HTML5', 'import-markdown'),
            array($this, 'cebe_markdown_html5_callback'),
            'daimma_general_options',
            'daimma_general_settings_section'
        );

        register_setting(
            'daimma_general_options',
            'daimma_cebe_markdown_html5',
            array($this, 'cebe_markdown_html5_validation')
        );

        add_settings_field(
            'cebe_markdown_keep_list_start_number',
            esc_attr__('Cebe Markdown List Start', 'import-markdown'),
            array($this, 'cebe_markdown_keep_list_start_number_callback'),
            'daimma_general_options',
            'daimma_general_settings_section'
        );

        register_setting(
            'daimma_general_options',
            'daimma_cebe_markdown_keep_list_start_number',
            array($this, 'cebe_markdown_keep_list_start_number_validation')
        );

        add_settings_field(
            'cebe_markdown_enable_new_lines',
            esc_attr__('Cebe Markdown New Lines', 'import-markdown'),
            array($this, 'cebe_markdown_enable_new_lines_callback'),
            'daimma_general_options',
            'daimma_general_settings_section'
        );

        register_setting(
            'daimma_general_options',
            'daimma_cebe_markdown_enable_new_lines',
            array($this, 'cebe_markdown_enable_new_lines_validation')
        );

    }

    //import options callbacks and validations -----------------------------------------------------------------------------
    public function import_post_type_callback($args)
    {

        $import_post_type = get_option("daimma_import_post_type");

        $available_post_types_a = get_post_types(array(
            'public'  => true,
            'show_ui' => true
        ));

        //Remove the "attachment" post type
        $available_post_types_a = array_diff($available_post_types_a, array('attachment'));

        $html = '<select id="daimma-import-post-type" name="daimma_import_post_type" class="daext-display-none">';

        foreach ($available_post_types_a as $single_post_type) {
            if ($import_post_type === $single_post_type) {
                $selected = 'selected';
            } else {
                $selected = '';
            }
            $post_type_obj = get_post_type_object($single_post_type);
            $html          .= '<option value="' . $single_post_type . '" ' . $selected . '>' . esc_attr($post_type_obj->label) . '</option>';
        }

        $html .= '</select>';
        $html .= '<div class="help-icon" title="' . esc_attr__('The post type to which the posts generated from the imported Markdown files should be assigned.',
                'import-markdown') . '"></div>';

        echo $html;

    }

    public function import_post_type_validation($input)
    {

        return $input;

    }

    public function import_menu_required_capability_validation($input)
    {

        if ( ! preg_match($this->regex_capability, $input)) {
            add_settings_error('daimma_import_menu_required_capability', 'daimma_import_menu_required_capability',
                esc_attr__('Please enter a valid capability in the "Required Capability" option.', 'import-markdown'));
            $output = get_option('daimma_import_menu_required_capability');
        } else {
            $output = $input;
        }

        return $output;

    }

    public function import_menu_required_capability_callback($args)
    {

        $html = '<input type="text" id="daimma-import-menu-required-capability" name="daimma_import_menu_required_capability" class="regular-text" value="' . esc_attr(get_option("daimma_import_menu_required_capability")) . '" />';
        $html .= '<div class="help-icon" title="' . esc_attr__('The capability required to get access on the "Import" menu.',
                'import-markdown') . '"></div>';

        echo $html;

    }

    public function markdown_parser_callback($args)
    {

        $html = '<select id="daimma-markdown-parser" name="daimma_markdown_parser" class="daext-display-none">';
        $html .= '<option ' . selected(get_option("daimma_markdown_parser"), 'parsedown',
                false) . ' value="parsedown">Parsedown</option>';
        $html .= '<option ' . selected(get_option("daimma_markdown_parser"), 'parsedown_extra',
                false) . ' value="parsedown_extra">Parsedown Extra</option>';
        $html .= '<option ' . selected(get_option("daimma_markdown_parser"), 'cebe_markdown',
                false) . ' value="cebe_markdown">Cebe Markdown</option>';
        $html .= '<option ' . selected(get_option("daimma_markdown_parser"), 'cebe_markdown_github_flavored',
                false) . ' value="cebe_markdown_github_flavored">Cebe Markdown Github</option>';
        $html .= '<option ' . selected(get_option("daimma_markdown_parser"), 'cebe_markdown_extra',
                false) . ' value="cebe_markdown_extra">Cebe Markdown Extra</option>';
        $html .= '</select>';
        $html .= '<div class="help-icon" title="' . esc_attr__('This option determines which parser should be used to perform the conversion.',
                'import-markdown') . '"></div>';

        echo $html;

    }

    public function markdown_parser_validation($input)
    {

        return $input;

    }

    public function cebe_markdown_html5_callback($args)
    {

        $html = '<select id="daimma-cebe-markdown-html5" name="daimma_cebe_markdown_html5" class="daext-display-none">';
        $html .= '<option ' . selected(intval(get_option("daimma_cebe_markdown_html5")), 1,
                false) . ' value="1">' . esc_attr__('Enabled', 'import-markdown') . '</option>';
        $html .= '<option ' . selected(intval(get_option("daimma_cebe_markdown_html5")), 0,
                false) . ' value="0">' . esc_attr__('Disabled', 'import-markdown') . '</option>';
        $html .= '</select>';
        $html .= '<div class="help-icon" title="' . esc_attr__('To enable HTML5 output instead of HTML4. This feature is actually applied only if one of the three "Cebe Markdown" parsers is used.',
                'import-markdown') . '"></div>';

        echo $html;

    }

    public function cebe_markdown_html5_validation($input)
    {

        return intval($input, 10) == 1 ? '1' : '0';

    }

    public function cebe_markdown_keep_list_start_number_callback($args)
    {

        $html = '<select id="daimma-cebe-markdown-keep-list-start-number" name="daimma_cebe_markdown_keep_list_start_number" class="daext-display-none">';
        $html .= '<option ' . selected(intval(get_option("daimma_cebe_markdown_keep_list_start_number")), 1,
                false) . ' value="1">' . esc_attr__('Enabled', 'import-markdown') . '</option>';
        $html .= '<option ' . selected(intval(get_option("daimma_cebe_markdown_keep_list_start_number")), 0,
                false) . ' value="0">' . esc_attr__('Disabled', 'import-markdown') . '</option>';
        $html .= '</select>';
        $html .= '<div class="help-icon" title="' . esc_attr__('To keep the number in the ordered lists as specified in the Markdown. The default behavior is to always start from 1 and increment by one regardless of the number specified in the Markdown. This feature is actually applied only if one of the three "Cebe Markdown" parsers is used.',
                'import-markdown') . '"></div>';

        echo $html;

    }

    public function cebe_markdown_keep_list_start_number_validation($input)
    {

        return intval($input, 10) == 1 ? '1' : '0';

    }

    public function cebe_markdown_enable_new_lines_callback($args)
    {

        $html = '<select id="daimma-cebe-markdown-enable-new-lines" name="daimma_cebe_markdown_enable_new_lines" class="daext-display-none">';
        $html .= '<option ' . selected(intval(get_option("daimma_cebe_markdown_enable_new_lines")), 1,
                false) . ' value="1">' . esc_attr__('Enabled', 'import-markdown') . '</option>';
        $html .= '<option ' . selected(intval(get_option("daimma_cebe_markdown_enable_new_lines")), 0,
                false) . ' value="0">' . esc_attr__('Disabled', 'import-markdown') . '</option>';
        $html .= '</select>';
        $html .= '<div class="help-icon" title="' . esc_attr__('To convert all the newlines to <br/> tags. By default only the newlines with two preceding spaces are converted to <br/> tags. This feature is actually applied only if the "Cebe Markdown GitHub" parser is used.',
                'import-markdown') . '"></div>';

        echo $html;

    }

    public function cebe_markdown_enable_new_lines_validation($input)
    {

        return intval($input, 10) == 1 ? '1' : '0';

    }

    /*
     * Given an array with the information related to the
     * uploaded Markdown file (markdown|mdown|mkdn|md|mkd|mdwn|mdtxt|mdtext|text|txt) a post is created with the filename as
     * the title and the HTML converted from the Markdown format as content
     *
     * @param $file_info An array with the information related to the uploaded file:
     * - name -> example.md
     * - type = application/octet-stream
     * - tmp_name = c:\wamp\tmp\php2A59.tmp
     * - error = 0
     * - size = 698
     */
    public function convert_markdown_to_post($file_info)
    {

        $file_name = $file_info['name'];

        //verify the extension
        if (preg_match('/^.+\.(markdown|mdown|mkdn|md|mkd|mdwn|mdtxt|mdtext|text|txt)$/', $file_name, $matches) === 1) {

            $file_extension = $matches[1];

            //get the file content
            $file_content = file_get_contents($file_info['tmp_name']);

            //convert html to markdown using the selected parser and options
            switch (get_option($this->shared->get('slug') . "_markdown_parser")) {

                case 'parsedown':

                    global $daimma_parsedown;
                    $html_content = $daimma_parsedown->text($file_content);

                    break;

                case 'parsedown_extra':

                    global $daimma_parsedown_extra;
                    $html_content = $daimma_parsedown_extra->text($file_content);

                    break;

                case 'cebe_markdown':

                    global $daimma_cebe_markdown;

                    if (intval(get_option($this->shared->get('slug') . "_cebe_markdown_html5"), 10) == true) {
                        $daimma_cebe_markdown->html5 = true;
                    }

                    if (intval(get_option($this->shared->get('slug') . "_cebe_markdown_keep_list_start_number"),
                            10) == true) {
                        $daimma_cebe_markdown->keepListStartNumber = true;
                    }

                    $html_content = $daimma_cebe_markdown->parse($file_content);

                    break;

                case 'cebe_markdown_github_flavored':

                    global $daimma_cebe_markdown_github_flavored;

                    if (intval(get_option($this->shared->get('slug') . "_cebe_markdown_html5"), 10) == true) {
                        $daimma_cebe_markdown_github_flavored->html5 = true;
                    }

                    if (intval(get_option($this->shared->get('slug') . "_cebe_markdown_keep_list_start_number"),
                            10) == true) {
                        $daimma_cebe_markdown_github_flavored->keepListStartNumber = true;
                    }

                    if (intval(get_option($this->shared->get('slug') . "_cebe_markdown_enable_new_lines"),
                            10) == true) {
                        $daimma_cebe_markdown_github_flavored->enableNewlines = true;
                    }

                    $html_content = $daimma_cebe_markdown_github_flavored->parse($file_content);

                    break;

                case 'cebe_markdown_extra':

                    global $daimma_cebe_markdown_extra;

                    if (intval(get_option($this->shared->get('slug') . "_cebe_markdown_html5"), 10) == true) {
                        $daimma_cebe_markdown_extra->html5 = true;
                    }

                    if (intval(get_option($this->shared->get('slug') . "_cebe_markdown_keep_list_start_number"),
                            10) == true) {
                        $daimma_cebe_markdown_extra->keepListStartNumber = true;
                    }

                    $html_content = $daimma_cebe_markdown_extra->parse($file_content);

                    break;

            }

            /*
             * Create a new post with the $html_content as a content and the $file_info['name'] as a title
             */

            // Create post object
            $new_post = array(
                'post_title'   => wp_strip_all_tags($this->remove_markdown_extension($file_info['name'])),
                'post_content' => $html_content,
                'post_type'    => get_option($this->shared->get('slug') . '_import_post_type')
            );

            // Insert the post into the database
            $new_post_id = wp_insert_post($new_post);

            if ($new_post_id !== 0 and ! is_wp_error($new_post_id)) {
                echo '<div class="updated settings-error notice is-dismissible below-h2"><p>' . esc_attr__('The new post has been successfully created. You can edit it ',
                        'import-markdown') . '<a href="post.php?post=' . $new_post_id . '&action=edit">' . esc_attr__('here',
                        'import-markdown') . '</a>.</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">' . esc_attr__('Dismiss this notice.',
                        'import-markdown') . '</span></button></div>';
            } else {
                echo '<div id="setting-error-daimma-unable-create-post" class="error settings-error notice is-dismissible"><p>' . esc_attr__('Unable to create the new post. An error occured.',
                        'import-markdown') . '</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">' . esc_attr__('Dismiss this notice.',
                        'import-markdown') . '</span></button></div>';
            }

        } else {

            echo '<div id="setting-error-daimma-select-markdown-file" class="error settings-error notice is-dismissible"><p>' . esc_attr__('Please select a Markdown file.',
                    'import-markdown') . '</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">' . esc_attr__('Dismiss this notice.',
                    'import-markdown') . '</span></button></div>';

        }

    }

    /*
     * Remove the markdown extension from the filename
     *
     * @param $filename
     * @return The $filename without the Markdown (markdown|mdown|mkdn|md|mkd|mdwn|mdtxt|mdtext|text|txt) extension
     */
    private function remove_markdown_extension($filename)
    {

        preg_match('/^[^\.]+(\.(?:markdown|mdown|mkdn|md|mkd|mdwn|mdtxt|mdtext|text|txt))$/', $filename, $matches,
            PREG_OFFSET_CAPTURE);

        return substr($filename, 0, $matches[1][1]);

    }

}