<?php
require_once 'configuration.php';

if ($_SESSION['logged'] != true) {
    header("location:login.php");
}

require_once './include/check_org_code.php';

/**
 * Reassign org_code and enable edit permission for Upazila and below
 *
 * Upazila users can edit the organizations under that UHC.
 * Like the UHC users can edit the USC and USC(New) and CC organizations
 */
if ($org_type_code == 1029 || $org_type_code == 1051){
    $org_code = (int) mysql_real_escape_string(trim($_GET['org_code']));

    $org_info = getOrgDisCodeAndUpaCodeFromOrgCode($org_code);
    $parent_org_info = getOrgDisCodeAndUpaCodeFromOrgCode($_SESSION['org_code']);

    if (($org_info['district_code'] == $parent_org_info['district_code']) && ($org_info['upazila_thana_code'] == $parent_org_info['upazila_thana_code'])){
        $org_code = (int) mysql_real_escape_string(trim($_GET['org_code']));
        $org_name = getOrgNameFormOrgCode($org_code);
        $org_type_name = getOrgTypeNameFormOrgCode($org_code);
        $echoAdminInfo = " | " . $parent_org_info['upazila_thana_name'];
        $isAdmin = TRUE;
    }
}


date_default_timezone_set('Asia/Dhaka');
$current_year = date("Y");
$current_month = date("n");
$current_dateTime = date("Y-m-d H:i:s");



if (isset($_POST['submit_success'])) {
    $submit_org_code = mysql_real_escape_string(trim($_GET['submit_org_code']));
    $submit_month = mysql_real_escape_string(trim($_GET['submit_month']));
    $submit_year = mysql_real_escape_string(trim($_GET['submit_year']));
    $submit_dateTime = mysql_real_escape_string(trim($_GET['submit_dateTime']));

    $sql = "UPDATE organization SET monthly_update=$current_month, monthly_update_datetime=\"$current_dateTime\" WHERE org_code ='$org_code'''";
    $result = mysql_query($sql) or die(mysql_error() . "<p>Code:<b>monthlyUpdate:1</p><p>Query:</b></p>___<p>$sql</p>");

    empty($_POST);
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <title><?php echo $org_name . " | " . $app_name; ?></title>
        <?php
        include_once 'include/header/header_css_js.inc.php';
        include_once 'include/header/header_ga.inc.php';
        ?>
    </head>

    <body data-spy="scroll" data-target=".bs-docs-sidebar">

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
                    <section id="mothly_update">
                        <h3>Monthly Update Summary</h3>
                        <div class="row">

                            <div class="span9">
                                <div class="well well-large">
                                    <p class="lead">
                                        Have you updated the HRM System Data for the Month of <?php echo date("F"); ?>?

                                    <form class="form-horizontal" action="" method="post">
                                        <input type="hidden" id="submit_org_code" name="submit_org_code" value="<?php echo "$org_code"; ?>">
                                        <input type="hidden" id="submit_month" name="submit_month" value="<?php echo "$current_month"; ?>">
                                        <input type="hidden" id="submit_year" name="submit_year" value="<?php echo "$current_year"; ?>">
                                        <input type="hidden" id="submit_dateTime" name="submit_dateTime" value="<?php echo "$current_dateTime"; ?>">
                                        <input type="hidden" id="submit_success" name="submit_success" value="yes">
                                        <button type="submit" class="btn btn-large btn-success">Updated HRM Data</button>
                                    </form>
                                    </p>

                                </div>
                                <?php
                                $sql = "SELECT monthly_update_datetime FROM organization where org_code=$org_code";
                                $result = mysql_query($sql) or die(mysql_error() . "<p>Code:<b>monthlyUpdate:1</p><p>Query:</b></p>___<p>$sql</p>");

                                $last_update_datetime = mysql_fetch_assoc($result);
                                ?>
                                <span class="label label-info"><em><h5>&nbsp;Last updated on <?php echo $last_update_datetime['monthly_update_datetime']; ?> &nbsp;</h5></em></span>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>
        <!-- Footer
        ================================================== -->
        <?php include_once 'include/footer/footer.inc.php'; ?>
    </body>
</html>
