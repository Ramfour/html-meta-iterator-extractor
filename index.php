<?php
/**
 * Практическое задание 26.6.1
 * Извлечение мета-тегов (title, description, keywords) из HTML-файла
 * с помощью класса, реализующего интерфейс Iterator
 *
 * @see https://www.php.net/manual/ru/class.iterator.php
 */

/**
 * Итератор по мета-тегам HTML-файла.
 *
 * Реализует интерфейс Iterator для обхода найденных мета-тегов
 * (title, description, keywords) из произвольного HTML-файла.
 */
class MetaTagIterator implements Iterator
{
    /** @var array Извлечённые мета-теги в формате ['name' => 'value'] */
    private array $tags = [];

    /** @var int Текущая позиция итератора */
    private int $position = 0;

    /**
     * @param string $filePath Путь к HTML-файлу
     * @throws RuntimeException если файл не найден
     */
    public function __construct(string $filePath)
    {
        if (!file_exists($filePath)) {
            throw new RuntimeException("Файл не найден: $filePath");
        }

        $html = file_get_contents($filePath);
        $this->tags = $this->extractTags($html);
    }

    /**
     * Парсит HTML и извлекает title, description, keywords
     *
     * @param string $html HTML-содержимое файла
     * @return array Массив ['name' => 'value']
     */
    private function extractTags(string $html): array
    {
        $dom = new DOMDocument();
        @$dom->loadHTML($html);
        $xpath = new DOMXPath($dom);

        $result = [];

        // Extract title
        $titleNodes = $xpath->query('//title');
        if ($titleNodes->length > 0) {
            $result['title'] = trim($titleNodes->item(0)->textContent);
        }

        // Extract meta name=description and meta name=keywords
        $metaNodes = $xpath->query('//meta[@name and @content]');
        foreach ($metaNodes as $node) {
            $name = strtolower($node->getAttribute('name'));
            if ($name === 'description' || $name === 'keywords') {
                $result[$name] = trim($node->getAttribute('content'));
            }
        }

        return $result;
    }

    // -------------------------
    // Iterator interface methods
    // -------------------------

    /**
     * Возвращает значение текущего элемента
     */
    public function current(): string
    {
        $keys = array_keys($this->tags);
        return $this->tags[$keys[$this->position]];
    }

    /**
     * Возвращает ключ текущего элемента
     */
    public function key(): string
    {
        $keys = array_keys($this->tags);
        return $keys[$this->position];
    }

    /**
     * Переходит к следующему элементу
     */
    public function next(): void
    {
        $this->position++;
    }

    /**
     * Сбрасывает итератор в начало
     */
    public function rewind(): void
    {
        $this->position = 0;
    }

    /**
     * Проверяет, существует ли текущий элемент
     */
    public function valid(): bool
    {
        return $this->position < count($this->tags);
    }
}

// -------------------------
// Использование
// -------------------------

$filePath = __DIR__ . '/page.html';

echo "Извлечение мета-тегов из файла: " . basename($filePath) . "\n";
echo str_repeat('=', 50) . "\n\n";

$iterator = new MetaTagIterator($filePath);

foreach ($iterator as $name => $value) {
    echo strtoupper($name) . ":\n";
    echo "  " . $value . "\n\n";
}
