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
                                        Automate Your Tracking
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
                                        <img src="https://static.quantimo.do/img/variable_categories/sleep.png"
                                             width="200" height="200" alt="robot picture">
                                    </td>
                                </tr>
                                <tr>
                                    <td class="headline">
                                        See What's Affecting Your Sleep Quality
                                    </td>
                                </tr>
                                <tr>
                                    <td>

                                        <center>
                                            <table style="margin: 0 auto;"cellpadding="0" cellspacing="0" width="60%">
                                                <tr>
                                                    <td style="color:#187272; text-align:center;">
                                                        <br>
                                                        Fitbit automatically records your time asleep, time taken to fall asleep,
                                                        number of awakenings during the night.  With this data, we can
                                                        identify the factors most likely to improve or worsen your sleep.
                                                        <br>
                                                        <br>
                                                        Fitbit also records the time you go to sleep so we can try to identify
                                                        the optimal sleep start time for you.
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
                                            <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="http://amzn.to/2mxHiTr" style="height:50px;v-text-anchor:middle;width:200px;" arcsize="8%" stroke="f" fillcolor="#178f8f">
                                                <w:anchorlock/>
                                                <center>
                                            <![endif]-->
                                            <a href="http://amzn.to/2mxHiTr"
                                               style="background-color:#178f8f;border-radius:4px;color:#ffffff;display:inline-block;font-family:Helvetica, Arial, sans-serif;font-size:16px;font-weight:bold;line-height:50px;text-align:center;text-decoration:none;width:200px;-webkit-text-size-adjust:none;">Get My Fitbit Now</a>
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
                                    <td style="background-color:#f5774e; padding-left: 20px; padding-right: 20px;" class="headline">
                                        <br>
                                        Automated Activity Tracking<br>
                                        <span style="color:#933f24; font-size: 18px;">See if you're meeting your activity goals and if exercise is helping or hurting you!</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        {{--<img src="https://www.filepicker.io/api/file/gwzDDKxQLePzJovbT6O0" width="147" height="121" alt="meter image">--}}
                                        <img src="https://static.quantimo.do/img/variable_categories/physical-activity.png"
                                             width="200" height="200" alt="robot picture">
                                    </td>
                                </tr>
                                <tr>
                                    <td>

                                        <center>
                                            <table style="margin: 0 auto;" cellpadding="0" cellspacing="0" width="45%">
                                                <tr>
                                                    <td style="color:#933f24; text-align:center;" >
                                                        Automatically track<br>
                                                        - Heart Rate<br>
                                                        - Step Count <br>
                                                        - Calories Burned <br>
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
                                            <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="http://amzn.to/2mxHiTr" style="height:50px;v-text-anchor:middle;width:200px;" arcsize="8%" stroke="f" fillcolor="#ac4d2f">
                                                <w:anchorlock/>
                                                <center>
                                            <![endif]-->
                                            <a href="http://amzn.to/2mxHiTr"
                                               style="background-color:#ac4d2f;border-radius:4px;color:#ffffff;display:inline-block;font-family: Helvetica, Arial, sans-serif;font-size:16px;font-weight:bold;line-height:50px;text-align:center;text-decoration:none;width:200px;-webkit-text-size-adjust:none;">Get My Fitbit Now</a>
                                            <!--[if mso]>
                                            </center>
                                            </v:roundrect>
                                            <![endif]--></div>
                                        <br>
                                        <br>
                                    </td>
                                </tr>
                            </table>


                            @include('email.import-data')


                        </td>
                    </tr>
                </table>
            </center>
        </td>
    </tr>
</table>
</body>
</html>
