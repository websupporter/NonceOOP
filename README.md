NonceOOP
========

Use WordPress Nonces in an objectoriented environment.

This class enables you, to use the WordPress Nonce system in an objectoriented environment.

## Basic usage
After you have required the nonceoop.php you can now access the class `NonceOOP`.

In this example, we initialize the class with the `$action` "action" and the `$request_name` "request-name". Just for the sake of demonstration, we hook into the `admin_head` action and display there
1. A link with the nonce by using `$this->get_url( $url )`, which is returning `$url` with the parameter `request-name` containing the nonce.
2. A form, where we have created an hidden field containing the nonce. The name of the field is `request-name`.

In this current setting NonceOOP would automatically check, if `$_REQUEST['request-name']` is populated and if so, it would check, if it is populated with the correct nonce.

If it wouldn't be populated with the correct nonce, `wp_die()` would be executed and the error message "You are not allowed to do this." would be displayed.

```
<?php
/**
 * Plugin Name: Test NonceOOP
 **/

use NonceOOP\NonceOOP;
require_once __DIR__ . '/vendor/autoload.php';


class Test extends NonceOOP {
	function __construct() {
    	$action       = 'action';
        $request_name = 'request-name';
		parent::__construct( $action, $request_name );
		add_action( 'admin_head', array( $this, 'write' ) );
	}

	function write(){
		echo '<a href="' . esc_url( $this->get_url( home_url() ) ) . '">test URL with nonce</a>';
		echo '<form method="post"><button>send</button>';
		echo $this->get_field();
		echo '</form>';
		die();
	}
}
new Test();
```

## Advanced usage
You have some options to configure the behavior.

### Disable the automatic check
If you do not want NonceOOP to automatically check the `$_REQUEST` you can disable this behavior:
```
$nonce = new NonceOOP( 'action', 'request', false );
```

### Define your own error message
If you want to alter the automatic error message NonceOOP is using:
```
$nonce = new NonceOOP( 'action', 'request', true, 'Another error message is displayed.' );
```
Now, the error message "Another error message is displayed." would be displayed in the `wp_die()`.

### Define your own callback
You might not want to use `wp_die()`, but rely on the automatic detection. Instead of an error message, you can define a callback function.

*Examples*
```
$nonce = new NonceOOP( 'action', 'request', true, function( $t ) { error_log() } );
```

```
$nonce = new NonceOOP( 'action', 'request', true, array( $this, 'callback_function' ) );
```

```
function callback() {
	echo 'Nono';
    exit;
}
$nonce = new NonceOOP( 'action', 'request', true, 'callback' );
```

## Functions

### `get_nonce()`
Returns a valid nonce for the given action. This can be used in ajax requests.

### `verify_nonce( $nonce )`
Verifies if the given nonce is valid for the current action.

### `get_field( $referer, $echo )`
Creates an `<input type="hidden">` with a valid nonce for the given action. The name of the field corresponds with the given name. `$referer` is a boolean. Set to `true` the referer input field will be printed as well (Default: `false`). `$echo` defines, if the field will be immediatly echoed (`true`) or just returned (`false`, default).

### `get_url( $url )`
Extends the given URL with a valid nonce. The name of the parameter corresponds with the given name.

### `set_lifetime( $new_lifetime )`
Nonces are usually valid for 24 hours. You can define a new lifetime in seconds with `set_lifetime()`. All previously generated nonces will be invalid, so you should call this function quite early. Example usage:

```
<?php
/**
 * Plugin Name: Test NonceOOP
 **/

use NonceOOP\NonceOOP;
require_once __DIR__ . '/vendor/autoload.php';


class Test extends NonceOOP {
	function __construct() {
		parent::__construct( 'action', 'referer', true, array( $this, 'callback' ) );
		$this->set_lifetime( 12 );
		add_action( 'admin_head', array( $this, 'write' ) );
	}

	function write(){
		echo '<a href="' . esc_url( $this->get_url( home_url() ) ) . '">test URL with nonce</a>';
		echo '<form method="post"><button>send</button>';
		echo $this->get_field();
		echo '</form>';
		die();
	}

	function callback( $t ) {
		die( 'nono' );
	}
}
new Test();
```

### `get_lifetime()`
Returns the current lifetime in seconds.

### `get_action()`
Get the current action.

### `set_action( $new_action )`
Change the current action. All previously generated nonces will be invalid, so you should call this function quite early. See the `set_lifetime()` example above.

### `check_request()`
Is used for the automatic check on the `$_REQUEST` and is hooked into the `init` action, if automatic check is enabled. Since the functions `check_ajax_referer()` and `check_admin_referer()` are not used, `check_request()` calls at least the actions `check_ajax_referer` (in case of `DOING_AJAX`) or `check_admin_referer` (in case of `is_admin()`), so functions hooked into these actions are still executed.

The usual behavior on a failed validation is to execute `wp_die()` or the callback function. In case of `DOING_AJAX` `wp_die()` will just return `-1` like `check_ajax_referer()` is doing.

### `nonce_life( $Lifetime )`
Is hooked into the filter `nonce_life` in case `set_lifetime()` was called.

### `get_nonce_age( $nonce )`
Returns `false` if the nonce is invalid. 1 indicates a yound nonce (0-12 hrs), 2 indicates on old nonce (12-24 hrs).

## The callback function
```
function callback( $object ) {
	switch ( $object->get_action() ) {
    	case "create-1":
        	wp_die( __( "You can't create!" ) );
            break;
        case "modify":
        	wp_die( __( "You can't modify!" ) );
            break;
        default:
        	wp_die( __( "No no." ) );
    }
}
```

The whole NonceOOP object will be handed over to the callback. This can be useful to switch for example the behavior of the callback depending on the action.


## How to install using composer

The composer.json file:
```
{
	"repositories": [
		{
			"type": "vcs",
			"url" : "https://github.com/websupporter/NonceOOP"
		}
	],
	"require": {
		"websupporter/nonceoop" : "1.0.*"
	}
}
```

Execute `composer install`

## The tests
To run the tests:

1. Switch into the NonceOOP directory (usually `vendors/websupporter/nonceoop/`), 
2. run `composer install`,
3. create a `phpunit.xml` (see the `phpunit.xml.dist` as example) and enter the database credentials to your database.
3. run `phpunit`

The tests rely on https://github.com/inpsyde/WP-Tests-Starter and https://github.com/inpsyde/wordpress-dev

