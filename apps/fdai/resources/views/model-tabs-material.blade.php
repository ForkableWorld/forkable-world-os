<?php /** @var App\Models\BaseModel $model */ ?>
<?php
/** @var App\Models\BaseModel $model */
$model = $model ?? $correlation ?? $aggregateCorrelation ?? $variable ?? $connector ?? $connection ?? $connectorImport ?? $userVariable ?? $user ?? $aggregateCorrelation ?? $application ?? $bshafferOauthAccessToken ?? $bshafferOauthAuthorizationCode ?? $bshafferOauthClient ?? $bshafferOauthRefreshToken ?? $buttonClick ?? $button ?? $card ?? $collaborator ?? $commonTag ?? $connection ?? $connectorDevice ?? $connectorImport ?? $connector ?? $correlation ?? $credential ?? $cryptoTrade ?? $ctCause ?? $ctConditionCause ?? $ctConditionSymptom ?? $ctConditionTreatment ?? $ctCondition ?? $ctCorrelation ?? $ctSideeffect ?? $ctSymptom ?? $ctTreatmentSideeffect ?? $ctTreatment ??  $deviceToken ?? $failedJob ?? $follower ?? $healthCheck ?? $measurementExport ?? $measurementImport ?? $measurement ?? $meddraAllIndication ?? $medium ?? $migration ?? $notification ?? $oAhmadfatoniApigeneratorDatum ?? $oBackendAccessLog ?? $oBackendUserGroup ?? $oBackendUserPreference ?? $oBackendUserRole ?? $oBackendUserThrottle ?? $oBackendUser ?? $oBackendUsersGroup ?? $oCache ?? $oCmsThemeDatum ?? $oCmsThemeLog ?? $oCmsThemeTemplate ?? $oDeferredBinding ?? $oFailedJob ?? $oFlynsarmySocialloginUserProvider ?? $oJob ?? $oKurtjensenPassageGroupsKey ?? $oKurtjensenPassageKey ?? $oKurtjensenPassageVariance ?? $oMeysamEventcounterEventLog ?? $oMeysamEventcounterEvent ?? $oMigration ?? $oRainlabBlogCategory ?? $oRainlabBlogPost ?? $oRainlabBlogPostsCategory ?? $oRainlabNotifyNotificationRule ?? $oRainlabNotifyNotification ?? $oRainlabNotifyRuleAction ?? $oRainlabNotifyRuleCondition ?? $oRainlabUserMailBlocker ?? $oRenatioDynamicpdfPdfLayout ?? $oRenatioDynamicpdfPdfTemplate ?? $oSession ?? $oSuresoftwareMaillogLog ?? $oSystemEventLog ?? $oSystemFile ?? $oSystemMailLayout ?? $oSystemMailPartial ?? $oSystemMailTemplate ?? $oSystemParameter ?? $oSystemPluginHistory ?? $oSystemPluginVersion ?? $oSystemRequestLog ?? $oSystemRevision ?? $oSystemSetting ?? $oUserGroup ?? $oUserThrottle ?? $oUser ?? $oUsersGroup ?? $passwordReset ?? $phrase ?? $purchase ?? $sentEmail ?? $sourcePlatform ?? $source ?? $study ?? $subscription ?? $telescopeEntry ?? $telescopeEntriesTag ?? $telescopeMonitoring ?? $thirdPartyCorrelation ?? $trackerLog ?? $trackerSession ?? $trackingReminderNotification ?? $trackingReminder ?? $unitCategory ?? $unitConversion ?? $unit ?? $userClient ?? $userTag ?? $userVariableClient ?? $userVariable ?? $variableCategory ?? $variableUserSource ?? $variable ?? $vote ?? $wpActionschedulerAction ?? $wpActionschedulerClaim ?? $wpActionschedulerGroup ?? $wpActionschedulerLog ?? $wpAreteWpSmileySetting ?? $wpAreteWpSmiley ?? $wpAreteWpSmileysManage ?? $wpAs3cfItem ?? $wpBlogVersion ?? $wpBlog ?? $wpBpActivity ?? $wpBpActivityMetum ?? $wpBpFriend ?? $wpBpGroup ?? $wpBpGroupsGroupmetum ?? $wpBpGroupsMember ?? $wpBpInvitation ?? $wpBpMessagesMessage ?? $wpBpMessagesMetum ?? $wpBpMessagesNotice ?? $wpBpMessagesRecipient ?? $wpBpNotification ?? $wpBpNotificationsMetum ?? $wpBpUserBlog ?? $wpBpUserBlogsBlogmetum ?? $wpBpXprofileDatum ?? $wpBpXprofileField ?? $wpBpXprofileGroup ?? $wpBpXprofileMetum ?? $wpCommentmetum ?? $wpComment ?? $wpDaRReaction ?? $wpDaRVote ?? $wpEffecto ?? $wpLink ?? $wpMailchimpCart ?? $wpMailchimpJob ?? $wpOption ?? $wpPostmetum ?? $wpPost ?? $wpRegistrationLog ?? $wpSignup ?? $wpSimplyStaticPage ?? $wpSirvImage ?? $wpSirvShortcode ?? $wpSite ?? $wpSitemetum ?? $wpTermRelationship ?? $wpTermTaxonomy ?? $wpTermmetum ?? $wpTerm ?? $wpUsermetum ?? $wpUser ?? $wpWcAdminNoteAction ?? $wpWcAdminNote ?? $wpWcCategoryLookup ?? $wpWcCustomerLookup ?? $wpWcDownloadLog ?? $wpWcOrderCouponLookup ?? $wpWcOrderProductLookup ?? $wpWcOrderStat ?? $wpWcOrderTaxLookup ?? $wpWcProductMetaLookup ?? $wpWcTaxRateClass ?? $wpWcWebhook ?? $wpWoocommerceApiKey ?? $wpWoocommerceAttributeTaxonomy ?? $wpWoocommerceDownloadableProductPermission ?? $wpWoocommerceLog ?? $wpWoocommerceOrderItemmetum ?? $wpWoocommerceOrderItem ?? $wpWoocommercePaymentTokenmetum ?? $wpWoocommercePaymentToken ?? $wpWoocommerceSession ?? $wpWoocommerceShippingZoneLocation ?? $wpWoocommerceShippingZoneMethod ?? $wpWoocommerceShippingZone ?? $wpWoocommerceTaxRateLocation ?? $wpWoocommerceTaxRate ?? $wpWpreactionsReactedUser ?? qm_request()->getModelInstance();
$route = $route ?? qm_request()->getDataLabRouteName();
if(!isset($table)){
    $table = qm_request()->getTable();
    $viewPath = qm_request()->getViewPath();
}
?>
<div class="card">
    <div class="card-header card-header-tabs card-header-info">
        <div class="nav-tabs-navigation">
            <div class="nav-tabs-wrapper">
                <ul class="nav nav-tabs" data-tabs="tabs">
                    <li class="nav-item">
                        <a class="nav-link" href="#profile" data-toggle="tab">
                            <i class="material-icons">bug_report</i> Bugs
                            <div class="ripple-container"></div>
                            <div class="ripple-container"></div></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#stats" data-toggle="tab">
                            <i class="material-icons">code</i> Stats
                            <div class="ripple-container"></div>
                            <div class="ripple-container"></div></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#material-cards" data-toggle="tab">
                            <i class="material-icons">cloud</i> material-cards
                            <div class="ripple-container"></div>
                            <div class="ripple-container"></div></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#activity" data-toggle="tab">
                            <i class="material-icons">cloud</i> activity
                            <div class="ripple-container"></div>
                            <div class="ripple-container"></div></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#timeline" data-toggle="tab">
                            <i class="material-icons">cloud</i> timeline
                            <div class="ripple-container"></div>
                            <div class="ripple-container"></div></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#info" data-toggle="tab">
                            <i class="material-icons">cloud</i> Info
                            <div class="ripple-container"></div>
                            <div class="ripple-container"></div></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#relationships" data-toggle="tab">
                            <i class="material-icons">cloud</i> Relationships
                            <div class="ripple-container"></div>
                            <div class="ripple-container"></div></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#settings" data-toggle="tab">
                            <i class="material-icons">cloud</i> Settings
                            <div class="ripple-container"></div>
                            <div class="ripple-container"></div></a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="tab-content">
            <div class="tab-pane" id="profile">
                <table class="table">
                    <tbody>
                    <tr>
                        <td>
                            <div class="form-check">
                                <label class="form-check-label">
                                    <input class="form-check-input" type="checkbox" value="" checked="">
                                    <span class="form-check-sign">
                                <span class="check"></span>
                              </span>
                                </label>
                            </div>
                        </td>
                        <td>Sign contract for "What are conference organizers afraid of?"</td>
                        <td class="td-actions text-right">
                            <button type="button" rel="tooltip" title="" class="btn btn-primary btn-link btn-sm" data-original-title="Edit Task">
                                <i class="material-icons">edit</i>
                            </button>
                            <button type="button" rel="tooltip" title="" class="btn btn-danger btn-link btn-sm" data-original-title="Remove">
                                <i class="material-icons">close</i>
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="form-check">
                                <label class="form-check-label">
                                    <input class="form-check-input" type="checkbox" value="">
                                    <span class="form-check-sign">
                                <span class="check"></span>
                              </span>
                                </label>
                            </div>
                        </td>
                        <td>Lines From Great Russian Literature? Or E-mails From My Boss?</td>
                        <td class="td-actions text-right">
                            <button type="button" rel="tooltip" title="" class="btn btn-primary btn-link btn-sm" data-original-title="Edit Task" aria-describedby="tooltip285380">
                                <i class="material-icons">edit</i>
                            </button>
                            <button type="button" rel="tooltip" title="" class="btn btn-danger btn-link btn-sm" data-original-title="Remove">
                                <i class="material-icons">close</i>
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="form-check">
                                <label class="form-check-label">
                                    <input class="form-check-input" type="checkbox" value="">
                                    <span class="form-check-sign">
                                <span class="check"></span>
                              </span>
                                </label>
                            </div>
                        </td>
                        <td>Flooded: One year later, assessing what was lost and what was found when a ravaging rain swept through metro Detroit
                        </td>
                        <td class="td-actions text-right">
                            <button type="button" rel="tooltip" title="" class="btn btn-primary btn-link btn-sm" data-original-title="Edit Task">
                                <i class="material-icons">edit</i>
                            </button>
                            <button type="button" rel="tooltip" title="" class="btn btn-danger btn-link btn-sm" data-original-title="Remove">
                                <i class="material-icons">close</i>
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="form-check">
                                <label class="form-check-label">
                                    <input class="form-check-input" type="checkbox" value="" checked="">
                                    <span class="form-check-sign">
                                <span class="check"></span>
                              </span>
                                </label>
                            </div>
                        </td>
                        <td>Create 4 Invisible User Experiences you Never Knew About</td>
                        <td class="td-actions text-right">
                            <button type="button" rel="tooltip" title="" class="btn btn-primary btn-link btn-sm" data-original-title="Edit Task">
                                <i class="material-icons">edit</i>
                            </button>
                            <button type="button" rel="tooltip" title="" class="btn btn-danger btn-link btn-sm" data-original-title="Remove">
                                <i class="material-icons">close</i>
                            </button>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <?php /** @var App\Models\BaseModel $model */ ?>
            <div class="tab-pane" id="stats">
                {!! $model->getDataLabRelationshipCountBoxesHtml() !!}
            </div>
            <div class="tab-pane" id="relationships" style="text-align: center;">
                @foreach( $model->getInterestingRelationshipButtons() as $button)
                    {!! $button->getMDLChip() !!}
                @endforeach
            </div>

            <div class="tab-pane" id="material-cards">
                {!! $model->getDataLabRelationshipCountCardsHtml() !!}
            </div>
            <div class="tab-pane" id="activity">
                @include('activity-stream')
            </div>
            <!-- /.tab-pane -->
            <div class="tab-pane" id="timeline">
                @include('timeline')
            </div>
            <!-- /.tab-pane -->
            <div class="tab-pane" id="settings">
                @include('edit-model-box')
            </div>
            <div class="tab-pane" id="info">
                @include("datalab.".$model->getRouteName().".show_fields")
            </div>
            <div class="tab-pane" id="relationships">
                @foreach( $model->getInterestingRelationshipButtons() as $button)
                    {!! $button->getStatCard() !!}
                @endforeach
            </div>
        </div>
    </div>
</div>
