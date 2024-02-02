 
$(function() {  
    faq();
});

function faq(){
    function mixitup() {
        $("#faq").mixItUp({
            animation: {
                duration: 300,
                effects: "fade translateZ(-360px) stagger(34ms)",
                easing: "ease",
               
            }
        });
    }

    mixitup();
}