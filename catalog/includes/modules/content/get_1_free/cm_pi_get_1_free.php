<?php
/*
  $Id cm_pi_get_1_free.php
  
  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2017 osCommerce

  Released under the GNU General Public License
*/

  class cm_pi_get_1_free {
    var $version = '1.7';
    var $code = '';
    var $group = '';
    var $title = '';
    var $description = '';
    var $sort_order = 0;
    var $enabled = false;

    function __construct() {
      $this->code = get_class($this);
      $this->group = basename(dirname(__FILE__));

      $this->title = MODULE_CONTENT_PRODUCT_INFO_GET_1_FREE_TITLE;
      $this->description = MODULE_CONTENT_PRODUCT_INFO_GET_1_FREE_DESCRIPTION;
      $this->description .= '<div class="secWarning">' . MODULE_CONTENT_BOOTSTRAP_ROW_DESCRIPTION . '</div>';

      if ( defined('MODULE_CONTENT_PRODUCT_INFO_GET_1_FREE_STATUS') ) {
        $this->sort_order = MODULE_CONTENT_PRODUCT_INFO_GET_1_FREE_SORT_ORDER;
        $this->enabled = (MODULE_CONTENT_PRODUCT_INFO_GET_1_FREE_STATUS == 'True');
      }
    }

    function execute() {
      global $oscTemplate, $product_info, $languages_id;
    
	 $content_width = (int)MODULE_CONTENT_PRODUCT_INFO_GET_1_FREE_CONTENT_WIDTH;
	 
	$get_1_free = NULL;	
    // If this product qualifies for free product(s) display promotional text
    $get_1_free_query = tep_db_query("select pd.products_name,
											 g1f.products_free_id,
                                             g1f.products_free_quantity,
                                             g1f.products_qualify_quantity,
											 g1f.get_1_free_expires_date
                                      from get_1_free g1f,
                                           products_description pd
                                      where g1f.products_id = '" . (int)$product_info['products_id'] . "'
                                        and pd.products_id = g1f. products_free_id
                                        and pd.language_id = '" . (int)$languages_id . "'
                                        and status = '1'"
                                    );
    if (tep_db_num_rows($get_1_free_query) > 0) {
      $free_product = tep_db_fetch_array($get_1_free_query);
		
		$get_1_free .= '<p class="alert alert-success">' . sprintf (TEXT_GET_1_FREE_PROMOTION, $free_product['products_qualify_quantity'], $product_info['products_name'], $free_product['products_free_quantity'],  '<a href="' . tep_href_link('product_info.php', 'products_id=' . $free_product['products_free_id']). '" target="_blank">' . $free_product['products_name'] . '</a>');
	
		if ($free_product['get_1_free_expires_date'] > date('Y-m-d H:i:s')) {
			$get_1_free .=  '<span class="small"><em>' . TEXT_OFFER_ENDS . '&nbsp;' . tep_date_long($free_product['get_1_free_expires_date']) . '</em></small>'; 
		}
		$get_1_free .=  '</p>';
	}

	$get_1_free .=  '<div class="clearfix"></div>';

          ob_start();
          include('includes/modules/content/' . $this->group . '/templates/get_1_free.php');
          $template = ob_get_clean();

          $oscTemplate->addContent($template, $this->group);
        
      
    }

    function isEnabled() {
      return $this->enabled;
    }
    
    function check() {
      return defined('MODULE_CONTENT_PRODUCT_INFO_GET_1_FREE_STATUS');
    }

    function install() {
      tep_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ( 'Module Version', 'MODULE_CONTENT_PRODUCT_INFO_GET_1_FREE_VERSION', '" . $this->version . "', 'The version of this module that you are running', '6', '0', 'tep_cfg_disabled(', now() ) ");
      tep_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Product Info Points Module', 'MODULE_CONTENT_PRODUCT_INFO_GET_1_FREE_STATUS', 'True', 'Should the points info be shown on the product info page?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Content Width', 'MODULE_CONTENT_PRODUCT_INFO_GET_1_FREE_CONTENT_WIDTH', '12', 'What width container should the content be shown in?', '6', '2', 'tep_cfg_select_option(array(\'12\', \'11\', \'10\', \'9\', \'8\', \'7\', \'6\', \'5\', \'4\', \'3\', \'2\', \'1\'), ', now())");
	  tep_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Uninstall Remove Database Table', 'MODULE_CONTENT_PRODUCT_INFO_GET_1_FREE_UNINSTALL_DATABASE', 'False', 'Do you want to remove the Get 1 Free Table when uninstall the module? (All Database entries like Customer Points will be deleted, Use this option only if you will not use Get 1 Free any more!)', '6', '25', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_CONTENT_PRODUCT_INFO_GET_1_FREE_SORT_ORDER', '20', 'Sort order of display. Lowest is displayed first.', '6', '3', now())");
	  
	   tep_db_query("CREATE TABLE IF NOT EXISTS get_1_free (
					  get_1_free_id int(11) NOT NULL auto_increment,
					  products_id int(11) NOT NULL default '0',
					  products_qualify_quantity int(11) NOT NULL default '0',
					  products_multiple int(11) NOT NULL default '0',
					  products_free_id int(11) NOT NULL default '0',
					  products_free_quantity int(11) NOT NULL default '0',
					  get_1_free_date_added datetime default NULL,
					  get_1_free_last_modified datetime default NULL,
					  get_1_free_expires_date datetime default NULL,
					  date_status_change datetime default NULL,
					  status int(1) NOT NULL default '1',
					  PRIMARY KEY  (`get_1_free_id`))");
    }

    function remove() {
      tep_db_query("delete from configuration where configuration_key in ('" . implode("', '", $this->keys()) . "')");
	   
	   if ( defined('MODULE_CONTENT_PRODUCT_INFO_GET_1_FREE_UNINSTALL_DATABASE') && MODULE_CONTENT_PRODUCT_INFO_GET_1_FREE_UNINSTALL_DATABASE == 'True' ) {
        tep_db_query("DROP TABLE IF EXISTS get_1_free");
	   }
    }

    function keys() {
      $keys = array();
      $keys[] = 'MODULE_CONTENT_PRODUCT_INFO_GET_1_FREE_VERSION';
      $keys[] = 'MODULE_CONTENT_PRODUCT_INFO_GET_1_FREE_STATUS';
      $keys[] = 'MODULE_CONTENT_PRODUCT_INFO_GET_1_FREE_CONTENT_WIDTH';
      $keys[] = 'MODULE_CONTENT_PRODUCT_INFO_GET_1_FREE_UNINSTALL_DATABASE';
      $keys[] = 'MODULE_CONTENT_PRODUCT_INFO_GET_1_FREE_SORT_ORDER';
      return $keys;
    }
  } // end class

  ////
  // Function to show a disabled entry (Value is shown but cannot be changed)
  if( !function_exists( 'tep_cfg_disabled' ) ) {
    function tep_cfg_disabled( $value ) {
      return tep_draw_input_field( 'configuration_value', $value, ' disabled' );
    }
  }