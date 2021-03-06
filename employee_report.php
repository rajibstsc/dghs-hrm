<?php
require_once 'configuration.php';

if ($_SESSION['logged'] != true) {
    header("location:login.php");
}

require_once './include/check_org_code.php';

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
                    <section id="report">
                        <div class="alert alert-success" id="generate_report">
                            <i class="icon-cog icon-spin icon-large"></i> <strong>Generating report...</strong>
                        </div>
                        <div class="row">
                            <?php
                            $sql = "SELECT
                                            total_manpower_imported_sanctioned_post_copy.id,
                                            total_manpower_imported_sanctioned_post_copy.designation,
                                            total_manpower_imported_sanctioned_post_copy.designation_code,
                                            total_manpower_imported_sanctioned_post_copy.type_of_post,
                                            total_manpower_imported_sanctioned_post_copy.pay_scale,
                                            total_manpower_imported_sanctioned_post_copy.class,
                                            COUNT(*) AS sp_count
                                    FROM
                                            total_manpower_imported_sanctioned_post_copy
                                    LEFT JOIN sanctioned_post_designation ON sanctioned_post_designation.designation_code = total_manpower_imported_sanctioned_post_copy.designation_code
                                    WHERE
                                            total_manpower_imported_sanctioned_post_copy.org_code = $org_code
                                    AND total_manpower_imported_sanctioned_post_copy.active LIKE 1
                                    GROUP BY
                                            total_manpower_imported_sanctioned_post_copy.designation
                                    ORDER BY
                                            sanctioned_post_designation.ranking";
                            $result = mysql_query($sql) or die(mysql_error() . "<br /><br />Code:<b>sql:2</b><br /><br /><b>Query:</b><br />___<br />$sql<br />");
                            $total_sanctioned_post = mysql_num_rows($result);
                            
                            
                            $total_sanctioned_post_count_sum = 0;
                            $total_sanctioned_post_existing_sum = 0;
                            $total_existing_male_sum = 0;
                            $total_existing_female_sum = 0;
                            ?>
                            <table class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th>Designation</th>
                                        <th>Type of post</th>
                                        <th>Payscale</th>
                                        <th>Class</th>                                        
                                        <th>Total Post(s)</th>
                                        <th>Filled up Post(s)</th>
                                        <th>Total Male</th>
                                        <th>Total Female</th>
                                        <th>Vacant Post(s)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    while ($row = mysql_fetch_assoc($result)) :
                                        $sql= "SELECT
                                                        designation,
                                                        designation_code,
                                                        COUNT(*) AS existing_total_count
                                                FROM
                                                        total_manpower_imported_sanctioned_post_copy
                                                WHERE
                                                        org_code = $org_code
                                                AND designation_code = " . $row['designation_code'] . "
                                                AND staff_id_2 > 0
                                                AND total_manpower_imported_sanctioned_post_copy.active LIKE 1";
                                                    $r = mysql_query($sql) or die(mysql_error() . "<br /><br />Code:<b>sql:2</b><br /><br /><b>Query:</b><br />___<br />$sql<br />");
                                            $a = mysql_fetch_assoc($r);
                                            $existing_total_count = $a['existing_total_count'];
                                            
                                            $sql= "SELECT
                                                        total_manpower_imported_sanctioned_post_copy.designation,
                                                        total_manpower_imported_sanctioned_post_copy.designation_code,
                                                        COUNT(*) AS existing_male_count
                                                FROM
                                                        total_manpower_imported_sanctioned_post_copy
                                                LEFT JOIN old_tbl_staff_organization ON old_tbl_staff_organization.staff_id = total_manpower_imported_sanctioned_post_copy.staff_id_2
                                                WHERE
                                                        total_manpower_imported_sanctioned_post_copy.org_code = $org_code
                                                AND total_manpower_imported_sanctioned_post_copy.designation_code = " . $row['designation_code'] . "
                                                AND total_manpower_imported_sanctioned_post_copy.staff_id_2 > 0
                                                AND old_tbl_staff_organization.sex=1
                                                AND total_manpower_imported_sanctioned_post_copy.active LIKE 1";
                                                    $r = mysql_query($sql) or die(mysql_error() . "<br /><br />Code:<b>sql:2</b><br /><br /><b>Query:</b><br />___<br />$sql<br />");
                                            $a = mysql_fetch_assoc($r);
                                            $existing_male_count = $a['existing_male_count'];
                                            
                                            $existing_female_count = $existing_total_count-$existing_male_count;
                                            $total_sanctioned_post_count_sum += $row['sp_count'];
                                            $total_sanctioned_post_existing_sum += $existing_total_count;
                                            $total_existing_male_sum += $existing_male_count;
                                            $total_existing_female_sum += $existing_female_count;
                                        ?>
                                    <tr>
                                        <td><?php echo $row['designation'] ; ?></td>
                                        <td><?php echo getTypeOfPostNameFromCode($row['type_of_post']); ?></td>
                                        <td><?php echo $row['pay_scale']; ?></td>
                                        <td><?php echo $row['class']; ?></td>
                                        <td><?php echo $row['sp_count']; ?></td>
                                        <td><?php echo $existing_total_count; ?></td>
                                        <td><?php echo $existing_male_count; ?></td>
                                        <td><?php echo $existing_female_count; ?></td>
                                        <td><?php echo $row['sp_count']-$existing_total_count; ?></td>
                                    </tr>
                                    <?php endwhile; ?>
                                    <tr class="info">
                                        <td colspan="4"><strong>Summary</strong></td>
                                        <td><strong><?php echo $total_sanctioned_post_count_sum; ?></strong></td>
                                        <td><strong><?php echo $total_sanctioned_post_existing_sum; ?></strong></td>
                                        <td><strong><?php echo $total_existing_male_sum; ?></strong></td>
                                        <td><strong><?php echo $total_existing_female_sum; ?></strong></td>
                                        <td><strong><?php echo $total_sanctioned_post_count_sum-$total_sanctioned_post_existing_sum; ?></string></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                    </section>

                </div>
            </div>

        </div>



        <!-- Footer
        ================================================== -->
        <?php include_once 'include/footer/footer.inc.php'; ?>      
        
        <script>
            $("#generate_report").hide();
        </script>

    </body>
</html>
