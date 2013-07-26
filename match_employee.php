<?php
require_once 'configuration.php';

if ($_SESSION['logged'] != true) {
    header("location:login.php");
}
$org_code = $_GET['org_code'];
if ($org_code == "") {
    $org_code = $_SESSION['org_code'];
}
//$org_code = $_SESSION['org_code'];
$org_code = (int) $org_code;


$org_code = $_SESSION['org_code'];
$org_name = $_SESSION['org_name'];
$org_type_name = $_SESSION['org_type_name'];
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title><?php echo $org_name . " | " . $app_name; ?></title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="description" content="">
        <meta name="author" content="">

        <!-- Le styles -->
        <link href="assets/css/bootstrap.min.css" rel="stylesheet">
        <link href="assets/css/bootstrap-responsive.css" rel="stylesheet">
        <link href="library/font-awesome/css/font-awesome.min.css" rel="stylesheet">
        <link href="assets/css/style.css" rel="stylesheet">
        <link href="assets/js/google-code-prettify/prettify.css" rel="stylesheet">

        <!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
        <!--[if lt IE 9]>
          <script src="assets/js/html5shiv.js"></script>
        <![endif]-->

        <!-- Le fav and touch icons -->
        <link rel="apple-touch-icon-precomposed" sizes="144x144" href="assets/ico/apple-touch-icon-144-precomposed.png">
        <link rel="apple-touch-icon-precomposed" sizes="114x114" href="assets/ico/apple-touch-icon-114-precomposed.png">
        <link rel="apple-touch-icon-precomposed" sizes="72x72" href="assets/ico/apple-touch-icon-72-precomposed.png">
        <link rel="apple-touch-icon-precomposed" href="assets/ico/apple-touch-icon-57-precomposed.png">
        <link rel="shortcut icon" href="assets/ico/favicon.png">

        <!--
        <script type="text/javascript">
            var _gaq = _gaq || [];
            _gaq.push(['_setAccount', 'ACCOUNT_ID']);
            _gaq.push(['_trackPageview']);
            (function() {
                var ga = document.createElement('script');
                ga.type = 'text/javascript';
                ga.async = true;
                ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
                var s = document.getElementsByTagName('script')[0];
                s.parentNode.insertBefore(ga, s);
            })();
        </script>
        -->
    </head>

    <body data-spy="scroll" data-target=".bs-docs-sidebar">

        <!-- Navbar
        ================================================== -->
        <div class="navbar navbar-inverse navbar-fixed-top">
            <div class="navbar-inner">
                <div class="container">
                    <button type="button" class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                    <a class="brand" href="./index.php"><?php echo $app_name; ?></a>
                    <div class="nav-collapse collapse">
                        <ul class="nav">
                            <li class="active">
                                <a href="./index.html">Home</a>                                
                            </li>
                            <li class="">
                                <a href="http://www.dghs.gov.bd" target="_brank">DGHS Website</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Subhead
        ================================================== -->
        <header class="jumbotron subhead" id="overview">
            <div class="container">
                <h1><?php echo $org_name; ?></h1>
                <p class="lead"><?php echo "$org_type_name"; ?></p>
            </div>
        </header>


        <div class="container">

            <!-- Docs nav
            ================================================== -->
            <div class="row">
                <div class="span3 bs-docs-sidebar">
                    <ul class="nav nav-list bs-docs-sidenav">
                        <li><a href="home.php?org_code=<?php echo $org_code; ?>"><i class="icon-chevron-right"></i><i class="icon-home"></i> Homepage</a>
                        <li><a href="org_profile.php?org_code=<?php echo $org_code; ?>"><i class="icon-chevron-right"></i><i class="icon-hospital"></i> Organization Profile</a></li>
                        <li><a href="sanctioned_post.php?org_code=<?php echo $org_code; ?>"><i class="icon-chevron-right"></i><i class="icon-group"></i> Sanctioned Post</a></li>
                        <li><a href="employee.php?org_code=<?php echo $org_code; ?>"><i class="icon-chevron-right"></i><i class="icon-user-md"></i> Employee Profile</a></li>
                        <li><a href="move_request.php?org_code=<?php echo $org_code; ?>"><i class="icon-chevron-right"></i><i class="icon-exchange"></i> Move Request</a></li>
                        <li class="active"><a href="match_employee.php?org_code=<?php echo $org_code; ?>"><i class="icon-chevron-right"></i><i class="icon-copy"></i> Match Employee</a></li>		
                        <li><a href="settings.php?org_code=<?php echo $org_code; ?>"><i class="icon-chevron-right"></i><i class="icon-cogs"></i> Settings</a></li>		
                        <li><a href="logout.php"><i class="icon-chevron-right"></i><i class="icon-signout"></i> Sign out</a></li>
                    </ul>
                </div>
                <div class="span9">
                    <!-- main
                    ================================================== -->
                    <section id="match_staff">
                        <?php

                        function getDesignationInfoFromCode($des_code) {
                            $sql = "SELECT
                                        sanctioned_post_designation.designation,
                                        sanctioned_post_designation.payscale,
                                        sanctioned_post_designation.class
                                    FROM
                                        sanctioned_post_designation
                                    WHERE
                                        sanctioned_post_designation.designation_code = $des_code";
                            $result = mysql_query($sql) or die(mysql_error() . "<br /><br />Code:<b>getOrgTypeNameFormOrgCode:2</b><br /><br /><b>Query:</b><br />___<br />$sql<br />");

                            $data = mysql_fetch_assoc($result);

                            return $data;
                        }

                        function getDeptNameFromId($dept_name) {
                            $sql = "SELECT
                                    very_old_departments.dept_id,
                                    very_old_departments.department_id,
                                    very_old_departments.`name`
                                FROM
                                    very_old_departments
                                WHERE
                                    very_old_departments.department_id = $dept_name";
                            $result = mysql_query($sql) or die(mysql_error() . "<br /><br />Code:<b>getOrgTypeNameFormOrgCode:2</b><br /><br /><b>Query:</b><br />___<br />$sql<br />");

                            $data = mysql_fetch_assoc($result);

                            return $data['name'];
                        }
                        ?>
                        <?php
                        $sql = "SELECT
                                    old_tbl_staff_organization.staff_id,
                                    old_tbl_staff_organization.sanctioned_post_id,
                                    old_tbl_staff_organization.designation_id,
                                    old_tbl_staff_organization.department_id,
                                    old_tbl_staff_organization.staff_name,
                                    old_tbl_staff_organization.father_name
                                FROM
                                    old_tbl_staff_organization
                                WHERE
                                    old_tbl_staff_organization.org_code = $org_code
                                ORDER BY
                                    old_tbl_staff_organization.staff_name ASC";
                        $result = mysql_query($sql) or die(mysql_error() . "<br /><br />Code:<b>:2</b><br /><br /><b>Query:</b><br />___<br />$sql<br />");
                        ?>

                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Staff Name</th>
                                    <th>Dept</th>
                                    <!--<th>Father's Name</th>-->
                                    <th>Designation</th>
                                    <th>Pay scale</th>
                                    <th>Class</th>
                                    <th>Staff Id</th>
                                    <th>Sanctioned Post Id</th>
                                    <!--<th></th>-->
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($data = mysql_fetch_assoc($result)): ?>
                                    <tr>
                                        <td><?php echo $data['staff_name']; ?></td>
                                        <td><?php echo getDeptNameFromId($data['department_id']); ?></td>
                                        <!--<td><?php echo $data['father_name']; ?></td>-->
                                        <?php
                                        $designation_info = getDesignationInfoFromCode($data['designation_id']);
                                        ?>
                                        <td><?php echo $designation_info['designation']; ?></td>
                                        <td><?php echo $designation_info['payscale']; ?></td>
                                        <td><?php echo $designation_info['class']; ?></td>
                                        <td><a href="employee.php?staff_id=<?php echo $data['staff_id']; ?>"><?php echo $data['staff_id']; ?></a></td>
                                        <td><a href="#" data-name="sanctioned_post_id" data-type="text" data-pk='<?php echo $data['staff_id']; ?>' class="text-input"><?php echo $data['sanctioned_post_id']; ?></a></td>
                                        <!--<td></td>-->
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </section>                    
                </div>
            </div>

        </div>



        <!-- Footer
        ================================================== -->
        <?php include_once 'include/footer/footer_menu.inc.php'; ?>



        <!-- Le javascript
        ================================================== -->
        <!-- Placed at the end of the document so the pages load faster -->
        <script type="text/javascript" src="http://platform.twitter.com/widgets.js"></script>
        <script src="assets/js/jquery.js"></script>
        <script src="assets/js/bootstrap.min.js"></script>

        <script src="assets/js/holder/holder.js"></script>
        <script src="assets/js/google-code-prettify/prettify.js"></script>

        <script src="assets/js/application.js"></script>

        <script src="library/bootstrap-editable/js/bootstrap-editable.min.js"></script>
        <script>
            $.fn.editable.defaults.mode = 'inline';

            var org_code= <?php echo "$org_code"; ?>;
            $(function() {
                $('#match_staff a.text-input').editable({
                    type: 'text',
                    url: 'post/post_match_staff.php',
                    params: function(params) {
                        params.org_code = org_code;
                        return params;
                    }
                });
            });
        </script>
    </body>
</html>