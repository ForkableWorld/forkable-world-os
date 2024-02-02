<head>
    <title>How are you?</title>
    <style>
        .rating-section img { width: 15%; display: inline-block; padding-left: 4px;
            padding-right: 4px; background-color: #FFFFFF; transition: all 200ms linear; }
        .rating-section img:hover { -webkit-filter: brightness(110%) saturate(125%); }
    </style>
</head>
<tbody>
<br>
<br>
<br>
<br>
<tr>
    <td align="center" valign="top" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">
        <!-- BEGIN HEADER // -->
        <table border="0" cellpadding="0" cellspacing="0" width="600" id="templateHeader" style="border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;min-width: 100%;background-color: #FFFFFF;border-top: 0;border-bottom: 0;">
            <tbody><tr>
                <td valign="top" class="headerContainer" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">

                    @include('components.buttons.rating-face-buttons')
                    @include('email.gap')
                    @include('email.gap')
                    @include('email.gap')
                    @include('email.gap')
                    @include('email.divider')
                    @include('download-buttons')
                    @include('email.social')
                </td>
            </tr>
            </tbody></table>
        <!-- // END HEADER -->
    </td>
</tr>
@include('email.footer-reminders')
</tbody>
