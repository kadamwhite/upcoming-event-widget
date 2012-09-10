<?php
/*
Plugin Name: Upcoming Event Widget
Plugin URI:
Description: Registers a widget to display the date and title of a single upcoming event
Version: 0.1
Author: K.Adam White
Author URI: http://www.kadamwhite.com
License: MIT / GPL2
License URI:
*/

function _bwp_upcoming_event_register_widget() {
	register_widget( '_bwp_upcoming_event_widget' );
}
add_action( 'widgets_init', '_bwp_upcoming_event_register_widget' );

function _bwp_enqueue_scripts($hook) {
	if( 'widgets.php' != $hook)
		return;
	wp_enqueue_script( 'jquery-ui-datepicker' );
	wp_enqueue_style( 'wp-jquery-ui-dialog' );
	wp_enqueue_style( 'jquery-ui-datepicker', plugins_url( '/css/jquery-ui-1.8.23.custom.css', __FILE__ ), false ); // Inside a plugin
}
add_action( 'admin_enqueue_scripts', '_bwp_enqueue_scripts' );

class _bwp_upcoming_event_widget extends WP_Widget {

	function _bwp_upcoming_event_widget() {
		$widget_ops = array(
			'classname' => '_bwp_upcoming_event_widget', // Rendered ID of widget
			'description' => __('A widget that displays an alert for an upcoming event ', 'example')
			);

		$this->WP_Widget('_bwp_upcoming_event_widget', __('Upcoming Event', 'example'), $widget_ops );
	}

	// Edit Widget form
	function form( $instance ) {

		//Set up some default widget settings.
		$defaults = array(
			'title' => __('Next Event', 'example'),
			'date' => __('', 'example'),
			'time' => __('', 'example'),
			'description' => __('Our awesome monthly meetup', 'example')
			);

		$instance = wp_parse_args( (array) $instance, $defaults );

		$title = $instance['title'];
		$date = date( 'l, F j Y', $instance['date'] );
		$time = $instance['time'];
		$description = $instance['description'];

		?>
		<p><?php _e('Title', 'example'); ?>: <input class="widefat" type="text"
			id="<?php echo $this->get_field_id( 'title' ); ?>"
			name="<?php echo $this->get_field_name( 'title' ); ?>"
			value="<?php echo esc_attr( $title ); ?>" style="width:100%;" />
		</p>
		<p><?php _e('Date', 'example'); ?>: <input class="widefat datepicker" type="text"
			placeholder="<?php echo __('Select event date', 'example'); ?>"
			id="<?php echo $this->get_field_id( 'date' ); ?>"
			name="<?php echo $this->get_field_name( 'date' ); ?>"
			value="<?php echo esc_attr( $date ); ?>" style="width:100%;" />
		</p>
		<p><?php _e('Time', 'example'); ?>: <textarea class="widefat" type="text"
			id="<?php echo $this->get_field_id( 'time' ); ?>"
			name="<?php echo $this->get_field_name( 'time' ); ?>"
			style="width:100%;"><?php echo esc_attr( $time ); ?></textarea>
		</p>
		<p><?php _e('Event Description (optional)', 'example'); ?>: <textarea class="widefat" type="text"
			id="<?php echo $this->get_field_id( 'description' ); ?>"
			name="<?php echo $this->get_field_name( 'description' ); ?>"
			style="width:100%;"><?php echo esc_attr( $description ); ?></textarea>
		</p>
		<?php /* TODO: Is this worth breaking out into a seperate script file? */ ?>
		<script type="text/javascript">
			jQuery(document).ready(function($){
				$('.datepicker')
					// Disable manual editing of date field
					.attr('readonly', 'true')
					// Alter styles to make it look still editable (Ugly!!)
					.css({
						'background-color': 'white',
						'cursor': 'text'
					})
					// Attach datepicker control
					.datepicker({
						dateFormat: 'DD, MM d yy'
					});
			});
	   </script>
		<?php
	}

	// Update the stored values on save
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		// Sanitize input
		$instance['title'] = strip_tags( $new_instance['title'] );
		// Store time as UNIX timestamp
		$instance['date'] = strtotime( strip_tags( $new_instance['date'] ) );
		$instance['time'] = strip_tags( $new_instance['time'] );
		$instance['description'] = strip_tags( $new_instance['description'] );

		return $instance;
	}

	// Render the widget
	function widget( $args, $instance ) {
		extract( $args );

		//Our variables from the widget settings.
		$title = apply_filters( 'widget_title', $instance['title'] );
		$month = empty( $instance['date'] ) ? '' : date( 'F', $instance['date'] );
		$day = empty( $instance['date'] ) ? '' : date( 'j', $instance['date'] );
		$time = empty( $instance['time'] ) ? '' : $instance['time'];
		$description = empty( $instance['description'] ) ? '' : $instance['description'];

		// TODO: Return without rendering if...
		// (a): Date is not set
		// (b): Date is in the past
		// (c): No description is given

		echo $before_widget;

		// Display the widget title
		if ( !empty( $title ) ) {
			echo $before_title . $title . $after_title;
		}
		?>
		<div class="event-date-container">
			<div class="event-date">
				<span class="month"><?php echo $month; ?></span>
				<span class="day"><?php echo $day; ?></span>
			</div>
		</div>
		<?php

		if ( $time )
			printf( '<p>' . __('%1$s at %2$s', 'example') . '</p>', $time, 'location' );
		if ( $description )
			printf( '<p class="event-description">' . __('%1$s.', 'example') . '</p>', $description );

		echo $after_widget;
?>
		<style type="text/css">
			.bwp_upcoming_event_widget {
				overflow: hidden;
			}
			.bwp_upcoming_event_widget .event-date-container {
				width: 100px;
				height: 85px;
				float: right;
				position: relative;
				text-align: center;
				font-family: Helvetica, Arial, sans-serif;
				text-transform: uppercase;
				font-weight: bold;
			}
			.bwp_upcoming_event_widget .event-date {
				border: 1px solid black;
				position: absolute;
				right: 5px;
				top: -20px;
				width: 100%;
				-webkit-transform: rotate(5deg) translate3d(0, 0, 0);
				-moz-transform:    rotate(5deg) translate3d(0, 0, 0);
				-ms-transform:     rotate(5deg) translate3d(0, 0, 0);
				-o-transform:      rotate(5deg) translate3d(0, 0, 0);
				transform:         rotate(5deg) translate3d(0, 0, 0);
			}
			.bwp_upcoming_event_widget .event-date .month {
				background-color: red;
				color: white;
				display: block;
				font-size: 14px;
				padding: 3px 5px;
				width: 90px;
			}
			.bwp_upcoming_event_widget .event-date .day {
				background-color: white;
				font-size: 50px;
				line-height: 70px;
			}
			.bwp_upcoming_event_widget .event-description {
				clear: both;
			}
		</style>
<?php
	}
}