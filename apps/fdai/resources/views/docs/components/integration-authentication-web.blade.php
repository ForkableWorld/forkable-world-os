<br>
<div id="web-desc" class="platform-desc" style="display: block;"><p>To integrate
        with {{ app_display_name() }}, you can either use the floating button shown in the lower right
        corner or add a button to your site's html to launch the {{ app_display_name() }}
        Integration.</p>
    <br>


    <p><span class="snark-text"> Example Button</span></p>
    <pre class=" language-html"><code class=" language-html"><span class="token tag"><span class="token tag"><span
                        class="token punctuation">&lt;</span>img</span> <span class="token attr-name">id</span><span
                    class="token attr-value"><span class="token punctuation">=</span><span
                        class="token punctuation">'</span>qm-integration-button<span class="token punctuation">'</span></span> <span
                    class="token attr-name">src</span><span class="token attr-value"><span
                        class="token punctuation">=</span><span
                        class="token punctuation">'</span>https://app.quantimo.do/qm-connect/connect.png<span
                        class="token punctuation">'</span></span><span
                    class="token punctuation">/&gt;</span></span></code></pre>
    <br>

    <img style="cursor: pointer;" id='qm-integration-button' src='https://app.quantimo.do/qm-connect/connect.png' alt=""/>
    <br>
    <br>
    <p>Launch Connect w/ options</p>
    <table>
        <tbody>
        <tr>
            <td>clientUserId</td>
            <td>&nbsp; Unique ID of user on your system (we send this back at the end)</td>
        </tr>
        <tr>
            <td>clientId</td>
            <td>&nbsp; Unique ID of your {{app_display_name()}} App (found on your app settings
                page)
            </td>
        </tr>
        <tr>
            <td>publicToken</td>
            <td>&nbsp; This is important! (but not yet. it is required for returning users)</td>
        </tr>
        <tr>
            <td>finish()</td>
            <td>&nbsp; Required. Called every time a user connects data.</td>
        </tr>
        <tr>
            <td>close()</td>
            <td>&nbsp; Optional. Called when a user closes the popup without connecting any data sources</td>
        </tr>
        <tr>
            <td>error()</td>
            <td>&nbsp; Optional. Called if an error occurs when loading the popup.</td>
        </tr>
        </tbody>
    </table>
    <br>
    <p>The last step here is to POST the<code class="language- language-undefined"> sessionTokenObject</code> as-is to
        an endpoint on your server for the token exchange in the next section.</p>

    <pre>
        {{ qm_integration_loader_and_options() }}
     </pre>
</div>