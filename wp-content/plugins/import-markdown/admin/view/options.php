<?php

if ( ! current_user_can('manage_options')) {
    wp_die(esc_attr__('You do not have sufficient capabilities to access this page.', 'import-markdown'));
}

?>

<div class="wrap">

    <h2>Import Markdown - <?php esc_attr_e('Options', 'import-markdown'); ?></h2>

    <?php

    //settings errors
    if (isset($_GET['settings-updated']) and $_GET['settings-updated'] == 'true') {
        settings_errors();
    }

    ?>

    <div id="daext-options-wrapper" class="daext-clearfix">

        <div class="main-container">

            <div class="options-table-container">

                <div class="nav-tab-wrapper">
                    <a href="?page=daimma-options&tab=general_options"
                       class="nav-tab nav-tab-active"><?php esc_attr_e('General', 'import-markdown'); ?></a>
                </div>

                <form method='post' action='options.php' autocomplete="off">

                    <?php

                    settings_fields($this->shared->get('slug') . '_general_options');
                    do_settings_sections($this->shared->get('slug') . '_general_options');

                    ?>

                    <div class="daext-options-action">
                        <input type="submit" name="submit" id="submit" class="button"
                               value="<?php esc_attr_e('Save Changes', 'import-markdown'); ?>">
                    </div>

                </form>

            </div>

        </div>

    </div>

</div>