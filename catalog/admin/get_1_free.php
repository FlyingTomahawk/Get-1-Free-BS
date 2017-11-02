<?php
/*
  $Id: get_1_free.php,v 1.5 2015/01/31 Tsimi Exp $
  
  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2015 osCommerce

  Released under the GNU General Public License
*/

  require('includes/application_top.php');

  require('includes/classes/currencies.php');
  $currencies = new currencies();

  $action = (isset($_GET['action']) ? $_GET['action'] : '');

  if (tep_not_null($action)) {
    switch ($action) {
      case 'setflag':
        tep_db_query("update get_1_free
                      set status = '" . (int)$_GET['flag'] . "',
                          date_status_change = now()
                      where get_1_free_id = '" . (int)$_GET['fID'] . "'"
                    );

        tep_redirect(tep_href_link('get_1_free.php', (isset($_GET['page']) ? 'page=' . $_GET['page'] . '&' : '') . 'fID=' . $_GET['fID'], 'NONSSL'));
        break;
        
      case 'insert':
        $products_id = tep_db_prepare_input($_POST['products_id']);
        $products_free_id = tep_db_prepare_input($_POST['products_free_id']);  
        $products_free_quantity = tep_db_prepare_input($_POST['products_free_quantity']);
        $products_qualify_quantity = tep_db_prepare_input($_POST['products_qualify_quantity']);
        $products_multiple = tep_db_prepare_input($_POST['products_multiple']);
		$expdate = tep_db_prepare_input($_POST['get_1_free_expires_date']);

        $expires_date = '';
        if (tep_not_null($expdate)) {
          $expires_date = substr($expdate, 0, 4) . substr($expdate, 5, 2) . substr($expdate, 8, 2);
        }

        tep_db_query("insert into get_1_free
                                  (products_id,
                                   products_free_id,
                                   products_free_quantity,
                                   products_qualify_quantity,
                                   products_multiple,
                                   get_1_free_date_added,
                                   get_1_free_expires_date,
                                   status)
                      values ('" . (int)$products_id . "',
                              '" . tep_db_input($products_free_id) . "',
                              '" . tep_db_input($products_free_quantity) . "',
                              '" . tep_db_input($products_qualify_quantity) . "',
                              '" . tep_db_input($products_multiple) . "',
                              now(),
                              '" . tep_db_input($expires_date) . "',
                              '1')"
                    );
        
        tep_redirect(tep_href_link('get_1_free.php', 'page=' . $_GET['page']));

        break;
        
      case 'update':
        $get_1_free_id = tep_db_prepare_input($_POST['get_1_free_id']);
        $products_id = tep_db_prepare_input($_POST['products_id']);
        $products_free_id = tep_db_prepare_input($_POST['products_free_id']);
        $products_free_quantity = tep_db_prepare_input($_POST['products_free_quantity']);
        $products_qualify_quantity = tep_db_prepare_input($_POST['products_qualify_quantity']);
        $products_multiple = tep_db_prepare_input($_POST['products_multiple']);
        $expdate = tep_db_prepare_input($_POST['get_1_free_expires_date']);
		
		$expires_date = '';
        if (tep_not_null($expdate)) {
          $expires_date = substr($expdate, 0, 4) . substr($expdate, 5, 2) . substr($expdate, 8, 2);
        }

        tep_db_query("update get_1_free
                      set products_free_id = '" . tep_db_input($products_free_id) . "',
                          products_free_quantity = '" . tep_db_input($products_free_quantity) . "',
                          products_qualify_quantity = '" . tep_db_input($products_qualify_quantity) . "',
                          products_multiple = '" . tep_db_input($products_multiple) . "',
                          get_1_free_last_modified = now(),
                          get_1_free_expires_date = '" . $expires_date . "'
					where get_1_free_id = '" . (int)$get_1_free_id . "'"
                      );

        tep_redirect(tep_href_link('get_1_free.php', 'page=' . $_GET['page'] . '&fID=' . (int)$get_1_free_id));
        break;
        
      case 'deleteconfirm':
        $get_1_free_id = tep_db_prepare_input($_GET['fID']);

        tep_db_query("delete from get_1_free
                      where get_1_free_id = '" . (int)$get_1_free_id . "'"
                    );

        tep_redirect(tep_href_link('get_1_free.php', 'page=' . $_GET['page']));
        break;
    }
  }
  
  require('includes/template_top.php');
  
  // Remember selected free product in the dropdown		
		 $free_products = array();
		  $free_array = array();
		  $free_query = tep_db_query("select p.products_id, 
											 pd.products_name, 
											 p.products_price 
											 from products p, 
											 products_description pd 
											 where p.products_id = pd.products_id 
											 and pd.language_id = '" . (int)$languages_id . "' 
											 and p.products_status = '1' 
											 order by products_name");
		  while ($free = tep_db_fetch_array($free_query)) {
			$free_products[] = array('id' => $free['products_id'],
									 'text' => $free['products_name'] . '&nbsp;(' . $currencies->format($free['products_price']) . ')');
			$free_array[$free['products_id']] = $free['products_name'];
		  }
   // End remember
		 
?>

    <table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
       <td width="100%"><table border="0" width="100%" cellspacing="0" cellpadding="0">
         <tr>
            <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
            <td class="pageHeading" align="right"><?php echo tep_draw_separator('pixel_trans.gif', HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT); ?></td>
          </tr>
        </table></td>
      </tr>
<?php
  if ( ($action == 'new') || ($action == 'edit') ) {
    $form_action = 'insert';
    if ( ($action == 'edit') && isset($_GET['fID']) ) {
      $form_action = 'update';

      $product_qualify_query = tep_db_query("select p.products_id,
                                                    pd.products_name,
                                                    g1f.products_free_id,
                                                    g1f.products_free_quantity,
                                                    g1f.products_qualify_quantity,
                                                    g1f.products_multiple,
                                                    g1f.status,
                                                    g1f.get_1_free_expires_date
                                            from products p,
                                                 products_description pd,
                                                 get_1_free g1f
                                            where p.products_id = pd.products_id
                                              and pd.language_id = '" . (int)$languages_id . "'
                                              and p.products_id = g1f.products_id
                                              and g1f.get_1_free_id = '" . (int)$_GET['fID'] . "'"
                                           );
      $product_qualify = tep_db_fetch_array($product_qualify_query);
      $fInfo = new objectInfo($product_qualify);
    } else {
      $fInfo = new objectInfo(array());

// create an array of products already set for get 1 free, which will be
//   excluded from the pull down menu of products
//   (when creating a new product promotion)
      $get_1_free_array = array();
      $get_1_free_query = tep_db_query("select p.products_id
                                        from products p,
                                             get_1_free g1f
                                        where g1f.products_id = p.products_id"
                                      );
      while ($get_1_free = tep_db_fetch_array($get_1_free_query)) {
        $get_1_free_array[] = $get_1_free['products_id'];
      }
    }
?>
      <tr><form name="new_get_1_free" <?php echo 'action="' . tep_href_link('get_1_free.php', tep_get_all_get_params(array('action', 'info', 'fID')) . 'action=' . $form_action, 'NONSSL') . '"'; ?> method="post">
<?php if ($form_action == 'update') echo tep_draw_hidden_field('get_1_free_id', $_GET['fID']); ?>
        <td><br><table border="0" cellspacing="0" cellpadding="2">
          <tr>
            <td class="main"><?php echo TEXT_GET_1_FREE_PRODUCT; ?>&nbsp;</td>
            <td class="main">
<?php
    if ( ($action == 'edit') && isset($_GET['fID']) ) {
      echo '<b>' . $fInfo->products_name . '</b>';
      echo tep_draw_hidden_field('products_id', $fInfo->products_id);
    } else {
      echo tep_draw_products_pull_down('products_id', '', $get_1_free_array);
    }
?>
            </td>
          </tr>
          <tr>
            <td class="main"><?php echo TEXT_GET_1_FREE_PRODUCTS_QUALIFY_QUANTITY; ?>&nbsp;</td>
            <td class="main"><?php echo tep_draw_input_field('products_qualify_quantity', (isset($fInfo->products_qualify_quantity) ? $fInfo->products_qualify_quantity : '')); ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo TEXT_GET_1_FREE_PRODUCTS_MULTIPLE ; ?>&nbsp;</td>
            <td class="main"><?php echo tep_draw_input_field('products_multiple', (isset($fInfo->products_multiple) ? $fInfo->products_multiple : '')); ?></td>
          </tr>
          <tr>
            <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo TEXT_GET_1_FREE_PRODUCTS_FREE; ?>&nbsp;</td>
            <td class="main"><?php echo tep_draw_pull_down_menu('products_free_id', $free_products, $fInfo->products_free_id); ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo TEXT_GET_1_FREE_PRODUCTS_FREE_QUANTITY; ?>&nbsp;</td>
            <td class="main"><?php echo tep_draw_input_field('products_free_quantity', (isset($fInfo->products_free_quantity) ? $fInfo->products_free_quantity : '')); ?></td>
          <tr>
            <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo TEXT_GET_1_FREE_EXPIRES_DATE; ?>&nbsp;</td>
            <td class="main"><?php echo tep_draw_input_field('get_1_free_expires_date', (tep_not_null($fInfo->get_1_free_expires_date) ? substr($fInfo->get_1_free_expires_date, 0, 4) . '-' . substr($fInfo->get_1_free_expires_date, 5, 2) . '-' . substr($fInfo->get_1_free_expires_date, 8, 2) : ''), 'id="expdate"') . ' <small>(YYYY-MM-DD)</small>'; ?></td>
		  </tr>
        </table>
		
<script type="text/javascript">
$('#expdate').datepicker({
  dateFormat: 'yy-mm-dd'
});
</script>		
		
		</td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
          <tr>
            <td class="main"><br><?php echo TEXT_GET_1_FREE_PRICE_TIP; ?></td>
         	<td class="smallText" align="right" valign="top"><br />
			<?php echo tep_draw_button(IMAGE_SAVE, 'disk', null, 'primary') . tep_draw_button(IMAGE_CANCEL, 'close', tep_href_link('get_1_free.php', 'page=' . $_GET['page'] . (isset($GET['fID']) ? '&fID=' . $GET['fID'] : ''))); ?></td>
          
		  </tr>
        </table></td>
      </form></tr>
<?php
  } else {
?>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_PRODUCTS; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_STATUS; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
              </tr>
<?php
    $get_1_free_query_raw = "select p.products_id,
                                    pd.products_name,
                                    p.products_price,
                                    g1f.get_1_free_id,
                                    g1f.products_free_id,
                                    g1f.products_free_quantity,
                                    g1f.products_qualify_quantity,
                                    g1f.products_multiple,
                                    g1f.get_1_free_date_added,
                                    g1f.get_1_free_last_modified,
                                    g1f.get_1_free_expires_date,
                                    g1f.date_status_change,
                                    g1f.status
                             from products p,
                                  get_1_free g1f,
                                  products_description pd
                             where p.products_id = pd.products_id
                               and pd.language_id = '" . (int)$languages_id . "'
                               and p.products_id = g1f.products_id
                             order by pd.products_name";
    $get_1_free_split = new splitPageResults($_GET['page'], MAX_DISPLAY_SEARCH_RESULTS, $get_1_free_query_raw, $get_1_free_query_numrows);
    $get_1_free_query = tep_db_query($get_1_free_query_raw);
    while ($get_1_free = tep_db_fetch_array($get_1_free_query)) {
      if ((!isset($_GET['fID']) || (isset($_GET['fID']) && ($_GET['fID'] == $get_1_free['get_1_free_id']))) && !isset($fInfo)) {
        $fInfo_array = $get_1_free;
        $fInfo = new objectInfo($get_1_free);
      }

      if (isset($fInfo) && is_object($fInfo) && ($get_1_free['get_1_free_id'] == $fInfo->get_1_free_id)) {
        echo '                  <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link('get_1_free.php', 'page=' . $_GET['page'] . '&fID=' . $fInfo->get_1_free_id . '&action=edit') . '\'">' . "\n";
      } else {
        echo '                  <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link('get_1_free.php', 'page=' . $_GET['page'] . '&fID=' . $get_1_free['get_1_free_id']) . '\'">' . "\n";
      }
?>
                <td  class="dataTableContent"><?php echo $get_1_free['products_name']; ?></td>
                <td  class="dataTableContent" align="right">
<?php
      if ($get_1_free['status'] == '1') {
        echo tep_image('images/icon_status_green.gif', IMAGE_ICON_STATUS_GREEN, 10, 10) . '&nbsp;&nbsp;<a href="' . tep_href_link('get_1_free.php', 'action=setflag&flag=0&fID=' . $get_1_free['get_1_free_id'], 'NONSSL') . '">' . tep_image('images/icon_status_red_light.gif', IMAGE_ICON_STATUS_RED_LIGHT, 10, 10) . '</a>';
      } else {
        echo '<a href="' . tep_href_link('get_1_free.php', 'action=setflag&flag=1&fID=' . $get_1_free['get_1_free_id'], 'NONSSL') . '">' . tep_image('images/icon_status_green_light.gif', IMAGE_ICON_STATUS_GREEN_LIGHT, 10, 10) . '</a>&nbsp;&nbsp;' . tep_image('images/icon_status_red.gif', IMAGE_ICON_STATUS_RED, 10, 10);
      }
?></td>
                <td class="dataTableContent" align="right"><?php if (isset($fInfo) && is_object($fInfo) && ($get_1_free['get_1_free_id'] == $fInfo->get_1_free_id)) { echo tep_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ''); } else { echo '<a href="' . tep_href_link('get_1_free.php', 'page=' . $_GET['page'] . '&fID=' . $get_1_free['get_1_free_id']) . '">' . tep_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
      </tr>
<?php
    }
?>
              <tr>
                <td colspan="4"><table border="0" width="100%" cellpadding="0"cellspacing="2">
                  <tr>
                    <td class="smallText" valign="top"><?php echo $get_1_free_split->display_count($get_1_free_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_GET_1_FREE); ?></td>
                    <td class="smallText" align="right"><?php echo $get_1_free_split->display_links($get_1_free_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $_GET['page']); ?></td>
                  </tr>
<?php
  if (empty($action)) {
?>
                  <tr>
                    <td class="smallText" colspan="2" align="right"><?php echo tep_draw_button(IMAGE_NEW_PRODUCT, 'plus', tep_href_link('get_1_free.php', 'page=' . $_GET['page'] . '&action=new')); ?></td>
                  </tr>
<?php
  }
?>
                </table></td>
              </tr>
            </table></td>
<?php
  $heading = array();
  $contents = array();

  switch ($action) {
    case 'delete':
      $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_DELETE_get_1_free . '</b>');
      $contents = array('form' => tep_draw_form('get_1_free', 'get_1_free.php', 'page=' . $_GET['page'] . '&fID=' . $fInfo->get_1_free_id . '&action=deleteconfirm'));
      $contents[] = array('text' => TEXT_INFO_DELETE_INTRO);
      $contents[] = array('text' => '<br><b>' . $fInfo->products_name . '</b>');
      $contents[] = array('align' => 'center', 'text' => '<br />' . tep_draw_button(IMAGE_DELETE, 'trash', null, 'primary') . tep_draw_button(IMAGE_CANCEL, 'close', tep_href_link('get_1_free.php', 'page=' . $_GET['page'] . '&fID=' . $fInfo->get_1_free_id)));
      break;
    default:
      if (is_object($fInfo)) {
        $product_free_query = tep_db_query("select products_name
                                            from products_description
                                            where products_id = '" . (int)$fInfo->products_free_id . "'"
                                   );
        $product_free = tep_db_fetch_array($product_free_query);
        
        $heading[] = array('text' => '<b>' . $fInfo->products_name . '</b>');

       	$contents[] = array('align' => 'center', 'text' => tep_draw_button(IMAGE_EDIT, 'document', tep_href_link('get_1_free.php', 'page=' . $_GET['page'] . '&fID=' . $fInfo->get_1_free_id . '&action=edit')) . tep_draw_button(IMAGE_DELETE, 'trash', tep_href_link('get_1_free.php', 'page=' . $_GET['page'] . '&fID=' . $fInfo->get_1_free_id . '&action=delete')));
        $contents[] = array('text' => '<br>' . TEXT_INFO_DATE_ADDED . ' ' . tep_date_short($fInfo->get_1_free_date_added));
        $contents[] = array('text' => '' . TEXT_INFO_LAST_MODIFIED . ' ' . tep_date_short($fInfo->get_1_free_last_modified));
        $contents[] = array('text' => '<br>' . TEXT_INFO_PRODUCTS_QUALIFY_QUANTITY . ' ' . $fInfo->products_qualify_quantity);
        $contents[] = array('text' => '' . TEXT_INFO_PRODUCTS_MULTIPLE . ' ' . $fInfo->products_multiple);
        $contents[] = array('text' => '<br>' . TEXT_INFO_PRODUCTS_FREE . ' ' . $product_free['products_name']);
        $contents[] = array('text' => '' . TEXT_INFO_PRODUCTS_FREE_QUANTITY . ' ' . $fInfo->products_free_quantity);
        $contents[] = array('text' => '<br>' . TEXT_INFO_EXPIRES_DATE . ' <b>' . tep_date_short($fInfo->get_1_free_expires_date) . '</b>');
        $contents[] = array('text' => '' . TEXT_INFO_STATUS_CHANGE . ' ' . tep_date_short($fInfo->date_status_change));
      }
      break;
  }
  if ( (tep_not_null($heading)) && (tep_not_null($contents)) ) {
    echo '            <td width="25%" valign="top">' . "\n";

    $box = new box;
    echo $box->infoBox($heading, $contents);

    echo '            </td>' . "\n";
  }
}
?>
          </tr>
        </table></td>
      </tr>
    </table></td>
<?php
  require('includes/template_bottom.php');
  require('includes/application_bottom.php');
?>