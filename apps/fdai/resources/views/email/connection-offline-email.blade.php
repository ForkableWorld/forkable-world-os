<?php /** @var App\Models\Connection $connection */ ?>
<table width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#ffffff">
    <tbody>
    <tr>
        <td align="left" valign="top">
            <table border="0" cellspacing="0" cellpadding="0">
                <tbody>
                <tr>
                    <td>
                        <p style="clear:both;font-size:24px">
                            Hi {{ $connection->getUser()->getTitleAttribute() }},
                        </p>
                        <p style="clear:both;font-size:24px">We can’t connect to
                            your {{ $connection->getQMConnector()->getTitleAttribute() }}.</p>
                        <p style="clear:both;font-size:24px">It could be because of an update you made, or improvements
                            on the service’s end.</p>
                        <p style="clear:both;font-size:24px;font-weight:bold">Click through to fix it.</p>
                        <table
                                style="background:#23448b;border-radius:0 4.5em 4.5em 0;padding-right:.6em"
                                bgcolor="#23448b"
                        >
                            <tbody>
                            <tr>
                                <td style="width:80px">
                                    <a
                                            href="{{ $connection->getQMConnector()->getConnectUrlWithParams() }}"
                                            style="border:0;color:black;text-decoration:underline"
                                            target="_blank"
                                            data-saferedirecturl="{{ $connection->getQMConnector()->getConnectUrlWithParams() }}"
                                    >
                                        <img
                                                src="{{ $connection->getQMConnector()->getImage() }}"
                                                alt="{{ $connection->getQMConnector()->getTitleAttribute() }}"
                                                title="{{ $connection->getQMConnector()->getTitleAttribute() }}"
                                                align="none"
                                                width="80"
                                                style="border:0;clear:both;display:block;float:none;margin:12px 12px 12px 8px;text-decoration:none;vertical-align:middle"
                                                class="CToWUd"
                                        >
                                    </a>
                                </td>
                                <td>
                                    <a
                                            href="{{ $connection->getQMConnector()->getConnectUrlWithParams() }}"
                                            style="background:black;border-radius:2.5em;border:3px solid transparent;color:#fff;display:inline-block;font-family:'AvenirNext-Bold','AvenirNext','Avenir','Helvetica Neue','Helvetica','Arial','sans-serif';font-size:36px;font-weight:bold;line-height:1;margin:0 auto;padding:.5em 1.5em;text-align:center;text-decoration:none;vertical-align:middle;white-space:nowrap"
                                            target="_blank"
                                            data-saferedirecturl="{{ $connection->getQMConnector()->getConnectUrlWithParams() }}"
                                    >
                                        Fix it
                                    </a>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                        <hr style="background:none;border-bottom-color:#f5f5f5;border-bottom-style:solid;border-width:0 0 3px;height:0;margin:88px 0 30px;padding:32px 0 0px">
                    </td>
                </tr>
                </tbody>
            </table>
        </td>
    </tr>
    </tbody>
</table>
