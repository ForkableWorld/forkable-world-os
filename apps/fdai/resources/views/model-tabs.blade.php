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
<div class="nav-tabs-custom">
    <ul class="nav nav-tabs">
        <li><a href="#stats" data-toggle="tab">Stats</a></li>
        @isadmin
            <li><a href="#material-cards" data-toggle="tab">Material Cards</a></li>
{{--            <li><a href="#activity" data-toggle="tab">Activity</a></li>--}}
{{--            <li><a href="#timeline" data-toggle="tab">Timeline</a></li>--}}
            <li><a href="#info" data-toggle="tab">Info</a></li>
            <li><a href="#relationships" data-toggle="tab">Relationships</a></li>
        @endisadmin
{{--        @if( $model->hasWriteAccess() )
            <li><a href="#settings" data-toggle="tab">Settings</a></li>
        @endif--}}
    </ul>
    <div class="tab-content">
        <div class="active tab-pane" id="stats">
            {!! $model->getDataLabRelationshipCountBoxesHtml() !!}
        </div>
        <div class="tab-pane" id="material-cards">
            {!! $model->getDataLabRelationshipCountCardsHtml() !!}
        </div>
{{--        <div class="tab-pane" id="activity">--}}
{{--            @include('activity-stream')--}}
{{--        </div>--}}
{{--    <!-- /.tab-pane -->--}}
{{--        <div class="tab-pane" id="timeline">--}}
{{--            @include('timeline')--}}
{{--        </div>--}}
    <!-- /.tab-pane -->
{{--
        <div class="tab-pane" id="settings">
            @include('edit-model-box')
        </div>
        --}}
        <div class="tab-pane" id="info">
            @include("datalab.".$model->getViewName().".show_fields")
        </div>
        <div class="tab-pane" id="relationships">
            @foreach( $model->getInterestingRelationshipButtons() as $button)
                {!! $button->getMaterialStatCard() !!}
            @endforeach
        </div>
        <!-- /.tab-pane -->
    </div>
    <!-- /.tab-content -->
</div>
<!-- /.nav-tabs-custom -->
