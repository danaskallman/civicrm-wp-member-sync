<div id="icon-options-general" class="icon32"></div>

<div class="wrap">

	<h2 class="nav-tab-wrapper">
		<a href="<?php echo $urls['settings']; ?>" class="nav-tab"><?php _e( 'Settings', 'civi-wp-member-sync' ); ?></a>
		<a href="<?php echo $urls['list']; ?>" class="nav-tab nav-tab-active"><?php _e( 'Association Rules', 'civi-wp-member-sync' ); ?></a>
		<a href="<?php echo $urls['manual_sync']; ?>" class="nav-tab"><?php _e( 'Manual Synchronize', 'civi-wp-member-sync' ); ?></a>
	</h2>

	<h3><?php _e( 'All Association Rules', 'civi-wp-member-sync' ); ?> <a class="add-new-h2" href="<?php echo $urls['rules']; ?>"><?php _e( 'Add New', 'civi-wp-member-sync' ); ?></a></h3> 

	<?php

	// if we've updated, show message...
	if ( isset( $_GET['syncrule'] ) ) {
		echo '<div id="message" class="updated"><p>';
	
		// switch message based on result
		switch( $_GET['syncrule'] ) {
			case 'edit':
				_e( 'Association Rule updated.', 'civi-wp-member-sync' );
				break;
			case 'add':
				_e( 'Association Rule added.', 'civi-wp-member-sync' );
				break;
			case 'delete':
				_e( 'Association Rule deleted.', 'civi-wp-member-sync' );
				break;
		}

		echo '</p></div>';
	}

	// if we've updated, show message (note that this will only display if we have JS turned off)
	if ( isset( $this->errors ) AND is_array( $this->errors ) ) {
	
		// init messages
		$error_messages = array();
	
		// construct array of messages based on error code
		foreach( $this->errors AS $error_code ) {
			$error_messages[] = $this->error_strings[$error_code];
		}
	
		// show them
		echo '<div id="message" class="error"><p>' . implode( '<br>', $error_messages ) . '</p></div>';
	
	}

	?>

	<table cellspacing="0" class="wp-list-table widefat fixed users">

		<thead>
			<tr>
				<th class="manage-column column-role" id="civi_member_type_id" scope="col"><?php _e( 'Civi Membership Type', 'civi-wp-member-sync' ); ?></th>
				<th class="manage-column column-role" id="current_rule" scope="col"><?php _e( 'Current Codes', 'civi-wp-member-sync' ); ?></th>
				<th class="manage-column column-role" id="expiry_rule" scope="col"><?php _e( 'Expired Codes', 'civi-wp-member-sync' ); ?></th>
				<th class="manage-column column-role" id="wp_mem_cap" scope="col"><?php _e( 'Membership Capability', 'civi-wp-member-sync' ); ?></th>
			</tr>
		</thead>

		<tbody class="civi_wp_member_sync_table" id="civi_wp_member_sync_list">
			<?php
		
			// loop through our data array, keyed by type ID
			foreach( $data AS $key => $item ) {
			
				// construct URLs for this item
				$edit_url = $urls['rules'] . '&mode=edit&type_id='.$key;
				$delete_url = wp_nonce_url( 
					$urls['list'] . '&syncrule=delete&type_id='.$key,
					'civi_wp_member_sync_delete_link',
					'civi_wp_member_sync_delete_nonce'
				);
			
				?>
				<tr> 
					<td>
						<?php echo $this->parent_obj->members->membership_name_get_by_id( $key ); ?><br />
						<div class="row-actions">
							<span class="edit"><a href="<?php echo $edit_url; ?>"><?php _e( 'Edit', 'civi-wp-member-sync' ); ?></a> | </span>
							<span class="delete"><a href="<?php echo $delete_url; ?>" class="submitdelete"><?php _e( 'Delete', 'civi-wp-member-sync' );?></a></span>
						</div>
					</td>
					<td><?php echo $this->parent_obj->members->status_rules_get_current( $item['current_rule'] ); ?></td>
					<td><?php echo $this->parent_obj->members->status_rules_get_current( $item['expiry_rule'] );?></td>
					<td><?php echo CIVI_WP_MEMBER_SYNC_CAP_PREFIX . $key; ?></td>
				</tr>
				<?php
		
			}
		
			?>
		</tbody>

	</table>

</div><!-- /.wrap -->


