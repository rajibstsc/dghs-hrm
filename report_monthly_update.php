<?php
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

$div_code = (int) mysql_real_escape_string(trim($_REQUEST['admin_division']));
$dis_code = (int) mysql_real_escape_string(trim($_REQUEST['admin_district']));
$upa_code = (int) mysql_real_escape_string(trim($_REQUEST['admin_upazila']));
$agency_code = (int) mysql_real_escape_string(trim($_REQUEST['org_agency']));
$type_code = (int) mysql_real_escape_string(trim($_REQUEST['org_type']));
$form_submit = (int) mysql_real_escape_string(trim($_REQUEST['form_submit']));
$view = mysql_real_escape_string(trim($_REQUEST['view']));
$status = mysql_real_escape_string(trim($_REQUEST['status']));


date_default_timezone_set('Asia/Dhaka');
$current_month = date("n");
    
    
if ($form_submit == 1 && isset($_REQUEST['form_submit'])) {

    /*
     * 
     * query builder to get the organizatino list
     */
    $query_string = "";
    if ($div_code > 0 || $dis_code > 0 || $upa_code > 0 || $agency_code > 0 || $type_code > 0) {
        $query_string .= " WHERE ";

        if ($agency_code > 0) {
            $query_string .= "organization.agency_code = $agency_code";
        }
        if ($upa_code > 0) {
            if ($agency_code > 0) {
                $query_string .= " AND ";
            }
            $query_string .= "organization.upazila_thana_code = $upa_code";
        }
        if ($dis_code > 0) {
            if ($upa_code > 0 || $agency_code > 0) {
                $query_string .= " AND ";
            }
            $query_string .= "organization.district_code = $dis_code";
        }
        if ($div_code > 0) {
            if ($dis_code > 0 || $upa_code > 0 || $agency_code > 0) {
                $query_string .= " AND ";
            }
            $query_string .= "organization.division_code = $div_code";
        }
        if ($type_code > 0) {
            if ($div_code > 0 || $dis_code > 0 || $upa_code > 0 || $agency_code > 0) {
                $query_string .= " AND ";
            }
            $query_string .= "organization.org_type_code = $type_code";
        }
    }


    


    $result_count = 0;
    if ($view == "org_list") {
        if ($status == "updated") {
            $sql = "SELECT
                    organization.org_name,
                    organization.org_code,
                    organization.division_name,
                    organization.district_name,
                    organization.upazila_thana_name,
                    organization.mobile_number1,
                    organization.email_address1
            FROM
                    organization
            $query_string";
            if ($div_code > 0 || $dis_code > 0 || $upa_code > 0 || $agency_code > 0 || $type_code > 0) {
                $sql .= " AND monthly_update = $current_month AND active LIKE 1 ";
            } else {
                $sql .= "WHERE monthly_update = $current_month AND active LIKE 1 ";
            }
            $org_list_result = mysql_query($sql) or die(mysql_error() . "<br /><br />Code:<b>get_org_type_summary:1</b><br /><br /><b>Query:</b><br />___<br />$sql<br />");
            $result_count = mysql_num_rows($org_list_result);
        } else {
            
            $sql = "SELECT
                    organization.org_name,
                    organization.org_code,
                    organization.division_name,
                    organization.district_name,
                    organization.upazila_thana_name,
                    organization.mobile_number1,
                    organization.email_address1
            FROM
                    organization
            $query_string";
            if ($div_code > 0 || $dis_code > 0 || $upa_code > 0 || $agency_code > 0 || $type_code > 0) {
                $sql .= " AND monthly_update != $current_month  AND active LIKE 1 ";
            } else {
                $sql .= "WHERE monthly_update != $current_month  AND active LIKE 1 ";
            }
            $org_list_result = mysql_query($sql) or die(mysql_error() . "<br /><br />Code:<b>get_org_type_summary:1</b><br /><br /><b>Query:</b><br />___<br />$sql<br />");
            $result_count = mysql_num_rows($org_list_result);
        }
    } else {
        if ($div_code > 0 || $dis_code > 0 || $upa_code > 0 || $agency_code > 0 || $type_code > 0) {
            $query_string .= " AND org_type_code > 0 ";
        } else {
            $query_string .= "WHERE organization.org_type_code > 0 ";
        }

        $sql = "SELECT
                    organization.org_type_name,
                    organization.org_type_code,
                    COUNT(*) AS total_count
            FROM
                    organization
            $query_string
            GROUP BY
                    organization.org_type_code";
        $sql .= " ORDER BY org_type_name";
        $org_type_summary_result = mysql_query($sql) or die(mysql_error() . "<br /><br />Code:<b>get_org_type_summary:1</b><br /><br /><b>Query:</b><br />___<br />$sql<br />");
        $result_count = mysql_num_rows($org_type_summary_result);
    }


//    echo "$sql";


    

    $count_total_org = 0;
    $count_total_org_updated = 0;
    $count_total_org_not_updated = 0;
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title><?php echo $org_name . " | " . $app_name; ?></title>
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
                                    <p class="lead">Monthly Update Summary</p>
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
                                                echo "<option value=\"" . $rows['org_agency_code'] . "\">" . $rows['org_agency_name'] . "</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="control-group">
                                        <select id="admin_division" name="admin_division">
                                            <option value="0">__ Select Division __</option>
                                            <?php
                                            /**
                                             * @todo change old_visision_id to division_bbs_code
                                             */
                                            $sql = "SELECT admin_division.division_name, admin_division.division_bbs_code FROM admin_division";
                                            $result = mysql_query($sql) or die(mysql_error() . "<br /><br />Code:<b>loadDivision:1</b><br /><br /><b>Query:</b><br />___<br />$sql<br />");

                                            while ($rows = mysql_fetch_assoc($result)) {
                                                echo "<option value=\"" . $rows['division_bbs_code'] . "\">" . $rows['division_name'] . "</option>";
                                            }
                                            ?>
                                        </select>
                                        <select id="admin_district" name="admin_district">
                                            <option value="0">__ Select District __</option>                                        
                                        </select>
                                        <select id="admin_upazila" name="admin_upazila">
                                            <option value="0">__ Select Upazila __</option>                                        
                                        </select>
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
                                                echo "<option value=\"" . $rows['org_type_code'] . "\">" . $rows['org_type_name'] . "</option>";
                                            }
                                            ?>
                                        </select>



                                    </div>
                                    <input name="form_submit" value="1" type="hidden" />
                                    <div class="control-group">
                                        <button id="btn_show_org_list" type="submit" class="btn btn-info">Show Report</button>
                                        <a href="report_monthly_update.php" class="btn btn-default" > Reset</a>
                                        <a id="loading_content" href="#" class="btn btn-info disabled" style="display:none;"><i class="icon-spinner icon-spin icon-large"></i> Loading content...</a>
                                    </div>  
                                </form>
                            </div>


                            <?php if ($form_submit == 1 && isset($_REQUEST['form_submit'])) : ?>
                                <?php if (!$result_count > 0): ?>
                                    <div class="alert alert-warning">
                                        <strong><em>No organization found for the following selection.</em></strong><br /><br />
                                        Selected Parameters are:<br>
                                        <?php
                                        $echo_string = "";
                                        if ($div_code > 0) {
                                            $echo_string .= " Division: <strong>" . getDivisionNamefromCode($div_code) . "</strong><br>";
                                        }
                                        if ($dis_code > 0) {
                                            $echo_string .= " District: <strong>" . getDistrictNamefromCode($dis_code) . "</strong><br>";
                                        }
                                        if ($upa_code > 0) {
                                            $echo_string .= " Upazila: <strong>" . getUpazilaNamefromBBSCode($upa_code, $dis_code) . "</strong><br>";
                                        }
                                        if ($agency_code > 0) {
                                            $echo_string .= " Agency: <strong>" . getAgencyNameFromAgencyCode($agency_code) . "</strong><br>";
                                        }
                                        if ($type_code > 0) {
                                            $echo_string .= " Org Type: <strong>" . getOrgTypeNameFormOrgTypeCode($type_code) . "</strong><br>";
                                        }

                                        echo "$echo_string";
                                        ?>
                                    </div>
                                <?php else: ?>
                                    <div class="alert alert-success">
                                        Selected Parameters are:<br>
                                        <?php
                                        $echo_string = "";
                                        if ($div_code > 0) {
                                            $echo_string .= " Division: <strong>" . getDivisionNamefromCode($div_code) . "</strong><br>";
                                        }
                                        if ($dis_code > 0) {
                                            $echo_string .= " District: <strong>" . getDistrictNamefromCode($dis_code) . "</strong><br>";
                                        }
                                        if ($upa_code > 0) {
                                            $echo_string .= " Upazila: <strong>" . getUpazilaNamefromBBSCode($upa_code, $dis_code) . "</strong><br>";
                                        }
                                        if ($agency_code > 0) {
                                            $echo_string .= " Agency: <strong>" . getAgencyNameFromAgencyCode($agency_code) . "</strong><br>";
                                        }
                                        if ($type_code > 0) {
                                            $echo_string .= " Org Type: <strong>" . getOrgTypeNameFormOrgTypeCode($type_code) . "</strong><br>";
                                        }

                                        echo "$echo_string";
                                        ?>
                                    </div>

                                    <?php if ($view == "org_list") : ?>
                                        <table class="table table-striped table-bordered">
                                            <thead>
                                                <tr>
                                                    <td>#</td>
                                                    <td><strong>Organization Name</strong></td>
                                                    <td><strong>Division</strong></td>
                                                    <td><strong>District</strong></td>
                                                    <td><strong>Upazila</strong></td>
                                                    <td><strong>Email</strong></td>
                                                    <td><strong>Contact</strong></td>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $j = 0;
                                                while ($row = mysql_fetch_assoc($org_list_result)):
                                                    $j++;
                                                    ?>
                                                    <tr>
                                                        <td><?php echo $j; ?></td>
                                                        <td><?php echo $row['org_name'] . "(" . $row['org_code'] . ")"; ?></td>
                                                        <td><?php echo $row['division_name']; ?></td>
                                                        <td><?php echo $row['district_name']; ?></td>
                                                        <td><?php echo $row['upazila_thana_name']; ?></td>
                                                        <td><?php echo $row['email_address1']; ?></td>
                                                        <td><?php echo $row['mobile_number1']; ?></td>
                                                    </tr>
                                                <?php endwhile; ?>
                                            </tbody>
                                        </table>

                                    <?php else: ?>
                                        <table class="table table-striped table-bordered">
                                            <thead>
                                                <tr>
                                                    <td><strong>Organization Type</strong></td>
                                                    <td><strong>Total Number</strong></td>
                                                    <td><strong>Updated HRM Data in Current Month</strong></td>
                                                    <td><strong>Did not updated HRM Data in Current Month</strong></td>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php while ($row = mysql_fetch_assoc($org_type_summary_result)): ?>
                                                    <tr>
                                                        <td><?php echo $row['org_type_name']; ?></td>
                                                        <td><?php echo $row['total_count']; ?></td>
                                                        <?php
                                                        $sql = "SELECT
                                                                    org_type_name,
                                                                    org_type_code
                                                            FROM
                                                                    organization
                                                            WHERE
                                                                    org_type_code = " . $row['org_type_code'] . "
                                                            AND monthly_update = $current_month";
                                                        $result = mysql_query($sql) or die(mysql_error() . "<br /><br />Code:<b>loadorg_type:1</b><br /><br /><b>Query:</b><br />___<br />$sql<br />");
                                                        $total_updated = mysql_num_rows($result);
                                                        $total_not_updated = $row['total_count'] - $total_updated;

                                                        $count_total_org += $row['total_count'];
                                                        $count_total_org_updated += $total_updated;
                                                        ?>
                                                        <td>
                                                            <?php if ($total_updated > 0): ?>
                                                            <a href="report_monthly_update.php?org_agency=<?php echo $agency_code; ?>&admin_division=<?php echo $div_code; ?>&admin_district=<?php echo $dis_code; ?>&admin_upazila=<?php echo $upa_code; ?>&org_type=<?php echo $row['org_type_code']; ?>&form_submit=1&view=org_list&status=updated"><?php echo $total_updated; ?></a>
                                                            <?php else: ?>
                                                                <?php echo $total_updated; ?>
                                                            <?php endif; ?>
                                                            
                                                        </td>
                                                        <td>
                                                            <?php if ($total_not_updated > 0): ?>
                                                            <a href="report_monthly_update.php?org_agency=<?php echo $agency_code; ?>&admin_division=<?php echo $div_code; ?>&admin_district=<?php echo $dis_code; ?>&admin_upazila=<?php echo $upa_code; ?>&org_type=<?php echo $row['org_type_code']; ?>&form_submit=1&view=org_list&status=not_updated"><?php echo $total_not_updated; ?></a>
                                                            <?php else: ?>
                                                                <?php echo $total_not_updated; ?>
                                                            <?php endif; ?>
                                                            
                                                        </td>
                                                    </tr>
                                                <?php endwhile; ?>
                                                <tr class="success">
                                                    <td><strong>Summary</strong></td>
                                                    <td><strong><?php echo $count_total_org; ?></strong></td>
                                                    <td><strong><?php echo $count_total_org_updated; ?></strong></td>
                                                    <td><strong><?php echo $count_total_org - $count_total_org_updated; ?></strong></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    <?php endif; ?>
                                <?php endif; ?>


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
                var div_code = $('#admin_division').val();
                $.ajax({
                    type: "POST",
                    url: 'get/get_districts.php',
                    data: {div_code: div_code},
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
                var dis_code = $('#admin_district').val();
                $("#loading_content").show();
                $.ajax({
                    type: "POST",
                    url: 'get/get_upazilas.php',
                    data: {dis_code: dis_code},
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
        </script>


    </body>
</html>
