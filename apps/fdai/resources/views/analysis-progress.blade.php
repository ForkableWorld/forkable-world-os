<?php
/** @var \App\Models\BaseModel $model */
use App\Utils\QMRoute;
$model = $model ?? $correlation ?? $aggregateCorrelation ?? $variable ?? $connector ?? $connection ?? $connectorImport ?? $userVariable ?? $user ?? $aggregateCorrelation ?? $application ?? $bshafferOauthAccessToken ?? $bshafferOauthAuthorizationCode ?? $bshafferOauthClient ?? $bshafferOauthRefreshToken ?? $buttonClick ?? $button ?? $card ?? $collaborator ?? $commonTag ?? $connection ?? $connectorDevice ?? $connectorImport ?? $connector ?? $correlation ?? $credential ?? $cryptoTrade ?? $ctCause ?? $ctConditionCause ?? $ctConditionSymptom ?? $ctConditionTreatment ?? $ctCondition ?? $ctCorrelation ?? $ctSideeffect ?? $ctSymptom ?? $ctTreatmentSideeffect ?? $ctTreatment ??  $deviceToken ?? $failedJob ?? $follower ?? $healthCheck ?? $measurementExport ?? $measurementImport ?? $measurement ?? $meddraAllIndication ?? $medium ?? $migration ?? $notification ?? $oAhmadfatoniApigeneratorDatum ?? $oBackendAccessLog ?? $oBackendUserGroup ?? $oBackendUserPreference ?? $oBackendUserRole ?? $oBackendUserThrottle ?? $oBackendUser ?? $oBackendUsersGroup ?? $oCache ?? $oCmsThemeDatum ?? $oCmsThemeLog ?? $oCmsThemeTemplate ?? $oDeferredBinding ?? $oFailedJob ?? $oFlynsarmySocialloginUserProvider ?? $oJob ?? $oKurtjensenPassageGroupsKey ?? $oKurtjensenPassageKey ?? $oKurtjensenPassageVariance ?? $oMeysamEventcounterEventLog ?? $oMeysamEventcounterEvent ?? $oMigration ?? $oRainlabBlogCategory ?? $oRainlabBlogPost ?? $oRainlabBlogPostsCategory ?? $oRainlabNotifyNotificationRule ?? $oRainlabNotifyNotification ?? $oRainlabNotifyRuleAction ?? $oRainlabNotifyRuleCondition ?? $oRainlabUserMailBlocker ?? $oRenatioDynamicpdfPdfLayout ?? $oRenatioDynamicpdfPdfTemplate ?? $oSession ?? $oSuresoftwareMaillogLog ?? $oSystemEventLog ?? $oSystemFile ?? $oSystemMailLayout ?? $oSystemMailPartial ?? $oSystemMailTemplate ?? $oSystemParameter ?? $oSystemPluginHistory ?? $oSystemPluginVersion ?? $oSystemRequestLog ?? $oSystemRevision ?? $oSystemSetting ?? $oUserGroup ?? $oUserThrottle ?? $oUser ?? $oUsersGroup ?? $passwordReset ?? $phrase ?? $purchase ?? $sentEmail ?? $sourcePlatform ?? $source ?? $study ?? $subscription ?? $telescopeEntry ?? $telescopeEntriesTag ?? $telescopeMonitoring ?? $thirdPartyCorrelation ?? $trackerLog ?? $trackerSession ?? $trackingReminderNotification ?? $trackingReminder ?? $unitCategory ?? $unitConversion ?? $unit ?? $userClient ?? $userTag ?? $userVariableClient ?? $userVariable ?? $variableCategory ?? $variableUserSource ?? $variable ?? $vote ?? $wpActionschedulerAction ?? $wpActionschedulerClaim ?? $wpActionschedulerGroup ?? $wpActionschedulerLog ?? $wpAreteWpSmileySetting ?? $wpAreteWpSmiley ?? $wpAreteWpSmileysManage ?? $wpAs3cfItem ?? $wpBlogVersion ?? $wpBlog ?? $wpBpActivity ?? $wpBpActivityMetum ?? $wpBpFriend ?? $wpBpGroup ?? $wpBpGroupsGroupmetum ?? $wpBpGroupsMember ?? $wpBpInvitation ?? $wpBpMessagesMessage ?? $wpBpMessagesMetum ?? $wpBpMessagesNotice ?? $wpBpMessagesRecipient ?? $wpBpNotification ?? $wpBpNotificationsMetum ?? $wpBpUserBlog ?? $wpBpUserBlogsBlogmetum ?? $wpBpXprofileDatum ?? $wpBpXprofileField ?? $wpBpXprofileGroup ?? $wpBpXprofileMetum ?? $wpCommentmetum ?? $wpComment ?? $wpDaRReaction ?? $wpDaRVote ?? $wpEffecto ?? $wpLink ?? $wpMailchimpCart ?? $wpMailchimpJob ?? $wpOption ?? $wpPostmetum ?? $wpPost ?? $wpRegistrationLog ?? $wpSignup ?? $wpSimplyStaticPage ?? $wpSirvImage ?? $wpSirvShortcode ?? $wpSite ?? $wpSitemetum ?? $wpTermRelationship ?? $wpTermTaxonomy ?? $wpTermmetum ?? $wpTerm ?? $wpUsermetum ?? $wpUser ?? $wpWcAdminNoteAction ?? $wpWcAdminNote ?? $wpWcCategoryLookup ?? $wpWcCustomerLookup ?? $wpWcDownloadLog ?? $wpWcOrderCouponLookup ?? $wpWcOrderProductLookup ?? $wpWcOrderStat ?? $wpWcOrderTaxLookup ?? $wpWcProductMetaLookup ?? $wpWcTaxRateClass ?? $wpWcWebhook ?? $wpWoocommerceApiKey ?? $wpWoocommerceAttributeTaxonomy ?? $wpWoocommerceDownloadableProductPermission ?? $wpWoocommerceLog ?? $wpWoocommerceOrderItemmetum ?? $wpWoocommerceOrderItem ?? $wpWoocommercePaymentTokenmetum ?? $wpWoocommercePaymentToken ?? $wpWoocommerceSession ?? $wpWoocommerceShippingZoneLocation ?? $wpWoocommerceShippingZoneMethod ?? $wpWoocommerceShippingZone ?? $wpWoocommerceTaxRateLocation ?? $wpWoocommerceTaxRate ?? $wpWpreactionsReactedUser ?? qm_request()->getModelInstance();
$route = $route ?? qm_request()->getDataLabRouteName();
if(!isset($table)){
    $table = qm_request()->getTable();
    $viewPath = qm_request()->getViewPath();
    $pluralClassName = qm_request()->getPluralClassName();
    $title = qm_request()->getPluralTitleWithHumanizedQuery();
}
/** @var \App\Models\BaseModel $fullClassName */
$fullClassName = qm_request()->getFullClassFromRoute();
$analyzable = QMRoute::isAnalysisProgress();
$trash = QMRoute::isTrash();
?>
@extends('layouts.admin-lte-app', ['title' => $title ])
@section('content')
    <section class="content-header">
        <h1 class="pull-left">{{ $title }} Analysis Progress</h1>
        <h1 class="pull-right">
            <a class="btn btn-primary pull-right"
               href="{{ route("datalab.".$model->getRouteName().".index", []) }}">
                <i class="fa fa-backward"></i> &nbsp;View All {{ $title }}
            </a>
        </h1>
        <div class="pull-right">{!! \App\Models\BaseModel::getDataLabIndexDropDown($table) !!}</div>
        @isadmin
        @if( $analyzable )
            <h1 class="pull-right">@include('jenkins-badge')</h1>
        @endif
        @endisadmin
    </section>
   <div class="content">
        @include('flash::message')
       <div class="clearfix"></div>
       @include('analyzable-admin-widgets')
        <div class="box box-primary">
            <div class="box-body">
                @include("table")
            </div>
        </div>
        <div class="text-center"></div>
    </div>
@endsection
