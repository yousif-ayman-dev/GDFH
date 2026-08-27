<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'is_verified')) {
                $table->boolean('is_verified')->default(false)->after('is_admin');
            }
            if (! Schema::hasColumn('users', 'verification_badge_at')) {
                $table->timestamp('verification_badge_at')->nullable()->after('is_verified');
            }
            if (! Schema::hasColumn('users', 'connects_balance')) {
                $table->integer('connects_balance')->default(50)->after('verification_badge_at');
            }
        });

        Schema::table('freelancer_profiles', function (Blueprint $table) {
            if (! Schema::hasColumn('freelancer_profiles', 'cv_path')) {
                $table->string('cv_path')->nullable()->after('bio');
            }
        });

        if (Schema::hasTable('proposals')) {
            Schema::table('proposals', function (Blueprint $table) {
                if (! Schema::hasColumn('proposals', 'connects_used')) {
                    $table->integer('connects_used')->default(2)->after('delivery_days');
                }
            });
        }

        if (Schema::hasTable('contracts')) {
            Schema::table('contracts', function (Blueprint $table) {
                if (! Schema::hasColumn('contracts', 'platform_fee_percent')) {
                    $table->decimal('platform_fee_percent', 5, 2)->default(10.00)->after('amount');
                }
                if (! Schema::hasColumn('contracts', 'platform_fee_amount')) {
                    $table->decimal('platform_fee_amount', 10, 2)->default(0.00)->after('platform_fee_percent');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['is_verified', 'verification_badge_at', 'connects_balance']);
        });

        Schema::table('freelancer_profiles', function (Blueprint $table) {
            $table->dropColumn(['cv_path']);
        });

        if (Schema::hasTable('proposals')) {
            Schema::table('proposals', function (Blueprint $table) {
                $table->dropColumn(['connects_used']);
            });
        }

        if (Schema::hasTable('contracts')) {
            Schema::table('contracts', function (Blueprint $table) {
                $table->dropColumn(['platform_fee_percent', 'platform_fee_amount']);
            });
        }
    }
};
