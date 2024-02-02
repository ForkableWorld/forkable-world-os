<div id="android-desc" class="platform-desc" style="display: block;"><p>Import the Connect SDK Module (Android Studio)</p>
    <ul>
        <li>Copy<code class="language- language-undefined"> humanapi-sdk</code> folder somewhere within your project's app folder</li>
        <li>Import humanapi-sdk as a module for your application</li>
        <li>Go to<code class="language- language-undefined"> File -&gt; New -&gt; Import Module -&gt; Source Directory</code> &amp; browse for humanapi-sdk folder</li>
        <li>Specify the Module Name as<code class="language- language-undefined"> :humanapi-sdk</code> and let Android Studio build the project</li>
        <li>Open<code class="language- language-undefined">build.gradle (Module:app)</code> file and add the following line in the dependencies block:
            <pre class=" language-undefined"><code class="language- language-undefined">compile project(':humanapi-sdk')</code></pre>
        </li>
        <li>Press the “sync now” link to start a sync of gradle files</li>
        <li>If not already present, be sure to add internet permissions to your app's AndroidManifest.xml file:
            <pre class=" language-undefined"><code class="language- language-undefined">&lt;uses-permission android:name='android.permission.INTERNET' /&gt;</code></pre>
        </li>
    </ul>
    <br>
    <p>Launch {{app_display_name()}} Integration</p>
    <pre class=" language-java"><code class=" language-java"><span class="token keyword">public</span> <span class="token keyword">void</span> <span class="token function">onConnect</span><span
                    class="token punctuation">(</span>View view<span class="token punctuation">)</span> <span class="token punctuation">{</span>
  Intent intent <span class="token operator">=</span> <span class="token keyword">new</span> <span class="token class-name">Intent</span><span class="token punctuation">(</span><span
                    class="token keyword">this</span><span class="token punctuation">,</span> co<span class="token punctuation">.</span>humanapi<span class="token punctuation">.</span>connectsdk<span
                    class="token punctuation">.</span>ConnectActivity<span class="token punctuation">.</span><span class="token keyword">class</span><span class="token punctuation">)</span><span
                    class="token punctuation">;</span>
  Bundle b <span class="token operator">=</span> <span class="token keyword">new</span> <span class="token class-name">Bundle</span><span class="token punctuation">(</span><span
                    class="token punctuation">)</span><span class="token punctuation">;</span>

  b<span class="token punctuation">.</span><span class="token function">putString</span><span class="token punctuation">(</span><span class="token string">"client_user_id"</span><span class="token punctuation">,</span> <span
                    class="token string">"test_user@gmail.com"</span><span class="token punctuation">)</span><span class="token punctuation">;</span>
  b<span class="token punctuation">.</span><span class="token function">putString</span><span class="token punctuation">(</span><span class="token string">"client_id"</span><span
                    class="token punctuation">,</span> <span class="token string">""</span><span class="token punctuation">)</span><span class="token punctuation">;</span>
  b<span class="token punctuation">.</span><span class="token function">putString</span><span class="token punctuation">(</span><span class="token string">"auth_url"</span><span
                    class="token punctuation">,</span> <span class="token string">"https://yourdomain.com/endpoint/to/send/sessionTokenObject"</span><span class="token punctuation">)</span><span
                    class="token punctuation">;</span>

  <span class="token comment" spellcheck="true">/* PublicToken (mandatory for existing users) */</span>
  <span class="token comment" spellcheck="true">//b.putString("public_token", "e56fa0350866bcf266da442cb974d84e");</span>

  intent<span class="token punctuation">.</span><span class="token function">putExtras</span><span class="token punctuation">(</span>b<span class="token punctuation">)</span><span
                    class="token punctuation">;</span>
  <span class="token function">startActivityForResult</span><span class="token punctuation">(</span>intent<span class="token punctuation">,</span> HUMANAPI_AUTH<span class="token punctuation">)</span><span
                    class="token punctuation">;</span>
<span class="token punctuation">}</span></code></pre>
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
        </tbody>
    </table>
    <br>
    <p>Implement Callback functions</p>
    <pre class=" language-java"><code class=" language-java">@Override
            <span class="token keyword">protected</span> <span class="token keyword">void</span> <span class="token function">onActivityResult</span><span class="token punctuation">(</span><span
                    class="token keyword">int</span> requestCode<span class="token punctuation">,</span> <span class="token keyword">int</span> resultCode<span
                    class="token punctuation">,</span> Intent data<span class="token punctuation">)</span> <span class="token punctuation">{</span>
  <span class="token keyword">if</span> <span class="token punctuation">(</span>requestCode <span class="token operator">!=</span> HUMANAPI_AUTH<span class="token punctuation">)</span> <span
                    class="token punctuation">{</span>
  <span class="token keyword">return</span><span class="token punctuation">;</span> <span class="token comment" spellcheck="true">// incorrect code</span>
  <span class="token punctuation">}</span>

  <span class="token keyword">if</span> <span class="token punctuation">(</span>resultCode <span class="token operator">==</span> RESULT_OK<span class="token punctuation">)</span> <span
                    class="token punctuation">{</span>
    Log<span class="token punctuation">.</span><span class="token function">d</span><span class="token punctuation">(</span><span class="token string">"hapi-home"</span><span class="token punctuation">,</span> <span
                    class="token string">"Authorization workflow completed"</span><span class="token punctuation">)</span><span class="token punctuation">;</span>
    Bundle b <span class="token operator">=</span> data<span class="token punctuation">.</span><span class="token function">getExtras</span><span class="token punctuation">(</span><span
                    class="token punctuation">)</span><span class="token punctuation">;</span>
    Log<span class="token punctuation">.</span><span class="token function">d</span><span class="token punctuation">(</span><span class="token string">"hapi-home"</span><span class="token punctuation">,</span> <span
                    class="token string">".. public_token="</span> <span class="token operator">+</span> b<span class="token punctuation">.</span><span class="token function">getString</span><span
                    class="token punctuation">(</span><span class="token string">"public_token"</span><span class="token punctuation">)</span><span class="token punctuation">)</span><span
                    class="token punctuation">;</span>
  <span class="token punctuation">}</span> <span class="token keyword">else</span> <span class="token keyword">if</span> <span class="token punctuation">(</span>resultCode <span class="token operator">==</span> RESULT_CANCELED<span
                    class="token punctuation">)</span> <span class="token punctuation">{</span>
    Log<span class="token punctuation">.</span><span class="token function">d</span><span class="token punctuation">(</span><span class="token string">"hapi-home"</span><span class="token punctuation">,</span> <span
                    class="token string">"Authorization workflow cancelled"</span><span class="token punctuation">)</span><span class="token punctuation">;</span>
  <span class="token punctuation">}</span>
<span class="token punctuation">}</span></code></pre>
    <table>
        <tbody>
        <tr>
            <td>RESULT_OK</td>
            <td>User successfully connected a data source.<code> publicToken</code> will be returned here after the next step.</td>
        </tr>
        <tr>
            <td>RESULT_CANCELED</td>
            <td>(optional) User closed {{app_display_name()}} Integration without connecting a source</td>
        </tr>
        </tbody>
    </table>
    <br>
    <p>Nice! Next step is to finalize the authentication process from the<code class="language- language-undefined"> auth_url</code>.</p></div>