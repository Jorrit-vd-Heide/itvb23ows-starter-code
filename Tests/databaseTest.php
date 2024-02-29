<?php

use PHPUnit\Framework\TestCase;

class DatabaseTest extends TestCase {
    protected $db;

    protected function setUp(): void {
        parent::setUp();
        $this->db = new mysqli('127.0.0.1', 'root', 'password', 'hive');
        if ($this->db->connect_error) {
            die("Connection failed: " . $this->db->connect_error);
        }
    }

    protected function tearDown(): void {
        parent::tearDown();
        // Clean up test data after each test
        // Perform necessary cleanup operations, such as deleting test records
    }

    public function testDatabaseConnection() {
        // Check if the database connection is established
        $this->assertInstanceOf(mysqli::class, $this->db);
    }
}
