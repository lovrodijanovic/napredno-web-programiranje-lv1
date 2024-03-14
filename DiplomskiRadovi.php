<?php 
interface iRadovi {
    public function create($data);
    public function save();
    public function read();
}

class DiplomskiRadovi implements iRadovi {
    private $_naziv_rada = NULL;
    private $_text_rada = NULL;
    private $_link_rada = NULL;
    private $_oib_tvrtke = NULL;

    function __construct($data) {
        $this->_naziv_rada = $data['naziv_rada'];
        $this->_text_rada = $data['text_rada'];
        $this->_link_rada = $data['link_rada'];
        $this->_oib_tvrtke = $data['oib_tvrtke'];
    }

    function create($data) {
        self::__construct($data);
    }

    function save()
    {
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "lv1";

        $conn = new mysqli($servername, $username, $password, $dbname);

        $naziv = $this->naziv_rada;
        $tekst = $this->tekst_rada;
        $link = $this->link_rada;
        $oib = $this->oib_tvrtke;

        $sql = "INSERT INTO `diplomski_radovi` (`naziv_rada`, `tekst_rada`, `link_rada`, `oib_tvrtke`) VALUES ('$naziv', '$tekst', '$link', '$oib')";
        if ($conn->query($sql) === true) {
            $this->read();
        } else {
            die("Error: " . $conn->connect_error);
        };
        $conn->close();
    }

    function read()
    {
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "lv1";

        $conn = new mysqli($servername, $username, $password, $dbname);

        if ($conn->connect_error) {
            die("Error: " . $conn->connect_error);
        }

        $sql = "SELECT * FROM `diplomski_radovi`";
        $output = $conn->query($sql);
        if ($output->num_rows > 0) {
            while ($item = $output->fetch_assoc()) {
                echo "<p>Naziv rada: " . $item["id"] . "</p>";
                echo "<p>Tekst rada: " . $item["tekst_rada"] . "</p>";
                echo "<p>Link rada: <a href='{$item["link_rada"]}' target='_blank'>{$item["link_rada"]}</a></p>";
                echo "<p>OIB tvrtke: " . $item["oib_tvrtke"] . "</p>";
            }
        }
        $conn->close();
    }

        public function fetch($redni_broj)
    {
        $url = "https://stup.ferit.hr/index.php/zavrsni-radovi/page/$redni_broj/";

        $curl = curl_init($url);

        curl_setopt($curl, CURLOPT_FAILONERROR, 1);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_TIMEOUT, 5);

        $response = curl_exec($curl);

        curl_close($curl);

        $dom = new simple_html_dom();
        $dom->load($response);

        foreach ($dom->find('article') as $article) {
            $slides = $article->find('ul.slides img');
            $titleLinks = $article->find('h2.entry-title a');
            foreach ($titleLinks as $index => $link) {
                $html = file_get_html($link->href);
                $postContent = $this->fetchTekst($link->href);
                $oib_tvrtke = isset($slides[$index]) ? preg_replace('/[^0-9]/', '', $slides[$index]->src) : '';
                $new_rad = [
                    'naziv_rada' => $link->plaintext,
                    'tekst_rada' => $postContent,
                    'link_rada' => $link->href,
                    'oib_tvrtke' => $oib_tvrtke
                ];
            }
        }

        $rad = $this->create($new_rad);
        $rad = $this->save();
        $rad = $this->format();

        return $rad;
    }

    private function fetchTekst($postLink)
    {
        $curl = curl_init($postLink);

        curl_setopt($curl, CURLOPT_FAILONERROR, 1);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_TIMEOUT, 5);

        $response = curl_exec($curl);

        curl_close($curl);

        $html = new simple_html_dom();
        $html->load($response);

        $postContent = $html->find('.post-content p', 0);

        return $postContent ? $postContent->plaintext : '';
    }

    public function format()
    {
        echo "<p>Naziv rada: " . $this->naziv_rada . "</p>";
        echo "<p>Tekst rada: " . $this->tekst_rada . "</p>";
        echo "<p>Link rada: <a href='{$this->link_rada}' target='_blank'>{$this->link_rada}</a></p>";
        echo "<p>OIB tvrtke: " . $this->oib_tvrtke . "</p>";
    }
}

$url = 'http://localhost/pr1/service.php';
$curl = curl_init($url);   
curl_setopt($curl, CURLOPT_FAILONERROR, 1); 
curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($curl, CURLOPT_TIMEOUT, 5);
curl_setopt($curl, CURLOPT_POST, 1);
curl_setopt($curl, CURLOPT_POSTFIELDS, 'name=Pero&pass=nekalozinka&format=json');

$r = curl_exec($curl);

curl_close($curl);

print_r($r);
?>