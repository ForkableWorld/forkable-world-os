<?php /** @var \App\Slim\Model\User\QMUser $publicUser */ ?>
<table width="100%" class="display-temp active" data-rel="classImgBottom" cellpadding="0" cellspacing="0" border="0"
       style="width:100%;background: none; border-width: 0px; border: 0px; margin: 0; padding: 0;">
    <tbody>
    <tr>
        <td cellpadding="0" cellspacing="0" border="0" valign="middle"
            style="border-left:none;border-top:none;border-bottom:none;padding-right:7px;border-right:solid 3px;border-color:#DD2022;width:100px;">
            <img style="width:100px;border-radius:50%;"
                 src="{{ $publicUser->avatarImage }}">
        </td>
        <td cellpadding="0" cellspacing="0" border="0" valign="top" style="padding-left:7px;">
            <table>
                <tbody>
                <tr>
                    <td colspan="2" style="line-height:1.4;padding-bottom:3px; font-weight: 600;"><span
                            style="color:#DD2022;font-family:Arial, sans-serif;font-size:18px;">{{ $publicUser->displayName }}</span></td>
                </tr>
                <tr>
                    <td colspan="2" style="color:#333333;font-family:Arial, sans-serif;font-size:14px; line-height: 1;">
                        Principal Investigator
                    </td>
                </tr>
                {{--
                <tr>
                    <td colspan="2"
                        style="color:#333333;font-family:Arial, sans-serif;font-size:14px;font-weight: bold;line-height: 1;">
                        QuantiModo
                    </td>
                </tr>

                <tr>
                    <td colspan="2" style="line-height:1.4;"><a
                            style="text-decoration:none;cursor:pointer;display:inline-block;" target="_blank"
                            href="https://https:://quantimo.do"><span
                                style="font-family:Arial, sans-serif;color:#DD2022;font-size:14px;">W:&nbsp;</span><span
                                style="font-family:Arial, sans-serif;color:#1da1db;font-size:14px;margin-right:10px;">https:://quantimo.do</span></a>
                    </td>
                </tr>
                <tr>
                    <td colspan="2" style="line-height:1.4;"><a
                            style="text-decoration:none;cursor:pointer;display:inline-block;" target="_blank"
                            href="mailto:m@thinkbynumbers.org"><span
                                style="font-family:Arial, sans-serif;color:#DD2022;font-size:14px;">E:&nbsp;</span><span
                                style="font-family:Arial, sans-serif;color:#1da1db;font-size:14px;margin-right:10px;">m@thinkbynumbers.org</span></a>
                    </td>
                </tr>
                --}}
                <tr>
                    <td colspan="2" style="line-height:1;padding-top:5px;padding-bottom:5px;">
{{--
                        <a href="https://www.facebook.com/mikepsinn" style="display:inline-block;margin-right:4px;"
                            target="_blank"><img width="25px" height="25px"
                                                 src="https://cdn1.designhill.com/assets/dh/images/email-signature/social_media/facebook.png"></a>
--}}
                        <a href="https://twitter.com/thinkbynumbers" style="display:inline-block;margin-right:4px;"
                            target="_blank"><img width="25px" height="25px"
                                                 src="https://cdn1.designhill.com/assets/dh/images/email-signature/social_media/twitter.png"></a><a
                            href="https://www.linkedin.com/in/mikesinn" style="display:inline-block;margin-right:4px;"
                            target="_blank"><img width="25px" height="25px"
                                                 src="https://cdn1.designhill.com/assets/dh/images/email-signature/social_media/linkedin.png">
                        </a>
                    </td>
                </tr>
{{--
                <tr>
                    <td><a class="submit-btnlink" target="_blank"
                           style="cursor:pointer;text-decoration:none;padding-left:32px;padding-right:32px;border-radius:25px;margin-top:8px;display:inline-block;vertical-align:middle;height:44px;line-height:44px;text-transform:uppercase;color:#FFFFFF;font-style:normal;font-weight:400;background:#DD2022;font-size:16px;"
                           href="https://quantimo.do/studies">More Studies</a></td>
                </tr>
--}}
                </tbody>
            </table>
        </td>
    </tr>
    </tbody>
</table>
