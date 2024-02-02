angular.module('starter')// Parent Controller - This controller runs before every one else
    .controller('AppCtrl', ["$scope", "$timeout", "$ionicPopover", "$ionicLoading", "$state", "$ionicHistory", "$rootScope",
        "$ionicPopup", "$ionicSideMenuDelegate", "$ionicPlatform", "$injector", "qmService",
        "clipboard", "$ionicActionSheet",
        "$locale", "$mdDialog", "$mdToast", "$sce",
        "appSettingsResponse", "$stateParams",
        function($scope, $timeout, $ionicPopover, $ionicLoading, $state, $ionicHistory, $rootScope,
                 $ionicPopup, $ionicSideMenuDelegate, $ionicPlatform, $injector, qmService,
                  clipboard, $ionicActionSheet,
                 //Analytics, //$ionicDeploy, // Analytics + uBlock origin extension breaks app
                 $locale, $mdDialog, $mdToast, $sce, appSettingsResponse, $stateParams){
            $scope.controller_name = "ApiPortalAppCtrl";
            qmService.initializeApplication(appSettingsResponse);
            $scope.$on('$ionicView.enter', function(e){
                qmLog.debug('appCtrl enter in state ' + $state.current.name + ' and url is ' + window.location.href);
            });
            $scope.$on('$ionicView.afterEnter', function(e){
                qmLog.debug($scope.controller_name + ".afterEnter so posting queued notifications if any");
                qmService.refreshUserUsingAccessTokenInUrlIfNecessary();
                if(qmService.statesToShowDriftButton.indexOf($state.current.name) !== -1){
                    qm.chatButton.showDriftButton();
                } else {
                    qm.chatButton.hideDriftButton();
                }
                if(typeof drift !== "undefined"){drift.page();}
                qm.storage.setItem(qm.items.lastUrl, window.location.href);
            });
            $scope.$on('$ionicView.beforeLeave', function(e){
                qmService.setLastStateAndUrl($state.current)
            });
            $scope.closeMenu = function(){
                $ionicSideMenuDelegate.toggleLeft(false);
            };
            $scope.showVariableActionSheet = function(v, extraButtons, state){
                qmService.actionSheets.showVariableObjectActionSheet(v.name, v, extraButtons, state);
            }
            $scope.generalButtonClickHandler = qmService.buttonClickHandlers.generalButtonClickHandler;
            $scope.$watch(function(){
                return $ionicSideMenuDelegate.getOpenRatio();
            }, function(ratio){
                if(ratio == 1){
                    $scope.showCloseMenuButton = true;
                    $scope.hideMenuButton = true;
                }
                if(ratio == 0){
                    $scope.showCloseMenuButton = false;
                    $scope.hideMenuButton = false;
                }
            });
            $scope.openUrl = function(url, showLocationBar, windowTarget){
                showLocationBar = showLocationBar || "no";
                windowTarget = windowTarget || '_blank';
                if(typeof cordova !== "undefined"){
                    cordova.InAppBrowser.open(url, windowTarget, 'location=' + showLocationBar + ',toolbar=yes,clearcache=no,clearsessioncache=no');
                }else{
                    if($rootScope.platform.isWeb){
                        window.open(url, windowTarget);  // Otherwise it opens weird popup instead of new tab
                    }else{
                        window.open(url, windowTarget, 'location=' + showLocationBar + ',toolbar=yes,clearcache=yes,clearsessioncache=yes');
                    }
                }
            };
            $rootScope.setLocalStorageFlagTrue = function(flagName){
                qmLog.debug('Set ' + flagName + ' to true', null);
                qmService.rootScope.setProperty(flagName, true);
                qm.storage.setItem(flagName, true);
            };
            $scope.showHelpInfoPopup = function(explanationId, ev, modelName){
                qmService.help.showExplanationsPopup(explanationId, ev, modelName);
            };
            $scope.closeMenuIfNeeded = function(menuItem){
                menuItem.showSubMenu = !menuItem.showSubMenu;
                if(menuItem.click){
                    $scope[menuItem.click] && $scope[menuItem.click]();
                }else if(!menuItem.subMenu){
                    $scope.closeMenu();
                }
            };
            $scope.safeApply = function(fn){
                if(!this.$root){ // Doesn't seem to cause any problems
                    qmLog.debug("this.$root is not set!");
                    if(fn && (typeof (fn) === 'function')){
                        fn();
                    }
                    return;
                }
                var phase = this.$root.$$phase;
                if(phase === '$apply' || phase === '$digest'){
                    if(fn && (typeof (fn) === 'function')){
                        fn();
                    }
                }else{
                    this.$apply(fn);
                }
            };
            $scope.onTextClick = function($event){
                qmLog.debug('Auto selecting text so the user doesn\'t have to press backspace...', null);
                $event.target.select();
            };
            $scope.goBack = function(providedStateParams){
                qmService.stateHelper.goBack(providedStateParams);
            };
            $scope.$on('$stateChangeSuccess', function(){
                qmService.navBar.setOfflineConnectionErrorShowing(false);
                qmLog.globalMetaData.context = $state.current.name;
                if(typeof analytics !== 'undefined'){
                    analytics.trackView($state.current.name);
                }
                $scope.closeMenu();
            });
            $scope.showMaterialAlert = function(title, textContent, ev){
                qmService.showMaterialAlert(title, textContent, ev);
            };
            $scope.copyLinkText = 'Copy Shareable Link';
            $scope.copyToClipboard = function(url, name){
                name = name || url;
                $scope.copyLinkText = 'Copied!';
                clipboard.copyText(url);
                qmService.showInfoToast('Copied ' + name + ' to clipboard!');
            };
            $scope.copyDemoLink = function(){
                var url = "https://web.quantimo.do" + window.location.hash;
                url = qm.urlHelper.addUrlQueryParamsToUrlString({clientId: qm.getClientId(), accessToken: "demo"}, url);
                var name = "Demo Link to " + qm.stringHelper.camelToTitleCase($state.current.name.replace('app.', ''));
                $scope.copyToClipboard(url, name);
            };
            $scope.sendEmailAfterVerification = function(emailType){
                qmService.email.sendEmailAfterVerification(emailType);
            };
            $scope.updateEmailAndExecuteCallback = function(callback){
                qmService.updateEmailAndExecuteCallback(callback);
            };
            $scope.showGeneralVariableSearchDialog = function(ev){
                function selectVariable(variable){
                    $scope.variableObject = variable;
                    qmLog.debug('Selected variable: ' + variable.name);
                    qmService.actionSheets.showVariableObjectActionSheet(variable.name, variable);
                }
                var dialogParameters = {
                    title: 'Select Variable',
                    helpText: "Search for a variable to add a measurement, reminder, view history, or see relationships",
                    placeholder: "Search for a variable", // Don't use ellipses because we append to this sometimes
                    buttonText: "Select Variable",
                    requestParams: {includePublic: true}
                };
                qmService.showVariableSearchDialog(dialogParameters, selectVariable, null, ev);
            };
            $scope.trustAsHtml = function(string){
                return $sce.trustAsHtml(string);
            };
        }]);
