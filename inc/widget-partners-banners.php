<?php
/**
 * Partner banners widget class
 *
 * @since 1.0.0
 */

class Partners_Banners_Widget extends WP_Widget {

	public function __construct() {
		$widget_ops = array( 'classname' => 'widget_recent_entries', 'description' => __( 'Your site’s partners logos.', 'partners-banners' ) );
		parent::__construct( 'widget-partners-banners', __( 'Partners Banners', 'partners-banners' ), $widget_ops );
		$this->alt_option_name = 'widget_partners_banners';

		add_action( 'save_post', array( &$this, 'flush_widget_cache' ) );
		add_action( 'deleted_post', array( &$this, 'flush_widget_cache' ) );
		add_action( 'switch_theme', array( &$this, 'flush_widget_cache' ) );
	}

	public function widget( $args, $instance ) {
		$cache = array();

		if ( ! $this->is_preview() ) {
			$cache = wp_cache_get( 'widget_partners_banners', 'widget' );
		}

		if ( ! is_array( $cache ) ) {
			$cache = array();
		}

		if ( ! isset( $args['widget_id'] ) ) {
			$args['widget_id'] = $this->id;
		}

		if ( isset( $cache[ $args['widget_id'] ] ) ) {
			echo $cache[ $args['widget_id'] ];
			return;
		}

		ob_start();

		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? __( '', 'partners-banners' ) : $instance['title'], $instance, $this->id_base );
		$posttype = $instance['posttype'];
		if ( empty( $instance['number'] ) || ! $number = absint( $instance['number'] ) ) {
			$number = 5;
		}

		$post_types = get_post_types( array( 'public' => true ), 'objects' );

		if ( array_key_exists( $posttype, (array) $post_types ) ) {
			$r = new WP_Query( array(
				'post_type' => 'partnersbanner',
				'posts_per_page' => $number,
				'no_found_rows' => true,
				'post_status' => 'publish',
				'ignore_sticky_posts' => true,
			) );

			if ( $r->have_posts() ) : ?>
				<?php echo $args['before_widget']; ?>
				<?php if ( $title ) {
					echo $args['before_title'] . $title . $args['after_title'];
				} ?>
				 <div class="partners">
				<?php while ( $r->have_posts() ) : $r->the_post(); ?>
					<div class="partner"><a href="<?php the_permalink() ?>" target="_blank"><img src="<?php echo $partnerimage = wp_get_attachment_url( get_post_thumbnail_id( $post->ID ) );?>" target="_blank" alt="<?php get_the_title() ? the_title() : the_ID(); ?>"></a>
					</div>
				<?php endwhile; ?>
				</div>
				<?php echo $args['after_widget']; ?>
				<?php
				wp_reset_postdata();
			endif;
		}

		if ( ! $this->is_preview() ) {
			$cache[ $args['widget_id'] ] = ob_get_flush();
			wp_cache_set( 'widget_partners_banners', $cache, 'widget' );
		}
		else {
			ob_end_flush();
		}
	}

	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['posttype'] = strip_tags( $new_instance['posttype'] );
		$instance['number'] = (int) $new_instance['number'];

		$this->flush_widget_cache();

		$alloptions = wp_cache_get( 'alloptions', 'options' );
		if ( isset( $alloptions['widget_partners_banners'] ) ) {
			delete_option( 'widget_partners_banners' );
		}

		return $instance;
	}

	public function flush_widget_cache() {
		wp_cache_delete( 'widget_partners_banners', 'widget' );
	}

	public function form( $instance ) {
		$title = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : '';
		$posttype = isset( $instance['posttype'] ) ? $instance['posttype']: 'post';
		$number = isset( $instance['number'] ) ? absint( $instance['number'] ) : 5;
?>
		<p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'partners-banners' ); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" />
				<input name="<?php echo $this->get_field_name( 'posttype' ); ?>" id="<?php echo $this->get_field_id( 'posttype' ); ?>" value="partnersbanner" type="hidden">
		</p>

		<p><label for="<?php echo $this->get_field_id( 'number' ); ?>"><?php _e( 'Number of partners to show:', 'partners-banners' ); ?></label>
		<input id="<?php echo $this->get_field_id( 'number' ); ?>" name="<?php echo $this->get_field_name( 'number' ); ?>" type="text" value="<?php echo $number; ?>" size="3" /></p>
<?php
	}
}
