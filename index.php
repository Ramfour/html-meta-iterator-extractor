<?php
/**
 * Практическое задание 26.6.1
 * Извлечение мета-тегов (title, description, keywords) из HTML с помощью итераций
 */

// HTML-код из задания
$html = <<<'HTML'
<html class="sb-init"><head>
	<base href="/templates/modex/">
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link href="/favicon.ico" rel="shortcut icon">
    <!-- CSS -->
	<link href="assets/css/preload.css" rel="stylesheet" type="text/css">
	<link href="assets/css/vendors.css" rel="stylesheet" type="text/css">
	<link href="assets/css/syntaxhighlighter/shCore.css" rel="stylesheet" type="text/css">
	<link href="/lib/jQuery_ui/jquery-ui.css" rel="stylesheet" type="text/css">
	<link href="assets/css/style_color.css?v=11" rel="stylesheet" type="text/css" title="default">
	<link href="assets/css/width-full.css" rel="stylesheet" type="text/css" title="default">
	<link href="/templates/modex/css/catalogue/catalogue.css" rel="stylesheet" type="text/css">
	<link href="/modules/slider/css/style.css" rel="stylesheet" type="text/css">	
	<link href="css/astself.css" rel="stylesheet" type="text/css">
	<!-- JS -->
	<script async="" src="https://mc.yandex.ru/metrika/tag.js"></script><script src="assets/js/vendors.js"></script>
<script src="/lib/jQuery_ui/jquery-ui.js"></script>
	<title>Об оплате - AMotors.spb</title>
<meta name="keywords" content="автосервис шушары, автосервис Пушкин, автозапчасть адрес телефон, автозапчасти шушары, магазин автозапчастей +в пушкине, автосервис отзывы">
<meta name="description" content="Автосервис Автошуши в Шушарах автозапчасти Пушкин СТО">
	<link rel="stylesheet" href="/templates/modex/css/docpart/style.css" type="text/css">
	<script src="/lib/jQuery_ui/jquery-ui.js"></script>
	<link href="/lib/jQuery_ui/jquery-ui.css" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css?family=PT+Sans:regular,italic,bold,bolditalic" rel="stylesheet" type="text/css">	
</head>
<body><div style="position:fixed;top:0px;left:0px;width:0;height:0;" id="scrollzipPoint"></div>
<!-- Preloader -->
<div id="preloader" class="" style="display: none;">
    <div id="status" style="display: none;">&nbsp;</div>
</div>
</body></html>
HTML;

/**
 * Класс для извлечения мета-тегов из HTML с использованием итераторов SPL
 */
class MetaTagExtractor
{
    private $dom;
    private $xpath;
    
    public function __construct(string $html)
    {
        $this->dom = new DOMDocument();
        // Подавляем предупреждения при парсинге невалидного HTML
        @$this->dom->loadHTML($html);
        $this->xpath = new DOMXPath($this->dom);
    }
    
    /**
     * Извлекает мета-теги используя итерацию по DOMNodeList
     * @return array Ассоциативный массив с мета-тегами
     */
    public function extractMetaTags(): array
    {
        $result = [
            'title' => null,
            'description' => null,
            'keywords' => null
        ];
        
        // Извлекаем title
        $titleNodes = $this->xpath->query('//title');
        if ($titleNodes->length > 0) {
            // Итерация по DOMNodeList (реализует Traversable)
            foreach ($titleNodes as $node) {
                $result['title'] = trim($node->textContent);
                break; // Берём первый title
            }
        }
        
        // Извлекаем meta-теги
        $metaNodes = $this->xpath->query('//meta[@name]');
        
        // Итерация по всем meta-тегам с атрибутом name
        foreach ($metaNodes as $metaNode) {
            $name = strtolower($metaNode->getAttribute('name'));
            $content = $metaNode->getAttribute('content');
            
            // Используем match для фильтрации нужных мета-тегов
            switch ($name) {
                case 'description':
                    $result['description'] = trim($content);
                    break;
                case 'keywords':
                    $result['keywords'] = trim($content);
                    break;
            }
        }
        
        return $result;
    }
    
    /**
     * Альтернативный метод с использованием ArrayIterator
     * Демонстрирует явное использование итератора SPL
     */
    public function extractMetaTagsWithIterator(): array
    {
        $result = [
            'title' => null,
            'description' => null,
            'keywords' => null
        ];
        
        // Title
        $titleNodes = $this->xpath->query('//title');
        if ($titleNodes->length > 0) {
            $result['title'] = trim($titleNodes->item(0)->textContent);
        }
        
        // Meta-теги через ArrayIterator
        $metaNodes = $this->xpath->query('//meta[@name]');
        $metaArray = [];
        
        // Преобразуем DOMNodeList в массив для использования с ArrayIterator
        foreach ($metaNodes as $node) {
            $metaArray[] = $node;
        }
        
        // Создаём итератор
        $iterator = new ArrayIterator($metaArray);
        
        // Итерация через ArrayIterator
        while ($iterator->valid()) {
            $metaNode = $iterator->current();
            $name = strtolower($metaNode->getAttribute('name'));
            $content = $metaNode->getAttribute('content');
            
            if ($name === 'description') {
                $result['description'] = trim($content);
            } elseif ($name === 'keywords') {
                $result['keywords'] = trim($content);
            }
            
            $iterator->next();
        }
        
        return $result;
    }
    
    /**
     * Форматированный вывод результата
     */
    public function printFormatted(array $metaTags): void
    {
        echo "=== Извлечённые мета-теги ===\n\n";
        
        echo "Title:\n";
        echo "  " . ($metaTags['title'] ?? '(не найден)') . "\n\n";
        
        echo "Description:\n";
        echo "  " . ($metaTags['description'] ?? '(не найден)') . "\n\n";
        
        echo "Keywords:\n";
        echo "  " . ($metaTags['keywords'] ?? '(не найден)') . "\n\n";
    }
}

// Запуск
echo "Метод 1: Прямая итерация по DOMNodeList\n";
echo str_repeat("=", 50) . "\n";
$extractor = new MetaTagExtractor($html);
$metaTags = $extractor->extractMetaTags();
$extractor->printFormatted($metaTags);

echo "\n" . str_repeat("=", 50) . "\n\n";

echo "Метод 2: Использование ArrayIterator (явная итерация SPL)\n";
echo str_repeat("=", 50) . "\n";
$metaTags2 = $extractor->extractMetaTagsWithIterator();
$extractor->printFormatted($metaTags2);
