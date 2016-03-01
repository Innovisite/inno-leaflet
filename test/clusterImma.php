<?php

function distance($v0, $v1)
{
  return sqrt( pow( $v0[0] - $v1[0], 2 ) + pow( $v0[1] - $v1[1], 2 ) );
}

// lon = -180 west, 180 east
// lat = 90 top, -90 bottom
// !!! input vector is lon, lat returned vector is X Y 
function convXY($v, $latRef)
{
  $R = 6371000.0; // earth radius in meters
  return array( $R * deg2rad($v[0]) * cos(deg2rad($latRef)),
		$R * deg2rad($v[1]),
		);		
}

function convLatLon($v, $latRef)
{
  $R = 6371000.0; // earth radius in meters
  return array(
	       rad2deg($v[0] / $R),
	       rad2deg($v[1] / ($R*cos( deg2rad($latRef) )))
	       );
}

function zoomAccuracy($z, $latRef)
{
  $C = 40075160; // earth circumference in meters
  $zpow = 1 << ($z + 8); // for 256 pixels wide tiles
  // S=C*cos(y)/2^(z+8)
  return $C*cos(deg2rad($latRef))/$zpow;
}

function getClusterDistance($c1, $c2)
{
  $pt1 = convXY([ $c1["longitude"], $c1["latitude"] ],
		$c1["latitude"]);
  $pt2 = convXY([ $c2["longitude"], $c2["latitude"] ],
		$c1["latitude"]);
  return distance($pt1, $pt2);
}

function addToCluster(&$cluster, $pt)
{
  // update bbox
  $cluster["bbox"] = [
		      min($cluster["bbox"][0], $pt["longitude"]),
		      max($cluster["bbox"][1], $pt["latitude"]),
		      max($cluster["bbox"][2], $pt["longitude"]),
		      min($cluster["bbox"][3], $pt["latitude"])
		      ];
  $cluster["size"] += 1;
  $cluster["longitude"] = ($cluster["bbox"][0] + $cluster["bbox"][2]) / 2.0;
  $cluster["latitude"] = ($cluster["bbox"][1] + $cluster["bbox"][3]) / 2.0;
}

function createNode($id, $obj, $minZoom, $maxZoom, $parent)
{
  $res = [
	  "id" => $id,
	  "longitude" => $obj["longitude"],
	  "latitude" => $obj["latitude"],
	  "size" => 1,
	  "bbox" => [
		     $obj["longitude"],
		     $obj["latitude"],
		     $obj["longitude"],
		     $obj["latitude"]
		     ],
	  "minZoom" => $minZoom,
	  "maxZoom" => $maxZoom,
	  "childs" => [],
	  "parent" => $parent
	  ];  
  return $res;
}

function recurChilds($obj, $cluster, &$clusters, $maxZoom, $parent)
{
  $res = null;
  $d = getClusterDistance($clusters[$cluster], $obj);
  $dBreak = null;
  for($s = $clusters[$cluster]["minZoom"]; is_null($dBreak) && 
	$s <= $clusters[$cluster]["maxZoom"]; ++$s)
    {
      if($d > 64*zoomAccuracy($s, $clusters[$cluster]["latitude"]))
	{
	  $dBreak = $s;
	}
    }
  if(is_null($dBreak))
    {
      addToCluster($clusters[$cluster], $obj);
      if($maxZoom > $clusters[$cluster]["maxZoom"])
	{
	  $added = null;
	  for($i = 0; is_null($added) && $i < count($clusters[$cluster]["childs"]); ++$i)
	    {
	      $added = recurChilds($obj, $clusters[$cluster]["childs"][$i], 
				     $clusters, $maxZoom, $cluster);
	    }
	  if(is_null($added))
	    {
	      //echo "createNode ==== not dbreak === not added\n";
	      $newIdx = count($clusters);
	      $clusters[$newIdx] = createNode($newIdx, $obj, 
					      $clusters[$cluster]["maxZoom"] + 1, 
					      $maxZoom, 
					      $cluster);
	      $clusters[$cluster]["childs"][] = $newIdx;
	      $res = $newIdx;
	      //print_r($clusters[$newIdx]);
	    }
	  else
	    {
	      $res = $added;
	    }
	}
    }
  else if($dBreak > $clusters[$cluster]["minZoom"])
    {
      //echo "createNode ==== dbreak ==== old\n";
      //echo "replace =======================\n";
      //print_r($clusters[$cluster]);

      $oldIdx = count($clusters);
      $clusters[$oldIdx] = createNode($oldIdx, $clusters[$cluster], $dBreak,
				      $clusters[$cluster]["maxZoom"], $cluster);
      $clusters[$oldIdx]["childs"] = $clusters[$cluster]["childs"]; // must reset parent
      $clusters[$oldIdx]["size"] = $clusters[$cluster]["size"];

      $newIdx = count($clusters);
      $clusters[] = createNode($newIdx, $obj, $dBreak,
			       $maxZoom, $cluster);

      addToCluster($clusters[$cluster], $clusters[$newIdx]);
      $clusters[$cluster]["maxZoom"] = $dBreak - 1;
      $clusters[$cluster]["childs"] = [ $oldIdx, $newIdx ];
      $res = $newIdx;

      //echo "by =============================\n";
      //print_r($clusters[$cluster]);
      //print_r($clusters[$oldIdx]);
      //print_r($clusters[$newIdx]);
    }
  return $res;
}

if(count($argv) < 2) 
  {
    die("Missing parameter <zoomlevel>\n");
  }

$zoomLevel = $argv[1];

include_once("BluePHP/Utils/DBConnect.inc");

$db = new DBConnect("SQLITE3", "", "../test/imma2016.db", "", "");
$db->connectToDB();
$query = "select * from imma";
$res = $db->query($query);

$clusters = [];
$cluster = null;
$root = null;
$parent = null;
while($obj = $res->nextAssoc())
  {
    $obj["longitude"] = intval($obj["longitude"]) / 10000.;
    $obj["latitude"] = intval($obj["latitude"]) / 10000.;

    //print_r($obj);

    if(is_null($root))
      {
	//echo "createNode root ==========\n";
	$clusters[] = createNode(0, $obj, 0, $zoomLevel, null);
	$root = 0;
	//print_r($clusters[0]);
      }
    else
      {	
	recurChilds($obj, $root, $clusters, $zoomLevel, null);
      }
  }


$str = "begin;\n";

for($i = 0; $i < count($clusters); ++$i) 
  {
    $lon = (int) (floatval($clusters[$i]["longitude"]) * 10000);
    $lat = (int) (floatval($clusters[$i]["latitude"]) * 10000);
    $size = $clusters[$i]["size"];
    $minZoom = $clusters[$i]["minZoom"];
    $maxZoom = $clusters[$i]["maxZoom"];
    $str .= "insert into clusters (longitude,latitude,minZoom,maxZoom,size) " . 
      "values($lon, $lat, $minZoom, $maxZoom, $size);\n";
    //print_r($clusters[$i]);
  }

$str .= "commit;\n";

echo $str;

?>