<?php
/* Plugin Name: WooCommerce Wine Properties
 * Description: Allows to describe wine properties per product 
 * Version: 1.0
 * Author: Frederik Gossen 
 * Text Domain: woo-wine-props
 * Domain Path: /languages
 */

defined('ABSPATH') or die('No script kiddies please!');

/* load translations */
add_action('init', 'woo_wine_props_load_translation');
function woo_wine_props_load_translation() {
	load_plugin_textdomain('woo-wine-props', false, 'woo-commerce-wine-properties/languages/');
}

/* add custom product fields to backend */
add_filter('woocommerce_product_data_tabs', 'woo_wine_props_register_product_tab');
function woo_wine_props_register_product_tab($tabs) {
	$tabs['wine_props'] = array(
		'label'  => __('Wine Properties', 'woo-wine-props'),
		'target' => 'wine_props',
		'class'  => array('show_if_simple', 'show_if_variable'));
	return $tabs;
}
add_filter('woocommerce_product_data_panels', 'woo_wine_props_render_product_tab'); 
function woo_wine_props_render_product_tab() {
	global $post;
	?><div id='wine_props' class='panel woocommerce_options_panel'><?php
		?><div class='options_group'><?php
			woocommerce_wp_text_input(array(
				'id'                => 'wine_props_alcohol_by_volume',
				'label'             => __('Alcohol by Volume (in %)', 'woo-wine-props'),
				'type'              => 'number',
				'custom_attributes' => array(
					'min'  => '0',
					'step' => '0.01')));
			woocommerce_wp_text_input(array(
				'id'    => 'wine_props_year',
				'label' => __('Vintage', 'woo-wine-props'),
				'type'  => 'number',
				'custom_attributes'	=> array(
					'min'  => '0',
					'step' => '1')));
		?></div><?php
	?></div><?php
}
add_action('woocommerce_process_product_meta', 'woo_wine_props_save');
function woo_wine_props_save($post_id) {
	if (isset($_POST['wine_props_alcohol_by_volume'])) 
		update_post_meta($post_id, 'wine_props_alcohol_by_volume', round(esc_attr($_POST['wine_props_alcohol_by_volume']), 2));
	if (isset($_POST['wine_props_year'])) 
		update_post_meta($post_id, 'wine_props_year', absint(esc_attr($_POST['wine_props_year'])));
}

/* add shortcode to be used in contents */
function woo_wine_props_render_shortcode($atts) {
	global $product;
	$product_id = isset($atts['product_id']) ?$atts['product_id']:$product->get_id();
	$alcohol_by_volume = get_post_meta($product_id, 'wine_props_alcohol_by_volume', true);
	$year = get_post_meta($product_id, 'wine_props_year', true);
	$wine_props_html = array();
	if (isset($alcohol_by_volume) && $alcohol_by_volume > 0)
		array_push($wine_props_html, '<b>' . __('Alcohol by Volume', 'woo-wine-props') . ':</b> ' . sprintf(__('%s %%', 'woo-wine-props'), number_format_i18n($alcohol_by_volume, 2)));
	if (isset($year) && $year > 0)
		array_push($wine_props_html, '<b>' . __('Vintage', 'woo-wine-props') . ':</b> ' . $year);

	return empty($wine_props_html) ? '' : '<p>' . join('<br/>', $wine_props_html) . '</p>';
}
add_shortcode('wine_props', 'woo_wine_props_render_shortcode');

