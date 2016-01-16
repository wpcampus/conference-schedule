<?php

class Conference_Schedule_API {

	/**
	 * Warming things up.
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	public function __construct() {

		// Register any REST fields
		add_action( 'rest_api_init', array( $this, 'register_rest_fields' ), 20 );

	}

	/**
	 * Register additional fields for the REST API.
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	public function register_rest_fields() {

		// The args are the same for each field
		$rest_field_args = array(
			'get_callback'		=> array( $this, 'get_field_value' ),
			'update_callback'	=> null,
			'schema'			=> null,
		);

		// Get the event date
		register_rest_field( 'schedule', 'event_date', $rest_field_args );

		// Get the event display string
		register_rest_field( 'schedule', 'event_date_display', $rest_field_args );

		// Get the event start time
		register_rest_field( 'schedule', 'event_start_time', $rest_field_args );

		// Get the event end time
		register_rest_field( 'schedule', 'event_end_time', $rest_field_args );

		// Get the event time display
		register_rest_field( 'schedule', 'event_time_display', $rest_field_args );

		// Get the event types
		register_rest_field( 'schedule', 'event_types', $rest_field_args );

		// Get the session categories
		register_rest_field( 'schedule', 'session_categories', $rest_field_args );

		// Get the event location
		register_rest_field( 'schedule', 'event_location', $rest_field_args );

		// Get the event speakers
		register_rest_field( 'schedule', 'event_speakers', $rest_field_args );

	}

	/**
	 * Get field values for the REST API.
	 *
	 * @param	array - $object - details of current post
	 * @param	string - $field_name - name of field
	 * @param	WP_REST_Request - $request - current request
	 * @return	mixed
	 */
	public function get_field_value( $object, $field_name, $request ) {

		switch( $field_name ) {

			case 'event_date':
				return get_post_meta( $object[ 'id' ], 'conf_sch_event_date', true );

			case 'event_date_display':
				if ( $event_date = get_post_meta( $object[ 'id' ], 'conf_sch_event_date', true ) ) {
					return date( 'l, F j, Y', strtotime( $event_date ) );
				}
				break;

			case 'event_start_time':
				return get_post_meta( $object[ 'id' ], 'conf_sch_event_start_time', true );

			case 'event_end_time':
				return get_post_meta( $object[ 'id' ], 'conf_sch_event_end_time', true );

			case 'event_time_display':

				// Get start and end time
				$event_start_time = get_post_meta( $object[ 'id' ], 'conf_sch_event_start_time', true );
				$event_end_time = get_post_meta( $object[ 'id' ], 'conf_sch_event_end_time', true );

				// Build display string
				$event_time_display = '';

				// Start with start time
				if ( $event_start_time ) {

					// Convert start time
					$event_start_time = strtotime( $event_start_time );

					// Build the start time
					$event_time_display = date( 'g:i', $event_start_time );

					// If we don't have an end time...
					if ( ! $event_end_time ) {
						$event_time_display = date( ' a', $event_start_time );
					}

					// If we have an end time...
					else {

						// Convert end time
						$event_end_time = strtotime( $event_end_time );

						// Figure out if the meridian is different
						if ( date( 'a', $event_start_time ) != date( 'g:i a', $event_end_time ) ) {
							$event_time_display .= date( ' a', $event_start_time ) . ' - ' . date( 'g:i a', $event_end_time );
						}

					}

				}
				return preg_replace( '/(a|p)m/', '$1.m.', $event_time_display );

			case 'event_types':
				return ( $types = wp_get_object_terms( $object[ 'id' ], 'event_types', array( 'fields' => 'slugs' ) ) ) ? $types : false;

			case 'session_categories':
				return ( $categories = wp_get_object_terms( $object[ 'id' ], 'session_categories', array( 'fields' => 'slugs' ) ) ) ? $categories : false;

			case 'event_location':
				if ( $event_location_id = get_post_meta( $object[ 'id' ], 'conf_sch_event_location', true ) ) {
					if ( $event_post = get_post( $event_location_id ) ) {
						return $event_post;
					}
				}
				return false;

			case 'event_speakers':
				return get_post_meta( $object[ 'id' ], 'conf_sch_event_speakers', true );

		}

		return false;
	}

}

// Let's get this show on the road
new Conference_Schedule_API;