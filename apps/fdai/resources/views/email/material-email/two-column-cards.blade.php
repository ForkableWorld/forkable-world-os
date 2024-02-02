<?php /** @noinspection PhpFullyQualifiedNameUsageInspection */
/** @var \App\Models\WpPost $left */
/** @var \App\Models\WpPost $right */
?>
<!-- START COLUMNS -->
<tr>
    <td width="100%" valign="top" align="center" class="padding-container">
        <table width="600" cellpadding="0" cellspacing="0" border="0" align="center" class="wrapper" bgcolor="#eeeeee"
               style="max-width: 600px;">
            <tr>
                <td width="100%" align="center">
                    <table width="600" cellpadding="0" cellspacing="0" border="0" class="container">
                        <!-- 2 COLUMNS -->
                        <tr>
                            <td align="center" class="wrapper" style="max-width:600px!important;">
                                <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                    <tbody>
                                    <tr>
                                        <td>
                                            <!-- LEFT COLUMN -->
                                            <table align="left" border="0" cellpadding="0" cellspacing="0" class="stack"
                                                   width="290px" bgcolor="#eeeeee">
                                                <tr>
                                                    <td style="padding: 0px 0px 18px 0px;">
                                                        <table border="0" cellpadding="0" cellspacing="0" class="card-1"
                                                               style="border-bottom: 2px solid #d4d4d4;">
                                                            <tbody>
                                                            <tr>
                                                                <td class="ripplelink" align="left" width="600">
                                                                    <img
                                                                        src="{{ $left->image ??  "http://paulgoddarddesign.com/emails/images/material-design/material-header-4.jpg" }}"
                                                                        width="600"
                                                                        style="border-radius: 3px 3px 0px 0px; display: block; border: 0px; width: 100%; max-width: 600px;"/>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td bgcolor="#ffffff" class="td-padding" align="left"
                                                                    style="font-family: 'Roboto Mono', monospace; color: #212121!important; font-size: 24px; line-height: 30px; padding-top: 18px; padding-left: 18px!important; padding-right: 18px!important; padding-bottom: 0px!important; mso-line-height-rule: exactly; mso-padding-alt: 18px 18px 0px 13px;">
                                                                    {{ $left->post_title }}
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td bgcolor="#ffffff" class="td-padding" align="left"
                                                                    style="font-family: 'Roboto Mono', monospace; color: #212121!important; font-size: 16px; line-height: 24px; padding-top: 18px; padding-left: 18px!important; padding-right: 18px!important; padding-bottom: 0px!important; mso-line-height-rule: exactly; mso-padding-alt: 18px 18px 0px 18px;">
                                                                    {{ $left->post_excerpt }}
                                                                </td>
                                                            </tr>
                                                            <!-- END BODY COPY -->
                                                            <!-- BUTTON -->
                                                            <tr>
                                                                <td bgcolor="#ffffff" align="left"
                                                                    style="padding: 18px 18px 18px 18px; mso-alt-padding: 18px 18px 18px 18px!important;">
                                                                    <table width="100%" border="0" cellspacing="0"
                                                                           cellpadding="0">
                                                                        <tr>
                                                                            <td>
                                                                                <table border="0" cellspacing="0"
                                                                                       cellpadding="0">
                                                                                    <tr>
                                                                                        <td align="left"
                                                                                            style="border-radius: 3px;"
                                                                                            bgcolor="#fc3f1e">
                                                                                            <a class="button raised"
                                                                                               href="{{ $left->guid }}"
                                                                                               target="_blank"
                                                                                               style="font-size: 14px; line-height: 14px; font-weight: 500; font-family: Helvetica, Arial, sans-serif; color: #ffffff; text-decoration: none; border-radius: 3px; padding: 10px 25px; border: 1px solid #fc3f1e; display: inline-block;">MORE INFO</a>
                                                                                        </td>
                                                                                    </tr>
                                                                                </table>
                                                                            </td>
                                                                        </tr>
                                                                    </table>
                                                                </td>
                                                            </tr>
                                                            <!-- END BUTTON -->
                                                            </tbody>
                                                        </table>
                                                    </td>
                                                </tr>
                                            </table>
                                            <!-- END LEFT -->
                                            <!-- RIGHT COLUMN -->
                                            <table align="right" border="0" cellpadding="0" cellspacing="0"
                                                   class="stack" width="290px" bgcolor="#eeeeee">
                                                <tr>
                                                    <td style="padding: 0px 0px 18px 0px;">
                                                        <table border="0" cellpadding="0" cellspacing="0" class="card-1"
                                                               style="border-bottom: 2px solid #d4d4d4;">
                                                            <tbody>
                                                            <tr>
                                                                <td class="ripplelink" align="left" width="600">
                                                                    <img
                                                                        src="{{ $right->image ??  "http://paulgoddarddesign.com/emails/images/material-design/material-header-5.jpg" }}"
                                                                        width="600"
                                                                        style="border-radius: 3px 3px 0px 0px; display: block; border: 0px; width: 100%; max-width: 600px;"/>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td bgcolor="#ffffff" class="td-padding" align="left"
                                                                    style="font-family: 'Roboto Mono', monospace; color: #212121!important; font-size: 24px; line-height: 30px; padding-top: 18px; padding-left: 18px!important; padding-right: 18px!important; padding-bottom: 0px!important; mso-line-height-rule: exactly; mso-padding-alt: 18px 18px 0px 13px;">
                                                                    {{ $right->post_title }}
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td bgcolor="#ffffff" class="td-padding" align="left"
                                                                    style="font-family: 'Roboto Mono', monospace; color: #212121!important; font-size: 16px; line-height: 24px; padding-top: 18px; padding-left: 18px!important; padding-right: 18px!important; padding-bottom: 0px!important; mso-line-height-rule: exactly; mso-padding-alt: 18px 18px 0px 18px;">
                                                                    {{ $right->post_excerpt }}
                                                                </td>
                                                            </tr>
                                                            <!-- END BODY COPY -->
                                                            <!-- BUTTON -->
                                                            <tr>
                                                                <td bgcolor="#ffffff" align="left"
                                                                    style="padding: 18px 18px 18px 18px; mso-alt-padding: 18px 18px 18px 18px!important;">
                                                                    <table width="100%" border="0" cellspacing="0"
                                                                           cellpadding="0">
                                                                        <tr>
                                                                            <td>
                                                                                <table border="0" cellspacing="0"
                                                                                       cellpadding="0">
                                                                                    <tr>
                                                                                        <td align="left"
                                                                                            style="border-radius: 3px;"
                                                                                            bgcolor="#25e47a">
                                                                                            <a class="button raised"
                                                                                               href="{{ $right->guid }}"
                                                                                               target="_blank"
                                                                                               style="font-size: 14px; line-height: 14px; font-weight: 500; font-family: Helvetica, Arial, sans-serif; color: #ffffff; text-decoration: none; border-radius: 3px; padding: 10px 25px; border: 1px solid #25e47a; display: inline-block;">MORE INFO</a>
                                                                                        </td>
                                                                                    </tr>
                                                                                </table>
                                                                            </td>
                                                                        </tr>
                                                                    </table>
                                                                </td>
                                                            </tr>
                                                            <!-- END BUTTON -->
                                                            </tbody>
                                                        </table>
                                                    </td>
                                                </tr>
                                            </table>
                                            <!-- END RIGHT -->
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                        <!-- END 2 COLUMNS -->
                    </table>
                </td>
            </tr>
        </table>
    </td>
</tr>
<!-- END COLUMNS -->