-- Welcome to the depths of Hell. Enjoy your stay! :)
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