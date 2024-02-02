<div id="step3" class="onboarding-step current-step">
	<div class="step-header"><h2>Get User Credentials</h2></div>
	<div class="step-content"><p>Regardless of what platform you launched Connect from, you should now have the
			temporary<code class="language- language-undefined"> sessionTokenObject </code>from Connect on
			your server.<br><br>All you need to do here is add your<code class="language- language-undefined">
				clientSecret</code> to it and POST it back to {{app_display_name()}}
			to get access credentials for the user.<br> No
			matter what or how many sources a user connects, you'll always have just one set of {{app_display_name()}}
			credentials to query data with.<br><br> Pretty neat huh? You should
			save them with the appropriate user model for future use.</p><br>
		<p>Here's an example of how this looks in Node.js:</p>
		<pre class=" language-javascript"><code class=" language-javascript"><span class="token keyword">var</span> request <span
					class="token operator">=</span> <span class="token function">require</span><span
					class="token punctuation">(</span><span class="token string">'request'</span><span
					class="token punctuation">)</span><span class="token punctuation">;</span>
app<span class="token punctuation">.</span><span class="token function">post</span><span
					class="token punctuation">(</span><span class="token string">'/connect/finish'</span><span
					class="token punctuation">,</span> <span class="token keyword">function</span><span
					class="token punctuation">(</span>req<span class="token punctuation">,</span> res<span
					class="token punctuation">)</span> <span class="token punctuation">{</span>
  <span class="token keyword">var</span> sessionTokenObject <span class="token operator">=</span> req<span
					class="token punctuation">.</span>body<span class="token punctuation">;</span>
  sessionTokenObject<span class="token punctuation">.</span>clientSecret <span class="token operator">=</span> <span
					class="token string">'{{$application->client_secret}}'</span><span
					class="token punctuation">;</span>
  <span class="token function">request</span><span class="token punctuation">(</span><span
					class="token punctuation">{</span>
    method<span class="token punctuation">:</span> <span class="token string">'POST'</span><span
					class="token punctuation">,</span>
    uri<span class="token punctuation">:</span> <span class="token string">'https://app.quantimo.do/api/v1/connect/tokens'</span><span
					class="token punctuation">,</span>
    json<span class="token punctuation">:</span> sessionTokenObject
  <span class="token punctuation">}</span><span class="token punctuation">,</span> <span
					class="token keyword">function</span><span class="token punctuation">(</span>err<span
					class="token punctuation">,</span> resp<span
					class="token punctuation">,</span> body<span class="token punctuation">)</span> <span
					class="token punctuation">{</span>
      <span class="token keyword">if</span><span class="token punctuation">(</span>err<span
					class="token punctuation">)</span> <span class="token keyword">return</span> res<span
					class="token punctuation">.</span><span class="token function">send</span><span
					class="token punctuation">(</span><span class="token number">422</span><span
					class="token punctuation">)</span><span class="token punctuation">;</span>
      <span class="token comment" spellcheck="true">// at this point if request was successful, the body object</span>
      <span class="token comment" spellcheck="true">// will have `accessToken` and `publicToken` associated in it
	      .</span>
      <span class="token comment" spellcheck="true">// You should store these fields in your system in association to user's data.</span>
      res<span class="token punctuation">.</span><span class="token function">send</span><span
					class="token punctuation">(</span><span class="token number">201</span><span
					class="token punctuation">)</span><span class="token punctuation">;</span>
      <span class="token comment" spellcheck="true">/* Mobile SDK users - you'll want to send the `publicToken` back to the device here instead */</span>
      <span class="token comment" spellcheck="true">// var responseJSON = {publicToken: body.publicToken};</span>
      <span class="token comment" spellcheck="true">// res.setHeader('Content-Type', 'application/json');</span>
      <span class="token comment" spellcheck="true">// res.status(201).send(JSON.stringify(responseJSON));</span>
    <span class="token punctuation">}</span><span class="token punctuation">)</span><span
					class="token punctuation">;</span>
<span class="token punctuation">}</span><span class="token punctuation">)</span><span class="token punctuation">;</span></code></pre>
	</div>
</div>
