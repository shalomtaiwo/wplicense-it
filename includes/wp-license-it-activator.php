<?php

class WP_License_It_Activator {

    public function __construct(){
		//add_action( 'init', array( $this, 'pluginprefix_setup_post_type' ));

		add_action( 'admin_init', array( $this, 'activate' ) );
	}

    protected static $wplit_db_version = 1.7;

    public static function activate() {
        $current_wplit_db_version = get_option('wplit_db_version');
        if ( !$current_wplit_db_version ) {
            $current_wplit_db_version = 0;
        }

        if (intval($current_wplit_db_version) < WP_License_It_Activator::$wplit_db_version) {
            if(WP_License_It_Activator::create_upgrade_db()) {
                update_option('wplit_db_version', WP_License_It_Activator::$wplit_db_version);
            }
        }

        // Create WP-Lit Files Directory
        $upload = wp_upload_dir();
        $upload_dir = $upload['basedir'];
        $upload_dir = $upload_dir . '/wplit-files';
        if (! is_dir($upload_dir)) {
        mkdir( $upload_dir, 0755 );
        }
        $wplit_protect_file = new WP_License_It_Protect_File();

        $wplit_protect_file->blockHTTPAccess($upload_dir, $fileType = '*');
    }


    private static function create_upgrade_db(){
        global $wpdb;

        $table_name = $wpdb->prefix . 'wplit_product_licenses';

        $charset_collate = '';
        if (!empty($wpdb->charset)){
            $charset_collate = "DEFAULT CHARACTER SET {$wpdb->charset}";
        }
        if (!empty($wpdb->collate)){
            $charset_collate = "COLLATE {$wpdb->collate}";
        }

        $sql = "CREATE TABLE " . $table_name . "("
                . "id mediumint(9) NOT NULL auto_increment,"
                . "product_id mediumint(9) DEFAULT 0 NOT NULL,"
                . "license_key varchar(48) NOT NULL, "
                . "product_api_key varchar(48) NOT NULL, "
                . "email varchar(48) NOT NULL, "
                . "valid_until datetime DEFAULT '0000-00-00 00:00:00' NOT NULL, "
                . "created_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL, "
                . "updated_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL, "
                . "UNIQUE KEY id (id)" . ")" . $charset_collate. ";";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        return true;
    }
}

new WP_License_It_Activator();