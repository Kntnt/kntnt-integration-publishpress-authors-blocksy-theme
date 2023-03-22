<?php

/**
 * Plugin Name:       Kntnt Integration for PublishPress Authors and Blocksy Theme
 * Plugin URI:        https://www.kntnt.com/
 * Description:       Allows PublishPress Authors to output multiple users in the byline of the Blocksy Theme.
 * Version:           1.0.0
 * Author:            Thomas Barregren
 * Author URI:        https://www.kntnt.com/
 * License:           GPL-3.0+
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.txt
 */

defined( 'ABSPATH' ) && new Plugin;

class Plugin {

	private array $authors;

	public function __construct() {
		add_action( 'wp', [ $this, 'run' ] );
	}

	public function run() {
		if ( $this->is_theme_active( 'blocksy' ) &&
		     is_plugin_active( 'publishpress-authors-pro/publishpress-authors-pro.php' ) &&
		     ( $this->authors = get_post_authors() ) ) {
			add_action( 'blocksy:post-meta:render-meta', [ $this, 'render_meta' ] );
		}
	}

	public function render_meta( $type_of_meta_data ) {
		if ( 'author' === $type_of_meta_data ) {

			// Prevent Blocksy from rendering author.
			add_filter( 'the_author', '__return_null', 9999, 0 );

			$html = [];
			foreach ( $this->authors as $author ) {

				// Following mimics lines 193–209 in
				// wp-content/themes/blocksy/inc/components/post-meta.php
				$attr               = [
					'class' => 'ct-meta-element-author',
					'href'  => $author->link,
					'title' => esc_attr( sprintf( __( 'Posts by %s', 'blocksy' ), $author->display_name ) ),
					'rel'   => 'author',
				];
				$schema_author_name = blocksy_schema_org_definitions( 'author_name', [ 'array' => true ] );
				$schema_author_url  = blocksy_schema_org_definitions( 'author_url', [ 'array' => true ] );
				$name               = blocksy_html_tag( 'span', $schema_author_name, $author->display_name );
				$html[]             = blocksy_html_tag( 'a', array_merge( $attr, $schema_author_url ), $name );

			}

			// Render each author as Blocksy does it.
			echo '<li class="meta-author"' . blocksy_schema_org_definitions( 'author' ) . '>';
			echo join( ", ", $html );
			echo '</li>';

		}
	}

	private function is_theme_active( $theme ) {
		return get_template() === $theme;
	}

}
