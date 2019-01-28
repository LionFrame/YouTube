<?php
/**
 * Library Requirements
 *
 * 1. Install composer (https://getcomposer.org)
 * 2. On the command line, change to this directory (api-samples/php)
 * 3. Require the google/apiclient library
 *    $ composer require google/apiclient:~2.0
 */
if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
  throw new \Exception('please run "composer require google/apiclient:~2.0" in "' . __DIR__ .'"');
}
require_once __DIR__ . '/vendor/autoload.php';
$htmlBody = <<<END
<form method="GET">
  <label>
    Условие поиска: <input type="search" id="q" name="q" placeholder="Ваш запрос">
  </label>
  <input type="submit" value="Поиск">
</form>
END;
// This code will execute if the user entered a search query in the form
// and submitted the form. Otherwise, the page displays the form above.
if (isset($_GET['q'])) {
  /*
   * Set $DEVELOPER_KEY to the "API key" value from the "Access" tab of the
   * {{ Google Cloud Console }} <{{ https://cloud.google.com/console }}>
   * Please ensure that you have enabled the YouTube Data API for your project.
   */
  $DEVELOPER_KEY = 'AIzaSyBnd8Wmi71lLjlIcJXD8XRknXAMRxISLOs';
  $client = new Google_Client();
  $client->setDeveloperKey($DEVELOPER_KEY);
  // Define an object that will be used to make all API requests.
  $youtube = new Google_Service_YouTube($client);
  $htmlBody = '';
  $videoResults = array();
  $query = $_GET['q'];
  try {
    // Call the search.list method to retrieve results matching the specified
    // query term.
    $searchResponse = $youtube->search->listSearch('snippet', array('maxResults' => 20, 'q' => $_GET['q'], 'type'=>'video', 'order'=>'date'));
    $videos = '';
    // Add each result to the appropriate list, and then display the lists of
    // matching videos.
    // По сути должен быть только один вызов foreach по listSearch получаем $массив ID новых видео объединяем в строку push(',',$массив) передаем строку в listVideos затем его сортируем
    foreach ($searchResponse['items'] as $searchResult) {
          $varPublishedAt=str_replace(["T",".000Z"], " ", $searchResult['snippet']['publishedAt']);

          $response = $youtube->videos->listVideos("statistics",
          array('id' => $searchResult['id']['videoId']));

          foreach ($response['items'] as $searchId) {
            //echo $searchId['statistics']['viewCount']. "<br>";
            //echo $searchId['id'] . "<br>";
            $viewCount = $searchId['statistics']['viewCount'];

            //$videoResults[]=[$searchId['id'],$searchId['statistics']['viewCount']];
          }
           //print_r($videoResults);

          !(empty($viewCount)) or $viewCount='0';
          $videos .= sprintf("<li>
          <div class='panel-heading'>
          Название: %s<br>
          Автор: %s<br>
          Дата публикации: %s<br>
          Количество просмотров: %s<br>
          </div>
          <div class='videoFromYouTube'>
            <iframe width='560' height='315' src='https://www.youtube.com/embed/%s' frameborder='0' allow='accelerometer; encrypted-media; gyroscope; picture-in-picture' allowfullscreen></iframe>
          </div>
          </li>",
              $searchResult['snippet']['title'], $searchResult['snippet']['channelTitle'],$varPublishedAt,$viewCount,$searchResult['id']['videoId'],$searchResult['id']['videoId']);

    }
    $htmlBody .="
    <h3>Результат поиска по запросу: $query</h3>
    <ol class='slide'>$videos</ol>";
;
  } catch (Google_Service_Exception $e) {
    $htmlBody .= sprintf('<p>Произошла ошибка службы: <code>%s</code></p>',
      htmlspecialchars($e->getMessage()));
  } catch (Google_Exception $e) {
    $htmlBody .= sprintf('<p>Произошла ошибка клиента: <code>%s</code></p>',
      htmlspecialchars($e->getMessage()));
  }
}
?>

<!doctype html>
<html>
  <head>
    <meta charset="utf-8">
    <title>YouTube Search</title>
    <script type="text/javascript" src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
  </head>
  <body>

    <?=$htmlBody?>



  <br>
  <br>
  <!--<?php // var_dump($searchResponse['items']); ?> -->
 <script type="text/javascript">
 $(document).ready(function () {
   $('div.panel-heading').next().hide();
     $('div.panel-heading').click(function(){
     $(this).next().slideToggle();
     $('div.panel-heading').not(this).next().stop(true,true).slideUp()
      });
    });
  </script>

  </body>
</html>
