@include('email.blocks-email-header')
<body class="body" style="padding:0; margin:0; display:block; background:#ffffff; -webkit-text-size-adjust:none" bgcolor="#ffffff">
<table align="center" cellpadding="0" cellspacing="0" class="force-full-width" height="100%" >
    <tr>
        <td align="center" valign="top" bgcolor="#ffffff"  width="100%">
            <center>
                <table style="margin: 0 auto;" cellpadding="0" cellspacing="0" width="600" class="w320">
                    <tr>
                        <td align="center" valign="top">

                            <table style="margin: 0 auto;" cellpadding="0" cellspacing="0" class="force-full-width" style="margin:0 auto;">
                                <tr>
                                    <td style="font-size: 30px; text-align:center;">
                                        <br>
                                        {{app_display_name()}}
                                        <br>
                                        <br>
                                    </td>
                                </tr>
                            </table>

                            <table style="margin: 0 auto;" cellpadding="0" cellspacing="0" class="force-full-width" bgcolor="#4dbfbf">
                                <tr>
                                    <td>
                                        <br>
                                        <img src="https://www.filepicker.io/api/file/carctJpuT0exMaN8WUYQ" width="224" height="240" alt="robot picture">
                                    </td>
                                </tr>
                                <tr>
                                    <td class="headline">
                                        Good News!
                                    </td>
                                </tr>
                                <tr>
                                    <td>

                                        <center>
                                            <table style="margin: 0 auto;" cellpadding="0" cellspacing="0" width="60%">
                                                <tr>
                                                    <td style="color:#187272;">
                                                        <br>
                                                        Your order has shipped! To track your order or make any changes please click the button below.
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
                                               style="background-color:#178f8f;border-radius:4px;color:#ffffff;display:inline-block;font-family:Helvetica, Arial, sans-serif;font-size:16px;font-weight:bold;line-height:50px;text-align:center;text-decoration:none;width:200px;-webkit-text-size-adjust:none;">My Order</a>
                                            <!--[if mso]>
                                            </center>
                                            </v:roundrect>
                                            <![endif]--></div>
                                        <br>
                                        <br>
                                    </td>
                                </tr>
                            </table>

                            <table style="margin: 0 auto;" cellpadding="0" cellspacing="0" class="force-full-width" bgcolor="#f5774e">
                                <tr>
                                    <td style="background-color:#f5774e;">

                                        <center>
                                            <table style="margin:0 auto;" cellspacing="0" cellpadding="0" class="force-width-80">
                                                <tr>
                                                    <td style="text-align:left; color:#933f24">
                                                        <br>
                                                        <br>
                                                        <span style="color:#ffffff;">Bob Erlicious</span> <br>
                                                        123 Flower Drive <br>
                                                        Victoria, BC <br>
                                                        V9P 2E8 <br>
                                                        1(250)222-2232
                                                    </td>
                                                    <td style="text-align:right; vertical-align:top; color:#933f24">
                                                        <br>
                                                        <br>
                                                        <span style="color:#ffffff;">Invoice: 00234</span> <br>
                                                        April 3, 2014
                                                    </td>
                                                </tr>
                                            </table>


                                            <table style="margin:0 auto;" cellspacing="0" cellpadding="0" class="force-width-80">
                                                <tr>
                                                    <td  class="mobile-block" >
                                                        <br>
                                                        <br>

                                                        <table cellspacing="0" cellpadding="0" class="force-full-width">
                                                            <tr>
                                                                <td style="color:#ffffff; background-color:#ac4d2f; padding: 10px 0px;">
                                                                    Expected Delivery Date
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td style="color:#933f24; padding:10px 0px; background-color: #f7a084;">
                                                                    Monday, 13th November 2014
                                                                </td>
                                                            </tr>
                                                        </table>

                                                        <br>
                                                    </td>
                                                </tr>
                                            </table>



                                            <table style="margin: 0 auto;" cellspacing="0" cellpadding="0" class="force-width-80">
                                                <tr>
                                                    <td style="text-align:left; color:#933f24;">
                                                        <br>
                                                        Thank you for your business. Please <a style="color:#ffffff;" href="#">contact us</a> with any questions regarding your order.
                                                        <br>
                                                        <br>
                                                        {{app_display_name()}}
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