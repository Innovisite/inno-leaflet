<?php

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
      $iLngMin = $map["bounds"][0] * 1000;
      $iLatMin = $map["bounds"][1] * 1000;
      $iLngMax = $map["bounds"][2] * 1000;
      $iLatMax = $map["bounds"][3] * 1000;
      for($i = 0; $i < 10; ++$i)
	{
	  $coords = [
		     rand((int) $iLngMin, (int) $iLngMax) / 1000.,
		     rand((int) $iLatMin, (int) $iLatMax) / 1000.
		     ];
	  $pt = [ "type" => "Feature",
		  "geometry" => [ "type" => "Point", "coordinates" => $coords ],
		  "properties" => [ "rand" => $i ]
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