<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DemoData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('DELETE FROM posts ;');
        DB::statement('DELETE FROM sources ;');
        DB::statement('DELETE FROM persons ;');


        DB::statement("INSERT INTO `persons` (`id`, `name`) VALUES
                            (41,	'Хульмарра Илтазяра'),
                            (42,	'Иммит Чергоба'),
                            (43,	'Иммит Гоба'),
                            (45,	'Дона Маривальди'),
                            (46,	'Изе Писакар'),
                            (47,	'Диеро Домине'),
                            (48,	'Ромеро Маривальди'),
                            (49,	'Керри Эмблкроун'),
                            (50,	'Хельм Бакмэн'),
                            (51,	'Грим Асл'),
                            (52,	'Сель Эвенвуд'),
                            (53,	'Стедд Эмблкроун'),
                            (54,	'Чен Вань'),
                            (55,	'Менг Шин'),
                            (56,	'Мей Хуан'),
                            (57,	'Ксяо Линь'),
                            (58,	'Цзюн Шин'),
                            (59,	'Алексей Навальный'),
                            (60,	'Мысли Студио');");


        DB::statement("INSERT INTO `sources` (`id`, `personId`, `active`, `type`, `code`) VALUES
                        (1,	59,	1,	'instagram',	'navalny'),
                        (2,	60,	1,	'instagram',	'mislistudio');");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
