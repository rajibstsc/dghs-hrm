<?php
set_time_limit(120000);

require_once 'configuration.php';

if ($_SESSION['logged'] != true) {
    header("location:login.php");
}

// assign values from session array
$org_code = $_SESSION['org_code'];
$org_name = $_SESSION['org_name'];
$org_type_name = $_SESSION['org_type_name'];

$echoAdminInfo = "";

// assign values admin users
if ($_SESSION['user_type'] == "admin" && $_GET['org_code'] != "") {
    $org_code = (int) mysql_real_escape_string($_GET['org_code']);
    $org_name = getOrgNameFormOrgCode($org_code);
    $org_type_name = getOrgTypeNameFormOrgCode($org_code);
    $echoAdminInfo = " | Administrator";
    $isAdmin = TRUE;
}

//print_r($_REQUEST);
$div_id = (int) mysql_real_escape_string(trim($_REQUEST['admin_division']));
$dis_id = (int) mysql_real_escape_string(trim($_REQUEST['admin_district']));
$upa_id = (int) mysql_real_escape_string(trim($_REQUEST['admin_upazila']));
$agency_code = (int) mysql_real_escape_string(trim($_REQUEST['org_agency']));
$type_code = (int) mysql_real_escape_string(trim($_REQUEST['org_type']));
$form_submit = (int) mysql_real_escape_string(trim($_REQUEST['form_submit']));
$staff_category = (int) mysql_real_escape_string(trim($_REQUEST['staff_category']));


if ($form_submit == 1 && isset($_REQUEST['form_submit'])) {

    /*
     * 
     * query builder to get the organizatino list
     */
    $query_string = "";
    if ($div_id > 0 || $dis_id > 0 || $upa_id > 0 || $agency_code > 0 || $type_code > 0) {
        $query_string .= " WHERE ";

        if ($agency_code > 0) {
            $query_string .= "organization.agency_code = $agency_code";
        }
        if ($upa_id > 0) {
            if ($agency_code > 0) {
                $query_string .= " AND ";
            }
            $query_string .= "organization.upazila_id = $upa_id";
        }
        if ($dis_id > 0) {
            if ($upa_id > 0 || $agency_code > 0) {
                $query_string .= " AND ";
            }
            $query_string .= "organization.district_id = $dis_id";
        }
        if ($div_id > 0) {
            if ($dis_id > 0 || $upa_id > 0 || $agency_code > 0) {
                $query_string .= " AND ";
            }
            $query_string .= "organization.division_id = $div_id";
        }
        if ($type_code > 0) {
            if ($div_id > 0 || $dis_id > 0 || $upa_id > 0 || $agency_code > 0) {
                $query_string .= " AND ";
            }
            $query_string .= "organization.org_type_code = $type_code";
        }
    }

    $query_string .= " ORDER BY org_name";

    $sql = "SELECT organization.org_name, organization.org_code FROM organization $query_string";
    $org_list_result = mysql_query($sql) or die(mysql_error() . "<br /><br />Code:<b>get_org_list:1</b><br /><br /><b>Query:</b><br />___<br />$sql<br />");
//    echo "$sql";

    /*     * *
     * 
     * get the sanctioned post count
     */
    $desognation_query_string = "";
    $data = mysql_fetch_assoc($org_list_result);
    $data_count = mysql_num_rows($org_list_result);
    $desognation_query_string .= " total_manpower_imported_sanctioned_post_copy.org_code = " . $data['org_code'];
    if ($staff_category > 0) {
        $desognation_query_string .= " AND  total_manpower_imported_sanctioned_post_copy.bangladesh_professional_category_code = $staff_category";
    }

    while ($data = mysql_fetch_assoc($org_list_result)) {
        $desognation_query_string .= " OR total_manpower_imported_sanctioned_post_copy.org_code = '" . $data['org_code'] . "'";
    }

    $sql = "SELECT
                total_manpower_imported_sanctioned_post_copy.id,
                total_manpower_imported_sanctioned_post_copy.designation,
                total_manpower_imported_sanctioned_post_copy.designation_code,
                total_manpower_imported_sanctioned_post_copy.type_of_post,
                sanctioned_post_designation.class,
                sanctioned_post_designation.payscale,
                COUNT(*) AS sp_count 
        FROM
                total_manpower_imported_sanctioned_post_copy
        LEFT JOIN `sanctioned_post_designation` ON total_manpower_imported_sanctioned_post_copy.designation_code = sanctioned_post_designation.designation_code
        WHERE
                $desognation_query_string
                AND total_manpower_imported_sanctioned_post_copy.active LIKE 1    
        GROUP BY 
                total_manpower_imported_sanctioned_post_copy.designation
        ORDER BY
                sanctioned_post_designation.ranking";
    
    $total_sanctioned_post = 0;
    if ($data_count > 0) {
        $designation_result = mysql_query($sql) or die(mysql_error() . "<br /><br />Code:<b>sql:2</b><br /><br /><b>Query:</b><br />___<br />$sql<br />");
        $total_sanctioned_post = mysql_num_rows($designation_result);
    }

    
    $total_sanctioned_post_count_sum = 0;
    $total_sanctioned_post_existing_sum = 0;
    $total_existing_male_sum = 0;
    $total_existing_female_sum = 0;
//    echo "$sql";
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title><?php echo $org_name . " Report | " . $app_name; ?></title>
        <?php
        include_once 'include/header/header_css_js.inc.php';
        include_once 'include/header/header_ga.inc.php';
        ?>
    </head>

    <body>

        <!-- Top navigation bar
        ================================================== -->
        <?php include_once 'include/header/header_top_menu.inc.php'; ?>

        <!-- Subhead
        ================================================== -->
        <header class="jumbotron subhead" id="overview">
            <div class="container">
                <h1><?php echo $org_name . $echoAdminInfo; ?></h1>
                <p class="lead"><?php echo "$org_type_name"; ?></p>
            </div>
        </header>


        <div class="container">

            <!-- Docs nav
            ================================================== -->
            <div class="row">
                <div class="span3 bs-docs-sidebar">
                    <ul class="nav nav-list bs-docs-sidenav">
                        <?php
                        $active_menu = "";
                        include_once 'include/left_menu.php';
                        ?>
                    </ul>
                </div>
                <div class="span9">
                    <!-- info area
                    ================================================== -->
                    <section id="report">

                        <div class="row">
                            <div class="">
                                <form class="form-horizontal" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="get">
                                    <p class="lead">Summary Report Includes All Organization</p>
                                    <div class="control-group">
                                        <select id="org_agency" name="org_agency">
                                            <option value="0">Select Agency</option>
                                            <?php
                                            $sql = "SELECT
                                                    org_agency_code.org_agency_code,
                                                    org_agency_code.org_agency_name
                                                FROM
                                                    org_agency_code
                                                ORDER BY
                                                    org_agency_code.org_agency_code";
                                            $result = mysql_query($sql) or die(mysql_error() . "<br /><br />Code:<b>loadorg_agency:1</b><br /><br /><b>Query:</b><br />___<br />$sql<br />");

                                            while ($rows = mysql_fetch_assoc($result)) {
												if ($rows['org_agency_code'] == $_REQUEST['org_agency'])
       												 echo "<option value=\"" . $rows['org_agency_code'] . "\" selected='selected'>" . $rows['org_agency_name'] . "</option>";
											    else
                                                echo "<option value=\"" . $rows['org_agency_code'] . "\">" . $rows['org_agency_name'] . "</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="control-group">
                                        <select id="admin_division" name="admin_division">
                                            <option value="0">Select Division</option>
                                            <?php
                                            /**
                                             * @todo change old_visision_id to division_bbs_code
                                             */
                                            $sql = "SELECT admin_division.division_name, admin_division.old_division_id FROM admin_division";
                                            $result = mysql_query($sql) or die(mysql_error() . "<br /><br />Code:<b>loadDivision:1</b><br /><br /><b>Query:</b><br />___<br />$sql<br />");

                                            while ($rows = mysql_fetch_assoc($result)) {
												if ($rows['old_division_id'] == $_REQUEST['admin_division'])
       												 echo "<option value=\"" . $rows['old_division_id'] . "\" selected='selected'>" . $rows['division_name'] . "</option>";
											    else
                                                echo "<option value=\"" . $rows['old_division_id'] . "\">" . $rows['division_name'] . "</option>";
                                            }
                                            ?>
                                        </select>
                                        <select id="admin_district" name="admin_district">
                                         <option value="0">Select District</option>
                                            <?php 

                                                    $sql = "SELECT 
                                                              admin_district.district_bbs_code,
                                                              admin_district.old_district_id,
                                                              admin_district.district_name
                                                      FROM
                                                              admin_district
                                                      WHERE
                                                              admin_district.division_id =$div_id
                                                      ORDER BY
                                                              admin_district.district_name";
                                      $result = mysql_query($sql) or die(mysql_error() . "<br /><br />Code:<b>get_district_list:1</b><br /><br /><b>Query:</b><br />___<br />$sql<br />");
                                              while ($rows = mysql_fetch_assoc($result)) {
                                                            if ($rows['old_district_id'] == $_REQUEST['admin_district'])
                                                             echo "<option value=\"" . $rows['old_district_id'] . "\" selected='selected'>" . $rows['district_name'] . "</option>";
                                                        else
                                                     echo "<option value=\"" . $rows['old_district_id'] . "\">" . $rows['district_name'] . "</option>";
                                            }
											
										?>
                                        </select>
<!--                                        <select id="admin_district" name="admin_district">
                                            <option value="0">Select District</option>                             
                                        </select>-->
                                        
                                        
                                        <select id="admin_upazila" name="admin_upazila">
                                         <option value="0">Select Upazila</option>
										<?php 
										    
											$sql = "SELECT
													admin_upazila.upazila_name,
													admin_upazila.old_upazila_id
												FROM
													admin_upazila
												WHERE
													admin_upazila.old_district_id = $dis_id
												ORDER BY
													admin_upazila.upazila_name";
									  $result = mysql_query($sql) or die(mysql_error() . "<br /><br />Code:<b>get_dupazila_list:1</b><br /><br /><b>Query:</b><br />___<br />$sql<br />");
										  while ($rows = mysql_fetch_assoc($result)) {
												if ($rows['old_upazila_id'] == $_REQUEST['admin_upazila'])
       												 echo "<option value=\"" . $rows['old_upazila_id'] . "\" selected='selected'>" . $rows['upazila_name'] . "</option>";
											    else
                                                     echo "<option value=\"" . $rows['old_upazila_id'] . "\">" . $rows['upazila_name'] . "</option>";
                                            }
											
										?>
                                        </select>
                                        
                                        <!--<select id="admin_upazila" name="admin_upazila">
                                            <option value="0">Select Upazila</option>                                        
                                        </select>-->

                                    </div>


                                    <div class="control-group">

                                        <select id="org_type" name="org_type">
                                            <option value="0">Select Org Type</option>
                                            <?php
                                            $sql = "SELECT
                                                            org_type.org_type_code,
                                                            org_type.org_type_name
                                                        FROM
                                                            org_type
                                                        ORDER BY
                                                            org_type.org_type_name ASC";
                                            $result = mysql_query($sql) or die(mysql_error() . "<br /><br />Code:<b>loadorg_type:1</b><br /><br /><b>Query:</b><br />___<br />$sql<br />");

                                            while ($rows = mysql_fetch_assoc($result)) {
												if($rows['org_type_code'] == $_REQUEST['org_type'])
												echo "<option value=\"" . $rows['org_type_code'] . "\" selected='selected'>" . $rows['org_type_name'] . "</option>";
												else
                                                echo "<option value=\"" . $rows['org_type_code'] . "\">" . $rows['org_type_name'] . "</option>";
                                            }
                                            ?>
                                        </select>

                                        <select id="staff_category" name="staff_category">
                                            <option value="0">Select Staff Category</option>
                                            <?php
                                            $sql = "SELECT
                                                            bangladesh_professional_category_code,
                                                            bangladesh_professional_category_name
                                                    FROM
                                                            `sanctioned_post_bangladesh_professional_category`
                                                    WHERE
                                                            active LIKE 1;";
                                            $result = mysql_query($sql) or die(mysql_error() . "<br /><br />Code:<b>bangladesh_professional_category:1</b><br /><br /><b>Query:</b><br />___<br />$sql<br />");

                                            while ($rows = mysql_fetch_assoc($result)) {
												if($rows['bangladesh_professional_category_code'] == $_REQUEST['staff_category'])
												echo "<option value=\"" . $rows['who_health_professional_group_code'] . "\" selected='selected'>" . $rows['bangladesh_professional_category_name'] . "</option>";
												else
                                                echo "<option value=\"" . $rows['bangladesh_professional_category_code'] . "\">" . $rows['bangladesh_professional_category_name'] . "</option>";
                                            }
                                            ?>
                                        </select>

                                    </div>
                                    <input name="form_submit" value="1" type="hidden" />
                                    <div class="control-group">
                                        <button id="btn_show_org_list" type="submit" class="btn btn-info">Show Report</button>

                                        <a id="loading_content" href="#" class="btn btn-info disabled" style="display:none;"><i class="icon-spinner icon-spin icon-large"></i> Loading content...</a>
                                    </div>  
                                </form>
                            </div>

                            <?php if ($form_submit == 1 && isset($_REQUEST['form_submit'])) : ?>
                                <div id="result_display">
                                    <div class="alert alert-success" id="generate_report">
                                        <i class="icon-cog icon-spin icon-large"></i> <strong>Generating report...</strong>
                                    </div>
                                    <div class="alert alert-info">
                                        Selected Parameters are:<br>
                                        <?php
                                        $echo_string = "";
                                        if ($div_id > 0) {
                                            $echo_string .= " Division: <strong>" . getDivisionNamefromCode(getDivisionCodeFormId($div_id)) . "</strong><br>";
                                        }
                                        if ($dis_id > 0) {
                                            $echo_string .= " District: <strong>" . getDistrictNamefromCode(getDistrictCodeFormId($dis_id)) . "</strong><br>";
                                        }
                                        if ($upa_id > 0) {
                                            $echo_string .= " Upazila: <strong>" . getUpazilaNamefromBBSCode(getUpazilaCodeFormId($upa_id), getDistrictCodeFormId($dis_id)) . "</strong><br>";
                                        }
                                        if ($agency_code > 0) {
                                            $echo_string .= " Agency: <strong>" . getAgencyNameFromAgencyCode($agency_code) . "</strong><br>";
                                        }
                                        if ($type_code > 0) {
                                            $echo_string .= " Org Type: <strong>" . getOrgTypeNameFormOrgTypeCode($type_code) . "</strong><br>";
                                        }
                                        if ($staff_category > 0) {
                                            $echo_string .= " Bangladesh Professional Staff Category: <strong>" . getBangladeshProfessionalStaffCategoryFromCode($staff_category) . "</strong><br>";
                                        }
                                        echo "$echo_string";
                                        ?>
                                    </div>
                    <input type="button" onclick="tableToExcel('testTable', 'W3C Example Table')" value="Export to Excel" class="btn btn-primary">
                                        <br/>
                                        <table class="table table-striped table-bordered" id="testTable">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Designation</th>
                                                <th>Type of Post</th>
                                                <th>Class</th>
                                                <th>Pay Scale</th>
                                                <th>Total Post(s)</th>
                                                <th>Filled up Post(s)</th>
                                                <th>Total Male</th>
                                                <th>Total Female</th>
                                                <th>Vacant Post(s)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $row_serial = 0;
                                            if ($total_sanctioned_post > 0):

                                                while ($row = mysql_fetch_assoc($designation_result)) :
                                                    $row_serial++;
                                                    $sql = "SELECT
                                                                    designation,
                                                                    designation_code,
                                                                    COUNT(*) AS existing_total_count
                                                            FROM
                                                                    total_manpower_imported_sanctioned_post_copy
                                                            WHERE
                                                                    ($desognation_query_string)
                                                            AND designation_code = " . $row['designation_code'] . "
                                                            AND staff_id_2 > 0";
//                                                echo "$sql";
//                                                die();
                                                    $r = mysql_query($sql) or die(mysql_error() . "<br /><br />Code:<b>sql:3</b><br /><br /><b>Query:</b><br />___<br />$sql<br />");
                                                    $a = mysql_fetch_assoc($r);
                                                    $existing_total_count = $a['existing_total_count'];

                                                    $sql = "SELECT
                                                                    total_manpower_imported_sanctioned_post_copy.designation,
                                                                    total_manpower_imported_sanctioned_post_copy.designation_code,
                                                                    COUNT(*) AS existing_male_count
                                                            FROM
                                                                    total_manpower_imported_sanctioned_post_copy
                                                            LEFT JOIN old_tbl_staff_organization ON old_tbl_staff_organization.staff_id = total_manpower_imported_sanctioned_post_copy.staff_id_2
                                                            WHERE
                                                                    ($desognation_query_string) 
                                                            AND total_manpower_imported_sanctioned_post_copy.designation_code = " . $row['designation_code'] . "
                                                            AND total_manpower_imported_sanctioned_post_copy.staff_id_2 > 0
                                                            AND old_tbl_staff_organization.sex=1
                                                            AND total_manpower_imported_sanctioned_post_copy.active LIKE 1
                                                            AND old_tbl_staff_organization.active LIKE '1'";
                                                    $r = mysql_query($sql) or die(mysql_error() . "<br /><br />Code:<b>sql:4</b><br /><br /><b>Query:</b><br />___<br />$sql<br />");
                                                    $a = mysql_fetch_assoc($r);
                                                    $existing_male_count = $a['existing_male_count'];

                                                    $existing_female_count = $existing_total_count - $existing_male_count;
                                                    $total_sanctioned_post_count_sum += $row['sp_count'];
                                                    $total_sanctioned_post_existing_sum += $existing_total_count;
                                                    $total_existing_male_sum += $existing_male_count;
                                                    $total_existing_female_sum += $existing_female_count;
                                                    ?>
                                                    <tr>
                                                        <td><?php echo "$row_serial"; ?></td>
                                                        <td><?php echo $row['designation']; ?></td>
                                                        <td><?php echo getTypeOfPostNameFromCode($row['type_of_post']); ?></td>
                                                        <td><?php echo $row['class']; ?></td>
                                                        <td><?php echo $row['payscale']; ?></td>
                                                        <td><?php echo $row['sp_count']; ?></td>
                                                        <td><?php echo $existing_total_count; ?></td>
                                                        <td><?php echo $existing_male_count; ?></td>
                                                        <td><?php echo $existing_female_count; ?></td>
                                                        <td><?php echo $row['sp_count'] - $existing_total_count; ?></td>
                                                    </tr>
                                                <?php endwhile; ?>

                                            <?php endif; ?>    
                                            <tr class="info">

                                                <td colspan="5"><strong>Summary</strong></td>                                                
                                                <td><strong><?php echo $total_sanctioned_post_count_sum; ?></strong></td>
                                                <td><strong><?php echo $total_sanctioned_post_existing_sum; ?></strong></td>
                                                <td><strong><?php echo $total_existing_male_sum; ?></strong></td>
                                                <td><strong><?php echo $total_existing_female_sum; ?></strong></td>
                                                <td><strong><?php echo $total_sanctioned_post_count_sum - $total_sanctioned_post_existing_sum; ?></string></td>
                                            </tr>
                                            <tr>
                                                <td colspan="5"></td>
                                                <td>Total Post(s)</td>
                                                <td>Filled up Post(s)</td>
                                                <td>Total Male</td>
                                                <td>Total Female</td>
                                                <td>Vacant Post(s)</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>

                    </section>

                </div>
            </div>

        </div>



        <!-- Footer
        ================================================== -->
        <?php include_once 'include/footer/footer.inc.php'; ?>

        <script type="text/javascript">
            // load division
            $('#admin_division').change(function() {
                $("#loading_content").show();
                var div_id = $('#admin_division').val();
                $.ajax({
                    type: "POST",
                    url: 'get/get_district_list.php',
                    data: {div_id: div_id},
                    dataType: 'json',
                    success: function(data)
                    {
                        $("#loading_content").hide();
                        var admin_district = document.getElementById('admin_district');
                        admin_district.options.length = 0;
                        for (var i = 0; i < data.length; i++) {
                            var d = data[i];
                            admin_district.options.add(new Option(d.text, d.value));
                        }
                    }
                });
            });

            // load district 
            $('#admin_district').change(function() {
                var dis_id = $('#admin_district').val();
                $("#loading_content").show();
                $.ajax({
                    type: "POST",
                    url: 'get/get_upazila_list.php',
                    data: {dis_id: dis_id},
                    dataType: 'json',
                    success: function(data)
                    {
                        $("#loading_content").hide();
                        var admin_upazila = document.getElementById('admin_upazila');
                        admin_upazila.options.length = 0;
                        for (var i = 0; i < data.length; i++) {
                            var d = data[i];
                            admin_upazila.options.add(new Option(d.text, d.value));
                        }
                    }
                });
            });

            $("#generate_report").hide();
        </script>
                <script type="text/javascript">
		var tableToExcel = (function() {
  var uri = 'data:application/vnd.ms-excel;base64,'
    , template = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40"><head><!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>{worksheet}</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]--></head><body><table>{table}</table></body></html>'
    , base64 = function(s) { return window.btoa(unescape(encodeURIComponent(s))) }
    , format = function(s, c) { return s.replace(/{(\w+)}/g, function(m, p) { return c[p]; }) }
  return function(table, name) {
    if (!table.nodeType) table = document.getElementById(table)
    var ctx = {worksheet: name || 'Worksheet', table: table.innerHTML}
    window.location.href = uri + base64(format(template, ctx))
  }
})()
		</script>
    </body>
</html>
