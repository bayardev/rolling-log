<?php

try
{
    $bdd = new PDO('mysql:host=localhost', 'root', 'Bayard');
}

catch (Exception $e)
{
        die('Erreur : ' . $e->getMessage());
}

$bdd->query("CREATE DATABASE IF NOT EXISTS serializer_test");
$bdd = null;
exec("../../../../vendor/bin/doctrine orm:schema-tool:update --force");
