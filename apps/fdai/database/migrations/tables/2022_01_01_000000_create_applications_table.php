<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApplicationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         Schema::create('applications', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('organization_id')->nullable();
            $table->string('client_id', 80)->unique();
            $table->string('app_display_name');
            $table->string('app_description')->nullable();
            $table->text('long_description')->nullable();
            $table->bigInteger('user_id')->index('applications_user_id_fk');
            $table->string('icon_url', 2083)->nullable();
            $table->string('text_logo', 2083)->nullable();
            $table->string('splash_screen', 2083)->nullable();
            $table->string('homepage_url')->nullable();
            $table->string('app_type', 32)->nullable();
            $table->text('app_design')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
            $table->softDeletes();
            $table->smallInteger('enabled')->default(1);
            $table->smallInteger('stripe_active')->default(0);
            $table->string('stripe_id')->nullable();
            $table->string('stripe_subscription')->nullable();
            $table->string('stripe_plan', 100)->nullable();
            $table->string('last_four', 4)->nullable();
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('subscription_ends_at')->nullable();
            $table->string('company_name', 100)->nullable();
            $table->string('country', 100)->nullable();
            $table->string('address')->nullable();
            $table->string('state', 100)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('zip', 10)->nullable();
            $table->integer('plan_id')->nullable();
            $table->integer('exceeding_call_count')->default(0);
            $table->decimal('exceeding_call_charge', 16)->nullable();
            $table->smallInteger('study')->default(0);
            $table->smallInteger('billing_enabled')->default(1);
            $table->integer('outcome_variable_id')->nullable()->index('applications_outcome_variable_id_fk');
            $table->integer('predictor_variable_id')->nullable()->index('applications_predictor_variable_id_fk');
            $table->smallInteger('physician')->default(0);
            $table->text('additional_settings')->nullable();
            $table->text('app_status')->nullable()->comment('The current build status for the iOS app, Android app, and Chrome extension.');
            $table->boolean('build_enabled')->default(false);
            $table->bigInteger('wp_post_id')->nullable()->index('applications_wp_posts_ID_fk');
            $table->integer('number_of_collaborators_where_app')->nullable()->comment('Number of Collaborators for this App.
                [Formula: 
                    update applications
                        left join (
                            select count(id) as total, app_id
                            from collaborators
                            group by app_id
                        )
                        as grouped on applications.id = grouped.app_id
                    set applications.number_of_collaborators_where_app = count(grouped.total)
                ]
                ');
            $table->boolean('is_public')->nullable();
            $table->integer('sort_order')->nullable();
            $table->string('slug', 200)->nullable()->unique('applications_slug_uindex')->comment('The slug is the part of a URL that identifies a page in human-readable keywords.');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('applications');
    }
}
