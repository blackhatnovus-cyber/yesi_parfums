<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->after('category')->constrained()->nullOnDelete();
            $table->string('status')->default('active')->index()->after('image');
            $table->softDeletes();
        });

        $now = now();

        DB::table('products')
            ->select('category')
            ->whereNotNull('category')
            ->distinct()
            ->orderBy('category')
            ->get()
            ->each(function (object $productCategory) use ($now): void {
                $categoryId = DB::table('categories')
                    ->where('name', $productCategory->category)
                    ->value('id');

                if ($categoryId === null) {
                    $categoryId = DB::table('categories')->insertGetId([
                        'name' => $productCategory->category,
                        'description' => null,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }

                DB::table('products')
                    ->where('category', $productCategory->category)
                    ->whereNull('category_id')
                    ->update(['category_id' => $categoryId]);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('category_id');
            $table->dropIndex(['status']);
            $table->dropColumn(['status', 'deleted_at']);
        });
    }
};
