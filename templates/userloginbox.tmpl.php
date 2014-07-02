<?php
/**
 * @file
 * @author Scott Cushman
 */
if ( !defined( 'MEDIAWIKI' ) ) {
	die();
}

/**
 * @ingroup Templates
 */
class UserLoginBoxTemplate extends QuickTemplate {
	public function execute() {
		global $wgCookieExpiration;

		$expirationDays = ceil( $wgCookieExpiration / ( 3600 * 24 ) );
		$passwordReminderSpecialPageName = ( class_exists( 'LoginReminder' ) ? 'LoginReminder' : 'ChangePassword' );
?>
<form name="userlogin" class="userlogin" method="post" action="<?php echo $this->data['action_url'] ?>">
	<?php if ( !empty( $this->data['social_buttons'] ) ) { ?>
	<h3><?php echo wfMessage( 'log_in_via' )->plain() ?></h3>
	<?php echo $this->data['social_buttons'] ?>
	<?php } ?>

	<div class="userlogin_inputs">
		<h3><?php echo wfMessage( 'login' )->plain() ?></h3>
		<input type="text" class="loginText input_med" name="wpName" id="wpName1<?php echo $this->data['suffix'] ?>" value="" size="20" />
		<input type="hidden" id="wpName1_showhide<?php echo $this->data['suffix'] ?>" /><br />

		<input type="password" class="loginPassword input_med" name="wpPassword" id="wpPassword1<?php echo $this->data['suffix'] ?>" value="" size="20" />
		<input type="hidden" id="wpPassword1_showhide<?php echo $this->data['suffix'] ?>" />
	</div>

	<input type="submit" class="button primary login_button" name="wpLoginattempt" id="wpLoginattempt" value="<?php echo wfMessage( 'login' )->plain() ?>" />

	<div class="userlogin_remember">
		<input type="checkbox" name="wpRemember" value="1" id="wpRemember<?php echo $this->data['suffix'] ?>" checked="checked" />
		<label for="wpRemember<?php echo $this->data['suffix'] ?>"><?php echo wfMessage( 'userlogin-remembermypassword' )->numParams( $expirationDays )->text() ?></label>
	</div>

	<div class="userlogin_links">
		<a href="<?php echo SpecialPage::getTitleFor( $passwordReminderSpecialPageName )->getFullURL() ?>" id="forgot_pwd<?php echo $this->data['suffix'] ?>"><?php echo wfMessage( 'userlogin-resetpassword-link' )->plain() ?></a>
		<a href="<?php echo SpecialPage::getTitleFor( 'Userlogin', 'signup' )->getFullURL() ?>"><?php echo wfMessage( 'nologinlink' )->plain() ?></a>
	</div>
</form>
<?php
	}
}