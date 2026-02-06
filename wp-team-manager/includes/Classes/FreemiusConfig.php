<?php
namespace DWL\Wtm\Classes;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class FreemiusConfig {

    use \DWL\Wtm\Traits\Singleton;

    protected function init() {
        // Add filter to load custom CSS for Freemius pricing page
        tmwstm_fs()->add_filter( 'pricing/css_path', [ $this, 'custom_pricing_css_path' ] );
        
        // Optional: Customize other Freemius aspects if needed
        // tmwstm_fs()->add_filter( 'pricing/show_annual_in_monthly', '__return_false' );
    }

    /**
     * Returns the path to the custom pricing CSS file.
     *
     * @param string $default_pricing_css_path
     * @return string
     */
    public function custom_pricing_css_path( $default_pricing_css_path ) {
        return TM_PATH . '/admin/assets/css/freemius-pricing.css';
    }
}
