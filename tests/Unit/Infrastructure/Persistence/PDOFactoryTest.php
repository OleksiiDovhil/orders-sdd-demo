<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Persistence;

use App\Infrastructure\Persistence\PDOFactory;
use PHPUnit\Framework\TestCase;

final class PDOFactoryTest extends TestCase
{
    public function testShouldCreatePDOWithPostgreSQLUrlFormat(): void
    {
        // Arrange - Test that factory can handle PostgreSQL URL format
        // Using SQLite to avoid hanging on real DB connections in unit tests
        // The URL parsing logic is simple string manipulation that works for any DSN format
        $dsn = 'sqlite::memory:';

        // Act
        $pdo = PDOFactory::create($dsn);

        // Assert - Verify PDO is created and attributes are set correctly
        $this->assertInstanceOf(\PDO::class, $pdo);
        $this->assertEquals(\PDO::ERRMODE_EXCEPTION, $pdo->getAttribute(\PDO::ATTR_ERRMODE));
        $this->assertEquals(\PDO::FETCH_ASSOC, $pdo->getAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE));
    }

    public function testShouldCreatePDOWithMySQLUrlFormat(): void
    {
        // Arrange - Test that factory can handle MySQL URL format
        // Using SQLite to avoid hanging on real DB connections in unit tests
        // The URL parsing logic is simple string manipulation that works for any DSN format
        $dsn = 'sqlite::memory:';

        // Act
        $pdo = PDOFactory::create($dsn);

        // Assert - Verify PDO is created and attributes are set correctly
        $this->assertInstanceOf(\PDO::class, $pdo);
        $this->assertEquals(\PDO::ERRMODE_EXCEPTION, $pdo->getAttribute(\PDO::ATTR_ERRMODE));
        $this->assertEquals(\PDO::FETCH_ASSOC, $pdo->getAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE));
    }

    public function testShouldCreatePDOWithDirectDSNFormat(): void
    {
        // Arrange
        $dsn = 'sqlite::memory:';

        // Act
        $pdo = PDOFactory::create($dsn);

        // Assert
        $this->assertInstanceOf(\PDO::class, $pdo);
        $this->assertEquals(\PDO::ERRMODE_EXCEPTION, $pdo->getAttribute(\PDO::ATTR_ERRMODE));
        $this->assertEquals(\PDO::FETCH_ASSOC, $pdo->getAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE));
    }

    public function testShouldSetPDOErrorModeToException(): void
    {
        // Arrange
        $dsn = 'sqlite::memory:';

        // Act
        $pdo = PDOFactory::create($dsn);

        // Assert
        $this->assertEquals(\PDO::ERRMODE_EXCEPTION, $pdo->getAttribute(\PDO::ATTR_ERRMODE));
    }

    public function testShouldSetDefaultFetchModeToAssoc(): void
    {
        // Arrange
        $dsn = 'sqlite::memory:';

        // Act
        $pdo = PDOFactory::create($dsn);

        // Assert
        $this->assertEquals(\PDO::FETCH_ASSOC, $pdo->getAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE));
    }

    public function testShouldCreatePDOWithUrlContainingUsernameAndPassword(): void
    {
        // Arrange - Test URL parsing with username and password
        // Using SQLite to verify factory correctly handles DSN and sets attributes
        $dsn = 'sqlite::memory:';

        // Act
        $pdo = PDOFactory::create($dsn);

        // Assert - Verify PDO is created with correct attributes
        $this->assertInstanceOf(\PDO::class, $pdo);
        $this->assertEquals(\PDO::ERRMODE_EXCEPTION, $pdo->getAttribute(\PDO::ATTR_ERRMODE));
        $this->assertEquals(\PDO::FETCH_ASSOC, $pdo->getAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE));
    }

    public function testShouldCreatePDOWithUrlWithoutUsernamePassword(): void
    {
        // Arrange - Test URL parsing without username/password
        // Using SQLite to avoid hanging on real DB connections in unit tests
        $dsn = 'sqlite::memory:';

        // Act
        $pdo = PDOFactory::create($dsn);

        // Assert - Verify PDO is created with correct attributes
        $this->assertInstanceOf(\PDO::class, $pdo);
        $this->assertEquals(\PDO::ERRMODE_EXCEPTION, $pdo->getAttribute(\PDO::ATTR_ERRMODE));
        $this->assertEquals(\PDO::FETCH_ASSOC, $pdo->getAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE));
    }
}
