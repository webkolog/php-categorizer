<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../categorizer.php';

class CategorizerTest extends TestCase
{
    private static $db;

    protected function setUp()
    {
        parent::setUp();

        if (self::$db === null) {
            $host = getenv('DB_HOST') ? getenv('DB_HOST') : '127.0.0.1';
            $port = getenv('DB_PORT') ? getenv('DB_PORT') : '3306';
            $dbname = getenv('DB_NAME') ? getenv('DB_NAME') : 'test_db';
            $user = getenv('DB_USER') ? getenv('DB_USER') : 'root';
            $pass = getenv('DB_PASS') ? getenv('DB_PASS') : 'root';

            self::$db = new PDO("mysql:host={$host};port={$port};dbname={$dbname};charset=utf8", $user, $pass);
            self::$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Tabloyu oluştur
            self::$db->exec("CREATE TABLE IF NOT EXISTS categories (
                id INT AUTO_INCREMENT PRIMARY KEY,
                parent_id INT DEFAULT 0,
                category_name VARCHAR(255) NOT NULL,
                sort_order INT DEFAULT 0
            )");
        }

        // Örnek verileri her test öncesi sıfırla
        self::$db->exec("TRUNCATE TABLE categories");
        self::$db->exec("INSERT INTO categories (id, parent_id, category_name, sort_order) VALUES
            (1, 0, 'Electronics', 1),
            (2, 1, 'Laptops', 1),
            (3, 2, 'Gaming Laptops', 1),
            (4, 0, 'Clothing', 2)
        ");
    }

    public function testTreeAndNestedListGeneration()
    {
        $categorizer = new Categorizer(self::$db);
        $categorizer->tableName = "categories";
        $categorizer->colId = "id";
        $categorizer->colTop = "parent_id";
        $categorizer->colName = "category_name";
        $categorizer->listRow = "sort_order";
        $categorizer->orderType = "ASC";
        $categorizer->selectedId = 3; // 'Gaming Laptops'

        $categorizer->makeCategorize();

        // 1. Ağaç Listesi Kontrolleri
        $this->assertCount(4, $categorizer->treeList);
        $this->assertEquals(0, $categorizer->treeList[0]['depth']); // Electronics
        $this->assertEquals(1, $categorizer->treeList[1]['depth']); // Laptops
        $this->assertEquals(2, $categorizer->treeList[2]['depth']); // Gaming Laptops

        // 2. Yol (Breadcrumb) Kontrolleri
        $this->assertCount(3, $categorizer->nestedList);
        $this->assertEquals('Electronics', $categorizer->nestedList[0]['name']);
        $this->assertEquals('Laptops', $categorizer->nestedList[1]['name']);
        $this->assertEquals('Gaming Laptops', $categorizer->nestedList[2]['name']);
    }
}
