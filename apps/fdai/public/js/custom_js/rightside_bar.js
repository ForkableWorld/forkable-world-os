
;(function(window) {
    var
      // Is Modernizr defined on the global scope
      Modernizr = typeof Modernizr !== "undefined" ? Modernizr : false,
      // whether or not is a touch device
      isTouchDevice = Modernizr ? Modernizr.touch : !!('ontouchstart' in window || 'onmsgesturechange' in window),
      // Are we expecting a touch or a click?
      buttonPressedEvent = (isTouchDevice) ? 'touchstart' : 'click',
      chandra = function() {
          this.init();
      };

    // Initialization method
    chandra.prototype.init = function() {
        this.isTouchDevice = isTouchDevice;
        this.buttonPressedEvent = buttonPressedEvent;
    };

    chandra.prototype.getViewportHeight = function() {

        var docElement = document.documentElement,
                client = docElement.clientHeight,
                inner = window.innerHeight;

        if (client < inner)
            return inner;
        else
            return client;
    };

    chandra.prototype.getViewportWidth = function() {

        var docElement = document.documentElement,
                client = docElement.clientWidth,
                inner = window.innerWidth;

        if (client < inner)
            return inner;
        else
            return client;
    };

    // Creates a chandra object.
    window.chandra = new chandra();
})(this);
;(function($, window, document, undefined) {

    var pluginName = "chandraMenu",
            Modernizr = typeof Modernizr !== "undefined" ? Modernizr : false,
            isTouchDevice = Modernizr ? Modernizr.touch : !!('ontouchstart' in window || 'onmsgesturechange' in window),
            buttonPressedEvent = (isTouchDevice) ? 'touchstart' : 'click',
            defaults = {
                toggle: true,
                hidingClass: 'sr-only',
                breakpoints: 768
            };

    function Plugin(element, options) {
        this.element = element;
        this.settings = $.extend({}, defaults, options);
        this._defaults = defaults;
        this._name = pluginName;
        this.buttonPressedEvent = buttonPressedEvent;
        this.init();
    }

    Plugin.prototype = {
        init: function() {

            var $this = $(this.element),
                    resizeTimer,
                    self = this;

          
        },
        initCollapse: function(el) {
            var breakpoints = this.settings.breakpoints;
            if ($(window).width() < breakpoints) {
                this.addCollapse(el);
            } else {
                this.removeCollapse(el);
            }
        },
        addCollapse: function(el) {
            var $toggle = this.settings.toggle;
            $('body').removeClass('sidebar-left-mini');

            el.find('li.active').has('ul').children('ul').addClass('collapse in');
            el.find('li').not('.active').has('ul').children('ul').addClass('collapse');

            el.find('li').has('ul').children('a').on(this.buttonPressedEvent, function(e) {
                e.preventDefault();

                $(this).parent('li').toggleClass('opened').children('ul').collapse('toggle');

                if ($toggle) {
                    $(this).parent('li').siblings().removeClass('opened').children('ul.in').collapse('hide');
                }
            });
        },
       
    };

    $.fn[ pluginName ] = function(options) {
        return this.each(function() {
            if (!$.data(this, "plugin_" + pluginName)) {
                $.data(this, "plugin_" + pluginName, new Plugin(this, options));
            }
        });
    };

})(jQuery, window, document);
;(function($) {
    "use strict";

    var $navBar = $('nav.navbar'),
            $body = $('body'),
            $menu = $('#menu');

    function addPaddingTop(el, val) {
        el.css('padding-top', val);
    }
    function removePaddingTop(el) {
        el.css('padding-top', 'inherit');
    }
    function getHeight(el) {
        return el.outerHeight();
    }

    function init() {
        var isFixedNav = $navBar.hasClass('navbar-fixed-top');
        var bodyPadTop = isFixedNav ? $navBar.outerHeight(true) : 0;

        $body.css('padding-top', bodyPadTop);

       
    }

    chandra.navBar = function() {
        var resizeTimer;
        init();
        $(window).resize(function() {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(init(), 250);
        });
    };
    return chandra;
})(jQuery); 
;(function($, chandra){
  "use strict";
  // Define toggleFullScreen
  chandra.toggleFullScreen = function() {
    if ((window.screenfull !== undefined) && screenfull.enabled) {
            $('#toggleFullScreen').on(chandra.buttonPressedEvent, function(e) {
                screenfull.toggle(window.document[0]);
                $('body').toggleClass('fullScreen');
                e.preventDefault();
            });
        } else {
            $('#toggleFullScreen').addClass('hidden');
        }
  };
  // Define boxFullScreen
  chandra.boxFullScreen = function() {
    if ((window.screenfull !== undefined) && screenfull.enabled) {
            $('.full-box').on(chandra.buttonPressedEvent, function(e) {
              var $toggledPanel = $(this).parents('.box')[0];
                screenfull.toggle($toggledPanel);
                $(this).parents('.box').toggleClass('full-screen-box');
                $(this).parents('.box').children('.body').toggleClass('full-screen-box');
                $(this).children('i').toggleClass('fa-compress');
                e.preventDefault();
            });
        } else {
            $('.full-box').addClass('hidden');
        }
  };
  chandra.panelBodyCollapse = function() {
    var $collapseButton = $('.collapse-box'),
            $collapsedPanelBody = $collapseButton.closest('.box').children('.body');

        $collapsedPanelBody.collapse('show');
        
        $collapseButton.on(chandra.buttonPressedEvent, function (e) {
          var $collapsePanelBody = $(this).closest('.box').children('.body'),
              $toggleButtonImage = $(this).children('i');
            $collapsePanelBody.on('show.bs.collapse', function () {
              $toggleButtonImage.removeClass('fa-minus fa-plus').addClass('fa-spinner fa-spin');
            });
            $collapsePanelBody.on('shown.bs.collapse', function () {
              $toggleButtonImage.removeClass('fa-spinner fa-spin').addClass('fa-minus');
            });
            
            $collapsePanelBody.on('hide.bs.collapse', function () {
              $toggleButtonImage.removeClass('fa-minus fa-plus').addClass('fa-spinner fa-spin');
            });
            
            $collapsePanelBody.on('hidden.bs.collapse', function () {
              $toggleButtonImage.removeClass('fa-spinner fa-spin').addClass('fa-plus');
            });

            $collapsePanelBody.collapse('toggle');
          
          e.preventDefault();
        });
  };
  chandra.boxHiding = function() {
    $('.close-box').on(chandra.buttonPressedEvent, function () {
        $(this).closest('.box').hide('slow');
    });
  };
  return chandra;
})(jQuery, chandra || {});
;(function($, chandra) {
    var $body = $('body'),
            $leftToggle = $('.toggle-left'),
            $rightToggle = $('.toggle-right'),
            $count = 0;

    chandra.chandraAnimatePanel = function() {
      
      if($('#left').length){
        $leftToggle.on(chandra.buttonPressedEvent, function(e) {

            if ($(window).width() < 768) {
                $body.toggleClass('sidebar-left-opened');
            } else {
                switch (true) {
                    case $body.hasClass("sidebar-left-hidden"):
                        $body.removeClass("sidebar-left-hidden sidebar-left-mini");
                        break;
                    case $body.hasClass("sidebar-left-mini"):
                        $body.removeClass("sidebar-left-mini").addClass("sidebar-left-hidden");
                        break;
                    default :
                        $body.addClass("sidebar-left-mini");
                }

                e.preventDefault();
            }
        });
      } else {
    $leftToggle.addClass('hidden');
      }
    if($('#right').length){
        $rightToggle.on(chandra.buttonPressedEvent, function(e) {
            switch (true) {
                // Close right panel
                case $body.hasClass("sidebar-right-opened"):
                    $body.removeClass("sidebar-right-opened");
                    break;
                default :
                    // Open right panel
                    $body.addClass("sidebar-right-opened");
                    if (!$body.hasClass("sidebar-left-mini") & !$body.hasClass("sidebar-left-hidden")) {
                        $body.addClass("sidebar-left-mini");
                    }
            }
            e.preventDefault();
        });
    } else {
    $rightToggle.addClass('hidden');
      }
    };
    return chandra;
})(jQuery, chandra || {});
;(function($) {
   $(document).ready(function() {
    
    $('[data-toggle="tooltip"]').tooltip();

    chandra.navBar();
    chandra.chandraAnimatePanel();
    chandra.toggleFullScreen();
    chandra.boxFullScreen();
    chandra.panelBodyCollapse();
    chandra.boxHiding();   
  });
})(jQuery);
