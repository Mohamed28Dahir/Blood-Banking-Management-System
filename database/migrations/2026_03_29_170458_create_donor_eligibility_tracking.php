<?php

db::table('donors')->insert([
    'eligible' => 1, // Track if the donor is eligible
    'created_at' => now(),
    'updated_at' => now()
]);

?>