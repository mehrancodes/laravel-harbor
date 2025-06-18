<?php

use App\Services\Forge\Actions\RecreateDatabase;
use App\Services\Forge\ForgeSetting;
use App\Services\Forge\ForgeService;
use Laravel\Forge\Forge;
use Laravel\Forge\Resources\Server;

beforeEach(function () {
    // Setup action and common variables
    $this->action = new RecreateDatabase();
    $this->serverId = '12345';
    $this->dbName = 'test_db';
    $this->dbPassword = 'test_password';
    
    // Create mocks that will be used by all tests
    $this->forgeMock = Mockery::mock(Forge::class);
    $this->settingMock = Mockery::mock(ForgeSetting::class);
    $this->server = new Server(['id' => $this->serverId]);
    
    // Setup base service mock that can be customized per test
    $this->service = Mockery::mock(ForgeService::class);
    $this->service->forge = $this->forgeMock;
    $this->service->server = $this->server;
});

afterEach(function () {
    Mockery::close();
});

it('skips recreation when database exists and force delete disabled', function () {
    // Configure setting for this specific test
    $this->settingMock->forceDeleteOldDatabase = false;
    $this->service->setting = $this->settingMock;

    // Mock database exists
    $this->forgeMock->shouldReceive('databases')
        ->once()
        ->with($this->serverId)
        ->andReturn([
            (object) ['name' => $this->dbName, 'id' => 99],
        ]);

    // Database should not be deleted
    $this->forgeMock->shouldNotReceive('deleteDatabase');
    $this->forgeMock->shouldNotReceive('deleteDatabaseUser');

    // Database should not be created when skipping recreation
    $this->forgeMock->shouldNotReceive('createDatabase');

    // Act
    $result = $this->action->handle($this->service, $this->dbName, $this->dbPassword);

    // Assert
    expect($result)->toBeTrue(); // Should return true to indicate env vars need updating
});

it('recreates database when exists and force delete enabled', function () {
    // Configure setting for this specific test
    $this->settingMock->forceDeleteOldDatabase = true;
    $this->service->setting = $this->settingMock;

    // Mock existing database
    $dbId = 99;
    $userId = 88;

    $this->forgeMock->shouldReceive('databases')
        ->once()
        ->with($this->serverId)
        ->andReturn([
            (object) ['name' => $this->dbName, 'id' => $dbId],
        ]);

    // Should delete the database
    $this->forgeMock->shouldReceive('deleteDatabase')
        ->once()
        ->with($this->serverId, $dbId);

    // Mock existing user
    $this->forgeMock->shouldReceive('databaseUsers')
        ->once()
        ->with($this->serverId)
        ->andReturn([
            (object) ['name' => $this->dbName, 'id' => $userId],
        ]);

    // Should delete the database user
    $this->forgeMock->shouldReceive('deleteDatabaseUser')
        ->once()
        ->with($this->serverId, $userId);

    // Should create a new database
    $this->forgeMock->shouldReceive('createDatabase')
        ->once()
        ->with($this->serverId, [
            'name' => $this->dbName,
            'user' => $this->dbName,
            'password' => $this->dbPassword,
        ]);

    // Create a partial mock to skip actual waiting
    $actionSpy = Mockery::mock(RecreateDatabase::class)->makePartial();
    $actionSpy->shouldAllowMockingProtectedMethods();
    $actionSpy->shouldReceive('waitForDatabaseDeletion')->once()->andReturn(null);

    // Act
    $result = $actionSpy->handle($this->service, $this->dbName, $this->dbPassword);

    // Assert
    expect($result)->toBeTrue();
});

it('creates new database when none exists', function () {
    // Set the service to use the mocked setting
    $this->service->setting = $this->settingMock;

    // No existing database
    $this->forgeMock->shouldReceive('databases')
        ->once()
        ->with($this->serverId)
        ->andReturn([
            (object) ['name' => 'other_db', 'id' => 55],
        ]);

    // Check for users (none match)
    $this->forgeMock->shouldReceive('databaseUsers')
        ->once()
        ->with($this->serverId)
        ->andReturn([
            (object) ['name' => 'other_user', 'id' => 66],
        ]);

    // Should create a new database
    $this->forgeMock->shouldReceive('createDatabase')
        ->once()
        ->with($this->serverId, [
            'name' => $this->dbName,
            'user' => $this->dbName,
            'password' => $this->dbPassword,
        ]);

    // We should not wait for deletion since nothing was deleted
    $actionSpy = Mockery::mock(RecreateDatabase::class)->makePartial();
    $actionSpy->shouldAllowMockingProtectedMethods();
    $actionSpy->shouldNotReceive('waitForDatabaseDeletion');

    // Act
    $result = $actionSpy->handle($this->service, $this->dbName, $this->dbPassword);

    // Assert
    expect($result)->toBeTrue();
});
