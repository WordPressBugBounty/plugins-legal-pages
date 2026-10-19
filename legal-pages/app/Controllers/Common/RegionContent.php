<?php
namespace LegalPage\Controllers\Common;

use LegalPage\Traits\Hook;
use LegalPage\Models\Settings;

defined( 'ABSPATH' ) || exit;

/**
 * [lp_if region="..."] ... [/lp_if] — renders the inner content only when the
 * site's "regions we serve" (Settings::get_served_regions()) includes one of the
 * listed regions. Drives the F7 multi-region policy templates.
 *
 * With no regions chosen the condition is skipped and the content always shows,
 * because extra legal text is safer than missing a visitor's rights. The region
 * list is comma-separated and case-insensitive; inner shortcodes are evaluated.
 *
 * Loaded as a Common controller so the tag is also offered in the admin
 * shortcode picker (via the legal_pages_shortcodes filter), like [legal_pages_cookie_list].
 */
class RegionContent {

    use Hook;

    public function __construct() {
        $this->action( 'plugins_loaded', [ $this, 'boot' ] );
    }

    public function boot() {
        $this->add_shortcode( 'lp_if', [ $this, 'render' ] );
        $this->filter( 'legal_pages_shortcodes', [ $this, 'add_to_picker' ] );
    }

    public function add_to_picker( $shortcodes ) {
        $shortcodes['lp_if'] = '';
        return $shortcodes;
    }

    public function render( $atts, $content = null ) {
        $atts = shortcode_atts( [ 'region' => '' ], $atts, 'lp_if' );

        if ( null === $content || '' === trim( $content ) ) {
            return '';
        }

        // wpautop + shortcode_unautop run before shortcodes and split paragraphs at the tags:
        // the inner content can start with a dangling "</p>", end with an empty "<p>" (visual
        // editor: each tag in its own paragraph) or with an unclosed "<p>…<br />" (plain text:
        // the last line shares a paragraph with [/lp_if]). Trim the edges, then let WordPress
        // close or drop whatever is still unbalanced, so a shown section is valid markup.
        $content = preg_replace( [ '#^\s*</p>#', '#<p>\s*$#', '#<br\s*/?>\s*$#' ], '', $content );
        $content = force_balance_tags( $content );

        $served = Settings::get_served_regions();

        // No regions chosen -> show every section (safer than hiding required rights).
        if ( empty( $served ) ) {
            return do_shortcode( $content );
        }

        // Unknown codes (typos, "ca", "gdpr") are ignored. With no valid code left there's
        // nothing to condition on, so the section shows rather than silently disappearing.
        $regions = array_filter( array_map( 'trim', explode( ',', strtolower( (string) $atts['region'] ) ) ) );
        $regions = array_intersect( $regions, Settings::REGIONS_SERVED );

        if ( empty( $regions ) || array_intersect( $regions, $served ) ) {
            return do_shortcode( $content );
        }

        return '';
    }
}
