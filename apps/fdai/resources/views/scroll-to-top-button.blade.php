<style>
    #scroll-to-top-button {
        display: none; /* Hidden by default */
        position: fixed; /* Fixed/sticky position */
        bottom: 70px; /* Place the button at the bottom of the page */
        right: 10px; /* Place the button 30px from the right */
        z-index: 99; /* Make sure it does not overlap */
        border: none; /* Remove borders */
        outline: none; /* Remove outline */
        background-color: red; /* Set a background color */
        color: white; /* Text color */
        cursor: pointer; /* Add a mouse pointer on hover */
        padding: 9px; /* Some padding */
        border-radius: 999px; /* Rounded corners */
        font-size: 18px; /* Increase font size */
    }

    #scroll-to-top-button:hover {
        background-color: #555; /* Add a dark-grey background on hover */
    }
</style>
<button onclick="topFunction()" id="scroll-to-top-button" title="Go to top">⮝</button>
<script>
    //Get the button:
    mybutton = document.getElementById("scroll-to-top-button");
    // When the user scrolls down 20px from the top of the document, show the button
    window.onscroll = function () {
        scrollFunction()
    };

    function scrollFunction() {
        if (document.body.scrollTop > 20 || document.documentElement.scrollTop > 20) {
            mybutton.style.display = "block";
        } else {
            mybutton.style.display = "none";
        }
    }

    // When the user clicks on the button, scroll to the top of the document
    function topFunction() {
        document.body.scrollTop = 0; // For Safari
        document.documentElement.scrollTop = 0; // For Chrome, Firefox, IE and Opera
    }
</script>