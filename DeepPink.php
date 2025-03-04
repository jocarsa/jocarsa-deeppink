<?php
class DeepPink {
    private $contenido;
    private $dom;

    public function __construct($nuevaurl) {
        // Attempt to get the URL content
        $this->contenido = @file_get_contents($nuevaurl);
        if ($this->contenido === false) {
            die("Error: Could not retrieve content from the URL.");
        }
        
        // Create a new DOMDocument and load the HTML
        $this->dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $this->dom->loadHTML($this->contenido);
        libxml_clear_errors();
    }

    public function dameTitulo() {
        $nodos = $this->dom->getElementsByTagName('title');
        echo "<tr>";
            echo '<td>';
                echo ($nodos->length > 0) ? "<div class='ok'></div>" : "<div class='ko'></div>";
            echo '</td>';
            echo '<td><h4>Título del sitio</h4></td>';
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
            echo '<td><h4>Descripción del sitio</h4></td>';
            echo '<td>';
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
            $titulos = $this->dom->getElementsByTagName('h'.$nivel);
            echo '<td>';
                echo ($titulos->length > 0) ? "<div class='ok'></div>" : "<div class='ko'></div>";
            echo '</td>';
            echo "<td><h4>Etiquetas de tipo H{$nivel}</h4></td>";
            echo '<td>';
            foreach ($titulos as $titulo) {
                echo $titulo->textContent."<br>";
            }
            echo '</td>';
        echo "</tr>";
    }
    
    public function damePalabras() {
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

        $body = $this->dom->getElementsByTagName('body')->item(0);
        if (!$body) {
            die("Error: Could not get the body content.");
        }
        // Remove <script> and <style> tags
        $scriptTags = $body->getElementsByTagName('script');
        for ($i = $scriptTags->length - 1; $i >= 0; $i--) {
            $scriptTags->item($i)->parentNode->removeChild($scriptTags->item($i));
        }
        $styleTags = $body->getElementsByTagName('style');
        for ($i = $styleTags->length - 1; $i >= 0; $i--) {
            $styleTags->item($i)->parentNode->removeChild($styleTags->item($i));
        }
        $textContent = $body->textContent;
        $cleanText = preg_replace('/[^a-zA-Z\s]/', '', $textContent);
        $words = preg_split('/\s+/', strtolower($cleanText));
        $wordCount = array_count_values($words);
        if (isset($wordCount[""])) unset($wordCount[""]);
        foreach ($stopwords as $stopword) {
            if (isset($wordCount[$stopword])) {
                unset($wordCount[$stopword]);
            }
        }
        arsort($wordCount);
        echo "<tr>";
            echo '<td>' . (!empty($wordCount) ? "<div class='ok'></div>" : "<div class='ko'></div>") . '</td>';
            echo "<td><h4>Palabras más frecuentes (sin stopwords ni contenido de script/style)</h4></td>";
            echo "<td>";
                foreach ($wordCount as $word => $count) {
                    if($count > 2){
                        echo $word . ": " . $count . "<br>";
                    }
                }
            echo "</td>";
        echo "</tr>";
    }

    public function nubeDePalabras() {
        // Essentially similar to damePalabras(), but shows words as a cloud.
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

        $body = $this->dom->getElementsByTagName('body')->item(0);
        if (!$body) {
            die("Error: Could not get the body content.");
        }
        $scriptTags = $body->getElementsByTagName('script');
        for ($i = $scriptTags->length - 1; $i >= 0; $i--) {
            $scriptTags->item($i)->parentNode->removeChild($scriptTags->item($i));
        }
        $styleTags = $body->getElementsByTagName('style');
        for ($i = $styleTags->length - 1; $i >= 0; $i--) {
            $styleTags->item($i)->parentNode->removeChild($styleTags->item($i));
        }
        $textContent = $body->textContent;
        $cleanText = preg_replace('/[^a-zA-Z\s]/', '', $textContent);
        $words = preg_split('/\s+/', strtolower($cleanText));
        $wordCount = array_count_values($words);
        if (isset($wordCount[""])) unset($wordCount[""]);
        foreach ($stopwords as $stopword) {
            if (isset($wordCount[$stopword])) {
                unset($wordCount[$stopword]);
            }
        }
        arsort($wordCount);
        echo "<tr>";
            echo '<td>' . (!empty($wordCount) ? "<div class='ok'></div>" : "<div class='ko'></div>") . '</td>';
            echo "<td><h4>Palabras más frecuentes</h4></td>";
            echo "<td>";
            foreach ($wordCount as $word => $count) {
                if($count > 2){
                    echo "<span style='font-size:" . ($count * 5) . "px'>" . $word . "</span> ";
                }
            }
            echo "</td>";
        echo "</tr>";
    }
}
?>

