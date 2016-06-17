<?php

class AffWP_Creative_CLI extends AffWP_Object_CLI {

	/**
	 * Referral display fields.
	 *
	 * @since 1.9
	 * @access protected
	 * @var array
	 */
	protected $obj_fields = array(
		'ID',
		'name',
		'url',
		'status',
		'date'
	);

	/**
	 * Sets up the fetcher for sanity-checking.
	 *
	 * @since 1.9
	 * @access public
	 */
	public function __construct() {
		$this->fetcher = new AffWP_Creative_Fetcher();
	}

	/**
	 * Retrieves a creative object or field(s) by ID.
	 *
	 * ## OPTIONS
	 *
	 * <id>
	 * : The creative ID to retrieve.
	 *
	 * [--field=<field>]
	 * : Instead of returning the whole creative object, returns the value of a single field.
	 *
	 * [--fields=<fields>]
	 * : Limit the output to specific fields. Defaults to all fields.
	 *
	 * [--format=<format>]
	 * : Accepted values: table, json, csv, yaml. Default: table
	 *
	 * ## EXAMPLES
	 *
	 *     # save the referral field value to a file
	 *     wp post get 12 --field=earnings > earnings.txt
	 */
	public function get( $args, $assoc_args ) {
		parent::get( $args, $assoc_args );
	}

	/**
	 * Adds a creative.
	 *
	 * ## OPTIONS
	 *
	 * [--name=<name>]
	 * : Required. Name identifier for the creative.
	 *
	 * [--description=<description>]
	 * : Description for the creative.
	 *
	 * [--link=<URL>]
	 * : URL the creative should link to.
	 *
	 * [--text=<text>]
	 * : Text for the creative.
	 *
	 * [--image=<URL>]
	 * : Image URL (local or external) to use for the creative.
	 *
	 * [--status=<status>]
	 * : Status for the creative. Accepts 'active' or 'inactive'. Default 'active'.
	 *
	 * ## EXAMPLES
	 *
	 *     # Creates a creative linking to http://affiliatewp.com
	 *     wp affwp creative create --name=AffiliateWP --link=http://affiliatewp.com
	 *
	 *     # Creates a creative using a locally-hosted image.
	 *     wp affwp creative create --name='Special Case' --image=https://example.org/my-image.jpg
	 *
	 *     # Create a creative with a status of 'inactive'
	 *     wp affwp creative create --name='My Creative' --status=inactive
	 *
	 * @since 1.9
	 * @access public
	 *
	 * @internal The --link flag maps to 'url' because --url is a global WP CLI flag.
	 *
	 * @param array $_          Top-level arguments (unused).
	 * @param array $assoc_args Associated arguments (flags).
	 */
	public function create( $_, $assoc_args ) {
		$name = WP_CLI\Utils\get_flag_value(  $assoc_args, 'name', '' );

		if ( empty( $name ) ) {
			WP_CLI::error( __( 'A --name value must be specified to add a new creative.', 'affiliate-wp' ) );
		}

		$data['name']        = $name;
		$data['description'] = WP_CLI\Utils\get_flag_value(  $assoc_args, 'description', ''       );
		$data['url']         = WP_CLI\Utils\get_flag_value(  $assoc_args, 'link',        ''       );
		$data['text']        = WP_CLI\Utils\get_flag_value(  $assoc_args, 'text',        ''       );
		$data['image']       = WP_CLI\Utils\get_flag_value(  $assoc_args, 'image',       ''       );
		$data['status']      = WP_CLI\Utils\get_flag_value(  $assoc_args, 'status',      'active' );

		$created = affwp_add_creative( $data );

		if ( $created ) {
			$creative = affiliate_wp()->creatives->get_by( 'name', $data['name'] );
			WP_CLI::success( sprintf( __( 'A creative with the ID %d has been successfully created.', 'affiliate-wp' ), $creative->creative_id ) );
		} else {
			WP_CLI::error( __( 'The creative could not be created.', 'affiliate-wp' ) );
		}
	}

	/**
	 * Updates a creative.
	 *
	 * ## OPTIONS
	 *
	 * [--name=<name>]
	 * : Name identifier for the creative.
	 *
	 * [--description=<description>]
	 * : Description for the creative.
	 *
	 * [--link=<URL>]
	 * : URL the creative should link to.
	 *
	 * [--text=<text>]
	 * : Text for the creative.
	 *
	 * [--image=<URL>]
	 * : Image URL (local or external) to use for the creative.
	 *
	 * [--status=<status>]
	 * : Status for the creative. Accepts 'active' or 'inactive'. Default 'active'.
	 *
	 * ## EXAMPLES
	 *
	 *     # Updates creative ID 300 with a new name 'New Name'
	 *     wp affwp creative update 300 --name='New Name'
	 *
	 *     # Updates creative ID 53 with a new image.
	 *     wp affwp creative update 53 --image=https://example.org/my-other-image.jpg
	 *
	 *     # Updates creative ID 199's status to inactive
	 *     wp affwp creative update 199 --status=inactive
	 *
	 * @since 1.9
	 * @access public
	 *
	 * @param array $args       Top-level arguments.
	 * @param array $assoc_args Associated arguments (flags).
	 */
	public function update( $args, $assoc_args ) {
		if ( empty( $args[0] ) ) {
			WP_CLI::error( __( 'A valid creative ID is required to proceed.', 'affiliate-wp' ) );
		}

		if ( ! $creative = affwp_get_creative( $args[0] ) ) {
			WP_CLI::error( __( 'A valid creative ID is required to proceed.', 'affiliate-wp' ) );
		}

		$data['name']        = WP_CLI\Utils\get_flag_value( $assoc_args, 'name', $creative->name        );
		$data['description'] = WP_CLI\Utils\get_flag_value( $assoc_args, 'name', $creative->description );
		$data['url']         = WP_CLI\Utils\get_flag_value( $assoc_args, 'name', $creative->link        );
		$data['text']        = WP_CLI\Utils\get_flag_value( $assoc_args, 'name', $creative->text        );
		$data['image']       = WP_CLI\Utils\get_flag_value( $assoc_args, 'name', $creative->image       );
		$data['status']      = WP_CLI\Utils\get_flag_value( $assoc_args, 'name', $creative->status      );
		$data['creative_id'] = $creative->creative_id;

		$updated = affwp_update_creative( $data );

		if ( $updated ) {
			WP_CLI::success( __( 'The creative was successfully updated.', 'affiliate-wp' ) );
		} else {
			WP_CLI::error( __( 'The creative could not be updated.', 'affiliate-wp' ) );
		}
	}

	/**
	 * Deletes a creative.
	 *
	 * @since 1.9
	 * @access public
	 *
	 * @param array $args       Top-level arguments.
	 * @param array $assoc_args Associated arguments (flags).
	 */
	public function delete( $args, $assoc_args ) {
		if ( empty( $args[0] ) ) {
			WP_CLI::error( __( 'A valid creative ID is required to proceed.', 'affiliate-wp' ) );
		}

		if ( ! $creative = affwp_get_creative( $args[0] ) ) {
			WP_CLI::error( __( 'A valid creative ID is required to proceed.', 'affiliate-wp' ) );
		}

		$deleted = affwp_delete_creative( $creative );

		if ( $deleted ) {
			WP_CLI::success( __( 'The creative was successfully deleted.', 'affiliate-wp' ) );
		} else {
			WP_CLI::error( __( 'The creative could not be deleted.', 'affiliate-wp' ) );
		}
	}

	/**
	 * Displays a list of creatives.
	 *
	 * ## OPTIONS
	 *
	 * [--<field>=<value>]
	 * : One or more args to pass to get_creatives().
	 *
	 * [--field=<field>]
	 * : Prints the value of a single field for each creative.
	 *
	 * [--fields=<fields>]
	 * : Limit the output to specific creative fields.
	 *
	 * [--format=<format>]
	 * : Accepted values: table, csv, json, count, ids, yaml. Default: table
	 *
	 * ## AVAILABLE FIELDS
	 *
	 * These fields will be displayed by default for each creative:
	 *
	 * * ID (alias for creative_id)
	 * * name
	 * * url
	 * * status
	 * * date
	 *
	 * These fields are optionally available:
	 *
	 * * description
	 * * text
	 * * image
	 *
	 * ## EXAMPLES
	 *
	 *     wp affwp creative list --field=affiliate_id
	 *
	 *     wp affwp creative list --rate_type=percentage --fields=affiliate_id,rate,earnings
	 *
	 *     wp affwp creative list --field=earnings --format=json
	 *
	 * @subcommand list
	 *
	 * @since 1.9
	 * @access public
	 *
	 * @param array $args       Top-level arguments.
	 * @param array $assoc_args Associated arguments (flags).
	 */
	public function list_( $_, $assoc_args ) {
		$formatter = $this->get_formatter( $assoc_args );

		$fields = $this->get_fields( $assoc_args );

		$defaults = array(
			'order' => 'ASC',
		);

		$args = array_merge( $defaults, $assoc_args );

		if ( 'count' == $formatter->format ) {
			$creatives = affiliate_wp()->creatives->get_creatives( $args, $count = true );

			WP_CLI::line( sprintf( __( 'Number of creatives: %d', 'affiliate-wp' ), $creatives ) );
		} else {
			$creatives = affiliate_wp()->creatives->get_creatives( $args );
			$creatives = $this->process_extra_fields( $fields, $creatives );

			$formatter->display_items( $creatives );
		}
	}

	/**
	 * Handler for the 'ID' (creative_id alias) field.
	 *
	 * @since 1.9
	 * @access protected
	 *
	 * @param AffWP_Affiliate &$item Affiliate object (passed by reference).
	 */
	protected function ID_field( &$item ) {
		$item->ID = $item->creative_id;
	}

	/**
	 * Handler for the 'date' field.
	 *
	 * Reformats the date for display.
	 *
	 * @since 1.9
	 * @access protected
	 *
	 * @param AffWP_Affiliate &$item Affiliate object (passed by reference).
	 */
	protected function date_field( &$item ) {
		$item->date = mysql2date( 'M j, Y', $item->date, false );
	}

}
WP_CLI::add_command( 'affwp creative', 'AffWP_Creative_CLI' );