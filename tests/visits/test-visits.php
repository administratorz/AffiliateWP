<?php

class Visit_Tests extends WP_UnitTestCase {

	function test_long_campaign() {

		// The 2 should get trimmed off as it is the 51st character
		$campaign = '111111111111111111111111111111111111111111111111112';
		$visit_id = affiliate_wp()->visits->add( array( 'campaign' => $campaign, 'affiliate_id' => 1 ) );
		$visit    = affiliate_wp()->visits->get_object( $visit_id );

		$this->assertEquals( 50, strlen( $visit->campaign ) );
		$this->assertEquals( '11111111111111111111111111111111111111111111111111', $visit->campaign );

	}

	function test_sanitize_visit_url() {
		$referral_var = affiliate_wp()->tracking->get_referral_var();

		$this->assertEquals( affwp_sanitize_visit_url( 'https://affiliatewp.com/' . $referral_var . '/pippin/query_var' ), 'https://affiliatewp.com/query_var' );
		$this->assertEquals( affwp_sanitize_visit_url( 'https://affiliatewp.com/sample-page/' . $referral_var . '/pippin/query_var/1' ), 'https://affiliatewp.com/sample-page/query_var/1' );
		$this->assertEquals( affwp_sanitize_visit_url( 'https://affiliatewp.com/sample-page/' . $referral_var . '/pippin/query_var/1/query_var2/2' ), 'https://affiliatewp.com/sample-page/query_var/1/query_var2/2' );
		$this->assertEquals( affwp_sanitize_visit_url( 'https://affiliatewp.com/' . $referral_var . '/pippin?query_var=1' ), 'https://affiliatewp.com?query_var=1' );
		$this->assertEquals( affwp_sanitize_visit_url( 'https://affiliatewp.com/sample-page/' . $referral_var . '/pippin?query_var=1' ), 'https://affiliatewp.com/sample-page?query_var=1' );
		$this->assertEquals( affwp_sanitize_visit_url( 'https://affiliatewp.com/sample-page/' . $referral_var . '/pippin?query_var=1&query_var2=2' ), 'https://affiliatewp.com/sample-page?query_var=1&query_var2=2' );
		$this->assertEquals( affwp_sanitize_visit_url( 'https://www.affiliatewp.com/' . $referral_var . '/pippin/query_var' ), 'https://www.affiliatewp.com/query_var' );
		$this->assertEquals( affwp_sanitize_visit_url( 'https://www.affiliatewp.com/sample-page/' . $referral_var . '/pippin/query_var/1' ), 'https://www.affiliatewp.com/sample-page/query_var/1' );
		$this->assertEquals( affwp_sanitize_visit_url( 'https://www.affiliatewp.com/sample-page/' . $referral_var . '/pippin/query_var/1/query_var2/2' ), 'https://www.affiliatewp.com/sample-page/query_var/1/query_var2/2' );
		$this->assertEquals( affwp_sanitize_visit_url( 'https://www.affiliatewp.com/' . $referral_var . '/pippin?query_var=1' ), 'https://www.affiliatewp.com?query_var=1' );
		$this->assertEquals( affwp_sanitize_visit_url( 'https://www.affiliatewp.com/sample-page/' . $referral_var . '/pippin?query_var=1' ), 'https://www.affiliatewp.com/sample-page?query_var=1' );
		$this->assertEquals( affwp_sanitize_visit_url( 'https://www.affiliatewp.com/sample-page/' . $referral_var . '/pippin?query_var=1&query_var2=2' ), 'https://www.affiliatewp.com/sample-page?query_var=1&query_var2=2' );
	}

	/**
	 * @covers affwp_delete_visit()
	 */
	public function test_delete_visit_with_invalid_visit_id_should_return_false() {
		$this->assertFalse( affwp_delete_visit( 0 ) );
	}

	/**
	 * @covers affwp_delete_visit()
	 */
	public function test_delete_visit_with_valid_visit_id_should_return_true() {
		$visit_id = affiliate_wp()->visits->add( array(
			'affiliate_id' => 1
		) );

		$this->assertTrue( affwp_delete_visit( $visit_id ) );
	}

	/**
	 * @covers affwp_delete_visit()
	 */
	public function test_delete_visit_with_invalid_visit_object_should_return_false() {
		$this->assertFalse( affwp_delete_visit( new stdClass() ) );
	}

	/**
	 * @covers affwp_delete_visit()
	 */
	public function test_delete_visit_with_valid_visit_object_should_return_true() {
		$visit_id = affiliate_wp()->visits->add( array(
			'affiliate_id' => 1
		) );

		$visit = affiliate_wp()->visits->get( $visit_id );

		$this->assertTrue( affwp_delete_visit( $visit ) );
	}

	/**
	 * @covers affwp_delete_visit()
	 */
	public function test_delete_visit_with_non_object_non_numeric_should_return_false() {
		$this->assertFalse( affwp_delete_visit( 'foo' ) );
	}

	/**
	 * @covers affwp_delete_visit()
	 */
	public function test_delete_visit_should_decrease_affiliate_visits_count() {
		$affiliate_id = affiliate_wp()->affiliates->add( array(
			'user_id' => $this->factory->user->create()
		) );

		$visit_ids = array();

		for ( $i = 0; $i <= 2; $i++ ) {
			$visit_ids[] = affiliate_wp()->visits->add( array(
				'affiliate_id' => $affiliate_id
			) );
		}

		$this->assertEquals( 3, affwp_get_affiliate_visit_count( $affiliate_id ) );

		// 3 becomes 2.
		affwp_delete_visit( $visit_ids[0] );

		$this->assertEquals( 2, affwp_get_affiliate_visit_count( $affiliate_id ) );

		// 2 becomes 1.
		affwp_delete_visit( $visit_ids[1] );

		$this->assertEquals( 1, affwp_get_affiliate_visit_count( $affiliate_id ) );
	}
}