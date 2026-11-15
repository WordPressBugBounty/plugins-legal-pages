<?php
namespace LegalPage\Controllers\Admin;

use LegalPage\Traits\Hook;
use LegalPage\Models\Settings;

defined( 'ABSPATH' ) || exit;

/**
 * Black Friday 2026 campaign (temporary — remove after the sale).
 *
 * Runs from 15 Nov 2026 00:00 to the end of 5 Dec 2026, site time, and only
 * for admins without a valid Legal Pages Pro license. Outside that window
 * this class does nothing and every screen shows its normal upgrade UI, so
 * the sale ends on time even if no plugin update ships after it.
 *
 * The data goes to the SPA as its own `LEGAL_PAGES_PROMO` global rather than
 * a `LEGAL_PAGES` key: Legal Pages Pro prints its own `LEGAL_PAGES` on these
 * screens and an unlicensed Pro install is still in the audience.
 *
 * To remove: delete this file, `spa/admin/src/components/common/BlackFriday.jsx`,
 * the `bf_2026_*` ids in `Models\Settings::DISMISSIBLE_NOTICES`, the
 * "Black Friday" block in `assets/admin/css/admin.css`, and the
 * `usePromo()` branches in the SPA (grep for BlackFriday).
 */
class Promo {
	use Hook;

	const START = '2026-11-15 00:00:00';
	const END   = '2026-12-06 00:00:00'; // Exclusive: the sale runs through all of Dec 5.
	const URL   = 'https://legalpagespro.com/?utm_source=lite-plugin&utm_campaign=black-friday-2026#pricing';

	/** Notices the user can close (stored per user like `cookie_scan`). */
	const NOTICES = [ 'bf_2026_bar', 'bf_2026_hero' ];

	public function __construct() {
		$this->action( 'admin_enqueue_scripts', [ $this, 'print_data' ], 20 );
		$this->action( 'admin_menu', [ $this, 'badge_upgrade_menu' ], 999 );
	}

	/**
	 * The campaign if it should show to the current user right now, else null.
	 *
	 * @return array|null
	 */
	public static function get() {
		if ( ! current_user_can( 'manage_options' ) || 'valid' === get_option( 'wplp_license_status', '' ) ) {
			return null;
		}

		$tz    = wp_timezone();
		$start = ( new \DateTimeImmutable( self::START, $tz ) )->getTimestamp();
		$end   = ( new \DateTimeImmutable( self::END, $tz ) )->getTimestamp();

		/**
		 * Filters the Black Friday campaign. Return null to turn it off, or change
		 * `start`/`end` (Unix timestamps) to preview it outside the sale dates.
		 */
		$promo = apply_filters(
			'legal_pages_promo',
			[
				'id'       => 'bf_2026',
				'start'    => $start,
				'end'      => $end,
				'discount' => 50,
				'price'    => 49,
				'url'      => self::URL,
			]
		);

		if ( ! is_array( $promo ) || time() < (int) $promo['start'] || time() >= (int) $promo['end'] ) {
			return null;
		}

		// "Dec 5": the last day of the sale, in site time.
		$promo['ends_label'] = wp_date( 'M j', (int) $promo['end'] - 1 );

		return $promo;
	}

	/**
	 * Print `LEGAL_PAGES_PROMO` before the admin bundle. Absent outside the sale.
	 *
	 * @return void
	 */
	public function print_data() {
		if ( ! wp_script_is( 'legal-pages-admin', 'enqueued' ) ) {
			return;
		}

		$promo = self::get();
		if ( ! $promo ) {
			return;
		}

		$promo['dismissed'] = array_values( array_intersect( Settings::get_dismissed_notices(), self::NOTICES ) );

		wp_add_inline_script(
			'legal-pages-admin',
			'var LEGAL_PAGES_PROMO = ' . wp_json_encode( $promo ) . ';',
			'before'
		);
	}

	/**
	 * Add the "-50%" badge to the "Upgrade to Pro" submenu item during the sale.
	 *
	 * @return void
	 */
	public function badge_upgrade_menu() {
		global $submenu;

		$promo = self::get();
		if ( ! $promo || empty( $submenu['adl-legal-pages'] ) ) {
			return;
		}

		foreach ( $submenu['adl-legal-pages'] as $i => $item ) {
			if ( isset( $item[2] ) && 'adl-legal-pages#/upgrade-to-pro' === $item[2] ) {
				$submenu['adl-legal-pages'][ $i ][0] .= sprintf( ' <span class="lp-promo-badge">-%d%%</span>', (int) $promo['discount'] );
				$submenu['adl-legal-pages'][ $i ][4]  = trim( ( $item[4] ?? '' ) . ' lp-promo-menu' );
			}
		}
	}
}
