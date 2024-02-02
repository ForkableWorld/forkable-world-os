<div id="ios-desc" class="platform-desc" style="display: block;"><p>Copy the HumanAPI folder from the<a href="https://github.com/humanapi/humanapi-ios-client" class="secondary action"> master branch of the iOS
            SDK</a> and place it in the base level of your project directory.</p><br>
    <p>In your ViewController.h file, add the following import and protocol:</p>
    <ul>
        <li>
            <pre class=" language-undefined"><code class="language- language-undefined">#import "{{app_display_name()}}IntegrationViewController.h"</code></pre>
        </li>
        <li>
            <pre class=" language-undefined"><code class="language- language-undefined">@interface ViewController : UIViewController &lt;HumanAPINotifications&gt;</code></pre>
        </li>
    </ul>
    <br>
    <p>Launch {{app_display_name()}} Integration</p>
    <pre class=" language-objectivec"><code class=" language-objectivec"><span class="token operator">-</span> <span class="token punctuation">(</span>IBAction<span class="token punctuation">)</span>launch{{app_display_name()}}Integration<span
                    class="token punctuation">:</span><span class="token punctuation">(</span>id<span class="token punctuation">)</span>sender <span class="token punctuation">{</span>

   NSString <span class="token operator">*</span>clientID <span class="token operator">=</span> <span class="token string">@"Your-Client-ID"</span><span class="token punctuation">;</span>
   NSString <span class="token operator">*</span>authURL <span class="token operator">=</span> <span class="token string">@"https://yourdomain.com/endpoint/to/send/sessionTokenObject"</span><span
                    class="token punctuation">;</span>

   {{app_display_name()}}IntegrationViewController <span class="token operator">*</span>hcvc <span class="token operator">=</span> <span class="token punctuation">[</span><span class="token punctuation">[</span>{{app_display_name()}}IntegrationViewController alloc<span
                    class="token punctuation">]</span> initWithClientID<span class="token punctuation">:</span>clientID andAuthURL<span class="token punctuation">:</span>authURL<span
                    class="token punctuation">]</span><span class="token punctuation">;</span>

   hcvc<span class="token punctuation">.</span>delegate <span class="token operator">=</span> <span class="token keyword">self</span><span class="token punctuation">;</span>
   <span class="token punctuation">[</span><span class="token keyword">self</span> presentViewController<span class="token punctuation">:</span>hcvc animated<span class="token punctuation">:</span>YES completion<span
                    class="token punctuation">:</span>nil<span class="token punctuation">]</span><span class="token punctuation">;</span>

   <span class="token comment" spellcheck="true">//Launch {{app_display_name()}} Integration</span>
   <span class="token punctuation">[</span>hcvc startConnectFlowForNewUser<span class="token punctuation">:</span><span class="token string">@"email@your.user"</span><span
                    class="token punctuation">]</span><span class="token punctuation">;</span> <span class="token comment" spellcheck="true">//can be any unique identifier</span>

   <span class="token comment" spellcheck="true">//If you have a publicToken for the user, supply it to {{app_display_name()}} Integration on launch</span>
   <span class="token comment" spellcheck="true">//[hcvc startConnectFlowForNewUser:@"email@your.user"</span>
   <span class="token comment" spellcheck="true">//                  andPublicToken:@"saved-public-token"];</span>
<span class="token punctuation">}</span></code></pre>
    <table>
        <tbody>
        <tr>
            <td>clientId</td>
            <td>Unique ID of your {{app_display_name()}} App (found on your app settings page)</td>
        </tr>
        <tr>
            <td>authURL</td>
            <td>Endpoint on your server to receive object to finish authentication (more on this next)</td>
        </tr>
        <tr>
            <td>publicToken</td>
            <td>Token retrieved from {{app_display_name()}} on first connection. Must be supplied for subsequent Connect launches</td>
        </tr>
        </tbody>
    </table>
    <br>
    <p>Implement the two Connect callbacks (we will be triggering success in the next step):</p>
    <pre class=" language-objectivec"><code class=" language-objectivec"><span class="token operator">-</span> <span class="token punctuation">(</span><span class="token keyword">void</span><span
                    class="token punctuation">)</span>onConnectSuccess<span class="token punctuation">:</span><span class="token punctuation">(</span>NSString <span class="token operator">*</span><span
                    class="token punctuation">)</span>publictoken <span class="token punctuation">{</span>
  <span class="token function">NSLog</span><span class="token punctuation">(</span><span class="token string">@"Connect success!  publicToken=%@"</span><span class="token punctuation">,</span> publicToken<span
                    class="token punctuation">)</span><span class="token punctuation">;</span>
  <span class="token comment" spellcheck="true">//Store publicToken with user model for subsequent {{app_display_name()}}Integration launches</span>
<span class="token punctuation">}</span>

<span class="token operator">-</span> <span class="token punctuation">(</span><span class="token keyword">void</span><span class="token punctuation">)</span>onConnectFailure<span
                    class="token punctuation">:</span><span class="token punctuation">(</span>NSString <span class="token operator">*</span><span class="token punctuation">)</span>error <span
                    class="token punctuation">{</span>
  <span class="token function">NSLog</span><span class="token punctuation">(</span><span class="token string">@"Connect failure: %@"</span><span class="token punctuation">,</span> error<span
                    class="token punctuation">)</span><span class="token punctuation">;</span>
  <span class="token comment" spellcheck="true">//(Optional) Called whenever Connect fails or the user closes w/out</span>
  <span class="token comment" spellcheck="true">//connecting a data source.</span>
<span class="token punctuation">}</span></code></pre>
    <br>
    <p>Nice! Next step is to finalize the authentication process from the<code class="language- language-undefined">authURL</code></p></div>