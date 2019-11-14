<?php
// Esto le dice a PHP que usaremos cadenas UTF-8 hasta el final
mb_internal_encoding('UTF-8');
 
// Esto le dice a PHP que generaremos cadenas UTF-8
mb_http_output('UTF-8');

header("Content-type: text/html; charset=utf8");

$filename = "htmlVtex.html";
$url = 'http://spreadsheets.google.com/feeds/list/1OOGi745CMppTm-J0dIovw-josIwphoY3yuVHgph2xME/od6/public/values?alt=json';
$file= file_get_contents($url);
$json = json_decode($file);
$rows = $json->{'feed'}->{'entry'};
$result = array();

// Agrupamos por categoria
foreach($rows as $row) {
  $categorias = $row->{'gsx$cat'}->{'$t'};
  $result[$categorias][] = array(
    "title" => $row->{'gsx$title'}->{'$t'},
    "cat" => $row->{'gsx$cat'}->{'$t'},
    "alt" => $row->{'gsx$alt'}->{'$t'},
    "filadesktop" => $row->{'gsx$filadesktop'}->{'$t'},
    "filamobile" => $row->{'gsx$filamobile'}->{'$t'},
    "img" => $row->{'gsx$img'}->{'$t'},
    "url" => $row->{'gsx$url'}->{'$t'}
  );
}

// Determinamos Card - Number
// Recorre los grupos
$arrCard = array();
$arrBanners = array();
foreach($result as $fila) {
  // Recorre los items de los grupos
  $splitFila = array();
  // ----->
  foreach($fila as $row){
    if($row['title'] == "slider"){
      $arrBanners = array(
        "alt" => $row["alt"],
        "url" => $row["url"],
        "img" => $row["img"]
      );
    } else {
      if($row['title'] == "si"){    
        $splitFila[$row["cat"]] = array(
          "cat" => $row["cat"],
          "url" => $row["url"],
          "img" => $row["img"]
        );
      } else {
        // corregimos la agrupacion por #/-2
        $filadesktop = explode ("-", $row['filadesktop']);
        $splitFila[$row['cat']]['itemsDesktop'][$filadesktop[0]][] = $row;
        $splitFila[$row['cat']]['itemsMobile'][$row['filamobile']][] = $row;
      }
    }
  }
  $arrCard[] = $splitFila;
}

// var_dump("<pre>", $arrCard);
// exit;

?>
<!doctype html>
<html>
    <head>
      <meta charset="UTF-8">
      <title>Página de prueba UTF-8</title>
    </head>
  <body>
<?php
$setHml = '';

/**
 * 
 * Imprime el Menu
 * 
 */
// $setHml .= '<section class="CategoriesAnchor">';
// foreach ($arrCard as $section){
//   // Recorre la section
//   foreach($section as $key => $row){
//     if(isset($row['cat']) && $row['cat'] != ""){
//       $setHml .= '<div class="CategoriesAnchor__item">';
//         $setHml .= '<a href="#seccion-'. preg_replace('/[^a-z0-9]/i', '_', (strtolower($row['cat']))) .'">';
//           $setHml .= '<span class="pvaicon '. $row['img'] .'"></span>';
//           $setHml .= '<h6>'. $row['cat'] .'</h6>';
//         $setHml .= '</a>';
//       $setHml .= '</div>';
//     }
//   }
// }
// $setHml .= '</section>';

/**
 * 
 * Imprime el banner general
 * 
 */
// Banner general	desktop
// $setHml .= '<section class="BannerCyberday"> 
//             <div class="Section">
//               <a href="'. $arrBanners['url'] .'"> 
//                 <img src="https://plazavea.vteximg.com.br/arquivos/'. $arrBanners['img'] .'.jpg" title="'. $arrBanners['alt'] .'" style="width:100%" /> 
//               </a> 
//             </div> 
//           </section>';

// Banner general	mobile
// $setHml .= '<section class="BannerCyberday_mb">
//             <div class="Section">
//               <a href="'. $arrBanners['url'] .'">
//                 <img src="https://plazavea.vteximg.com.br/arquivos/'. str_replace("-D-", "-M-", $arrBanners['img']) .'.jpg" title="'. $arrBanners['alt'] .'" style="width:100%" />
//               </a>
//             </div>
//           </section>';

/**
 * 
 * Imprimimos los items
 * 
 */
// Recorre la agrupacion
foreach ($arrCard as $section){
  // Recorre la section
  foreach($section as $key =>  $row){
    if(isset($row['cat']) && $row['cat'] != ""){
      $setHml .= '<div class="Section" id="seccion-'. preg_replace('/[^a-z0-9]/i', '_', (strtolower($row['cat']))) .'">';
        
      // <! --- TITULO --->
        $setHml .= '<div class="MainTitle">
              <span class="MainTitle"></span>
              <h3>'. $row['cat'] .'</h3>';
          if($row['url'] != ""){
            $setHml .= '  <a href="'. $row['url'] .'">VER TODO</a>';
          }
        $setHml .= '</div>';
        // <! --- /TITULO --->

        // <! --- ITEMS PC --->
        foreach($row['itemsDesktop'] as $keyFilaDesktop => $fila){
          // Pint items Card
          
          // var_dump("<pre>", $fila);
          // exit;

          // Obtenemos la informacion por filas
          //  -- Preguntamos si es tiene algun SPLIT  de #/-2
          //  -- Si tenemos GUION medio entonces $trueDash == 1
          $trueDash = 0;
          foreach($fila as $rowInfo){
            if (strpos($rowInfo['filadesktop'], '-') !== false) {
              $trueDash = 1;
              break;
            }
          }

          // Cart Conten
          $countSection = $trueDash == 0 ? count($fila) : 2;
          $setHml .= '<div class="Card'. $countSection .'">';
          foreach($fila as $index => $rowInfo){
            // Cart child
            if($index == 0){
              $countSection = $trueDash == 0 ? count($fila) : 2;
            } else {
              $countSection = $trueDash == 0 ? count($fila) : (count($fila) + 1);
            }
            $setHml .= '
              <div class="Card'. $countSection .'__item">
                <a href="'. $rowInfo['url'] .'">
                  <img src="https://plazavea.vteximg.com.br/arquivos/'. $rowInfo['img'] .'.jpg" title="'. $rowInfo['alt'] .'" />
                </a>
              </div>';
          }
          // 

          $setHml .= '</div>';
        }
        // <! --- ITEMS PC --->

        // <! --- ITEMS MOVIL --->
        foreach($row['itemsMobile'] as $keyFilaDesktop => $fila){
          // Pint items Card
          $countSection = count($fila);
          $setHml .= '<div class="Card'. $countSection .'-mb">';
          foreach($fila as $rowInfo){
            $setHml .= '
              <div class="Card'. $countSection .'-mb__item">
                <a href="'. $rowInfo['url'] .'">
                  <img src="https://plazavea.vteximg.com.br/arquivos/'. str_replace("-D-", "-M-", $rowInfo['img']) .'.jpg" title="'. $rowInfo['alt'] .'" />
                </a>
              </div>';
          }
          $setHml .= '</div>';
        }
        // <! --- ITEMS MOVIL --->
      $setHml .= '</div>';
    }
  }
}

$setOuputHTml = $setHml;
echo $setOuputHTml;

//save the file...
// $fh = fopen($filename,"w");
// fwrite($fh, $setOuputHTml);
// fclose($fh);

// echo "<a href='".$filename."'>Click Here</a> to download the file...";

?>
</body>
</html>