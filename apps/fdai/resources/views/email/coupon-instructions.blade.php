<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
</head>
<h3 style="text-align: center;">To redeem your coupon for {{app_display_name()}} Plus, please go to</h3>
<h3 style="text-align: center;">
    <a href="{{getHostAppSettings()->additionalSettings->downloadLinks->webApp}}/upgrade">
        {{getHostAppSettings()->additionalSettings->downloadLinks->webApp}}
        /upgrade
    </a>
</h3>
<h3 style="text-align: center;">in a web browser.</h3>
<p style="text-align: center;">Coupons cannot be redeemed inside the Android or iOS mobile apps because they use a
    different billing method.</p>
<p style="text-align: center;">If you like the app, I'd be eternally grateful for a 5-star review!</p>
<p style="text-align: center;">Otherwise, please let me know what I can do to improve it for you!</p>
&nbsp;
&nbsp;
@include('email.footer-general')
</html>