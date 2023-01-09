<?php

/*
 * this class should be used to stores properties and methods shared by the
 * admin and public side of wordpress
 */

class Daimma_Shared
{

    protected static $instance = null;

    private $data = array();

    private function __construct()
    {

        //Set plugin textdomain
        load_plugin_textdomain('import-markdown', false, 'import-markdown/lang/');

        $this->data['slug'] = 'daimma';
        $this->data['ver']  = '1.05';
        $this->data['dir']  = substr(plugin_dir_path(__FILE__), 0, -7);
        $this->data['url']  = substr(plugin_dir_url(__FILE__), 0, -7);

    }

    public static function get_instance()
    {

        if (null == self::$instance) {
            self::$instance = new self;
        }

        return self::$instance;

    }

    //retrieve data
    public function get($index)
    {
        return $this->data[$index];
    }

}