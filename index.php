<?php

//init first
require_once "./classes/csv_reader.php";
$oCsvReader = new CsvReader();

$fileName = './employees.csv';

$oCsvReader->readCsv($fileName);

$oCsvReader->processBirthdays();
$oCsvReader->setCakesCsv();
$oCsvReader->exportCsv();