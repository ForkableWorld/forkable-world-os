console.log("USER AGENT: "+navigator.userAgent);
if(typeof bugsnag !== "undefined"){
    window.bugsnagClient = bugsnag('3de50ea1404eb28810229d41cfe30603')
}
if(window.location.href.indexOf("app.quantimo.do") > -1){
    window.LogRocket && window.LogRocket.init('mkcthl/quantimodo');
}
