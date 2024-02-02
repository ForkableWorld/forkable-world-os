angular.module('starter').controller('LoginCtrl', ["$scope", "$state", "$rootScope", "$ionicLoading", "$injector",
    "$stateParams", "$timeout", "qmService", "$mdDialog",
    function($scope, $state, $rootScope, $ionicLoading, $injector, $stateParams, $timeout, qmService, $mdDialog){
        function hideLoginPageLoader(){qmService.hideLoader();}
        const handleLogin = async function(response){
            //debugger
            var user = await response.json();
            if(user.data){user = user.data;}
	        if(user.user){user = user.user;}
            qmService.setUserInLocalStorageBugsnagIntercomPush(user);
            hideLoginPageLoader();
	        afterLoginGoToUrlOrState();
        };
        $scope.state = {
            //useLocalUserNamePasswordForms: qm.platform.isMobileOrChromeExtension(),
            useLocalUserNamePasswordForms: true,
            loading: false,
            alreadyRetried: false,
            showRetry: false,
            registrationForm: false,
            loginForm: false,
	        forgotPasswordUrl: qm.auth.getForgotPasswordUrl(),
            emailRegister: async function () {
                var params = $scope.state.registrationForm;
                params.register = true;
                if(!params.passwordConfirm || params.passwordConfirm !== params.password){
                    qmService.showMaterialAlert("Confirm Password", "Passwords do not match!");
                    return;
                }
                qmService.showFullScreenLoader();
                fetch('/api/v1/user', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(params)
                }).then(handleLogin).catch(function(error){
                    debugger
                    var message = qm.api.getErrorMessageFromResponse(error);
                    qmService.showMaterialAlert("Error", message);
                    hideLoginPageLoader();  // Hides login loader too early
                    //leaveIfLoggedIn();
                });
            },
	        metamaskLogin: function () {
				qm.web3.web3Login().then(function (response) {
					qmService.setUserInLocalStorageBugsnagIntercomPush(response.user);
					afterLoginGoToUrlOrState();
				});
	        },
	        metamaskRegister: function () {
		        qm.web3.web3Register().then(function (response) {
			        qmService.setUserInLocalStorageBugsnagIntercomPush(response.data);
			        afterLoginGoToUrlOrState();
		        });
	        },
            emailLogin: function () {
                qmService.showFullScreenLoader(); // Chrome needs to do this because we can't redirect with access token
                fetch('/api/v1/user',{
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify($scope.state.loginForm)
                })
                .then(handleLogin)
				.catch(function(error){
					qmLog.error("Email Login Error", error);
					qmService.showMaterialAlert("Login Error", "Hmm. I couldn't sign you in with those credentials.  Please double check them or contact me at https://help.quantimo.do.");
					hideLoginPageLoader();  // Hides login loader too early
				});
            }
        };
        $scope.state.socialLogin = function(connectorName, ev, additionalParams){
            if(connectorName === 'quantimodo' && $scope.state.useLocalUserNamePasswordForms){
                if(additionalParams.register){
                    $scope.state.registrationForm = {};
                }else{
                    $scope.state.loginForm = {};
                }
                return;
            }
            loginTimeout();
            qm.connectorHelper.webConnectViaRedirect(connectorName, ev, additionalParams);
        };
        $scope.controller_name = "LoginCtrl";
        qmService.navBar.setFilterBarSearchIcon(false);
        $scope.circlePage = {
            title: null,
            overlayIcon: false, //TODO: Figure out how to position properly in circle-page.html
            color: {
                "backgroundColor": "#3467d6",
                circleColor: "#00000042"
            },
            image: {
                url: "img/robots/robot-waving.svg",
                height: "120",
                width: "120",
                display: "block",
                left: "10px"
            },
            bodyText: "Sign in so you never lose your precious data.",
            // moreInfo: "Your data belongs to you.  Security and privacy our top priorities. I promise that even if " +
            //     "the NSA waterboards me, I will never divulge share your data without your permission.",
        };
        function handleLoginError(error, metaData){
            $scope.retryLogin(error);
            qmLog.error('Login failure: ' + error, metaData, metaData);
        }
        function handleLoginSuccess(){
            //debugger
            if(qm.getUser() && $state.current.name.indexOf('login') !== -1){
                afterLoginGoToUrlOrState();
            }
        }
        function afterLoginGoToUrlOrState(){
            var afterLoginGoToUrl = qm.storage.getItem(qm.items.afterLoginGoToUrl);
            if(afterLoginGoToUrl){
                $timeout(function(){
                    qm.storage.removeItem(qm.items.afterLoginGoToUrl);
                }, 10000);
                window.location.replace(afterLoginGoToUrl);
                return true;
            }
            var afterLoginGoToState = qmService.login.getAfterLoginState();
            if(afterLoginGoToState){
                qmService.goToState(afterLoginGoToState);
                $timeout(function(){  // Wait 10 seconds in case it's called again too quick and sends to default state
                    qm.storage.removeItem(qm.items.afterLoginGoToState);
                }, 10000);
                return true;
            }
            if(qm.appMode.isBuilder()){
                qmService.goToState(qm.staticData.stateNames.configuration);
            }
            if(qm.appMode.isPhysician()){
                qmService.goToState(qm.staticData.stateNames.physician);
            }
            qmService.goToState(qm.staticData.stateNames.onboarding);
        }
        var loginTimeout = function(){
            var duration = 60;
            qm.loaders.psychedelicLoader.show();
            $scope.circlePage.title = 'Logging in...';
            $scope.circlePage.bodyText = 'Thank you for your patience. Your call is very important to us!';
            qmLog.authDebug('Setting login timeout...');
            $timeout(function(){
                $scope.state.showRetry = true;
            }, 15000);
            return $timeout(function(){
                qmLog.authDebug('Finished login timeout');
                if(!qm.getUser()){
                    handleLoginError("timed out");
                }else{
                    handleLoginSuccess();
                }
            }, duration * 1000);
        };
        $scope.$on('$ionicView.beforeEnter', function(e){
	        if (document.title !== "Login") {document.title = "Login";}
	        qmLog.authDebug('beforeEnter in state ' + $state.current.name);
			if(qm.auth.loggingOut($stateParams)){return;}
	        qmService.showFullScreenLoader();
			qm.getUser(function(user){
		        if(user){
			        qmLog.authDebug('Already logged in on login page.  goToDefaultStateIfNoAfterLoginGoToUrlOrState...');
			        afterLoginGoToUrlOrState();
		        }
	        }, function(error){
		        hideLoginPageLoader();
		        qmLog.debug(error);
	        })
        });
        $scope.$on('$ionicView.enter', function(){
	        qmService.showFullScreenLoader();
            //leaveIfLoggedIn();  // Can't call this again because it will send to default state even if the leaveIfLoggedIn in beforeEnter sent us to another state
            qmLog.authDebug($state.current.name + ' enter...');
            qmService.navBar.hideNavigationMenu();
        });
        $scope.$on('$ionicView.afterEnter', function(){
            qm.connectorHelper.getConnectorsFromLocalStorageOrApi();  // Pre-load to speed up login
            //leaveIfLoggedIn();  // Can't call this again because it will send to default state even if the leaveIfLoggedIn in beforeEnter sent us to another state
            qmService.splash.hideSplashScreen();
            var errorMessage = qm.urlHelper.getParam('error');
            if(!qm.stringHelper.isFalsey(errorMessage)){
                errorMessage = decodeURIComponent(errorMessage);
                qmLog.error(errorMessage);
                if(!qm.getUser()){
                    qmService.showMaterialAlert("Login Issue", "Hmm.  I couldn't log you in with that method.  Could you try a different one?  Thanks!  Error: " +
                    errorMessage);
                }
            }
            if(qm.speech.getSpeechEnabled() && !qm.urlHelper.getParam('loggingIn')){
                //qm.speech.sayIfNotInRecentStatements("I'm not sure who you are... So while you're logging in... Take that time to ask yourself who YOU... think YOU are on a deeper level.");
            }
        });
        $scope.state.setAuthDebugEnabled = function(){
            qmLog.setAuthDebugEnabled(true);
            qmLog.authDebug("Enabled auth debug with on-hold button");
        };
        $scope.retryLogin = function(error){
            qmLog.setAuthDebugEnabled(true);
            $scope.state.alreadyRetried = true;
            $scope.state.showRetry = false;
            //$scope.circlePage.title = 'Please try logging in again';
            $scope.circlePage.title = null;
            qmLog.error("Called retry login because: ", error);
        };
    }]);
