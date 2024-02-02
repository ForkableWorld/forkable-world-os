@include('email.blocks-email-header')
<body class="body" style="padding:0; margin:0; display:block; background:#ffffff; -webkit-text-size-adjust:none" bgcolor="#ffffff">
<table align="center" cellpadding="0" cellspacing="0" width="100%" height="100%" >
    <tr>
        <td align="center" valign="top" bgcolor="#ffffff"  width="100%">
            <center>
                <table style="margin: 0 auto;" cellpadding="0" cellspacing="0" width="600" class="w320">
                    <tr>
                        <td align="center" valign="top">

                            <table style="margin: 0 auto;" cellpadding="0" cellspacing="0" width="100%" style="margin:0 auto;">
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
                                        Update!
                                    </td>
                                </tr>
                                <tr>
                                    <td>

                                        <center>
                                            <table style="margin: 0 auto;" cellpadding="0" cellspacing="0" width="60%">
                                                <tr>
                                                    <td style="color:#187272;">
                                                        <br>
                                                        Your account settings have been updated.
                                                        <br>
                                                        <br>
                                                        <br>
                                                    </td>
                                                </tr>
                                            </table>
                                        </center>

                                    </td>
                                </tr>
                            </table>

                            <table style="margin: 0 auto;" cellpadding="0" cellspacing="0" width="100%" bgcolor="#f5774e">
                                <tr>
                                    <td>
                                        <br>
                                        <img src="https://www.filepicker.io/api/file/hkpp4OzbQme8bszfOs1k" width="113" height="100" alt="meter image">
                                    </td>
                                </tr>
                                <tr>
                                    <td>

                                        <center>
                                            <table style="margin: 0 auto;" cellpadding="0" cellspacing="0" width="60%">
                                                <tr>
                                                    <td style="color:#933f24;">
                                                        Your account settings have been updated. If you did not update your settings,<br> please <a style="color:#ffffff" href="#">contact support</a> <br><br>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td style="color:#933f24;">
                                                        Thanks for being a customer!<br>
                                                        {{app_display_name()}}
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
                                               style="background-color:#ac4d2f;border-radius:4px;color:#ffffff;display:inline-block;font-family: Helvetica, Arial, sans-serif;font-size:16px;font-weight:bold;line-height:50px;text-align:center;text-decoration:none;width:200px;-webkit-text-size-adjust:none;">My Account</a>
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
