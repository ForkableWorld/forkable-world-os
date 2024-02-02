$(function() {
    /* For demo purposes */
    var demo = $("<div />").css({
        position: "fixed",
        top: "150px",
        right: "0",
        background: "rgba(0, 0, 0, 0.7)",
        "border-radius": "5px 0px 0px 5px",
        padding: "5px",
        "font-size": "16px",
        "z-index": "999999",
        cursor: "pointer",
        color: "#ddd"
    }).html(" <i class='fa fa-fw fa-gears skin-icon'></i>").addClass("no-print");

    var demo_settings = $("<div />").css({
        "padding": "10px",
        position: "fixed",
        top: "100px",
        right: "-200px",
        background: "#fff",
        border: "3px solid rgba(0, 0, 0, 0.7)",
        "width": "200px",
        "z-index": "999999"
    }).addClass("no-print");
    demo_settings.append(
            "<h4 style='margin: 0 0 5px 0; border-bottom: 1px dashed #ddd; padding-bottom: 3px;'>Skins</h4>"
           
          +"<div class='well'>"
           +"<div class='skinmulti_btn' onclick='loadjscssfile(\"skin-blue.css\",\"css\")'>"
                    +"<div class='skin-blue skin_size'></div>"
                    +"<div class='skin_black skin_size'></div>"
                +"</div>"
             
                +"<div class='skinmulti_btn' onclick='loadjscssfile(\"skin-mint.css\",\"css\")'>"
                    +"<div class='skin-mint skin_size'></div>"
                    +"<div class='skin_black skin_size'></div>"
                +"</div>"
                 
                +"<div class='skinmulti_btn' onclick='loadjscssfile(\"skin-grape.css\",\"css\")'>"
                    +"<div class='skin-grape skin_size'></div>"
                    +"<div class='skin_black skin_size'></div>"
                +"</div>"
                +"<div class='skinmulti_btn' onclick='loadjscssfile(\"skin-lavender.css\",\"css\")'>"
                    +"<div class='skin-lavender skin_size'></div>"
                    +"<div class='skin_black skin_size'></div>"
                +"</div>"
                +"<div class='skinmulti_btn' onclick='loadjscssfile(\"skin-pink.css\",\"css\")'>"
                    +"<div class='skin-pink skin_size'></div>"
                    +"<div class='skin_black skin_size'></div>"
                +"</div>"
                +"<div class='skinmulti_btn' onclick='loadjscssfile(\"skin-sunflower.css\",\"css\")'>"
                    +"<div class='skin-sunflower skin_size'></div>"
                    +"<div class='skin_black skin_size'></div>"
                +"</div>"
                +"<div class='skinmulti_btn' onclick='loadjscssfile(\"skin-blue-gray.css\",\"css\")'>"
                    +"<div class='skin-blue skin_size'></div>"
                    +"<div class='skin_white skin_size'></div>"
                +"</div>"
                +"<div class='skinmulti_btn' onclick='loadjscssfile(\"skin-mint-gray.css\",\"css\")'>"
                    +"<div class='skin-mint skin_size'></div>"
                    +"<div class='skin_white skin_size'></div>"
                +"</div>"
                +"<div class='skinmulti_btn' onclick='loadjscssfile(\"skin-grape-gray.css\",\"css\")'>"
                    +"<div class='skin-grape skin_size'></div>"
                    +"<div class='skin_white skin_size'></div>"
                +"</div>"
                +"<div class='skinmulti_btn' onclick='loadjscssfile(\"skin-lavender-gray.css\",\"css\")'>"
                    +"<div class='skin-lavender skin_size'></div>"
                    +"<div class='skin_white skin_size'></div>"
                +"</div>"
                +"<div class='skinmulti_btn' onclick='loadjscssfile(\"skin-pink-gray.css\",\"css\")'>"
                    +"<div class='skin-pink skin_size'></div>"
                    +"<div class='skin_white skin_size'></div>"
                +"</div>"
                +"<div class='skinmulti_btn' onclick='loadjscssfile(\"skin-sunflower-gray.css\",\"css\")'>"
                    +"<div class='skin-sunflower skin_size'></div>"
                    +"<div class='skin_white skin_size'></div>"
                +"</div>"
                +"</div>"
            );

    demo.click(function() {
        if (!$(this).hasClass("open")) {
            $(this).css("right", "200px");
            demo_settings.css("right", "0");
            $(this).addClass("open");
        } else {
            $(this).css("right", "0");
            demo_settings.css("right", "-200px");
            $(this).removeClass("open")
        }
    });

    $("body").append(demo);
    $("body").append(demo_settings);
});
     function loadjscssfile(filename, filetype) {
        if (filetype == "css") {
            var fileref = document.createElement("link");
            fileref.href = 'css/custom_css/skins/' + filename;
            fileref.rel = "stylesheet";
            fileref.type = "text/css";
            document.getElementsByTagName("head")[0].appendChild(fileref)
        }
    }