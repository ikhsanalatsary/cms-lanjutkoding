<?php

if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'import-markdown') );
}

?>

<!-- output -->

<div class="wrap">

    <h2><?php esc_html_e( 'Import Markdown - Help', 'import-markdown'); ?></h2>

    <div id="daext-menu-wrapper">

        <p><?php esc_html_e( 'Visit the resources below to find your answers or to ask questions directly to the plugin developers.', 'import-markdown'); ?></p>
        <ul>
            <li><a href="https://daext.com/support/"><?php esc_html_e( 'Support Conditions', 'import-markdown'); ?></li>
            <li><a href="https://daext.com"><?php esc_html_e( 'Developer Website', 'import-markdown'); ?></a></li>
            <li><a href="https://daext.com/ultimate-markdown/"><?php esc_html_e( 'Pro Version',
                        'import-markdown' ); ?></a></li>
            <li>
                <a href="https://wordpress.org/plugins/import-markdown/"><?php esc_html_e( 'WordPress.org Plugin Page',
                        'import-markdown' ); ?></a></li>
            <li>
                <a href="https://wordpress.org/support/plugin/import-markdown/"><?php esc_html_e( 'WordPress.org Support Forum',
                        'import-markdown' ); ?></a></li>
        </ul>
        <p>

    </div>

</div>