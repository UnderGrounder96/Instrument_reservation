$.noConflict();
jQuery(function() {
    jQuery(".hide").hide();
    <?php
        if(isset($_GET["use"]) || isset($_GET["er"]))
            echo "jQuery(\"#user1\").show();\n";

        else if(isset($_GET["rig"]))
            echo "jQuery(\"#rig1\").show()\n";

        else
            echo "jQuery(\"#inst1\").show();\n";
    ?>

    jQuery("#inst").click(function() {
        jQuery(".hide").hide();
        jQuery("#inst1").show();
    });

    jQuery("#user").click(function() {
        jQuery(".hide").hide();
        jQuery("#user1").show();
    });

    jQuery("#rig").click(function() {
        jQuery(".hide").hide();
        jQuery("#rig1").show();
    });
});