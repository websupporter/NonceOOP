<?php

use NonceOOP\NonceOOP;

class NonceOOP_Tests extends WP_UnitTestCase {

	var $action = 'a-nonce-action';
	var $name   = 'a-nonce-request-name';

	/**
	 * Check if get_nonce() returns the expected nonce
	 **/
	public function test_get_nonce() {
		$nonce = new NonceOOP( $this->action, $this->name );
		$get_nonce = $nonce->get_nonce();
    	
		$expected_nonce = wp_create_nonce( $this->action );

		$this->assertEquals( $get_nonce, $expected_nonce );
	}

	/**
	 * Check get_nonce_age()
	 **/
	public function test_get_nonce_age() {
		$nonce = new NonceOOP( $this->action, $this->name );
		$get_nonce = $nonce->get_nonce();

		$valid = $nonce->get_nonce_age( $get_nonce );
		$this->assertEquals( $valid, 1 );
	}

	/**
	 * Check if verify_nonce() validates correctly
	 **/
	public function test_verify_nonce() {
		$nonce = new NonceOOP( $this->action, $this->name );
		$get_nonce = $nonce->get_nonce();

		$valid = $nonce->verify_nonce( $get_nonce );
		$this->assertTrue( $valid );

		$not_valid = $nonce->verify_nonce( $get_nonce . '-failure' );
		$this->assertFalse( $not_valid );
	}

	/**
	 * Check if lifetime can be changed
	 **/
	public function test_set_lifetime() {
		$nonce = new NonceOOP( $this->action, $this->name );
		$new_lifetime = $nonce->set_lifetime( 1 );
		$this->assertEquals( $new_lifetime, 1 );
		$get_nonce = $nonce->get_nonce();
		sleep( 2 );

		$not_valid = $nonce->verify_nonce( $get_nonce );
		$this->assertFalse( $not_valid );
	}

	/**
	 * Check correct URL with get_url()
	 **/
	function test_get_url() {
		$url = home_url();
		$nonce = new NonceOOP( $this->action, $this->name );

		$generated_url = $nonce->get_url( $url );
		$expected_url = home_url() . '?a-nonce-request-name=' . $nonce->get_nonce();

		$this->assertEquals( $generated_url, $expected_url );
	}

	/**
	 * Check if check_request() works
	 **/
	function test_check_request() {
		$nonce = new NonceOOP( $this->action, $this->name, false, function() {} );
		$get_nonce = $nonce->get_nonce();
		
		//No nonce send, did work.
		$worked = $nonce->check_request();
		$this->assertTrue( $worked );

		$_REQUEST[ $this->name ] = $get_nonce;

		//Correct nonce send, did work.
		$worked = $nonce->check_request();
		$this->assertTrue( $worked );

		$_REQUEST[ $this->name ] = $get_nonce . '-failed';

		//Wrong nonce send, failed.
		$failed = $nonce->check_request();
		$this->assertFalse( $failed );

	}

	/**
	 * Check get_field() returns the expected HTML string
	 **/
	function test_get_field() {
		$expected_html = wp_nonce_field( $this->action, $this->name, false, false );
		$nonce = new NonceOOP( $this->action, $this->name );
		$html = $nonce->get_field();

		$this->assertEquals( $html, $expected_html );
	}
}