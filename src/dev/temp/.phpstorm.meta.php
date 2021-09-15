<?php

namespace PHPSTORM_META {
    registerArgumentsSet('database_tables_values', $tablesNameString);
    expectedArguments(\Joonika\Database::get(), 0, argumentsSet('database_tables_values'));
    expectedArguments(\Joonika\Database::select(), 0, argumentsSet('database_tables_values'));
}