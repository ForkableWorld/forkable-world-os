<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <!-- Designed by https://github.com/kaytcat -->
    <!-- Robot header image designed by Freepik.com -->

    <style type="text/css">
        @import url(http://fonts.googleapis.com/css?family=Droid+Sans);

        /* Take care of image borders and formatting */

        img {
            max-width: 600px;
            outline: none;
            text-decoration: none;
            -ms-interpolation-mode: bicubic;
        }

        a {
            text-decoration: none;
            border: 0;
            outline: none;
            color: #F0F8FF !important;
            /*color: #ffffff !important;  Can't use white because Gmail won't allow white links to prevent spam or something*/
        }

        a img {
            border: none;
        }

        /* General styling */

        td, h1, h2, h3 {
            font-family: Helvetica, Arial, sans-serif;
            font-weight: 400;
        }

        td {
            text-align: center;
        }

        body {
            -webkit-font-smoothing: antialiased;
            -webkit-text-size-adjust: none;
            width: 100%;
            height: 100%;
            color: #37302d;
            background: #ffffff;
            font-size: 16px;
        }

        table {
            border-collapse: collapse !important;
        }

        .headline {
            color: #ffffff;
            font-size: 24px;
            padding-right: 10px;
            padding-left: 10px;
        }

        .force-full-width {
            width: 100% !important;
        }

        .force-width-80 {
            width: 80% !important;
        }

        .pusher {
            width: 55px;
        }

        .steps {
            width: 43px;
        }


    </style>

    <style type="text/css" media="screen">
        @media screen {
            /*Thanks Outlook 2013! http://goo.gl/XLxpyl*/
            td, h1, h2, h3 {
                font-family: 'Droid Sans', 'Helvetica Neue', 'Arial', 'sans-serif' !important;
            }
        }
    </style>

    <style type="text/css" media="only screen and (max-width: 480px)">
        /* Mobile styles */
        @media only screen and (max-width: 480px) {

            table[class="w320"] {
                width: 320px !important;
            }

            td[class="mobile-block"] {
                width: 100% !important;
                display: block !important;
            }

            td[class="pusher"] {
                width: 0 !important;
            }

            img[class="steps"] {
                width: 28px !important;
            }
        }
    </style>
</head>
<tr>
    <td align="center" valign="top"
        style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">
        <!-- BEGIN HEADER // -->
        <table border="0" cellpadding="0" cellspacing="0" width="600" id="templateHeader"
               style="border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;min-width: 100%;background-color: #FFFFFF;border-top: 0;border-bottom: 0;">
            <tbody>
            <tr>
                <td valign="top" class="headerContainer"
                    style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">
                    <table border="0" cellpadding="0" cellspacing="0" width="100%" class="mcnTextBlock"
                           style="min-width: 100%;border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">
                        <tbody class="mcnTextBlockOuter">
                        <tr>
                            <td valign="top" class="mcnTextBlockInner"
                                style="padding-top: 9px;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">
                                <!--[if mso]>
                                <table align="left" border="0" cellspacing="0" cellpadding="0" width="100%"
                                       style="width:100%;">
                                    <tr>
                                <![endif]-->

                                <!--[if mso]>
                                <td valign="top" width="600" style="width:600px;">
                                <![endif]-->
                                <table align="left" border="0" cellpadding="0" cellspacing="0"
                                       style="max-width: 100%;min-width: 100%;border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;"
                                       width="100%" class="mcnTextContentContainer">
                                    <tbody>
                                    <tr>

                                        <td valign="top" class="mcnTextContent"
                                            style="padding-top: 0;padding-right: 18px;padding-bottom: 9px;padding-left: 18px;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;color: #606060;font-family: Helvetica;font-size: 15px;line-height: 150%;text-align: left;">

                                            <div
                                                style="margin: 0px;padding: 0px;border: 0px;vertical-align: baseline;color: #222222;font-family: Arial, Helvetica, sans-serif;line-height: normal;">
                                                &nbsp;
                                            </div>
                                            <h2 style="text-align: center">
                                                Thank you for joining our study!
                                            </h2>
                                            <p>
                                                By taking a few seconds to answer 2 questions each day, you'll be
                                                helping us to discover new ways to reduce suffering. As you accumulate
                                                data, you'll also be able to see how hidden factors are influencing your
                                                own health and happiness!
                                            </p>
                                            <p>
                                                The first step is to install
                                                the {{app_display_name()}}
                                                mobile app and/or Chrome browser extension.
                                            </p>
                                            @include('email.downloads-buttons-table')
                                            <div
                                                style="margin: 0px;padding: 0px;border: 0px;vertical-align: baseline;color: #222222;font-family: Arial, Helvetica, sans-serif;line-height: normal;">
                                                <div
                                                    style="margin: 0px; padding: 0px; border: 0px; vertical-align: baseline;">
                                                    &nbsp;
                                                </div>
                                            </div>

                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                                <!--[if mso]>
                                </td>
                                <![endif]-->

                                <!--[if mso]>
                                </tr>
                                </table>
                                <![endif]-->
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
            </tbody>
        </table>
        <!-- // END HEADER -->
    </td>
</tr>
</tbody>
@include('email.footer-general')
</body>
</html>