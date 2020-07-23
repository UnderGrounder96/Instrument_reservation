$.noConflict();

jQuery(() => {
  jQuery(".hide").hide();

  jQuery(".topRight").addClass("nav-item nav-link text-body");

  jQuery("#inst").click(() => {
    jQuery(".hide").hide();
    jQuery("#inst1").show();
  });

  jQuery("#user").click(() => {
    jQuery(".hide").hide();
    jQuery("#user1").show();
  });

  jQuery("#rig").click(() => {
    jQuery(".hide").hide();
    jQuery("#rig1").show();
  });
});

/*
// NOT WORKING

  <?php
    if(isset($_GET["use"]) || isset($_GET["er"]))
      echo "jQuery(\"#user1\").show();\n";

    else if(isset($_GET["rig"]))
      echo "jQuery(\"#rig1\").show()\n";

    else
      echo "jQuery(\"#inst1\").show();\n";
  >
*/
