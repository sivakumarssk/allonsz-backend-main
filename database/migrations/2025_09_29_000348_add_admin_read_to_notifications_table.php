<?php
// database/migrations/xxxx_xx_xx_xxxxxx_add_admin_read_to_notifications_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAdminReadToNotificationsTable extends Migration
{
    public function up()
    {
        Schema::table('notifications', function (Blueprint $table) {
            // Only add columns if they do NOT exist
            if (!Schema::hasColumn('notifications', 'admin_read')) {
                $table->boolean('admin_read')->default(false)->after('is_read');
            }

            if (!Schema::hasColumn('notifications', 'icon')) {
                $table->string('icon')->nullable()->after('admin_read');
            }

            if (!Schema::hasColumn('notifications', 'url')) {
                $table->string('url')->nullable()->after('icon');
            }

            if (!Schema::hasColumn('notifications', 'data')) {
                $table->json('data')->nullable()->after('url');
            }

            // Add index only if the column exists and index does not exist
            if (!Schema::hasColumn('notifications', 'admin_read')) {
                $table->index(['admin_read']);
            }
        });
    }

    public function down()
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropColumn(['admin_read', 'icon', 'url', 'data']);
        });
    }
}
