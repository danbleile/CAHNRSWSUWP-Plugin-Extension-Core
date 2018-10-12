<?php namespace WSUWP\CAHNRSWSUWP_Plugin_Extension_Core;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
} // End if

/*
* @desc Class to handle County_Contact_Info shortcode
* @since 3.0.0
*/
class County_Contact_Shortcode {

	protected $prefix = '';

	// @var array $default_settings Array of default settings
	protected $default_settings = array(
		'show_map'     => '',
		'map_address'  => '',
		'content'      => '',
	);


	public function __construct() {

		$core_setting = get_theme_mod( 'extension_core_county_contact', '' );

		if ( ! empty( $core_setting ) ) {

			\add_action( 'init', array( $this, 'register_shortcode' ) );

			\add_filter( 'cpb_shortcodes', array( $this, 'register_cpb_shortcode' ) );

			\add_filter( 'cpb_to_shortcode', array( $this, 'cpb_to_shortcode' ) );

			\add_filter( 'cpb_clean_atts', array( $this, 'cpb_clean_atts' ), 10, 3 );

		} // End if

	} // End __construct


	/*
	* @desc Register section shortcode
	* @since 3.0.0
	*/
	public function register_shortcode() {

		\add_shortcode( 'county_contact_info', array( $this, 'get_rendered_shortcode' ) );

	} // End register_shortcode


	/*
	* @desc Register Shortcode with Pagebuilder
	* @since 0.0.1
	*/
	public function register_cpb_shortcode( $shortcodes ) {

		$default_atts = apply_filters( 'cpb_shortcode_default_atts', $this->default_settings, array(), 'county_contact_info' );

		$shortcodes['county_contact_info'] = array(
			'form_callback'         => array( $this, 'get_shortcode_form' ),
			'label'                 => 'County Contact Info', // Label of the item
			'render_callback'       => array( $this, 'get_rendered_shortcode' ), // Callback to render shortcode
			'default_atts'          => $default_atts,
			'in_column'             => true, // Allow in column
		);

		return $shortcodes;

	} // End register_cpb_shortcode


	/*
	* @desc Render the shortcode
	* @since 3.0.0
	*
	* @param array $atts Shortcode attributes
	* @param string $content Shortcode content
	*
	* @return string HTML shortcode output
	*/
	public function get_rendered_shortcode( $atts, $content ) {

		$html = '';

		$default_atts = apply_filters( 'cpb_shortcode_default_atts', $this->default_settings, $atts, 'county_contact_info' );

		// Check default settings
		$atts = \shortcode_atts( $default_atts, $atts, 'county_site_search_form' );

		// Let's just check and make sure this exists first
		if ( function_exists( 'spine_get_option' ) ) {

			$department = esc_html( spine_get_option( 'contact_department' ) );

			$address = esc_html( spine_get_option( 'contact_streetAddress' ) );

			$locality = esc_html( spine_get_option( 'contact_addressLocality' ) );

			$zip_code = esc_html( spine_get_option( 'contact_postalCode' ) );

			$telephone = esc_html( spine_get_option( 'contact_telephone' ) );

			$email = esc_html( spine_get_option( 'contact_email' ) );

			$contact_point = esc_html( spine_get_option( 'contact_ContactPoint' ) );

			$contact_title = esc_html( spine_get_option( 'contact_ContactPointTitle' ) );

			if ( ! empty( $atts['show_map'] ) ) {

				wp_enqueue_script( 'google_maps_api', '//maps.googleapis.com/maps/api/js?key=AIzaSyCcTd3MjJf_pv1xZs2KbP2HO8b6K8cQsAg', array(), false, true );

				wp_enqueue_script( 'google-map-embed', ecore_get_plugin_url( 'lib/js/google-map-embed.js' ), array( 'google_maps_api' ), false, true );

				$map_address = $address . ' ' . $locality . ' ' . $zip_code;

				$marker_desc = '<div>' . $address . '<br />' . $locality . '<br />' . $zip_code . '</div><div>' . wpautop( wp_kses_post( $content ) ) . '</div>';

				$map_data = array(
					'address' => ( ! empty( $atts['map_address'] ) ) ? esc_html( $atts['map_address'] ) : $map_address,
					'title'   => esc_html( spine_get_option( 'contact_department' ) ),
					'desc'    => $marker_desc,
					//'zoom'    => 15,
				);

				wp_localize_script( 'google-map-embed', 'map_data', $map_data );

			} // End if

			ob_start();

			include __DIR__ . '/contact-info.php';

			$html .= ob_get_clean();

		} // End if

		return $html;

	} // End get_rendered_shortcode


	/*
	* @desc Get HTML for shortcode form
	* @since 3.0.0
	*
	* @param array $atts Shortcode attributes
	* @param string $content Shortcode content
	*
	* @return string HTML shortcode form output
	*/
	public function get_shortcode_form( $id, $settings, $content, $cpb_form ) {

		$form_html = $cpb_form->checkbox_field( \CAHNRSWP\Plugin\Pagebuilder\cpb_get_input_name( $id, true, 'show_map' ), 1, $settings['show_map'], 'Show Map' );

		$form_html .= $cpb_form->text_field( \CAHNRSWP\Plugin\Pagebuilder\cpb_get_input_name( $id, true, 'map_address' ), $settings['map_address'], 'Map Address' );

		$form_html .= $cpb_form->textarea_field( \CAHNRSWP\Plugin\Pagebuilder\cpb_get_input_name( $id, true, 'content' ), $content, 'Additional Content', 'cpb-full-width' );

		return array(
			'Basic'    => $form_html,
		);

	} // End get_shortcode_form


	/**
	 * @desc Filter shortcode to include content
	 * @since 0.0.3
	 *
	 * @param array $shortcode Shortcode Array
	 *
	 * @return array Modifed shortcode array
	 */
	public function cpb_to_shortcode( $shortcode ) {

		if ( 'county_contact_info' === $shortcode['slug'] ) {

			if ( ! empty( $shortcode['atts']['content'] ) ) {

				$shortcode['content'] = $shortcode['atts']['content'];

			} // End if
		} // End if

		return $shortcode;

	} // End cpb_to_shortcode


	/**
	 * @desc Filter shortcode to include content
	 * @since 0.0.3
	 *
	 * @param array $clean_settings Clean settings to be used in to shortcode method
	 * @param array $shortcode Shortcode Array
	 *
	 * @return array Modifed shortcode array
	 */
	public function cpb_clean_atts( $clean_settings, $save_settings, $shortcode ) {

		if ( 'county_contact_info' === $shortcode['slug'] ) {

			if ( ! empty( $save_settings['content'] ) ) {

				$clean_settings['content'] = wp_kses_post( $save_settings['content'] );

			} // End if
		} // End if

		return $clean_settings;

	} // End cpb_clean_atts


} // End County_Programs

$cpb_shortcode_county_contact_info = new County_Contact_Shortcode();
