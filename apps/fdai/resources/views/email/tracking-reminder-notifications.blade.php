<tbody>
<tr>
    <td align="center" valign="top" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">
        <!-- BEGIN HEADER // -->
        <table border="0" cellpadding="0" cellspacing="0" width="600" id="templateHeader" style="border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;min-width: 100%;background-color: #FFFFFF;border-top: 0;border-bottom: 0;">
            <tbody><tr>
                <td valign="top" class="headerContainer" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">

                    Hi, {{ $userName }}!
                    <br> <br>
                    {{ $trackingMessage }}
                    @include('email.gap')
                    @include('email.inbox-button')
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