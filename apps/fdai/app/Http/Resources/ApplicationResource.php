<?php

namespace App\Http\Resources;


use Illuminate\Http\Request;

/** @mixin \App\Models\Application */
class ApplicationResource extends BaseJsonResource
{
    /**
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'client_id' => $this->client_id,
            'user_id' => $this->user_id,
            'app_display_name' => $this->app_display_name,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'organization_id' => $this->organization_id,
            'app_description' => $this->app_description,
            'long_description' => $this->long_description,
            'icon_url' => $this->icon_url,
            'text_logo' => $this->text_logo,
            'splash_screen' => $this->splash_screen,
            'homepage_url' => $this->homepage_url,
            'app_type' => $this->app_type,
            'app_design' => $this->app_design,
            'enabled' => $this->enabled,
            'stripe_active' => $this->stripe_active,
            'stripe_id' => $this->stripe_id,
            'stripe_subscription' => $this->stripe_subscription,
            'stripe_plan' => $this->stripe_plan,
            'last_four' => $this->last_four,
            'trial_ends_at' => $this->trial_ends_at,
            'subscription_ends_at' => $this->subscription_ends_at,
            'company_name' => $this->company_name,
            'country' => $this->country,
            'address' => $this->address,
            'state' => $this->state,
            'city' => $this->city,
            'zip' => $this->zip,
            'plan_id' => $this->plan_id,
            'exceeding_call_count' => $this->exceeding_call_count,
            'exceeding_call_charge' => $this->exceeding_call_charge,
            'study' => $this->study,
            'billing_enabled' => $this->billing_enabled,
            'outcome_variable_id' => $this->outcome_variable_id,
            'predictor_variable_id' => $this->predictor_variable_id,
            'physician' => $this->physician,
            'additional_settings' => $this->additional_settings,
            'app_status' => $this->app_status,
            'build_enabled' => $this->build_enabled,
            'collaborators_count' => $this->collaborators_count,
            'subscriptions_count' => $this->subscriptions_count,
            'number_of_collaborators_where_app' => $this->number_of_collaborators_where_app,
            'is_public' => $this->is_public,
            'sort_order' => $this->sort_order,
            'slug' => $this->slug,
            //'raw' => $this->raw,

            //'wp_post_id' => $this->wp_post_id,

            //'outcome' => new VariableResource($this->whenLoaded('outcome')),
            //'predictor' => new VariableResource($this->whenLoaded('predictor')),
            'user' => new UserResource($this->whenLoaded('user')),
            'outcome_variable' => new VariableResource($this->whenLoaded('outcome_variable')),
            'predictor_variable' => new VariableResource($this->whenLoaded('predictor_variable')),
        ];
    }
}
