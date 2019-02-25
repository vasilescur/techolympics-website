/* --- Removes the built-in roles from the admin menu --- */
add_action('admin_menu', 'remove_built_in_roles');
 
function remove_built_in_roles() {
    global $wp_roles;
 
    $roles_to_remove = array('subscriber', 'contributor', 'author', 'editor');
 
    foreach ($roles_to_remove as $role) {
        if (isset($wp_roles->roles[$role])) {
            $wp_roles->remove_role($role);
        }
    }
}

/* --- Allow users to log in after registration --- */
add_action( 'gform_user_registered', 'vc_gf_registration_autologin',  10, 4 );
function vc_gf_registration_autologin( $user_id, $user_config, $entry, $password ) {
$user = get_userdata( $user_id );
$user_login = $user->user_login;
$user_password = $password;
 
    wp_signon( array(
'user_login' => $user_login,
'user_password' =>  $user_password,
'remember' => false
 
    ) );
}

/* --- Set user role picker for account creation page --- */
/* --- No longer needed: Made into a dropdown connected to registration feeds --- */
/* add_filter( 'gravityflow_role_field', 'sh_gravityflow_role_field', 10, 3 );
function sh_gravityflow_role_field( $roles, $form_id, $field ) {
	$roles = array(
		array( 'value' => 'student', 'text' => 'Student' ),	// Student reg open!
        array( 'value' => 'faculty_member', 'text' => 'Faculty' ),
        //array( 'volunteer' => 'volunteer', 'text' => 'Volunteer' ) //Volunteer reg closed
	);
	return $roles;
} */

/* --- Log in --- */
/* If this breaks, sorry... abcdefghijklmnopqrstuvwxyz <-- to find this in the DB */
function pt_redirect_login_page() {
    $login_page  = home_url( '/login-2/' );
    $page_viewed = basename($_SERVER['REQUEST_URI']);
    
    if( $page_viewed == "wp-login.php" && $_SERVER['REQUEST_METHOD'] == 'GET') {
        wp_redirect($login_page);
        exit;
    }
}
add_action('init','pt_redirect_login_page');

function pt_login_failed() {
    $login_page  = home_url( '/login-2/' );
    wp_redirect($login_page . "?success=0");
    exit;
}
add_action( 'wp_login_failed', 'pt_login_failed' );

function pt_verify_username_password( $user, $username, $password ) {
    $login_page  = home_url( '/login-2/' );
    if( $username == "" || $password == "" ){
        wp_redirect( $login_page . "?success=0" );
        exit;
    }
}
add_filter( 'authenticate', 'pt_verify_username_password', 1, 3);

/* --- Logout button --- */
add_filter( 'wp_nav_menu_items', 'my_nav_menu_logout_link' );
function my_nav_menu_logout_link( $menu ) {
    if (!is_user_logged_in()) {
        return $menu;
    }
    else {
        $logoutlink = '<li><a href="' . wp_logout_url('https://techolympics.org') . '">Logout</a></li>';
        $menu = $menu . $logoutlink;
        return $menu;
    }
}


/* ----- Custom Table Stuff ----- */

/**
 * Take a WPDB query result and display it as a table, with headers from data keys.
 * This example only works with ARRAY_A type result from $wpdb query.
 * @param  array                $db_data Result from $wpdb query
 * @return bool                          Success, outputs table HTML
 */
function data_table( $db_data ) {
	if ( !is_array( $db_data) || empty( $db_data ) ) return false;

	// Get the table header cells by formatting first row's keys
	$header_vals = array();
	$keys = array_keys( $db_data[0] );
	foreach ($keys as $row_key) {
		$header_vals[] = ucwords( str_replace( '_', ' ', $row_key ) ); // capitalise and convert underscores to spaces
	}
	$header = "<thead><tr><th>" . join( '</th><th>', $header_vals ) . "</th></tr></thead>";

	// Make the data rows
	$rows = array();
	foreach ( $db_data as $row ) {
		$row_vals = array();
		foreach ($row as $key => $value) {

			// format any date values properly with WP date format
			if ( strpos( $key, 'date' ) !== false || strpos( $key, 'modified' ) !== false ) {
				$date_format = get_option( 'date_format' );
				$value = mysql2date( $date_format, $value );
			}
			$row_vals[] = $value;
		}
		$rows[] = "<tr><td>" . join( '</td><td>', $row_vals ) . "</td></tr>";
	}

	// Put the table together and output
	echo '<table class="wp-list-table widefat fixed posts">' . 
   '<colgroup>
       <col span="1" style="width: 15%;">
       <col span="1">
       <col span="1" style="width: 15%;">
       <col span="1" style="width: 15%;">
       <col span="1" style="width: 5%;">
       <col span="1" style="width: 5%;">
       <col span="1" style="width: 8%;">
       <col span="1">
       <col span="1" style="width: 15%;">
    </colgroup>' . $header . '<tbody>' . join( $rows ) . '</tbody></table>';

	return true;
}

function data_table_as_string( $db_data ) {
	if ( !is_array( $db_data) || empty( $db_data ) ) return false;

	// Get the table header cells by formatting first row's keys
	$header_vals = array();
	$keys = array_keys( $db_data[0] );
	foreach ($keys as $row_key) {
		$header_vals[] = ucwords( str_replace( '_', ' ', $row_key ) ); // capitalise and convert underscores to spaces
	}
	$header = "<thead><tr><th>" . join( '</th><th>', $header_vals ) . "</th></tr></thead>";

	// Make the data rows
	$rows = array();
	foreach ( $db_data as $row ) {
		$row_vals = array();
		foreach ($row as $key => $value) {

			// format any date values properly with WP date format
			if ( strpos( $key, 'date' ) !== false || strpos( $key, 'modified' ) !== false ) {
				$date_format = get_option( 'date_format' );
				$value = mysql2date( $date_format, $value );
			}
			$row_vals[] = $value;
		}
		$rows[] = "<tr><td>" . join( '</td><td>', $row_vals ) . "</td></tr>";
	}

	// Put the table together and output
	return '<table class="wp-list-table widefat fixed posts">' . $header . '<tbody>' . join( $rows ) . '</tbody></table>';
}

/* Usage with an Admin Dashboard page */
function database_admin_dashboard_page() {
	
    // I hate php and WordPress for this:
    $usersTableName = $wpdb->prefix . 'users';
    $leadTableName = $wpdb->prefix . 'rg_lead';
    
	// Query: Full Report
	global $wpdb;
	$paid_students = $wpdb->get_results("	-- Welcome to the depths of Hell. Enjoy your stay! :)
        SELECT			(
    				CASE WHEN (
                    
                        SELECT `value`								
                        FROM wpxp_rg_lead_detail					
                        WHERE 	`field_number` = 5					
                            AND `form_id` = 1						
                            AND `lead_id` IN (						
                                SELECT `lead_id`					
                                FROM wpxp_rg_lead_detail			
                                WHERE 	`field_number` = 4
                                    AND `form_id` = 1
                                    AND `value` = `user_email`
                            )
                        LIMIT 0, 1
                    ) IN ('student', 'faculty_member')
                         THEN `display_name`
  						 ELSE 
                            CONCAT( 
                                (
                                    SELECT `value`								
                                    FROM wpxp_rg_lead_detail 
                                    WHERE   `form_id` = 1
                                        AND `lead_id` IN (
                                            SELECT `lead_id`
                                            FROM wpxp_rg_lead_detail
                                            WHERE	`field_number` = 4
                                                AND `form_id` = 1
                                                AND `value` = `user_email`
                                        )
                                    	AND cast(`field_number` AS DECIMAL(2,1)) = 6.3
                                    LIMIT 0, 1
                                ), ' ',
                                (
                                    SELECT `value`								
                                    FROM wpxp_rg_lead_detail
                                    WHERE	`form_id` = 1
                                        AND `lead_id` IN (
                                            SELECT `lead_id`
                                            FROM wpxp_rg_lead_detail
                                            WHERE	`field_number` = 4
                                                AND `form_id` = 1
                                                AND `value` = `user_email`
                                        )
                                    	AND cast(`field_number` AS DECIMAL(2,1)) = 6.6
                                    LIMIT 0, 1
                                )
                            )  -- end CONCAT
    		  END) AS 'Name',
                 (
                    SELECT `value`								-- select value
                    FROM wpxp_rg_lead_detail					-- from form submissions table
                    WHERE 	`field_number` = 5					-- Role (account type) field
                        AND `form_id` = 1						-- Account creation form
                        AND `lead_id` IN (						-- lead_id is unique per user per form
                            SELECT `lead_id`					
                            FROM wpxp_rg_lead_detail			-- get the lead_id by looking up the email in form submissions
                            WHERE 	`field_number` = 4
                                AND `form_id` = 1
                                AND `value` = `user_email`
                        )
                    LIMIT 0, 1									-- I don't know why but it doesn't work without this
                ) AS 'Role',
                `user_email` AS 'Email',
                
                (
    				CASE WHEN (
                    
                        SELECT `value`								
                        FROM wpxp_rg_lead_detail					
                        WHERE 	`field_number` = 5					
                            AND `form_id` = 1						
                            AND `lead_id` IN (						
                                SELECT `lead_id`					
                                FROM wpxp_rg_lead_detail			
                                WHERE 	`field_number` = 4
                                    AND `form_id` = 1
                                    AND `value` = `user_email`
                            )
                        LIMIT 0, 1
                    ) IN ('student', 'faculty_member')
                         THEN (
                    		SELECT `meta_value`									-- get the value of a metadata key
                    			FROM wpxp_usermeta								-- User meta table
                    			WHERE 	`meta_key` = 'School'					-- Key is 'School'
                        			AND wpxp_usermeta.user_id = wpxp_users.id	-- Match usermeta id to user id   
                         )
  						 ELSE (
						     CASE WHEN (
                                 SELECT `value`
                                    FROM wpxp_rg_lead_detail
                                    WHERE	`field_number` = 4
                                        AND `form_id` = 11
                                        AND `lead_id` IN (
                                            SELECT `id`
                                            FROM wpxp_rg_lead
                                            WHERE	`created_by` = wpxp_users.ID  -- lead_id depends on form!
                                                AND `form_id` = 11
                                        )
                                    LIMIT 0, 1
                             ) = 'Other' THEN (
                                 SELECT `value`
                                    FROM wpxp_rg_lead_detail
                                    WHERE	`field_number` = 2
                                        AND `form_id` = 11
                                        AND `lead_id` IN (
                                            SELECT `id`
                                            FROM wpxp_rg_lead
                                            WHERE	`created_by` = wpxp_users.ID  -- lead_id depends on form!
                                                AND `form_id` = 11
                                        )
                                    LIMIT 0, 1
                             ) ELSE (
                                 SELECT `value`
                                    FROM wpxp_rg_lead_detail
                                    WHERE	`field_number` = 4
                                        AND `form_id` = 11
                                        AND `lead_id` IN (
                                            SELECT `id`
                                            FROM wpxp_rg_lead
                                            WHERE	`created_by` = wpxp_users.ID
                                                AND `form_id` = 11
                                        )
                                    LIMIT 0, 1
                             )
                      END)
    		  END) AS 'School/Company',
                
                
--                (                    
--                    SELECT `meta_value`							-- get the value of a metadata key
--                    FROM wpxp_usermeta							-- User meta table
--                    WHERE 	`meta_key` = 'School'				-- Key is 'School'
--                        AND wpxp_usermeta.user_id = wpxp_users.id	-- Match usermeta id to user id
--                ) AS 'School',
                (
                    CASE WHEN (
                    	SELECT `value`								
                        FROM wpxp_rg_lead_detail					
                        WHERE 	`field_number` = 5					
                            AND `form_id` = 1						
                            AND `lead_id` IN (						
                                SELECT `lead_id`					
                                FROM wpxp_rg_lead_detail			
                                WHERE 	`field_number` = 4
                                    AND `form_id` = 1
                                    AND `value` = `user_email`
                            )
                        LIMIT 0, 1
                    ) IN ('student', 'faculty_member')
                    	THEN (
                            CASE WHEN `ID` IN (							-- If the student's id matches
                                SELECT `created_by`						-- The list of ID's that have created entries
                                    FROM wpxp_rg_lead						-- In the form submissions table
                                    WHERE `form_id` = 2						-- of Form #2 (profile)
                    		) THEN 'Yes' ELSE 'No' END					-- If yes, then 'Yes'
             		    )
                    	ELSE (
                            CASE WHEN (
                                (
                                    CASE WHEN (

                                        SELECT `value`								
                                        FROM wpxp_rg_lead_detail					
                                        WHERE 	`field_number` = 5					
                                            AND `form_id` = 1						
                                            AND `lead_id` IN (						
                                                SELECT `lead_id`					
                                                FROM wpxp_rg_lead_detail			
                                                WHERE 	`field_number` = 4
                                                    AND `form_id` = 1
                                                    AND `value` = `user_email`
                                            )
                                        LIMIT 0, 1
                                    ) IN ('student', 'faculty_member')
                                         THEN (
                                            SELECT `meta_value`									-- get the value of a metadata key
                                                FROM wpxp_usermeta								-- User meta table
                                                WHERE 	`meta_key` = 'School'					-- Key is 'School'
                                                    AND wpxp_usermeta.user_id = wpxp_users.id	-- Match usermeta id to user id   
                                         )
                                         ELSE (
                                             CASE WHEN (
                                                 SELECT `value`
                                                    FROM wpxp_rg_lead_detail
                                                    WHERE	`field_number` = 4
                                                        AND `form_id` = 11
                                                        AND `lead_id` IN (
                                                            SELECT `id`
                                                            FROM wpxp_rg_lead
                                                            WHERE	`created_by` = wpxp_users.ID  -- lead_id depends on form!
                                                                AND `form_id` = 11
                                                        )
                                                    LIMIT 0, 1
                                             ) = 'Other' THEN (
                                                 SELECT `value`
                                                    FROM wpxp_rg_lead_detail
                                                    WHERE	`field_number` = 2
                                                        AND `form_id` = 11
                                                        AND `lead_id` IN (
                                                            SELECT `id`
                                                            FROM wpxp_rg_lead
                                                            WHERE	`created_by` = wpxp_users.ID  -- lead_id depends on form!
                                                                AND `form_id` = 11
                                                        )
                                                    LIMIT 0, 1
                                             ) ELSE (
                                                 SELECT `value`
                                                    FROM wpxp_rg_lead_detail
                                                    WHERE	`field_number` = 4
                                                        AND `form_id` = 11
                                                        AND `lead_id` IN (
                                                            SELECT `id`
                                                            FROM wpxp_rg_lead
                                                            WHERE	`created_by` = wpxp_users.ID
                                                                AND `form_id` = 11
                                                        )
                                                    LIMIT 0, 1
                                             )
                                      END)
                              END)
                                
                            ) IS NOT NULL
                            	THEN 'Yes'
                            	ELSE 'No' END
                        )
                    
                 END) AS 'Profile?',

                (
                    CASE WHEN `ID` IN (
                        SELECT `created_by`
                        FROM wpxp_rg_lead
                        WHERE `form_id` = 6						-- Same thing but for Payment form (ID = 6)
                    ) THEN 'Yes' ELSE 'No'
             END) AS 'Paid?',
                (
                    SELECT `value`								-- Same method as getting school, but this time for shirt size
                    FROM wpxp_rg_lead_detail
                    WHERE 	`field_number` = 8					-- Field #22 is shirt size
                        AND `form_id` = 2						-- Student profile form (ID = 2)
                        AND `lead_id` IN (
                            SELECT `lead_id`
                            FROM wpxp_rg_lead_detail
                            WHERE 	`field_number` = 22			
                                AND `form_id` = 2				-- Student profile form (ID = 2)
                                AND `value` = `user_email`
                        )
                    LIMIT 0, 1									-- Again, don't know why/if needed but it ain't broke so don't fix it.
                ) AS 'Size',
                (
                    SELECT `value`								-- Same method yet again, but to get the parent phone number.
                    FROM wpxp_rg_lead_detail
                    WHERE	`field_number` = 13
                        AND `form_id` = 2
                        AND `lead_id` IN (
                            SELECT `lead_id`
                            FROM wpxp_rg_lead_detail
                            WHERE	`field_number` = 22
                                AND `form_id` = 2
                                AND `value` = `user_email`
                        )
                    LIMIT 0, 1
                ) AS 'Parent Phone',
                (
                	SELECT `value`								-- Watch out-- must search in a different way
                    FROM wpxp_rg_lead_detail
                    WHERE	`field_number` = 7
                        AND `form_id` = 5
                        AND `lead_id` IN (
                            SELECT `id`
                            FROM wpxp_rg_lead
                            WHERE	`created_by` = wpxp_users.ID  -- lead_id depends on form!
                                AND `form_id` = 5
                        )
                    LIMIT 0, 1
                ) AS 'Allergies etc.'
        FROM wpxp_users
        ORDER BY `Role`, `Name`									-- Sort by Role, then sub-sort by Name
    ", ARRAY_A );

	?>
		<div class="wrap">
			<h1>Students</h1>
			<?php data_table( $paid_students ); ?>
		</div>
	<?php
}

/* Register menu page to use for paid students */
function register_paid_students_page() {
  add_menu_page( 'Master Registration Report', 'TechOlympics', 'manage_options', 'master_registration_report', 'database_admin_dashboard_page', 'dashicons-list-view', 90 );
}
add_action( 'admin_menu', 'register_paid_students_page' );


/* Faculty Dashboards */

//[my-students-list]
function my_students_list_func( $atts ){
	// Get current user info
    $current_user = wp_get_current_user();
    $email = $current_user->user_email;
    
    // Query
    global $wpdb;
    $stmt = $wpdb->prepare("
    SELECT `display_name`, `user_email`, (
        CASE WHEN (
           `ID` IN (
               SELECT created_by
               FROM wpxp_gf_entry
               WHERE form_id = 15
            )
        ) THEN 'Yes' ELSE 'No'
        END) AS 'Paid?'
        FROM wpxp_users
        WHERE user_email COLLATE utf8mb4_unicode_520_ci IN (
            SELECT DISTINCT D.meta_value
            FROM wpxp_gf_entry_meta A, wpxp_gf_entry_meta B, wpxp_gf_entry_meta C, wpxp_gf_entry_meta D
            WHERE B.meta_value = %s 
            AND  B.entry_id = A.entry_id
            AND A.meta_Key = 7 
            AND C.meta_value = A.meta_value 
            AND C.entry_id = D.entry_id 
            AND D.meta_key=4 
        )
        AND `ID` IN (
           SELECT created_by
           FROM wpxp_gf_entry
           WHERE form_id = 14
        )
    ", $email);
    $my_students = $wpdb->get_results($stmt, ARRAY_A);
    
    return data_table_as_string($my_students);
}
add_shortcode( 'my-students-list', 'my_students_list_func' );




/* // Old version
//[my-paid-students-list]
function my_paid_students_list_func( $atts ){
	// Get current user info
    $current_id = get_current_user_id();
    $school = get_usermeta($current_id, 'School');
    
    // Query
    global $wpdb;
    $my_paid_students = $wpdb->get_results("
        SELECT `display_name`, `user_email`
        FROM wpxp_users
        WHERE `ID` in (
            SELECT DISTINCT user_id
            FROM wpxp_usermeta
            WHERE meta_value = '{$school}'
        ) AND ID IN (SELECT created_by FROM `wpxp_rg_lead` WHERE `payment_status` = 'Paid')
    ", ARRAY_A); 
    //TODO: this is vulnerable to SQL injection through School field; it's a dropdown so it's okay
    
    return data_table_as_string($my_paid_students);
}
add_shortcode( 'my-paid-students-list', 'my_paid_students_list_func' );
*/






