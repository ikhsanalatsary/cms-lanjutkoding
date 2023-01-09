<?php

if ( ! current_user_can(get_option($this->shared->get('slug') . '_import_menu_required_capability'))) {
    wp_die(esc_attr__('You do not have sufficient permissions to access this page.', 'import-markdown'));
}

?>

<!-- process data -->

<!-- output -->

<div class="wrap">

    <h2>Import Markdown - <?php esc_attr_e('Import', 'import-markdown'); ?></h2>

    <div class="main-container">

        <div id="daext-menu-wrapper">

            <?php

            //process the Markdown file upload
            if (isset($_FILES['file_to_upload'])) {

                //convert the file to a post
                $this->convert_markdown_to_post($_FILES['file_to_upload']);

            }

            //If the markdown parser doesn't support the current PHP version generate a message and hide the upload form
            $markdown_parser = get_option($this->shared->get('slug') . "_markdown_parser");

            if (DAIMMA_PHP_VERSION < 50300 and ($markdown_parser == 'parsedown' or $markdown_parser == 'parsedown_extra')) {
                echo '<p>' . esc_attr__('The Parsedown parser requires at least PHP 5.3', 'import-markdown') . '</p>';
                $hide_upload_form = true;
            }

            if (DAIMMA_PHP_VERSION < 50400 and
                ($markdown_parser == 'cebe_markdown' or
                 $markdown_parser == 'cebe_markdown_github_flavored' or
                 $markdown_parser == 'cebe_markdown_extra')
            ) {
                echo '<p>' . esc_attr__('The Cebe Markdown parser requires at least PHP 5.4', 'import-markdown') . '</p>';
                $hide_upload_form = true;
            }

            ?>

            <?php if ( ! isset($hide_upload_form)) : ?>
                <p><?php esc_attr_e('Choose a Markdown file (.md .markdown .mdown .mkdn .mkd .mdwn .mdtxt .mdtext .text .txt) to upload, then click Upload file and import.',
                        'import-markdown'); ?></p>
                <form enctype="multipart/form-data" id="import-upload-form" method="post" class="wp-upload-form"
                      action="">
                    <p>
                        <label for="upload"><?php esc_attr_e('Choose a file from your computer:', 'import-markdown'); ?></label>
                        <input type="file" id="upload" name="file_to_upload">
                    </p>
                    <p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary"
                                             value="<?php esc_attr_e('Upload file and import', 'import-markdown'); ?>"></p>
                </form>
            <?php endif; ?>

        </div>

    </div>

</div>