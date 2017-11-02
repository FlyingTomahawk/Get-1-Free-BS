<?php
/*
  $Id: get_1_free.php
  
  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2017 osCommerce

  Released under the GNU General Public License
*/

// Sets the status of a get_1_free product
  function tep_set_get_1_free_status($get_1_free_id, $status) {
    return tep_db_query("update get_1_free set status = '" . $status . "', date_status_change = now() where get_1_free_id = '" . (int)$get_1_free_id . "'");
  }

////
// Auto expire products on get_1_free
  function tep_expire_get_1_free() {
    $get_1_free_query = tep_db_query("select get_1_free_id from get_1_free where status = '1' and now() >= get_1_free_expires_date and get_1_free_expires_date > 0");
    if (tep_db_num_rows($get_1_free_query)) {
      while ($get_1_free = tep_db_fetch_array($get_1_free_query)) {
        tep_set_get_1_free_status($get_1_free['get_1_free_id'], '0');
      }
    }
  }
?>