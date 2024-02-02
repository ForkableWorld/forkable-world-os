!function (targetObject) {
    'use strict';

    function setDisplayNone(loader, bodyElement) {
        try {
            loader.style.display = 'none', bodyElement.removeChild(loader);
        } catch (c) {
            HumanConnect._debug;
        }
    }

    var baseApiUrl = 'https://connect.quantimo.do',
        HumanConnect = {},
        iframeElement = null,
        showLoader = null;
    if (!targetObject.HumanConnect) {
        var bodyElement = document.getElementsByTagName('body')[0] || document.documentElement,
            h = bodyElement.style.overflow,
            handleMessageCallback = function (response) {
                HumanConnect._debug;
                try {
                    var connectResponse = JSON.parse(response.data);
                    if ('qmapi-connect-close' === connectResponse.type && iframeElement && (setDisplayNone(iframeElement, bodyElement),
                            HumanConnect.onClose && HumanConnect.onClose(),
                            showLoader && setDisplayNone(showLoader, bodyElement), bodyElement.style.overflow = h),
                        'qmapi-connect-finish' === connectResponse.type && iframeElement && (iframeElement.style.display = 'none',
                            HumanConnect.onFinish && HumanConnect.onFinish(null, connectResponse.token),
                            showLoader && setDisplayNone(showLoader, bodyElement), bodyElement.style.overflow = h),
                        'qmapi-connect-open' === connectResponse.type && (store && store.set('qmapiSessionToken', connectResponse.token.sessionToken),
                            showLoader && setDisplayNone(showLoader, bodyElement),
                                bodyElement.style.overflow = 'hidden'),
                        'qmapi-connect-popup-iframe' === connectResponse.type && createIframe(connectResponse.url, 'qmapi-popup-iframe'),
                        'qmapi-close-popup-iframe' === connectResponse.type)
                    {
                        var popupIframe = document.getElementById('qmapi-popup-iframe');
                        popupIframe.parentElement.removeChild(popupIframe), iframeElement.contentWindow.postMessage(JSON.stringify({type: 'qmapi-external-auth-added'}), '*');
                    }
                    'qmapi-close-popup-mobile' === connectResponse.type && iframeElement.contentWindow.postMessage(JSON.stringify({
                        type: 'qmapi-external-auth-added'
                    }), '*'),
                    'qmapi-connect-error' === connectResponse.type && HumanConnect.onError && HumanConnect.onError(connectResponse.error);
                } catch (error) {
                    console.error(error);
                    HumanConnect._debug;
                }
            };
        targetObject.addEventListener ? targetObject.addEventListener('message', handleMessageCallback, !1) : targetObject.attachEvent && targetObject.attachEvent('onmessage', handleMessageCallback);
        var buildUrlAndCreateIframe = function (clientId, clientUserId, e) {
                var publicToken = HumanConnect.publicToken,
                    apiUrlWithClientId = baseApiUrl + '?clientUserId=' + clientUserId;
                return apiUrlWithClientId += publicToken ? '&publicToken=' + publicToken : '&clientId=' + clientId, apiUrlWithClientId += e ? '&sessionToken= ' + e : '',
                    apiUrlWithClientId += HumanConnect.iframeFlag ? '&iframeFlag=1' : '', apiUrlWithClientId += HumanConnect.embed ? '&embed=1' : '',
                    apiUrlWithClientId += '&lang=' + HumanConnect.language, apiUrlWithClientId += HumanConnect._isDevPortal ? '&devPortal=1' : '',
                    apiUrlWithClientId += HumanConnect.__finishUrl ? '&finishUrl=' + HumanConnect.__finishUrl : '',
                    apiUrlWithClientId += HumanConnect.__closeUrl ? '&closeUrl=' + HumanConnect.__closeUrl : '',
                    createIframe(apiUrlWithClientId, !1);
            },
            createIframe = function (apiUrlWithClientId, closePopupIframe) {
                var iframeElement = document.createElement('iframe');
                return closePopupIframe !== !1 && (iframeElement.id = closePopupIframe), iframeElement.src = apiUrlWithClientId, iframeElement.style.position = 'fixed', iframeElement.style.top = '0',
                    iframeElement.style.left = '0', iframeElement.style.width = '100%', iframeElement.style.height = '100%', iframeElement.style.display = 'block', iframeElement.style.margin = '0',
                    iframeElement.style.padding = '0', iframeElement.style.border = '0px none transparent', iframeElement.style.visibility = 'visible', iframeElement.style.backgroundColor = 'transparent',
                    iframeElement.style.overflowX = 'hidden', iframeElement.style.overflowY = 'auto', iframeElement.style['-webkit-tap-highlight-color'] = 'transparent', iframeElement.style.zIndex = '99999',
                    iframeElement.setAttribute('frameBorder', '0'), iframeElement.setAttribute('allowtransparency', 'true'), bodyElement.appendChild(iframeElement), iframeElement;
            },
            showHumanAPILoader = function () {
                var a = document.createElement('img');
                return a.src = baseApiUrl + '/spinner.gif', a.style.height = '30px', a.style.width = '30px', a.style.position = 'fixed', a.style.top = '50%', a.style.left = '50%', a.style.marginLeft = '-15px', a.style.zIndex = '99999', bodyElement.appendChild(a), a;
            },
            documentHead = document.getElementsByTagName('head')[0] || document.documentElement,
            n = function (a) {
                documentHead.insertBefore(a, documentHead.firstChild);
            },
            o = function (a, callbackOnTimeout, c) {
                var script = document.createElement('script');
                if (script.src = a, 'function' == typeof callbackOnTimeout) {
                    var e;
                    script.onload = script.onreadystatechange = function () {
                        e || this.readyState && 'loaded' !== this.readyState && 'complete' !== this.readyState || (e = !0, setTimeout(callbackOnTimeout, 0), script.onload = script.onreadystatechange = null);
                    };
                }
                'function' == typeof c && (script.onerror = c), n(script);
            };
        HumanConnect.open = function (connectOptions) {
            showHumanAPILoader = showHumanAPILoader();
            var clientId = connectOptions.clientId,
                clientUserId = connectOptions.clientUserId;
            connectOptions._baseURL && (baseApiUrl = connectOptions._baseURL);
            var publicToken = connectOptions.publicToken || HumanConnect.publicToken;
            HumanConnect.publicToken = publicToken,
                HumanConnect.onFinish = connectOptions.finish,
                HumanConnect.onClose = connectOptions.close,
                HumanConnect.onError = connectOptions.error,
                HumanConnect.iframeFlag = !1,
                HumanConnect._debug = connectOptions._debug,
                HumanConnect._isDevPortal = connectOptions._isDevPortal,
                HumanConnect.language = connectOptions.language || 'en',
                HumanConnect.embed = connectOptions.embed || !1,
                HumanConnect.uiState = connectOptions.uiState || !1,
                HumanConnect.uiMessageType = connectOptions.uiMessageType || !1,
                HumanConnect.__finishUrl = connectOptions.__finishUrl,
                HumanConnect.__closeUrl = connectOptions.__closeUrl,
                o(baseApiUrl + '/store.min.js', function () {
                iframeElement = buildUrlAndCreateIframe(clientId, clientUserId, store.get('quantimodoSessionToken'));
            }, function () {
                iframeElement = buildUrlAndCreateIframe(clientId, clientUserId, null);
            });
        }, HumanConnect.setPublicToken = function (a) {
            HumanConnect.publicToken = a;
        }, targetObject.HumanConnect = HumanConnect;
    }
}(this);