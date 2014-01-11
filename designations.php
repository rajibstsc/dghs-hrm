<?php
require_once 'configuration.php'; // your config file
if ($_SESSION['logged'] != true) { // checks whether you are already logged in
    header("location:login.php");
}
/**
 * crudFrame work relative location from this
 */
$crudFrameworkRelativePath = "scripts/crud_framework";
/**
 * Module config
 */
$moduleName = "mod_system_configuration"; // the module this page belongs to
$moduleTitle = "Designation"; // user friendly name
$cssPrefix = ""; //css prefix that is added before some identifiers
/**
 * checks whether current viewer has permission to access/view this page
 */
require_once "$crudFrameworkRelativePath/cf_check_view_permission.php";
/*
 * Log config
 */
$storeLogInDatabase = TRUE; // TURE if you want to use 'log' table
$loggedInUserKeyValue = $_SESSION['user_id']; // this variable store the primary identifier stored in session


/**
 * Database/table config
 */
$dbTableName = 'sanctioned_post_designation'; // database table to operate
$dbTablePrimaryKeyFieldName = 'id'; // primary key field name
$dbTablePrimaryKeyFieldVal = mysql_real_escape_string(trim($_REQUEST["$dbTablePrimaryKeyFieldName"])); // primary key field value that is passed as parameter or form post
/**
 * Database/table config for user/datetime record of this operation
 */
$updatedbyFieldName = 'updated_by'; // this is the fieldname where the user who updated this value is stored
$updatedbyFieldVal = getLoggedUserName(); // gets appropriate value.
$updatedDateTimeFieldName = 'updated_datetime';
$updatedDateTimeFieldVal = getDateTime();

/**
 * Form handling config
 */
$param = mysql_real_escape_string(trim($_REQUEST['param'])); // gets the actio param
$exception_field = array('submit', 'param', 'reset'); // array to store field names that needs to skipped in constructed query
$requiredFieldNames = array('designation'); // array for required fields

/* * ********************************************************************************************************************************************
 * Delete
 */
if ($param == 'delete') {
    require_once "$crudFrameworkRelativePath/cf_delete.php";
}
/* * *****************************************
 * Add/Edit
 */
if (!strlen($dbTablePrimaryKeyFieldVal)) {
    $param = "add";
} else {
    if ($a = getRowVal($dbTableName, $dbTablePrimaryKeyFieldName, $dbTablePrimaryKeyFieldVal)) {
        $param = "edit";
    } else {
        $valid = false;
        array_push($alert, "Invalid $dbTablePrimaryKeyFieldName. No such $dbTablePrimaryKeyFieldName found in database");
    }
}
if (isset($_POST[submit])) {
    if ($param == 'add' || $param == 'edit') {
        /*
         * 	server side validation
         */
        // Need to update this
        if (count($requiredFieldNames)) {
            require_once "$crudFrameworkRelativePath/cf_required_field_check.php";
        }

        /*         * ********************************** */
        if ($valid) {
            if ($param == 'add') {
                $str_k_additional = ""; // add updated_by,updated_datetime type field names here. start with comma
                $str_v_additional = ""; // add updated_by,updated_datetime type field values here. start with comma
                require_once "$crudFrameworkRelativePath/cf_add.php";
                // if success then $valid= TRUE
            } else if ($param == 'edit') {
                /*
                 * 	Check whether current user has edit
                 */
                $str_additioal = ""; // add updated_by,updated_datetime type ,field1=val1,field2=val2 names here Must start with comma
                require_once "$crudFrameworkRelativePath/cf_edit.php";
            }
            //echo $sql;
        }
    }
}

/* * **************************************** */
$dataRows = getRows($dbTableName, $condition);
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title><?php echo $org_name . " | " . $app_name; ?></title>
        <?php
        include_once 'include/header/header_css_js.inc.php';
        //include_once 'include/header/header_ga.inc.php';
        ?>

        <!--CSS-->
        <style type="text/css">
            .formTitle {
                color: inherit;
                font-family: inherit;
                font-weight: bold;
                line-height: 20px;
                margin: 10px 0;
                text-rendering: optimizelegibility;}
            #tabularData{font-size: 12px;}

        </style>
    </head>
    <body data-spy="scroll" data-target=".bs-docs-sidebar">
        <?php //require_once "$crudFrameworkRelativePath/cf_jquery_modal_popup.php"; ?>

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

            <!-- Docs nav
            ================================================== -->
            <div class="row-fluid">
                <div class="span3 bs-docs-sidebar">
                    <ul class="nav nav-list bs-docs-sidenav">
                        <?php include_once 'include/left_menu.php'; ?>
                    </ul>
                </div>
                <div class="span9">
                    <!-- Form Start here
                    ================================================== -->

                    <h4 class="<?= $cssPrefix ?>formTitle"><?= ucfirst($param) . " " . $moduleTitle ?></h4>
                    <div class="<?= $cssPrefix ?>toAlertMsg"><?php printAlert($valid, $alert); ?></div>
                    <div class="<?= $cssPrefix ?>addButton"><a href="<?php echo $_SERVER['PHP_SELF']; ?>">[+] Add</a></div>
                    <div class="<?= $cssPrefix ?>form">

                        <?php
                        if (hasPermission($moduleName, $param, getLoggedUserName())) {
                            ?>
                            <form class="cmxform" id="commentForm"  action="<?= $_SERVER['PHP_SELF'] ?>" method="post" enctype="multipart/form-data">
                                <input id="cdesignation" name="designation" type="text" value="<?= addEditInputField('designation') ?>" class="validate[requried]"/>

                                <!-- Default input items -->
                                <input name="submit" type="submit" class="btn" value="Save" />
                                <input name="reset" type="reset" class="btn" value="Reset" />
                                <?php if (strlen($dbTablePrimaryKeyFieldVal)) { ?>
                                    <input type="hidden" name="<?= $dbTablePrimaryKeyFieldName ?>" value="<?php echo $dbTablePrimaryKeyFieldVal; ?>" />
                                <?php } ?>
                                <!-- =================== -->
                            </form>
                        <?php }
                        ?>

                    </div>
                    <div id="<?= $cssPrefix ?>tabularData">
                        <!--<h2>List of Departments</h2>-->
                        <table id="datatable" width="100%">
                            <thead>
                                <tr>
                                    <th>id</th>
                                    <th><!--designation_code-->code</th>
                                    <th>designation</th>
                                    <th>payscale</th>
                                    <th>class</th>
                                    <th>DGC<!--designation_group_code--></th>
                                    <th>GC</th>
                                    <th>ranking</th>
                                    <th>bpcc<!--bangladesh_professional_category_code--></th>
                                    <th>wogc<!--who_occupation_group_codebook--></th>
                                    <th>updated by</th>
                                    <th>updated on</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $i = 1;
                                foreach ($dataRows as $dataRow) {
                                    ?>
                                    <tr id="<?= $dataRow[$dbTablePrimaryKeyFieldName] ?>">
                                        <td><a href="<?= $_SERVER['PHP_SELF'] ?>?param=edit&<?= $dbTablePrimaryKeyFieldName ?>=<?= $dataRow['id'] ?>"><?= $dataRow['id'] ?></td>
                                        <td><?= $dataRow['designation_code'] ?></td>
                                        <td><?= $dataRow['designation'] ?></td>
                                        <td><?= $dataRow['payscale'] ?></td>
                                        <td><?= $dataRow['class'] ?></td>
                                        <td><?= $dataRow['designation_group_code'] ?></td>
                                        <td><?= $dataRow['group_code'] ?></td>
                                        <td><?= $dataRow['ranking'] ?></td>
                                        <td><?= $dataRow['bangladesh_professional_category_code'] ?></td>
                                        <td><?= $dataRow['who_occupation_group_codebook'] ?></td>
                                        <td><?= $dataRow['updated_by'] ?></td>
                                        <td><?= $dataRow['updated_datetime'] ?></td>
                                        <td>
                                            <?php if (hasPermission($moduleName, 'manage', getLoggedUserName())) { ?>
                                                <a class='cf_delete' id='<?= $dataRow[$dbTablePrimaryKeyFieldName] ?>"' href='#'>Delete</a>
                                                <?php
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                    <?php
                                    $i++;
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <!-- Footer
        ================================================== -->
        <?php include_once 'include/footer/footer.inc.php'; ?>
        <script type="text/javascript">
            $('table#datatable').dataTable({
                "bJQueryUI": true,
                "sPaginationType": "full_numbers",
                "aaSorting": [[0, "desc"]],
                "iDisplayLength": 25,
                "bStateSave": true
            });
        </script>
    </body>
</html>
