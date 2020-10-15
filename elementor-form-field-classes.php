<?php

/**
 * Plugin Name: Form Field Classes for Elementor
 * Plugin URI:  https://wordpress.org/plugins/elementor-form-field-classes/
 * Text Domain: elementor-form-field-classes
 * Description: Add class field to Elementor Form Fields
 * Author:      Ryan Soury
 * Author URI:  https://www.webdoodle.com.au
 * Version:     0.1.0
 * License:     GPLv2+
 *
 * @package WordPress
 * @author  Ryan Soury <ryan@webdoodle.com.au>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version 2020-07-15
 */

class Elementor_Forms_Input_Classes
{

	public $allowed_fields = [
		'text',
		'email',
		'url',
		'password',
	];

	public function __construct()
	{
		// Add class attribute to form field render
		add_filter('elementor_pro/forms/render/item', [$this, 'maybe_add_css_class'], 10, 3);

		add_action('elementor/element/form/section_form_fields/before_section_end', [$this, 'add_css_class_field_control'], 100, 2);
	}

	/**
	 * add_css_class_field_control
	 * @param $element
	 * @param $args
	 */
	public function add_css_class_field_control($element, $args)
	{
		$elementor = \Elementor\Plugin::instance();
		$control_data = $elementor->controls_manager->get_control_from_stack($element->get_name(), 'form_fields');

		if (is_wp_error($control_data)) {
			return;
		}
		// create a new css class control as a repeater field
		$tmp = new Elementor\Repeater();
		$tmp->add_control(
			'field_css_class',
			[
				'label' => 'CSS class',
				'inner_tab' => 'form_fields_advanced_tab',
				'tab' => 'content',
				'tabs_wrapper' => 'form_fields_tabs',
				'type' => 'text',
				'conditions' => [
					'terms' => [
						[
							'name' => 'field_type',
							'operator' => 'in',
							'value' => $this->allowed_fields,
						],
					],
				],
			]
		);

		$pattern_field = $tmp->get_controls();
		$pattern_field = $pattern_field['field_css_class'];

		// insert new class field in advanced tab before field ID control
		$new_order = [];
		foreach ($control_data['fields'] as $field_key => $field) {
			if ('custom_id' === $field['name']) {
				$new_order['field_css_class'] = $pattern_field;
			}
			$new_order[$field_key] = $field;
		}
		$control_data['fields'] = $new_order;

		$element->update_control('form_fields', $control_data);
	}

	public function maybe_add_css_class($field, $field_index, $form_widget)
	{
		if (!empty($field['field_css_class']) && in_array($field['field_type'], $this->allowed_fields)) {

			$form_widget->add_render_attribute('input' . $field_index, 'class', $field['field_css_class']);
		}
		return $field;
	}
}

new Elementor_Forms_Input_Classes();
