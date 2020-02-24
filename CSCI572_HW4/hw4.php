<?php
// Tianxin Zhou for CSCI572 hw4
// zhou631@usc.edu
// make sure browsers see this page as utf-8 encoded HTML
header('Content-Type: text/html; charset=utf-8');
$limit = 10;
$query = isset($_REQUEST['q']) ? $_REQUEST['q'] : false;
$results = false;
$additionalParameters = array(
  'sort' => 'pageRankFile desc'
);
$urlmap = array_map('str_getcsv', file('URLtoHTML_yahoo_news.csv'));

if ($query)
{
 // The Apache Solr Client library should be on the include path
 // which is usually most easily accomplished by placing in the
 // same directory as this script ( . or current directory is a default
 // php include path entry in the php.ini)
 require_once('solr-php-client-master/Apache/Solr/Service.php');
 // create a new solr service instance - host, port, and corename
 // path (all defaults in this example)
 $solr = new Apache_Solr_Service('localhost', 8983, '/solr/hw4');
 // if magic quotes is enabled then stripslashes will be needed
 if (get_magic_quotes_gpc() == 1)
 {
 $query = stripslashes($query);
 }
 // in production code you'll always want to use a try /catch for any
 // possible exceptions emitted by searching (i.e. connection
 // problems or a query parsing error)
 try
 {
   if($_GET['method'] == "pagerank")
   {
     $results = $solr->search($query, 0, $limit, $additionalParameters);

   }
   else {
     $results = $solr->search($query, 0, $limit);
   }
 }
 catch (Exception $e)
 {
 // in production you'd probably log or email this error to an admin
 // and then show a special message to the user but for this example
 // we're going to show the full exception
 die("<html><head><title>SEARCH EXCEPTION</title><body><pre>{$e->__toString()}</pre></body></html>");
 }
}
?>
<html>
 <head>
 <title>HW4</title>
 </head>
 <body>
 <form accept-charset="utf-8" method="get">
<center>
   <label for="q">Search:</label>



 <input id="q" name="q" type="text" value="<?php echo htmlspecialchars($query, ENT_QUOTES, 'utf-8'); ?>"/>
</center>
<center>
  <span>Algorithm: </span>
  <input type="radio" name="method" value="lucene" <?php if($_REQUEST['method'] == "lucene") {echo 'checked ="checked"';} ?>> Lucene
  <input type="radio" name="method" value="pagerank" <?php if($_REQUEST['method'] ==  "pagerank") {echo 'checked ="checked"';} ?>> Pagerank
  <input type="submit"/>
</center>


 </form>
<?php
// display results
if ($results)
{
 $total = (int) $results->response->numFound;
 $start = min(1, $total);
 $end = min($limit, $total);
?>
 <div>Results <?php echo $start; ?> - <?php echo $end;?> of <?php echo $total; ?>:</div>
 <ol>
<?php
 // iterate result documents
 foreach ($results->response->docs as $doc)
 {
?>
  <li>

<?php
  $title = 'NA';
  $url = $doc->og_url;
  $descript = "NA";
  $id ="NA";

  if ($doc->description != null ||$doc->description != '' )
  {
    $descript = $doc->description;
  }

  if ($doc->id != null ||$doc->id != '' )
  {
    $id = $doc->id;
  }

  if ($doc->title != null ||$doc->title != '' )
  {
    $title = $doc->title;
  }

  if ($doc -> og_url == null ||$doc -> og_url == '' )
  {
    $url= "NA";
    foreach($urlmap as $map)
    {
      $temp = "/Users/tianxin/Downloads/Yahoo/yahoo/" ;
      if($id == $temp.$map[0])
      {
        $url = $map[1];
        break;
      }
    }
  }

  echo "<tr>
  Title:          <a href = '$url'>$title</a> </br>
  URL:            <a href = '$url'>$url</a> </br>
  ID:             $id</br>
  Description:    $descript
  </br>
  </tr>";

?>

  </li>
</br>
<?php
}
?>
  </ol>
<?php
}
?>
  </body>
</html>
