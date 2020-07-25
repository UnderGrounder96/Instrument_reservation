$.noConflict();

jQuery(window).on("load", () => {
  jQuery(".spinner").delay(1000).fadeOut(1000);
});

jQuery(() => {
  // CALC FULL HEIGHT
  getHeight();

  jQuery(window).resize(() => {
    getHeight();
  });

  jQuery(".validate").each(function () {
    validate(this);

    jQuery(this).keyup(function () {
      validate(this);
    });
  });

  jQuery(".hide").hide();

  jQuery("#inst").click(() => {
    _hide("#inst");
    jQuery("#inst1").show();
  });

  jQuery("#user").click(() => {
    _hide("#user");
    jQuery("#user1").show();
  });

  jQuery("#rig").click(() => {
    _hide("#rig");
    jQuery("#rig1").show();
  });
});

function _hide(toShow) {
  jQuery(".hide").hide();
  jQuery("li").removeClass("active");
  jQuery(toShow).addClass("active");
}

function getHeight() {
  let fullHeighMinusHeader =
    jQuery(window).height() -
    jQuery("header").outerHeight() -
    jQuery("footer").outerHeight() -
    80;

  jQuery("main").height(fullHeighMinusHeader.toFixed(2));
}

// Validate Function
function validate(input) {
  let obj = jQuery(input);

  // Not empty
  if (!/^[a-zA-Z0-9_ ]{1,30}$/.test(obj.val())) {
    // Invalid
    obj.css("border-color", "#FAC3C3");
  } else {
    // Valid
    obj.css("border-color", "lightgreen");
    if (obj.next().hasClass("error")) {
      obj.next().remove();
    }
  }
}
