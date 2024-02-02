<div id="cordova-desc" class="platform-desc" style="display: block;"><p>Launch {{app_display_name()}} Integration in a
		web view and handle finish and close callback URLs:</p>
	<pre class=" language-javascript"><code class=" language-javascript"><span class="token keyword">var</span> baseURL <span
				class="token operator">=</span> <span class="token string">{{qm_url('/api/v1/embed?')}}</span><span
				class="token punctuation">;</span>
<span class="token keyword">var</span> clientID <span class="token operator">=</span> <span class="token string">'{{$application->client_id}}'</span><span
				class="token punctuation">;</span>
<span class="token keyword">var</span> clientUserId <span class="token operator">=</span> <span class="token string">'someuser@gmail.com'</span><span
				class="token punctuation">;</span>
<span class="token keyword">var</span> publicToken <span class="token operator">=</span> <span class="token keyword">null</span><span
				class="token punctuation">;</span> <span class="token comment"
                                                         spellcheck="true">//Set to publicToken value if previously retrieved or 'null' for new users</span>
<span class="token keyword">var</span> finishURL <span class="token operator">=</span> <span
				class="token string">{{qm_url('/api/v1/connection/finish')}}</span><span
				class="token punctuation">;</span>
<span class="token keyword">var</span> closeURL <span class="token operator">=</span> <span
				class="token string">{{qm_url('/api/v1/window/close')}}</span><span class="token punctuation">;</span>

<span class="token comment" spellcheck="true">//construct URL and launch Connect</span>
<span class="token keyword">var</span> url <span class="token operator">=</span> baseURL <span
				class="token operator">+</span> <span class="token string">'client_id='</span> <span
				class="token operator">+</span> clientID <span class="token operator">+</span> <span
				class="token string">'&amp;client_user_id='</span> <span
				class="token operator">+</span> clientUserId <span class="token operator">+</span> <span
				class="token string">'&amp;finish_url='</span> <span class="token operator">+</span> finishURL <span
				class="token operator">+</span> <span class="token string">'&amp;close_url='</span><span
				class="token operator">+</span> closeURL <span class="token operator">+</span> <span
				class="token punctuation">(</span>publicToken <span class="token operator">!=</span> <span
				class="token keyword">null</span> <span class="token operator">?</span> <span class="token string">"&amp;public_token="</span><span
				class="token operator">+</span> publicToken <span class="token punctuation">:</span> <span
				class="token string">''</span><span class="token punctuation">)</span><span
				class="token punctuation">;</span>
<span class="token keyword">var</span> ref <span class="token operator">=</span> window<span
				class="token punctuation">.</span><span class="token function">open</span><span
				class="token punctuation">(</span>url<span
				class="token punctuation">,</span> <span class="token string">'_blank'</span><span
				class="token punctuation">,</span> <span class="token string">'toolbar=no, location=no'</span><span
				class="token punctuation">)</span><span class="token punctuation">;</span>

<span class="token comment" spellcheck="true">// Handle Connect Callbacks</span>
ref<span class="token punctuation">.</span><span class="token function">addEventListener</span><span
				class="token punctuation">(</span><span class="token string">'loadstart'</span><span
				class="token punctuation">,</span> <span class="token keyword">function</span><span
				class="token punctuation">(</span>event<span class="token punctuation">)</span> <span
				class="token punctuation">{</span>
  <span class="token keyword">if</span> <span class="token punctuation">(</span>event<span
				class="token punctuation">.</span>url<span class="token punctuation">.</span><span
				class="token function">indexOf</span><span class="token punctuation">(</span><span
				class="token string">{{hostOriginWithProtocol()}}</span><span
				class="token punctuation">)</span> <span class="token operator">===</span> <span
				class="token number">0</span><span class="token punctuation">)</span> <span
				class="token punctuation">{</span>
    <span class="token keyword">if</span> <span class="token punctuation">(</span>event<span
				class="token punctuation">.</span>url<span class="token punctuation">.</span><span
				class="token function">indexOf</span><span class="token punctuation">(</span><span class="token string">finishUrl</span><span
				class="token punctuation">)</span> <span
				class="token operator">===</span> <span class="token number">0</span><span
				class="token punctuation">)</span> <span class="token punctuation">{</span>

      <span class="token comment" spellcheck="true">//Create sessionTokenObject from finish url parameters</span>
      <span class="token keyword">var</span> paramString <span class="token operator">=</span> event<span
				class="token punctuation">.</span>url<span class="token punctuation">.</span><span
				class="token function">replace</span><span class="token punctuation">(</span>finishURL<span
				class="token operator">+</span><span class="token string">"?"</span><span
				class="token punctuation">,</span><span class="token string">""</span><span
				class="token punctuation">)</span><span class="token punctuation">;</span>
      <span class="token keyword">var</span> match <span class="token operator">=</span> <span
				class="token string">""</span><span class="token punctuation">;</span>
      <span class="token keyword">var</span> params <span class="token operator">=</span> <span
				class="token punctuation">{</span><span class="token punctuation">}</span><span
				class="token punctuation">;</span>
      <span class="token keyword">var</span> regex <span class="token operator">=</span> <span class="token regex">/([^&amp;=]+)=?([^&amp;]*)/g</span><span
				class="token punctuation">;</span>

      <span class="token keyword">while</span> <span class="token punctuation">(</span>match <span
				class="token operator">=</span> regex<span class="token punctuation">.</span><span
				class="token function">exec</span><span class="token punctuation">(</span>paramString<span
				class="token punctuation">)</span><span class="token punctuation">)</span>
        params<span class="token punctuation">[</span>match<span class="token punctuation">[</span><span
				class="token number">1</span><span class="token punctuation">]</span><span
				class="token punctuation">]</span> <span class="token operator">=</span> match<span
				class="token punctuation">[</span><span class="token number">2</span><span
				class="token punctuation">]</span><span class="token punctuation">;</span>

      <span class="token keyword">var</span> sessionTokenObject <span class="token operator">=</span> <span
				class="token punctuation">{</span>
{{--        <span class="token string">"quantimodoUserId"</span><span class="token punctuation">:</span> params<span
				class="token punctuation">[</span><span class="token string">"quantimodoUserId"</span><span
				class="token punctuation">]</span><span class="token punctuation">,</span>--}}
        <span class="token string">"clientId"</span><span class="token punctuation">:</span> params<span
				class="token punctuation">[</span><span class="token string">"client_id"</span><span
				class="token punctuation">]</span><span class="token punctuation">,</span>
        <span class="token string">"sessionToken"</span><span class="token punctuation">:</span> params<span
				class="token punctuation">[</span><span class="token string">"session_token"</span><span
				class="token punctuation">]</span>
      <span class="token punctuation">}</span>

      <span class="token comment" spellcheck="true">//TODO: Post `sessionTokenObject` to your server to finish auth the process (see next step)</span>
      ref<span class="token punctuation">.</span><span class="token function">close</span><span
				class="token punctuation">(</span><span class="token punctuation">)</span><span
				class="token punctuation">;</span>
    <span class="token punctuation">}</span> <span class="token keyword">else</span> <span
				class="token keyword">if</span> <span class="token punctuation">(</span>event<span
				class="token punctuation">.</span>url<span
				class="token punctuation">.</span><span class="token function">indexOf</span><span
				class="token punctuation">(</span><span class="token string">closeUrl</span><span
				class="token punctuation">)</span> <span class="token operator">===</span> <span
				class="token number">0</span><span
				class="token punctuation">)</span> <span class="token punctuation">{</span>
      <span class="token comment" spellcheck="true">// Optional. Do something on close</span>
      ref<span class="token punctuation">.</span><span class="token function">close</span><span
				class="token punctuation">(</span><span class="token punctuation">)</span><span
				class="token punctuation">;</span>
    <span class="token punctuation">}</span>
  <span class="token punctuation">}</span>
<span class="token punctuation">}</span><span class="token punctuation">)</span><span class="token punctuation">;</span></code></pre>
	<table>
		<tbody>
		<tr>
			<td>clientUserId</td>
			<td>Unique ID of user on your system (we send this back at the end)</td>
		</tr>
		<tr>
			<td>clientId</td>
			<td>Unique ID of your {{app_display_name()}} App (found on your app settings page)</td>
		</tr>
		<tr>
			<td>publicToken</td>
			<td>This is important! (but not yet. it is required for returning users)</td>
		</tr>
		<tr>
			<td>hc-finish</td>
			<td>Required. Called every time a user connects data.</td>
		</tr>
		<tr>
			<td>hc-close</td>
			<td>Optional. Called when a user closes the popup without connecting any data sources</td>
		</tr>
		</tbody>
	</table>
</div>
