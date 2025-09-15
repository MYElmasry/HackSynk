<?php
// Database setup - create database and table if they don't exist
function setupDatabase() {
    $host = 'localhost';
    $username = 'root';
    $password = '';
    $dbname = 'hacksynk';
    
    try {
        // First connect without database name to create it if needed
        $pdo_setup = new PDO("mysql:host=$host", $username, $password);
        $pdo_setup->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Create database if it doesn't exist
        $sql = "CREATE DATABASE IF NOT EXISTS $dbname";
        $pdo_setup->exec($sql);
        
        // Now connect to the database
        $pdo_setup = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $pdo_setup->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Create participants table
        $sql = "CREATE TABLE IF NOT EXISTS participants (
            id INT AUTO_INCREMENT PRIMARY KEY,
            full_name VARCHAR(100) NOT NULL,
            username VARCHAR(50) UNIQUE NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            city_country VARCHAR(100),
            skills_expertise VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        
        $pdo_setup->exec($sql);
        
        // Create organizers table
        $sql = "CREATE TABLE IF NOT EXISTS organizers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            full_name VARCHAR(100) NOT NULL,
            username VARCHAR(50) UNIQUE NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            organization_name VARCHAR(100),
            job_title_position VARCHAR(100),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        
        $pdo_setup->exec($sql);
        
        // Create judges table
        $sql = "CREATE TABLE IF NOT EXISTS judges (
            id INT AUTO_INCREMENT PRIMARY KEY,
            full_name VARCHAR(100) NOT NULL,
            username VARCHAR(50) UNIQUE NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            professional_title VARCHAR(100),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        
        $pdo_setup->exec($sql);
        
        // Close the setup connection
        $pdo_setup = null;
        
        return true;
        
    } catch(PDOException $e) {
        die("Database setup failed: " . $e->getMessage());
    }
}
?>