<?php
// Run this SQL to check what columns are missing:
// DESCRIBE notifications;
// DESCRIBE users;
// DESCRIBE packages;

// Create migration: php artisan make:migration fix_missing_columns

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FixMissingColumns extends Migration
{
    public function up()
    {
        // Fix notifications table
        if (Schema::hasTable('notifications') && !Schema::hasColumn('notifications', 'admin_read')) {
            Schema::table('notifications', function (Blueprint $table) {
                $table->boolean('admin_read')->default(false);
                $table->string('icon')->nullable();
                $table->string('url')->nullable();
                $table->json('data')->nullable();
            });
        }

        // Fix users table - add any missing columns
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (!Schema::hasColumn('users', 'first_name')) {
                    $table->string('first_name')->nullable();
                }
                if (!Schema::hasColumn('users', 'last_name')) {
                    $table->string('last_name')->nullable();
                }
                if (!Schema::hasColumn('users', 'username')) {
                    $table->string('username')->unique()->nullable();
                }
                if (!Schema::hasColumn('users', 'wallet')) {
                    $table->decimal('wallet', 10, 2)->default(0);
                }
                if (!Schema::hasColumn('users', 'referal_code')) {
                    $table->string('referal_code')->unique()->nullable();
                }
                if (!Schema::hasColumn('users', 'referal_id')) {
                    $table->unsignedBigInteger('referal_id')->nullable();
                }
                if (!Schema::hasColumn('users', 'country_id')) {
                    $table->unsignedBigInteger('country_id')->nullable();
                }
                if (!Schema::hasColumn('users', 'state_id')) {
                    $table->unsignedBigInteger('state_id')->nullable();
                }
                if (!Schema::hasColumn('users', 'district_id')) {
                    $table->unsignedBigInteger('district_id')->nullable();
                }
                if (!Schema::hasColumn('users', 'mandal_id')) {
                    $table->unsignedBigInteger('mandal_id')->nullable();
                }
                if (!Schema::hasColumn('users', 'age_id')) {
                    $table->unsignedBigInteger('age_id')->nullable();
                }
                if (!Schema::hasColumn('users', 'photo')) {
                    $table->string('photo')->nullable();
                }
                if (!Schema::hasColumn('users', 'api_token')) {
                    $table->string('api_token')->nullable();
                }
                if (!Schema::hasColumn('users', 'device_token')) {
                    $table->string('device_token')->nullable();
                }
                if (!Schema::hasColumn('users', 'status')) {
                    $table->enum('status', ['active', 'inactive'])->default('active');
                }
                // Add soft deletes if not exists
                if (!Schema::hasColumn('users', 'deleted_at')) {
                    $table->softDeletes();
                }
            });
        }

        // Create missing tables if they don't exist
        if (!Schema::hasTable('settings')) {
            Schema::create('settings', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique();
                $table->text('value')->nullable();
                $table->string('type')->default('text');
                $table->string('group')->default('general');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('countries')) {
            Schema::create('countries', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('code', 10)->unique();
                $table->softDeletes();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('states')) {
            Schema::create('states', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('code', 10)->nullable();
                $table->unsignedBigInteger('country_id');
                $table->softDeletes();
                $table->timestamps();
                
                $table->foreign('country_id')->references('id')->on('countries')->onDelete('cascade');
            });
        }

        if (!Schema::hasTable('districts')) {
            Schema::create('districts', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->unsignedBigInteger('state_id');
                $table->softDeletes();
                $table->timestamps();
                
                $table->foreign('state_id')->references('id')->on('states')->onDelete('cascade');
            });
        }

        if (!Schema::hasTable('mandals')) {
            Schema::create('mandals', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->unsignedBigInteger('district_id');
                $table->unsignedBigInteger('state_id');
                $table->softDeletes();
                $table->timestamps();
                
                $table->foreign('district_id')->references('id')->on('districts')->onDelete('cascade');
                $table->foreign('state_id')->references('id')->on('states')->onDelete('cascade');
            });
        }

        if (!Schema::hasTable('ages')) {
            Schema::create('ages', function (Blueprint $table) {
                $table->id();
                $table->string('range');
                $table->integer('min_age');
                $table->integer('max_age');
                $table->timestamps();
            });
        }

        // Fix packages table if needed
        if (Schema::hasTable('packages')) {
            Schema::table('packages', function (Blueprint $table) {
                if (!Schema::hasColumn('packages', 'total_members')) {
                    $table->integer('total_members')->default(7);
                }
                if (!Schema::hasColumn('packages', 'max_downlines')) {
                    $table->integer('max_downlines')->default(2);
                }
                if (!Schema::hasColumn('packages', 'reward_amount')) {
                    $table->decimal('reward_amount', 10, 2)->default(0);
                }
                if (!Schema::hasColumn('packages', 'status')) {
                    $table->enum('status', ['active', 'inactive'])->default('active');
                }
                if (!Schema::hasColumn('packages', 'deleted_at')) {
                    $table->softDeletes();
                }
            });
        }
    }

    public function down()
    {
        // Rollback logic here
    }
}