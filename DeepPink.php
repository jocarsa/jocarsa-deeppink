<?php
class DeepPink {
    private $contenido;
    private $dom;

    public function __construct($nuevaurl) {
        // Intentar obtener el contenido de la URL
        $this->contenido = @file_get_contents($nuevaurl);
        
        if ($this->contenido === false) {
            die("Error: No se pudo obtener el contenido de la URL.");
        }
        
        // Crear un nuevo DOMDocument y cargar el HTML
        $this->dom = new DOMDocument();
        libxml_use_internal_errors(true); // Evitar advertencias por HTML mal formado
        $this->dom->loadHTML($this->contenido);
        libxml_clear_errors();
    }

    public function dameTitulo() {
        $nodos = $this->dom->getElementsByTagName('title');
        echo "<tr>";
            echo '<td>';
                if ($nodos->length > 0) {
                    echo "<div class='ok'></div>";
                } else {
                    echo "<div class='ko'></div>";
                }
            echo '</td>';
            echo '<td>';
                echo "<h4>Título del sitio</h4>";
            echo '</td>';
            echo '<td>';
                if ($nodos->length > 0) {
                    echo $nodos->item(0)->textContent;
                }
            echo '</td>';
        echo "</tr>";
    }
    
    public function dameDescripcion() {
        echo "<tr>";
            echo '<td>';
                $metaTags = $this->dom->getElementsByTagName('meta');
                foreach ($metaTags as $meta) {
                    if ($meta->getAttribute('name') === 'description') {
                        echo "<div class='ok'></div>";
                    }
                }
            echo '</td>';
            echo '<td>';
                echo "<h4>Descripción del sitio</h4>";
            echo '</td>';
            echo '<td>';
                $metaTags = $this->dom->getElementsByTagName('meta');
                foreach ($metaTags as $meta) {
                    if ($meta->getAttribute('name') === 'description') {
                        echo $meta->getAttribute('content');
                        return;
                    }
                }
                echo "<div class='ko'></div>";
            echo '</td>';
        echo "</tr>";
    }
    public function dameTitulos($nivel) {
        echo "<tr>";
        	$titulo1 = $this->dom->getElementsByTagName('h'.$nivel);
            echo '<td>';
                if ($titulo1->length > 0) {
                	echo "<div class='ok'></div>";
                }else{
                		echo "<div class='ko'></div>";
                }
                
            echo '</td>';
            echo '<td>';
                echo "<h4>Etiquetas de tipo H".$nivel."</h4>";
            echo '</td>';
            echo '<td>';
                $titulo1 = $this->dom->getElementsByTagName('h'.$nivel);
                foreach ($titulo1 as $titulo) {
                     echo $titulo->textContent."<br>";
                }
             
            echo '</td>';
        echo "</tr>";
    }
    
    public function damePalabras() {
			 // Array de stopwords en español
			 $stopwords = array(
				  "a", "acá", "ahí", "al", "algo", "algunas", "algunos", "allá", "allí", "ambos",
				  "ante", "antes", "aquel", "aquella", "aquellas", "aquellos", "aquí", "arriba",
				  "así", "atrás", "bajo", "bien", "cabe", "cada", "casi", "como", "con", "conmigo",
				  "conseguir", "consigo", "consigue", "consiguen", "contra", "cual", "cuales",
				  "cualquier", "cualquiera", "cuándo", "cuanta", "cuanto", "cuatro", "cuya", "cuyas",
				  "cuyo", "cuyos", "de", "del", "desde", "donde", "dos", "el", "él", "ella",
				  "ellas", "ellos", "en", "encima", "entre", "era", "eras", "éramos", "eran",
				  "eres", "es", "esa", "esas", "ese", "eso", "esos", "esta", "estaba", "estabais",
				  "estábamos", "estaban", "está", "estamos", "están", "este", "esto", "estos",
				  "estoy", "etc", "fue", "fuera", "fuimos", "ha", "había", "habéis", "habíamos",
				  "habían", "hace", "hacen", "hacer", "hacia", "hago", "incluso", "la", "las",
				  "lo", "los", "más", "me", "mi", "mis", "mía", "mías", "mío", "míos", "muy",
				  "nada", "ni", "no", "nos", "nosotras", "nosotros", "nuestra", "nuestras",
				  "nuestro", "nuestros", "o", "os", "otra", "otras", "otro", "otros", "para",
				  "pero", "poco", "por", "porque", "que", "quien", "quienes", "qué", "se",
				  "si", "sí", "siendo", "sin", "sobre", "sois", "solo", "somos", "soy", "su",
				  "sus", "también", "tan", "tanto", "te", "tendrá", "tendremos", "tienen",
				  "tener", "tengo", "ti", "tiene", "todo", "todos", "tu", "tus", "un", "una",
				  "unas", "uno", "unos", "vosotras", "vosotros", "vuestra", "vuestras", "ya", "yo","y"
			 );

			 // Obtener el contenido del body del HTML
			 $body = $this->dom->getElementsByTagName('body')->item(0);
			 if (!$body) {
				  die("Error: No se pudo obtener el contenido del cuerpo del HTML.");
			 }

			 // Eliminar etiquetas <script> y <style> para ignorar su contenido
			 $scriptTags = $body->getElementsByTagName('script');
			 for ($i = $scriptTags->length - 1; $i >= 0; $i--) {
				  $script = $scriptTags->item($i);
				  $script->parentNode->removeChild($script);
			 }
			 $styleTags = $body->getElementsByTagName('style');
			 for ($i = $styleTags->length - 1; $i >= 0; $i--) {
				  $style = $styleTags->item($i);
				  $style->parentNode->removeChild($style);
			 }

			 // Extraer el contenido textual del body (sin <script> ni <style>)
			 $textContent = $body->textContent;

			 // Eliminar caracteres especiales y números, dejando solo letras y espacios
			 $cleanText = preg_replace('/[^a-zA-Z\s]/', '', $textContent);

			 // Convertir a minúsculas y dividir en palabras
			 $words = preg_split('/\s+/', strtolower($cleanText));

			 // Contar la frecuencia de cada palabra
			 $wordCount = array_count_values($words);

			 // Eliminar entradas vacías
			 if (isset($wordCount[""])) {
				  unset($wordCount[""]);
			 }

			 // Filtrar las stopwords del array de palabras contadas
			 foreach ($stopwords as $stopword) {
				  if (isset($wordCount[$stopword])) {
				      unset($wordCount[$stopword]);
				  }
			 }

			 // Ordenar las palabras por frecuencia en orden descendente
			 arsort($wordCount);

			 // Mostrar los resultados en una fila de tabla
			 echo "<tr>";
				  echo '<td>';
				      if (!empty($wordCount)) {
				          echo "<div class='ok'></div>";
				      } else {
				          echo "<div class='ko'></div>";
				      }
				  echo '</td>';
				  echo '<td>';
				      echo "<h4>Palabras más frecuentes (sin stopwords ni contenido de script/style)</h4>";
				  echo '</td>';
				  echo '<td>';
				      foreach ($wordCount as $word => $count) {
				      	if($count > 2){
				          	echo $word . ": " . $count . "<br>";
				          }
				      }
				  echo '</td>';
			 echo "</tr>";
		}
	public function nubeDePalabras() {
			 // Array de stopwords en español
			 $stopwords = array(
				  "a", "acá", "ahí", "al", "algo", "algunas", "algunos", "allá", "allí", "ambos",
				  "ante", "antes", "aquel", "aquella", "aquellas", "aquellos", "aquí", "arriba",
				  "así", "atrás", "bajo", "bien", "cabe", "cada", "casi", "como", "con", "conmigo",
				  "conseguir", "consigo", "consigue", "consiguen", "contra", "cual", "cuales",
				  "cualquier", "cualquiera", "cuándo", "cuanta", "cuanto", "cuatro", "cuya", "cuyas",
				  "cuyo", "cuyos", "de", "del", "desde", "donde", "dos", "el", "él", "ella",
				  "ellas", "ellos", "en", "encima", "entre", "era", "eras", "éramos", "eran",
				  "eres", "es", "esa", "esas", "ese", "eso", "esos", "esta", "estaba", "estabais",
				  "estábamos", "estaban", "está", "estamos", "están", "este", "esto", "estos",
				  "estoy", "etc", "fue", "fuera", "fuimos", "ha", "había", "habéis", "habíamos",
				  "habían", "hace", "hacen", "hacer", "hacia", "hago", "incluso", "la", "las",
				  "lo", "los", "más", "me", "mi", "mis", "mía", "mías", "mío", "míos", "muy",
				  "nada", "ni", "no", "nos", "nosotras", "nosotros", "nuestra", "nuestras",
				  "nuestro", "nuestros", "o", "os", "otra", "otras", "otro", "otros", "para",
				  "pero", "poco", "por", "porque", "que", "quien", "quienes", "qué", "se",
				  "si", "sí", "siendo", "sin", "sobre", "sois", "solo", "somos", "soy", "su",
				  "sus", "también", "tan", "tanto", "te", "tendrá", "tendremos", "tienen",
				  "tener", "tengo", "ti", "tiene", "todo", "todos", "tu", "tus", "un", "una",
				  "unas", "uno", "unos", "vosotras", "vosotros", "vuestra", "vuestras", "ya", "yo","y"
			 );

			 // Obtener el contenido del body del HTML
			 $body = $this->dom->getElementsByTagName('body')->item(0);
			 if (!$body) {
				  die("Error: No se pudo obtener el contenido del cuerpo del HTML.");
			 }

			 // Eliminar etiquetas <script> y <style> para ignorar su contenido
			 $scriptTags = $body->getElementsByTagName('script');
			 for ($i = $scriptTags->length - 1; $i >= 0; $i--) {
				  $script = $scriptTags->item($i);
				  $script->parentNode->removeChild($script);
			 }
			 $styleTags = $body->getElementsByTagName('style');
			 for ($i = $styleTags->length - 1; $i >= 0; $i--) {
				  $style = $styleTags->item($i);
				  $style->parentNode->removeChild($style);
			 }

			 // Extraer el contenido textual del body (sin <script> ni <style>)
			 $textContent = $body->textContent;

			 // Eliminar caracteres especiales y números, dejando solo letras y espacios
			 $cleanText = preg_replace('/[^a-zA-Z\s]/', '', $textContent);

			 // Convertir a minúsculas y dividir en palabras
			 $words = preg_split('/\s+/', strtolower($cleanText));

			 // Contar la frecuencia de cada palabra
			 $wordCount = array_count_values($words);

			 // Eliminar entradas vacías
			 if (isset($wordCount[""])) {
				  unset($wordCount[""]);
			 }

			 // Filtrar las stopwords del array de palabras contadas
			 foreach ($stopwords as $stopword) {
				  if (isset($wordCount[$stopword])) {
				      unset($wordCount[$stopword]);
				  }
			 }

			 // Ordenar las palabras por frecuencia en orden descendente
			 arsort($words);

			 // Mostrar los resultados en una fila de tabla
			 echo "<tr>";
				  echo '<td>';
				      if (!empty($wordCount)) {
				          echo "<div class='ok'></div>";
				      } else {
				          echo "<div class='ko'></div>";
				      }
				  echo '</td>';
				  echo '<td>';
				      echo "<h4>Palabras más frecuentes</h4>";
				  echo '</td>';
				  echo '<td>';
				      foreach ($wordCount as $word => $count) {
				         if($count > 2){
				          	echo "<span style='font-size:" . ($count*5) . "px'>".$word . "</span>";
				          }
				      }
				  echo '</td>';
			 echo "</tr>";
		}

    public function dameEnlaces() {
        $enlaces = $this->dom->getElementsByTagName('a');

        if ($enlaces->length == 0) {
            echo "<div class='ko'></div>";
            return;
        }
        echo "<div class='ok'></div>";
        foreach ($enlaces as $valor) {
            echo $valor->textContent . " - " . $valor->getAttribute("href") . "<br>";
        }
    }
}
?>

