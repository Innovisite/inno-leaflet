<?php

include_once("BluePHP/Utils/DBConnect.inc");

function check_raw_request_json() 
  {  
    if(!count($_POST)) 
      {
	$rawRequest = file_get_contents('php://input');
	$_POST = json_decode($rawRequest, true);
      }
  }

try
{

  check_raw_request_json();
  
  header('Cache-Control: no-cache, must-revalidate');
  header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
  header('Access-Control-Allow-Origin: *');  
  header('Content-type: application/json');
  
  $map = $_POST;
  
  if(is_array($map) and isset($map["bounds"]) and
     isset($map["zoom"])) 
    {
      $geojson = [ "type" => "FeatureCollection",
		   "features" => []
		   ];
      $db = new DBConnect("SQLITE3", "", "../test/imma2016.db", "", "");
      $db->connectToDB();

      $iLngMin = (int) ($map["bounds"][0] * 10000);
      $iLatMin = (int) ($map["bounds"][1] * 10000);
      $iLngMax = (int) ($map["bounds"][2] * 10000);
      $iLatMax = (int) ($map["bounds"][3] * 10000);

//      $query = "select * from imma where longitude > $iLngMin and " . 
//	"longitude < $iLngMax and latitude < $iLatMin and " . 
//	"latitude > $iLatMax";
//      $res = $db->query($query);
//      while($obj = $res->nextAssoc())
//	{
//	  $coords = [
//		     $obj["longitude"] / 10000.,
//		     $obj["latitude"] / 10000.
//		     ];
//	  $pt = [ "type" => "Feature",
//		  "geometry" => [ "type" => "Point", "coordinates" => $coords ],
//		  "properties" => [ "siren" => $obj["siren"],
//				    "denomination" => $obj["denomination"],
//				    "longitude" => $obj["longitude"],
//				    "latitude" => $obj["latitude"]
//				    ]
//		  ];
//	  $geojson["features"][] = $pt;
//	}

      $query = "select * from clusters where " . 
	"minZoom <= " . $map["zoom"] . " and " . 
	"maxZoom >= " . $map["zoom"] . " and " .  
	"longitude > $iLngMin and " . 
	"longitude < $iLngMax and latitude < $iLatMin and " . 
	"latitude > $iLatMax";
      $res = $db->query($query);
      while($obj = $res->nextAssoc())
	{
	  $coords = [
		     $obj["longitude"] / 10000.,
		     $obj["latitude"] / 10000.
		     ];
	  $pt = [ "type" => "Feature",
		  "geometry" => [ "type" => "Point", "coordinates" => $coords ],
		  "properties" => [ "id" => $obj["id"],
				    "longitude" => $obj["longitude"],
				    "latitude" => $obj["latitude"],
				    "size" => $obj["size"],
				    "zoom" => $obj["zoom"]
				    ]
		  ];
	  $geojson["features"][] = $pt;
	}
      
      echo json_encode([ "result_code" => "ok",
			 "data" => $geojson ]);
    }
  else
    {
      throw new Exception("Invalid bounds parameters");
    }
}
catch(Exception $e)
{
  echo json_encode([ "result_code" => "error",
		     "result_msg" => $e->getMessage()
		     ]);
}

?>