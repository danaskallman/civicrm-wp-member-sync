<?php /* 
--------------------------------------------------------------------------------
Civi_WP_Member_Sync_Users Class
--------------------------------------------------------------------------------
*/



/**
 * Class for encapsulating WordPress user functionality
 */
class Civi_WP_Member_Sync_Users {

	/** 
	 * Properties
	 */
	
	// parent object
	public $parent_obj;
	
	
	
	/** 
	 * Initialise this object
	 * @param object $parent_obj The parent object
	 * @return object
	 */
	function __construct( $parent_obj ) {
		
		// store reference to parent
		$this->parent_obj = $parent_obj;
	
		// --<
		return $this;
		
	}
	
	
	
	/**
	 * Initialise when CiviCRM initialises
	 * @return nothing
	 */
	public function initialise() {
		
	}
	
	
	
	//##########################################################################
	
	
	
	/**
	 * Get WordPress user role
	 * @param WP_User $user WP_User object
	 * @return string $role Primary WordPress role for this user
	 */
	public function wp_role_get( $user ) {
	
		// kick out if we don't receive a valid user
		if ( ! is_a( $user, 'WP_User' ) ) return false;
		
		// only build role names array once, since this is called by the sync routine
		if ( ! isset( $this->role_names ) ) {
		
			// get role names array
			$this->role_names = $this->wp_role_names_get_all();
		
		}
		
		// init filtered as empty
		$filtered_roles = array_keys( $this->role_names );
		
		// roles is still an array
		foreach ( $user->roles AS $role ) {
		
			// return the first valid one
			if ( $role AND in_array( $role, $filtered_roles ) ) { return $role; }
		
		}
	
		// fallback
		return false;
		
	}
	
	
		
	/**
	 * Set WordPress user role
	 * @param WP_User $user WP_User object of the logged-in user.
	 * @param string $old_role Old WordPress role key
	 * @param string $new_role New WordPress role key
	 * @return nothing
	 */
	public function wp_role_set( $user, $old_role, $new_role ) {
		
		// kick out if we don't receive a valid user
		if ( ! is_a( $user, 'WP_User' ) ) return;
		
		// sanity check params
		if ( empty( $old_role ) ) return;
		if ( empty( $new_role ) ) return;
		
		// Remove old role then add new role, so that we don't inadventently 
		// overwrite multiple roles, for example when BBPress is active
		
		// remove user's existing role
		$user->remove_role( $old_role );
		 
		// add new role
		$user->add_role( $new_role );
		 
	}
	
	
		
	//##########################################################################
	
	
	
	/**
	 * Get a WordPress role name by role key
	 * @param string $key The machine-readable name of the WP_Role
	 * @return string $role_name The human-readable name of the WP_Role
	 */
	public function wp_role_name_get( $key ) {
		
		// only build role names array once, since this is called by the list page
		if ( ! isset( $this->role_names ) ) {
		
			// get role names array
			$this->role_names = $this->wp_role_names_get_all();
		
		}
		
		// get value by key
		$role_name = isset( $this->role_names[$key] ) ? $this->role_names[$key] : false;
		
		// --<
		return $role_name;
		
	}
	
	
		
	/**
	 * Get all WordPress role names
	 * @return array $role_names An array of role names, keyed by role key
	 */
	public function wp_role_names_get_all() {
		
		// access roles global
		global $wp_roles;

		// load roles if not set
		if ( ! isset( $wp_roles ) ) {
			$wp_roles = new WP_Roles();
		}
		
		// get names
		$role_names = $wp_roles->get_names();
		
		// if we have BBPress active, filter out its custom roles
		if ( function_exists( 'bbp_get_blog_roles' ) ) {
		
			// get BBPress-filtered roles
			$bbp_roles = bbp_get_blog_roles();
			
			// init roles
			$role_names = array();
			
			// sanity check
			if ( ! empty( $bbp_roles ) ) {
				foreach( $bbp_roles AS $bbp_role => $bbp_role_data ) {
					
					// add to roles array
					$role_names[$bbp_role] = $bbp_role_data['name'];
					
				}
			}
			
		}
		
		//print_r( $role_names ); die();
		
		// --<
		return $role_names;
		
	}
	
	
	
	//##########################################################################
	
	
	
	/**
	 * Add a capability to a WordPress user
	 * @param WP_User $user WP_User object of the logged-in user.
	 * @param string $capability Capability name
	 * @return nothing
	 */
	public function wp_cap_add( $user, $capability ) {
		
		// kick out if we don't receive a valid user
		if ( ! is_a( $user, 'WP_User' ) ) return;
		
		// sanity check params
		if ( empty( $capability ) ) return;
		
		// does this user have that capability?
		if ( ! $user->has_cap( $capability ) ) {
		
			// no, add it
			$user->add_cap( $capability );
		
		}
	
	}
	
	
		
	/**
	 * Remove a capability from a WordPress user
	 * @param WP_User $user WP_User object of the logged-in user.
	 * @param string $capability Capability name
	 * @return nothing
	 */
	public function wp_cap_remove( $user, $capability ) {
		
		// kick out if we don't receive a valid user
		if ( ! is_a( $user, 'WP_User' ) ) return;
		
		// sanity check params
		if ( empty( $capability ) ) return;
		
		// does this user have that capability?
		if ( $user->has_cap( $capability ) ) {
		
			// yes, remove it
			$user->remove_cap( $capability );
		
		}
		
	}
	
	
		
	//##########################################################################
	
	
	
	/**
	 * Get a WordPress user for a Civi contact ID
	 * @param int $contact_id The numeric CiviCRM contact ID
	 * @return WP_User $user WP_User object for the WordPress user
	 */
	public function wp_user_get_by_civi_id( $contact_id ) {
		
		// kick out if no CiviCRM
		if ( ! civi_wp()->initialize() ) return false;
		
		// make sure Civi file is included
		require_once 'CRM/Core/BAO/UFMatch.php';
			
		// search using Civi's logic
		$user_id = CRM_Core_BAO_UFMatch::getUFId( $contact_id );
		
		// kick out if we didn't get one
		if ( empty( $user_id ) ) { return false; }
		
		// get user object
		$user = new WP_User( $user_id );
		
		// --<
		return $user;
		
	}
	
	
	
	/**
	 * Get a Civi contact ID for a WordPress user object
	 * @param WP_User $user WP_User object of the logged-in user.
	 * @return int $civi_contact_id The numerical CiviCRM contact ID
	 */
	public function civi_contact_id_get( $user ) {
	
		// kick out if no CiviCRM
		if ( ! civi_wp()->initialize() ) return false;
		
		// make sure Civi file is included
		require_once 'CRM/Core/BAO/UFMatch.php';
			
		// do initial search
		$civi_contact_id = CRM_Core_BAO_UFMatch::getContactId( $user->ID );
		if ( ! $civi_contact_id ) {
			
			// sync this user
			CRM_Core_BAO_UFMatch::synchronizeUFMatch(
				$user, // user object
				$user->ID, // ID
				$user->user_mail, // unique identifier
				'WordPress', // CMS
				null, // status
				'Individual', // contact type
				null // is_login
			);
			
			// get the Civi contact ID
			$civi_contact_id = CRM_Core_BAO_UFMatch::getContactId( $user->id );
			
			// sanity check
			if ( ! $civi_contact_id ) {
				return false;
			}
		
		}
		
		// --<
		return $civi_contact_id;
		
	}
	
	
	
} // class ends


