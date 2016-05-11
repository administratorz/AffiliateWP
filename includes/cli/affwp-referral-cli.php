<?php

class AffWP_Referral_CLI extends AffWP_Object_CLI {

	/**
	 * Referral display fields.
	 *
	 * @since 1.9
	 * @access protected
	 * @var array
	 */
	protected $obj_fields = array(
		'ID',
		'affiliate_name',
		'description',
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
		$this->fetcher = new AffWP_Referral_Fetcher();
	}

	/**
	 * Retrieves a referral object or field(s) by ID.
	 *
	 * ## OPTIONS
	 *
	 * <id>
	 * : The referral ID to retrieve.
	 *
	 * [--field=<field>]
	 * : Instead of returning the whole referral object, returns the value of a single field.
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
	 * Adds a referral.
	 *
	 * ## OPTIONS
	 *
	 * <username|ID>
	 * : Affiliate username or ID
	 *
	 * [--amount=<number>]
	 * : Referral amount.
	 *
	 * [--description=<description>]
	 * : Referral description.
	 *
	 * [--reference=<reference>]
	 * : Referral reference (usually product information).
	 *
	 * [--context=<context>]
	 * : Referral context (usually related to the integration, e.g. woocommerce)
	 *
	 * [--status=<status>]
	 * : Referral status. Accepts 'unpaid', 'paid', 'pending', or 'rejected'.
	 *
	 * If not specified, 'pending' will be used.
	 *
	 * ## EXAMPLES
	 *
	 *     # Creates a referral for affiliate edduser1 with an amount of $2 and 'unpaid' status
	 *     wp affwp referral create edduser1 --amount=2 --status=unpaid
	 *
	 *     # Creates a referral for affiliate woouser1 with a context of woocommerce and 'pending' status
	 *     wp affwp referral create woouser1 --context=woocommerce --status=pending
	 *
	 *     # Creates a referral for affiliate ID 142 with description of "For services rendered."
	 *     wp affwp referral create 142 --description='For services rendered.'
	 *
	 * @since 1.9
	 * @access public
	 *
	 * @param array $args       Top-level arguments.
	 * @param array $assoc_args Associated arguments (flags).
	 */
	public function create( $args, $assoc_args ) {
		if ( empty( $args[0] ) ) {
			WP_CLI::error( __( 'A valid affiliate username or ID must be specified as the first argument.', 'affiliate-wp' ) );
		} else {
			$affiliate = affwp_get_affiliate( $args[0] );

			if ( ! $affiliate ) {
				WP_CLI::error( sprintf( __( 'An affiliate with the ID or username "%s" does not exist. See wp affwp affiliate create for adding affiliates.', 'affiliate-wp' ), $args[0] ) );
			}

			// Grab flag values.
			$data['amount']       = WP_CLI\Utils\get_flag_value( $assoc_args, 'amount'      );
			$data['description']  = WP_CLI\Utils\get_flag_value( $assoc_args, 'description' );
			$data['reference']    = WP_CLI\Utils\get_flag_value( $assoc_args, 'reference'   );
			$data['context']      = WP_CLI\Utils\get_flag_value( $assoc_args, 'context'     );
			$data['status']       = WP_CLI\Utils\get_flag_value( $assoc_args, 'status'      );
			$data['affiliate_id'] = $affiliate->affiliate_id;
			$data['user_id']      = $affiliate->user_id;

		}

		if ( ! in_array( $status, array( 'unpaid', 'paid', 'pending', 'rejected' ) ) ) {
			$status = 'pending';
		}

		$referral = affwp_add_referral( $data );

		if ( $referral ) {
			WP_CLI::success( sprintf( __( 'A referral with the ID "%d" has been created.', 'affiliate-wp' ), $referral->referral_id ) );
		} else {
			WP_CLI::error( __( 'The referral could not be added.', 'affiliate-wp' ) );
		}
	}

	/**
	 * Updates a referral.
	 *
	 * ## OPTIONS
	 *
	 * <referral_id>
	 * : Referral ID.
	 *
	 * [--affiliate_id=<id>]
	 * : Affiliate ID.
	 *
	 * [--amount=<number>]
	 * : Referral amount.
	 *
	 * [--description=<description>]
	 * : Referral description.
	 *
	 * [--reference=<reference>]
	 * : Referral reference (usually product information).
	 *
	 * [--context=<context>]
	 * : Referral context (usually related to the integration, e.g. woocommerce)
	 *
	 * [--status=<status>]
	 * : Referral status. Accepts 'unpaid', 'paid', 'pending', or 'rejected'.
	 *
	 * ## EXAMPLES
	 *
	 * @since 1.9
	 * @access public
	 *
	 * @param array $args       Top-level arguments.
	 * @param array $assoc_args Associated arguments (flags).
	 */
	public function update( $args, $assoc_args ) {
		if ( empty( $args[0] ) || ! is_numeric( $args[0] ) ) {
			WP_CLI::error( __( 'A valid referral ID is required to proceed.', 'affiliate-wp' ) );
		} else {
			$referral = affwp_get_referral( $args[0] );
		}

		if ( ! $referral ) {
			WP_CLI::error( __( 'A valid referral ID is required to proceed.', 'affiliate-wp' ) );
		}

		$data['affiliate_id'] = WP_CLI\Utils\get_flag_value( $assoc_args, 'affiliate_id', $referral->affiliate_id );
		$data['amount']       = WP_CLI\Utils\get_flag_value( $assoc_args, 'amount',       $referral->amount       );
		$data['description']  = WP_CLI\Utils\get_flag_value( $assoc_args, 'description',  $referral->description  );
		$data['reference']    = WP_CLI\Utils\get_flag_value( $assoc_args, 'reference',    $referral->reference    );
		$data['context']      = WP_CLI\Utils\get_flag_value( $assoc_args, 'context',      $referral->context      );
		$data['status']       = WP_CLI\Utils\get_flag_value( $assoc_args, 'status',       $referral->status       );

		$update = affiliate_wp()->referrals->update( $referral->referral_id, $data );

		if ( $update ) {
			WP_CLI::success( __( 'The referral was updated successfully.', 'affiliate-wp' ) );
		} else {
			WP_CLI::error( __( 'The referral could not be updated', 'affiliate-wp' ) );
		}

	}

	/**
	 * Deletes a referral.
	 *
	 * @since 1.9
	 * @access public
	 *
	 * @param array $args       Top-level arguments.
	 * @param array $assoc_args Associated arguments (flags).
	 */
	public function delete( $args, $assoc_args ) {

	}

	/**
	 * Displays a list of referrals.
	 *
	 * ## OPTIONS
	 *
	 * [--<field>=<value>]
	 * : One or more args to pass to get_referrals().
	 *
	 * [--field=<field>]
	 * : Prints the value of a single field for each referral.
	 *
	 * [--fields=<fields>]
	 * : Limit the output to specific referral fields.
	 *
	 * [--format=<format>]
	 * : Accepted values: table, csv, json, count, ids, yaml. Default: table
	 *
	 * ## AVAILABLE FIELDS
	 *
	 * These fields will be displayed by default for each referral:
	 *
	 * * ID (alias for referral_id)
	 * * affiliate_name
	 * * description
	 * * status
	 * * date
	 *
	 * These fields are optionally available:
	 *
	 * * reference
	 * * amount
	 * * currency
	 * * custom
	 * * campaign
	 * * visit_id
	 * * affiliate_id
	 *
	 * ## EXAMPLES
	 *
	 * affwp referral list --field=affiliate_name
	 *
	 * affwp referral list --rate_type=percentage --fields=affiliate_id,rate,earnings
	 *
	 * affwp referral list --field=earnings --format=json
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
			'order'   => 'ASC',
		);

		$args = array_merge( $defaults, $assoc_args );

		if ( 'count' == $formatter->format ) {
			$affiliates = affiliate_wp()->referrals->get_referrals( $args, $count = true );

			WP_CLI::line( sprintf( __( 'Number of referrals: %d', 'affiliate-wp' ), $affiliates ) );
		} else {
			$referrals = affiliate_wp()->referrals->get_referrals( $args );
			$referrals = $this->process_extra_fields( $fields, $referrals );

			$formatter->display_items( $referrals );
		}
	}

	/**
	 * Handler for the 'ID' (referral_id alias) field.
	 *
	 * @since 1.9
	 * @access protected
	 *
	 * @param AffWP_Affiliate &$item Affiliate object (passed by reference).
	 */
	protected function ID_field( &$item ) {
		$item->ID = $item->referral_id;
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

	/**
	 * Handler for the 'affiliate_name' field.
	 *
	 * @since 1.9
	 * @access protected
	 *
	 * @param AffWP_Affiliate &$item Affiliate object (passed by reference).
	 */
	protected function affiliate_name_field( &$item ) {
		$item->affiliate_name = affwp_get_affiliate_name( $item->affiliate_id );
	}

}
WP_CLI::add_command( 'affwp referral', 'AffWP_Referral_CLI' );
