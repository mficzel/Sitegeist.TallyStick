<?php
declare(strict_types=1);

namespace Sitegeist\TallyStick\Domain;

use Neos\Flow\Annotations as Flow;
use Neos\Cache\Frontend\VariableFrontend;

class BeanCounter
{

    /**
     * @var VariableFrontend
     * @Flow\Inject
     */
    protected $cache;

    protected $currentData = [];

    public function countSet(string $cacheName, int $bytes): void
    {
        $numBefore = $this->currentData[$cacheName . '_set_num'] ?? 0;
        $bytesBefore = $this->currentData[$cacheName . '_set_bytes'] ?? 0;

        $this->currentData[$cacheName . '_set_num'] = $numBefore + 1;
        $this->currentData[$cacheName . '_set_bytes'] = $bytesBefore + $bytes;
    }

    public function countGet(string $cacheName, int $bytes): void
    {
        $numBefore = $this->currentData[$cacheName . '_get_num'] ?? 0;
        $bytesBefore = $this->currentData[$cacheName . '_get_bytes'] ?? 0;

        $this->currentData[$cacheName . '_get_num'] = $numBefore + 1;
        $this->currentData[$cacheName . '_get_bytes'] = $bytesBefore + $bytes;
    }

    public function countHas(string $cacheName): void
    {
        $num = $this->currentData[$cacheName . '_has'] ?? 0;
        $num ++;
        $this->currentData[$cacheName . '_has'] = $num;
    }

    public function countRemove(string $cacheName): void
    {
        $num = $this->currentData[$cacheName . '_remove'] ?? 0;
        $num ++;
        $this->currentData[$cacheName . '_remove'] = $num;
    }

    public function countFlush(string $cacheName): void
    {
        $num = $this->currentData[$cacheName . '_flush'] ?? 0;
        $num ++;
        $this->currentData[$cacheName . '_flush'] = $num;
    }

    public function countCollectGarbage(string $cacheName): void
    {
        $num = $this->currentData[$cacheName . '_collectGarbage'] ?? 0;
        $num ++;
        $this->currentData[$cacheName . '_collectGarbage'] = $num;
    }

    public function shutdownObject(): void
    {
        $data = $this->cache->get('statistics');
        if ($data === false) {
            $data = [];
        }
        foreach ($this->currentData as $key => $value) {
            $data[$key] = array_key_exists($key, $data) ? $data[$key] + $value : $value;
        }
        $this->cache->set('statistics', $data);
    }

    public function getStatSoFar(): array
    {
        $data = $this->cache->get('statistics');
        if (is_array($data)) {
            return $data;
        } else {
            return [];
        }
    }

    public function reset(): void
    {
        $this->cache->remove('statistics');
    }
}
