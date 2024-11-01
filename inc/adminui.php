<style>
	.shopp.adminextras label {
		display: inline-block;
		font-weight: bold;
		width: 12em;
	}
</style>

<div class="wrap shopp adminextras">

	<?php echo $status_message; ?>

	<?php shopp_admin_screen_tabs(); ?>

	<div class="icon32"> </div>
	<h2><?php _e( 'Admin Extras', 'shopp-admin-extras' ); ?></h2>

	<h3><?php _e( 'Product Comments & Pings', 'shopp-admin-extras' ); ?></h3>
	<p><?php _e( 'This will only only enable/disable settings for Shopp Products only, not regular posts or other custom post types.', 'shopp-admin-extras' ); ?></p>

	<table class="widefat">
		<thead>
			<tr>
				<th><?php _e( 'Option', 'shopp-admin-extras' ); ?></th>
				<th><?php _e( 'Enabled', 'shopp-admin-extras' ); ?></th>
				<th><?php _e( 'Disabled', 'shopp-admin-extras' ); ?></th>
			</tr>
		</thead>
		<tbody>
		    <tr>
			    <td><?php _e( 'Product Comments', 'shopp-admin-extras' ); ?></td>
			    <td><?php echo esc_html( $shopp_comments['open'] ); ?></td>
			    <td><?php echo esc_html( $shopp_comments['closed'] ); ?></td>
		    </tr>
		    <tr>
			    <td><?php _e( 'Product Pings', 'shopp-admin-extras' ); ?></td>
			    <td><?php echo esc_html( $shopp_pings['open'] ); ?></td>
			    <td><?php echo esc_html( $shopp_pings['closed'] ); ?></td>
		    </tr>
		</tbody>
	</table>

	<form action="<?php echo $action; ?>" method="post">

		<div class="toolhelp">
			<p><?php _e( 'Note that this is a one-time process, and settings can be overridden on a individual product basis or by WordPress\' settings.', 'shopp-admin-extras' ); ?></p>
		</div>

		<div class="tools">

			<ul>
				<li>
					<label for="shopp_comments"><?php _e( 'Product Comments', 'shopp-admin-extras' ); ?></label>
					<select name="shopp_comments" id="shopp_comments">
						<option selected="selected"><?php _e( 'No Change', 'shopp-admin-extras' ); ?></option>
						<option><?php _e( 'Enable', 'shopp-admin-extras' ); ?></option>
						<option><?php _e( 'Disable', 'shopp-admin-extras' ); ?></option>
					</select>
				</li>
				<li>
					<label for="shopp_pings"><?php _e( 'Product Pings', 'shopp-admin-extras' ); ?></label>
					<select name="shopp_pings" id="shopp_pings">
						<option selected="selected"><?php _e( 'No Change', 'shopp-admin-extras' ); ?></option>
						<option><?php _e( 'Enable', 'shopp-admin-extras' ); ?></option>
						<option><?php _e( 'Disable', 'shopp-admin-extras' ); ?></option>
					</select>
				</li>
			</ul>

			<input type="submit" name="converter" id="converter" value="Change Status" class="button-primary" />

		</div>

	</form>

</div>
