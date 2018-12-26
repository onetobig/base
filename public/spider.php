<?php
abstract class Spider {

    public function get($url) {
        $content = file_get_contents($url);
        return $content;
    }

    abstract public function parseTitle($content);

    public function fetch($url){
        $content = $this->get($url);
        $title = $this->parseTitle($content);
        echo $title.PHP_EOL;
    }

}

class QQSpider extends Spider {

    public function parseTitle($content){
        preg_match("#<title>([^<>+]+)</title>#", $content, $mat);
        return $mat[1];
    }
}

class NeteaseSpider extends Spider {

    public function parseTitle($content) {
        preg_match("#<h1>([^<>+]+)</h1>#iUs", $content, $mat);
        return $mat[1];
    }
}

$spider = new QQSpider();
$spider->fetch('http://new.qq.com/omn/20181220/20181220A0W39K.html');

