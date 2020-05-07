<?php

class Parser
{
    public $titles = [];
    public $text = [];
    public $filesDocx = [];

//    дириктория где хранятся docx файлы
    public $dirDocx = 'docx_parse/';
//    дириктория где будут хранится html файлы
    public $dirHtml = 'html_create/';

    public function __construct()
    {
        $this->getNamesFilesDocx();
        $this->getTitleAndText();
    }

    //Запускает цикл, которые читает все файлы, сохраняет их заголовки в массив
    public function getTitleAndText()
    {
        foreach ($this->filesDocx as $file) {
            $this->titles[] = $this->readDocx($file)[0];
            $this->text[] = $this->readDocx($file)[1];
        }
    }

    //Читает файл и обрабатывает заголовок и текст
    function readDocx($filePath)
    {
        $zip = new ZipArchive;
        $dataFile = 'word/document.xml';
        if (true === $zip->open($filePath)) {
            if (($index = $zip->locateName($dataFile)) !== false) {
                $data = $zip->getFromIndex($index);
                $zip->close();
                $dom = new DOMDocument();
                $dom->loadXML($data, LIBXML_NOENT | LIBXML_XINCLUDE | LIBXML_NOERROR | LIBXML_NOWARNING);
                $textArr = explode('</w:p>', $dom->saveXML());
                $title = trim(strip_tags($textArr[0]));
                $text = '';
                for($i=1; $i < count($textArr); $i++)
                {
                    $text .= '<p style="font-size: 1em; font-family: Arial;">'.strip_tags($textArr[$i]).'</p>';
                }
                return [$this->titles[] = $title, $this->text[] = $text];
//
            }
            $zip->close();
        }
        return "";
    }

    //Запускает цикл, которые читает все файлы, сохраняет их заголовки в массив
    public function getNamesFilesDocx()
    {
        $scan = scandir($this->dirDocx);
        foreach ($scan as $file) {
            if (stristr($file, '.Docx')) {
                $this->filesDocx[] = $this->dirDocx . $file;
            }
        }
    }

    //Создает html файлы из обработанных заголовков
    public function createTemplateHTML()
    {
        $fileName = $this->titles;
        for ($i = 0; $i < count($fileName)-1; $i++) {
            $htmlFile = fopen(str_replace(' ','_',$this->dirHtml.$fileName[$i]).'.html', 'w') or die("can't open file");
            $str = '
                 <!doctype html>
                <html lang="ru">
                    <head>
                        <meta charset="UTF-8">
                        <meta name="viewport"
                            content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
                        <meta http-equiv="X-UA-Compatible" content="ie=edge">
                        <title>'.$this->titles[$i].' в Москве - Автосервис "Роверсити"</title>
                         <meta name="Description" content="'.$this->titles[$i].' в Москве. Бесплатная диагностика. Гарантия качества. Записаться - 8(495)150-70-69.">
                         <meta name="KeyWords" content="'.$this->titles[$i].' в Москве. Бесплатная диагностика. Гарантия качества. Записаться - 8(495)150-70-69.">
                    </head>
                    <body>
                        <h1>'.$this->titles[$i].'</h1> 
                        '.$this->text[$i].'
                    </body>
                </html>';
            fwrite($htmlFile, $str);
            fclose($htmlFile);
        }
    }

}

$p = new Parser();
$p->createTemplateHTML();
