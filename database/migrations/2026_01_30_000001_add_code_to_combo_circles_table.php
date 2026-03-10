<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCodeToComboCirclesTable extends Migration
{
    public function up()
    {
        Schema::table('combo_circles', function (Blueprint $table) {
            $table->string('code', 10)->nullable()->after('id');
        });

        // Generate unique codes for existing combo circles
        $circles = \App\Models\ComboCircle::whereNull('code')->get();
        foreach ($circles as $circle) {
            $circle->code = $this->generateUniqueCode();
            $circle->save();
        }
    }

    public function down()
    {
        Schema::table('combo_circles', function (Blueprint $table) {
            $table->dropColumn('code');
        });
    }

    private function generateUniqueCode()
    {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        do {
            $code = '';
            for ($i = 0; $i < 8; $i++) {
                $code .= $characters[rand(0, strlen($characters) - 1)];
            }
        } while (\App\Models\ComboCircle::where('code', $code)->exists());

        return $code;
    }
}
