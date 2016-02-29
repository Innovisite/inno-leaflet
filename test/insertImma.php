<?php

include_once("BluePHP/Utils/CSVReader.inc");


$csv = new CSVReader([
		      "file" => "entreprises-immatriculees-en-2016.csv",
		      "header" => true,
		      "sep" => ";"
		      ]);

$denoms = $csv->getColData("Dénomination");
$sirens = $csv->getColData("Siren");
$geos = $csv->getColData("Géolocalisation");

$str = "begin;\n";

$nbImma = count($sirens);
for($i = 0; $i < $nbImma; ++$i) {
  $siren = $sirens[$i];
  $denom = addslashes(strtr($denoms[$i],'"',' '));
  $geoElts = explode(",", strtr($geos[$i], " ", ""), 2);
  if(is_array($geoElts) and count($geoElts) == 2)
    {
      $ilat = (int) (floatval($geoElts[0]) * 10000);
      $ilon = (int) (floatval($geoElts[1]) * 10000);
      $str .= "insert into imma values('$siren',\"$denom\",$ilon,$ilat);\n";
    }
}

$str .= "commit;\n";

echo $str;

?>