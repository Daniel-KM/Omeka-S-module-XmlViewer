<?php declare(strict_types=1);

namespace XmlViewerTest;

use Laminas\ServiceManager\ServiceLocatorInterface;
use Omeka\Api\Manager as ApiManager;

/**
 * Shared test helpers for XmlViewer module tests.
 */
trait XmlViewerTestTrait
{
    /**
     * @var bool Whether admin is logged in.
     */
    protected bool $isLoggedIn = false;

    /**
     * @var array IDs of items created during tests (for cleanup).
     */
    protected array $createdResources = [];

    /**
     * Get the service locator.
     */
    protected function getServiceLocator(): ServiceLocatorInterface
    {
        if (isset($this->application) && $this->application !== null) {
            return $this->application->getServiceManager();
        }
        return $this->getApplication()->getServiceManager();
    }

    /**
     * Get the API manager.
     */
    protected function api(): ApiManager
    {
        if ($this->isLoggedIn) {
            $this->ensureLoggedIn();
        }
        return $this->getServiceLocator()->get('Omeka\ApiManager');
    }

    /**
     * Login as admin user.
     */
    protected function loginAdmin(): void
    {
        $this->isLoggedIn = true;
        $this->ensureLoggedIn();
    }

    /**
     * Ensure admin is logged in on the current application instance.
     */
    protected function ensureLoggedIn(): void
    {
        $services = $this->getServiceLocator();
        $auth = $services->get('Omeka\AuthenticationService');

        if ($auth->hasIdentity()) {
            return;
        }

        $adapter = $auth->getAdapter();
        $adapter->setIdentity('admin@example.com');
        $adapter->setCredential('root');
        $auth->authenticate();
    }

    /**
     * Logout current user.
     */
    protected function logout(): void
    {
        $this->isLoggedIn = false;
        $auth = $this->getServiceLocator()->get('Omeka\AuthenticationService');
        $auth->clearIdentity();
    }

    /**
     * Get the path to the fixtures directory.
     */
    protected function getFixturesPath(): string
    {
        return dirname(__DIR__) . '/fixtures';
    }

    /**
     * Get a fixture file content.
     */
    protected function getFixture(string $name): string
    {
        $path = $this->getFixturesPath() . '/' . $name;
        if (!file_exists($path)) {
            throw new \RuntimeException("Fixture not found: $path");
        }
        return file_get_contents($path);
    }

    /**
     * Clean up created resources after test.
     */
    protected function cleanupResources(): void
    {
        foreach ($this->createdResources as $resource) {
            try {
                $this->api()->delete($resource['type'], $resource['id']);
            } catch (\Exception $e) {
                // Ignore errors during cleanup.
            }
        }
        $this->createdResources = [];
    }
}
