<?php

use Illuminate\Database\Migrations\Migration;

class CreateInlineSearchView extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $query = <<<EOT
        CREATE OR REPLACE
        VIEW inline_search AS
            SELECT id, concat(first_name, ' ', last_name)::varchar(255) as title, 'users' AS type
            FROM users
            UNION ALL
            SELECT id, name, 'companies' AS type
            FROM companies
            UNION ALL
            SELECT id, name, type AS type
            FROM company_items;
EOT;


        DB::statement($query);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("DROP VIEW IF EXISTS inline_search");
    }
}
