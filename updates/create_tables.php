<?php namespace Abnmt\Theater\Updates;

use Db;
use Schema;
use October\Rain\Database\Updates\Migration;

class CreateTables extends Migration
{

    public function up()
    {
        Db::unprepared(file_get_contents('D:\Dropbox\OpenServer\domains\komedianty.abnmt.com\plugins\abnmt\theater\updates\abnmt_komedianty.sql'));
    }

    public function down()
    {
        Schema::dropIfExists('abnmt_theater_events');
        Schema::dropIfExists('abnmt_theater_people');
        Schema::dropIfExists('abnmt_theater_performances');
        Db::table('rainlab_blog_posts')->truncate();
        Db::table('system_files')->truncate();
    }

}
