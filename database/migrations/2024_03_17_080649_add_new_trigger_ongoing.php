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

        DB::unprepared('
        CREATE TRIGGER update_status_ongoing
        BEFORE UPDATE ON projects
        FOR EACH ROW
        BEGIN
        IF (NEW.schedule <= NOW() AND NEW.status != 4 AND NEW.status != 0  AND NEW.status != 5  AND NEW.status != 3) THEN
            SET NEW.status = 2;
        END IF;
        END
        ');
        DB::unprepared('
            CREATE TRIGGER update_status_pending
            BEFORE UPDATE ON projects
            FOR EACH ROW
            BEGIN
                IF (NEW.schedule > NOW() AND NEW.status != 4 AND NEW.status != 0  AND NEW.status != 5  AND NEW.status != 3) THEN
                    SET NEW.status = 1;
                END IF;
            END
        ');

        DB::unprepared('
            CREATE TRIGGER update_status_done
            BEFORE UPDATE ON projects
            FOR EACH ROW
            BEGIN
                IF (NEW.deadline < NOW() AND NEW.status != 4 AND NEW.status != 0  AND NEW.status != 5  AND NEW.status != 3) THEN
                    SET NEW.status = 4;
                END IF;
            END
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared('DROP TRIGGER `update_status_ongoing`');
        DB::unprepared('DROP TRIGGER `update_status_pending`');
        // DB::unprepared('DROP TRIGGER `update_status_done`');
    }
};
