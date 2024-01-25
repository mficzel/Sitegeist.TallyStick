<?php
declare(strict_types=1);

namespace Sitegeist\TallyStick\Cache;

use Neos\Flow\Annotations as Flow;
use Neos\Cache\Backend\AbstractBackend;
use Neos\Cache\Backend\BackendInterface;
use Neos\Cache\BackendInstantiationTrait;
use Neos\Flow\Log\Utility\LogEnvironment;
use Sitegeist\TallyStick\Domain\BeanCounter;
use Neos\Cache\Backend\IterableBackendInterface;

class TallyStickBackend extends AbstractBackend implements IterableBackendInterface
{
    use BackendInstantiationTrait;

    protected array $backendOptions;
    protected string $backendObjectNameName;

    protected ?BackendInterface $backend = null;

    /**
     * @var BeanCounter
     * @Flow\Inject
     */
    protected BeanCounter $beanCounter;


    /**
     * This setter is used by AbstractBackend::setProperties()
     */
    protected function setBackend(string $backend): void
    {
        $this->backendObjectNameName = $backend;
    }

    /**
     * This setter is used by AbstractBackend::setProperties()
     */
    protected function setBackendOptions(array $backendOptions): void
    {
        $this->backendOptions = $backendOptions;
    }

    protected function getBackend(): BackendInterface
    {
        if ($this->backend instanceof BackendInterface) {
            return $this->backend;
        }
        $this->backend = $this->instantiateBackend($this->backendObjectNameName, $this->backendOptions, $this->environmentConfiguration);
        return $this->backend;
    }

    public function set(string $entryIdentifier, string $data, array $tags = [], int $lifetime = null): void
    {
        $this->beanCounter->countSet($this->cache->getIdentifier(), strlen($data));
        $this->getBackend()->set($entryIdentifier, $data, $tags, $lifetime);
    }

    public function get(string $entryIdentifier)
    {
        $data = $this->getBackend()->get($entryIdentifier);
        $this->beanCounter->countGet($this->cache->getIdentifier(), ($data === false) ? 0 : strlen($data));
        return $data;
    }

    public function has(string $entryIdentifier): bool
    {
        $this->beanCounter->countHas($this->cache->getIdentifier());
        return $this->getBackend()->has($entryIdentifier);
    }

    public function remove(string $entryIdentifier): bool
    {
        $this->beanCounter->countRemove($this->cache->getIdentifier());
        return $this->getBackend()->remove($entryIdentifier);
    }

    public function flush(): void
    {
        $this->beanCounter->countFlush($this->cache->getIdentifier());
        $this->getBackend()->flush();
    }

    public function collectGarbage(): void
    {
        $this->beanCounter->countCollectGarbage($this->cache->getIdentifier());
        $this->getBackend()->collectGarbage();
    }

    // IterableBackendInterface

    public function current(): mixed
    {
        assert ($this->backend instanceof IterableBackendInterface);
        return $this->backend->current();
    }

    public function next(): void
    {
        assert ($this->backend instanceof IterableBackendInterface);
        $this->backend->next();
    }

    public function key(): string|int|bool|null|float
    {
        assert ($this->backend instanceof IterableBackendInterface);
        return$this->backend->key();
    }

    public function valid(): bool
    {
        assert ($this->backend instanceof IterableBackendInterface);
        return $this->backend->valid();
    }

    public function rewind(): void
    {
        assert ($this->backend instanceof IterableBackendInterface);
        $this->backend->rewind();
    }
}
