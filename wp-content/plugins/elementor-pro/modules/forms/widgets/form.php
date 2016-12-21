<?php
namespace ElementorPro\Modules\Forms\Widgets;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Typography;
use Elementor\Scheme_Color;
use Elementor\Scheme_Typography;
use Elementor\Widget_Base;
use ElementorPro\Classes\Utils;
use ElementorPro\Modules\Forms\Classes\Ajax_Handler;
use ElementorPro\Modules\Forms\Classes\Recaptcha_Handler;
use ElementorPro\Modules\Forms\Module;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Form extends Widget_Base {

	public function get_name() {
		return 'form';
	}

	public function get_title() {
		return __( 'Form', 'elementor-pro' );
	}

	public function get_icon() {
		return 'eicon-form-horizontal';
	}

	public function get_categories() {
		return [ 'pro-elements' ];
	}

	public function on_export( $element ) {
		unset(
			$element['settings']['email_to'],
			$element['settings']['email_from'],
			$element['settings']['email_from_name'],
			$element['settings']['email_subject'],
			$element['settings']['redirect_to']
		);

		return $element;
	}

	public static function get_button_sizes() {
		return [
			'xs' => __( 'Extra Small', 'elementor-pro' ),
			'sm' => __( 'Small', 'elementor-pro' ),
			'md' => __( 'Medium', 'elementor-pro' ),
			'lg' => __( 'Large', 'elementor-pro' ),
			'xl' => __( 'Extra Large', 'elementor-pro' ),
		];
	}

	protected function _register_controls() {
		$this->start_controls_section(
			'section_form_fields',
			[
				'label' => __( 'Form Fields', 'elementor-pro' ),
			]
		);

		$this->add_control(
			'form_name',
			[
				'label' => __( 'Form Name', 'elementor-pro' ),
				'type' => Controls_Manager::TEXT,
				'default' => __( 'New Form', 'elementor-pro' ),
				'placeholder' => __( 'Form Name', 'elementor-pro' ),
			]
		);

		$this->add_control(
			'form_fields',
			[
				'label' => __( 'Form Fields', 'elementor-pro' ),
				'type' => Controls_Manager::REPEATER,
				'show_label' => false,
				'separator' => 'before',
				'fields' => [
					[
						'name' => 'field_type',
						'label' => __( 'Type', 'elementor-pro' ),
						'type' => Controls_Manager::SELECT,
						'options' => [
							'text' => __( 'Text', 'elementor-pro' ),
							'tel' => __( 'Tel', 'elementor-pro' ),
							'email' => __( 'Email', 'elementor-pro' ),
							'textarea' => __( 'Textarea', 'elementor-pro' ),
							'number' => __( 'Number', 'elementor-pro' ),
							'select' => __( 'Select', 'elementor-pro' ),
							'url' => __( 'URL', 'elementor-pro' ),
							'checkbox' => __( 'Checkbox', 'elementor-pro' ),
							'radio' => __( 'Radio', 'elementor-pro' ),
							'recaptcha' => __( 'reCAPTCHA', 'elementor-pro' ),
						],
						'default' => 'text',
					],
					[
						'name' => 'field_label',
						'label' => __( 'Label', 'elementor-pro' ),
						'type' => Controls_Manager::TEXT,
						'default' => '',
						'conditions' => [
							'terms' => [
								[
									'name' => 'field_type',
									'operator' => '!in',
									'value' => [
										'recaptcha',
									],
								],
							],
						],
					],
					[
						'name' => 'placeholder',
						'label' => __( 'Placeholder', 'elementor-pro' ),
						'type' => Controls_Manager::TEXT,
						'default' => '',
						'conditions' => [
							'terms' => [
								[
									'name' => 'field_type',
									'operator' => 'in',
									'value' => [
										'tel',
										'text',
										'email',
										'textarea',
										'number',
										'url',
									],
								],
							],
						],
					],
					[
						'name' => 'required',
						'label' => __( 'Required', 'elementor-pro' ),
						'type' => Controls_Manager::SWITCHER,
						'label_on' => __( 'Yes', 'elementor-pro' ),
						'label_off' => __( 'No', 'elementor-pro' ),
						'return_value' => true,
						'default' => '',
						'conditions' => [
							'terms' => [
								[
									'name' => 'field_type',
									'operator' => '!in',
									'value' => [
										'checkbox',
										'recaptcha',
									],
								],
							],
						],

					],
					[
						'name' => 'field_options',
						'label' => __( 'Options', 'elementor-pro' ),
						'type' => Controls_Manager::TEXTAREA,
						'default' => '',
						'description' => 'Enter each option in a separate line',
						'conditions' => [
							'terms' => [
								[
									'name' => 'field_type',
									'operator' => 'in',
									'value' => [
										'select',
										'checkbox',
										'radio',
									],
								],
							],
						],
					],
					[
						'name' => 'inline_list',
						'label' => __( 'Inline List', 'elementor-pro' ),
						'type' => Controls_Manager::SWITCHER,
						'label_on' => __( 'Yes', 'elementor-pro' ),
						'label_off' => __( 'No', 'elementor-pro' ),
						'return_value' => 'elementor-subgroup-inline',
						'default' => '',
						'conditions' => [
							'terms' => [
								[
									'name' => 'field_type',
									'operator' => 'in',
									'value' => [
										'checkbox',
										'radio',
									],
								],
							],
						],
					],
					[
						'name' => 'width',
						'label' => __( 'Column Width', 'elementor-pro' ),
						'type' => Controls_Manager::SELECT,
						'options' => [
							'100' => '100%',
							'80' => '80%',
							'75' => '75%',
							'66' => '66%',
							'60' => '60%',
							'50' => '50%',
							'40' => '40%',
							'33' => '33%',
							'25' => '25%',
							'20' => '20%',
						],
						'default' => '100',
						'conditions' => [
							'terms' => [
								[
									'name' => 'field_type',
									'operator' => '!in',
									'value' => [
										'recaptcha',
									],
								],
							],
						],
					],
					[
						'name' => 'rows',
						'label' => __( 'Rows', 'elementor-pro' ),
						'type' => Controls_Manager::NUMBER,
						'default' => 4,
						'conditions' => [
							'terms' => [
								[
									'name' => 'field_type',
									'value' => 'textarea',
								],
							],
						],
					],
					[
						'name' => 'recaptcha_size',
						'label' => __( 'Size', 'elementor-pro' ),
						'type' => Controls_Manager::SELECT,
						'default' => 'normal',
						'options' => [
							'normal' => __( 'Normal', 'elementor-pro' ),
							'compact' => __( 'Compact', 'elementor-pro' ),
						],
						'conditions' => [
							'terms' => [
								[
									'name' => 'field_type',
									'value' => 'recaptcha',
								],
							],
						],
					],
					[
						'name' => 'recaptcha_style',
						'label' => __( 'Style', 'elementor-pro' ),
						'type' => Controls_Manager::SELECT,
						'default' => 'light',
						'options' => [
							'light' => __( 'Light', 'elementor-pro' ),
							'dark' => __( 'Dark', 'elementor-pro' ),
						],
						'conditions' => [
							'terms' => [
								[
									'name' => 'field_type',
									'value' => 'recaptcha',
								],
							],
						],
					],
					[
						'name' => 'css_classes',
						'label' => __( 'CSS Classes', 'elementor-pro' ),
						'type' => Controls_Manager::HIDDEN,
						'default' => '',
						'title' => __( 'Add your custom class WITHOUT the dot. e.g: my-class', 'elementor-pro' ),
					],
				],
				'default' => [
					[
						'field_type' => 'text',
						'field_label' => __( 'Name', 'elementor-pro' ),
						'placeholder' => __( 'Name', 'elementor-pro' ),
						'width' => '100',
					],
					[
						'field_type' => 'email',
						'required' => true,
						'field_label' => __( 'Email', 'elementor-pro' ),
						'placeholder' => __( 'Email', 'elementor-pro' ),
						'width' => '100',
					],
					[
						'field_type' => 'textarea',
						'field_label' => __( 'Message', 'elementor-pro' ),
						'placeholder' => __( 'Message', 'elementor-pro' ),
						'width' => '100',
					],
				],
				'title_field' => '{{{ field_label }}}',
			]
		);

		$this->add_control(
			'input_size',
			[
				'label' => __( 'Input Size', 'elementor-pro' ),
				'type' => Controls_Manager::SELECT,
				'options' => [
					'xs' => __( 'Extra Small', 'elementor-pro' ),
					'sm' => __( 'Small', 'elementor-pro' ),
					'md' => __( 'Medium', 'elementor-pro' ),
					'lg' => __( 'Large', 'elementor-pro' ),
					'xl' => __( 'Extra Large', 'elementor-pro' ),
				],
				'default' => 'sm',
				'separator' => 'before',
			]
		);

		$this->add_control(
			'show_labels',
			[
				'label' => __( 'Labels', 'elementor-pro' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => __( 'Show', 'elementor-pro' ),
				'label_off' => __( 'Hide', 'elementor-pro' ),
				'return_value' => true,
				'default' => true,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'label_position',
			[
				'label' => __( 'Label Position', 'elementor-pro' ),
				'type' => Controls_Manager::HIDDEN,
				'options' => [
					'above' => __( 'Above', 'elementor-pro' ),
					'inline' => __( 'Inline', 'elementor-pro' ),
				],
				'default' => 'above',
				'condition' => [
					'show_labels!' => '',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_submit_button',
			[
				'label' => __( 'Submit Button', 'elementor-pro' ),
			]
		);

		$this->add_control(
			'button_text',
			[
				'label' => __( 'Text', 'elementor-pro' ),
				'type' => Controls_Manager::TEXT,
				'default' => __( 'Send', 'elementor-pro' ),
				'placeholder' => __( 'Send', 'elementor-pro' ),
			]
		);

		$this->add_control(
			'button_size',
			[
				'label' => __( 'Size', 'elementor-pro' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'sm',
				'options' => self::get_button_sizes(),
			]
		);

		$this->add_control(
			'button_width',
			[
				'label' => __( 'Column Width', 'elementor-pro' ),
				'type' => Controls_Manager::SELECT,
				'options' => [
					'100' => '100%',
					'80' => '80%',
					'75' => '75%',
					'66' => '66%',
					'60' => '60%',
					'50' => '50%',
					'40' => '40%',
					'33' => '33%',
					'25' => '25%',
					'20' => '20%',
				],
				'default' => '100',
			]
		);

		$this->add_responsive_control(
			'button_align',
			[
				'label' => __( 'Alignment', 'elementor-pro' ),
				'type' => Controls_Manager::CHOOSE,
				'options' => [
					'start' => [
						'title' => __( 'Left', 'elementor-pro' ),
						'icon' => 'fa fa-align-left',
					],
					'center' => [
						'title' => __( 'Center', 'elementor-pro' ),
						'icon' => 'fa fa-align-center',
					],
					'end' => [
						'title' => __( 'Right', 'elementor-pro' ),
						'icon' => 'fa fa-align-right',
					],
					'stretch' => [
						'title' => __( 'Justified', 'elementor-pro' ),
						'icon' => 'fa fa-align-justify',
					],
				],
				'default' => 'stretch',
			]
		);

		$this->add_control(
			'button_icon',
			[
				'label' => __( 'Icon', 'elementor-pro' ),
				'type' => Controls_Manager::ICON,
				'label_block' => true,
				'default' => '',
			]
		);

		$this->add_control(
			'button_icon_align',
			[
				'label' => __( 'Icon Position', 'elementor-pro' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'left',
				'options' => [
					'left' => __( 'Before', 'elementor-pro' ),
					'right' => __( 'After', 'elementor-pro' ),
				],
				'condition' => [
					'button_icon!' => '',
				],
			]
		);

		$this->add_control(
			'button_icon_indent',
			[
				'label' => __( 'Icon Spacing', 'elementor-pro' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'max' => 50,
					],
				],
				'condition' => [
					'button_icon!' => '',
				],
				'selectors' => [
					'{{WRAPPER}} .elementor-button .elementor-align-icon-right' => 'margin-left: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .elementor-button .elementor-align-icon-left' => 'margin-right: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_form_options',
			[
				'label' => __( 'Emails & Options', 'elementor-pro' ),
				'tab' => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'email_to',
			[
				'label' => __( 'Email-To', 'elementor-pro' ),
				'type' => Controls_Manager::TEXT,
				'default' => get_option( 'admin_email' ),
				'placeholder' => get_option( 'admin_email' ),
				'label_block' => true,
				'title' => __( 'Separate emails with commas', 'elementor-pro' ),
			]
		);

		$default_message = sprintf( __( 'New message from "%s"', 'elementor-pro' ), get_option( 'blogname' ) );

		$this->add_control(
			'email_subject',
			[
				'label' => __( 'Email Subject', 'elementor-pro' ),
				'type' => Controls_Manager::TEXT,
				'default' => $default_message,
				'placeholder' => $default_message,
				'label_block' => true,
			]
		);

		$site_domain = Utils::get_site_domain();

		$this->add_control(
			'email_from',
			[
				'label' => __( 'From Email', 'elementor-pro' ),
				'type' => Controls_Manager::TEXT,
				'default' => 'email@' . $site_domain,
			]
		);

		$this->add_control(
			'email_from_name',
			[
				'label' => __( 'From Name', 'elementor-pro' ),
				'type' => Controls_Manager::TEXT,
				'default' => get_bloginfo( 'name' ),
			]
		);

		$this->add_control(
			'email_reply_to',
			[
				'label' => __( 'Reply-To', 'elementor-pro' ),
				'type' => Controls_Manager::SELECT,
				'options' => [
					'' => '',
				],
			]
		);

		$this->add_control(
			'form_metadata',
			[
				'label' => __( 'Meta Data', 'elementor-pro' ),
				'type' => Controls_Manager::SELECT2,
				'multiple' => true,
				'label_block' => true,
				'separator' => 'before',
				'default' => [
					'date',
					'time',
					'page_url',
					'user_agent',
					'remote_ip',
					'credit',
				],
				'options' => [
					'date' => __( 'Date', 'elementor-pro' ),
					'time' => __( 'Time', 'elementor-pro' ),
					'page_url' => __( 'Page URL', 'elementor-pro' ),
					'user_agent' => __( 'User Agent', 'elementor-pro' ),
					'remote_ip' => __( 'Remote IP', 'elementor-pro' ),
					'credit' => __( 'Credit', 'elementor-pro' ),
				],
			]
		);

		$this->add_control(
			'redirect_to',
			[
				'label' => __( 'Redirect To', 'elementor-pro' ),
				'type' => Controls_Manager::TEXT,
				'placeholder' => home_url( '/thank-you' ),
				'label_block' => true,
			]
		);

		$this->add_control(
			'webhooks',
			[
				'label' => __( 'Webhook URL', 'elementor-pro' ),
				'type' => Controls_Manager::TEXT,
				'placeholder' => 'https://your-webhook-url',
				'label_block' => true,
				'separator' => 'before',
				'description' => __( 'Enter the integration URL (like Zapier) that will receive the form\'s submitted data.', 'elementor-pro' ),
			]
		);

		$this->add_control(
			'custom_messages',
			[
				'label' => __( 'Custom Messages', 'elementor-pro' ),
				'type' => Controls_Manager::SWITCHER,
				'default' => '',
				'separator' => 'before',
				'label_on' => __( 'Yes', 'elementor-pro' ),
				'label_off' => __( 'No', 'elementor-pro' ),
			]
		);

		$default_messages = Ajax_Handler::get_default_messages();

		$this->add_control(
			'success_message',
			[
				'label' => __( 'Success Message', 'elementor-pro' ),
				'type' => Controls_Manager::TEXT,
				'default' => $default_messages[ Ajax_Handler::SUCCESS ],
				'placeholder' => $default_messages[ Ajax_Handler::SUCCESS ],
				'label_block' => true,
				'condition' => [
					'custom_messages!' => '',
				],
			]
		);

		$this->add_control(
			'error_message',
			[
				'label' => __( 'Error Message', 'elementor-pro' ),
				'type' => Controls_Manager::TEXT,
				'default' => $default_messages[ Ajax_Handler::ERROR ],
				'placeholder' => $default_messages[ Ajax_Handler::ERROR ],
				'label_block' => true,
				'condition' => [
					'custom_messages!' => '',
				],
			]
		);

		$this->add_control(
			'required_field_message',
			[
				'label' => __( 'Required field Message', 'elementor-pro' ),
				'type' => Controls_Manager::TEXT,
				'default' => $default_messages[ Ajax_Handler::FIELD_REQUIRED ],
				'placeholder' => $default_messages[ Ajax_Handler::FIELD_REQUIRED ],
				'label_block' => true,
				'condition' => [
					'custom_messages!' => '',
				],
			]
		);

		$this->add_control(
			'invalid_message',
			[
				'label' => __( 'Invalid Message', 'elementor-pro' ),
				'type' => Controls_Manager::TEXT,
				'default' => $default_messages[ Ajax_Handler::INVALID_FORM ],
				'placeholder' => $default_messages[ Ajax_Handler::INVALID_FORM ],
				'label_block' => true,
				'condition' => [
					'custom_messages!' => '',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_form_style',
			[
				'label' => __( 'Form', 'elementor-pro' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'column_gap',
			[
				'label' => __( 'Column gap', 'elementor-pro' ),
				'type' => Controls_Manager::SLIDER,
				'default' => [
					'size' => 10,
				],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 60,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .elementor-field-group' => 'padding-right: calc( {{SIZE}}{{UNIT}}/2 ); padding-left: calc( {{SIZE}}{{UNIT}}/2 );',
					'{{WRAPPER}} .elementor-form-fields-wrapper' => 'margin-left: calc( -{{SIZE}}{{UNIT}}/2 ); margin-right: calc( -{{SIZE}}{{UNIT}}/2 );',
				],
			]
		);

		$this->add_control(
			'row_gap',
			[
				'label' => __( 'Row gap', 'elementor-pro' ),
				'type' => Controls_Manager::SLIDER,
				'default' => [
					'size' => 10,
				],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 60,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .elementor-field-group' => 'margin-bottom: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .elementor-form-fields-wrapper' => 'margin-bottom: -{{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'heading_label',
			[
				'label' => __( 'Label', 'elementor-pro' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => [
					'show_labels!' => '',
				],
			]
		);

		$this->add_control(
			'label_spacing',
			[
				'label' => __( 'Spacing', 'elementor-pro' ),
				'type' => Controls_Manager::SLIDER,
				'default' => [
					'size' => 0,
				],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 60,
					],
				],
				'selectors' => [
					'body.rtl {{WRAPPER}} .elementor-labels-inline .elementor-field-group > label' => 'padding-left: {{SIZE}}{{UNIT}};',
					// for the label position = inline option
					'body:not(.rtl) {{WRAPPER}} .elementor-labels-inline .elementor-field-group > label' => 'padding-right: {{SIZE}}{{UNIT}};',
					// for the label position = inline option
					'body {{WRAPPER}} .elementor-labels-above .elementor-field-group > label' => 'padding-bottom: {{SIZE}}{{UNIT}};',
					// for the label position = above option
				],
				'condition' => [
					'show_labels!' => '',
				],
			]
		);

		$this->add_control(
			'label_color',
			[
				'label' => __( 'Text Color', 'elementor-pro' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .elementor-field-group > label' => 'color: {{VALUE}};',
				],
				'scheme' => [
					'type' => Scheme_Color::get_type(),
					'value' => Scheme_Color::COLOR_3,
				],
				'condition' => [
					'show_labels!' => '',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'label_typography',
				'selector' => '{{WRAPPER}} .elementor-field-group > label',
				'scheme' => Scheme_Typography::TYPOGRAPHY_3,
				'condition' => [
					'show_labels!' => '',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_field_style',
			[
				'label' => __( 'Field', 'elementor-pro' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'field_text_color',
			[
				'label' => __( 'Text Color', 'elementor-pro' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .elementor-field-group .elementor-field, {{WRAPPER}} .elementor-field-subgroup' => 'color: {{VALUE}};',
				],
				'scheme' => [
					'type' => Scheme_Color::get_type(),
					'value' => Scheme_Color::COLOR_3,
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'field_typography',
				'selector' => '{{WRAPPER}} .elementor-field-group .elementor-field',
				'scheme' => Scheme_Typography::TYPOGRAPHY_3,
			]
		);

		$this->add_control(
			'field_background_color',
			[
				'label' => __( 'Background Color', 'elementor-pro' ),
				'type' => Controls_Manager::COLOR,
				'default' => '#ffffff',
				'selectors' => [
					'{{WRAPPER}} .elementor-field-group .elementor-field:not(.elementor-select-wrapper)' => 'background-color: {{VALUE}};',
					'{{WRAPPER}} .elementor-field-group .elementor-select-wrapper select' => 'background-color: {{VALUE}};',
				],
				'separator' => 'before',
			]
		);

		$this->add_control(
			'field_border_color',
			[
				'label' => __( 'Border Color', 'elementor-pro' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .elementor-field-group .elementor-field:not(.elementor-select-wrapper)' => 'border-color: {{VALUE}};',
					'{{WRAPPER}} .elementor-field-group .elementor-select-wrapper select' => 'border-color: {{VALUE}};',
					'{{WRAPPER}} .elementor-field-group .elementor-select-wrapper::before' => 'color: {{VALUE}};',
				],
				'separator' => 'before',
			]
		);

		$this->add_control(
			'field_border_width',
			[
				'label' => __( 'Border  Width', 'elementor-pro' ),
				'type' => Controls_Manager::DIMENSIONS,
				'placeholder' => '1',
				'size_units' => [ 'px' ],
				'selectors' => [
					'{{WRAPPER}} .elementor-field-group .elementor-field:not(.elementor-select-wrapper)' => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					'{{WRAPPER}} .elementor-field-group .elementor-select-wrapper select' => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'field_border_radius',
			[
				'label' => __( 'Border Radius', 'elementor-pro' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .elementor-field-group .elementor-field:not(.elementor-select-wrapper)' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					'{{WRAPPER}} .elementor-field-group .elementor-select-wrapper select' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_button_style',
			[
				'label' => __( 'Button', 'elementor-pro' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->start_controls_tabs( 'tabs_button_style' );

		$this->start_controls_tab(
			'tab_button_normal',
			[
				'label' => __( 'Normal', 'elementor-pro' ),
			]
		);

		$this->add_control(
			'button_text_color',
			[
				'label' => __( 'Text Color', 'elementor-pro' ),
				'type' => Controls_Manager::COLOR,
				'default' => '',
				'selectors' => [
					'{{WRAPPER}} .elementor-button' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'button_typography',
				'label' => __( 'Typography', 'elementor-pro' ),
				'scheme' => Scheme_Typography::TYPOGRAPHY_4,
				'selector' => '{{WRAPPER}} .elementor-button',
			]
		);

		$this->add_control(
			'button_background_color',
			[
				'label' => __( 'Background Color', 'elementor-pro' ),
				'type' => Controls_Manager::COLOR,
				'scheme' => [
					'type' => Scheme_Color::get_type(),
					'value' => Scheme_Color::COLOR_4,
				],
				'selectors' => [
					'{{WRAPPER}} .elementor-button' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(), [
				'name' => 'button_border',
				'label' => __( 'Border', 'elementor-pro' ),
				'placeholder' => '1px',
				'default' => '1px',
				'selector' => '{{WRAPPER}} .elementor-button',
			]
		);

		$this->add_control(
			'button_border_radius',
			[
				'label' => __( 'Border Radius', 'elementor-pro' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .elementor-button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'button_text_padding',
			[
				'label' => __( 'Text Padding', 'elementor-pro' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .elementor-button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_button_hover',
			[
				'label' => __( 'Hover', 'elementor-pro' ),
			]
		);

		$this->add_control(
			'button_hover_color',
			[
				'label' => __( 'Text Color', 'elementor-pro' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .elementor-button:hover' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'button_background_hover_color',
			[
				'label' => __( 'Background Color', 'elementor-pro' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .elementor-button:hover' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'button_hover_border_color',
			[
				'label' => __( 'Border Color', 'elementor-pro' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .elementor-button:hover' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'button_hover_animation',
			[
				'label' => __( 'Animation', 'elementor-pro' ),
				'type' => Controls_Manager::HOVER_ANIMATION,
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	private function make_textarea_field( $item, $item_index ) {
		$this->add_render_attribute( 'textarea' . $item_index, [
			'class' => [
				'elementor-field-textual',
				'elementor-field',
				esc_attr( $item['css_classes'] ),
				'elementor-size-' . $item['input_size'],
			],
			'name' => $this->get_attribute_name( $item_index ),
			'id' => $this->get_attribute_id( $item_index ),
			'rows' => $item['rows'],
		] );

		if ( $item['placeholder'] ) {
			$this->add_render_attribute( 'textarea' . $item_index , 'placeholder', $item['placeholder'] );
		}

		if ( $item['required'] ) {
			$this->add_render_attribute( 'textarea' . $item_index , 'required', true );
		}

		return '<textarea ' . $this->get_render_attribute_string( 'textarea' . $item_index ) . '></textarea>';
	}

	private function make_select_field( $item, $i ) {
		$this->add_render_attribute(
			[
				'select-wrapper' . $i => [
					'class' => [
						'elementor-field',
						'elementor-select-wrapper',
						esc_attr( $item['css_classes'] ),
					],
				],
				'select' . $i => [
					'name' => $this->get_attribute_name( $i ),
					'id' => $this->get_attribute_id( $i ),
					'class' => [
						'elementor-field-textual',
						'elementor-size-' . $item['input_size'],
					],
				],
			]
		);

		if ( $item['required'] ) {
			$this->add_render_attribute( 'select' . $i , 'required', true );
		}

		$options = preg_split( "/\\r\\n|\\r|\\n/", $item['field_options'] );

		if ( ! $options ) {
			return '';
		}

		ob_start();
		?>
		<div <?php echo $this->get_render_attribute_string( 'select-wrapper' . $i ); ?>>
			<select <?php echo $this->get_render_attribute_string( 'select' . $i ); ?>>
				<?php
				foreach ( $options as $option ) : ?>
					<option value="<?php echo esc_attr( $option ); ?>"><?php echo $option; ?></option>
				<?php endforeach; ?>
			</select>
		</div>
		<?php

		return ob_get_clean();
	}

	private function make_radio_checkbox_field( $item, $item_index, $type ) {
		$options = preg_split( "/\\r\\n|\\r|\\n/", $item['field_options'] );
		$html    = '';
		if ( $options ) {
			$html .= '<div class="elementor-field-subgroup ' . esc_attr( $item['css_classes'] ) . ' ' . $item['inline_list'] . '">';
			foreach ( $options as $key => $option ) {
				$html .= '<span class="elementor-field-option"><input type="' . $type . '"
							value="' . esc_attr( $option ) . '"
							id="' . $this->get_attribute_id( $item_index ) . '-' . $key . '"
							name="' . $this->get_attribute_name( $item_index ) . ( ( 'checkbox' === $type && count( $options ) > 1 ) ? '[]"' : '"' ) .
				         ( ( $item['required'] && 'radio' === $type )  ? ' required' : '' ) . '>
							<label for="' . $this->get_attribute_id( $item_index ) . '-' . $key . '">' . $option . '</label></span>';
			}
			$html .= '</div>';
		}
		return $html;
	}

	private function form_fields_render_attributes( $i, $instance, $item ) {
		$this->add_render_attribute(
			[
				'field-group' . $i => [
					'class' => [
						'elementor-field-type-' . $item['field_type'],
						'elementor-field-group',
						'elementor-column',
					],
					'data-col' => $item['width'],
				],
				'input' . $i => [
					'type' => $item['field_type'],
					'name' => $this->get_attribute_name( $i ),
					'id' => $this->get_attribute_id( $i ),
					'class' => [
						'elementor-field',
						'elementor-size-' . $item['input_size'],
						esc_attr( $item['css_classes'] ),
					],
				],
				'label' . $i => [
					'for' => $this->get_attribute_id( $i ),
					'class' => 'elementor-field-label',
				],
			]
		);

		if ( $item['placeholder'] ) {
			$this->add_render_attribute( 'input' . $i , 'placeholder', $item['placeholder'] );
		}

		if ( ! $instance['show_labels'] ) {
			$this->add_render_attribute( 'label' . $i, 'class', 'elementor-screen-only' );
		}

		if ( $item['required'] ) {
			$this->add_render_attribute( 'field-group' . $i , 'class', 'elementor-field-required' )
				 ->add_render_attribute( 'input' . $i , 'required', true );
		}
	}

	protected function render() {
		$instance = $this->get_settings();

		$this->add_render_attribute(
			[
				'wrapper' => [
					'class' => [
						'elementor-form-fields-wrapper',
						'elementor-labels-' . $instance['label_position'],
					],
				],
				'submit-group' => [
					'class' => [
						'elementor-field-group',
						'elementor-column',
						'elementor-field-type-submit',
						'elementor-button-align-' . $instance['button_align'],
					],
					'data-col' => $instance['button_width'],
				],
				'button' => [
					'class' => 'elementor-button',
				],
				'icon-align' => [
					'class' => [
						'elementor-align-icon-' . $instance['button_icon_align'],
						'elementor-button-icon',
					],
				],
			]
		);

		if ( ! empty( $instance['button_size'] ) ) {
			$this->add_render_attribute( 'button', 'class', 'elementor-size-' . $instance['button_size'] );
		}

		if ( ! empty( $instance['button_type'] ) ) {
			$this->add_render_attribute( 'button', 'class', 'elementor-button-' . $instance['button_type'] );
		}

		if ( $instance['button_hover_animation'] ) {
			$this->add_render_attribute( 'button', 'class', 'elementor-animation-' . $instance['button_hover_animation'] );
		}

		?>
		<form class="elementor-form" method="post">
			<input type="hidden" name="post_id" value="<?php echo get_the_ID() ?>" />
			<input type="hidden" name="form_id" value="<?php echo $this->get_id() ?>" />
			<?php wp_nonce_field( 'elementor-pro-form-' . $this->get_id(), '_nonce', true ); ?>

			<div <?php echo $this->get_render_attribute_string( 'wrapper' ); ?>>
				<?php
				foreach ( $instance['form_fields'] as $item_index => $item ) :
					$item['input_size'] = $instance['input_size'];

					$this->form_fields_render_attributes( $item_index, $instance, $item );
				?>
				<div <?php echo $this->get_render_attribute_string( 'field-group' . $item_index ); ?>>
					<?php
					if ( $item['field_label'] ) {
						echo '<label ' . $this->get_render_attribute_string( 'label' . $item_index ) . '>' . $item['field_label'] . '</label>';
					}

					switch ( $item['field_type'] ) :
						case 'textarea':
							echo $this->make_textarea_field( $item, $item_index );
							break;

						case 'select':
							echo $this->make_select_field( $item, $item_index );
							break;

						case 'radio':
						case 'checkbox':
							echo $this->make_radio_checkbox_field( $item, $item_index, $item['field_type'] );
							break;
						case 'recaptcha':
							// TODO: allow register external fields types
							echo Module::instance()->get_component( 'recaptcha' )->make_recaptcha_field( $item, $item_index, $this );
							break;
						case 'text':
						case 'email':
						case 'url':
						case 'password':
						case 'tel':
						case 'number':
						case 'search':
							$this->add_render_attribute( 'input' . $item_index, 'class', 'elementor-field-textual' );
						default:
							echo '<input size="1" ' . $this->get_render_attribute_string( 'input' . $item_index ) . '>';
					endswitch;
					?>
				</div>
				<?php	endforeach; ?>
				<div <?php echo $this->get_render_attribute_string( 'submit-group' ); ?>>
					<button type="submit" <?php echo $this->get_render_attribute_string( 'button' ); ?>>
						<span <?php echo $this->get_render_attribute_string( 'content-wrapper' ); // TODO: what to do about content-wrapper ?>>
							<?php if ( ! empty( $instance['button_icon'] ) ) : ?>
								<span <?php echo $this->get_render_attribute_string( 'icon-align' ); ?>>
									<i class="<?php echo esc_attr( $instance['button_icon'] ); ?>"></i>
								</span>
							<?php endif;
							if ( ! empty( $instance['button_text'] ) ) : ?>
							<span class="elementor-button-text"><?php echo $instance['button_text']; ?></span>
							<?php endif; ?>
						</span>
					</button>
				</div>
			</div>
		</form>
	<?php
	}

	protected function _content_template() {
		?>
		<form class="elementor-form">
			<div class="elementor-form-fields-wrapper elementor-labels-{{settings.label_position}}">
				<#
					for ( var i in settings.form_fields ) {
						var item = settings.form_fields[ i ],
						options = item.field_options ? item.field_options.split( '\n' ) : [],
						itemClasses = _.escape( item.css_classes ),
						labelVisibility = '',
						placeholder = '',
						required = '',
						inputField = '';

						if ( ! settings.show_labels ) {
							labelVisibility = 'class="elementor-screen-only"';
						}

						if ( item.required ) {
							required = 'required';
						}

						if ( item.placeholder ) {
							placeholder = 'placeholder="' + _.escape( item.placeholder ) + '"';
						}
						#>
						<div class="elementor-field-group elementor-column elementor-field-type-{{item.field_type}} {{item.required ? 'elementor-field-required' : ''}}" data-col="{{item.width}}">

							<# if ( item.field_label ) { #>
								<label for="form_field_{{ i }}" {{{ labelVisibility }}} >{{{ item.field_label }}}</label>
							<# } #>

							<# switch ( item.field_type ) {
								case 'textarea':
									inputField = '<textarea class="elementor-field elementor-field-textual elementor-size-' + settings.input_size + ' ' + itemClasses + '" name="form_field_' + i + '" id="form_field_' + i + '" rows="' + item.rows + '" ' + required + ' ' + placeholder + '></textarea>';
									break;

								case 'select':
									if ( options ) {
										inputField = '<div class="elementor-field elementor-select-wrapper ' + itemClasses + '">';
										inputField += '<select class="elementor-field-textual elementor-size-' + settings.input_size + '" name="form_field_' + i + '" id="form_field_' + i + '" ' + required + ' >';
										for ( var x in options ) {
											inputField += '<option value="' + options[x] + '">' + options[x] + '</option>';
										}
										inputField += '</select></div>';
									}
									break;

								case 'radio':
								case 'checkbox':
									if ( options ) {
										var multiple = '';

										if ( 'checkbox' === item.field_type && options.length > 1 ) {
											multiple = '[]';
										}

										inputField = '<div class="elementor-field-subgroup ' + itemClasses + ' ' + item.inline_list + '">';

										for ( var x in options ) {
											inputField += '<span class="elementor-field-option"><input type="' + item.field_type + '" value="' + options[ x ] + '" id="form_field_' + i + '-' + x + '" name="form_field_' + i + multiple + '" ' + required +  '> ';
											inputField += '<label for="form_field_' + i + '-' + x + '">' + options[ x ] + '</label></span>';
										}

										inputField += '</div>';
									}
									break;

								case 'recaptcha':
									inputField += '<div class="elementor-field">';
									<?php if ( Recaptcha_Handler::is_enabled() ) {  ?>
										inputField += '<div class="elementor-g-recaptcha' + itemClasses + '" data-sitekey="<?php echo Recaptcha_Handler::get_site_key() ?>" data-theme="' + item.recaptcha_style + '" data-size="' + item.recaptcha_size + '"></div>';
									<?php } else { ?>
										inputField += '<div class="elementor-alert"><?php echo Recaptcha_Handler::get_setup_message() ?></div>';
									<?php } ?>
									inputField += '</div>';
								break;
								case 'text':
								case 'email':
								case 'url':
								case 'password':
								case 'tel':
								case 'number':
								case 'search':
									itemClasses = 'elementor-field-textual ' + itemClasses;
								default:
									inputField = '<input size="1" type="' + item.field_type + '" class="elementor-field elementor-size-' + settings.input_size + ' ' + itemClasses + '" name="form_field_' + i + '" id="form_field_' + i + '" ' + required + ' ' + placeholder + ' >';
								}
								#>
							{{{ inputField }}}
						</div>
					<# } #>

					<div class="elementor-field-group elementor-column elementor-field-type-submit elementor-button-align-{{ settings.button_align }}" data-col="{{ settings.button_width }}">
						<button type="submit" class="elementor-button elementor-size-{{ settings.button_size }} elementor-button-{{ settings.button_type }} elementor-animation-{{ settings.button_hover_animation }}">
							<span>
								<# if ( settings.button_icon ) { #>
									<span class="elementor-button-icon elementor-align-icon-{{ settings.button_icon_align }}">
										<i class="{{ settings.button_icon }}"></i>
									</span>
								<# } #>

								<# if ( settings.button_text ) { #>
									<span class="elementor-button-text">{{{ settings.button_text }}}</span>
								<# } #>
							</span>
						</button>
					</div>
			</div>
		</form>
		<?php
	}

	public function render_plain_content() {}

	private function get_attribute_name( $item_index ) {
		return "form_fields[{$item_index}]";
	}

	private function get_attribute_id( $item_index ) {
		return 'form-field-' . $item_index;
	}
}
