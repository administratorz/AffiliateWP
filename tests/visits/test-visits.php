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
}