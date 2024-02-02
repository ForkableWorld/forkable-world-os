@include('email.blocks-email-header')
<body class="body" style="padding:0; margin:0; display:block; background:#ffffff; -webkit-text-size-adjust:none" bgcolor="#ffffff">
<table align="center" cellpadding="0" cellspacing="0" width="100%" height="100%" >
    <tr>
        <td align="center" valign="top" bgcolor="#ffffff"  width="100%">
            <center>
                <table cellpadding="0" cellspacing="0" width="600" class="w320">
                    <tr>
                        <td align="center" valign="top">

                            <table cellpadding="0" cellspacing="0" width="100%" style="margin:0 auto;">
                                <tr>
                                    <td style="font-size: 30px; text-align:center;">
                                        <br>
                                        {{app_display_name()}}
                                        <br>
                                        <br>
                                    </td>
                                </tr>
                            </table>

                            <table style="margin: 0 auto;" cellpadding="0" cellspacing="0" width="100%" bgcolor="#4dbfbf">
                                <tr>
                                    <td>
                                        <br>
                                        {{--<img src="https://www.filepicker.io/api/file/Pv8CShvQHeBXdhYu9aQE" width="216" height="189" alt="robot picture">--}}
                                        <img src="https://static.quantimo.do/img/robots/quantimodo-robot-waving-700-700.png"
                                             width="200" height="200" alt="robot picture">
                                    </td>
                                </tr>
                                <tr>
                                    <td class="headline">
                                        Time for an upgrade?
                                    </td>
                                </tr>
                                <tr>
                                    <td>

                                        <center>
                                            <table style="margin: 0 auto;"cellpadding="0" cellspacing="0" width="60%">
                                                <tr>
                                                    <td style="color:#187272; text-align:center;">
                                                        <br>
                                                        You won't believe what a difference a tune up can make! Upgrade to a gold account and find out what you've been missing.
                                                        <br>
                                                        <br>
                                                    </td>
                                                </tr>
                                            </table>
                                        </center>

                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div><!--[if mso]>
                                            <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="http://" style="height:50px;v-text-anchor:middle;width:200px;" arcsize="8%" stroke="f" fillcolor="#178f8f">
                                                <w:anchorlock/>
                                                <center>
                                            <![endif]-->
                                            <a href="http://"
                                               style="background-color:#178f8f;border-radius:4px;color:#ffffff;display:inline-block;font-family:Helvetica, Arial, sans-serif;font-size:16px;font-weight:bold;line-height:50px;text-align:center;text-decoration:none;width:200px;-webkit-text-size-adjust:none;">Learn More</a>
                                            <!--[if mso]>
                                            </center>
                                            </v:roundrect>
                                            <![endif]--></div>
                                        <br>
                                        <br>
                                    </td>
                                </tr>
                            </table>

                            <table style="margin:0 auto;" cellpadding="0" cellspacing="0" class="force-full-width" bgcolor="#f5774e">
                                <tr>
                                    <td style="background-color:#f5774e;" class="headline">
                                        <br>
                                        25% off!<br>
                                        <span style="color:#933f24; font-size: 18px;">Premium account upgrade!</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <img src="https://www.filepicker.io/api/file/gwzDDKxQLePzJovbT6O0" width="147" height="121" alt="meter image">
                                    </td>
                                </tr>
                                <tr>
                                    <td>

                                        <center>
                                            <table style="margin: 0 auto;" cellpadding="0" cellspacing="0" width="45%">
                                                <tr>
                                                    <td style="color:#933f24; text-align:center;" >
                                                        - Faster download speeds for all users!<br>
                                                        - No wait times <br>
                                                        - Unlimited downloads <br>
                                                        - Premium quality
                                                        <br><br>
                                                    </td>
                                                </tr>
                                            </table>
                                        </center>

                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div><!--[if mso]>
                                            <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="http://" style="height:50px;v-text-anchor:middle;width:200px;" arcsize="8%" stroke="f" fillcolor="#ac4d2f">
                                                <w:anchorlock/>
                                                <center>
                                            <![endif]-->
                                            <a href="http://"
                                               style="background-color:#ac4d2f;border-radius:4px;color:#ffffff;display:inline-block;font-family: Helvetica, Arial, sans-serif;font-size:16px;font-weight:bold;line-height:50px;text-align:center;text-decoration:none;width:200px;-webkit-text-size-adjust:none;">Upgrade</a>
                                            <!--[if mso]>
                                            </center>
                                            </v:roundrect>
                                            <![endif]--></div>
                                        <br>
                                        <br>
                                    </td>
                                </tr>
                            </table>

                            @include('email.block-brown')





                        </td>
                    </tr>
                </table>
            </center>
        </td>
    </tr>
</table>
</body>
</html>
