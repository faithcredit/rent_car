<?php
	$backup_codes_action = add_query_arg(
		array(
			'action' => 'load_sgs2fabc',
		),
		wp_login_url()
	);
?>

<style>#login { width:360px; padding:5% 0 0; }</style>
<?php if ( ! $args['is_wp_login'] ) : ?>
<style>.login-action- > div#login:first-of-type{ display: none; } .login-action- > div#login:nth-last-of-type(2){ display: block !important; } </style>
<?php endif ?>

<?php if ( ! empty( $args['error'] ) ) : ?>
	<div id="login_error"><strong><?php echo $args['error']; ?></strong><br /></div>
<?php endif ?>

<form name="sgs2fa_form" id="loginform" action="<?php echo $args['action']; ?>" method="post">
	<h1><?php esc_html_e( '2-factor Authentication', 'sg-security' ); ?></h1>
	<br />
	<p class="sg-2fa-title"><?php esc_html_e( 'In order to log in, please enter the verification code from your Authenticator app:', 'sg-security' ); ?></p>
		<br />
		<label for="sgc2facode"><?php esc_html_e( 'Authentication Code:', 'sg-security' ); ?></label>
		<input name="sgc2facode" id="sgc2facode" class="input" value="" size="20" pattern="[0-9]*" autofocus />
	</p>

	<?php if ( $args['interim_login'] ) : ?>
		<input type="hidden" name="interim-login" value="1" />
	<?php else : ?>
		<input type="hidden" name="redirect_to" value="<?php echo $args['redirect_to']; ?>" />
	<?php endif; ?>

	<input type="hidden" name="rememberme" id="rememberme" value="<?php echo $args['rememberme']; ?>" />
	<input name="do_not_challenge" type="checkbox" id="do_not_challenge" />
	<label for="do_not_challenge"><?php esc_html_e( 'Do not challenge me for the next 30 days.', 'sg-security' ); ?></label>
	<p>
		<br />
		<?php submit_button( __( 'Authenticate', 'sg-security' ) ); ?>
	</p>
</form>

<a href="<?php echo esc_url( $backup_codes_action ); ?>" style="text-align:center; display:block; padding: 10px">
	<?php esc_html_e( 'Login using backup codes', 'sg-security' ); ?>
</a>
