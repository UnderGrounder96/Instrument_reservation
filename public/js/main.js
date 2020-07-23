$.noConflict();

jQuery(() => {
    jQuery(".hide").hide();

    jQuery(".topRight").addClass("nav-item nav-link text-body");

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
