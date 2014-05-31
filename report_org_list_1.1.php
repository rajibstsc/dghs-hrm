<?php
require_once 'configuration.php';

if ($_SESSION['logged'] != true) {
  header("location:login.php");
}
// checks for value inconsistency
$DBvalidation = FALSE;
if ($_REQUEST['highlight_empty_cell'] == 'true') { // checks for empty cells
  $DBvalidation = TRUE;
}

// just to easily create readable names from db field names. this is a lame feature anyway :) but it helps reduce the column width
$replaceUnderScoreWithSpace = FALSE;
if ($_REQUEST['tableheader_without_underscore'] == 'true') {
  $replaceUnderScoreWithSpace = TRUE;
}

// forms submission
if (isset($_REQUEST['submit'])) {

  $parameterized_query = " WHERE 1 "; // default parameter query
  //$selection_string = "";

  $singleSelectItems = array('division_code', 'district_code', 'upazila_id'); // put the input field names for single selection dropdowns. Input filed name must be same as table filed name

  foreach ($singleSelectItems as $singleSelectItem) {
    if (strlen($_REQUEST[$singleSelectItem]) && $_REQUEST[$singleSelectItem] > 0) {
      $parameterized_query.=" AND $singleSelectItem = '" . mysql_real_escape_string(trim($_REQUEST[$singleSelectItem])) . "' ";
    }
  }

  $multiSelectItems = array('agency_code', 'org_type_code', 'org_level_code', 'org_healthcare_level_code', 'org_location_type', 'ownership_code', 'source_of_electricity_main_code', 'source_of_electricity_alternate_code', 'source_of_water_supply_main_code', 'source_of_water_supply_alternate_code', 'toilet_type_code', 'toilet_adequacy_code', 'fuel_source_code', 'laundry_code', 'autoclave_code'); // put the input field names for multiple selection dropdowns. Input filed name must be same as table filed name
  $csvs = array();
  foreach ($multiSelectItems as $multiSelectItem) {
    if (count($_REQUEST[$multiSelectItem])) {
      $csvs[$multiSelectItem] = "'" . implode("','", $_REQUEST[$multiSelectItem]) . "'";
      $parameterized_query.=" AND $multiSelectItem in (" . $csvs[$multiSelectItem] . ")  ";
      //$selection_string .= " Agency: <strong>" . getAgencyNameFromAgencyCode($agency_code) . "</strong>";
    }
  }

  if (strlen($_REQUEST['search_field']) && strlen($_REQUEST['search_criteria']) && strlen($_REQUEST['search_value'])) {
    if (in_array($_REQUEST['search_criteria'], array('=', '<', '<=', '>', '>='))) {
      $parameterized_query.=" AND " . $_REQUEST['search_field'] . " " . $_REQUEST['search_criteria'] . " " . $_REQUEST['search_value'] . "  ";
    } else if ($_REQUEST['search_criteria'] == "LIKE") {
      $parameterized_query.=" AND " . $_REQUEST['search_field'] . " " . $_REQUEST['search_criteria'] . " '%" . $_REQUEST['search_value'] . "%'  ";
    }
  }

  if (strlen(trim($_REQUEST['SQLSelect']))) {
    $parameterized_query.= " AND " . trim(($_REQUEST['SQLSelect']));
  }

  /*
   *    SQL GROUP BY
   */
  $countField = "";
  if (strlen(trim($_REQUEST['SQLGroup']))) {
    $group_by = trim($_REQUEST['SQLGroup']);
    $parameterized_query .= " GROUP BY $group_by ";
    $countField = ",COUNT(*) as total";
  }
  /*   * *********** */
  /*
   *    SQL ORDER BY
   */
  $order_by = "org_name";
  $order_sort = "ASC";

  /**
   * Show fields
   */
  $TableFields = getTableFieldNames('organization');

  if (count($_REQUEST['f'])) {
    $showFields = $_REQUEST['f'];
  } else {
    $showFields = array("id", "org_name", "org_code", "org_type_name", "org_type_code", "agency_name", "org_function_code", "org_level_name", "upazila_thana_name", "union_name",);
  }
  $showFieldsCsv = implode(',', $showFields);

  /*   * **************** */

  /*   * **
   * If order is set then it over rides the f[]
   */
  if (strlen(trim($_REQUEST['ColOrder']))) {

    $showFieldsCsv = str_replace(" ", '', trim($_REQUEST['ColOrder'], " ,"));
    $showFields = explode(',', $showFieldsCsv);
  }

  if (strlen(trim($_REQUEST['order_by'])) && strlen(trim($_REQUEST['order_sort']))) {
    $order_by = trim($_REQUEST['order_by']);
    $order_sort = trim($_REQUEST['order_sort']);
  }

  $parameterized_query .= " ORDER BY $order_by $order_sort ";
  /*   * *********** */



  $sql = "SELECT * $countField FROM organization $parameterized_query";
  $result = mysql_query($sql) or die(mysql_error() . "<br /><br />Code:<b>get_org_list:1</b><br /><br /><b>Query:</b><br />___<br />$sql<br />");
  $count = mysql_num_rows($result);

  //
  $fieldNameAlias = array();
  assignAliasForDbFieldNames();

  /*
    //easy create $fieldNameAlias by printing
    $fieldNames = getTableFieldNames('organization');
    foreach ($fieldNames as $fieldName) {
    echo '$fieldNameAlias[\'' . $fieldName . '\'] =\'' . $fieldName . '\'' . ";<br/>";
    }

   */
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>Report</title>
    <?php
    include_once 'include/header/header_css_js.inc.php';
    include_once 'include/header/header_ga.inc.php';
    ?>
    <link href="assets/css/report.css" rel="stylesheet"/>
  </head>
  <body>
    <?php include_once 'include/header/header_top_menu.inc.php'; ?>
    <div class="container">
      <h4 style="text-transform: uppercase">Report : Organization</h4>
      <?php if ($_REQUEST['HideFilter'] != 'true') { ?>

        <div id="showHide" style="cursor: pointer">
          <span id="showHideBtn" >[ - ] Hide Filters</span>
          <script type="text/javascript">
            $('#showHide').click(function() {
              //alert('test');
              $('#filter').toggle(function() {
                var text = "Show";
                if ($('div#filter').is(":visible")) {
                  text = "[ - ] Hide Filters";
                } else {
                  text = "[+]Show Filters";
                }
                $('span#showHideBtn').html(text);
              });

            });</script>

        </div>

        <div class = "filter" id = "filter">
          <form class = "form-horizontal" action = "<?php echo $_SERVER['PHP_SELF']; ?>" method = "get" style = "padding: 0px; margin: 0px;">
            <table id = "">
              <tr>
                <td style = "vertical-align: top">
                  <b>Select Administrative Unit</b><br/>
                  <table>
                    <tr>
                      <td><b>Division</b></td>
                      <td><?php createSelectOptions('admin_division', 'division_bbs_code', 'division_name', $customQuery, $_REQUEST['division_code'], "division_code", " id='admin_division'  class='pull-left' ", $optionIdField)
        ?></td>
                    </tr>
                    <tr>
                      <td><b>District</b></td>
                      <td><?php createSelectOptions('admin_district', 'district_bbs_code', 'district_name', " WHERE division_bbs_code='" . $_REQUEST['division_code'] . "'", $_REQUEST['district_code'], "district_code", " id='admin_district' class='pull-left' ", $optionIdField); ?></td>
                    </tr>
                    <tr>
                      <td><b>Upazila</b></td>
                      <td><?php createSelectOptions('admin_upazila', 'id', 'upazila_name', " WHERE upazila_district_code='" . $_REQUEST['district_code'] . "'", $_REQUEST['upazila_id'], "upazila_id", " id='admin_upazila'  class='pull-left' ", $optionIdField); ?></td>
                    </tr>
                  </table>
                  <?php
                  $checked = "";
                  if ($_REQUEST['noDatatable'] == 'true') {
                    $checked = " checked='checked' ";
                  }
                  ?>
                  <input type="checkbox" name="noDatatable" value="true" <?= $checked ?>/> Optimize loading
                </td>
                <td style="vertical-align: top">
                  <b>Agency</b><br/>
                  <?php //createMultiSelectOptions($dbtableName, $dbtableIdField, $dbtableValueField, $customQuery, $selectedIdCsv, $name, $params);          ?>
                  <?php createMultiSelectOptions('org_agency_code', 'org_agency_code', 'org_agency_name', $customQuery, $csvs['agency_code'], "agency_code[]", " id='agency_code'  class='multiselect' "); ?><br/>
                  <b>Org Level</b><br/>
                  <?php createMultiSelectOptions('org_level', 'org_level_code', 'org_level_name', $customQuery, $csvs['org_level_code'], "org_level_code[]", " id='org_level_code' class='multiselect' "); ?>
                </td>
                <td style="vertical-align: top">
                  <b>Org Type</b><br/>
                  <?php createMultiSelectOptions('org_type', 'org_type_code', 'org_type_name', $customQuery, $csvs['org_type_code'], "org_type_code[]", " id='type_code'  class='multiselect'"); ?>
                  <br/><b>Healthcare Level</b><br/>
                  <?php createMultiSelectOptions('org_healthcare_levels', 'healthcare_code', 'healthcare_name', $customQuery, $csvs['org_healthcare_level_code'], "org_healthcare_level_code[]", " id='org_healthcare_level_code'  class='multiselect'"); ?>
                </td>
                <td style="vertical-align: top">
                  <b>Location Type</b><br/>
                  <?php createMultiSelectOptions('org_location_type', 'org_location_type_code', 'org_location_type_name', $customQuery, $csvs['org_location_type'], "org_location_type[]", " id='org_location_type'  class='multiselect'"); ?>
                  <br/><b>Ownership</b><br/>
                  <?php createMultiSelectOptions('org_ownership_authority', 'org_ownership_authority_code', 'org_ownership_authority_name', $customQuery, $csvs['ownership_code'], "ownership_code[]", " id='ownership_code'  class='multiselect'"); ?>
                  <br/><b>Waste disposal</b><br/>
                  <?php createMultiSelectOptions('org_waste_disposal_system', 'waste_disposal_system_code', 'waste_disposal_system_name', $customQuery, $csvs['waste_disposal_code'], "waste_disposal_code[]", " id='waste_disposal_code'  class='multiselect'"); ?>
                </td>

                <td style="vertical-align: top">
                  <b>Main Electricity</b><br/>
                  <?php createMultiSelectOptions('org_source_of_electricity_main', 'electricity_source_code', 'electricity_source_name', $customQuery, $csvs['source_of_electricity_main_code'], "source_of_electricity_main_code[]", " id='source_of_electricity_main_code'  class='multiselect'"); ?>
                  <br/><b>Alternate Electricity</b><br/>
                  <?php createMultiSelectOptions('org_source_of_electricity_alternate', 'electricity_source_code', 'electricity_source_name', $customQuery, $csvs['source_of_electricity_alternate_code'], "source_of_electricity_alternate_code[]", " id='source_of_electricity_alternate_code'  class='multiselect'"); ?>
                  <br/><b>Toilet type</b><br/>
                  <?php createMultiSelectOptions('org_toilet_type', 'toilet_type_code', 'toilet_type_name', $customQuery, $csvs['toilet_type_code'], "toilet_type_code[]", " id='toilet_type_code'  class='multiselect'"); ?>
                </td>
                <td style="vertical-align: top">
                  <b>Main water</b><br/>
                  <?php createMultiSelectOptions('org_source_of_water_supply_main', 'water_supply_source_code', 'water_supply_source_name', $customQuery, $csvs['source_of_water_supply_main_code'], "source_of_water_supply_main_code[]", " id='source_of_water_supply_main_code'  class='multiselect'"); ?>
                  <br/>
                  <b>Alternate water</b><br/>
                  <?php createMultiSelectOptions('org_source_of_water_supply_alternate', 'water_supply_source_code', 'water_supply_source_name', $customQuery, $csvs['source_of_water_supply_alternate_code'], "source_of_water_supply_alternate_code[]", " id='source_of_water_supply_alternate_code'  class='multiselect'"); ?>
                  <br/><b>Toilet Adequacy</b><br/>
                  <?php createMultiSelectOptions('org_toilet_adequacy', 'toilet_adequacy_code', 'toilet_adequacy_name', $customQuery, $csvs['source_of_water_supply_alternate_code'], "source_of_water_supply_alternate_code[]", " id='source_of_water_supply_alternate_code'  class='multiselect'"); ?>
                </td>

                <td style="vertical-align: top">
                  <b>Fuel Source</b><br/>
                  <?php createMultiSelectOptions('org_fuel_source', 'fuel_source_code', 'fuel_source_name', $customQuery, $csvs['fuel_source_code'], "fuel_source_code[]", " id='fuel_source_code'  class='multiselect'"); ?>
                  <br/><b>Laundry code</b><br/>
                  <?php createMultiSelectOptions('org_laundry_system', 'laundry_system_code', 'laundry_system_name', $customQuery, $csvs['laundry_code'], "laundry_code[]", " id='laundry_code'  class='multiselect'"); ?>
                  <br/><b>Autoclave</b><br/>
                  <?php createMultiSelectOptions('org_autoclave_system', 'autoclave_system_code', 'autoclave_system_name', $customQuery, $csvs['autoclave_code'], "autoclave_code[]", " id='autoclave_code'  class='multiselect'"); ?>
                </td>

                <td style="vertical-align:top;">
                  <b>View Columns</b><br/>
                  <?php
                  createMultiSelectOptions("INFORMATION_SCHEMA.COLUMNS", "COLUMN_NAME", "COLUMN_NAME", "WHERE TABLE_SCHEMA = '$dbname' AND TABLE_NAME = 'organization'", $showFieldsCsv, "f[]", " class='multiselect' ");
                  ?>
                  <br/><b><i class="icon-list-ul"></i> Field query</b><br/>
                  <table>
                    <tr>
                      <td><b>Field</b></td>
                      <td><?php createSelectOptions('INFORMATION_SCHEMA.COLUMNS', 'COLUMN_NAME', 'COLUMN_NAME', "WHERE TABLE_SCHEMA = '$dbname' AND TABLE_NAME = 'organization'", $_REQUEST['search_field'], "search_field", " id='search_field'  class='pull-left' ", $optionIdField) ?></td>
                    </tr>
                    <tr>
                      <td><b>Criteria</b></td>
                      <td><?php
                        $listArray = array('=', 'LIKE', '>', ">=", "<", "<=");
                        createSelectOptionsFrmArray($listArray, $_REQUEST['search_criteria'], 'search_criteria', $params = "");
                        ?>
                      </td>
                    </tr>
                    <tr>
                      <td><b>Value</b></td>
                      <td><input class='' name="search_value" style="border: 1px solid #CCCCCC; height: 15px; width: 142px;" value="<?php echo addEditInputField('search_value'); ?>" /></td>
                    </tr>
                  </table>
                </td>
                <td style="vertical-align:top">
                  <b>Additional SQL select criteria</b>
                  <input name="SQLSelect" value="<?php echo addEditInputField('SQLSelect'); ?>" style="border: 1px solid #CCCCCC; height: 15px; width: 142px;"/>
                  <b>Group by</b> (csv)<br/>
                  <input name="SQLGroup" value="<?php echo addEditInputField('SQLGroup'); ?>" style="border: 1px solid #CCCCCC; height: 15px; width: 142px;"/>
                  <b>Column order</b> (csv)<br/>
                  <input name="ColOrder" value="<?php echo addEditInputField('ColOrder'); ?>" style="border: 1px solid #CCCCCC; height: 15px; width: 142px;"/>
                  <br/>
                  <b>Order by</b><br/>
                  <?php
                  createSelectOptions('INFORMATION_SCHEMA.COLUMNS', 'COLUMN_NAME', 'COLUMN_NAME', "WHERE TABLE_SCHEMA = '$dbname' AND TABLE_NAME = 'organization'", $_REQUEST['order_by'], "order_by", " id='order_by'  class='pull-left' ", $optionIdField);

                  $listArray = array('ASC', 'DESC');
                  createSelectOptionsFrmArray($listArray, $_REQUEST['order_sort'], 'order_sort', $params = "");
                  ?>

                </td>
              </tr>
            </table>
            <table>
              <tr>
                <td>
                  <div class="btn-group">
                    <button name="submit" type="submit" class="btn btn-success" style="text-transform: uppercase">Generate Report</button>
                    <a href="<?php echo $_SERVER['PHP_SELF'] ?>" class="btn" style="text-transform: uppercase">Reset</a>
                    <a id="loading_content" href="#" class="btn btn-info disabled" style="display:none;text-transform: uppercase"><i class="icon-spinner icon-spin icon-large"></i> Loading content...</a>
                  </div>
                </td>
                <td>
                  <?php
                  $checked = "";
                  if ($_REQUEST['show_sql'] == 'true') {
                    $checked = " checked='checked' ";
                  }
                  ?>
                  <input type="checkbox" name="show_sql" value="true" <?= $checked ?>/> Show SQL query
                </td>
                <td>
                  <?php
                  $checked = "";
                  if ($_REQUEST['highlight_empty_cell'] == 'true') {
                    $checked = " checked='checked' ";
                  }
                  ?>
                  <input type="checkbox" name="highlight_empty_cell" value="true" <?= $checked ?>/>Highlight empty cell
                </td>
                <td>
                  <?php
                  $checked = "";
                  if ($_REQUEST['tableheader_without_underscore'] == 'true') {
                    $checked = " checked='checked' ";
                  }
                  ?>
                  <input type="checkbox" name="tableheader_without_underscore" value="true" <?= $checked ?>/>Remove '_' from table header
                  <?php
                  $checked = "";
                  if ($_REQUEST['HideFilter'] == 'true') {
                    $checked = " checked='checked' ";
                  }
                  ?>
                  <input type="checkbox" name="HideFilter" value="true" <?= $checked ?>/>Hide filters
                </td>
              </tr>
            </table>
          </form>
        </div>
        <?php
        if (strlen($sql) && $_REQUEST['show_sql'] == 'true') {
          echo "<pre class='pull-left'>$sql</pre>";
        }
      }
      if (isset($_REQUEST['submit'])) {
        ?>
        <blockquote class="pull-left"><?php echo "$selection_string"; ?></blockquote>
        <blockquote class="pull-left">
          Total <strong><em><?= $count ?></em></strong> organization found.<br />
        </blockquote>

        <?php
        if ($_REQUEST['noDatatable'] == 'true') {
          $param = "";
        } else {
          $param = " id='datatable' ";
        }
        ?>
        <table class="table table-condensed table-bordered" <?= $param ?>>
          <thead>
            <tr>
              <?php
              foreach ($showFields as $fieldName) {
                if (in_array($fieldName, $TableFields)) {
                  ?>
                  <td id="<?= $fieldName ?>"><strong><a href="#" title="<?= $fieldName ?>">
                        <?php
                        if (strlen($fieldNameAlias[$fieldName])) {
                          if ($replaceUnderScoreWithSpace) {
                            $fieldName = str_replace('_', ' ', $fieldNameAlias[$fieldName]);
                          } else {
                            $fieldName = $fieldNameAlias[$fieldName];
                          }
                        } else {
                          if ($replaceUnderScoreWithSpace) {
                            $fieldName = str_replace('_', ' ', $fieldName);
                          }
                        }
                        echo $fieldName;
                        ?>
                      </a>
                    </strong>
                  </td>
                  <?php
                }
              }
              if (strlen($countField)) {
                echo "<td><b>Total</b></td>";
              }
              ?>

            </tr>
          </thead>
          <tbody>
            <?php
            $subTotal = 0;
            while ($data = mysql_fetch_assoc($result)) {
              ?>
              <tr>
                <?php
                $totalFields = count($showFields);
                $filledFields = 0;
                foreach ($showFields as $fieldName) {
                  if (in_array($fieldName, $TableFields)) {
                    if ($DBvalidation == TRUE) {
                      $tdClass = "";
                      if (!strlen($data[$fieldName])) {
                        $filledFields++;
                        $tdClass = "bgRed";
                      } else {

                        //  }
                      }
                    }
                    ?>
                    <td class="<?= $tdClass ?>"><?php echo $data[$fieldName]; ?></td>
                    <?php
                  }
                }
                if (strlen($countField)) {
                  $subTotal+=$data['total'];
                  echo "<td>" . $data['total'] . "</td>";
                }
                ?>

              </tr>
            <?php } ?>
          </tbody>
        </table>
        <?php
      }
      if (strlen($countField)) {
        echo "<h4 class='pull-right'>SUBTOTAL " . $subTotal . "</h4><br/>";
      }
      ?>
    </div>

    <!-- Footer
    ================================================== -->
    <?php include_once 'include/footer/footer.inc.php'; ?>
    <?php include_once 'include/report/report_org_list/report_org_list.js.php'; ?>
  </body>
</html>
<?php

function assignAliasForDbFieldNames() {
  global $fieldNameAlias;
  $fieldNameAlias['id'] = 'id';
  $fieldNameAlias['org_name'] = 'Organization name';
  $fieldNameAlias['org_code'] = 'org_code';
  $fieldNameAlias['org_type_code'] = 'org_type_code';
  $fieldNameAlias['org_type_name'] = 'org_type_name';
  $fieldNameAlias['agency_code'] = 'agency_code';
  $fieldNameAlias['agency_name'] = 'agency_name';
  $fieldNameAlias['org_function_code'] = 'org_function_code';
  $fieldNameAlias['org_level_code'] = 'org_level_code';
  $fieldNameAlias['org_level_name'] = 'org_level_name';
  $fieldNameAlias['org_healthcare_level_code'] = 'org_healthcare_level_code';
  $fieldNameAlias['special_service_code'] = 'special_service_code';
  $fieldNameAlias['year_established'] = 'year_established';
  $fieldNameAlias['org_location_type'] = 'org_location_type';
  $fieldNameAlias['division_code'] = 'division_code';
  $fieldNameAlias['division_name'] = 'division_name';
  $fieldNameAlias['division_id'] = 'division_id';
  $fieldNameAlias['district_code'] = 'district_code';
  $fieldNameAlias['district_name'] = 'district_name';
  $fieldNameAlias['district_id'] = 'district_id';
  $fieldNameAlias['upazila_thana_code'] = 'upazila_thana_code';
  $fieldNameAlias['upazila_thana_name'] = 'upazila_thana_name';
  $fieldNameAlias['upazila_id'] = 'upazila_id';
  $fieldNameAlias['union_code'] = 'union_code';
  $fieldNameAlias['union_name'] = 'union_name';
  $fieldNameAlias['union_id'] = 'union_id';
  $fieldNameAlias['ward_code'] = 'ward_code';
  $fieldNameAlias['village_code'] = 'village_code';
  $fieldNameAlias['house_number'] = 'house_number';
  $fieldNameAlias['latitude'] = 'latitude';
  $fieldNameAlias['longitude'] = 'longitude';
  $fieldNameAlias['org_photo'] = 'org_photo';
  $fieldNameAlias['financial_revenue_code'] = 'financial_revenue_code';
  $fieldNameAlias['ownership_code'] = 'ownership_code';
  $fieldNameAlias['mailing_address'] = 'mailing_address';
  $fieldNameAlias['land_phone1'] = 'land_phone1';
  $fieldNameAlias['land_phone2'] = 'land_phone2';
  $fieldNameAlias['land_phone3'] = 'land_phone3';
  $fieldNameAlias['mobile_number1'] = 'mobile_number1';
  $fieldNameAlias['mobile_number2'] = 'mobile_number2';
  $fieldNameAlias['mobile_number3'] = 'mobile_number3';
  $fieldNameAlias['email_address1'] = 'email_address1';
  $fieldNameAlias['email_address2'] = 'email_address2';
  $fieldNameAlias['email_address3'] = 'email_address3';
  $fieldNameAlias['fax_number1'] = 'fax_number1';
  $fieldNameAlias['fax_number2'] = 'fax_number2';
  $fieldNameAlias['fax_number3'] = 'fax_number3';
  $fieldNameAlias['website_address'] = 'website_address';
  $fieldNameAlias['facebook_page'] = 'facebook_page';
  $fieldNameAlias['google_plus_page'] = 'google_plus_page';
  $fieldNameAlias['twitter_page'] = 'twitter_page';
  $fieldNameAlias['youtube_page'] = 'youtube_page';
  $fieldNameAlias['source_of_electricity_main_code'] = 'source_of_electricity_main_code';
  $fieldNameAlias['source_of_electricity_alternate_code'] = 'source_of_electricity_alternate_code';
  $fieldNameAlias['source_of_water_supply_main_code'] = 'source_of_water_supply_main_code';
  $fieldNameAlias['source_of_water_supply_alternate_code'] = 'source_of_water_supply_alternate_code';
  $fieldNameAlias['toilet_type_code'] = 'toilet_type_code';
  $fieldNameAlias['toilet_adequacy_code'] = 'toilet_adequacy_code';
  $fieldNameAlias['fuel_source_code'] = 'fuel_source_code';
  $fieldNameAlias['laundry_code'] = 'laundry_code';
  $fieldNameAlias['autoclave_code'] = 'autoclave_code';
  $fieldNameAlias['waste_disposal_code'] = 'waste_disposal_code';
  $fieldNameAlias['sanctioned_office_equipment'] = 'sanctioned_office_equipment';
  $fieldNameAlias['sanctioned_vehicles'] = 'sanctioned_vehicles';
  $fieldNameAlias['sanctioned_bed_number'] = 'sanctioned_bed_number';
  $fieldNameAlias['other_miscellaneous_issues'] = 'other_miscellaneous_issues';
  $fieldNameAlias['permission_approval_license_info_code'] = 'permission_approval_license_info_code';
  $fieldNameAlias['permission_approval_license_info_date'] = 'permission_approval_license_info_date';
  $fieldNameAlias['permission_approval_license_type'] = 'permission_approval_license_type';
  $fieldNameAlias['permission_approval_license_aithority'] = 'permission_approval_license_aithority';
  $fieldNameAlias['permission_approval_license_number'] = 'permission_approval_license_number';
  $fieldNameAlias['permission_approval_license_next_renewal_date'] = 'permission_approval_license_next_renewal_date';
  $fieldNameAlias['permission_approval_license_conditions'] = 'permission_approval_license_conditions';
  $fieldNameAlias['land_info_code'] = 'land_info_code';
  $fieldNameAlias['land_size'] = 'land_size';
  $fieldNameAlias['land_mouza_name'] = 'land_mouza_name';
  $fieldNameAlias['land_mouza_geo_code'] = 'land_mouza_geo_code';
  $fieldNameAlias['land_jl_number'] = 'land_jl_number';
  $fieldNameAlias['land_functional_code'] = 'land_functional_code';
  $fieldNameAlias['land_rs_dag_number'] = 'land_rs_dag_number';
  $fieldNameAlias['land_ss_dag_number'] = 'land_ss_dag_number';
  $fieldNameAlias['land_kharian_number'] = 'land_kharian_number';
  $fieldNameAlias['land_other_info'] = 'land_other_info';
  $fieldNameAlias['land_mutation_number'] = 'land_mutation_number';
  $fieldNameAlias['additional_chcp_name'] = 'additional_chcp_name';
  $fieldNameAlias['additional_chcp_contact'] = 'additional_chcp_contact';
  $fieldNameAlias['additional_community_group_info'] = 'additional_community_group_info';
  $fieldNameAlias['additional_chairnam_name'] = 'additional_chairnam_name';
  $fieldNameAlias['additional_chairman_contact'] = 'additional_chairman_contact';
  $fieldNameAlias['additional_chairman_community_support_info'] = 'additional_chairman_community_support_info';
  $fieldNameAlias['additional_csg_1_name'] = 'additional_csg_1_name';
  $fieldNameAlias['additional_csg_1_contact'] = 'additional_csg_1_contact';
  $fieldNameAlias['additional_csg_2_name'] = 'additional_csg_2_name';
  $fieldNameAlias['additional_csg_2_contact'] = 'additional_csg_2_contact';
  $fieldNameAlias['org_functions'] = 'org_functions';
  $fieldNameAlias['uploaded_file'] = 'uploaded_file';
  $fieldNameAlias['updated_by'] = 'updated_by';
  $fieldNameAlias['active'] = 'active';
  $fieldNameAlias['updated_datetime'] = 'updated_datetime';
  $fieldNameAlias['organization_id'] = 'organization_id';
  $fieldNameAlias['monthly_update'] = 'monthly_update';
  $fieldNameAlias['monthly_update_datetime'] = 'monthly_update_datetime';
  $fieldNameAlias['upload_datetime'] = 'upload_datetime';
  $fieldNameAlias['uploaded_by'] = 'uploaded_by';
}
?>