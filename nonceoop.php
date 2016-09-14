<?php
	/**
	 * NonceOOP
	 * This library helps you to use WordPress Nonces in an objectoriented environment
	 **/

	class NonceOOP {

		private $action   = '';
		private $lifetime = DAY_IN_SECONDS;

		function __construct() {

		}

		/**
		 * Get the current nonce.
		 * Can be used in the `init` hook or later.
		 *
		 * @return (string) $nonce
		 **/
		function get_nonce() {
			$nonce = wp_create_nonce( $this->action );
			return $nonce;
		}

		/**
		 * Verifies a nonce
		 *
		 * @return (boolean) $is_valid Whether the nonce is valid
		 **/
		function verify_nonce( $nonce ) {
			$is_valid = wp_verify_nonce( $nonce, $this->action );
			return $is_valid;
		}

		/**
		 * Set the Nonce action
		 *
		 * @param (string) $new_action The new action
		 *
		 * @return (string) The action
		 **/
		function set_action( $new_action ) {
			$this->action = $new_action;
			return $this->get_action();
		}

		/**
		 * Set the Nonce action
		 *
		 * @return (string) returns the current action
		 **/
		function get_action() {
			return $this->action;
		}

		/**
		 * Set the Lifetime.
		 * Nonces you have created before will be invalid.
		 *
		 * @param (integer) $new_lifetime The new lifetime in seconds
		 *
		 * @return (integer) The lifetime
		 **/
		function set_lifetime( $new_lifetime ) {
			$this->lifetime = $new_lifetime;
			add_filter( 'nonce_life', array( $this, 'nonce_life' ) );
			return $this->get_lifetime();
		}

		/**
		 * Set the Lifetime of a nonce
		 *
		 * @return (integer) returns the seconds a nonce is valid
		 **/
		function get_lifetime() {
			return $this->lifetime;
		}

		/**
		 * If hooked into `nonce_life` via set_lifetime, it changes the lifetime of a nonce.
		 * Nonces you have created before will be invalid.
		 *
		 * @param (integer) $lifetime The old lifetime.
		 *
		 * @return (integer) $this->lifetime The new lifetime.
		 **/
		function nonce_life( $lifetime ) {
			return $this->lifetime;
		}

	}