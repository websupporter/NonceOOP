<?php
	/**
	 * NonceOOP
	 * This library helps you to use WordPress Nonces in an objectoriented environment.
	 **/

	class NonceOOP {

		private $action    = '';
		private $name      = 'oop_nonce';
		private $lifetime  = DAY_IN_SECONDS;
		private $autocheck = true;
		private $callback  = 'You are not allowed to do this.';

		/**
		 * Initialize NonceOOP
		 *
		 * @param (string)  $new_action The action.
		 * @param (string)  $new_name   The name for the $_REQUEST.
		 * @param (boolean) $autocheck  Whether to automatically check $_REQUEST if a nonce is currently send.
		 * @param (string)  $callback   If this string is callable, the function will be executed if the validation fails.
		 *                              Otherwise, the string is used as the error message inside of `wp_die()`.
		 *
		 * @return (boolean) `true`
		 **/
		function __construct( $new_action = '', $new_name = 'oop_nonce', $new_autocheck = true, $new_callback = 'You are not allowed to do this.' ) {
			if ( empty( $new_action ) ) {
				_doing_it_wrong( 'NonceOOP', 'You should give a unique action.', '1.0.0' );
			}
			$this->action    = $new_action;
			$this->name      = $new_name;
			$this->autocheck = $new_autocheck;
			$this->callback  = $new_callback;

			// If we handle the validation automatically and a nonce request is found, we start validation
			if ( $this->autocheck && ! empty ( $_REQUEST[ $this->name ] ) ) {
				add_action( 'init', array( $this, 'check_request' ) );
			}

			return true;
		}

		/**
		 * Checks the current $_REQUEST if a nonce exists and if its valid. If is is not valid, `wp_die()` will be executed or a defined callback function.
		 *
		 * @return (boolean) `false` indicates the nonce was not valid. `true` indicates, the nonce was valid or no nonce was found in the $_REQUEST.
		 **/
		function check_request() {
			// Check if the $REQUEST contains a nonce
			if ( ! isset( $_REQUEST[ $this->name ] ) ) {
				return true;
			}

			// Check the nonce
			$is_valid = $this->verify_nonce( $_REQUEST[ $this->name ] );

			// Since `check_ajax_referer()` and `check_admin_referer()` rely on `wp_verify_nonce()`
			// no additional layer of security is given. But we could run into `wp_die()` although a callback
			// function has been defined. So instead of using these functions, we just call the internally
			// used actions.
			if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
				// In case we have an ajax request, we reproduce a bit of the
				// check_ajax_referer()

				/* This action is documented in wp-includes/pluggable.php */
				do_action( 'check_ajax_referer', $this->action, $is_valid );
			} elseif ( is_admin() ) {
				// In case we are in the admin, we reproduce a bit of the check_admin_referer()

				/* This action is documented in wp-includes/pluggable.php */
				do_action( 'check_admin_referer', $this->action, $is_valid );
			}

			if ( $is_valid ) {
				return true;
			}

			if ( !is_callable( $this->callback ) ) {
				// Check for ajax
				if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
					// In case we have an ajax request, we reproduce a bit of the
					// check_ajax_referer()

					wp_die( -1 );
				}

				// If $callback contains the error message, we exit with `wp_die()`
				wp_die(	$this->callback );
			} else {

				// If $callback contains a callable function, we exeute the function.
				// The current object will be given as parameter.
				call_user_func_array( $this->callback, array( $this ) );
			}
			
			return false;
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

			// `wp_verify_nonce()` returns 1, 2 or `false`. 1 is indicating a "young" nonce, 
			// 2 is indicating an "old" nonce and false an invalid. 
			// We just want to know, if the nonce is valid.
			if( false !== $is_valid )
				return true;

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

		/**
		 * Creates an hidden input field with the nonce.
		 *
		 * @param (boolean) $referer Wheter the referer should be placed. Default: `false`
		 * @param (boolean) $echo    Wheter the input field should be returned (`false`) or echoed (`true`). Default: `false`
		 *
		 * @return (string|boolean) Returns the HTML string or true in case the string gets echoed.
		 **/
		function get_field( $referer = false, $echo = false ) {
			$html = wp_nonce_field( $this->action, $this->name, $referer, false );

			if ( ! $echo ) {
				return $html;
			}

			echo $html;

			return true;
		}

		/**
		 * Adds the nonce to a given URL
		 *
		 * @param (string) $url The URL where the nonce should be added to.
		 *
		 * @return (string) $url The URL with the nonce parameter
		 **/
		function get_url( $url ) {
			$url = wp_nonce_url( $url, $this->action, $this->name );
			return $url;
		}

	}