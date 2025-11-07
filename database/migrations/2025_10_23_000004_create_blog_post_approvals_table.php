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
        if (!Schema::hasTable('blog_post_approvals')) {
            Schema::create('blog_post_approvals', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('blog_post_id');
                $table->unsignedBigInteger('admin_id');
                $table->enum('action', ['approved', 'rejected', 'changes_requested']);
                $table->text('reason')->nullable();
                $table->text('admin_notes')->nullable();
                $table->timestamps();
                
                // Foreign keys
                $table->foreign('blog_post_id')->references('id')->on('blog_posts')->onDelete('cascade');
                $table->foreign('admin_id')->references('id')->on('users')->onDelete('cascade');
                
                // Index for queries
                $table->index('blog_post_id');
                $table->index('admin_id');
                $table->index('created_at');
            });
        }
        
        // Add columns to blog_posts table if they don't exist
        Schema::table('blog_posts', function (Blueprint $table) {
            if (!Schema::hasColumn('blog_posts', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable()->after('status');
            }
            if (!Schema::hasColumn('blog_posts', 'admin_notes')) {
                $table->text('admin_notes')->nullable()->after('rejection_reason');
            }
            if (!Schema::hasColumn('blog_posts', 'reviewed_by')) {
                $table->unsignedBigInteger('reviewed_by')->nullable()->after('admin_notes');
                $table->foreign('reviewed_by')->references('id')->on('users')->onDelete('set null');
            }
            if (!Schema::hasColumn('blog_posts', 'reviewed_at')) {
                $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blog_post_approvals');
        
        Schema::table('blog_posts', function (Blueprint $table) {
            if (Schema::hasColumn('blog_posts', 'reviewed_by')) {
                $table->dropForeign(['reviewed_by']);
                $table->dropColumn('reviewed_by');
            }
            if (Schema::hasColumn('blog_posts', 'reviewed_at')) {
                $table->dropColumn('reviewed_at');
            }
            if (Schema::hasColumn('blog_posts', 'rejection_reason')) {
                $table->dropColumn('rejection_reason');
            }
            if (Schema::hasColumn('blog_posts', 'admin_notes')) {
                $table->dropColumn('admin_notes');
            }
        });
    }
};
