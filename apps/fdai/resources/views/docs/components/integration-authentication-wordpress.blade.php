<br>
<div id="web-desc" class="platform-desc" style="display: block;"><p>To integrate
        with {{app_display_name()}}, you can either use the floating button shown in the lower right
        corner or add a button to your site's html to launch the {{app_display_name()}}
        Integration.</p>
    <br>
    <br>
    <p>Add the following text to the QuantiModo JS code snippet box at
        https://yoursite.com/wp-admin/options-general.php?page=menus.php:</p>
    <pre class=" language-javascript"><code class=" language-javascript">
        <span class="token tag"><span class="token tag"><span class="token punctuation">&lt;</span>script</span> <span
                class="token attr-name">src</span><span class="token attr-value"><span
                    class="token punctuation">=</span><span
                    class="token punctuation">'</span>https://app.quantimo.do/api/v1/integration.js?clientId={{$application->client_id}}<span
                    class="token punctuation">'</span></span><span
                class="token punctuation">&gt;</span></span><span
                class="token script language-javascript"></span><span class="token tag"><span class="token tag"><span
                        class="token punctuation">&lt;/</span>script</span><span
                    class="token punctuation">&gt;</span></span><br>
            <span class="token tag"><span class="token punctuation">&lt;</span>script</span><span
                class="token punctuation">&gt;</span>
            window.QuantiModoIntegration.options <span class="token operator">=</span> <span
                class="token punctuation">{</span>
 //clientUserId<span class="token punctuation">:</span> <span class="token function">encodeURIComponent</span><span
                class="token punctuation">(</span><span
                class="token string">'UNIQUE_ID_FOR_YOUR_USER'</span><span class="token punctuation">)</span><span
                class="token punctuation">,</span>
 clientId<span class="token punctuation">:</span> <span class="token string">'{{$application->client_id}}'</span><span
                class="token punctuation">,</span>
 publicToken<span class="token punctuation">:</span> <span class="token string">''</span><span
                class="token punctuation">,</span>
 finish<span class="token punctuation">:</span> <span class="token keyword">function</span><span
                class="token punctuation">(</span> sessionTokenObject<span
                class="token punctuation">)</span> <span class="token punctuation">{</span>
   <span class="token comment" spellcheck="true">/* Called after user finishes connecting */</span>
   <span class="token comment" spellcheck="true">//POST sessionTokenObject to your server</span>
   <span class="token comment" spellcheck="true">// Include code here to refresh the page.</span>
 <span class="token punctuation">}</span><span class="token punctuation">,</span>
 close<span class="token punctuation">:</span> <span class="token keyword">function</span><span
                class="token punctuation">(</span><span class="token punctuation">)</span> <span
                class="token punctuation">{</span>
     <span class="token comment" spellcheck="true">/* (optional) Called when a user closes the popup without connecting any data sources */</span>
 <span class="token punctuation">}</span><span class="token punctuation">,</span>
 error<span class="token punctuation">:</span> <span class="token keyword">function</span><span
                class="token punctuation">(</span>err<span class="token punctuation">)</span> <span
                class="token punctuation">{</span>
     <span class="token comment"
           spellcheck="true">/* (optional) Called if an error occurs when loading the popup. */</span>
 <span class="token punctuation">}</span>
<span class="token punctuation">}</span>
            <br>
window.QuantiModoIntegration<span class="token punctuation">.</span><span class="token function">createSingleFloatingActionButton</span><span
                class="token punctuation">(</span><span
                class="token punctuation">)</span><span class="token punctuation">;</span>

        <span class="token tag"><span class="token tag"><span class="token punctuation">&lt;/</span>script</span><span
                class="token punctuation">&gt;</span></span>
        </code></pre>
</div>
