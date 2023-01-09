<?php

if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'import-markdown' ) );
}

?>

<!-- output -->

<div class="wrap">

    <h2><?php esc_html_e( 'Import Markdown - Pro Version', 'import-markdown' ); ?></h2>

    <div id="daext-menu-wrapper">

        <p><?php echo esc_html__( 'For professional users, we distribute a',
					'import-markdown' ) . ' <a href="https://daext.com/ultimate-markdown/">' . esc_attr__( 'Pro Version',
					'import-markdown' ) . '</a> ' . esc_html__( 'of this plugin.',
					'import-markdown' ) . '</p>'; ?>
        <h2><?php esc_html_e( 'Additional Features Included in the Pro Version', 'import-markdown' ); ?></h2>
        <ul>
            <li><?php echo esc_html__( 'A Markdown editor with live preview to write perfect Markdown documents directly in WordPress','import-markdown' ); ?></li>
            <li><?php echo esc_html__( 'Ability to create an internal archive of Markdown documents','import-markdown' ); ?></li>
            <li><?php echo esc_html__( 'Ability to mass import Markdown files','import-markdown' ); ?></li>
            <li><?php echo esc_html__( 'Ability to mass export the WordPress posts as Markdown documents','import-markdown' ); ?></li>
            <li><?php echo esc_html__( 'Ability to mass export the internal archive of Markdown documents','import-markdown' ); ?></li>
            <li><?php echo esc_html__( 'Post sidebar section to upload Markdown documents directly in the editor','import-markdown' ); ?></li>
            <li><?php echo esc_html__( 'Post sidebar section to generate blocks from Markdown text','import-markdown' ); ?></li>
            <li><?php echo esc_html__( 'Post sidebar section to generate articles from the internal archive of  Markdown documents','import-markdown' ); ?></li>
            <li><?php echo esc_html__( 'Post sidebar section to convert the edited article to a Markdown file','import-markdown' ); ?></li>
            <li><?php echo esc_html__( 'REST API endpoint to send Markdown documents to your WordPress website from any external application','import-markdown' ); ?></li>
            <li><?php echo esc_html__( 'REST API endpoint to read data from the internal archive of Markdown documents','import-markdown' ); ?></li>
            <li><?php echo esc_html__( 'Additional options to set custom menu capabilities for all the plugin menus','import-markdown' ); ?></li>
            <li><?php echo esc_html__( 'Additional Markdown parsers','import-markdown' ); ?></li>
        </ul>
        <h2><?php esc_html_e( 'Additional Benefits of the Pro Version', 'import-markdown' ); ?></h2>
        <ul>
            <li><?php esc_html_e( '24 hours support provided 7 days a week', 'import-markdown' ); ?></li>
            <li><?php echo esc_html__( '30 day money back guarantee (more information is available in the',
						'import-markdown' ) . ' <a href="https://daext.com/refund-policy/">' . esc_html__( 'Refund Policy',
						'import-markdown' ) . '</a> ' . esc_html__( 'page', 'import-markdown' ) . ')'; ?></li>
        </ul>
        <h2><?php esc_html_e( 'Get Started', 'import-markdown' ); ?></h2>
        <p><?php echo esc_html__( 'Download the',
					'import-markdown' ) . ' <a href="https://daext.com/ultimate-markdown/">' . esc_html__( 'Pro Version',
					'import-markdown' ) . '</a> ' . esc_html__( 'now by selecting one of the available plans.',
					'import-markdown' ); ?></p>
    </div>

</div>

