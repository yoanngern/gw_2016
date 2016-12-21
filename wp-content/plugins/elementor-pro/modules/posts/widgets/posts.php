<?php
namespace ElementorPro\Modules\Posts\Widgets;

use Elementor\Widget_Base;
use ElementorPro\Modules\PanelPostsControl\Controls\Group_Control_Posts;
use ElementorPro\Modules\PanelPostsControl\Module;
use ElementorPro\Modules\Posts\Skins;
use Elementor\Controls_Manager;

/**
 * Class Posts
 */
class Posts extends Widget_Base {

	/**
	 * @var \WP_Query
	 */
	private $query = null;

	protected $_has_template_content = false;

	public function get_name() {
		return 'posts';
	}

	public function get_title() {
		return __( 'Posts', 'elementor-pro' );
	}

	public function get_icon() {
		return 'eicon-post-list';
	}

	public function get_categories() {
		return [ 'pro-elements' ];
	}

	public function on_import( $element ) {
		if ( ! get_post_type_object( $element['settings']['post_type'] ) ) {
			$element['settings']['post_type'] = 'post';
		}

		return $element;
	}

	protected function _register_skins() {
		$this->add_skin( new Skins\Skin_Classic( $this ) );
	}

	public function get_query() {
		return $this->query;
	}

	protected function _register_controls() {
		$this->register_query_section_controls();
	}

	public function render() {}

	private function get_authors() {
		$user_query = new \WP_User_Query(
			[
				'who' => 'authors',
				'has_published_posts' => true,
				'fields' => [
					'ID',
					'display_name',
				],
			]
		);

		$authors = [];

		foreach ( $user_query->get_results() as $result ) {
			$authors[ $result->ID ] = $result->display_name;
		}

		return $authors;
	}

	private function register_query_section_controls() {
		$this->start_controls_section(
			'section_layout',
			[
				'label' => __( 'Layout', 'elementor-pro' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_query',
			[
				'label' => __( 'Query', 'elementor-pro' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_group_control(
			Group_Control_Posts::get_type(),
			[
				'name' => 'posts',
				'label' => __( 'Posts', 'elementor-pro' ),
			]
		);

		$this->add_control(
			'authors',
			[
				'label'   => __( 'Authors', 'elementor-pro' ),
				'type'    => Controls_Manager::SELECT2,
				'label_block' => true,
				'multiple'    => true,
				'default' => [],
				'options' => $this->get_authors(),
				'condition' => [
					'posts_post_type!' => 'by_id',
				],
			]
		);

		$this->add_control(
			'advanced',
			[
				'label'   => __( 'Advanced', 'elementor-pro' ),
				'type'    => Controls_Manager::HEADING,
			]
		);

		$this->add_control(
			'orderby',
			[
				'label'   => __( 'Order By', 'elementor-pro' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'post_date',
				'options' => [
					'post_date'  => __( 'Date', 'elementor-pro' ),
					'post_title' => __( 'Title', 'elementor-pro' ),
					'menu_order' => __( 'Menu Order', 'elementor-pro' ),
					'rand'       => __( 'Random', 'elementor-pro' ),
				],
			]
		);

		$this->add_control(
			'order',
			[
				'label'   => __( 'Order', 'elementor-pro' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'desc',
				'options' => [
					'asc'  => __( 'ASC', 'elementor-pro' ),
					'desc' => __( 'DESC', 'elementor-pro' ),
				],
			]
		);

		$this->add_control(
			'offset',
			[
				'label'   => __( 'Offset', 'elementor-pro' ),
				'type'    => Controls_Manager::NUMBER,
				'default' => 0,
				'condition' => [
					'post_type!' => 'by_id',
				],
				'description' => __( 'Use this setting to skip over posts (e.g. \'2\' to skip over 2 posts).', 'elementor-pro' ),
			]
		);

		$this->end_controls_section();
	}

	public function query_posts() {
		$query_args = Module::get_query_args( 'posts', $this->get_settings() );

		$query_args['posts_per_page'] = $this->get_current_skin()->get_instance_value( 'posts_per_page' );

		$this->query = new \WP_Query( $query_args );
	}
}
