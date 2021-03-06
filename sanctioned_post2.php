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
if ($org_type_code == 1029 || $org_type_code == 1051) {
    $org_code = (int) mysql_real_escape_string(trim($_GET['org_code']));

    $org_info = getOrgDisCodeAndUpaCodeFromOrgCode($org_code);
    $parent_org_info = getOrgDisCodeAndUpaCodeFromOrgCode($_SESSION['org_code']);

    if (($org_info['district_code'] == $parent_org_info['district_code']) && ($org_info['upazila_thana_code'] == $parent_org_info['upazila_thana_code'])) {
        $org_code = (int) mysql_real_escape_string(trim($_GET['org_code']));
        $org_name = getOrgNameFormOrgCode($org_code);
        $org_type_name = getOrgTypeNameFormOrgCode($org_code);
        $echoAdminInfo = " | " . $parent_org_info['upazila_thana_name'];
        $isAdmin = TRUE;
    }
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
        <link href="assets/css/login.css" rel="stylesheet"/>
    </head>

    <body data-spy="scroll" data-target=".bs-docs-sidebar">

        <!-- Top navigation bar
        ================================================== -->
        <?php include_once 'include/header/header_top_menu.inc.php'; ?>

        <!-- Subhead
        ================================================== -->
        <header class="jumbotron subhead" id="overview">
            <div class="container">
                <h1><?php echo $org_name; ?></h1>
                <p class="lead"><?php echo "$org_type_name"; ?></p>
            </div>
        </header>


        <div class="container">

            <!-- nav
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
                    <!-- Sanctioned Post
                    ================================================== -->
                    <section id="sanctioned-post">

                        <div class="row">
                            <div class="span9">
                                <div class="btn-group pull-right">
                                    <a class="btn"href="sanctioned_post.php?org_code=<?php echo $org_code; ?>"><i class="icon-group"></i> Sanctioned Post</a>
                                    <a class="btn" href="sanctioned_post_sorted.php?org_code=<?php echo $org_code; ?>"><i class="icon-sort-by-alphabet"></i> Sorted</a>
                                </div>
                                <h3>Sanctioned Post</h3>
                                <h5>First Level Divisions</h5>
                                <div class="accordion" id="sanctioned_post_divisions">
                                    <?php
                                    $sql = "SELECT total_manpower_imported_sanctioned_post_copy.first_level_name,
                                                                                    total_manpower_imported_sanctioned_post_copy.first_level_id
                                                                                FROM total_manpower_imported_sanctioned_post_copy
                                                                                WHERE total_manpower_imported_sanctioned_post_copy.org_code= $org_code
                                                                                AND total_manpower_imported_sanctioned_post_copy.first_level_name != \"\"
                                                                                AND total_manpower_imported_sanctioned_post_copy.active LIKE 1
                                                                                GROUP BY total_manpower_imported_sanctioned_post_copy.first_level_name
                                                                                ORDER BY total_manpower_imported_sanctioned_post_copy.first_level_name";
                                    $first_level_result = mysql_query($sql) or die(mysql_error() . "<br /><br />Code:<b>sanctioned_post_divisions:2</b><br /><br /><b>Query:</b><br />___<br />$sql<br />");
                                    while ($first_level_list = mysql_fetch_assoc($first_level_result)):
                                        $first_level_accord_id = $designation_div_id = preg_replace("/[^a-zA-Z0-9]+/", "", strtolower($first_level_list['first_level_name']));
                                        ;
                                        ?>
                                        <div class="accordion-group">
                                            <div class="accordion-heading">
                                                <a class="accordion-toggle" id="heading<?php echo "$first_level_accord_id"; ?>" data-toggle="collapse" data-parent="#sanctioned_post_divisions" href="#<?php echo "$first_level_accord_id"; ?>">
                                                    <?php echo $first_level_list['first_level_name']; ?>
                                                </a>
                                            </div>
                                            <div id="<?php echo "$first_level_accord_id"; ?>" class="accordion-body collapse">
                                                <div class="accordion-inner">
                                                    <p>
                                                        <button class="btn btn-primary btn-small" type="button" id="btn_designations_under_<?php echo "$first_level_accord_id"; ?>">View Designations</button>
                                                        <button class="btn btn-info btn-small" type="button" id="load_2nd_for<?php echo $designation_div_id; ?>">View Second Level Divisions</button>
                                                    </p>
                                                    <div id="designations_under_<?php echo "$first_level_accord_id"; ?>">


                                                    </div>
                                                    <div id="div_2nd_<?php echo $designation_div_id; ?>"></div>
                                                    <div id="loading-2nd-<?php echo $designation_div_id; ?>"  class="alert" style="display: none;"><i class="icon-spinner icon-spin icon-large"></i> Loading second level divisions...</div>
                                                </div>
                                            </div>
                                            <script type="text/javascript">
                                                $("#load_2nd_for<?php echo $designation_div_id; ?>").click(function() {
                                                    $("#loading-2nd-<?php echo $designation_div_id; ?>").show();
                                                    $.ajax({
                                                        type: "POST",
                                                        url: "get/get_sp_second_level.php",
                                                        data: {
                                                            org_code: "<?php echo "$org_code"; ?>",
                                                            first_level_id: "<?php echo $first_level_list['first_level_id']; ?>",
                                                            div: "desi_2nd_",
                                                            loading: "loading-desi-"

                                                        },
                                                        success: function(data) {
                                                            $("#loading-2nd-<?php echo $designation_div_id; ?>").hide();
                                                            $('#div_2nd_<?php echo $designation_div_id; ?>').html("");
                                                            $("#div_2nd_<?php echo $designation_div_id; ?>").append(data);
                                                        }
                                                    });

                                                });

                                                $("#btn_designations_under_<?php echo "$first_level_accord_id"; ?>").click(function() {
                                                    $("#loading-2nd-<?php echo $designation_div_id; ?>").show();
                                                    $.ajax({
                                                        type: "POST",
                                                        url: "get/get_sp_designations.php",
                                                        data: {
                                                            org_code: "<?php echo "$org_code"; ?>",
                                                            first_level_id: "<?php echo $first_level_list['first_level_id']; ?>"
                                                        },
                                                        success: function(data) {
                                                            $("#loading-2nd-<?php echo $designation_div_id; ?>").hide();
                                                            $('#designations_under_<?php echo "$first_level_accord_id"; ?>').html("");
                                                            $("#designations_under_<?php echo "$first_level_accord_id"; ?>").append(data);
                                                        }
                                                    });

                                                });
    //                                                $("#btn_designations_under_<?php echo "$first_level_accord_id"; ?>").click(function (){
    //                                                    $("#designations_under_<?php echo "$first_level_accord_id"; ?>").append("List");
    //                                                });
                                            </script>
                                        </div>
                                    <?php endwhile; ?>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div> <!-- /container -->
        <!-- Footer
        ================================================== -->
        <?php include_once 'include/footer/footer.inc.php'; ?>
    </body>
</html>
