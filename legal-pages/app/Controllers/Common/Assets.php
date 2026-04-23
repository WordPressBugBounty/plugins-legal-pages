<?php
namespace LegalPage\Controllers\Common;

use LegalPage\Traits\Hook;
use LegalPage\Traits\Asset;

defined( 'ABSPATH' ) || exit;

class Assets {
	use Hook, Asset;

	/**
	 * Constructor to add all hooks.
	 */
	public function __construct() {
		$this->action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
		$this->action( 'wp_enqueue_scripts', array( $this, 'enqueue_public_assets' ) );
		$this->action( 'wp_head', array( $this, 'modal' ) );
		$this->action( 'admin_head', array( $this, 'modal' ) );
	}

	/**
	 * Enqueue admin assets (React app + styles)
	 *
	 * @return void
	 */
	public function enqueue_admin_assets() {
		
		wp_enqueue_style(
			'legal-pages-admin',
			LEGAL_PAGES_ASSETS_URL . 'admin/css/admin.css',
			array(),
			LEGAL_PAGES_VERSION
		);
		if ( ! legal_page_is_legal_pages_screen() ) {
			return;
		}

		$this->enqueue_style(
			'legal-pages-admin',
			LEGAL_PAGES_URL . 'build/admin.bundle.css',
		);

		wp_enqueue_script(
			'legal-pages-admin-legacy',
			LEGAL_PAGES_ASSETS_URL . 'admin/js/admin.js',
			array( 'jquery' ),
			LEGAL_PAGES_VERSION,
			true
		);

		wp_enqueue_media();

		wp_enqueue_editor();
		wp_enqueue_script( 'wp-tinymce' );

		$this->enqueue_script(
			'legal-pages-admin',
			LEGAL_PAGES_URL . 'build/admin.bundle.js',
			array('jquery', 'wp-util', 'wp-media-utils', 'media-upload', 'thickbox'),
			null,
			array( 'in_footer' => true )
		);

		$this->enqueue_common_assets( 'legal-pages-admin' );
	}

	/**
	 * Enqueue public/frontend assets
	 *
	 * @return void
	 */
	public function enqueue_public_assets() {
		// Public React CSS
		$this->enqueue_style(
			'legal-pages-public',
			LEGAL_PAGES_URL . 'build/public.bundle.css'
		);

		// Public React JS bundle
		$this->enqueue_script(
			'legal-pages-public',
			LEGAL_PAGES_URL . 'build/public.bundle.js',
			array(),
			null,
			array( 'in_footer' => true )
		);

		// Shared assets + localization
		$this->enqueue_common_assets( 'legal-pages-public' );
	}

	/**
	 * Enqueue shared assets (Tailwind + localization)
	 *
	 * @param string $script_handle Script handle to localize
	 * @return void
	 */
	private function enqueue_common_assets( string $script_handle ) {

		// Common styles
		$this->enqueue_style(
			'legal-pages-common',
			LEGAL_PAGES_ASSETS_URL . 'common/css/common.css',
			array(),
			LEGAL_PAGES_VERSION,
		);
		// Tailwind CSS (shared)
		$this->enqueue_style(
			'legal-pages-tailwind',
			LEGAL_PAGES_URL . 'build/tailwind.build.bundle.css'
		);
		

		// Common init.js file
		$this->enqueue_script(
			'legal-pages-common',
			LEGAL_PAGES_ASSETS_URL . 'common/js/init.js',
			array(),
			LEGAL_PAGES_VERSION,
			array( 'in_footer' => true )
		);

		$this->localize_script(
			$script_handle,
			'LEGAL_PAGES',
			apply_filters(
				'legal_pages_localized_vars',
				legal_page_get_localized_data()
			)
		);
	}

	public function modal() {
		echo '
		<div id="adl-legal-modal" style="display: none;">
			<img id="adl-legal-modal-loader" src="' . esc_attr( LEGAL_PAGES_ASSETS_URL . 'common/img/loader.gif' ) . '" />
		</div>';
	}
}
