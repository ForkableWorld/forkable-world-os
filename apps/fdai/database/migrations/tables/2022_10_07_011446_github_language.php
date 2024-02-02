<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('github_repositories', function (Blueprint $table) {
            $table->string('language')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('github_repositories', function (Blueprint $table) {
            //
        });
    }
};
