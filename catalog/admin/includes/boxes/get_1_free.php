<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2017 osCommerce

  Released under the GNU General Public License
  
*/


    foreach ( $cl_box_groups as &$group ) {
    if ( $group['heading'] == BOX_HEADING_CATALOG ) {
      $group['apps'][] = array('code' => 'get_1_free.php',
                               'title' => BOX_CATALOG_GET_1_FREE,
                               'link' => tep_href_link('get_1_free.php'));

      break;
    }
  }
?>